<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'faculty') {
    header("Location: ../login.php");
    exit;
}

$student_id = intval($_GET['id'] ?? 0);
$st_stmt = $db->prepare("SELECT id, name, roll_no, course, id_proof FROM users WHERE id = ? AND role = 'student'");
$st_stmt->execute([$student_id]);
$student = $st_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: dashboard.php");
    exit;
}

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_photo'])) {
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['student_photo']['tmp_name'];
        $file_name = $_FILES['student_photo']['name'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            // Root uploads folder path
            $target_dir = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

            // Agar folder nahi hai toh banao aur permission 0777 do
            if (!is_dir($target_dir)) {
                @mkdir($target_dir, 0777, true);
            }
            @chmod($target_dir, 0777);

            $new_filename = 'st_photo_' . $student_id . '_' . time() . '.' . $ext;
            $dest_path    = $target_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                // Purani photo delete karein agar maujood ho
                if (!empty($student['id_proof'])) {
                    $old_file = $target_dir . $student['id_proof'];
                    if (file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }

                // Database me photo update karein
                $up = $db->prepare("UPDATE users SET id_proof = ? WHERE id = ?");
                $done = $up->execute([$new_filename, $student_id]);

                if ($done) {
                    $student['id_proof'] = $new_filename;
                    $msg = "Photo successfully update ho gayi! Ab yeh Yellow ID Card aur Attendance Sheet par live dikhegi.";
                } else {
                    $error = "Database record update fail ho gaya!";
                }
            } else {
                $error = "Upload directory me write permission nahi hai. Kripya server par 'uploads' folder ko 777 permission dein.";
            }
        } else {
            $error = "Sirf JPG, JPEG, PNG ya WEBP format ki image upload karein.";
        }
    } else {
        $err_code = $_FILES['student_photo']['error'] ?? 4;
        if ($err_code === UPLOAD_ERR_NO_FILE) {
            $error = "Kripya upload karne ke liye photo select karein.";
        } else {
            $error = "Upload error code: " . $err_code;
        }
    }
}

// Check if photo exists on disk
$uploads_system_path = realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$photo_exists = (!empty($student['id_proof']) && file_exists($uploads_system_path . $student['id_proof']));
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card card-custom p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-camera-fill text-primary me-2"></i>Upload / Change Student Photo
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
                <div style="width: 130px; height: 155px; margin: 0 auto; border: 3px solid #7a1518; border-radius: 8px; overflow: hidden; background: #f8fafc;" class="shadow-sm">
                    <?php if ($photo_exists): ?>
                        <img src="../uploads/<?= htmlspecialchars($student['id_proof']) ?>?t=<?= time() ?>" alt="Student Photo" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted small">
                            <i class="bi bi-person fs-1"></i>
                            <span>No Photo</span>
                        </div>
                    <?php endif; ?>
                </div>
                <h6 class="fw-bold text-dark mt-2 mb-0"><?= htmlspecialchars($student['name']) ?></h6>
                <span class="badge bg-warning text-dark my-1"><?= htmlspecialchars($student['roll_no'] ?? 'N/A') ?></span>
                <div class="small text-muted"><?= htmlspecialchars($student['course'] ?? '') ?></div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="save_photo" value="1">
                <div class="mb-3">
                    <label class="small fw-bold text-dark">Select Passport Size Photo *</label>
                    <input type="file" name="student_photo" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                    <small class="text-muted" style="font-size: 11px;">Image choose karne ke baad niche diye button par click karein.</small>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill fw-semibold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Save & Set Photo
                    </button>
                    <a href="dashboard.php" class="btn btn-light rounded-pill">Done / Return to Class Desk</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
