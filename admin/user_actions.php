<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/User.php';
header('Content-Type: application/json');
$userModel = new User($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'occupation' => $_POST['occupation'],
            'state_code' => $_POST['state_code'],
            'phone' => $_POST['phone'],
            'role_id' => $_POST['role_id'],
            'password' => $_POST['password'],
        ];
        $result = $userModel->create($data);
        echo json_encode(['success' => $result]);
        break;
    case 'edit':
        $id = $_POST['id'];
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'occupation' => $_POST['occupation'],
            'state_code' => $_POST['state_code'],
            'phone' => $_POST['phone'],
            'role_id' => $_POST['role_id'],
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        $result = $userModel->update($id, $data);
        echo json_encode(['success' => $result]);
        break;
    case 'delete':
        $id = $_POST['id'];
        $result = $userModel->delete($id);
        echo json_encode(['success' => $result]);
        break;
    case 'list':
        $users = $userModel->getAll();
        echo json_encode(['users' => $users]);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}
