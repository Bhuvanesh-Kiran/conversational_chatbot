<?php
// ============================================================
// config.php — Central Configuration (OpenRouter Version)
// ============================================================

// --- DATABASE SETTINGS ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'chatbot_db');
define('DB_USER', 'root');       // ⚠️ your MySQL username
define('DB_PASS', '');           // ⚠️ your MySQL password
define('DB_CHARSET', 'utf8mb4');

// --- OPENROUTER API (FREE) ---
// ⚠️ Get free key from: https://openrouter.ai/
// Click "Sign In" → Google login → Keys → Create Key
define('OPENROUTER_API_KEY', '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');

// --- CHATBOT PERSONA ---
define('BOT_NAME', 'Aria');
define('SYSTEM_PROMPT', "You are Aria, a helpful and friendly customer support assistant. 
You help users with billing questions, technical issues, refunds, account management, and general inquiries.
Keep responses concise, warm, and professional.
If you cannot answer something, politely say so and offer to connect the user with a human agent.
Always maintain context from the conversation history provided.");

// --- SESSION SETTINGS ---
define('SESSION_LIFETIME', 3600);

function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}