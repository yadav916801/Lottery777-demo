<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'faculty') {
    header("Location: ../login.php");
    exit;
}
?>

<!-- HTML5 QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode"></script>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card card-custom p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-qr-code-scan text-primary me-2"></i>QR Code Attendance Scanner
                    </h5>
                    <small class="text-muted">Student ID Card scan karein automatic present lagane ke liye</small>
                </div>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left"></i> Back to Class
                </a>
            </div>

            <!-- Date Selector -->
            <div class="mb-3 d-flex align-items-center gap-2">
                <label class="small fw-bold text-nowrap">Attendance Date:</label>
                <input type="date" id="scan_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Live Camera Viewport -->
            <div id="reader" style="width: 100%; border-radius: 12px; overflow: hidden; border: 2px dashed #0284c7;" class="bg-light mb-3"></div>

            <!-- Instant Scan Response Box -->
            <div id="scanResultBox" class="alert alert-success d-none py-2 px-3 rounded-3 shadow-sm mb-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-3 text-success me-2"></i>
                    <div>
                        <strong id="resName" class="d-block text-dark"></strong>
                        <small id="resRoll" class="text-muted"></small>
                    </div>
                </div>
            </div>

            <div id="scanErrorBox" class="alert alert-danger d-none py-2 px-3 rounded-3 shadow-sm mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="errorMsg"></span>
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary rounded-pill fw-semibold" id="startBtn" onclick="startScanner()">
                    <i class="bi bi-camera-fill me-1"></i> Start Camera Scanner
                </button>
                <button type="button" class="btn btn-danger rounded-pill fw-semibold d-none" id="stopBtn" onclick="stopScanner()">
                    <i class="bi bi-stop-circle me-1"></i> Stop Camera
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let html5QrCode = null;
let lastScannedCode = null;
let isProcessing = false;

function startScanner() {
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    html5QrCode.start(
        { facingMode: "environment" }, // Back Camera
        config,
        onScanSuccess
    ).then(() => {
        document.getElementById('startBtn').classList.add('d-none');
        document.getElementById('stopBtn').classList.remove('d-none');
    }).catch(err => {
        alert("Camera access allow karein: " + err);
    });
}

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('startBtn').classList.remove('d-none');
            document.getElementById('stopBtn').classList.add('d-none');
        });
    }
}

function onScanSuccess(decodedText) {
    if (isProcessing || decodedText === lastScannedCode) return;

    isProcessing = true;
    lastScannedCode = decodedText;
    const selectedDate = document.getElementById('scan_date').value;

    const formData = new FormData();
    formData.append('roll_no', decodedText);
    formData.append('date', selectedDate);

    fetch('../ajax_qr_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const resBox = document.getElementById('scanResultBox');
        const errBox = document.getElementById('scanErrorBox');

        if (data.status === 'success') {
            errBox.classList.add('d-none');
            resBox.classList.remove('d-none');
            document.getElementById('resName').innerText = data.student_name + " (PRESENT)";
            document.getElementById('resRoll').innerText = "Roll: " + data.roll_no + " | " + data.course;
        } else {
            resBox.classList.add('d-none');
            errBox.classList.remove('d-none');
            document.getElementById('errorMsg').innerText = data.message;
        }

        // 2 second baad agla student scan karne ke liye ready
        setTimeout(() => {
            isProcessing = false;
            lastScannedCode = null;
        }, 2000);
    })
    .catch(err => {
        console.error(err);
        isProcessing = false;
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
