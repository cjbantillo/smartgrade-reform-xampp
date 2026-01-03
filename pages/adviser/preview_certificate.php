<?php
// pages/adviser/preview_certificate.php
require_once '../../includes/auth.php';

// Allow Admin and Teacher (Adviser)
if (!in_array($current_user['role'], ['ADMIN', 'TEACHER'])) {
    die("Access denied.");
}

// Fetch school info
$stmt = $pdo->prepare("SELECT school_name, principal_name, logo_path FROM schools WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();

// Dummy data for preview
$student = [
    'first_name' => 'Juan',
    'last_name' => 'Dela Cruz',
];
$adviser_name = 'Maria Clara Santos';
$honors = 'With High Honors'; // Example
$is_student_view = false; // Clean preview

// Generate preview PDF
require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

ob_start();
include '../../templates/documents/certificate.html';
$html = ob_get_clean();

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("Certificate_Template_Preview.pdf", ["Attachment" => false]);
exit;