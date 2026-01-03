<?php
// pages/teacher/encode_grades.php
require_once '../../includes/auth.php';
require_role('TEACHER');

$section_subject_id = (int)($_GET['ss'] ?? 0);
if ($section_subject_id <= 0) {
    die("Invalid class.");
}

// Verify teacher is assigned
$stmt = $pdo->prepare("
    SELECT 1 FROM section_subjects ss
    WHERE ss.section_subject_id = ? AND ss.teacher_id = ?
");
$stmt->execute([$section_subject_id, $current_user['teacher_id']]);
if ($stmt->rowCount() === 0) {
    die("Access denied: You are not assigned to teach this subject.");
}

// Fetch class details
$stmt = $pdo->prepare("
    SELECT 
        sec.name AS section_name,
        subj.subject_name,
        subj.subject_code,
        sem.name AS semester_name
    FROM section_subjects ss
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN subjects subj ON ss.subject_id = subj.subject_id
    JOIN semesters sem ON sec.school_year_id = sem.school_year_id
    WHERE ss.section_subject_id = ?
");
$stmt->execute([$section_subject_id]);
$class_info = $stmt->fetch();

if (!$class_info) {
    die("Class not found.");
}

// Fetch class semester first
$stmt = $pdo->prepare("
    SELECT sem.semester_id
    FROM section_subjects ss
    JOIN sections sec ON ss.section_id = sec.section_id
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    JOIN semesters sem ON sy.school_year_id = sem.school_year_id
    WHERE ss.section_subject_id = ?
    LIMIT 1
");
$stmt->execute([$section_subject_id]);
$semester_row = $stmt->fetch();
$semester_id = $semester_row['semester_id'] ?? 0;

// Fetch students for this exact section_subject and semester
$stmt = $pdo->prepare("
    SELECT 
        e.enrollment_id,
        s.student_id,
        s.lrn,
        s.first_name,
        s.last_name,
        g.final_grade
    FROM enrollments e
    JOIN students s ON e.student_id = s.student_id
    LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id
    WHERE e.section_subject_id = ?
      AND e.semester_id = ?
    ORDER BY s.last_name, s.first_name
");
$stmt->execute([$section_subject_id, $semester_id]);
$students = $stmt->fetchAll();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['grades'])) {
    try {
        $pdo->beginTransaction();

        foreach ($_POST['grades'] as $enrollment_id => $final_grade) {
            $final_grade = trim($final_grade) === '' ? null : (float)$final_grade;

            $stmt = $pdo->prepare("
                INSERT INTO grades (enrollment_id, final_grade, is_final) 
                VALUES (?, ?, TRUE)
                ON DUPLICATE KEY UPDATE 
                    final_grade = VALUES(final_grade),
                    is_final = TRUE
            ");
            $stmt->execute([$enrollment_id, $final_grade]);
        }

        $pdo->commit();
        $success = "Final grades saved successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to save grades.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encode Final Grades - <?= htmlspecialchars($class_info['subject_name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f9f9f9;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #1e6d4a;
            text-align: center;
        }

        p.info {
            text-align: center;
            font-size: 18px;
            margin: 10px 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        th,
        td {
            padding: 14px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        input[type="number"] {
            width: 120px;
            padding: 10px;
            font-size: 16px;
            text-align: center;
            border: 1px solid #aaa;
            border-radius: 6px;
        }

        .btn {
            background: #1e6d4a;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin: 20px 0;
        }

        .btn:hover {
            background: #165c3a;
        }

        .back {
            text-align: center;
            margin: 20px 0;
        }

        .back a {
            color: #1e6d4a;
            font-weight: bold;
            font-size: 18px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Encode Final Grades</h2>
        <p class="info">
            <strong><?= htmlspecialchars($class_info['subject_code'] . ' - ' . $class_info['subject_name']) ?></strong><br>
            Section: <?= htmlspecialchars($class_info['section_name']) ?> |
            Semester: <?= htmlspecialchars($class_info['semester_name']) ?>
        </p>

        <div class="back">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($students)): ?>
            <p style="text-align:center; color:#d32f2f; font-size:18px;">
                No students enrolled in this subject for <?= htmlspecialchars($class_info['semester_name']) ?>.
            </p>
        <?php else: ?>
            <form method="post">
                <table>
                    <thead>
                        <tr>
                            <th>LRN</th>
                            <th>Student Name</th>
                            <th>Final Grade (0–100)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $stu): ?>
                            <tr>
                                <td><?= htmlspecialchars($stu['lrn']) ?></td>
                                <td style="text-align:left; padding-left:20px;">
                                    <strong><?= htmlspecialchars($stu['last_name'] . ', ' . $stu['first_name']) ?></strong>
                                </td>
                                <td>
                                    <input type="number"
                                        name="grades[<?= $stu['enrollment_id'] ?>]"
                                        value="<?= $stu['final_grade'] !== null ? number_format($stu['final_grade'], 2) : '' ?>"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        placeholder="e.g. 90.50"
                                        required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="text-align:center;">
                    <button type="submit" class="btn">Save All Final Grades</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>