<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    header("Location: dashboard.php");
    exit;
}

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_photo'])) {
    if (isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['teacher_photo']['tmp_name'];
        $file_name = $_FILES['teacher_photo']['name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $target_dir = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            if (!is_dir($target_dir)) {
                @mkdir($target_dir, 0777, true);
            }

            $new_filename = 'fac_photo_' . $teacher_id . '_' . time() . '.' . $ext;
            $dest_path    = $target_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                if (!empty($teacher['doc_proof'])) {
                    $old_file = $target_dir . $teacher['doc_proof'];
                    if (file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }

                $up = $db->prepare("UPDATE teachers SET doc_proof = ? WHERE id = ?");
                $done = $up->execute([$new_filename, $teacher_id]);

                if ($done) {
                    $teacher['doc_proof'] = $new_filename;
                    $msg = "Teacher photo successfully update ho gayi! Faculty ID Card par set ho chuki hai.";
                } else {
                    $error = "Database update fail ho gaya!";
                }
            } else {
                $error = "Upload directory me file move nahi ho saki. Permissions check karein.";
            }
        } else {
            $error = "Sirf JPG, JPEG, PNG ya WEBP format ki image upload karein.";
        }
    } else {
        $error = "Kripya upload ke liye image select karein.";
    }
}

$uploads_system_path = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$photo_exists = (!empty($teacher['doc_proof']) && file_exists($uploads_system_path . $teacher['doc_proof']));
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card card-custom p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-camera-fill text-primary me-2"></i>Manage Teacher Photo
                </h6>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success py-2 small rounded-3"><?= $msg ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small rounded-3"><?= $error ?></div>
            <?php endif; ?>

            <div class="text-center my-3">
                <div style="width: 130px; height: 155px; margin: 0 auto; border: 3px solid #1e3a8a; border-radius: 8px; overflow: hidden; background: #f8fafc;" class="shadow-sm">
                    <?php if ($photo_exists): ?>
                        <img src="../uploads/<?= htmlspecialchars($teacher['doc_proof']) ?>?t=<?= time() ?>" alt="Teacher Photo" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small">
                            <i class="bi bi-person fs-1"></i>
                            <span>No Photo</span>
                        </div>
                    <?php endif; ?>
                </div>
                <h6 class="fw-bold text-dark mt-2 mb-0"><?= htmlspecialchars($teacher['name']) ?></h6>
                <span class="badge bg-primary text-white my-1"><?= htmlspecialchars($teacher['reg_no'] ?? 'N/A') ?></span>
                <div class="small text-muted"><?= htmlspecialchars($teacher['subject_taught'] ?? '') ?> (<?= htmlspecialchars($teacher['assigned_class'] ?? '') ?>)</div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_photo" value="1">
                <div class="mb-3">
                    <label class="small fw-bold text-dark">Select Passport Size Photo *</label>
                    <input type="file" name="teacher_photo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                    <small class="text-muted" style="font-size: 11px;">Yeh photo teacher ke digital Navy Blue ID card par live show hogi.</small>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save Teacher Photo
                    </button>
                    <a href="dashboard.php" class="btn btn-light rounded-pill">Done / Return to Console</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
