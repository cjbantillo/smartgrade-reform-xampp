<?php
// pages/adviser/revoke_sf9.php
require_once '../../includes/auth.php';

// Permission check: Only Admin or Adviser of the student's section
if ($current_user['role'] === 'ADMIN') {
    // Admin can revoke any SF9
} else {
    if ($current_user['role'] !== 'TEACHER' || empty($current_user['teacher_id'])) {
        die("Access denied: Only advisers or admins can revoke SF9.");
    }

    $student_id = (int)($_GET['student_id'] ?? 0);
    if ($student_id <= 0) {
        die("Invalid student.");
    }

    // Verify that this teacher is the adviser of the student's section
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM sections sec
        JOIN section_subjects ss ON sec.section_id = ss.section_id
        JOIN enrollments e ON ss.section_subject_id = e.section_subject_id
        WHERE e.student_id = ? 
          AND sec.adviser_teacher_id = ?
        LIMIT 1
    ");
    $stmt->execute([$student_id, $current_user['teacher_id']]);
    if ($stmt->rowCount() === 0) {
        die("Access denied: You are not the adviser of this student.");
    }
}

$doc_id = (int)($_GET['doc_id'] ?? 0);
$student_id = (int)($_GET['student_id'] ?? 0);

if ($doc_id <= 0 || $student_id <= 0) {
    die("Invalid request.");
}

// Verify the document exists, belongs to the student, is SF9, and is active
$stmt = $pdo->prepare("
    SELECT document_id, version 
    FROM documents 
    WHERE document_id = ? 
      AND student_id = ? 
      AND document_type = 'SF9' 
      AND is_active = TRUE
");
$stmt->execute([$doc_id, $student_id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Document not found, already revoked, or access denied.");
}

// Revoke the document (soft delete)
try {
    $pdo->beginTransaction();

    // Mark as inactive
    $stmt = $pdo->prepare("UPDATE documents SET is_active = FALSE WHERE document_id = ?");
    $stmt->execute([$doc_id]);

    // Log the revocation in audit trail
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs 
        (user_id, action, target_table, target_id, details, created_at) 
        VALUES (?, 'REVOKED_SF9', 'documents', ?, ?, NOW())
    ");
    $details = "Revoked SF9 version {$doc['version']} for student ID {$student_id}";
    $stmt->execute([$current_user['user_id'], $doc_id, $details]);

    $pdo->commit();

    // Success message via session or query string
    header("Location: document_center.php?section_id=" . urlencode($_GET['section_id'] ?? '') . "&revoked=1");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Failed to revoke document. Please try again.");
}