<?php
require_once '../../inc/db.php'; // Adjust the path if needed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    die("Invalid testimonial ID.");
}

// Sanitize input
$name = trim($_POST['name'] ?? '');
$comment = trim($_POST['comment'] ?? '');
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

if ($name === '' || $comment === '' || $rating < 1 || $rating > 5) {
    die("Invalid input provided.");
}

try {
    // Fetch existing testimonial image
    $stmt = $conn->prepare("SELECT image FROM testimonial WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        die("Testimonial not found.");
    }

    $image = $existing['image']; // Use current image by default

    // Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = 'uploads/testimonials/';
        $target_dir = "../" . $upload_dir;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = basename($_FILES['image']['name']);
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($file_ext, $allowed_exts)) {
            die("Invalid image format.");
        }

        $new_filename = uniqid('testimonial_', true) . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            die("Failed to upload image.");
        }

        // Delete old image if it exists
        $old_path = "../" . $image;
        if (file_exists($old_path) && is_file($old_path)) {
            unlink($old_path);
        }

        $image = $upload_dir . $new_filename;
    }

    // Update testimonial
    $update = $conn->prepare("
        UPDATE testimonial 
        SET name = ?, comment = ?, rating = ?, image = ? 
        WHERE id = ?
    ");
    $update->execute([$name, $comment, $rating, $image, $id]);

    header("Location: ../testimonial?success=Testimonial updated successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>