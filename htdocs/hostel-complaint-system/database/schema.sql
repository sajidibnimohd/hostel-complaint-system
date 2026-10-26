-- ============================================================
-- Hostel Complaint Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS hostel_complaint_system;
USE hostel_complaint_system;

-- ------------------------------------------------------------
-- Table: students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    student_id     INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    roll_no        VARCHAR(30) NOT NULL UNIQUE,
    email          VARCHAR(100) NOT NULL UNIQUE,
    phone          VARCHAR(15) NOT NULL,
    hostel_block   VARCHAR(20) NOT NULL,
    room_no        VARCHAR(10) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: admin
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    admin_id       INT AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(50) NOT NULL UNIQUE,
    full_name      VARCHAR(100) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: complaints
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id   INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT NOT NULL,
    category       ENUM('Electrical','Plumbing','Cleanliness','Internet/WiFi',
                         'Mess/Food','Furniture','Security','Noise','Other') NOT NULL,
    subject        VARCHAR(150) NOT NULL,
    description    TEXT NOT NULL,
    priority       ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low',
    status         ENUM('Pending','In Progress','Resolved','Rejected') NOT NULL DEFAULT 'Pending',
    admin_remarks  TEXT NULL,
    hostel_block   VARCHAR(20) NOT NULL,
    room_no        VARCHAR(10) NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: complaint_logs (status change history / timeline)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS complaint_logs (
    log_id         INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id   INT NOT NULL,
    status         VARCHAR(30) NOT NULL,
    remarks        TEXT NULL,
    changed_by     VARCHAR(50) NOT NULL DEFAULT 'System',
    changed_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seed data: default admin (username: admin / password: Admin@123)
-- Password hash below corresponds to "Admin@123" (bcrypt)
-- ------------------------------------------------------------
INSERT INTO admin (username, full_name, password)
VALUES ('admin', 'Hostel Administrator', '$2y$10$92IXUNpkjO0rOQ5byMi.YeIeQ5.5gs.0z5v3iX3z1r5c0y0f5s2Ke')
ON DUPLICATE KEY UPDATE username = username;

-- NOTE: The hash above is a placeholder. Because password_hash() output differs
-- per run, it is safer to create the admin account by visiting
-- admin/create_admin.php once after setup (see README.md) rather than relying
-- on this seed row. Delete this row if the login does not work and use the
-- create_admin.php script instead.
