<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable error logging to file for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php-error.log');

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/Event.php';
header('Content-Type: application/json');

// Check database connection
if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . ($conn ? $conn->connect_error : 'No connection')]);
    exit;
}

$eventModel = new Event($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/';
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $uniqueName = 'event_' . time() . '_' . bin2hex(random_bytes(20)) . '.' . $ext;
            $targetPath = $uploadDir . $uniqueName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = '/public/uploads/' . $uniqueName;
            }
        } else if (!empty($_POST['image'])) {
            $imagePath = $_POST['image']; // fallback if image URL is provided
        }
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'date' => $_POST['date'],
            'location' => $_POST['location'],
            'image' => $imagePath,
        ];
        $result = $eventModel->create($data);
        echo json_encode(['success' => $result]);
        break;
    case 'edit':
        $id = $_POST['id'];
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'date' => $_POST['date'],
            'location' => $_POST['location'],
        ];
        if (!empty($_POST['image'])) {
            $data['image'] = $_POST['image'];
        }
        $result = $eventModel->update($id, $data);
        echo json_encode(['success' => $result]);
        break;
    case 'delete':
        $id = $_POST['id'];
        $result = $eventModel->delete($id);
        echo json_encode(['success' => $result]);
        break;
    case 'list':
        $events = $eventModel->getAll();
        echo json_encode(['events' => $events]);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}
