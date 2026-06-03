<?php
session_start();
include 'config.php';

$userType = $_SESSION['user_type'] ?? 'guest';
$userId   = $_SESSION['user_id'] ?? null;

$canReview = in_array($userType, ['standard', 'admin']);

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id, username, email, country, profile_pic, user_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Optional: force login for protected pages
function require_login() {
    global $user;
    if (!$user) {
        header("Location: login.php");
        exit;
    }
}
?>