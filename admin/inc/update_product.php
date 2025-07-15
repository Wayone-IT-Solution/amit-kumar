<?php
    require_once '../../inc/db.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Invalid request method.");
    }
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        die("Invalid product ID.");
    }
    
    // Sanitize and fetch input
    $name              = trim($_POST['name'] ?? '');
    $category_id       = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $description       = trim($_POST['description'] ?? '');
    $weight            = trim($_POST['weight'] ?? '');
    $price             = trim($_POST['price'] ?? '');
    $discount_price    = trim($_POST['discount_price'] ?? '');
    $tags              = trim($_POST['tags'] ?? '');
    $min_order         = isset($_POST['min_order']) ? (int)$_POST['min_order'] : null;
    $max_order         = isset($_POST['max_order']) ? (int)$_POST['max_order'] : null;
    
    try {
        // Get existing product image path
        $stmt = $conn->prepare("SELECT product_image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$existing) {
            die("Product not found.");
        }
    
        $product_image = $existing['product_image']; // Default to existing image
    
        // Handle new image upload
        if (!empty($_FILES['product_image']['name'])) {
            $upload_dir = 'uploads/products/';
            $target_dir = "../" . $upload_dir;
    
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
    
            $filename = basename($_FILES['product_image']['name']);
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
            if (!in_array($file_ext, $allowed_exts)) {
                die("Invalid file type.");
            }
    
            $new_filename = uniqid('prod_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
    
            if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                die("Failed to upload image.");
            }
    
            // Delete old image file
            $old_path = "../" . $product_image;
            if (file_exists($old_path) && is_file($old_path)) {
                unlink($old_path);
            }
    
            $product_image = $upload_dir . $new_filename;
        }
    
        // Update the product record
        $update = $conn->prepare("
            UPDATE products SET 
                name = ?, 
                category_id = ?, 
                description = ?, 
                weight = ?, 
                price = ?, 
                discount_price = ?, 
                min_order = ?, 
                tags = ?,
                max_order = ?,  
                product_image = ?
            WHERE id = ?
        ");
    
        $update->execute([
            $name,
            $category_id,
            $description,
            $weight,
            $price,
            $discount_price,
            $min_order,
            $tags,
            $max_order,
            $product_image,
            $id
        ]);
    
        header("Location: ../product?success=Product updated successfully");
        exit;
    
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
    ?>
