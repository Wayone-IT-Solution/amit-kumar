<?php
// Force JSON response
header('Content-Type: application/json');

// Show errors in development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('db.php');

// Fetch all boxes, ignore category_id
$sql = "SELECT id, box_name, box_price, box_image FROM boxes ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return response
echo json_encode($boxes);
