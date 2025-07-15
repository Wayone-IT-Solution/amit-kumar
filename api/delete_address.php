<?php
require '../inc/db.php'; // Make sure $conn (PDO) is defined
header('Content-Type: application/json');

try {
    // Allow only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Only POST method is allowed.']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;

    if ($order_id <= 0) {
        echo json_encode(['status' => false, 'message' => 'Invalid order ID.']);
        exit;
    }

    // Check order status
    $stmt = $conn->prepare("SELECT order_status FROM orders WHERE id = :id");
    $stmt->execute([':id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['status' => false, 'message' => 'Order not found.']);
        exit;
    }

    if ($order['order_status'] === 'start_preparing') {
        echo json_encode(['status' => false, 'message' => 'Cannot delete address while order is being prepared.']);
        exit;
    }

    // Nullify address fields
    $stmt = $conn->prepare("UPDATE orders SET 
        address_details = NULL, 
        house_block = NULL, 
        area_road = NULL, 
        save_as = NULL, 
        receiver_name = NULL, 
        receiver_phone = NULL 
        WHERE id = :id");

    $stmt->execute([':id' => $order_id]);

    echo json_encode(['status' => true, 'message' => 'Address details deleted successfully.']);

} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
