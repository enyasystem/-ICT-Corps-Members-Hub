<?php
class Event {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Get all events
    public function getAll() {
        $sql = "SELECT * FROM events ORDER BY id DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get event by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create event
    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO events (title, description, date, location, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $data['title'], $data['description'], $data['date'], $data['location'], $data['image']);
        return $stmt->execute();
    }

    // Update event
    public function update($id, $data) {
        $fields = [];
        $params = [];
        $types = '';
        foreach (["title", "description", "date", "location", "image"] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= 's';
            }
        }
        $params[] = $id;
        $types .= 'i';
        $sql = "UPDATE events SET ".implode(", ", $fields)." WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Delete event
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
