<?php
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

if ($_SESSION['user_role'] !== 'admin') {
    // Redirect to a login page or error page
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$admin = new Admin($db);

if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    if ($admin->deleteCourse($course_id)) {
        header('Location: colleges.php'); // Redirect after deletion
    } else {
        echo "Error deleting course.";
    }
}
?>
