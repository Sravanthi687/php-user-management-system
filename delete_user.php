<?php
// delete_user.php
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            // Redirect back to manage users with a success parameter
            header("Location: manage_users.php?deleted=1");
            exit;
        } else {
            die("Database statement preparation failed.");
        }
    } catch (Exception $e) {
        die("Error deleting user: " . $e->getMessage());
    }
} else {
    header("Location: manage_users.php");
    exit;
}
?>
