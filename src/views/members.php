<?php
require_once __DIR__ . '/../config/database.php';

ob_start();

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT u.id, u.name, u.profile_picture, u.occupation, u.state_code, u.phone, u.email, u.business_id, u.contact_private, r.display_name as role_name, r.badge_color 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.role_id IS NULL AND (u.name LIKE ? OR u.occupation LIKE ?) 
        ORDER BY u.id DESC LIMIT 12";
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/tailwind.css" rel="stylesheet">
    <title>Members Directory - ICT Corps Members Hub</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Members Directory</h1>
        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search members..." class="p-2 border border-gray-300 rounded w-full">
            <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded">Search</button>
        </form>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($members as $member):
                $skills = getSkills($conn, $member['id']);
                $business = getBusiness($conn, $member['business_id']);
                $is_private = isset($member['contact_private']) && $member['contact_private'];
            ?>
            <div class="bg-white rounded shadow p-4 flex flex-col items-center relative">
                <?php
                $profile_picture = trim($member['profile_picture']);
                $is_valid_picture = $profile_picture && strtolower($profile_picture) !== 'profile picture';
                ?>
                <!-- Role badge -->
                <?php if (!empty($member['role_name']) && strtolower($member['role_name']) !== 'member'): ?>
                  <span class="absolute top-3 left-3 px-3 py-1 <?= htmlspecialchars($member['badge_color'] ?? 'bg-gray-200 text-gray-800') ?> text-xs rounded-full font-bold shadow capitalize"><?= htmlspecialchars($member['role_name']) ?></span>
                <?php endif; ?>
                <img src="<?= $is_valid_picture ? htmlspecialchars((strpos($profile_picture, '/ICT-Corps-Members-Hub') === 0 ? $profile_picture : '/ICT-Corps-Members-Hub' . $profile_picture)) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=008751&color=fff' ?>" class="w-20 h-20 rounded-full object-cover mb-2" alt="Profile Picture">
                <div class="font-bold text-lg mb-1"> <?= htmlspecialchars($member['name']) ?> </div>
                <div class="text-sm text-gray-600 mb-1"> <?= htmlspecialchars($member['occupation']) ?> </div>
                <div class="text-xs text-gray-500 mb-1">
                    <?php if (!$is_private): ?>
                        State Code: <?= htmlspecialchars($member['state_code']) ?>
                    <?php else: ?>
                        <span class="italic text-gray-400">State code private</span>
                    <?php endif; ?>
                </div>
                <div class="mb-1">
                    <?php foreach ($skills as $skill): ?>
                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1 mb-1"> <?= htmlspecialchars($skill) ?> </span>
                    <?php endforeach; ?>
                </div>
                <?php if ($business): ?>
                    <div class="text-sm font-semibold mt-2">Business: <?= htmlspecialchars($business['name']) ?></div>
                    <div class="text-xs text-gray-500 mb-2"> <?= htmlspecialchars($business['description']) ?> </div>
                <?php endif; ?>
                <div class="flex gap-2 mt-2">
                    <?php if (!$is_private && $member['phone']): ?>
                        <a href="tel:<?= htmlspecialchars($member['phone']) ?>" title="Phone" class="text-green-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm0 14a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5a2 2 0 00-2 2v2zm14-14a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5a2 2 0 012-2h2zm0 14a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2v2z" /></svg></a>
                    <?php endif; ?>
                    <?php if (!$is_private && $member['email']): ?>
                        <a href="mailto:<?= htmlspecialchars($member['email']) ?>" title="Email" class="text-green-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0a4 4 0 11-8 0 4 4 0 018 0zm0 0v4m0-4V8" /></svg></a>
                    <?php endif; ?>
                    <?php if ($is_private): ?>
                        <span class="text-xs text-gray-400 italic">Contact info private</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Pagination or Show More can be added here -->
    </div>
</body>
</html>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
