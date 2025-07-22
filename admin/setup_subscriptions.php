<?php
require_once '../inc/db.php';

try {
    // Create subscriptions table
    $sql = "
    CREATE TABLE IF NOT EXISTS `subscriptions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `description` text,
      `valid_days` int(11) NOT NULL DEFAULT 30,
      `price` decimal(10,2) NOT NULL DEFAULT 0.00,
      `image` varchar(255) DEFAULT NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($sql);
    echo "✅ Subscriptions table created successfully!<br>";
    
    // Check if table has data
    $stmt = $conn->query("SELECT COUNT(*) FROM subscriptions");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample data
        $sampleData = [
            ['Monthly Dairy Plan', 'Get fresh dairy products delivered to your doorstep every month. Includes milk, curd, butter, and cheese.', 30, 1500.00],
            ['Weekly Fresh Milk', 'Fresh milk delivery every week. Perfect for families who consume milk regularly.', 7, 500.00],
            ['Premium Dairy Package', 'Premium quality dairy products including organic milk, fresh cream, and artisanal cheese.', 30, 2500.00],
            ['Student Special Plan', 'Affordable dairy plan for students with basic dairy products.', 15, 800.00]
        ];
        
        $stmt = $conn->prepare("INSERT INTO subscriptions (title, description, valid_days, price) VALUES (?, ?, ?, ?)");
        
        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        
        echo "✅ Sample subscription plans added successfully!<br>";
    } else {
        echo "ℹ️ Subscriptions table already has data.<br>";
    }
    
    echo "<br>🎉 Setup completed! You can now access the subscription management at: <a href='subscription.php'>subscription.php</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 