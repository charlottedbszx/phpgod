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

$teacher_college_id = $_SESSION['college_id']; // Teacher's associated college_id
$message = '';
$message_class = '';

// Check and display messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_class = $_SESSION['message_class'];
    unset($_SESSION['message'], $_SESSION['message_class']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Container for the whole page layout -->
    <div class="container-fluid">
        <div class="row">
            <!-- Side Navigation -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container">
                    <h2 class="mt-5">Courses in <?php echo htmlspecialchars($_SESSION['college_name']); ?></h2>
                    <a href='add_student.php' class='btn btn-primary btn-sm mt-3'>Add Student</a>

                    <!-- Display success or error messages -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?> mt-3">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Search Bar -->
                    <form method="GET" action="" class="form-inline mt-4">
                        <input 
                            type="text" 
                            name="search" 
                            class="form-control mr-2" 
                            placeholder="Search courses..." 
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                    <!-- List of Courses -->
                    <h3 class="mt-5">Courses</h3>
                    <?php
                    // Get the search term if it exists
                    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

                    // Get courses based on the teacher's college and optional search term
                    $courses = $admin->getCoursesByCollegeIds($teacher_college_id, $search_term);

                    if ($courses->rowCount() > 0) {
                        echo "<ul class='list-group mt-3'>";
                        while ($course = $courses->fetch(PDO::FETCH_ASSOC)) {
                            $course_id = $course['id'];
                            $course_code = htmlspecialchars($course['course_code']);
                            $course_description = htmlspecialchars($course['course_description']);

                            echo "<li class='list-group-item'>
                                    <h4>$course_code - $course_description</h4>";

                            // Fetch students for this course
                            $students = $admin->getStudentsByCourseId($course_id);

                            if ($students->rowCount() > 0) {
                                echo "<table class='table mt-3'>
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Email</th>
                                                <th>Year</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                while ($student = $students->fetch(PDO::FETCH_ASSOC)) {
                                    $student_id = $student['id']; // Assuming student has an 'id' field
                                    $student_name = htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_initial'] . '.');
                                    $email = htmlspecialchars($student['email']);
                                    $year = htmlspecialchars($student['year_level']);

                                    echo "<tr>
                                            <td>$student_name</td>
                                            <td>$email</td>
                                            <td>$year</td>
                                            <td>
                                                <!-- Edit Button -->
                                                <a href='edit_student.php?id=$student_id' class='btn btn-warning btn-sm'>Edit</a>

                                                <!-- Delete Button -->
                                                <a href='delete_student.php?id=$student_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this student?\")'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                                echo "</tbody></table>";
                            } else {
                                echo "<div class='alert alert-info'>No students added yet for this course.</div>";
                            }

                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<div class='alert alert-info mt-3'>No courses found for your search term.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
