<?php
// Include necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and is an admin
if ($_SESSION['user_role'] !== 'admin') {
    // Redirect to a login page or error page
    header("Location: ../login.php");
    exit;
}

// Check if the `admin_id` is provided via GET
if (isset($_GET['admin_id'])) {
    $admin_id = $_GET['admin_id'];

    // Create a new Database connection
    $db = new Database();
    $admin = new Admin($db);

    // Attempt to delete the admin
    if ($admin->deleteAdmin($admin_id)) {
        // Set success message
        $_SESSION['message'] = "Admin deleted successfully.";
        $_SESSION['message_class'] = "alert-success";
    } else {
        // Set error message
        $_SESSION['message'] = "Failed to delete admin. Please try again.";
        $_SESSION['message_class'] = "alert-danger";
    }
} else {
    // Set error message if no admin_id is provided
    $_SESSION['message'] = "Invalid admin ID.";
    $_SESSION['message_class'] = "alert-danger";
}

// Redirect back to the manage admins page
header("Location: admin.php");
exit;
