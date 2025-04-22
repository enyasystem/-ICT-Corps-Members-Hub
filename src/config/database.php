<?php
$host = '127.0.0.1'; // Use the IP address for localhost
$user = 'root'; // Username for MySQL
$password = ''; // MySQL password (if any)
$dbname = 'ict_cds_corps_member'; // The database you're trying to connect to
$port = 3307; // The port you're using for MySQL

// Creating connection
$conn = new mysqli($host, $user, $password, $dbname, $port);

// Checking connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
