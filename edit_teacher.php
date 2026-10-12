<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
    $name           = trim($_POST['name']);
    $father         = trim($_POST['father_name']);
    $address        = trim($_POST['address']);
    $assigned_class = trim($_POST['assigned_class']);
    $subject_taught = trim($_POST['subject_taught']);
    $password       = trim($_POST['password']);

    // Teachers table update
    $up_t = $db->prepare("UPDATE teachers SET name = ?, father_name = ?, address = ?, assigned_class = ?, subject_taught = ? WHERE id = ?");
    $up_t->execute([$name, $father, $address, $assigned_class, $subject_taught, $id]);

    // Users login table update
    if (!empty($password)) {
        $up_u = $db->prepare("UPDATE users SET name = ?, password = ? WHERE email = ?");
        $up_u->execute([$name, $password, $teacher['email']]);
    } else {
        $up_u = $db->prepare("UPDATE users SET name = ? WHERE email = ?");
        $up_u->execute([$name, $teacher['email']]);
    }

    // Direct Home (Dashboard) Redirect
    header("Location: dashboard.php?msg=teacher_updated");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-custom p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Teacher Details (Admin Only)
                </h5>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Back to Hub
                </a>
            </div>

            <form method="POST">
                <input type="hidden" name="update_teacher" value="1">
                
                <div class="row g-2">
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Teacher Reg Number (Locked)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($teacher['reg_no']) ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Email Address (Locked)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($teacher['email']) ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($teacher['name']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Father's Name *</label>
                        <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars($teacher['father_name']) ?>" required>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="small fw-bold">Address *</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($teacher['address']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Assigned Class / Department *</label>
                        <input type="text" name="assigned_class" class="form-control" value="<?= htmlspecialchars($teacher['assigned_class']) ?>" placeholder="e.g. BCA" required>
                        <small class="text-muted" style="font-size: 11px;">Isi class ke students teacher ke panel me dikhenge.</small>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="small fw-bold">Subject Taught *</label>
                        <input type="text" name="subject_taught" class="form-control" value="<?= htmlspecialchars($teacher['subject_taught']) ?>" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="small fw-bold">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Khali chhod dein agar password nahi badalna">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="dashboard.php" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
