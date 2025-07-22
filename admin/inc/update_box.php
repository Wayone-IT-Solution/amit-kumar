<?php
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Get and validate box ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    die("Invalid box ID.");
}

// Sanitize inputs
$box_name = trim($_POST['box_name'] ?? '');
$box_price = trim($_POST['box_price'] ?? '');

if (empty($box_name)) {
    die("Box name is required.");
}
if (!is_numeric($box_price) || floatval($box_price) < 0) {
    die("Box price must be a non-negative number.");
}

try {
    // Fetch existing image path
    $stmt = $conn->prepare("SELECT box_image FROM boxes WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        die("Box not found.");
    }

    $box_image = $existing['box_image']; // Default to existing image

    // Handle new image upload
    if (!empty($_FILES['box_image']['name'])) {
        $upload_dir = 'uploads/boxes/';
        $target_dir = "../" . $upload_dir;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = basename($_FILES['box_image']['name']);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($file_ext, $allowed_exts)) {
            die("Invalid file type.");
        }

        $new_filename = uniqid('box_', true) . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (!move_uploaded_file($_FILES['box_image']['tmp_name'], $target_file)) {
            die("Failed to upload image.");
        }

        // Delete old image
        $old_path = "../" . $box_image;
        if (file_exists($old_path) && is_file($old_path)) {
            unlink($old_path);
        }

        $box_image = $upload_dir . $new_filename; // Save new image path
    }

    // Update database
    $update = $conn->prepare("
        UPDATE boxes 
        SET box_name = ?, box_price = ?, box_image = ? 
        WHERE id = ?
    ");
    $update->execute([$box_name, $box_price, $box_image, $id]);

    header("Location: ../boxes?success=Box updated successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
