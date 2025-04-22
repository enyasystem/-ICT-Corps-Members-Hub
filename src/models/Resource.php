<?php
class Resource {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all resources
    public function getAll() {
        $sql = "SELECT * FROM resources ORDER BY id DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get resource by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM resources WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create resource
    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO resources (title, description, file, uploaded_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $data['title'], $data['description'], $data['file'], $data['uploaded_at']);
        return $stmt->execute();
    }

    // Update resource
    public function update($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        foreach (["title", "description", "file", "uploaded_at"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= 's';
            }
        }
        $params[] = $id;
        $types .= 'i';
        $sql = "UPDATE resources SET ".implode(", ", $fields)." WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Delete resource
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
