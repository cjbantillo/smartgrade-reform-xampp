<?php
// pages/teacher/manage_subjects.php
require_once '../../includes/auth.php';
require_role('TEACHER');

$section_id = (int)($_GET['section_id'] ?? 0);
if ($section_id <= 0) {
    die("Invalid section.");
}

// Verify teacher owns this section
$stmt = $pdo->prepare("
    SELECT sec.*, sy.year_label
    FROM sections sec
    JOIN school_years sy ON sec.school_year_id = sy.school_year_id
    WHERE sec.section_id = ?
      AND (sec.created_by_teacher_id = ? OR sec.adviser_teacher_id = ?)
");
$stmt->execute([$section_id, $current_user['teacher_id'], $current_user['teacher_id']]);
$section = $stmt->fetch();

if (!$section) {
    die("Access denied: You do not manage this section.");
}

// Fetch current subjects
$stmt = $pdo->prepare("
    SELECT 
        ss.section_subject_id,
        ss.teacher_id AS current_teacher_id,
        subj.subject_id,
        subj.subject_code,
        subj.subject_name,
        t.first_name,
        t.last_name
    FROM section_subjects ss
    JOIN subjects subj ON ss.subject_id = subj.subject_id
    LEFT JOIN teachers t ON ss.teacher_id = t.teacher_id
    WHERE ss.section_id = ?
    ORDER BY subj.subject_name
");
$stmt->execute([$section_id]);
$current_subjects = $stmt->fetchAll();

// All subjects and teachers
$stmt = $pdo->prepare("SELECT subject_id, subject_code, subject_name FROM subjects WHERE school_id = ? ORDER BY subject_name");
$stmt->execute([$_SESSION['school_id']]);
$all_subjects = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT t.teacher_id, t.first_name, t.last_name
    FROM teachers t
    JOIN users u ON t.user_id = u.user_id
    WHERE u.school_id = ? AND u.is_active = TRUE
    ORDER BY t.last_name, t.first_name
");
$stmt->execute([$_SESSION['school_id']]);
$all_teachers = $stmt->fetchAll();

$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $section_subject_id = (int)($_POST['section_subject_id'] ?? 0);

    if ($subject_id <= 0 || $teacher_id <= 0) {
        $error = "Please select both subject and teacher.";
    } else {
        try {
            if ($action === 'add') {
                // Check if already exists
                $stmt = $pdo->prepare("SELECT section_subject_id FROM section_subjects WHERE section_id = ? AND subject_id = ?");
                $stmt->execute([$section_id, $subject_id]);
                if ($stmt->rowCount() > 0) {
                    $error = "This subject is already assigned to the section.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO section_subjects (section_id, subject_id, teacher_id) VALUES (?, ?, ?)");
                    $stmt->execute([$section_id, $subject_id, $teacher_id]);
                    $success = "Subject assigned successfully!";
                }
            } elseif ($action === 'update' && $section_subject_id > 0) {
                // Overwrite existing assignment
                $stmt = $pdo->prepare("UPDATE section_subjects SET teacher_id = ? WHERE section_subject_id = ? AND section_id = ?");
                $stmt->execute([$teacher_id, $section_subject_id, $section_id]);
                $success = "Teacher reassigned successfully!";
            }

            if ($success) {
                header("Location: manage_subjects.php?section_id=$section_id&success=1");
                exit;
            }
        } catch (Exception $e) {
            $error = "Operation failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - <?= htmlspecialchars($section['name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #1e6d4a; }
        .back { margin: 20px 0; }
        .success { color: green; background: #e6ffe6; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { color: red; background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background: #f0f0f0; }
        select, button { padding: 10px; font-size: 16px; }
        .form-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin: 20px 0; }
        .btn { background: #1e6d4a; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; display: inline-block; font-size: 14px; }
        .btn:hover { background: #165c3a; }
        .btn-small { padding: 6px 12px; font-size: 13px; }
        .confirm-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Subjects: <?= htmlspecialchars($section['name']) ?></h2>
    <p><strong>School Year:</strong> <?= htmlspecialchars($section['year_label']) ?></p>

    <div class="back">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <?php if ($_GET['success'] ?? false): ?>
        <div class="success">
            <?= $success ?: "Operation completed successfully!" ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <h3>➕ Assign New Subject</h3>
    <form method="post" class="form-row">
        <input type="hidden" name="action" value="add">
        <select name="subject_id" required style="flex:1; min-width:200px;">
            <option value="">-- Select Subject --</option>
            <?php foreach ($all_subjects as $subj): ?>
                <?php 
                $already_assigned = false;
                foreach ($current_subjects as $cs) {
                    if ($cs['subject_id'] == $subj['subject_id']) {
                        $already_assigned = true;
                        break;
                    }
                }
                if ($already_assigned) continue;
                ?>
                <option value="<?= $subj['subject_id'] ?>">
                    <?= htmlspecialchars($subj['subject_code'] . ' - ' . $subj['subject_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="teacher_id" required style="flex:1; min-width:200px;">
            <option value="">-- Assign Teacher --</option>
            <?php foreach ($all_teachers as $t): ?>
                <option value="<?= $t['teacher_id'] ?>">
                    <?= htmlspecialchars($t['last_name'] . ', ' . $t['first_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Assign Subject</button>
    </form>

    <h3>📚 Current Subjects in This Section</h3>
    <?php if (empty($current_subjects)): ?>
        <p>No subjects assigned yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Current Teacher</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($current_subjects as $cs): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cs['subject_code'] . ' - ' . $cs['subject_name']) ?></strong></td>
                    <td>
                        <?= $cs['first_name'] && $cs['last_name'] 
                            ? htmlspecialchars($cs['last_name'] . ', ' . $cs['first_name'])
                            : '<em>None</em>' ?>
                    </td>
                    <td>
                        <button onclick="showReassign(<?= $cs['section_subject_id'] ?>, <?= $cs['subject_id'] ?>, '<?= htmlspecialchars(addslashes($cs['subject_name']), ENT_QUOTES) ?>', <?= $cs['current_teacher_id'] ?? 'null' ?>)"
                                class="btn btn-small">
                            Change Teacher
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Reassign Modal -->
    <div id="reassignModal" style="display:none;" class="confirm-box">
        <h3>Reassign Teacher</h3>
        <p>Are you sure you want to change the teacher for <strong id="subjectName"></strong>?</p>
        <form method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="section_subject_id" id="sectionSubjectId">
            <input type="hidden" name="subject_id" id="modalSubjectId">

            <select name="teacher_id" id="newTeacher" required style="width:100%; padding:10px; margin:10px 0;">
                <option value="">-- Select New Teacher --</option>
                <?php foreach ($all_teachers as $t): ?>
                    <option value="<?= $t['teacher_id'] ?>">
                        <?= htmlspecialchars($t['last_name'] . ', ' . $t['first_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" style="background:#d32f2f; margin-right:10px;">Yes, Reassign</button>
            <button type="button" onclick="document.getElementById('reassignModal').style.display='none';">Cancel</button>
        </form>
    </div>
</div>

<script>
function showReassign(ss_id, subj_id, subj_name, current_teacher_id) {
    document.getElementById('reassignModal').style.display = 'block';
    document.getElementById('subjectName').textContent = subj_name;
    document.getElementById('sectionSubjectId').value = ss_id;
    document.getElementById('modalSubjectId').value = subj_id;
    
    // Pre-select current teacher
    const select = document.getElementById('newTeacher');
    select.value = current_teacher_id || '';
}
</script>
</body>
</html>