<?php

// Include necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and has the correct role
if ($_SESSION['user_role'] !== 'teacher') {
    // Redirect to login page or error page if not a teacher
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

// Check if the 'id' parameter is passed via GET
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Prepare and execute the delete query
    $delete_success = $admin->deleteStudentById($student_id);

    // Set a session message to indicate success or failure
    if ($delete_success) {
        $_SESSION['message'] = 'Student deleted successfully.';
        $_SESSION['message_class'] = 'alert-success';
    } else {
        $_SESSION['message'] = 'Failed to delete the student.';
        $_SESSION['message_class'] = 'alert-danger';
    }

    // Redirect back to the teacher dashboard
    header("Location: students.php");
    exit;
} else {
    // If 'id' is not passed, redirect to the teacher dashboard with an error message
    $_SESSION['message'] = 'Invalid student ID.';
    $_SESSION['message_class'] = 'alert-danger';
    header("Location: students.php");
    exit;
}
