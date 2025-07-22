<?php
require_once('../../inc/db.php');

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? 0;

if ($id) {
    $stmt = $conn->prepare("UPDATE categories SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header("Location: ../category"); // Update path
    exit;
}
