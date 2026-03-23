<?php
// ============================================================
// api/analytics.php — Analytics & Stats API
// ============================================================

header('Content-Type: application/json');
require_once '../config.php';

function respond($data): void {
    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

try {
    $db = getDBConnection();

    // Total messages
    $totalMsgs = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();

    // Total sessions
    $totalSessions = $db->query("SELECT COUNT(*) FROM sessions")->fetchColumn();

    // Messages today
    $msgsToday = $db->query(
        "SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()"
    )->fetchColumn();

    // Intent breakdown
    $intents = $db->query(
        "SELECT intent, count FROM intent_analytics ORDER BY count DESC LIMIT 10"
    )->fetchAll();

    // Recent messages (last 10)
    $recent = $db->query(
        "SELECT m.role, LEFT(m.content, 80) as preview, m.intent, m.created_at, s.user_name
         FROM messages m JOIN sessions s ON m.session_id = s.session_id
         ORDER BY m.created_at DESC LIMIT 10"
    )->fetchAll();

    respond([
        'total_messages'  => (int)$totalMsgs,
        'total_sessions'  => (int)$totalSessions,
        'messages_today'  => (int)$msgsToday,
        'intent_breakdown'=> $intents,
        'recent_messages' => $recent,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
