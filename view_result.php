<?php
require_once '../includes/auth_check.php';
check_auth('student');
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$marks = $conn->query("SELECT * FROM marks WHERE student_id = $student_id");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h3>Exam Marks & Evaluation</h3>
<div class="card p-3 shadow-sm mt-3">
    <table class="table table-striped table-bordered">
        <thead>
            <tr><th>Subject</th><th>Score Obtained</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if($marks && $marks->num_rows > 0): ?>
                <?php while($m = $marks->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($m['subject']) ?></td>
                    <td><?= $m['score'] ?> / 100</td>
                    <td>
                        <span class="badge bg-<?= $m['score'] >= 40 ? 'success' : 'danger' ?>">
                            <?= $m['score'] >= 40 ? 'Passed' : 'Failed' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center">No examination marks published yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
