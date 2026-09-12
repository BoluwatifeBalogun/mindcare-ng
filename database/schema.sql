-- MindCare NG — MySQL schema (import via phpMyAdmin or: mysql -u root < schema.sql)
CREATE DATABASE IF NOT EXISTS mindcare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mindcare;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,      -- matric number for students
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','counsellor','admin') NOT NULL DEFAULT 'student',
  dept VARCHAR(120) DEFAULT NULL,
  level VARCHAR(20) DEFAULT NULL,
  title VARCHAR(120) DEFAULT NULL,           -- counsellor title/focus
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE chat_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  role ENUM('user','assistant','counsellor') NOT NULL,
  from_name VARCHAR(120) DEFAULT NULL,       -- set for counsellor notes
  text TEXT NOT NULL,
  risk ENUM('low','moderate','high') DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_student_time (student_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE mood_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  entry_date DATE NOT NULL,
  value TINYINT NOT NULL,                    -- 1..5
  note VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uniq_day (student_id, entry_date),
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assessments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  tool ENUM('PHQ-9','GAD-7') NOT NULL,
  score TINYINT NOT NULL,
  band VARCHAR(30) NOT NULL,
  item9 TINYINT NOT NULL DEFAULT 0,          -- PHQ-9 item 9 raw answer
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  counsellor_id INT DEFAULT NULL,
  when_text VARCHAR(80) NOT NULL,
  status ENUM('pending','confirmed','done','cancelled') NOT NULL DEFAULT 'pending',
  reason VARCHAR(255) DEFAULT NULL,
  is_follow_up TINYINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (counsellor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE referrals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  source VARCHAR(60) NOT NULL,
  risk ENUM('moderate','high') NOT NULL,
  summary VARCHAR(500) NOT NULL,
  context_json TEXT DEFAULT NULL,            -- last messages excerpt
  status ENUM('open','taken') NOT NULL DEFAULT 'open',
  taken_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (taken_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
  name VARCHAR(40) PRIMARY KEY,
  value VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Seeds. Staff default password is: changeme123  (change after first login)
INSERT INTO users (name, username, password_hash, role, dept, title) VALUES
('Registrar\'s Office', 'admin', '$2y$10$kR804S2gYJWk2fMwMKKH2e8JY97lmLjpmLqGqfTk0m/oCX8juLJXy', 'admin', 'ICT Directorate', NULL),
('Mrs. Adebayo Funke', 'adebayo.f', '$2y$10$kR804S2gYJWk2fMwMKKH2e8JY97lmLjpmLqGqfTk0m/oCX8juLJXy', 'counsellor', 'Counselling Unit', 'Senior Counsellor · Anxiety & academic stress'),
('Mr. Chukwu Daniel', 'chukwu.d', '$2y$10$kR804S2gYJWk2fMwMKKH2e8JY97lmLjpmLqGqfTk0m/oCX8juLJXy', 'counsellor', 'Counselling Unit', 'Counselling Psychologist · Depression & grief');

INSERT INTO settings (name, value) VALUES
('crisis_line', '112'),
('centre', 'Campus Counselling Centre, Student Affairs Block, 8am to 4pm'),
('follow_up_hours', '48');
