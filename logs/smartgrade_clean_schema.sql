-- =====================================================
-- SMARTGRADE CLEAN SCHEMA (FINAL – UPDATED)
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------
-- SCHOOLS (DepEd-aware)
-- -------------------------
CREATE TABLE schools (
  school_id INT AUTO_INCREMENT PRIMARY KEY,
  school_code VARCHAR(50) UNIQUE NOT NULL,     -- DepEd School ID
  school_name VARCHAR(255) NOT NULL,
  school_email VARCHAR(255),
  address TEXT,
  district VARCHAR(255),
  division VARCHAR(255),
  region VARCHAR(100),
  logo_path VARCHAR(255),

  principal_name VARCHAR(255),
  superintendent_name VARCHAR(255),

  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------
-- USERS
-- -------------------------
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ADMIN','TEACHER','STUDENT') NOT NULL,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  UNIQUE (email),
  FOREIGN KEY (school_id) REFERENCES schools(school_id)
);

-- -------------------------
-- TEACHERS
-- -------------------------
CREATE TABLE teachers (
  teacher_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  employee_no VARCHAR(50),
  is_active BOOLEAN DEFAULT TRUE,

  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- -------------------------
-- STUDENTS
-- -------------------------
CREATE TABLE students (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  lrn VARCHAR(50) UNIQUE,
  first_name VARCHAR(100),
  last_name VARCHAR(100),
  birthdate DATE,
  address TEXT,
  status ENUM('active','graduated','archived','dropped') DEFAULT 'active',

  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- -------------------------
-- SCHOOL YEARS
-- -------------------------
CREATE TABLE school_years (
  school_year_id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  year_label VARCHAR(20),
  is_active BOOLEAN DEFAULT FALSE,

  FOREIGN KEY (school_id) REFERENCES schools(school_id)
);

-- -------------------------
-- SEMESTERS / TERMS
-- -------------------------
CREATE TABLE semesters (
  semester_id INT AUTO_INCREMENT PRIMARY KEY,
  school_year_id INT NOT NULL,
  name VARCHAR(50),
  sort_order INT,

  FOREIGN KEY (school_year_id) REFERENCES school_years(school_year_id)
);

-- -------------------------
-- SUBJECTS
-- -------------------------
CREATE TABLE subjects (
  subject_id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  subject_code VARCHAR(50),
  subject_name VARCHAR(255),
  units DECIMAL(5,2),
  subject_type ENUM('core','applied','specialized'),

  FOREIGN KEY (school_id) REFERENCES schools(school_id)
);

-- -------------------------
-- SECTIONS / CLASSES
-- -------------------------
CREATE TABLE sections (
  section_id INT AUTO_INCREMENT PRIMARY KEY,
  school_year_id INT NOT NULL,
  name VARCHAR(100),
  created_by_teacher_id INT NOT NULL,
  adviser_teacher_id INT,

  FOREIGN KEY (school_year_id) REFERENCES school_years(school_year_id),
  FOREIGN KEY (created_by_teacher_id) REFERENCES teachers(teacher_id),
  FOREIGN KEY (adviser_teacher_id) REFERENCES teachers(teacher_id)
);

-- -------------------------
-- SECTION SUBJECTS
-- -------------------------
CREATE TABLE section_subjects (
  section_subject_id INT AUTO_INCREMENT PRIMARY KEY,
  section_id INT NOT NULL,
  subject_id INT NOT NULL,
  teacher_id INT NOT NULL,

  FOREIGN KEY (section_id) REFERENCES sections(section_id),
  FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
  FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id)
);

-- -------------------------
-- ENROLLMENTS (WITH ATTEMPTS)
-- -------------------------
CREATE TABLE enrollments (
  enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  section_subject_id INT NOT NULL,
  semester_id INT NOT NULL,
  attempt_no INT DEFAULT 1,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (student_id) REFERENCES students(student_id),
  FOREIGN KEY (section_subject_id) REFERENCES section_subjects(section_subject_id),
  FOREIGN KEY (semester_id) REFERENCES semesters(semester_id)
);

-- -------------------------
-- GRADES
-- -------------------------
CREATE TABLE grades (
  grade_id INT AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT NOT NULL,
  written_work DECIMAL(5,2),
  performance_task DECIMAL(5,2),
  quarterly_exam DECIMAL(5,2),
  final_grade DECIMAL(5,2),
  is_final BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id)
);

-- -------------------------
-- HONORS
-- -------------------------
CREATE TABLE honors (
  honor_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  semester_id INT NOT NULL,
  honor_type VARCHAR(100),
  gwa DECIMAL(5,2),

  FOREIGN KEY (student_id) REFERENCES students(student_id),
  FOREIGN KEY (semester_id) REFERENCES semesters(semester_id)
);

-- -------------------------
-- DOCUMENTS
-- -------------------------
CREATE TABLE documents (
  document_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  document_type ENUM('SF9','SF10','CERTIFICATE'),
  version INT DEFAULT 1,
  file_path VARCHAR(255),
  generated_by_user_id INT,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (student_id) REFERENCES students(student_id),
  FOREIGN KEY (generated_by_user_id) REFERENCES users(user_id)
);

-- -------------------------
-- AUDIT LOGS
-- -------------------------
CREATE TABLE audit_logs (
  audit_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(255),
  target_table VARCHAR(100),
  target_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

SET FOREIGN_KEY_CHECKS = 1;
