<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost"; // Replace with your database host
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "unimaidresources"; // Replace with your database name

try {
    // Create a new PDO instance for database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SQL query to fetch all slide images
    $stmt = $conn->prepare("SELECT * FROM `slide_images`");
    $stmt->execute();

    // Fetch all rows as an associative array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any records were found
    if ($result) {
        // Return the results as JSON
        echo json_encode($result);
    } else {
        // Return an error message if no images are found
        http_response_code(404);
        echo json_encode(['error' => 'No images found']);
    }
} catch (PDOException $e) {
    // Handle database connection or query errors
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Handle any other errors
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

// Close the database connection
$conn = null;