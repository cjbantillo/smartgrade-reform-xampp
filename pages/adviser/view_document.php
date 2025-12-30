<?php
// pages/adviser/view_document.php
require_once '../../includes/auth.php';

// Allow Adviser and Admin only
if (!in_array($current_user['role'], ['ADMIN', 'TEACHER'])) {
    die("Access denied.");
}

$doc_id = (int)($_GET['doc_id'] ?? 0);
if ($doc_id <= 0) {
    die("Invalid document.");
}

$stmt = $pdo->prepare("SELECT file_path FROM documents WHERE document_id = ? AND is_active = TRUE");
$stmt->execute([$doc_id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Document not found.");
}

$file_path = '../../' . $doc['file_path'];

if (!file_exists($file_path)) {
    die("File not found.");
}

// Serve clean PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="SF9_Official.pdf"');
readfile($file_path);
exit;