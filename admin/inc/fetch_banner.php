<?php
require_once('../../inc/db.php');

header('Content-Type: application/json');

$page = $_POST['page_name'] ?? '';

if ($page) {
    $stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
    $stmt->execute([$page]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['image' => $result['image'] ?? '']);
} else {
    echo json_encode(['image' => '']);
}
