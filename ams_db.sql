-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 28, 2026 at 08:17 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ams_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `group_name` varchar(10) DEFAULT NULL COMMENT 'e.g., A, B, C',
  `schedule_time` time DEFAULT NULL COMMENT 'e.g., 09:00:00',
  `schedule_day` varchar(20) DEFAULT NULL COMMENT 'e.g., Monday',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `teacher_id`, `group_name`, `schedule_time`, `schedule_day`, `created_at`) VALUES
(4, 'Ethical Hacking', 1, 'A', '09:00:00', 'Sunday', '2026-04-12 08:46:45'),
(5, 'Fullstack Development', 1, 'B', '13:30:00', 'Sunday', '2026-04-12 08:46:45'),
(6, 'Collaboration Development', 1, 'C', '16:00:00', 'Sunday', '2026-04-12 08:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `score` int(11) NOT NULL CHECK (`score` >= 0 and `score` <= 100),
  `status` enum('Pass','Fail') NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `course_id`, `score`, `status`, `teacher_id`, `created_at`, `updated_at`) VALUES
(11, 13, 6, 86, 'Pass', 1, '2026-04-12 13:37:01', '2026-04-12 13:37:01'),
(12, 16, 6, 39, 'Fail', 1, '2026-04-12 13:37:14', '2026-04-12 13:37:14'),
(13, 19, 6, 50, 'Pass', 1, '2026-04-12 13:37:22', '2026-04-12 13:38:01'),
(16, 11, 4, 26, 'Fail', 1, '2026-04-12 15:10:55', '2026-04-12 15:10:55'),
(17, 14, 4, 79, 'Pass', 1, '2026-04-12 15:11:11', '2026-04-12 15:11:11'),
(18, 17, 4, 59, 'Pass', 1, '2026-04-12 15:11:12', '2026-04-12 15:11:12'),
(19, 20, 4, 55, 'Pass', 1, '2026-04-12 15:11:14', '2026-04-12 15:11:14'),
(20, 12, 5, 56, 'Pass', 1, '2026-04-12 15:11:37', '2026-04-12 15:11:37'),
(21, 15, 5, 33, 'Fail', 1, '2026-04-12 15:11:38', '2026-04-12 15:11:38'),
(22, 18, 5, 55, 'Pass', 1, '2026-04-12 15:11:39', '2026-04-12 15:11:39');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `title`, `filename`, `course_id`, `teacher_id`, `uploaded_at`) VALUES
(9, 'Collaboration Basics', 'collaboration_basics.pdf', 6, 1, '2026-04-12 08:47:46'),
(10, 'Lesson1', 'mat_69dba0e5814c4_7407e1a8.txt', 4, 1, '2026-04-12 13:40:53'),
(11, 'Lesson1', 'mat_69dba29e8996d_a3f1ed97.txt', 6, 1, '2026-04-12 13:48:14'),
(12, 'Lesson1', 'mat_69dba49a09920_37489448.txt', 5, 1, '2026-04-12 13:56:42');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_no` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `group_name` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_no`, `full_name`, `email`, `group_name`, `created_at`) VALUES
(11, '101', 'Alice Morgan', 'alice@gmail.com', 'A', '2026-04-12 08:46:45'),
(12, '102', 'Bob Kimura', 'bob@gmail.com', 'B', '2026-04-12 08:46:45'),
(13, '103', 'Clara Johansson', 'clara@gmail.com', 'C', '2026-04-12 08:46:45'),
(14, '104', 'David Osei', 'david@gmail.com', 'A', '2026-04-12 08:46:45'),
(15, '105', 'Eva Rossi', 'eva@gmail.com', 'B', '2026-04-12 08:46:45'),
(16, '106', 'Felix Andrade', 'felix@gmail.com', 'C', '2026-04-12 08:46:45'),
(17, '107', 'Grace Liu', 'grace@gmail.com', 'A', '2026-04-12 08:46:45'),
(18, '108', 'Hiro Tanaka', 'hiro@gmail.com', 'B', '2026-04-12 08:46:45'),
(19, '109', 'Irene Park', 'irene@gmail.com', 'C', '2026-04-12 08:46:45'),
(20, '110', 'James Okoro', 'james@gmail.com', 'A', '2026-04-12 08:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hashed password',
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL COMMENT 'stored filename in uploads/photos/',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `full_name`, `email`, `password`, `department`, `phone`, `photo`, `created_at`) VALUES
(1, 'Sarah Johnson', 'teacher@ams.com', '$2y$10$DraML3xWatFqnM9nOEib1eW8j2.5L7xD6ULKBB0xABiYXKTOZ7yV2', 'Computer Science', '+1 (555) 123-4567', NULL, '2026-04-11 15:35:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_group_name` (`group_name`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`course_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_teacher_id` (`teacher_id`),
  ADD KEY `idx_uploaded_at` (`uploaded_at`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`),
  ADD KEY `idx_student_no` (`student_no`),
  ADD KEY `idx_group_name` (`group_name`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
