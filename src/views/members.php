<?php
require_once __DIR__ . '/../config/database.php';
ob_start();

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT u.id, u.name, u.profile_picture, u.occupation, u.state_code, u.phone, u.email, u.business_id, u.contact_private, r.display_name as role_name, r.name as role_key, r.badge_color 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE r.name = 'member' AND (u.name LIKE ? OR u.occupation LIKE ?) 
        ORDER BY u.id DESC LIMIT 30";
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$members = $result->fetch_all(MYSQLI_ASSOC);

// Fetch skills and business for each member
function getSkills($conn, $user_id) {
    $stmt = $conn->prepare("SELECT s.name FROM skills s JOIN user_skills us ON s.id = us.skill_id WHERE us.user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $skills = [];
    while ($row = $result->fetch_assoc()) {
        $skills[] = $row['name'];
    }
    return $skills;
}
function getBusiness($conn, $business_id) {
    if (!$business_id) return null;
    $stmt = $conn->prepare("SELECT name, description FROM businesses WHERE id = ?");
    $stmt->bind_param('i', $business_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
<?php include __DIR__ . '/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Members Directory - Corper Connect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../css/tailwind.css" rel="stylesheet">
  <style>
    body, html { height: 100%; margin: 0; }
    .member-card { background: rgba(22, 101, 52, 0.06); border-radius: 1.2rem; box-shadow: 0 4px 16px 0 rgba(22,101,52,0.10); border: 1px solid #e0e0e0; padding: 1.2rem 1rem 1.5rem 1rem; display: flex; flex-direction: column; align-items: center; text-align: center; min-width: 0; transition: box-shadow 0.3s, transform 0.3s; position: relative; }
    .member-card:hover { box-shadow: 0 8px 32px 0 rgba(22,101,52,0.18); transform: translateY(-4px) scale(1.03); }
    .member-avatar { width: 56px; height: 56px; border-radius: 50%; border: 2px solid #16a34a; object-fit: cover; background: #f0fdf4; margin-bottom: 0.5rem; }
    .member-name { font-weight: bold; color: #14532d; font-size: 1rem; margin-bottom: 0.2rem; width: 100%; letter-spacing: -0.5px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
    .member-occupation { color: #198754; font-size: 0.92rem; margin-bottom: 0.5rem; width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
    .member-skills { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.25rem; margin-bottom: 0.5rem; }
    .member-skill-badge { background: #e6f4ea; color: #198754; border-radius: 999px; padding: 0.2rem 0.7rem; font-size: 0.75rem; font-weight: 500; box-shadow: 0 1px 2px #e0e0e0; }
    .member-business { color: #555; font-size: 0.82rem; margin-bottom: 0.3rem; width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
    .badge-supervisor { background: #1e293b; color: #fff; }
    .badge-president { background: #14532d; color: #fff; }
    .badge-vice-president { background: #15803d; color: #fff; }
    .badge-pro { background: #0d9488; color: #fff; }
    .badge-treasurer { background: #f59e42; color: #fff; }
    .badge-exco { background: #6366f1; color: #fff; }
    .badge-member { background: #e2e8f0; color: #222; }
    .member-badge { position: absolute; top: 0.7rem; left: 0.7rem; z-index: 2; font-size: 0.85rem; letter-spacing: 0.5px; margin-bottom: 0; display: inline-block; padding: 0.3rem 1rem; border-radius: 999px; box-shadow: 0 1px 4px #e0e0e0; }
    .member-details-btn { background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%); color: #fff; border: none; border-radius: 999px; padding: 0.4rem 1.2rem; font-weight: 600; font-size: 0.98rem; margin-top: 0.5rem; box-shadow: 0 2px 8px 0 rgba(22,101,52,0.10); transition: background 0.2s, transform 0.2s; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; }
    .member-details-btn:hover { background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%); color: #fff; transform: scale(1.04); }
    @media (max-width: 575.98px) { .member-card { padding: 0.8rem 0.5rem 1rem 0.5rem; border-radius: 0.8rem; } .member-avatar { width: 44px; height: 44px; } }
  </style>
</head>
<body>

<!-- Members Directory Section -->
<section class="py-5 bg-green-50">
  <div class="container">
    <h2 class="display-5 fw-bold text-center text-success mb-4">Members Directory</h2>
    <form method="GET" class="mb-4 d-flex gap-2 justify-content-center">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search members..." class="form-control w-50" style="max-width:400px;">
      <button type="submit" class="btn btn-success px-4">Search</button>
    </form>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 justify-content-center">
      <?php foreach ($members as $member): 
        $skills = getSkills($conn, $member['id']);
        $business = getBusiness($conn, $member['business_id']);
        $is_private = isset($member['contact_private']) && $member['contact_private'];
        $profile_picture = trim($member['profile_picture']);
        $is_valid_picture = $profile_picture && strtolower($profile_picture) !== 'profile picture';
        if ($is_valid_picture && strpos($profile_picture, '/public/uploads/') === 0) {
            $profile_picture = '/ICT-Corps-Members-Hub' . $profile_picture;
        }
        $role_key = strtolower($member['role_key'] ?? 'member');
        $badgeClass = 'badge-' . str_replace('_', '-', $role_key);
      ?>
      <div class="col">
        <div class="member-card">
          <?php if (!empty($member['role_name']) && $role_key !== 'member'): ?>
            <span class="member-badge <?= htmlspecialchars($badgeClass) ?> fw-semibold text-xs shadow-sm">
              <?= htmlspecialchars($member['role_name']) ?>
            </span>
          <?php endif; ?>
          <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="member-avatar" alt="<?= htmlspecialchars($member['name']) ?>">
          <div class="member-name"> <?= htmlspecialchars($member['name']) ?> </div>
          <div class="member-occupation"> <?= htmlspecialchars($member['occupation']) ?> </div>
          <div class="member-skills">
            <?php foreach ($skills as $skill): ?>
              <span class="member-skill-badge"> <?= htmlspecialchars($skill) ?> </span>
            <?php endforeach; ?>
          </div>
          <?php if ($business): ?>
            <div class="member-business">Business: <span class="fw-semibold"><?= htmlspecialchars($business['name']) ?></span></div>
          <?php endif; ?>
          <button type="button" class="member-details-btn" data-bs-toggle="modal" data-bs-target="#memberModal<?= $member['id'] ?>">
            <i class="fa fa-info-circle"></i> Details
          </button>
        </div>
      </div>
      <!-- Member Modal -->
      <div class="modal fade" id="memberModal<?= $member['id'] ?>" tabindex="-1" aria-labelledby="memberModalLabel<?= $member['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="memberModalLabel<?= $member['id'] ?>">Member Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
              <div class="d-flex justify-content-center w-100 mb-3">
                <img src="<?= $is_valid_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="rounded-circle border border-3 border-success shadow mx-auto d-block" style="width: 90px; height: 90px; object-fit: cover; background: #f0fdf4;" alt="<?= htmlspecialchars($member['name']) ?>">
              </div>
              <h5 class="fw-bold mb-1"> <?= htmlspecialchars($member['name']) ?> </h5>
              <?php if (!empty($member['role_name'])): ?>
                <div class="mb-2"><span class="badge <?= htmlspecialchars($badgeClass) ?> px-3 py-1"> <?= htmlspecialchars($member['role_name']) ?> </span></div>
              <?php endif; ?>
              <div class="mb-2 text-success-emphasis"> <?= htmlspecialchars($member['occupation']) ?> </div>
              <div class="mb-2">
                <?php foreach ($skills as $skill): ?>
                  <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-2 py-1 shadow-sm mb-1" style="font-size: 0.85rem;"> <?= htmlspecialchars($skill) ?> </span>
                <?php endforeach; ?>
              </div>
              <?php if ($business): ?>
                <div class="mb-2 text-secondary-emphasis">Business: <span class="fw-semibold"> <?= htmlspecialchars($business['name']) ?> </span></div>
                <div class="mb-2 text-muted small"> <?= htmlspecialchars($business['description']) ?> </div>
              <?php endif; ?>
              <div class="mb-2">
                <?php if (!$is_private && $member['state_code']): ?>
                  <span class="badge bg-light text-success border px-3 py-1">State Code: <?= htmlspecialchars($member['state_code']) ?></span>
                <?php else: ?>
                  <span class="text-xs text-gray-400 italic">State code private</span>
                <?php endif; ?>
              </div>
              <div class="mb-2">
                <?php if (!$is_private && $member['phone']): ?>
                  <a href="tel:<?= htmlspecialchars($member['phone']) ?>" class="btn btn-outline-success btn-sm me-2">Phone</a>
                <?php endif; ?>
                <?php if (!$is_private && $member['email']): ?>
                  <a href="mailto:<?= htmlspecialchars($member['email']) ?>" class="btn btn-outline-success btn-sm">Email</a>
                <?php endif; ?>
                <?php if ($is_private): ?>
                  <span class="text-xs text-gray-400 italic">Contact info private</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Pagination or Show More can be added here -->
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include __DIR__ . '/footer.php'; ?>
