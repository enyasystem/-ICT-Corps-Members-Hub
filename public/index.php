<?php
// filepath: /ICT-Corps-Members-Hub/ICT-Corps-Members-Hub/public/index.php

require_once '../src/config/database.php';

// Normalize URI (remove trailing slash)
$requestUri = rtrim($_SERVER['REQUEST_URI'], '/');

if (isset($_GET['ajax_member_id'])) {
    $id = intval($_GET['ajax_member_id']);
    $stmt = $conn->prepare("SELECT name, email, occupation, profile_picture, state_code, business_id, phone, github, linkedin, portfolio, website, socials, contact_private FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Fix path for profile picture
        $profile_picture = trim($row['profile_picture']);
        if ($profile_picture && strpos($profile_picture, '/public/uploads/') === 0) {
            $profile_picture = '/ICT-Corps-Members-Hub' . $profile_picture;
        }
        echo '<div class="flex flex-col items-center text-center">';
        echo '<img src="'.($profile_picture ? htmlspecialchars($profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($row['name']).'&background=008751&color=fff').'" class="w-24 h-24 rounded-full object-cover mb-3 border-4 border-green-700 shadow">';
        echo '<div class="font-bold text-2xl text-green-900 mb-1">'.htmlspecialchars($row['name']).'</div>';
        echo '<div class="text-green-700 mb-1">'.htmlspecialchars($row['occupation']).'</div>';
        echo '<div class="text-gray-500 text-sm mb-2">';
        if ($row['contact_private']) {
            echo '<span class="italic text-gray-400">State code private</span>';
        } else {
            echo 'State Code: '.htmlspecialchars($row['state_code']);
        }
        echo '</div>';
        if ($row['business_id']) {
            $bstmt = $conn->prepare("SELECT name, description FROM businesses WHERE id = ?");
            $bstmt->bind_param('i', $row['business_id']);
            $bstmt->execute();
            $bresult = $bstmt->get_result();
            if ($b = $bresult->fetch_assoc()) {
                echo '<div class="mb-2"><span class="font-semibold">Business:</span> '.htmlspecialchars($b['name']).'<br><span class="text-xs text-gray-500">'.htmlspecialchars($b['description']).'</span></div>';
            }
        }
        if ($row['contact_private']) {
            echo '<div class="text-gray-400 italic text-sm mb-1">Contact info is private</div>';
        } else {
            if ($row['email']) echo '<div class="mb-1"><i class="fa fa-envelope text-green-700"></i> '.htmlspecialchars($row['email']).'</div>';
            if ($row['phone']) echo '<div class="mb-1"><i class="fa fa-phone text-green-700"></i> '.htmlspecialchars($row['phone']).'</div>';
        }
        if ($row['github']) echo '<div class="mb-1"><i class="fab fa-github text-green-700"></i> <a href="'.htmlspecialchars($row['github']).'" target="_blank">GitHub</a></div>';
        if ($row['linkedin']) echo '<div class="mb-1"><i class="fab fa-linkedin text-green-700"></i> <a href="'.htmlspecialchars($row['linkedin']).'" target="_blank">LinkedIn</a></div>';
        if ($row['portfolio']) echo '<div class="mb-1"><i class="fa fa-globe text-green-700"></i> <a href="'.htmlspecialchars($row['portfolio']).'" target="_blank">Portfolio</a></div>';
        if ($row['website']) echo '<div class="mb-1"><i class="fa fa-link text-green-700"></i> <a href="'.htmlspecialchars($row['website']).'" target="_blank">Website</a></div>';
        if ($row['socials']) echo '<div class="mb-1"><i class="fa fa-share-nodes text-green-700"></i> '.htmlspecialchars($row['socials']).'</div>';
        echo '</div>';
    }
    exit;
}

switch ($requestUri) {
    case '':
    case '/':
    case '/ICT-Corps-Members-Hub/public':
    case '/ICT-Corps-Members-Hub/public/index.php':
    case '/public':
    case '/public/index.php':
        include '../src/views/home.php';
        break;
    case '/members':
    case '/ICT-Corps-Members-Hub/public/members.php':
    case '/ICT-Corps-Members-Hub/public/members':
    case '/public/members.php':
    case '/public/members':
        include '../src/views/members.php';
        break;
    default:
        http_response_code(404);
        echo "<div style='text-align:center;margin-top:10vh;font-size:2rem;color:#008751;font-family:sans-serif;'>404 Not Found</div>";
        break;
}
?>

