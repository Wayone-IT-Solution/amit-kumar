<?php

include("./inc/db.php");    

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $status = $_POST['status'] ?? 'inactive';

    if ($id && $address && $pincode) {
        $stmt = $conn->prepare("UPDATE pincodes SET address = :address, pincode = :pincode, status = :status WHERE id = :id");
        $stmt->execute([
            ':address' => $address,
            ':pincode' => $pincode,
            ':status' => $status,
            ':id' => $id
        ]);
        header("Location: ../delivery_locations.php");
        exit;
    } else {
        echo "All fields are required.";
    }
}
?>