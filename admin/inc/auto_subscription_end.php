<?php
require_once '../../inc/db.php';

// Function to automatically end expired subscriptions
function autoEndExpiredSubscriptions($conn) {
    try {
        // Get all active subscription orders that have expired
        $stmt = $conn->prepare("
            SELECT 
                so.id,
                so.user_id,
                so.subscription_id,
                so.created_at,
                s.valid_days,
                s.title as subscription_title
            FROM subscription_orders so
            JOIN subscriptions s ON so.subscription_id = s.id
            WHERE so.status = 'active'
            AND DATE_ADD(so.created_at, INTERVAL s.valid_days DAY) <= NOW()
        ");
        
        $stmt->execute();
        $expiredSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updatedCount = 0;
        $errors = [];
        
        foreach ($expiredSubscriptions as $subscription) {
            try {
                // Update subscription status to expired
                $updateStmt = $conn->prepare("
                    UPDATE subscription_orders 
                    SET status = 'expired', 
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($updateStmt->execute([$subscription['id']])) {
                    $updatedCount++;
                    
                    // Log the expiration
                    $logStmt = $conn->prepare("
                        INSERT INTO subscription_logs 
                        (subscription_order_id, user_id, action, details, created_at) 
                        VALUES (?, ?, 'expired', ?, NOW())
                    ");
                    
                    $details = "Subscription '{$subscription['subscription_title']}' automatically expired after {$subscription['valid_days']} days";
                    $logStmt->execute([$subscription['id'], $subscription['user_id'], $details]);
                    
                } else {
                    $errors[] = "Failed to update subscription order ID: " . $subscription['id'];
                }
                
            } catch (Exception $e) {
                $errors[] = "Error processing subscription ID {$subscription['id']}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'updated_count' => $updatedCount,
            'errors' => $errors,
            'message' => "Successfully expired {$updatedCount} subscriptions"
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Function to check and end 30-day subscriptions specifically
function checkAndEnd30DaySubscriptions($conn) {
    try {
        // Get all active 30-day subscription orders that have expired
        $stmt = $conn->prepare("
            SELECT 
                so.id,
                so.user_id,
                so.subscription_id,
                so.created_at,
                s.valid_days,
                s.title as subscription_title
            FROM subscription_orders so
            JOIN subscriptions s ON so.subscription_id = s.id
            WHERE so.status = 'active'
            AND s.valid_days = 30
            AND DATE_ADD(so.created_at, INTERVAL 30 DAY) <= NOW()
        ");
        
        $stmt->execute();
        $expired30DaySubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updatedCount = 0;
        $errors = [];
        
        foreach ($expired30DaySubscriptions as $subscription) {
            try {
                // Update subscription status to expired
                $updateStmt = $conn->prepare("
                    UPDATE subscription_orders 
                    SET status = 'expired', 
                        updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($updateStmt->execute([$subscription['id']])) {
                    $updatedCount++;
                    
                    // Log the expiration
                    $logStmt = $conn->prepare("
                        INSERT INTO subscription_logs 
                        (subscription_order_id, user_id, action, details, created_at) 
                        VALUES (?, ?, 'expired', ?, NOW())
                    ");
                    
                    $details = "30-day subscription '{$subscription['subscription_title']}' automatically expired";
                    $logStmt->execute([$subscription['id'], $subscription['user_id'], $details]);
                    
                } else {
                    $errors[] = "Failed to update 30-day subscription order ID: " . $subscription['id'];
                }
                
            } catch (Exception $e) {
                $errors[] = "Error processing 30-day subscription ID {$subscription['id']}: " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'updated_count' => $updatedCount,
            'errors' => $errors,
            'message' => "Successfully expired {$updatedCount} 30-day subscriptions"
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Function to get subscription expiration info
function getSubscriptionExpirationInfo($conn, $subscriptionOrderId) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                so.id,
                so.user_id,
                so.subscription_id,
                so.created_at,
                so.status,
                s.valid_days,
                s.title as subscription_title,
                DATE_ADD(so.created_at, INTERVAL s.valid_days DAY) as expiry_date,
                DATEDIFF(DATE_ADD(so.created_at, INTERVAL s.valid_days DAY), NOW()) as days_remaining
            FROM subscription_orders so
            JOIN subscriptions s ON so.subscription_id = s.id
            WHERE so.id = ?
        ");
        
        $stmt->execute([$subscriptionOrderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        return false;
    }
}

// Function to manually end a specific subscription
function manuallyEndSubscription($conn, $subscriptionOrderId, $adminId = null) {
    try {
        // Get subscription info
        $subscriptionInfo = getSubscriptionExpirationInfo($conn, $subscriptionOrderId);
        
        if (!$subscriptionInfo) {
            return [
                'success' => false,
                'error' => 'Subscription not found'
            ];
        }
        
        if ($subscriptionInfo['status'] !== 'active') {
            return [
                'success' => false,
                'error' => 'Subscription is not active'
            ];
        }
        
        // Update subscription status to expired
        $updateStmt = $conn->prepare("
            UPDATE subscription_orders 
            SET status = 'expired', 
                updated_at = NOW() 
            WHERE id = ?
        ");
        
        if ($updateStmt->execute([$subscriptionOrderId])) {
            // Log the manual expiration
            $logStmt = $conn->prepare("
                INSERT INTO subscription_logs 
                (subscription_order_id, user_id, action, details, admin_id, created_at) 
                VALUES (?, ?, 'manually_expired', ?, ?, NOW())
            ");
            
            $details = "Subscription '{$subscriptionInfo['subscription_title']}' manually ended by admin";
            $logStmt->execute([$subscriptionOrderId, $subscriptionInfo['user_id'], $details, $adminId]);
            
            return [
                'success' => true,
                'message' => "Subscription '{$subscriptionInfo['subscription_title']}' has been ended successfully"
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to update subscription status'
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Handle direct script execution (for cron jobs)
if (php_sapi_name() === 'cli' || isset($_GET['run_auto_end'])) {
    $result = autoEndExpiredSubscriptions($conn);
    
    if (php_sapi_name() === 'cli') {
        // CLI output
        if ($result['success']) {
            echo "SUCCESS: {$result['message']}\n";
            if (!empty($result['errors'])) {
                echo "ERRORS:\n";
                foreach ($result['errors'] as $error) {
                    echo "- $error\n";
                }
            }
        } else {
            echo "ERROR: {$result['error']}\n";
        }
    } else {
        // Web output
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'auto_end_all':
            $result = autoEndExpiredSubscriptions($conn);
            echo json_encode($result);
            break;
            
        case 'auto_end_30day':
            $result = checkAndEnd30DaySubscriptions($conn);
            echo json_encode($result);
            break;
            
        case 'manual_end':
            $subscriptionOrderId = (int)$_POST['subscription_order_id'];
            $adminId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
            $result = manuallyEndSubscription($conn, $subscriptionOrderId, $adminId);
            echo json_encode($result);
            break;
            
        case 'get_expiration_info':
            $subscriptionOrderId = (int)$_POST['subscription_order_id'];
            $result = getSubscriptionExpirationInfo($conn, $subscriptionOrderId);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
    }
    exit;
}
?> 