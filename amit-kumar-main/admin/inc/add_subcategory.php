<?php
require_once '../../inc/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}
$category_id = (int)($_POST['category_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$status = $_POST['status'] ?? 'active';
if ($category_id <= 0) die("Category is required.");
if (empty($title)) die("Subcategory title is required.");
if (!in_array($status, ['active', 'inactive'])) die("Invalid status.");
if (!isset($_FILES['subcategory_image']) || $_FILES['subcategory_image']['error'] !== UPLOAD_ERR_OK) {
    die('Subcategory image is required.');
}
$allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
$imageTmp = $_FILES['subcategory_image']['tmp_name'];
$imageName = basename($_FILES['subcategory_image']['name']);
$imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
if (!in_array($imageExt, $allowedExts)) {
    die('Invalid image format. Only JPG, JPEG, PNG, WEBP allowed.');
}
$safeImageName = uniqid('subcat_', true) . '.' . $imageExt;
$uploadDir = '../uploads/subcategories/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$fullImagePath = $uploadDir . $safeImageName;
if (!move_uploaded_file($imageTmp, $fullImagePath)) {
    die('Failed to save uploaded image.');
}
try {
    $stmt = $conn->prepare("INSERT INTO subcategories (category_id, title, subcategory_image, status) VALUES (:category_id, :title, :image, :status)");
    $stmt->execute([
        ':category_id' => $category_id,
        ':title' => $title,
        ':image' => $fullImagePath,
        ':status' => $status
    ]);
    header("Location: ../subcategory.php?success=Subcategory added successfully");
    exit;
} catch (Throwable $e) {
    die('Database error: ' . $e->getMessage());
} 