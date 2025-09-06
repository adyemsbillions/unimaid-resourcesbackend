<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "unimaidresources";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        $message = "No valid image file uploaded";
        $messageType = "danger";
    } else {
        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
        $fileType = mime_content_type($_FILES["image"]["tmp_name"]);
        if (!in_array($fileType, $allowedTypes)) {
            $message = "Only JPEG, PNG, and GIF images are allowed";
            $messageType = "danger";
        } else {
            $fileName = uniqid() . "-" . basename($_FILES["image"]["name"]);
            $filePath = $uploadDir . $fileName;
            $imageUrl = "uploads/" . $fileName;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $filePath)) {
                $stmt = $conn->prepare("INSERT INTO slide_images (image_url) VALUES (?)");
                $stmt->bind_param("s", $imageUrl);

                if ($stmt->execute()) {
                    $message = "Image uploaded successfully";
                    $messageType = "success";
                } else {
                    $message = "Failed to save image URL to database";
                    $messageType = "danger";
                }
                $stmt->close();
            } else {
                $message = "Failed to save image";
                $messageType = "danger";
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Slide Image</title>
    <!-- Bootstrap CSS (optional, can be removed for custom CSS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
    }

    .container {
        max-width: 500px;
        margin: 50px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .header h1 {
        color: #333;
        font-size: 24px;
        font-weight: bold;
    }

    .form-label {
        color: #333;
        font-weight: 500;
    }

    .btn-primary {
        background: linear-gradient(90deg, #8B5CF6, #7C3AED);
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: 600;
    }

    .btn-primary:hover {
        background: linear-gradient(90deg, #7C3AED, #8B5CF6);
    }

    .alert {
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Unimaid Resources - Upload Slide Image</h1>
        </div>
        <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="image" class="form-label">Select Image (JPEG, PNG, GIF)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif"
                    required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload Image</button>
        </form>
    </div>
    <!-- Bootstrap JS (optional, for alert styling) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>