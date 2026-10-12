<?php
require_once '../includes/auth_check.php';
check_auth('faculty');
require_once '../config/db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $date = $_POST['attendance_date'];
    $records = $_POST['status'] ?? [];

    foreach ($records as $student_id => $status) {
        // Purana record check karein taaki duplicate na ho
        $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
        $check->bind_param("is", $student_id, $date);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND date = ?");
            $stmt->bind_param("sis", $status, $student_id, $date);
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $student_id, $date, $status);
        }
        $stmt->execute();
    }
    $msg = "Attendance successfully save ho gayi!";
}

$students = $conn->query("SELECT id, name, email FROM users WHERE role = 'student' ORDER BY name ASC");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h3>Mark Daily Attendance</h3>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= $msg ?></div>
<?php endif; ?>

<div class="card p-3 shadow-sm">
    <form method="POST">
        <input type="hidden" name="submit_attendance" value="1">
        <div class="mb-3 w-25">
            <label class="fw-bold">Date Choose Karein:</label>
            <input type="date" name="attendance_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Roll / ID</th>
                    <th>Student Name</th>
                    <th>Present</th>
                    <th>Absent</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($st = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= $st['id'] ?></td>
                    <td><?= htmlspecialchars($st['name']) ?></td>
                    <td>
                        <input type="radio" name="status[<?= $st['id'] ?>]" value="Present" checked>
                    </td>
                    <td>
                        <input type="radio" name="status[<?= $st['id'] ?>]" value="Absent">
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button class="btn btn-success px-4">Save Attendance</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
