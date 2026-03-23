<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once '../config.php';
session_start();

function respond(bool $success, $data = null, string $error = ''): void {
    echo json_encode(['success' => $success, 'data' => $data, 'error' => $error]);
    exit();
}

function detectIntent(string $message): string {
    $msg = strtolower($message);
    $intents = [
        'greeting'          => ['hello', 'hi', 'hey', 'good morning', 'good evening', 'howdy'],
        'billing'           => ['bill', 'invoice', 'payment', 'charge', 'subscription', 'plan', 'price', 'cost'],
        'refund'            => ['refund', 'money back', 'return', 'cancel', 'reimburse'],
        'technical_support' => ['error', 'bug', 'issue', 'problem', 'not working', 'broken', 'crash', 'help', 'fix'],
        'account'           => ['account', 'password', 'login', 'email', 'profile', 'username', 'sign in'],
        'goodbye'           => ['bye', 'goodbye', 'see you', 'take care', 'thanks', 'thank you'],
    ];
    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains($msg, $kw)) return $intent;
        }
    }
    return 'general_inquiry';
}

function ensureSession(PDO $db, string $sid, string $userName): void {
    $db->prepare("INSERT IGNORE INTO sessions (session_id, user_name) VALUES (?, ?)")->execute([$sid, $userName]);
}

function saveMessage(PDO $db, string $sid, string $role, string $content, string $intent = ''): void {
    $db->prepare("INSERT INTO messages (session_id, role, content, intent) VALUES (?, ?, ?, ?)")->execute([$sid, $role, $content, $intent ?: null]);
}

function loadHistory(PDO $db, string $sid, int $limit = 20): array {
    $stmt = $db->prepare("SELECT role, content FROM messages WHERE session_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$sid, $limit]);
    $rows = array_reverse($stmt->fetchAll());
    return array_map(fn($r) => ['role' => $r['role'], 'content' => $r['content']], $rows);
}

function trackIntent(PDO $db, string $intent): void {
    $db->prepare("INSERT INTO intent_analytics (intent, count) VALUES (?, 1) ON DUPLICATE KEY UPDATE count = count + 1, last_seen = NOW()")->execute([$intent]);
}

// ---- OpenRouter API (Free models, no strict rate limits) ----
function callAI(array $messages): string {
    // Build messages array with system prompt
    $apiMessages = [
        ['role' => 'system', 'content' => SYSTEM_PROMPT]
    ];
    foreach ($messages as $msg) {
        $apiMessages[] = [
            'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
            'content' => $msg['content']
        ];
    }

    $payload = json_encode([
        'model'    => 'openrouter/free', // free model on OpenRouter
        'messages' => $apiMessages,
        'max_tokens' => 1024,
        'temperature' => 0.7,
    ]);

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: http://localhost',
            'X-Title: Aria Chatbot',
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $result   = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($result === false || $httpCode !== 200) {
        return "I'm having trouble connecting right now. Please try again. (HTTP: $httpCode)";
    }

    $decoded = json_decode($result, true);
    return $decoded['choices'][0]['message']['content'] ?? "Sorry, I couldn't generate a response.";
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = getDBConnection();

    if ($action === 'init_session') {
        $userName = trim($_POST['user_name'] ?? 'Guest');
        $sid = bin2hex(random_bytes(16));
        $_SESSION['chat_sid'] = $sid;
        $_SESSION['user_name'] = $userName;
        ensureSession($db, $sid, $userName);
        $greeting = "Hi " . htmlspecialchars($userName) . "! I'm " . BOT_NAME . ", your support assistant. How can I help you today?";
        saveMessage($db, $sid, 'assistant', $greeting, 'greeting');
        respond(true, ['session_id' => $sid, 'greeting' => $greeting, 'bot_name' => BOT_NAME]);
    }

    elseif ($action === 'send_message') {
        $sid     = $_SESSION['chat_sid'] ?? $_POST['session_id'] ?? '';
        $userMsg = trim($_POST['message'] ?? '');
        if (empty($sid) || empty($userMsg)) respond(false, null, 'Missing session or message.');
        $userName = $_SESSION['user_name'] ?? 'Guest';
        ensureSession($db, $sid, $userName);
        $intent = detectIntent($userMsg);
        trackIntent($db, $intent);
        saveMessage($db, $sid, 'user', $userMsg, $intent);
        $history = loadHistory($db, $sid, 20);
        $aiReply = callAI($history);
        saveMessage($db, $sid, 'assistant', $aiReply, '');
        respond(true, ['reply' => $aiReply, 'intent' => $intent]);
    }

    elseif ($action === 'get_history') {
        $sid = $_SESSION['chat_sid'] ?? $_GET['session_id'] ?? '';
        if (empty($sid)) respond(false, null, 'No session.');
        $stmt = $db->prepare("SELECT role, content, intent, created_at FROM messages WHERE session_id = ? ORDER BY created_at ASC");
        $stmt->execute([$sid]);
        respond(true, $stmt->fetchAll());
    }

    elseif ($action === 'clear_session') {
        $sid = $_SESSION['chat_sid'] ?? '';
        if ($sid) $db->prepare("DELETE FROM messages WHERE session_id = ?")->execute([$sid]);
        session_destroy();
        respond(true, ['message' => 'Session cleared.']);
    }

    else { respond(false, null, 'Unknown action.'); }

} catch (Exception $e) {
    respond(false, null, 'Server error: ' . $e->getMessage());
}