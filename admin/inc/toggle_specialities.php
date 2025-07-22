<?php
require '../../inc/db.php';

if (!isset($_GET['id'], $_GET['status'])) {
    header("Location: ../product?error=Missing+parameters");
    exit;
}

$id = (int) $_GET['id'];
$status = (int) $_GET['status'];

try {
    $stmt = $conn->prepare("UPDATE products SET specialities = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);
    header("Location: ../product?success=Specialities+updated");
} catch (PDOException $e) {
    header("Location: ../product?error=Failed+to+update");
}
