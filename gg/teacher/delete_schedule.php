<?php

// Include necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and has the correct role
if ($_SESSION['user_role'] !== 'teacher') {
    // Redirect to login page or error page
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

// Get the subject_id from the URL
if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']); // Ensure the subject_id is an integer

    // Call the delete method to delete the schedule
    if ($admin->deleteSchedule($subject_id)) {
        // Set a success message in session
        $_SESSION['message'] = 'Schedule deleted successfully!';
        $_SESSION['message_class'] = 'alert-success';
    } else {
        // Set an error message in session
        $_SESSION['message'] = 'Failed to delete schedule.';
        $_SESSION['message_class'] = 'alert-danger';
    }

    // Redirect back to the teacher dashboard or courses page
    header("Location: schedules.php");
    exit;
} else {
    // Redirect if subject_id is not set
    header("Location: schedules.php");
    exit;
}
?>
