<?php
// Include your database connection file
require_once('../../inc/db.php'); // Adjust path if needed

if (isset($_GET['id'], $_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];

    // Make sure $pdo is defined in db.php
    $stmt = $conn->prepare("UPDATE pincodes SET status = :status WHERE id = :id");
    $stmt->execute([
        'status' => $status,
        'id' => $id
    ]);

    // Redirect back to the list page
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    echo "Invalid request!";
}
