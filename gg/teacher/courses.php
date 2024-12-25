<?php

// Include necessary files
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

// Capture search query from the URL if it's set
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// Check if the form is submitted for adding a new subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $course_id = $_POST['course_id'];
    $subject_name = trim($_POST['subject_name']); // Trim to prevent empty space entries

    if (!empty($subject_name) && !empty($course_id)) {
        // Add subject if name is provided
        if ($admin->addSubject($course_id, $subject_name)) {
            $message = "New subject added successfully!";
            $message_class = 'alert-success';
        } else {
            $message = "Error: Subject could not be added.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "Subject name or course ID cannot be empty!";
        $message_class = 'alert-warning';
    }
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

                    <!-- Search Bar -->
                    <form method="GET" action="courses.php" class="form-inline mt-4">
                        <input type="text" name="search_query" class="form-control" placeholder="Search Courses" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn btn-primary ml-2">Search</button>
                    </form>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert <?php echo $_SESSION['message_class']; ?> mt-3">
                            <?php echo htmlspecialchars($_SESSION['message']); ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_class']); ?>
                    <?php endif; ?>

                    <!-- Check and display message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?> mt-3">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- List of Courses -->
                    <h3 class="mt-5">Courses</h3>
                    <?php
                    // Get courses based on the teacher's college and search query
                    $courses = $admin->getCourses($teacher_college_id, $search_query);

                    if ($courses->rowCount() > 0) {
                        echo "<ul class='list-group mt-3'>";
                        while ($course = $courses->fetch(PDO::FETCH_ASSOC)) {
                            $course_id = $course['id'];
                            $course_code = htmlspecialchars($course['course_code']);
                            $course_description = htmlspecialchars($course['course_description']); // Fetch course description

                            echo "<li class='list-group-item'>
                                    <h4>$course_code - $course_description</h4>"; // Display course code and description

                            // Fetch subjects for this course
                            $subjects = $admin->getSubjectsByCourse($course_id);

                            if ($subjects->rowCount() > 0) {
                                echo "<table class='table mt-3'>
                                        <thead>
                                            <tr>
                                                <th>Subject Name</th>
                                                <th>Year</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                while ($subject = $subjects->fetch(PDO::FETCH_ASSOC)) {
                                    $subject_id = $subject['id'];
                                    $subject_name = htmlspecialchars($subject['subject_name']);
                                    $year = htmlspecialchars($subject['year_level']);

                                    echo "<tr>
                                            <td>$subject_name</td>
                                            <td>$year</td>
                                            <td>
                                                <a href='edit_subject.php?subject_id=$subject_id' class='btn btn-warning btn-sm'>Edit</a>
                                                <a href='delete_subject.php?subject_id=$subject_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this subject?\")'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                                echo "</tbody></table>";
                            } else {
                                echo "<div class='alert alert-info'>No subjects added yet.</div>";
                            }

                            // Button to Add Subject (redirect to add_subject.php)
                            echo " <a href='add_subject.php?course_id=$course_id' class='btn btn-primary btn-sm'>Add subject</a></li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<div class='alert alert-info mt-3'>No courses found for your college.</div>";
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</body>
</html>
