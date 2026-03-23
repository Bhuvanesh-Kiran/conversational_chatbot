# 🤖 Aria — Context-Aware AI Customer Support Chatbot
### Problem Statement 5 | Tech Stack: HTML + CSS + JS + PHP + MySQL + Claude AI

---

## 📁 Project Structure

```
chatbot_project/
├── index.html              ← Main chatbot UI (open this in browser)
├── config.php              ← ⚠️ UPDATE THIS FIRST (DB + API key)
├── api/
│   ├── chat.php            ← Core chat API (session, messages, AI)
│   └── analytics.php       ← Stats API for sidebar
└── database/
    └── schema.sql          ← Run this in MySQL/phpMyAdmin
```

---

## 🚀 Setup Guide (Step by Step)

### Step 1 — Install Requirements
- **XAMPP / WAMP / MAMP** (includes Apache + MySQL + PHP)
- Download: https://www.apachefriends.org/

### Step 2 — Place Files
Copy the entire `chatbot_project/` folder into:
- **XAMPP**: `C:/xampp/htdocs/chatbot_project/`
- **WAMP**: `C:/wamp64/www/chatbot_project/`
- **MAMP**: `/Applications/MAMP/htdocs/chatbot_project/`

### Step 3 — Create Database
1. Open **phpMyAdmin** → http://localhost/phpmyadmin
2. Click **Import** tab
3. Upload `database/schema.sql`
4. Click **Go**

### Step 4 — Update Config ⚠️ REQUIRED
Open `config.php` and update:

```php
// DATABASE
define('DB_HOST', 'localhost');   // usually stays localhost
define('DB_NAME', 'chatbot_db'); // already set
define('DB_USER', 'root');        // ⚠️ your MySQL username
define('DB_PASS', '');            // ⚠️ your MySQL password (XAMPP default = empty)

// AI API KEY
define('AI_API_KEY', 'YOUR_KEY_HERE');

```

### Step 5 — Run
Open browser → **http://localhost/chatbot_project/index.html**

---

## ⚠️ Files You MUST Edit Manually

| File | What to Change | Where |
|------|----------------|-------|
| `config.php` | Line 10: `DB_USER` — your MySQL username | Line 10 |
| `config.php` | Line 11: `DB_PASS` — your MySQL password | Line 11 |
| `config.php` | Line 15: `AI_API_KEY` — paste your API key | Line 15 |
| `config.php` | Line 20: `BOT_NAME` — rename the bot (optional) | Line 20 |
| `config.php` | Lines 21-26: `SYSTEM_PROMPT` — customize bot personality | Lines 21-26 |

**That's it! Only `config.php` needs manual edits.**

---

## 🔑 Getting an Anthropic API Key
1. Go to https://console.anthropic.com/
2. Sign up / Log in
3. Go to **API Keys** → **Create Key**
4. Copy and paste into `config.php`

---

## ✨ Features Implemented

| Feature | Status |
|---------|--------|
| Multi-turn conversation with memory | ✅ |
| Intent detection (billing, refund, tech, greeting…) | ✅ |
| Session management (PHP sessions + MySQL) | ✅ |
| Full conversation history stored in DB | ✅ |
| Claude AI responses via API | ✅ |
| Typing indicator animation | ✅ |
| Quick reply buttons | ✅ |
| Sidebar with live session stats | ✅ |
| Context window log (last 8 messages shown) | ✅ |
| Clear/reset session | ✅ |
| Intent analytics tracked in DB | ✅ |
| Responsive mobile layout | ✅ |

---

## 🗄️ Database Tables

**sessions** — one row per user visit
```
id | session_id | user_name | created_at | last_active
```

**messages** — every message in every conversation
```
id | session_id | role | content | intent | created_at
```

**intent_analytics** — cumulative intent counts
```
id | intent | count | last_seen
```

---

## 🤖 How Context Memory Works

1. User sends message → PHP saves to `messages` table
2. API loads last **20 messages** for that session from DB
3. Full history is sent to Claude as conversation context
4. Claude responds with full awareness of past turns
5. Reply saved to DB → displayed in UI

---

## 🎯 Detected Intents

| Intent | Trigger Words |
|--------|--------------|
| greeting | hello, hi, hey |
| billing | bill, invoice, payment, charge, subscription |
| refund | refund, money back, return, cancel |
| technical_support | error, bug, issue, not working, broken |
| account | password, login, email, profile |
| goodbye | bye, thanks, thank you |
| general_inquiry | (everything else) |

---

## 🛠️ Customization

**Change bot name/personality** → Edit `config.php` lines 20-26

**Add more intents** → Edit `detectIntent()` function in `api/chat.php`

**Add more quick replies** → Edit the quick-replies section in `index.html` (around line 200)

**Change color scheme** → Edit CSS variables in `index.html` `:root` block (lines 20-34)
