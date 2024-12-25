<?php
// add_subject.php

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

// Get the course_id from the query string
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    header("Location: courses.php");
    exit;
}

$course_id = $_GET['course_id'];
$message = '';
$message_class = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']); // Trim to prevent empty space entries
    $subject_code = trim($_POST['subject_code']); // Trim subject code to prevent empty space entries
    $year = $_POST['year']; // Get the selected year

    if (!empty($subject_name) && !empty($subject_code) && !empty($year)) {
        // Add subject if name, code, and year are provided
        if ($admin->addSubject($course_id, $subject_name, $subject_code, $year)) {
            $message = "New subject added successfully!";
            $message_class = 'alert-success';
        } else {
            $message = "Error: Subject could not be added.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "Subject name, code, and year cannot be empty!";
        $message_class = 'alert-warning';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 bg-light">
                <?php require_once '../includes/side_nav.php'; ?>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9">
                <h2 class="mt-4">Add Subject</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_class; ?> mt-3">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST" class="mt-3">
                    <div class="form-group">
                        <label for="subject_name">Subject Name</label>
                        <input type="text" class="form-control" name="subject_name" required>
                    </div>
                    <div class="form-group">
                        <label for="subject_code">Subject Code</label>
                        <input type="text" class="form-control" name="subject_code" required>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <select class="form-control" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                    <a href="courses.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </main>
        </div>
    </div>
</body>
</html>
