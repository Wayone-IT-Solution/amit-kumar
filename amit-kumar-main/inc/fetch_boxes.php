<?php
// Force JSON response
header('Content-Type: application/json');

// Show errors in development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('db.php');

// Get category ID
$categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

if ($categoryId <= 0) {
    echo json_encode(["error" => "Invalid category ID"]);
    exit;
}

// Fetch JSON-encoded box IDs from the category
$stmt = $conn->prepare("SELECT box_ids_json FROM categories WHERE id = ?");
$stmt->execute([$categoryId]);
$boxIdsJson = $stmt->fetchColumn();

if (!$boxIdsJson) {
    echo json_encode(["error" => "No box IDs found for this category."]);
    exit;
}

// Decode JSON into array
$boxIds = json_decode($boxIdsJson, true);

if (!is_array($boxIds) || empty($boxIds)) {
    echo json_encode(["error" => "Invalid or empty box ID format."]);
    exit;
}

// Prepare placeholders for SQL IN clause
$placeholders = implode(',', array_fill(0, count($boxIds), '?'));

// Fetch box details
$sql = "SELECT id, box_name, box_price, box_image FROM boxes WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$stmt->execute($boxIds);
$boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return response
echo json_encode($boxes);
