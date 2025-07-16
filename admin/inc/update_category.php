<?php
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    die("Invalid category ID.");
}

$title = trim($_POST['title'] ?? '');
if (empty($title)) {
    die("Category title is required.");
}
if (str_word_count($title) > 10) {
    die("Title must not exceed 10 words.");
}

try {
    // Get existing category data
    $stmt = $conn->prepare("SELECT category_image, box_ids_json FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        die("Category not found.");
    }

    $category_image = $existing['category_image'];
    $boxIdsJsonChanged = false;
    $boxIdsJson = $existing['box_ids_json'];

    // Check if new box_ids are submitted
    if (isset($_POST['box_ids']) && is_array($_POST['box_ids'])) {
        $selectedBoxIds = array_map('intval', array_filter($_POST['box_ids'], 'is_numeric'));
        $existingBoxIds = json_decode($existing['box_ids_json'], true) ?? [];

        sort($selectedBoxIds);
        sort($existingBoxIds);

        if ($selectedBoxIds !== $existingBoxIds) {
            $boxIdsJson = json_encode($selectedBoxIds);
            $boxIdsJsonChanged = true;
        }
    }

    // Handle image upload if new one provided
    if (!empty($_FILES['category_image']['name']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/categories/';
        $target_dir = "../" . $upload_dir;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $filename = basename($_FILES['category_image']['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed_exts)) {
            die("Invalid file type. Allowed types: jpg, jpeg, png, gif, webp.");
        }

        $new_filename = uniqid('cat_', true) . '.' . $ext;
        $target_file = $target_dir . $new_filename;

        if (!move_uploaded_file($_FILES['category_image']['tmp_name'], $target_file)) {
            die("Failed to upload image.");
        }

        // Delete old image
        $old_path = "../" . $category_image;
        if (!empty($category_image) && file_exists($old_path) && is_file($old_path)) {
            unlink($old_path);
        }

        $category_image = $upload_dir . $new_filename;
    }

    // Build SQL
    $sql = "UPDATE categories SET title = :title, category_image = :image";
    if ($boxIdsJsonChanged) {
        $sql .= ", box_ids_json = :boxes";
    }
    $sql .= " WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':image', $category_image);
    if ($boxIdsJsonChanged) {
        $stmt->bindValue(':boxes', $boxIdsJson);
    }
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    header("Location: ../category?success=" . urlencode("Category updated successfully."));
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
