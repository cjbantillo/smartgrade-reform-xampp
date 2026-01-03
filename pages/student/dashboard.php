<?php
// pages/student/dashboard.php
require_once '../../includes/auth.php';
require_role('STUDENT');

// Fetch student profile
$stmt = $pdo->prepare("
    SELECT 
        s.student_id,
        s.lrn,
        s.first_name,
        s.last_name,
        sec.name AS section_name,
        sy.year_label
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN enrollments e ON s.student_id = e.student_id
    LEFT JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id
    LEFT JOIN sections sec ON ss.section_id = sec.section_id
    LEFT JOIN school_years sy ON sec.school_year_id = sy.school_year_id AND sy.is_active = TRUE
    WHERE u.user_id = ?
    GROUP BY s.student_id, sec.name, sy.year_label
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    die("Student profile not found. Please contact administrator.");
}

// Fetch school name
$stmt = $pdo->prepare("SELECT school_name FROM schools WHERE school_id = ?");
$stmt->execute([$_SESSION['school_id']]);
$school = $stmt->fetch();

// Fetch latest active SF9
$stmt = $pdo->prepare("
    SELECT document_id, version, created_at
    FROM documents
    WHERE student_id = ? 
      AND document_type = 'SF9' 
      AND is_active = TRUE
    ORDER BY version DESC
    LIMIT 1
");
$stmt->execute([$student['student_id']]);
$latest_sf9 = $stmt->fetch();

// Fetch latest active certificate
$stmt = $pdo->prepare("
    SELECT document_id, version, created_at
    FROM documents
    WHERE student_id = ? 
      AND document_type = 'CERTIFICATE' 
      AND is_active = TRUE
    ORDER BY version DESC LIMIT 1
");
$stmt->execute([$student['student_id']]);
$latest_certificate = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades & Documents - SMARTGRADE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f6f9;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e6d4a;
            text-align: center;
        }

        .nav {
            text-align: center;
            margin: 30px 0;
        }

        .nav a {
            margin: 0 20px;
            color: #1e6d4a;
            font-weight: bold;
            font-size: 18px;
        }

        .card {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: center;
        }

        .student-info {
            font-size: 18px;
            margin: 20px 0;
        }

        .btn {
            background: #0066cc;
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            display: inline-block;
        }

        .btn:hover {
            background: #0050aa;
        }

        .no-sf9 {
            color: #d32f2f;
            font-size: 20px;
            margin: 20px 0;
        }

        .watermark-note {
            font-size: 16px;
            color: #d32f2f;
            font-weight: bold;
            margin: 30px 0;
            padding: 20px;
            background: #fff0f0;
            border-radius: 10px;
        }

        .sf9-info {
            font-size: 18px;
            margin: 20px 0;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h1>
        <p style="text-align:center; font-size:18px;">
            <strong>LRN:</strong> <?= htmlspecialchars($student['lrn']) ?> |
            <strong>Section:</strong> <?= htmlspecialchars($student['section_name'] ?? 'Not Assigned') ?> |
            <strong>S.Y.:</strong> <?= htmlspecialchars($student['year_label'] ?? '2024-2025') ?>
        </p>

        <div class="nav">
            <a href="../../index.php">← Home</a> |
            <a href="../../pages/logout.php">Logout</a>
        </div>

        <div class="card">
            <h2>📊 My Grades (2024-2025)</h2>

            <?php if (empty($grades_by_semester)): ?>
                <p style="text-align:center; color:#666;">No grades recorded yet.</p>
            <?php else: ?>
                <?php foreach ($grades_by_semester as $semester => $subjects): ?>
                    <h3><?= htmlspecialchars($semester) ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th>Student Name</th>
                                <th>Final Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td><?= htmlspecialchars($grade['lrn']) ?></td>
                                    <td><strong><?= htmlspecialchars($grade['last_name'] . ', ' . $grade['first_name']) ?></strong></td>
                                    <td class="final">
                                        <?= $grade['final_grade'] ? number_format($grade['final_grade'], 2) : '<span class="pending">Pending</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 style="color:#1e6d4a;">📄 My SF9 Report Card</h2>
            <p class="student-info"><strong>School:</strong> <?= htmlspecialchars($school['school_name']) ?></p>

            <?php if ($latest_sf9): ?>
                <div class="sf9-info">
                    <p><strong>Current Version:</strong> v<?= $latest_sf9['version'] ?></p>
                    <p><strong>Generated on:</strong> <?= date('F d, Y', strtotime($latest_sf9['created_at'])) ?></p>
                </div>
                <div class="watermark-note">
                    Your SF9 is protected with a watermark for student use.
                </div>
                <a href="view_document.php?doc_id=<?= $latest_sf9['document_id'] ?>"
                    class="btn" target="_blank">
                    👁️ View My SF9 (Watermarked)
                </a>
            <?php else: ?>
                <p class="no-sf9">No SF9 generated yet. Contact your adviser.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>