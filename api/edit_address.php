<?php
require '../inc/db.php'; // Ensure $conn (PDO) is initialized
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Only POST method is allowed.']);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true);
    $order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;

    if ($order_id <= 0) {
        echo json_encode(['status' => false, 'message' => 'Invalid order ID.']);
        exit;
    }

    // Extract new address fields
    $address_details = $input['address_details'] ?? null;
    $house_block = $input['house_block'] ?? null;
    $area_road = $input['area_road'] ?? null;
    $save_as = $input['save_as'] ?? null;
    $receiver_name = $input['receiver_name'] ?? null;
    $receiver_phone = $input['receiver_phone'] ?? null;

    // Validate minimum required fields
    if (!$address_details || !$receiver_name || !$receiver_phone) {
        echo json_encode(['status' => false, 'message' => 'Missing required address fields.']);
        exit;
    }

    // Check if order exists
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = :id");
    $stmt->execute([':id' => $order_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['status' => false, 'message' => 'Order not found.']);
        exit;
    }

    // Update address
    $stmt = $conn->prepare("
        UPDATE orders SET
            address_details = :address_details,
            house_block = :house_block,
            area_road = :area_road,
            save_as = :save_as,
            receiver_name = :receiver_name,
            receiver_phone = :receiver_phone
        WHERE id = :id
    ");

    $stmt->execute([
        ':address_details' => $address_details,
        ':house_block' => $house_block,
        ':area_road' => $area_road,
        ':save_as' => $save_as,
        ':receiver_name' => $receiver_name,
        ':receiver_phone' => $receiver_phone,
        ':id' => $order_id
    ]);

    echo json_encode(['status' => true, 'message' => 'Address updated successfully.']);

} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
