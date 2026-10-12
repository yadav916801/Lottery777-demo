<?php
require_once '../includes/auth_check.php';
check_auth('admin');
require_once '../config/db.php';

$faculty = $conn->query("SELECT * FROM users WHERE role = 'faculty'");
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h3>Registered Faculty Members</h3>
<div class="card p-3 shadow-sm mt-3">
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Faculty ID</th><th>Name</th><th>Email</th></tr>
        </thead>
        <tbody>
            <?php while($f = $faculty->fetch_assoc()): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['name']) ?></td>
                <td><?= htmlspecialchars($f['email']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
