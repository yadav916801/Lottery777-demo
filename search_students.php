<?php
// Role-based verification
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'faculty'])) {
    exit('Access Denied');
}

$is_admin = ($_SESSION['user_role'] === 'admin');
$assigned_batch = $_SESSION['assigned_class'] ?? '';

// Fetch data based on role
if ($is_admin) {
    $search_query = $db->query("SELECT id, name, father_name, roll_no, email, course, course_year, semester, id_proof, status FROM users WHERE role = 'student' ORDER BY id DESC");
} else {
    // Teacher gets only their assigned batch
    $clean_kw = explode(' ', $assigned_batch)[0];
    $search_query = $db->prepare("SELECT id, name, father_name, roll_no, email, course, course_year, semester, id_proof, status 
                                  FROM users 
                                  WHERE role = 'student' 
                                    AND status = 'approved' 
                                    AND (LOWER(TRIM(course)) LIKE LOWER(:kw) OR LOWER(:cls) LIKE '%' || LOWER(TRIM(course)) || '%') 
                                  ORDER BY roll_no ASC");
    $search_query->execute([
        ':kw'  => '%' . $clean_kw . '%',
        ':cls' => $assigned_batch
    ]);
}
$all_students = $search_query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card card-custom p-3 bg-white shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-bounding-box text-primary me-2"></i>Student Face & ID Lookup Desk
            </h6>
            <small class="text-muted">Instant live search by Roll Number, Name or Department</small>
        </div>
        <div style="min-width: 260px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="liveSearchInput" class="form-control bg-light border-start-0" placeholder="Type Name, Roll No or Course..." onkeyup="filterStudentCards()">
            </div>
        </div>
    </div>

    <!-- Student Cards Grid -->
    <div class="row g-3" id="studentsCardContainer">
        <?php if (count($all_students) > 0): ?>
            <?php foreach ($all_students as $st): 
                $photo_src = (!empty($st['id_proof']) && file_exists(__DIR__ . '/../uploads/' . $st['id_proof']))
                    ? '../uploads/' . htmlspecialchars($st['id_proof'])
                    : 'https://via.placeholder.com/150x180/7a1518/ffffff?text=NO+FACE';
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3 student-item" 
                 data-name="<?= strtolower(htmlspecialchars($st['name'])) ?>" 
                 data-roll="<?= strtolower(htmlspecialchars($st['roll_no'] ?? '')) ?>" 
                 data-course="<?= strtolower(htmlspecialchars($st['course'] ?? '')) ?>">
                <div class="card h-100 border rounded-3 p-2 text-center bg-light shadow-sm">
                    <!-- Face Display Thumbnail -->
                    <div class="mx-auto my-2" style="width: 90px; height: 105px; border-radius: 8px; border: 2px solid #7a1518; overflow: hidden; background: #fff;">
                        <img src="<?= $photo_src ?>" alt="Face" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <strong class="text-dark small text-truncate d-block"><?= htmlspecialchars($st['name']) ?></strong>
                    <span class="badge bg-warning text-dark mx-auto my-1" style="font-size: 11px;"><?= htmlspecialchars($st['roll_no'] ?? 'N/A') ?></span>
                    <div class="small text-muted" style="font-size: 11px;"><?= htmlspecialchars($st['course'] ?? '') ?> (<?= htmlspecialchars($st['semester'] ?? '') ?>)</div>
                    
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2 py-0 px-3 fw-semibold" 
                            onclick="openFaceModal('<?= htmlspecialchars($st['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($st['father_name'] ?? 'N/A', ENT_QUOTES) ?>', '<?= htmlspecialchars($st['roll_no'] ?? 'N/A') ?>', '<?= htmlspecialchars($st['course'] ?? '') ?>', '<?= htmlspecialchars($st['semester'] ?? '') ?>', '<?= $photo_src ?>')">
                        <i class="bi bi-eye-fill me-1"></i> Verify Face
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4 text-muted">Koi student data uplabdh nahi hai.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Face & ID Verification Modal Popup -->
<div class="modal fade" id="faceViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-shield-check text-success me-2"></i>Verified Identity & Face Record</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-2 pb-4">
                <div class="mx-auto my-3 shadow" style="width: 140px; height: 170px; border-radius: 10px; border: 3px solid #7a1518; overflow: hidden; background: #fff;">
                    <img id="modalFaceImg" src="" alt="Face Full View" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h5 class="fw-bold text-dark mb-1" id="modalStudentName"></h5>
                <span class="badge bg-warning text-dark fs-6 px-3 py-1 rounded-pill mb-3" id="modalRollNo"></span>

                <div class="card bg-light border-0 p-3 mx-2 text-start small">
                    <div class="mb-1"><strong>Father's Name:</strong> <span id="modalFather" class="text-secondary"></span></div>
                    <div class="mb-1"><strong>Enrolled Course:</strong> <span id="modalCourse" class="text-secondary"></span></div>
                    <div><strong>Current Semester:</strong> <span id="modalSem" class="text-secondary"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time Card Search Filter
function filterStudentCards() {
    const input = document.getElementById('liveSearchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.student-item');

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const roll = card.getAttribute('data-roll');
        const course = card.getAttribute('data-course');

        if (name.includes(input) || roll.includes(input) || course.includes(input)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Show Verification Modal
function openFaceModal(name, father, roll, course, sem, photo) {
    document.getElementById('modalStudentName').innerText = name;
    document.getElementById('modalFather').innerText = father;
    document.getElementById('modalRollNo').innerText = "Roll: " + roll;
    document.getElementById('modalCourse').innerText = course;
    document.getElementById('modalSem').innerText = sem;
    document.getElementById('modalFaceImg').src = photo;

    const modalEl = document.getElementById('faceViewModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>
