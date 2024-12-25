<?php
// Include the necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();
if ($_SESSION['user_role'] !== 'admin') {
    // Redirect to a login page or error page
    header("Location: ../login.php");
    exit;
}
// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

// Get the teacher ID from the query string
$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;

// Redirect if no valid teacher ID is provided
if ($teacher_id <= 0) {
    $_SESSION['message'] = "Invalid teacher ID.";
    $_SESSION['message_class'] = 'alert-danger';
    header("Location: teacher.php");
    exit;
}

// Delete the teacher
if ($admin->deleteTeacher($teacher_id)) {
    $_SESSION['message'] = "Teacher deleted successfully!";
    $_SESSION['message_class'] = 'alert-success';
} else {
    $_SESSION['message'] = "Error: Could not delete the teacher.";
    $_SESSION['message_class'] = 'alert-danger';
}

// Redirect back to the manage teachers page
header("Location: teacher.php");
exit;
