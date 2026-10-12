<?php
require_once '../includes/auth_check.php';
check_auth('faculty');
require_once '../config/db.php';

// Create marks table if missing
$conn->query("CREATE TABLE IF NOT EXISTS marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
)");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $subject = trim($_POST['subject']);
    $score = intval($_POST['score']);

    $stmt = $conn->prepare("INSERT INTO marks (student_id, subject, score) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $student_id, $subject, $score);
    if ($stmt->execute()) {
        $msg = "Marks entered successfully!";
    }
}

$students = $conn->query("SELECT id, name FROM users WHERE role = 'student'");
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h3>Submit Student Marks</h3>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card p-3 shadow-sm w-50">
    <form method="POST">
        <div class="mb-3">
            <label>Student</label>
            <select name="student_id" class="form-select" required>
                <?php while($st = $students->fetch_assoc()): ?>
                    <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name']) ?> (ID: <?= $st['id'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" placeholder="e.g. Web Development" required>
        </div>
        <div class="mb-3">
            <label>Marks (out of 100)</label>
            <input type="number" name="score" max="100" min="0" class="form-control" required>
        </div>
        <button class="btn btn-success">Save Marks</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
