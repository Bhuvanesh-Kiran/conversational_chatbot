-- ============================================================
-- Context-Aware Conversational Chatbot - Database Schema
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS chatbot_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE chatbot_db;

-- Sessions table: tracks each user/visitor session
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128) NOT NULL UNIQUE,
    user_name VARCHAR(100) DEFAULT 'Guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Messages table: stores full conversation history per session
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128) NOT NULL,
    role ENUM('user', 'assistant') NOT NULL,
    content TEXT NOT NULL,
    intent VARCHAR(100) DEFAULT NULL,        -- detected intent label
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_created (created_at)
);

-- Analytics table: tracks intent counts for dashboard
CREATE TABLE IF NOT EXISTS intent_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intent VARCHAR(100) NOT NULL,
    count INT DEFAULT 1,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_intent (intent)
);

-- Sample FAQ data (optional seed)
INSERT IGNORE INTO intent_analytics (intent, count) VALUES
('greeting', 0),
('billing', 0),
('technical_support', 0),
('refund', 0),
('general_inquiry', 0);
