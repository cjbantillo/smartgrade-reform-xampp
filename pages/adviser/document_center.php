<?php
// pages/adviser/document_center.php
require_once '../../includes/auth.php';

// Permission: Admin or Adviser
if ($current_user['role'] === 'ADMIN') {
    // Admin can access all
} elseif ($current_user['role'] === 'TEACHER') {
    // Must be an adviser
    $stmt = $pdo->prepare("
        SELECT 1 FROM sections 
        WHERE adviser_teacher_id = ? 
          AND school_year_id = (SELECT school_year_id FROM school_years WHERE is_active = TRUE)
    ");
    $stmt->execute([$current_user['teacher_id']]);
    if ($stmt->rowCount() === 0) {
        die("Access denied: You are not an adviser of any section.");
    }
} else {
    die("Access denied.");
}

// Success message from generation
$success_message = '';
if ($_GET['sf9_generated'] ?? false) {
    $success_message = "SF9 Report Card generated successfully! A new version has been created.";
}

// Get active school year
$stmt = $pdo->prepare("SELECT school_year_id, year_label FROM school_years WHERE is_active = TRUE");
$stmt->execute();
$active_year = $stmt->fetch();

if (!$active_year) {
    die("No active school year.");
}

// Fetch sections this user advises
$sections_where = $current_user['role'] === 'ADMIN'
    ? "sy.is_active = TRUE"
    : "sec.adviser_teacher_id = ? AND sy.is_active = TRUE";

$params = $current_user['role'] === 'ADMIN' ? [] : [$current_user['teacher_id']];

$stmt = $pdo->prepare("
    SELECT sec.section_id, sec.name AS section_name
    FROM sections sec
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    WHERE $sections_where
    ORDER BY sec.name
");
$stmt->execute($params);
$advised_sections = $stmt->fetchAll();

// Default to first section
$selected_section_id = (int)($_GET['section_id'] ?? ($advised_sections[0]['section_id'] ?? 0));

$students = [];

if ($selected_section_id > 0) {
    // Fetch students + SF9 count
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            s.student_id,
            s.lrn,
            s.first_name,
            s.last_name,
            (SELECT COUNT(*) FROM documents d 
             WHERE d.student_id = s.student_id 
               AND d.document_type = 'SF9' 
               AND d.is_active = TRUE) AS sf9_count
        FROM students s
        JOIN enrollments e ON s.student_id = e.student_id
        JOIN section_subjects ss ON e.section_subject_id = ss.section_subject_id
        WHERE ss.section_id = ?
        ORDER BY s.last_name, s.first_name
    ");
    $stmt->execute([$selected_section_id]);
    $students = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Center - Adviser</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f6f9;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #1e6d4a;
            text-align: center;
        }

        .back {
            margin: 20px 0;
            text-align: center;
        }

        .back a {
            color: #1e6d4a;
            font-weight: bold;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }

        .section-selector {
            margin: 30px 0;
            text-align: center;
        }

        select {
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            min-width: 300px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 16px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .btn-generate {
            background: #0066cc;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
        }

        .btn-generate:hover {
            background: #0050aa;
        }

        .no-students {
            text-align: center;
            color: #666;
            padding: 60px;
            font-size: 18px;
        }

        .version-count {
            font-weight: bold;
            color: #1e6d4a;
            font-size: 18px;
        }

        td a.btn-generate {
            white-space: nowrap;
            font-size: 14px;
            padding: 10px 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Document Center</h1>
        <p style="text-align:center;"><strong>School Year:</strong> <?= htmlspecialchars($active_year['year_label']) ?></p>

        <div class="back">
            <?php if ($current_user['role'] === 'ADMIN'): ?>
                <a href="../admin/dashboard.php">← Admin Dashboard</a>
            <?php else: ?>
                <a href="../teacher/dashboard.php">← Teacher Dashboard</a>
            <?php endif; ?>
        </div>

        <!-- Success Message - Only Once -->
        <?php if ($success_message): ?>
            <div class="success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <!-- Revoked Message -->
        <?php if ($_GET['revoked'] ?? false): ?>
            <div class="success">
                Previous SF9 has been revoked. You can now generate a corrected version.
            </div>
        <?php endif; ?>

        <div class="section-selector">
            <label><strong>Select Section:</strong></label><br><br>
            <select onchange="if(this.value) window.location='document_center.php?section_id='+this.value">
                <option value="">-- Choose a Section --</option>
                <?php foreach ($advised_sections as $sec): ?>
                    <option value="<?= $sec['section_id'] ?>" <?= $selected_section_id == $sec['section_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sec['section_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selected_section_id <= 0): ?>
            <p class="no-students">Please select a section to view students.</p>
        <?php elseif (empty($students)): ?>
            <p class="no-students">No students enrolled in this section yet.</p>
        <?php else: ?>
            <?php
            $section_name = '';
            foreach ($advised_sections as $sec) {
                if ($sec['section_id'] == $selected_section_id) {
                    $section_name = $sec['section_name'];
                    break;
                }
            }
            ?>
            <h2 style="text-align:center; color:#1e6d4a; margin:40px 0 20px;">Students in <?= htmlspecialchars($section_name) ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>LRN</th>
                        <th>Student Name</th>
                        <th>SF9 Generated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $stu): ?>
                        <tr>
                            <td><?= htmlspecialchars($stu['lrn']) ?></td>
                            <td><strong><?= htmlspecialchars($stu['last_name'] . ', ' . $stu['first_name']) ?></strong></td>
                            <td class="version-count"><?= (int)$stu['sf9_count'] ?> version(s)</td>
                            <td style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                                <!-- Generate New -->
                                <a href="generate_sf9.php?student_id=<?= $stu['student_id'] ?>"
                                    class="btn-generate"
                                    title="Generate new version">
                                    📄 Generate New
                                </a>

                                <!-- View Latest (Clean) -->
                                <?php if ((int)$stu['sf9_count'] > 0): ?>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT document_id 
                                        FROM documents 
                                        WHERE student_id = ? AND document_type = 'SF9' AND is_active = TRUE
                                        ORDER BY version DESC LIMIT 1
                                    ");
                                    $stmt->execute([$stu['student_id']]);
                                    $latest = $stmt->fetch();
                                    ?>
                                    <a href="view_document.php?doc_id=<?= $latest['document_id'] ?>"
                                        class="btn-generate" style="background:#17a2b8;"
                                        title="View clean version"
                                        target="_blank">
                                        👁️ View
                                    </a>

                                    <!-- Revoke (Only if active exists) -->
                                    <a href="revoke_sf9.php?doc_id=<?= $latest['document_id'] ?>&student_id=<?= $stu['student_id'] ?>"
                                        class="btn-generate" style="background:#d32f2f;"
                                        title="Revoke current version"
                                        onclick="return confirm('Revoke this SF9? It will be archived and you can generate a corrected version.')">
                                        ❌ Revoke
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>