<?php
// pages/adviser/generate_sf10.php
require_once '../../includes/auth.php';

// Only Admin or Adviser
if ($current_user['role'] !== 'ADMIN' && $current_user['role'] !== 'TEACHER') {
    die("Access denied.");
}

$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    die("Invalid student.");
}

// Verify adviser/admin access (same as SF9)
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

// Fetch all subjects and grades for G11 and G12
$stmt = $pdo->prepare("
    SELECT subj.subject_name,
           sy.year_label,
           g.final_grade
    FROM enrollments e
    JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id
    JOIN subjects subj ON ss.subject_id = subj.subject_id
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id AND g.is_final = TRUE
    WHERE e.student_id = ?
    ORDER BY sy.year_label, subj.subject_name
");
$stmt->execute([$student_id]);
$all_grades = $stmt->fetchAll();

// Group by year and subject
$grades_by_year = [];
foreach ($all_grades as $g) {
    $year = $g['year_label'];
    $grades_by_year[$year][$g['subject_name']] = $g['final_grade'];
}

// Calculate GWA per year
$gwa_g11 = $gwa_g12 = null;
if (isset($grades_by_year['2024-2025'])) { // Adjust year as needed
    $valid = array_filter($grades_by_year['2024-2025'], 'is_numeric');
    $gwa_g11 = !empty($valid) ? round(array_sum($valid) / count($valid), 2) : null;
}

// Prepare subjects list (merge G11 and G12)
$subjects = [];
$all_subjects = array_unique(array_merge(
    array_keys($grades_by_year['2024-2025'] ?? []),
    array_keys($grades_by_year['2025-2026'] ?? [])
));

foreach ($all_subjects as $subj_name) {
    $subjects[] = [
        'subject_name' => $subj_name,
        'g11_grade' => $grades_by_year['2024-2025'][$subj_name] ?? null,
        'g12_grade' => $grades_by_year['2025-2026'][$subj_name] ?? null,
        'remarks' => '' // Add logic if needed
    ];
}

$current_year = '2024-2025';

// Generate PDF (same as SF9)
require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$is_student_view = false;

ob_start();
include '../../templates/documents/sf10.html';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$pdf_output = $dompdf->output();

// Save file, snapshot, database record (same as SF9 logic)
// ... (copy from generate_sf9.php)

header("Location: document_center.php?sf10_generated=1");
exit;