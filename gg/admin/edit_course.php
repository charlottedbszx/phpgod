<?php
// Include necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

// Start session to store the message
session_start();

if ($_SESSION['user_role'] !== 'admin') {
    // Redirect to a login page or error page
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

$message = '';
$message_class = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $course_code = $_POST['course_code'];
    $course_description = $_POST['course_description'];

    // Update course logic
    if (!empty($course_code) && !empty($course_description)) {
        // Update the course if all fields are provided
        if ($admin->updateCourse($course_id, $course_code, $course_description)) {
            // Set session message and redirect
            $_SESSION['message'] = "Course updated successfully!";
            $_SESSION['message_class'] = 'alert-success';
            header("location: colleges.php"); // Redirect after success
            exit; // Always call exit after header redirect to prevent further code execution
        } else {
            $message = "Error: Could not update the course.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "Course code and description cannot be empty!";
        $message_class = 'alert-warning';
    }
} else {
    // Fetch course details by ID
    $course_id = $_GET['course_id'];
    $course = $admin->getCourseById($course_id);
    if ($course) {
        $course_code = $course['course_code'];
        $course_description = $course['course_description'];
    } else {
        $message = "Course not found.";
        $message_class = 'alert-danger';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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
                    <h2 class="mt-5">Edit Course</h2>

                    <!-- Display message if any -->
                    <?php if (isset($message)): ?>
                        <div class="alert <?php echo $message_class; ?> mt-3"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <!-- Edit Course Form -->
                    <form action="edit_course.php" method="POST">
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <div class="form-group">
                            <label for="course_code">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="course_description">Course Description</label>
                            <input type="text" class="form-control" id="course_description" name="course_description" value="<?php echo htmlspecialchars($course_description); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
