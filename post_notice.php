<?php
require_once '../includes/auth_check.php';
check_auth('admin');
require_once '../config/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    $stmt = $conn->prepare("INSERT INTO notices (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    if ($stmt->execute()) {
        $msg = "Notice posted successfully!";
    }
}

$notices = $conn->query("SELECT * FROM notices ORDER BY posted_date DESC");
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h3>Post a New Notice</h3>
<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<div class="card p-3 shadow-sm mb-4">
    <form method="POST">
        <div class="mb-3">
            <label>Notice Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Content / Description</label>
            <textarea name="content" class="form-control" rows="3" required></textarea>
        </div>
        <button class="btn btn-primary">Publish Notice</button>
    </form>
</div>

<h4>Recent Notices</h4>
<div class="list-group">
    <?php while($n = $notices->fetch_assoc()): ?>
        <div class="list-group-item">
            <h6><?= htmlspecialchars($n['title']) ?> <small class="text-muted float-end"><?= $n['posted_date'] ?></small></h6>
            <p class="mb-0 text-secondary"><?= nl2br(htmlspecialchars($n['content'])) ?></p>
        </div>
    <?php endwhile; ?>
</div>

<?php include '../includes/footer.php'; ?>
