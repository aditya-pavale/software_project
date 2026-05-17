# MentorBridge v2 — myAmrita Style

## Design: myAmrita Portal
- Crimson/maroon (#9b1c3c) header matching myAmrita
- Campus sketch watermark background on login
- Tile-grid navigation like the Student Portal
- "my AMRITA" logo treatment on login page

## All Bugs Fixed in v2
- ✅ interview.php now opens as a listing page + per-mentor interview
- ✅ feedback.php works properly (gated by completion)
- ✅ feedback_history.php — NEW: students see all feedback they gave
- ✅ my_mentor.php — students can book MULTIPLE sessions (not limited to 1)
- ✅ election.php — election deadline enforced from semester_config table
- ✅ mentor_sessions.php — FIXED: properly shows all sessions for mentor
- ✅ mentor_students.php — FIXED: shows elected students with cards
- ✅ admin.php — users grouped by department section; semester config panel added

## Setup
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```
Edit `includes/db.php` → set DB_USER and DB_PASS.
Open: `http://localhost/mentorbridge-v2/public/login.php`

## Demo Accounts (password: password123)
| Role    | Email                  |
|---------|------------------------|
| Student | aditya@amrita.edu      |
| Mentor  | priya@amrita.edu       |
| Admin   | admin@amrita.edu       |

## Flow
```
public/*.php → form POST → actions/*.php → DB → redirect with flash
```
## Student State Machine
INIT → INTERVIEWING → ALL_COMPLETED → SELECTION_UNLOCKED → SELECTED
