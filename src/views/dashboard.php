<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /public/auth/login.php");
    exit;
}
require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch all skills
$skills = [];
$result = $conn->query("SELECT id, name FROM skills ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}

// Fetch user's current skills
$user_skill_ids = [];
$stmt = $conn->prepare("SELECT skill_id FROM user_skills WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $user_skill_ids[] = $row['skill_id'];
}

// Fetch user's business info
$business = null;
$stmt = $conn->prepare("SELECT b.* FROM businesses b JOIN users u ON u.business_id = b.id WHERE u.id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res) $business = $res->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $state_code = trim($_POST['state_code']);
    $occupation = trim($_POST['occupation']);
    $phone = trim($_POST['phone']);
    $contact_private = isset($_POST['contact_private']) ? 1 : 0;
    $skills_selected = isset($_POST['skills']) ? $_POST['skills'] : [];
    $business_name = trim($_POST['business_name']);
    $business_description = trim($_POST['business_description']);
    $website = trim($_POST['website']);
    $socials = trim($_POST['socials']);
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $target = '/public/uploads/profile_' . $user_id . '_' . time() . '.' . $ext;
        $abs_target = __DIR__ . '/../../public/uploads/profile_' . $user_id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $abs_target)) {
            $profile_picture = $target;
        }
    }
    $update_user_sql = "UPDATE users SET name=?, state_code=?, occupation=?, phone=?, contact_private=?";
    $params = [$name, $state_code, $occupation, $phone, $contact_private];
    $types = 'ssssi';
    if ($profile_picture) {
        $update_user_sql .= ", profile_picture=?";
        $params[] = $profile_picture;
        $types .= 's';
    }
    $update_user_sql .= " WHERE id=?";
    $params[] = $user_id;
    $types .= 'i';
    $stmt = $conn->prepare($update_user_sql);
    $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    // Update skills
    $stmt = $conn->prepare("DELETE FROM user_skills WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    foreach ($skills_selected as $sid) {
        $stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $user_id, $sid);
        $stmt->execute();
    }
    // Update or insert business info
    if ($business) {
        $stmt = $conn->prepare("UPDATE businesses SET name=?, description=?, website=?, socials=? WHERE id=?");
        $stmt->bind_param('ssssi', $business_name, $business_description, $website, $socials, $business['id']);
        $stmt->execute();
    } else if ($business_name) {
        $stmt = $conn->prepare("INSERT INTO businesses (name, description, website, socials) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $business_name, $business_description, $website, $socials);
        $stmt->execute();
        $bid = $conn->insert_id;
        $stmt = $conn->prepare("UPDATE users SET business_id=? WHERE id=?");
        $stmt->bind_param('ii', $bid, $user_id);
        $stmt->execute();
    }
    if ($ok) {
        $success = 'Profile updated successfully!';
        $_SESSION['user_name'] = $name;
    } else {
        $error = 'Failed to update profile.';
    }
    header("Location: dashboard.php?success=1");
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT name, state_code, occupation, phone, email, contact_private, profile_picture, business_id FROM users WHERE id=?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$business = $user['business_id'] ? getBusinessById($conn, $user['business_id']) : null;
$user_skill_ids = [];
$stmt2 = $conn->prepare("SELECT skill_id FROM user_skills WHERE user_id = ?");
$stmt2->bind_param('i', $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $user_skill_ids[] = $row['skill_id'];
}

function getBusinessById($conn, $bid) {
    $stmt = $conn->prepare("SELECT * FROM businesses WHERE id = ?");
    $stmt->bind_param('i', $bid);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

ob_start();
?>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div id="toast-success" class="fixed top-6 right-6 z-50 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-fade-in">
    <i class="fa fa-check-circle text-2xl"></i>
    <span>Profile updated successfully!</span>
  </div>
<?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
  <div id="toast-error" class="fixed top-6 right-6 z-50 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 animate-fade-in">
    <i class="fa fa-times-circle text-2xl"></i>
    <span>Failed to update profile.</span>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/navbar.php'; ?>

<div class="max-w-3xl mx-auto mt-12 mb-12 bg-white/80 glassmorphism p-10 rounded-3xl shadow-2xl animate-fade-in">
    <h1 class="text-3xl md:text-4xl font-extrabold text-green-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-user-circle text-green-700"></i> Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!
    </h1>
    <p class="mb-6 text-gray-700">You are logged in to the ICT Corps Members Hub.</p>
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="flex flex-col md:flex-row items-center gap-6 mb-6">
            <div class="relative group">
                <?php if (!empty($user['profile_picture'])): ?>
                    <?php
                    $profilePicPath = $user['profile_picture'];
                    if (strpos($profilePicPath, '/ICT-Corps-Members-Hub') !== 0) {
                        $profilePicPath = '/ICT-Corps-Members-Hub' . $profilePicPath;
                    }
                    ?>
                    <img id="profilePic" src="<?= htmlspecialchars($profilePicPath) ?>" alt="Profile Picture" class="w-28 h-28 rounded-full object-cover border-4 border-green-700 shadow-lg transition-all duration-300 group-hover:blur-[2px]">
                <?php else: ?>
                    <div id="profilePic" class="w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 border-4 border-green-700 shadow-lg text-3xl">No Image</div>
                <?php endif; ?>
                <label class="absolute bottom-2 right-2 bg-green-700 text-white rounded-full p-2 shadow cursor-pointer hover:bg-green-900 transition" title="Change Photo">
                    <i class="fa fa-camera"></i>
                    <input type="file" name="profile_picture" accept="image/*" class="hidden" onchange="previewProfilePic(event)">
                </label>
            </div>
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 font-semibold">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">State Code</label>
                    <input type="text" name="state_code" value="<?= htmlspecialchars($user['state_code']) ?>" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Occupation</label>
                    <input type="text" name="occupation" value="<?= htmlspecialchars($user['occupation']) ?>" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block mb-1 font-semibold">Skillset(s)</label>
                    <select name="skills[]" multiple class="w-full border rounded px-3 py-2">
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?= $skill['id'] ?>" <?= in_array($skill['id'], $user_skill_ids) ? 'selected' : '' ?>><?= htmlspecialchars($skill['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1 font-semibold">Business Name</label>
                <input type="text" name="business_name" value="<?= htmlspecialchars($business['name'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Business Description</label>
                <textarea name="business_description" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($business['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Website</label>
                <input type="url" name="website" value="<?= htmlspecialchars($business['website'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-1 font-semibold">Socials</label>
                <input type="text" name="socials" value="<?= htmlspecialchars($business['socials'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="flex items-center mt-4">
            <input type="checkbox" name="contact_private" id="contact_private" value="1" <?= $user['contact_private'] ? 'checked' : '' ?>>
            <label for="contact_private" class="ml-2">Keep my contact info private</label>
        </div>
        <div class="flex gap-4 mt-8">
            <button type="submit" class="bg-green-700 text-white px-8 py-3 rounded-lg font-bold shadow hover:bg-green-800 transition">Save Changes</button>
            <a href="/public/auth/logout.php" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg font-bold shadow hover:bg-red-700 transition">Logout</a>
        </div>
    </form>
</div>
<script>
function previewProfilePic(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('profilePic');
            if (img.tagName === 'IMG') {
                img.src = e.target.result;
            } else {
                img.innerHTML = '';
                img.style.backgroundImage = `url('${e.target.result}')`;
                img.style.backgroundSize = 'cover';
                img.style.backgroundPosition = 'center';
            }
        };
        reader.readAsDataURL(file);
    }
}
setTimeout(() => {
    const toast = document.getElementById('toast-success') || document.getElementById('toast-error');
    if (toast) toast.style.display = 'none';
}, 3000);
</script>
<style>
.glassmorphism {
    background: rgba(255,255,255,0.18);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 1.5rem;
    border: 1px solid rgba(255,255,255,0.25);
}
.animate-fade-in {
    animation: fade-in 1s cubic-bezier(.4,0,.2,1) both;
}
@keyframes fade-in {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: none; }
}
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
