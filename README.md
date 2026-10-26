# Hostel Complaint Management System

A full web app built with **PHP + MySQL + HTML/CSS/JS** for a Web Technology lab project.
Students can register, file hostel complaints (electrical, plumbing, cleanliness, etc.),
and track status. Admins can log in, manage all complaints, update status/remarks, and
manage student records. Includes an **AI chatbot assistant** that helps students describe
their issue and auto-fills the complaint form (category + priority + subject), and both
client-side and server-side form validation.

## Features

- Student registration & login (passwords hashed with bcrypt)
- Complaint submission form with category, priority, room number, description
- Client-side (JS) + server-side (PHP) validation on every form
- Student dashboard: stats + recent complaints
- "My Complaints" page with status filter
- Complaint detail page with full status **timeline**
- Admin login (separate from students)
- Admin dashboard: stats, category breakdown, urgent open complaints
- Admin complaint management: search/filter, update status + remarks (auto logs timeline)
- Admin student management: search, view, delete
- 🤖 **AI Chat Assistant**: rule-based keyword matching chatbot that:
  - Asks the student to describe their issue in plain English
  - Detects category (Electrical / Plumbing / Cleanliness / Internet / Mess / Furniture / Security / Noise)
  - Detects urgency/priority from risk keywords (e.g. "spark", "leak", "theft")
  - Auto-fills the complaint form for the student to review and submit

## Tech Stack

- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 8 (PDO + prepared statements — SQL-injection safe)
- **Database:** MySQL / MariaDB
- **AI Feature:** Rule-based keyword-matching chatbot (pure JS, no external API/key needed)

## Folder Structure

```
hostel-complaint-system/
├── admin/                 # Admin pages (login, dashboard, manage complaints/students)
├── student/                # Student pages (register, login, dashboard, complaints)
├── config/db.php           # Database connection
├── database/schema.sql     # Full DB schema
├── includes/                # Shared header/footer/functions/chatbot widget
├── assets/css/style.css     # All styling
├── assets/js/validation.js  # Client-side form validation
├── assets/js/chatbot.js     # AI chatbot logic
└── index.php                # Landing page
```

## Setup Instructions (XAMPP / WAMP / MAMP)

1. **Copy the project folder** into your server's web root:
   - XAMPP: `C:/xampp/htdocs/hostel-complaint-system`
   - WAMP: `C:/wamp64/www/hostel-complaint-system`
   - MAMP: `/Applications/MAMP/htdocs/hostel-complaint-system`

2. **Start Apache and MySQL** from your XAMPP/WAMP/MAMP control panel.

3. **Create the database:**
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
   - Click **Import**, choose `database/schema.sql`, and run it.
   - This creates the `hostel_complaint_system` database with all tables.

4. **Check DB credentials** in `config/db.php` — defaults match a standard XAMPP setup
   (`host: localhost`, `user: root`, `password: ''`). Update if your MySQL user/password differ.

5. **Create the admin account (one-time):**
   - Visit `http://localhost/hostel-complaint-system/admin/create_admin.php`
   - Fill in a username, name, and password to create your admin login.
   - **Delete `admin/create_admin.php` after this step** (or restrict it) since it's a setup-only script.

6. **Open the app:**
   - `http://localhost/hostel-complaint-system/`
   - Register as a student, or log in as admin with the account you just created.

## Using the AI Chatbot

1. Log in as a student and go to **New Complaint**.
2. Click the 💬 chat bubble (bottom-right corner).
3. Describe your issue in plain words, e.g. *"the tap in my bathroom is leaking badly"*.
4. The assistant detects category (**Plumbing**) and priority (**Medium**), asks for your
   room number, then auto-fills the complaint form. Review and hit **Submit**.

## Validation Rules (client + server)

| Field | Rule |
|---|---|
| Full Name | 3–50 letters |
| Roll Number | 3–30 alphanumeric chars (+ `-` `/`) |
| Email | valid email format, unique |
| Phone | 10 digits, starts with 6–9 |
| Password | 6+ chars, 1 uppercase, 1 digit |
| Complaint Subject | 5+ chars |
| Complaint Description | 15+ chars |
| Room Number | required, alphanumeric |

## Notes / Possible Extensions

- Add email/SMS notifications on status change
- Add file/photo upload for complaint evidence
- Add pagination for large complaint lists
- Swap the rule-based chatbot for a real LLM API call if your lab allows external API keys
