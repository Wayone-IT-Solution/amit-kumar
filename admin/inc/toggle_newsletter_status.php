<?php
require_once '../../inc/db.php';

if (isset($_GET['id'], $_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = (int)$_GET['status'];

    $stmt = $conn->prepare("UPDATE newsletter SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

header('Location: ../newsletter_list.php'); // Adjust redirect as needed
exit;
