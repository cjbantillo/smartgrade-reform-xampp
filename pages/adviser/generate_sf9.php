<?php
// pages/adviser/generate_sf9.php
require_once '../../includes/auth.php';

// Permission check: Admin or Adviser of the student's section
if ($current_user['role'] === 'ADMIN') {
    // Admin can generate for anyone
} else {
    if ($current_user['role'] !== 'TEACHER' || empty($current_user['teacher_id'])) {
        die("Access denied: Only advisers or admins can generate SF9.");
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

// Get student ID safely
$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    die("Invalid student.");
}

// Fetch student and section info
$stmt = $pdo->prepare("
    SELECT 
        s.student_id, 
        s.lrn, 
        s.first_name, 
        s.last_name,
        sec.name AS section_name,
        sy.year_label
    FROM students s
    JOIN enrollments e ON s.student_id = e.student_id
    JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    WHERE s.student_id = ? 
      AND sy.is_active = TRUE
    GROUP BY s.student_id, sec.name, sy.year_label
    LIMIT 1
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found or not enrolled in the active school year.");
}

// Fetch school info
$stmt = $pdo->prepare("SELECT school_name, principal_name FROM schools WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();

if (!$school) {
    die("School information not found.");
}

// Fetch adviser name
$stmt = $pdo->prepare("
    SELECT t.first_name, t.last_name
    FROM sections sec
    JOIN teachers t ON sec.adviser_teacher_id = t.teacher_id
    WHERE sec.section_id = (
        SELECT ss.section_id 
        FROM section_subjects ss
        JOIN enrollments e ON ss.section_subject_id = e.section_subject_id
        WHERE e.student_id = ?
        LIMIT 1
    )
");
$stmt->execute([$student_id]);
$adviser = $stmt->fetch();
$adviser_name = $adviser ? $adviser['last_name'] . ', ' . $adviser['first_name'] : 'N/A';

// Fetch grades for all subjects
$stmt = $pdo->prepare("
    SELECT 
        subj.subject_name,
        subj.subject_code,
        g.final_grade,
        CASE 
            WHEN g.final_grade >= 75 THEN 'PASSED'
            WHEN g.final_grade IS NULL THEN ''
            ELSE 'FAILED'
        END AS remarks
    FROM enrollments e
    JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id
    JOIN subjects subj ON ss.subject_id = subj.subject_id
    LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id AND g.is_final = TRUE
    WHERE e.student_id = ?
    ORDER BY subj.subject_name
");
$stmt->execute([$student_id]);
$subjects = $stmt->fetchAll();

// Calculate General Weighted Average (GWA)
$valid_grades = array_filter(array_column($subjects, 'final_grade'), 'is_numeric');
$gwa = !empty($valid_grades) ? round(array_sum($valid_grades) / count($valid_grades), 2) : null;

// Create data snapshot for audit
$snapshot = [
    'generated_at' => date('Y-m-d H:i:s'),
    'generated_by_user_id' => $current_user['user_id'],
    'generated_by_role' => $current_user['role'],
    'student' => [
        'id' => $student['student_id'],
        'lrn' => $student['lrn'],
        'name' => $student['last_name'] . ', ' . $student['first_name'],
        'section' => $student['section_name']
    ],
    'school' => $school,
    'adviser' => $adviser_name,
    'school_year' => $student['year_label'],
    'subjects' => $subjects,
    'gwa' => $gwa
];

// Define storage paths
$base_dir = "../../storage/documents/school_{$_SESSION['school_id']}/students/{$student_id}/sf9";
$snapshot_dir = "../../storage/snapshots";

if (!is_dir($base_dir)) {
    mkdir($base_dir, 0777, true);
}
if (!is_dir($snapshot_dir)) {
    mkdir($snapshot_dir, 0777, true);
}

// Determine next version number
$existing_files = glob($base_dir . "/sf9_v*.pdf");
$version = count($existing_files) + 1;

$file_name = "sf9_v{$version}.pdf";
$file_path = "{$base_dir}/{$file_name}";
$snapshot_file = "{$snapshot_dir}/sf9_student_{$student_id}_v{$version}.json";

// Load DomPDF
require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans'); // Better for special characters
$dompdf = new Dompdf($options);

// Render HTML template
$is_student_view = false; // Default for adviser/admin

// If called from student dashboard, enable watermark
if (isset($_GET['student_view']) && $current_user['role'] === 'STUDENT') {
    $is_student_view = true;
}
ob_start();
include '../../templates/documents/sf9.html'; // Your template with {{variables}} replaced via PHP
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$pdf_content = $dompdf->output();

// Save PDF file
file_put_contents($file_path, $pdf_content);

// Save JSON snapshot
file_put_contents($snapshot_file, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Insert record into documents table
$stmt = $pdo->prepare("
    INSERT INTO documents 
    (student_id, document_type, version, file_path, generated_by_user_id, is_active, created_at)
    VALUES (?, 'SF9', ?, ?, ?, TRUE, NOW())
");
$db_file_path = "storage/documents/school_{$_SESSION['school_id']}/students/{$student_id}/sf9/{$file_name}";
$stmt->execute([$student_id, $version, $db_file_path, $current_user['user_id']]);

$document_id = $pdo->lastInsertId();

// Log audit trail
$stmt = $pdo->prepare("
    INSERT INTO audit_logs (user_id, action, target_table, target_id, created_at)
    VALUES (?, 'GENERATED_SF9', 'documents', ?, NOW())
");
$stmt->execute([$current_user['user_id'], $document_id]);

// Redirect back to Document Center with success flag
header("Location: document_center.php?sf9_generated=1");
exit;