<?php
require '../../inc/db.php'; // PDO connection

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = ($_GET['status'] == 1) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE products SET best_seller = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $id]);

    header("Location: ../product"); // adjust as needed
    exit;
} else {
    echo "Invalid request.";
}
?>