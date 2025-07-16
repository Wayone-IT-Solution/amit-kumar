<?php
require_once '../../inc/db.php';
$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';
if ($id && in_array($status, ['active', 'inactive'])) {
    $stmt = $conn->prepare("UPDATE subcategories SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header("Location: ../subcategory.php");
    exit;
} else {
    die("Invalid request.");
} 