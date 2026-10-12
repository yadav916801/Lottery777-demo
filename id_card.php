<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$st = $db->query("SELECT * FROM users WHERE id = $student_id")->fetch(PDO::FETCH_ASSOC);

$roll_no = htmlspecialchars($st['roll_no'] ?? 'FSU26040127');
$barcode_url = "https://barcodeapi.org/api/128/" . urlencode($roll_no);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($roll_no);
?>

<style>
.id-card-wrapper {
    display: flex;
    justify-content: center;
    gap: 25px;
    flex-wrap: wrap;
    padding: 15px 0 40px;
}
.fsu-card {
    width: 320px;
    height: 520px;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.18);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #f5dc00;
    border: 1px solid #d4be00;
    font-family: Arial, Helvetica, sans-serif;
}
.fsu-back {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 22px 18px 12px;
}
.header-emblem {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    padding-top: 15px;
}
.fsu-shield {
    background: #7a1518;
    color: #fff;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 900;
    line-height: 1;
    border: 2px solid #fff;
    text-align: center;
}
.fsu-title-block {
    display: flex;
    align-items: center;
    border: 2px solid #7a1518;
}
.fsu-badge-red {
    background: #7a1518;
    color: #f5dc00;
    font-size: 28px;
    font-weight: 900;
    padding: 0 6px;
    line-height: 1.1;
}
.fsu-uni-text {
    color: #7a1518;
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 2px;
    padding: 0 8px;
}
.photo-box {
    width: 140px;
    height: 165px;
    background: #fff;
    border: 3px solid #7a1518;
    margin: 8px auto 0;
    overflow: hidden;
}
.photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.student-name {
    color: #1e293b;
    font-weight: 900;
    font-size: 17px;
    text-transform: uppercase;
    margin-top: 8px;
}
.barcode-box img {
    height: 40px;
    width: 80%;
    object-fit: fill;
}
.roll-text {
    font-size: 15px;
    font-weight: 800;
    color: #000;
    letter-spacing: 0.5px;
}
.course-text {
    font-size: 16px;
    font-weight: 900;
    color: #000;
    text-transform: uppercase;
}
.session-text {
    font-size: 13px;
    font-weight: 800;
    color: #000;
}
.registrar-sign {
    position: absolute;
    bottom: 8px;
    right: 14px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: #000;
}
.back-row {
    font-size: 12px;
    color: #1e293b;
    margin-bottom: 6px;
    line-height: 1.35;
}
.rules-list {
    border-top: 1px dashed #64748b;
    padding-top: 10px;
    margin-top: 12px;
    font-size: 11px;
    color: #334155;
    line-height: 1.4;
}
.footer-addr {
    border-top: 2px solid #000;
    padding-top: 8px;
    font-size: 9px;
    text-align: center;
    color: #0f172a;
    line-height: 1.25;
}
@media print {
    body * { visibility: hidden; }
    .id-card-wrapper, .id-card-wrapper * { visibility: visible; }
    .id-card-wrapper { position: absolute; top: 0; left: 0; }
    .no-print { display: none !important; }
}
</style>

<div class="container text-center">
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm me-2">
            <i class="bi bi-printer me-1"></i> Print Student Card
        </button>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-3">Back</a>
    </div>

    <div class="id-card-wrapper">
        <!-- FRONT SIDE -->
        <div class="fsu-card">
            <div>
                <div class="header-emblem">
                    <div class="fsu-shield">FS<br><span style="font-size: 7px;">UNIVERSITY</span></div>
                    <div class="fsu-title-block">
                        <div class="fsu-badge-red">FS</div>
                        <div class="fsu-uni-text">UNIVERSITY</div>
                    </div>
                </div>
                <div style="font-size: 8px; font-weight: 800; color: #7a1518; letter-spacing: 1px; margin-top: 2px;">
                    SHIKOHABAD • UTTAR PRADESH
                </div>

                <div class="photo-box">
                    <?php if (!empty($st['id_proof']) && file_exists('../uploads/' . $st['id_proof'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($st['id_proof']) ?>" alt="Photo">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/150x180/7a1518/ffffff?text=STUDENT+PHOTO" alt="Photo">
                    <?php endif; ?>
                </div>

                <div class="student-name"><?= htmlspecialchars($st['name']) ?></div>
            </div>

            <div class="pb-2">
                <div class="barcode-box">
                    <img src="<?= $barcode_url ?>" alt="Barcode">
                </div>
                <div class="roll-text"><?= $roll_no ?></div>
                <div class="course-text"><?= htmlspecialchars($st['course'] ?? 'B.C.A.') ?></div>
                <div class="session-text"><?= htmlspecialchars($st['course_year'] ?? '2026-2029') ?></div>
            </div>

            <div class="registrar-sign">
                <div style="font-family: cursive; font-size: 13px; line-height: 1;">Registrar</div>
                Registrar
            </div>
        </div>

        <!-- BACK SIDE -->
        <div class="fsu-card fsu-back text-start">
            <div>
                <div class="back-row"><strong>Father's Name</strong> : <?= htmlspecialchars($st['father_name'] ?? 'Jagdeesh') ?></div>
                <div class="back-row"><strong>Date of Birth</strong> : <?= htmlspecialchars($st['dob'] ?? '08/08/2006') ?></div>
                <div class="back-row"><strong>Blood Group</strong> : <?= htmlspecialchars($st['blood_group'] ?? 'O+') ?></div>
                <div class="back-row"><strong>Address</strong> : <?= htmlspecialchars($st['address'] ?? 'Firozabad, Uttar Pradesh') ?></div>
                <div class="back-row"><strong>Emergency Contact</strong> : <?= htmlspecialchars($st['contact'] ?? '9876543210') ?></div>

                <div class="rules-list">
                    1- Student must carry and wear the identity card during the campus.<br>
                    2- The responsibility of the card will be ID card holder.<br>
                    3- Every new card will be issued @ Rs 500 in case of damage/Loss.
                </div>
            </div>

            <div class="text-center my-1">
                <img src="<?= $qr_url ?>" style="width: 55px; height: 55px;" alt="QR Attendance">
            </div>

            <div class="footer-addr">
                <strong>NH-19, Agra Road, Near Balaji Mandir,<br>Shikohabad, Firozabad, Uttar Pradesh-283135</strong><br>
                www.fsu.edu.in, +91 9172866666, 9675111147
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
