<?php

// Include the necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start(); // Start session to store messages
// Create a new Database connection

if ($_SESSION['user_role'] !== 'admin') {
    // Redirect to a login page or error page
    header("Location: ../login.php");
    exit;
}

$db = new Database();
$admin = new Admin($db);

$message = '';
$message_class = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $college_id = $_POST['college_id'];
    $course_code = $_POST['course_code'];
    $course_description = $_POST['course_description'];

    // Validate input
    if (!empty($course_code) && !empty($course_description)) {
        // Add course if course code and description are provided
        if ($admin->addCourse($college_id, $course_code, $course_description)) {
            // Set success message in session
            $_SESSION['message'] = "New course added successfully!";
            $_SESSION['message_class'] = 'alert-success';
            header("location: colleges.php"); // Redirect after successful add
            exit; // Ensure no further code execution
        } else {
            $message = "Error: Could not add the course.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "Course code and description cannot be empty!";
        $message_class = 'alert-warning';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Container for the page layout -->
    <div class="container-fluid">
        <div class="row">
            <!-- Side Navigation -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container">
                    <h2 class="mt-5">Add Course</h2>

                    <!-- Display message if any -->
                    <?php if (isset($message)): ?>
                        <div class="alert <?php echo $message_class; ?> mt-3"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <!-- Add Course Form -->
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="course_code">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" required>
                        </div>
                        <div class="form-group">
                            <label for="course_description">Course Description</label>
                            <input type="text" class="form-control" id="course_description" name="course_description" required>
                        </div>
                        <input type="hidden" name="college_id" value="<?php echo $_GET['college_id']; ?>">
                        <button type="submit" class="btn btn-primary" name="submit">Add Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
