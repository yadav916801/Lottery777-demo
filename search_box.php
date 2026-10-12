<div class="col-12 mb-4">
    <div class="card card-custom p-3 bg-white shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-bounding-box text-primary me-2"></i>Campus Face & ID Search Desk
                </h6>
                <small class="text-muted">Live search by Roll No, Name or Course</small>
            </div>
            <div style="min-width: 250px;">
                <input type="text" id="adminSearchInput" class="form-control form-control-sm bg-light" placeholder="Search Roll No, Name..." onkeyup="filterAdminSearch()">
            </div>
        </div>

        <div class="row g-2" id="adminStudentsContainer" style="max-height: 400px; overflow-y: auto;">
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $st): 
                    $photo_src = (!empty($st['id_proof']) && file_exists($uploads_system_path . $st['id_proof']))
                        ? '../uploads/' . htmlspecialchars($st['id_proof'])
                        : 'https://via.placeholder.com/150x180/7a1518/ffffff?text=NO+PHOTO';
                ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 admin-st-item"
                     data-name="<?= strtolower(htmlspecialchars($st['name'] ?? '')) ?>"
                     data-roll="<?= strtolower(htmlspecialchars($st['roll_no'] ?? '')) ?>"
                     data-course="<?= strtolower(htmlspecialchars($st['course'] ?? '')) ?>">
                    <div class="card h-100 border rounded-3 p-2 text-center bg-light shadow-sm">
                        <div class="mx-auto my-1" style="width: 75px; height: 90px; border-radius: 8px; border: 2px solid #7a1518; overflow: hidden; background: #fff;">
                            <img src="<?= $photo_src ?>" alt="Face" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <strong class="text-dark small text-truncate d-block mt-1"><?= htmlspecialchars($st['name']) ?></strong>
                        <span class="badge bg-warning text-dark mx-auto my-1" style="font-size: 10px;"><?= htmlspecialchars($st['roll_no'] ?? 'N/A') ?></span>
                        <div class="text-muted text-truncate" style="font-size: 10px;"><?= htmlspecialchars($st['course'] ?? '') ?></div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2 py-0 px-2" style="font-size: 11px;"
                                onclick="showAdminFaceModal('<?= htmlspecialchars($st['name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($st['father_name'] ?? 'N/A', ENT_QUOTES) ?>', '<?= htmlspecialchars($st['roll_no'] ?? 'N/A') ?>', '<?= htmlspecialchars($st['course'] ?? '') ?>', '<?= htmlspecialchars($st['semester'] ?? '') ?>', '<?= htmlspecialchars($st['email'] ?? '') ?>', '<?= $photo_src ?>')">
                            <i class="bi bi-eye-fill"></i> View Face
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-3 text-muted small">Koi approved student available nahi hai.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
