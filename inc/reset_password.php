<?php
session_start();
require_once 'db.php';

$newPass = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';

if ($newPass !== $confirmPass) {
    die("Passwords do not match.");
}

$uid = $_SESSION['reset_user_id'] ?? null;

if ($uid) {
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $uid]);
    session_unset();
    session_destroy();
    echo "<script>alert('Password updated successfully'); window.location.href='/amit-kumar/login.php';</script>";
} else {
    echo "<script>alert('Session expired. Try again.'); window.location.href='/amit-/login.php';</script>";
}
