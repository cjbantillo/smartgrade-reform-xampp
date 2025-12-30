<?php
// pages/student/view_document.php
require_once '../../includes/auth.php';
require_role('STUDENT');

$doc_id = (int)($_GET['doc_id'] ?? 0);
if ($doc_id <= 0) {
    die("Invalid document.");
}

// Verify ownership
$stmt = $pdo->prepare("
    SELECT d.file_path
    FROM documents d
    JOIN students s ON d.student_id = s.student_id
    WHERE d.document_id = ? 
      AND s.user_id = ? 
      AND d.document_type = 'SF9' 
      AND d.is_active = TRUE
");
$stmt->execute([$doc_id, $_SESSION['user_id']]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Document not found or access denied.");
}

$original_pdf_path = '../../' . $doc['file_path'];

if (!file_exists($original_pdf_path)) {
    die("PDF file not found on server.");
}

// Load TCPDF and FPDI
require_once '../../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

class WatermarkedPDF extends Fpdi {

    public function Header() {
        // No header
    }

    public function Footer() {
        // No footer
    }

    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false) {
        parent::AddPage($orientation, $format, $keepmargins, $tocpage);

        // Add watermark
        $this->SetFont('helvetica', 'B', 80);
        $this->SetTextColor(220, 50, 50);
        $this->SetAlpha(0.25);
        $this->StartTransform();
        $this->Rotate(45, 148.5, 105);
        $this->Text(30, 105, 'Official Copy – Student View');
        $this->StopTransform();
        $this->SetAlpha(1);
    }
}

// Create PDF
$pdf = new WatermarkedPDF();

// Document info
$pdf->SetCreator('SMARTGRADE');
$pdf->SetAuthor('SMARTGRADE');
$pdf->SetTitle('SF9 - Student View');
$pdf->SetSubject('Official Copy with Watermark');

// Remove header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Zero margins
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

// Import original PDF
$page_count = $pdf->setSourceFile($original_pdf_path);

for ($i = 1; $i <= $page_count; $i++) {
    $tpl_idx = $pdf->importPage($i);
    $pdf->AddPage('L'); // Landscape
    $pdf->useTemplate($tpl_idx, 0, 0, 297, 210); // Full page
}

// Output
$pdf->Output('SF9_Student_View.pdf', 'I');
exit;