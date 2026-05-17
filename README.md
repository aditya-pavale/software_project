# MentorBridge · myAmrita
### Mentorship & Counselling Portal — Amrita Vishwa Vidyapeetham, Bengaluru

> A production-grade web portal connecting students with faculty mentors for academic counselling, session management, and progress tracking.

---

## Live Deployment

**URL:** https://mentornest.xo.je

---

## Overview

MentorBridge is a full-stack PHP/MySQL web application built for the Computer Science Engineering department at Amrita Vishwa Vidyapeetham, Bengaluru Campus. It digitises the entire mentor-student counselling workflow — from goal submission and mentor discovery to session management and feedback collection.

---

## Key Features

### Student
- Submit academic goals, challenges, and expectations (once, shared with elected mentor)
- Browse all 46 faculty mentors with photos, designations, and capacity
- Request a Meet — request a brief interaction with any mentor before committing
- Elect one mentor permanently after reviewing profiles
- Book counselling sessions with elected mentor
- Submit feedback after sessions are marked complete by mentor
- Password change via Settings

### Mentor
- View assigned students and their submitted goals
- Manage and respond to student meet requests
- Mark sessions as completed with notes
- View student ratings and feedback
- Password change via Settings

### Admin
- Dashboard with live stats: total students, mentors, elections, goal coverage
- Search and filter all 63 students by name, roll number, or state
- Reset any user's password
- Unmap student-mentor assignments
- Configure semester name, goals deadline, and election deadline

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8.3 (no framework)            |
| Database   | MySQL 5.7+                        |
| Frontend   | HTML5, CSS3, Vanilla JavaScript   |
| Fonts      | Google Fonts (Libre Baskerville, Source Sans 3) |
| Hosting    | InfinityFree (shared PHP hosting) |

---

## Project Structure

```
mb6/
├── actions/          # Form handlers (login, elect, goals, sessions, feedback, meet)
├── database/
│   ├── schema.sql    # All table definitions
│   └── seed.sql      # 46 real faculty + 63 CSE-B students (password: amma)
├── includes/
│   ├── auth.php      # Session management, role guards, security
│   ├── config.php    # Database credentials (edit before deploy)
│   ├── db.php        # PDO connection with BASE_URL detection
│   └── helpers.php   # All shared functions
├── public/           # All user-facing pages
│   ├── assets/css/   # style.css — full responsive CSS
│   ├── login.php
│   ├── dashboard.php
│   ├── goals.php
│   ├── mentors.php
│   ├── my_mentor.php
│   ├── mentor_students.php
│   ├── mentor_sessions.php
│   ├── meet_requests.php
│   ├── admin.php
│   └── ...
├── index.php         # Root redirect
└── .htaccess         # Security + InfinityFree compatibility
```

---

## Database Schema

| Table            | Purpose                                      |
|------------------|----------------------------------------------|
| `users`          | All accounts (student, mentor, admin)        |
| `student_profiles` | Roll number, department                    |
| `mentor_profiles`  | Designation, quota, photo URL              |
| `student_goals`  | One goals record per student (5 questions)   |
| `mentor_current` | Student-mentor election mapping (permanent)  |
| `sessions`       | Booked counselling sessions                  |
| `feedback`       | Session feedback (gated by completion)       |
| `meet_requests`  | Pre-election meet requests between students and mentors |
| `semester_config`| Deadline and semester settings (admin-controlled) |

---

## Security

- bcrypt password hashing on all accounts
- Session regeneration on login (prevents session fixation)
- Role-based access control enforced on every page via `guard()`
- HTTP-only session cookies
- 4-hour session timeout on inactivity
- BASE_URL-aware redirects (works in any subfolder)
- `display_errors` disabled in production via `.htaccess`
- No SQL injection — all queries use PDO prepared statements

---

## Setup (Local)

```bash
# 1. Import database
mysql -u root -p mentorbridge < database/schema.sql
mysql -u root -p mentorbridge < database/seed.sql

# 2. Configure credentials
# Edit includes/config.php — set DB_HOST, DB_USER, DB_PASS, DB_NAME

# 3. Open in browser
http://localhost/mb6/public/login.php
```

---

## Deploy on InfinityFree

1. Upload all files to `htdocs/`
2. Edit `includes/config.php` with InfinityFree MySQL credentials
3. In phpMyAdmin → import `schema.sql` then `seed.sql`
4. Visit your domain — it redirects to login automatically

---

## Demo Accounts

All accounts use password: **`amma`**

| Role    | Email                                          |
|---------|------------------------------------------------|
| Admin   | admin@amrita.edu                               |
| Mentor  | sreevidya.b@amrita.edu                         |
| Mentor  | amudha.j@amrita.edu                            |
| Student | bl.en.u4cse23101@bl.students.amrita.edu        |
| Student | bl.en.u4cse23102@bl.students.amrita.edu        |

---

## Student Flow

```
Register/Login → Submit Goals (once) → Browse Mentors
→ Request a Meet (optional) → Elect Mentor (permanent)
→ Book Sessions → Receive Feedback Unlock → Submit Feedback
```

---

## Built By

**Aditya Sanjay Pavale** — BL.EN.U4CSE23101  
Computer Science Engineering, Amrita Vishwa Vidyapeetham, Bengaluru  

---

*This project is for academic and institutional use within Amrita Vishwa Vidyapeetham.*
