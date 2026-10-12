<?php
require_once '../includes/auth_check.php';
check_auth('student');
require_once '../config/db.php';

$student_id = $_SESSION['user_id'];
$records = $conn->query("SELECT * FROM attendance WHERE student_id = $student_id ORDER BY date DESC");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h3>Complete Attendance Records</h3>
<div class="card p-3 shadow-sm mt-3">
    <table class="table table-bordered">
        <thead>
            <tr><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php while($r = $records->fetch_assoc()): ?>
            <tr>
                <td><?= $r['date'] ?></td>
                <td><span class="badge bg-<?= $r['status'] === 'Present' ? 'success' : 'danger' ?>"><?= $r['status'] ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
