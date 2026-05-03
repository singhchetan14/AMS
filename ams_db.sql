-- =====================================================================
-- ams_db.sql  —  Final clean schema for the Academic Management System
-- =====================================================================
-- Run this once in phpMyAdmin (or `mysql -u root < ams_db.sql`) to set up
-- a working database from scratch. It will:
--   1. Drop and recreate database `ams_db`
--   2. Create all required tables with the right columns/keys
--   3. Insert one default admin, one default teacher, one default student
--
-- Default credentials (CHANGE AFTER FIRST LOGIN):
--   Admin    -> admin@ams.com    / admin123
--   Teacher  -> teacher@ams.com  / teacher123
--   Student  -> student@ams.com  / student123
-- =====================================================================

DROP DATABASE IF EXISTS `ams_db`;
CREATE DATABASE `ams_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ams_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ---------------------------------------------------------------------
-- users  —  one row per admin / teacher / student (single auth source)
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(150) NOT NULL,
  `password`    VARCHAR(255) DEFAULT NULL,
  `role`        ENUM('student','teacher','admin') NOT NULL DEFAULT 'student',
  `full_name`   VARCHAR(100) DEFAULT NULL,
  `student_no`  VARCHAR(20)  DEFAULT NULL,
  `department`  VARCHAR(100) DEFAULT NULL,
  `phone`       VARCHAR(20)  DEFAULT NULL,
  `photo`       VARCHAR(255) DEFAULT NULL,
  `group_name`  VARCHAR(10)  DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- courses  —  teacher_id is NULL until admin assigns the course
-- ---------------------------------------------------------------------
CREATE TABLE `courses` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100) NOT NULL,
  `teacher_id`    INT(11)      DEFAULT NULL,
  `group_name`    VARCHAR(10)  DEFAULT NULL,
  `schedule_time` TIME         DEFAULT NULL,
  `schedule_day`  VARCHAR(20)  DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_teacher_id` (`teacher_id`),
  KEY `idx_group_name` (`group_name`),
  CONSTRAINT `fk_courses_teacher`
    FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- students  —  extra per-student fields used by the teacher dashboard
-- (separate from `users` so a student account can exist without these)
-- ---------------------------------------------------------------------
CREATE TABLE `students` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `student_no` VARCHAR(20)  NOT NULL,
  `full_name`  VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) DEFAULT NULL,
  `group_name` VARCHAR(10)  DEFAULT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_no` (`student_no`),
  KEY `idx_group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- grades
-- ---------------------------------------------------------------------
CREATE TABLE `grades` (
  `id`         INT(11)   NOT NULL AUTO_INCREMENT,
  `student_id` INT(11)   NOT NULL,
  `course_id`  INT(11)   NOT NULL,
  `score`      INT(11)   NOT NULL CHECK (`score` >= 0 AND `score` <= 100),
  `status`     ENUM('Pass','Fail') NOT NULL,
  `teacher_id` INT(11)   NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grade` (`student_id`,`course_id`),
  KEY `idx_course_id`  (`course_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  CONSTRAINT `fk_grades_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grades_course`
    FOREIGN KEY (`course_id`)  REFERENCES `courses`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grades_teacher`
    FOREIGN KEY (`teacher_id`) REFERENCES `users`    (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- materials  —  files uploaded by teachers per course
-- ---------------------------------------------------------------------
CREATE TABLE `materials` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255) NOT NULL,
  `filename`    VARCHAR(255) NOT NULL,
  `course_id`   INT(11)      NOT NULL,
  `teacher_id`  INT(11)      NOT NULL,
  `uploaded_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_course_id`  (`course_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  CONSTRAINT `fk_materials_course`
    FOREIGN KEY (`course_id`)  REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_materials_teacher`
    FOREIGN KEY (`teacher_id`) REFERENCES `users`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- password_resets  —  used by auth/forgot-password flow (OTP codes)
-- ---------------------------------------------------------------------
CREATE TABLE `password_resets` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(150) NOT NULL,
  `code`       VARCHAR(6)   NOT NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DEFAULT USERS  (passwords are bcrypt hashes — DO NOT edit by hand)
-- =====================================================================
-- admin@ams.com   / admin123
-- teacher@ams.com / teacher123
-- student@ams.com / student123
-- =====================================================================
INSERT INTO `users` (`email`, `password`, `role`, `full_name`, `department`) VALUES
('admin@ams.com',
 '$2y$10$3Q7dRz627m4bfJQCg8O4CuxII///lnEPvt59LwXaSFDifMlorCkL2',
 'admin',
 'Default Admin',
 NULL),
('teacher@ams.com',
 '$2y$10$pAARPr/Y5oqNQ9usYbjCh.pSylX1uxGnqHvhXrYu/YpVuLd/ReRnq',
 'teacher',
 'Default Teacher',
 'Computer Science'),
('student@ams.com',
 '$2y$10$R6RnU3qLr77Uu6.z3lix1.9jmMRcJjKye0hDj5XysP4gri7OKA7/W',
 'student',
 'Default Student',
 NULL);

-- Mirror the default student into the `students` table so the teacher
-- dashboard's "View Students" / grade entry has at least one row to show.
INSERT INTO `students` (`student_no`, `full_name`, `email`, `group_name`) VALUES
('S0001', 'Default Student', 'student@ams.com', 'A');
