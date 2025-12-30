-- =====================================================
-- SMARTGRADE SEED DATA (FINAL – UPDATED)
-- =====================================================

START TRANSACTION;

-- -----------------------------------------------------
-- 1. SCHOOL (OFFICIAL DepEd DATA)
-- -----------------------------------------------------
INSERT INTO schools (
  school_code,
  school_name,
  school_email,
  address,
  district,
  division,
  region,
  principal_name,
  superintendent_name,
  is_active
) VALUES (
  '317511',
  'Ampayon National High School',
  '317511@deped.gov.ph',
  'Purok 3B, Ampayon, Butuan City, Agusan del Norte',
  'East Butuan District I',
  'Schools Division of Agusan del Norte',
  'CARAGA Region',
  'SOLEDAD M. RUBILLOS',
  'Schools Division Superintendent – Agusan del Norte',
  TRUE
);

-- -----------------------------------------------------
-- 2. USERS
-- -----------------------------------------------------

-- ADMIN
INSERT INTO users (
  school_id, email, password_hash, role, is_active
) VALUES (
  1, 'admin@deped.gov.ph', '$2y$10$7KyINRYVlS89zlmPykpdCOkBYJvtvl23lt04TLRZCsliVg6HUMODi', 'ADMIN', TRUE
);

-- TEACHER
INSERT INTO users (
  school_id, email, password_hash, role, is_active
) VALUES (
  1, 'teacher1@deped.gov.ph', '$2y$10$MJ/tzQoRpyStUMn1a3AfxOhMGmdFba.R3vH3mck4XGdrs.kvJumY.', 'TEACHER', TRUE
);

-- STUDENT
INSERT INTO users (
  school_id, email, password_hash, role, is_active
) VALUES (
  1, 'student1@gmail.com', '$2y$10$vktCKS/3McXJAfj6YpTPIOLOfhwYU77hssF2NSa/MKi/8qCPEmsim', 'STUDENT', TRUE
);

-- -----------------------------------------------------
-- 3. TEACHER PROFILE
-- -----------------------------------------------------
INSERT INTO teachers (
  user_id, first_name, last_name, employee_no, is_active
) VALUES (
  2, 'ELMA JOY', 'JOPIA', 'T-2024-001', TRUE
);

-- -----------------------------------------------------
-- 4. STUDENT PROFILE
-- -----------------------------------------------------
INSERT INTO students (
  user_id, lrn, first_name, last_name, birthdate, address, status
) VALUES (
  3, '123456789012', 'KIRSTEIN GENZEN', 'NOJAPA',
  '2007-06-15', 'Butuan City', 'active'
);

-- -----------------------------------------------------
-- 5. SCHOOL YEAR
-- -----------------------------------------------------
INSERT INTO school_years (
  school_id, year_label, is_active
) VALUES (
  1, '2024-2025', TRUE
);

-- -----------------------------------------------------
-- 6. SEMESTERS
-- -----------------------------------------------------
INSERT INTO semesters (
  school_year_id, name, sort_order
) VALUES
(1, '1st Semester', 1),
(1, '2nd Semester', 2);

-- -----------------------------------------------------
-- 7. SUBJECTS
-- -----------------------------------------------------
INSERT INTO subjects (
  school_id, subject_code, subject_name, units, subject_type
) VALUES
(1, 'ENG12', 'English for Academic and Professional Purposes', 3.00, 'core'),
(1, 'MATH12', 'General Mathematics', 3.00, 'core');

-- -----------------------------------------------------
-- 8. SECTION
-- -----------------------------------------------------
INSERT INTO sections (
  school_year_id, name, created_by_teacher_id, adviser_teacher_id
) VALUES (
  1, 'Grade 12 - Einstein', 1, 1
);

-- -----------------------------------------------------
-- 9. SECTION SUBJECTS
-- -----------------------------------------------------
INSERT INTO section_subjects (
  section_id, subject_id, teacher_id
) VALUES
(1, 1, 1),
(1, 2, 1);

-- -----------------------------------------------------
-- 10. ENROLLMENT
-- -----------------------------------------------------
INSERT INTO enrollments (
  student_id, section_subject_id, semester_id, attempt_no
) VALUES
(1, 1, 1, 1),
(1, 2, 1, 1);

-- -----------------------------------------------------
-- 11. GRADES
-- -----------------------------------------------------
INSERT INTO grades (
  enrollment_id, written_work, performance_task, quarterly_exam,
  final_grade, is_final
) VALUES
(1, 88, 90, 85, 88.50, TRUE),
(2, 92, 91, 90, 91.00, TRUE);

-- -----------------------------------------------------
-- 12. HONORS
-- -----------------------------------------------------
INSERT INTO honors (
  student_id, semester_id, honor_type, gwa
) VALUES (
  1, 1, 'WITH HONORS', 89.75
);

-- -----------------------------------------------------
-- 13. DOCUMENTS
-- -----------------------------------------------------
INSERT INTO documents (
  student_id, document_type, version, file_path,
  generated_by_user_id, is_active
) VALUES
(1, 'SF9', 1,
 'storage/documents/school_1/students/1/sf9/sf9_v1.pdf', 1, TRUE),
(1, 'CERTIFICATE', 1,
 'storage/documents/school_1/students/1/certificates/honors_v1.pdf', 1, TRUE);

-- -----------------------------------------------------
-- 14. AUDIT LOGS
-- -----------------------------------------------------
INSERT INTO audit_logs (
  user_id, action, target_table, target_id
) VALUES
(1, 'GENERATED_SF9', 'documents', 1),
(1, 'GENERATED_CERTIFICATE', 'documents', 2);

COMMIT;
