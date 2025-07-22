<?php
require_once '../../inc/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}
$id = (int)($_POST['id'] ?? 0);
$category_id = (int)($_POST['category_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$status = $_POST['status'] ?? 'active';
if ($id <= 0) die("Invalid subcategory ID.");
if ($category_id <= 0) die("Category is required.");
if (empty($title)) die("Subcategory title is required.");
if (!in_array($status, ['active', 'inactive'])) die("Invalid status.");
$stmt = $conn->prepare("SELECT subcategory_image FROM subcategories WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$existing) die("Subcategory not found.");
$subcategory_image = $existing['subcategory_image'];
if (!empty($_FILES['subcategory_image']['name'])) {
    $uploadDir = '../uploads/subcategories/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $imageName = basename($_FILES['subcategory_image']['name']);
    $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($imageExt, $allowedExts)) {
        die('Invalid image format.');
    }
    $safeImageName = uniqid('subcat_', true) . '.' . $imageExt;
    $fullImagePath = $uploadDir . $safeImageName;
    if (!move_uploaded_file($_FILES['subcategory_image']['tmp_name'], $fullImagePath)) {
        die('Failed to upload image.');
    }
    if (!empty($subcategory_image) && file_exists($subcategory_image) && is_file($subcategory_image)) {
        unlink($subcategory_image);
    }
    $subcategory_image = $fullImagePath;
}
try {
    $stmt = $conn->prepare("UPDATE subcategories SET category_id = :category_id, title = :title, subcategory_image = :image, status = :status WHERE id = :id");
    $stmt->execute([
        ':category_id' => $category_id,
        ':title' => $title,
        ':image' => $subcategory_image,
        ':status' => $status,
        ':id' => $id
    ]);
    header("Location: ../subcategory.php?success=Subcategory updated successfully");
    exit;
} catch (Throwable $e) {
    die('Database error: ' . $e->getMessage());
} 