<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection settings
$host = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$database = "unimaidresources";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON input"]);
    exit();
}

// Extract and validate input
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$department = trim($input['department'] ?? '');
$phoneNumber = trim($input['phoneNumber'] ?? '');

// Basic validation
if (empty($username) || empty($email) || empty($password) || empty($department)) {
    http_response_code(400);
    echo json_encode(["error" => "All fields except phone number are required"]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format"]);
    exit();
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["error" => "Password must be at least 6 characters"]);
    exit();
}

// Hash the password
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Prepare and execute SQL statement
$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, department, phone_number) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $email, $passwordHash, $department, $phoneNumber);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "User registered successfully"]);
} else {
    http_response_code(400);
    if ($conn->errno === 1062) { // Duplicate entry error
        echo json_encode(["error" => "Username or email already exists"]);
    } else {
        echo json_encode(["error" => "Registration failed: " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>