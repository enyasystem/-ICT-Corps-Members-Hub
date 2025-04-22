<?php
class User {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all users with their roles
    public function getAll() {
        $sql = "SELECT u.*, r.display_name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get user by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create user
    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, occupation, state_code, phone, role_id, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param('sssssis', $data['name'], $data['email'], $data['occupation'], $data['state_code'], $data['phone'], $data['role_id'], $hashed);
        return $stmt->execute();
    }

    // Update user
    public function update($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        foreach (["name", "email", "occupation", "state_code", "phone", "role_id"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= 's';
            }
        }
        if (isset($data['password']) && $data['password']) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= 's';
        }
        $params[] = $id;
        $types .= 'i';
        $sql = "UPDATE users SET ".implode(", ", $fields)." WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Delete user
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
