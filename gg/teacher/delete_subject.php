<?php

// Include the necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and their role
if ($_SESSION['user_role'] !== 'teacher') {
    // Redirect to a login page or error page
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

// Get the subject_id from the query string
if (isset($_GET['subject_id']) && !empty($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];
    
    // Fetch the subject details to ensure it exists
    $subject = $admin->getSubjectById($subject_id);

    if ($subject->rowCount() > 0) {
        // If the subject exists, delete it
        if ($admin->deleteSubject($subject_id)) {
            $_SESSION['message'] = "Subject deleted successfully!";
            $_SESSION['message_class'] = 'alert-success';
        } else {
            $_SESSION['message'] = "Error: Subject could not be deleted.";
            $_SESSION['message_class'] = 'alert-danger';
        }
    } else {
        $_SESSION['message'] = "Error: Subject not found.";
        $_SESSION['message_class'] = 'alert-danger';
    }

    // Redirect back to the teacher dashboard
    header("Location: courses.php");
    exit;
} else {
    // If subject_id is not set, redirect to the teacher dashboard
    header("Location: courses.php");
    exit;
}

?>
