-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 03, 2026 at 12:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartgrade_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `target_table` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_id`, `user_id`, `action`, `target_table`, `target_id`, `created_at`) VALUES
(26, 1, 'GENERATED_SF9', 'documents', 26, '2025-12-29 06:45:07'),
(27, 1, 'GENERATED_SF9', 'documents', 27, '2025-12-29 06:45:15'),
(28, 1, 'GENERATED_SF9', 'documents', 28, '2025-12-29 06:45:23'),
(29, 2, 'GENERATED_SF9', 'documents', 29, '2025-12-29 06:48:32'),
(30, 2, 'GENERATED_SF9', 'documents', 30, '2025-12-29 06:48:40'),
(31, 2, 'GENERATED_SF9', 'documents', 31, '2025-12-29 07:57:23'),
(32, 2, 'GENERATED_SF9', 'documents', 32, '2025-12-29 09:21:47'),
(33, 2, 'GENERATED_SF9', 'documents', 33, '2025-12-29 09:21:53'),
(34, 1, 'GENERATED_SF9', 'documents', 34, '2025-12-29 09:23:14'),
(35, 1, 'GENERATED_SF9', 'documents', 35, '2025-12-29 09:23:17'),
(36, 1, 'GENERATED_SF9', 'documents', 36, '2025-12-29 09:24:05'),
(37, 2, 'GENERATED_SF9', 'documents', 37, '2025-12-29 09:24:19'),
(38, 1, 'GENERATED_SF9', 'documents', 38, '2025-12-29 09:25:01'),
(39, 2, 'GENERATED_SF9', 'documents', 44, '2025-12-29 10:10:36'),
(40, 2, 'GENERATED_SF9', 'documents', 45, '2025-12-29 10:10:39'),
(41, 5, 'GENERATED_SF9', 'documents', 46, '2026-01-03 07:25:19'),
(42, 5, 'GENERATED_SF9', 'documents', 47, '2026-01-03 07:26:39'),
(43, 5, 'GENERATED_SF9', 'documents', 48, '2026-01-03 07:26:55'),
(44, 5, 'GENERATED_SF9', 'documents', 49, '2026-01-03 07:27:14'),
(45, 5, 'GENERATED_SF9', 'documents', 50, '2026-01-03 07:27:19'),
(46, 5, 'GENERATED_SF9', 'documents', 51, '2026-01-03 07:27:30'),
(47, 5, 'GENERATED_SF9', 'documents', 52, '2026-01-03 07:27:38');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `document_type` enum('SF9','SF10','CERTIFICATE') DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_by_user_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `student_id`, `document_type`, `version`, `file_path`, `generated_by_user_id`, `is_active`, `created_at`) VALUES
(34, 2, 'SF9', 3, 'storage/documents/school_1/students/2/sf9/sf9_v3.pdf', 1, 1, '2025-12-29 09:23:14'),
(35, 2, 'SF9', 4, 'storage/documents/school_1/students/2/sf9/sf9_v4.pdf', 1, 1, '2025-12-29 09:23:17'),
(36, 1, 'SF9', 24, 'storage/documents/school_1/students/1/sf9/sf9_v24.pdf', 1, 1, '2025-12-29 09:24:05'),
(37, 1, 'SF9', 25, 'storage/documents/school_1/students/1/sf9/sf9_v25.pdf', 2, 1, '2025-12-29 09:24:19'),
(38, 1, 'SF9', 26, 'storage/documents/school_1/students/1/sf9/sf9_v26.pdf', 1, 1, '2025-12-29 09:25:01'),
(39, 2, 'CERTIFICATE', 1, 'storage/documents/school_1/students/2/certificate/certificate_v1.pdf', 1, 1, '2025-12-29 09:51:05'),
(40, 2, 'CERTIFICATE', 2, 'storage/documents/school_1/students/2/certificate/certificate_v2.pdf', 1, 1, '2025-12-29 09:51:08'),
(41, 2, 'CERTIFICATE', 3, 'storage/documents/school_1/students/2/certificate/certificate_v3.pdf', 1, 1, '2025-12-29 09:51:11'),
(42, 2, 'CERTIFICATE', 4, 'storage/documents/school_1/students/2/certificate/certificate_v4.pdf', 1, 1, '2025-12-29 09:51:12'),
(43, 1, 'CERTIFICATE', 1, 'storage/documents/school_1/students/1/certificate/certificate_v1.pdf', 1, 1, '2025-12-29 09:51:26'),
(44, 1, 'SF9', 27, 'storage/documents/school_1/students/1/sf9/sf9_v27.pdf', 2, 1, '2025-12-29 10:10:36'),
(45, 1, 'SF9', 28, 'storage/documents/school_1/students/1/sf9/sf9_v28.pdf', 2, 1, '2025-12-29 10:10:39'),
(46, 2, 'SF9', 5, 'storage/documents/school_1/students/2/sf9/sf9_v5.pdf', 5, 1, '2026-01-03 07:25:19'),
(47, 2, 'SF9', 6, 'storage/documents/school_1/students/2/sf9/sf9_v6.pdf', 5, 1, '2026-01-03 07:26:39'),
(48, 2, 'SF9', 7, 'storage/documents/school_1/students/2/sf9/sf9_v7.pdf', 5, 1, '2026-01-03 07:26:55'),
(49, 2, 'SF9', 8, 'storage/documents/school_1/students/2/sf9/sf9_v8.pdf', 5, 1, '2026-01-03 07:27:14'),
(50, 2, 'SF9', 9, 'storage/documents/school_1/students/2/sf9/sf9_v9.pdf', 5, 1, '2026-01-03 07:27:19'),
(51, 2, 'SF9', 10, 'storage/documents/school_1/students/2/sf9/sf9_v10.pdf', 5, 1, '2026-01-03 07:27:30'),
(52, 2, 'SF9', 11, 'storage/documents/school_1/students/2/sf9/sf9_v11.pdf', 5, 1, '2026-01-03 07:27:38'),
(53, 2, 'CERTIFICATE', 5, 'storage/documents/school_1/students/2/certificate/certificate_v5.pdf', 5, 1, '2026-01-03 10:20:19'),
(54, 1, 'CERTIFICATE', 2, 'storage/documents/school_1/students/1/certificate/certificate_v2.pdf', 5, 1, '2026-01-03 10:20:25');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_subject_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `attempt_no` int(11) DEFAULT 1,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `section_subject_id`, `semester_id`, `attempt_no`, `enrolled_at`) VALUES
(1, 1, 1, 1, 1, '2025-12-28 02:43:43'),
(2, 1, 2, 1, 1, '2025-12-28 02:43:43'),
(3, 1, 3, 1, 1, '2025-12-28 06:23:23'),
(5, 2, 3, 1, 1, '2025-12-28 06:41:13'),
(6, 2, 4, 1, 1, '2025-12-28 06:41:13'),
(9, 3, 3, 1, 1, '2025-12-28 06:41:13'),
(10, 3, 4, 1, 1, '2025-12-28 06:41:13'),
(13, 5, 3, 1, 1, '2025-12-28 06:41:13'),
(14, 5, 4, 1, 1, '2025-12-28 06:41:13'),
(23, 1, 5, 1, 1, '2026-01-03 10:54:09'),
(24, 1, 6, 1, 1, '2026-01-03 10:54:09'),
(27, 2, 5, 1, 1, '2026-01-03 10:54:34'),
(28, 2, 6, 1, 1, '2026-01-03 10:54:34');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `is_final` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `enrollment_id`, `final_grade`, `is_final`, `created_at`) VALUES
(29, 28, 95.00, 1, '2026-01-03 11:28:34'),
(30, 24, 96.00, 1, '2026-01-03 11:28:34');

-- --------------------------------------------------------

--
-- Table structure for table `honors`
--

CREATE TABLE `honors` (
  `honor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `honor_type` varchar(100) DEFAULT NULL,
  `gwa` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `honors`
--

INSERT INTO `honors` (`honor_id`, `student_id`, `semester_id`, `honor_type`, `gwa`) VALUES
(1, 1, 1, 'WITH HONORS', 89.75);

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` int(11) NOT NULL,
  `school_code` varchar(50) NOT NULL,
  `school_name` varchar(255) NOT NULL,
  `school_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `principal_name` varchar(255) DEFAULT NULL,
  `superintendent_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `school_code`, `school_name`, `school_email`, `address`, `district`, `division`, `region`, `logo_path`, `principal_name`, `superintendent_name`, `is_active`, `created_at`) VALUES
(1, '317511', 'Ampayon National High School', '317511@deped.gov.ph', 'Purok 3B, Ampayon, Butuan City, Agusan del Norte', 'East Butuan District I', 'Schools Division of Agusan del Norte', 'CARAGA Region', NULL, 'SOLEDAD M. RUBILLOS', 'Schools Division Superintendent – Agusan del Norte', 1, '2025-12-28 02:43:42');

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `school_year_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `year_label` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`school_year_id`, `school_id`, `year_label`, `is_active`) VALUES
(1, 1, '2024-2025', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_by_teacher_id` int(11) NOT NULL,
  `adviser_teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `school_year_id`, `name`, `created_by_teacher_id`, `adviser_teacher_id`) VALUES
(1, 1, 'Grade 12 - Einstein', 1, 1),
(3, 1, 'G-12 Newton', 2, 2),
(4, 1, 'G-11 Mabini', 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `section_subjects`
--

CREATE TABLE `section_subjects` (
  `section_subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `section_subjects`
--

INSERT INTO `section_subjects` (`section_subject_id`, `section_id`, `subject_id`, `teacher_id`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 4, 1, 1),
(4, 4, 2, 1),
(5, 3, 2, 1),
(6, 3, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `semester_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`semester_id`, `school_year_id`, `name`, `sort_order`) VALUES
(1, 1, '1st Semester', 1),
(2, 1, '2nd Semester', 2);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lrn` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','graduated','archived','dropped') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `lrn`, `first_name`, `last_name`, `birthdate`, `address`, `status`) VALUES
(1, 3, '123456789012', 'KIRSTEIN GENZEN', 'NOJAPA', '2007-06-15', 'Butuan City', 'active'),
(2, 4, '123456789122', 'Christian James', 'Bantillo', '2003-05-07', '', 'active'),
(3, 4, '100000000001', 'Juan', 'Dela Cruz', '2008-03-15', 'Butuan City', 'active'),
(4, 5, '100000000002', 'Maria Clara', 'Santos', '2008-05-20', 'Butuan City', 'active'),
(5, 6, '100000000003', 'Jose', 'Rivera', '2008-01-12', 'Butuan City', 'active'),
(6, 7, '100000000004', 'Sofia', 'Mendoza', '2008-07-08', 'Butuan City', 'active'),
(7, 8, '100000000005', 'Pedro', 'Reyes', '2008-09-30', 'Butuan City', 'active'),
(8, 9, '100000000006', 'Lucia', 'Garcia', '2008-11-22', 'Butuan City', 'active'),
(9, 10, '100000000007', 'Antonio', 'Fernandez', '2008-02-18', 'Butuan City', 'active'),
(10, 11, '100000000008', 'Isabella', 'Torres', '2008-04-05', 'Butuan City', 'active'),
(11, 12, '100000000009', 'Miguel', 'Villa', '2008-06-14', 'Butuan City', 'active'),
(12, 13, '100000000010', 'Camila', 'Cruz', '2008-08-27', 'Butuan City', 'active'),
(13, 14, '100000000011', 'Daniel', 'Hernandez', '2008-10-03', 'Butuan City', 'active'),
(14, 15, '100000000012', 'Valentina', 'Ramirez', '2008-12-11', 'Butuan City', 'active'),
(15, 16, '100000000013', 'Santiago', 'Diaz', '2008-03-25', 'Butuan City', 'active'),
(16, 17, '100000000014', 'Emma', 'Flores', '2008-05-17', 'Butuan City', 'active'),
(17, 18, '100000000015', 'Mateo', 'Castro', '2008-07-29', 'Butuan City', 'active'),
(18, 19, '100000000016', 'Olivia', 'Gomez', '2008-09-04', 'Butuan City', 'active'),
(19, 20, '100000000017', 'Liam', 'Rodriguez', '2008-11-16', 'Butuan City', 'active'),
(20, 21, '100000000018', 'Ava', 'Martinez', '2008-01-09', 'Butuan City', 'active'),
(21, 22, '100000000019', 'Noah', 'Lopez', '2008-02-21', 'Butuan City', 'active'),
(22, 23, '100000000020', 'Sophia', 'Perez', '2008-04-13', 'Butuan City', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `subject_code` varchar(50) DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `units` decimal(5,2) DEFAULT NULL,
  `subject_type` enum('core','applied','specialized') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `school_id`, `subject_code`, `subject_name`, `units`, `subject_type`) VALUES
(1, 1, 'ENG12', 'English for Academic and Professional Purposes', 3.00, 'core'),
(2, 1, 'MATH12', 'General Mathematics', 3.00, 'core');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `employee_no` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `first_name`, `last_name`, `employee_no`, `is_active`) VALUES
(1, 2, 'ELMA JOY', 'JOPIA', 'T-2024-001', 1),
(2, 5, 'Juan', 'Dela Cruz', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ADMIN','TEACHER','STUDENT') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `school_id`, `email`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 1, 'admin@deped.gov.ph', '$2y$10$7KyINRYVlS89zlmPykpdCOkBYJvtvl23lt04TLRZCsliVg6HUMODi', 'ADMIN', 1, '2025-12-28 02:43:42'),
(2, 1, 'teacher1@deped.gov.ph', '$2y$10$MJ/tzQoRpyStUMn1a3AfxOhMGmdFba.R3vH3mck4XGdrs.kvJumY.', 'TEACHER', 0, '2025-12-28 02:43:42'),
(3, 1, 'student1@gmail.com', '$2y$10$vktCKS/3McXJAfj6YpTPIOLOfhwYU77hssF2NSa/MKi/8qCPEmsim', 'STUDENT', 1, '2025-12-28 02:43:42'),
(4, 1, 'bantillocj1@gmail.com', '$2y$10$6tjmo7jaCoyDojeRfC8lKuJBoH7mQyByQ0FgUm5w5MjKw0.rOiDbi', 'STUDENT', 1, '2025-12-28 03:59:16'),
(5, 1, 'teacher@deped.gov.ph', '$2y$10$X7qWQzn88GR1WOiTWcLGLOocSeDJW1jyPgyf5QszwdTzqeBuJfPbO', 'TEACHER', 1, '2025-12-28 04:03:15'),
(6, 1, 'juan.dela.cruz@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(7, 1, 'maria.clara@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(8, 1, 'jose.rivera@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(9, 1, 'sofia.mendoza@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(10, 1, 'pedro.santos@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(11, 1, 'lucia.reyes@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(12, 1, 'antonio.garcia@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(13, 1, 'isabella.fernandez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(14, 1, 'miguel.torres@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(15, 1, 'camila.villa@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(16, 1, 'daniel.cruz@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(17, 1, 'valentina.hernandez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(18, 1, 'santiago.ramirez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(19, 1, 'emma.diaz@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(20, 1, 'mateo.flores@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(21, 1, 'olivia.castro@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(22, 1, 'liam.gomez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(23, 1, 'ava.rodriguez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(24, 1, 'noah.martinez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36'),
(25, 1, 'sophia.lopez@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', 1, '2025-12-28 06:40:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `generated_by_user_id` (`generated_by_user_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`section_subject_id`,`semester_id`),
  ADD KEY `section_subject_id` (`section_subject_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `honors`
--
ALTER TABLE `honors`
  ADD PRIMARY KEY (`honor_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`school_id`),
  ADD UNIQUE KEY `school_code` (`school_code`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`school_year_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `school_year_id` (`school_year_id`),
  ADD KEY `created_by_teacher_id` (`created_by_teacher_id`),
  ADD KEY `adviser_teacher_id` (`adviser_teacher_id`);

--
-- Indexes for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD PRIMARY KEY (`section_subject_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`semester_id`),
  ADD KEY `school_year_id` (`school_year_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `lrn` (`lrn`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `honors`
--
ALTER TABLE `honors`
  MODIFY `honor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `school_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `school_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `section_subjects`
--
ALTER TABLE `section_subjects`
  MODIFY `section_subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`section_subject_id`) REFERENCES `section_subjects` (`section_subject_id`),
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`);

--
-- Constraints for table `honors`
--
ALTER TABLE `honors`
  ADD CONSTRAINT `honors_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `honors_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`);

--
-- Constraints for table `school_years`
--
ALTER TABLE `school_years`
  ADD CONSTRAINT `school_years_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`school_year_id`),
  ADD CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`created_by_teacher_id`) REFERENCES `teachers` (`teacher_id`),
  ADD CONSTRAINT `sections_ibfk_3` FOREIGN KEY (`adviser_teacher_id`) REFERENCES `teachers` (`teacher_id`);

--
-- Constraints for table `section_subjects`
--
ALTER TABLE `section_subjects`
  ADD CONSTRAINT `section_subjects_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`),
  ADD CONSTRAINT `section_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `section_subjects_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`);

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`school_year_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`);

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`school_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
