<?php
require_once '../../inc/db.php'; // Adjust the path if needed

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid testimonial ID.");
}

$id = (int)$_GET['id'];

try {
    // Fetch the current testimonial image path
    $stmt = $conn->prepare("SELECT image FROM testimonial WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$testimonial) {
        die("Testimonial not found.");
    }

    // Delete the image file if it exists
    $imagePath = "../" . $testimonial['image'];
    if (!empty($testimonial['image']) && file_exists($imagePath) && is_file($imagePath)) {
        unlink($imagePath);
    }

    // Delete the testimonial from the database
    $delete = $conn->prepare("DELETE FROM testimonial WHERE id = ?");
    $delete->execute([$id]);

    header("Location: ../testimonial?success=Testimonial deleted successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>