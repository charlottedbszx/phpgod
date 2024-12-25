<?php
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in as a student
if ($_SESSION['user_role'] !== 'student') {
    die("Access denied. This page is only accessible to students.");
}

// Fetch the logged-in student's ID (assuming it's stored in the session)
if (!isset($_SESSION['student_id'])) {
    die("Access denied. Student ID not specified.");
}

$student_id = $_SESSION['student_id'];

// Initialize database and student class
$db = new Database();
$student = new Admin($db);

// Fetch the student's profile data
$student_profile = $student->getStudentProfile($student_id);

if (!$student_profile) {
    die("Student profile not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Side Navigation -->
        <div class="col-md-3 bg-light border-right py-4">
            <?php require_once '../includes/side_nav.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 py-4">
            <h2 class="text-center mb-4 display-4">Student Profile</h2> <!-- Larger heading -->
            <div class="card mx-auto" style="max-width: 600px;"> <!-- Slightly larger card width -->
                <div class="card-body">
                    <h4 class="card-title text-primary fs-3">Welcome, <?php echo htmlspecialchars($student_profile['first_name']); ?>!</h4> <!-- Larger title -->
                    <p class="card-text fs-5">
                        <strong>Full Name:</strong> 
                        <?php 
                        echo htmlspecialchars($student_profile['first_name']) . ' ' . 
                            htmlspecialchars($student_profile['middle_initial']) . '. ' . 
                            htmlspecialchars($student_profile['last_name']); 
                        ?>
                    </p>
                    <p class="card-text fs-5">
                        <strong>Course:</strong> <?php echo htmlspecialchars($student_profile['course']); ?>
                    </p>
                    <p class="card-text fs-5">
                        <strong>Year Level:</strong> <?php echo htmlspecialchars($student_profile['year_level']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
