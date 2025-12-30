<?php
// pages/adviser/generate_certificate.php
require_once '../../includes/auth.php';

if ($current_user['role'] !== 'ADMIN' && $current_user['role'] !== 'TEACHER') {
    die("Access denied.");
}

$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    die("Invalid student.");
}

// Permission check
if ($current_user['role'] === 'TEACHER') {
    $stmt = $pdo->prepare("
        SELECT 1 FROM sections sec
        JOIN section_subjects ss ON sec.section_id = ss.section_id
        JOIN enrollments e ON ss.section_subject_id = e.section_subject_id
        WHERE e.student_id = ? AND sec.adviser_teacher_id = ?
    ");
    $stmt->execute([$student_id, $current_user['teacher_id']]);
    if ($stmt->rowCount() === 0) {
        die("Access denied.");
    }
}

// Fetch student
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Fetch school
$stmt = $pdo->prepare("SELECT school_name, principal_name FROM schools WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();

// Adviser
$stmt = $pdo->prepare("SELECT t.first_name, t.last_name FROM teachers t JOIN sections sec ON t.teacher_id = sec.adviser_teacher_id JOIN section_subjects ss ON sec.section_id = ss.section_id JOIN enrollments e ON ss.section_subject_id = e.section_subject_id WHERE e.student_id = ? LIMIT 1");
$stmt->execute([$student_id]);
$adviser = $stmt->fetch();
$adviser_name = $adviser ? $adviser['last_name'] . ', ' . $adviser['first_name'] : 'N/A';

// Check for honors (example: with honors if GWA >= 90)
$gwa = 92.5; // Replace with actual calculation
$honors = '';
if ($gwa >= 98) $honors = 'With Highest Honors';
elseif ($gwa >= 95) $honors = 'With High Honors';
elseif ($gwa >= 90) $honors = 'With Honors';

// Generate PDF
require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$is_student_view = false;

ob_start();
include '../../templates/documents/certificate.html';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$pdf_output = $dompdf->output();

// Save file (same logic as SF9)
$dir = "../../storage/documents/school_{$_SESSION['school_id']}/students/{$student_id}/certificate";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$version = count(glob($dir . "/certificate_v*.pdf")) + 1;
$file_name = "certificate_v{$version}.pdf";
$file_path = "{$dir}/{$file_name}";

file_put_contents($file_path, $pdf_output);

// Save to documents table
$stmt = $pdo->prepare("
    INSERT INTO documents 
    (student_id, document_type, version, file_path, generated_by_user_id, is_active, created_at)
    VALUES (?, 'CERTIFICATE', ?, ?, ?, TRUE, NOW())
");
$db_path = "storage/documents/school_{$_SESSION['school_id']}/students/{$student_id}/certificate/{$file_name}";
$stmt->execute([$student_id, $version, $db_path, $current_user['user_id']]);

// Redirect
header("Location: document_center.php?certificate_generated=1");
exit;