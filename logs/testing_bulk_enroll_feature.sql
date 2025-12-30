-- =====================================================
-- BULK TEST STUDENTS FOR ENROLLMENT TESTING
-- =====================================================

START TRANSACTION;

-- 1. Insert Users (STUDENT role, school_id = 1)
INSERT INTO users (school_id, email, password_hash, role, is_active) VALUES
(1, 'juan.dela.cruz@gmail.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),  -- password: password
(1, 'maria.clara@gmail.com',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'jose.rivera@gmail.com',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'sofia.mendoza@gmail.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'pedro.santos@gmail.com',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'lucia.reyes@gmail.com',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'antonio.garcia@gmail.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'isabella.fernandez@gmail.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'miguel.torres@gmail.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'camila.villa@gmail.com',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'daniel.cruz@gmail.com',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'valentina.hernandez@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'santiago.ramirez@gmail.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'emma.diaz@gmail.com',             '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'mateo.flores@gmail.com',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'olivia.castro@gmail.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'liam.gomez@gmail.com',            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'ava.rodriguez@gmail.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'noah.martinez@gmail.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE),
(1, 'sophia.lopez@gmail.com',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'STUDENT', TRUE);

-- 2. Insert Student Profiles (linked to the users above, user_id = last inserted + offset)
-- Assuming the last user_id before this insert is 3 (from seed data), adjust if needed
-- Here we use user_id from 4 to 23

INSERT INTO students (user_id, lrn, first_name, last_name, birthdate, address, status) VALUES
(4,  '100000000001', 'Juan',        'Dela Cruz',      '2008-03-15', 'Butuan City', 'active'),
(5,  '100000000002', 'Maria Clara', 'Santos',         '2008-05-20', 'Butuan City', 'active'),
(6,  '100000000003', 'Jose',        'Rivera',         '2008-01-12', 'Butuan City', 'active'),
(7,  '100000000004', 'Sofia',       'Mendoza',        '2008-07-08', 'Butuan City', 'active'),
(8,  '100000000005', 'Pedro',       'Reyes',          '2008-09-30', 'Butuan City', 'active'),
(9,  '100000000006', 'Lucia',       'Garcia',         '2008-11-22', 'Butuan City', 'active'),
(10, '100000000007', 'Antonio',     'Fernandez',      '2008-02-18', 'Butuan City', 'active'),
(11, '100000000008', 'Isabella',    'Torres',         '2008-04-05', 'Butuan City', 'active'),
(12, '100000000009', 'Miguel',      'Villa',          '2008-06-14', 'Butuan City', 'active'),
(13, '100000000010', 'Camila',      'Cruz',           '2008-08-27', 'Butuan City', 'active'),
(14, '100000000011', 'Daniel',      'Hernandez',      '2008-10-03', 'Butuan City', 'active'),
(15, '100000000012', 'Valentina',   'Ramirez',        '2008-12-11', 'Butuan City', 'active'),
(16, '100000000013', 'Santiago',    'Diaz',           '2008-03-25', 'Butuan City', 'active'),
(17, '100000000014', 'Emma',        'Flores',         '2008-05-17', 'Butuan City', 'active'),
(18, '100000000015', 'Mateo',       'Castro',         '2008-07-29', 'Butuan City', 'active'),
(19, '100000000016', 'Olivia',      'Gomez',          '2008-09-04', 'Butuan City', 'active'),
(20, '100000000017', 'Liam',        'Rodriguez',      '2008-11-16', 'Butuan City', 'active'),
(21, '100000000018', 'Ava',         'Martinez',       '2008-01-09', 'Butuan City', 'active'),
(22, '100000000019', 'Noah',        'Lopez',          '2008-02-21', 'Butuan City', 'active'),
(23, '100000000020', 'Sophia',      'Perez',          '2008-04-13', 'Butuan City', 'active');

COMMIT;

-- =====================================================
-- All students now have login credentials:
-- Email: as above
-- Password: password (same hash for easy testing)
-- =====================================================