<?php

class Member {
    private $id;
    private $name;
    private $occupation;
    private $skillset;
    private $businessName;

    public function __construct($id, $name, $occupation, $skillset, $businessName) {
        $this->id = $id;
        $this->name = $name;
        $this->occupation = $occupation;
        $this->skillset = $skillset;
        $this->businessName = $businessName;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getOccupation() {
        return $this->occupation;
    }

    public function getSkillset() {
        return $this->skillset;
    }

    public function getBusinessName() {
        return $this->businessName;
    }

    public static function fetchMembers($conn) {
        $stmt = $conn->prepare("SELECT * FROM members");
        $stmt->execute();
        $result = $stmt->get_result();
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = new Member($row['id'], $row['name'], $row['occupation'], $row['skillset'], $row['business_name']);
        }
        return $members;
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO members (name, occupation, skillset, business_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $this->name, $this->occupation, $this->skillset, $this->businessName);
        return $stmt->execute();
    }
}
