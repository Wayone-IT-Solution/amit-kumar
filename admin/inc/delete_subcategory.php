<?php
require_once '../../inc/db.php';
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid subcategory ID.");
}
$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT subcategory_image FROM subcategories WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existing && !empty($existing['subcategory_image']) && file_exists($existing['subcategory_image']) && is_file($existing['subcategory_image'])) {
    unlink($existing['subcategory_image']);
}
$stmt = $conn->prepare("DELETE FROM subcategories WHERE id = ?");
$stmt->execute([$id]);
header("Location: ../subcategory.php?success=Subcategory deleted successfully");
exit; 