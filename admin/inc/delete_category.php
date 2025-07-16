<?php
require_once '../../inc/db.php'; // Adjust the path if needed

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid category ID.");
}

$id = (int)$_GET['id'];

try {
    // Fetch the current image path
    $stmt = $conn->prepare("SELECT category_image FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        die("Category not found.");
    }

    // Delete the image file if it exists
    $imagePath = "../" . $category['category_image'];
    if (!empty($category['category_image']) && file_exists($imagePath) && is_file($imagePath)) {
        unlink($imagePath);
    }

    // Delete the category from the database
    $delete = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $delete->execute([$id]);

    header("Location: ../category?success=Category deleted successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>