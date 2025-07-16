<?php
require_once '../../inc/db.php'; // Adjust the path if needed

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$id = (int)$_GET['id'];

try {
    // Fetch the current product image path
    $stmt = $conn->prepare("SELECT product_image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    // Delete the image file if it exists
    $imagePath = "../" . $product['product_image'];
    if (!empty($product['product_image']) && file_exists($imagePath) && is_file($imagePath)) {
        unlink($imagePath);
    }

    // Delete the product from the database
    $delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete->execute([$id]);

    header("Location: ../product?success=Product deleted successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>