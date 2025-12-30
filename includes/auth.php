<?php
// includes/auth.php

session_start();
require_once __DIR__ . '/../config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Optional: Fetch current user details (useful often)
// Replace the existing fetch query with this:
// includes/auth.php - Improved query
$stmt = $pdo->prepare("
    SELECT 
        u.user_id, 
        u.email, 
        u.role, 
        u.school_id, 
        u.is_active,
        s.school_name,
        
        -- Teacher fields
        t.teacher_id,
        t.first_name AS teacher_first_name,
        t.last_name AS teacher_last_name,
        
        -- Student fields
        st.student_id,
        st.first_name AS student_first_name,
        st.last_name AS student_last_name,
        st.lrn,
        st.status AS student_status
    FROM users u 
    JOIN schools s ON u.school_id = s.school_id
    LEFT JOIN teachers t ON u.user_id = t.user_id
    LEFT JOIN students st ON u.user_id = st.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

if (!$current_user) {
    session_destroy();
    header("Location: ../pages/login.php");
    exit;
}

// Helper function to require specific role
function require_role($allowed_roles) {
    global $current_user;
    if (!in_array($current_user['role'], (array)$allowed_roles)) {
        die("Access denied. You do not have permission to view this page.");
    }
}