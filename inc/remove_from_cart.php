<?php
session_start();
$key = $_GET['key'] ?? '';
if (isset($_SESSION['cart'][$key])) {
    unset($_SESSION['cart'][$key]);
}
header('Location: ../cart');
exit;
