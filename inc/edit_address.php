<?php
require_once 'db.php'; // adjust path to your db file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['address_id']) ? (int) $_POST['address_id'] : 0;
    $address_details = trim($_POST['address_details'] ?? '');
    $house_block = trim($_POST['house_block'] ?? '');
    $area_road = trim($_POST['area_road'] ?? '');
    $save_as = trim($_POST['save_as'] ?? 'Others');
    $receiver_name = trim($_POST['receiver_name'] ?? '');
    $receiver_phone = trim($_POST['receiver_phone'] ?? '');

    if ($id <= 0 || empty($address_details) || empty($house_block) || empty($area_road) || empty($receiver_name) || empty($receiver_phone)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $sql = "UPDATE orders 
                SET address_details = :address_details,
                    house_block = :house_block,
                    area_road = :area_road,
                    save_as = :save_as,
                    receiver_name = :receiver_name,
                    receiver_phone = :receiver_phone
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':address_details' => $address_details,
            ':house_block' => $house_block,
            ':area_road' => $area_road,
            ':save_as' => $save_as,
            ':receiver_name' => $receiver_name,
            ':receiver_phone' => $receiver_phone,
            ':id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Address updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
