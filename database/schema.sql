-- MentorBridge Schema v2.0 (Simplified Flow)

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('student','mentor','admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_profiles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL UNIQUE,
    department  VARCHAR(100),
    roll_number VARCHAR(40),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE mentor_profiles (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL UNIQUE,
    specialization VARCHAR(150),
    department     VARCHAR(100),
    quote          TEXT,
    max_students   INT DEFAULT 20,
    photo_url      VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE semester_config (
    id                INT PRIMARY KEY DEFAULT 1,
    semester_name     VARCHAR(60) DEFAULT 'Semester 2 - 2025-26',
    goals_deadline    DATE,
    election_deadline DATE,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE student_goals (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL UNIQUE,
    academic_goals  TEXT,
    challenges      TEXT,
    expectations    TEXT,
    vision          TEXT,
    skills_develop  TEXT,
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE mentor_current (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL UNIQUE,
    mentor_id  INT NOT NULL,
    locked_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id)  REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sessions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    mentor_id    INT NOT NULL,
    title        VARCHAR(200),
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    status       ENUM('upcoming','done','cancelled') DEFAULT 'upcoming',
    notes        TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id)  REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE feedback (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    mentor_id    INT NOT NULL,
    session_id   INT DEFAULT NULL,
    rating       ENUM('up','down') NOT NULL,
    comment      TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL
);

INSERT INTO semester_config (id,semester_name,goals_deadline,election_deadline)
VALUES (1,'Semester 2 - 2025-26','2026-08-31','2026-09-15');

-- Meet requests: student requests a brief interaction with a mentor before electing
CREATE TABLE meet_requests (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    mentor_id    INT NOT NULL,
    message      TEXT,
    status       ENUM('pending','accepted','declined') DEFAULT 'pending',
    mentor_reply TEXT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_meet (student_id, mentor_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id)  REFERENCES users(id) ON DELETE CASCADE
);
