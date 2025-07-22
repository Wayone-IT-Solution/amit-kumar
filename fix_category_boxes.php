<?php
// Run this script from your project root: php fix_category_boxes.php

try {
    $pdo = new PDO('mysql:host=YOUR_AWS_DB_HOST;dbname=dairy', 'root', ''); // <-- Replace with your AWS RDS endpoint or server IP
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all box IDs
    $boxes = $pdo->query('SELECT id FROM boxes')->fetchAll(PDO::FETCH_COLUMN);
    if (!$boxes) {
        echo "No boxes found in the boxes table.\n";
        exit(1);
    }
    $json = json_encode(array_map('intval', $boxes));

    // Update all categories
    $stmt = $pdo->prepare('UPDATE categories SET box_ids_json = ?');
    $stmt->execute([$json]);
    echo "All categories updated with box IDs: $json\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 