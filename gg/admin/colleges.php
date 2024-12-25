<?php

// Include the necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

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

// Check if the form is submitted for adding a new college
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $college_name = $_POST['college_name'];

    if (!empty($college_name)) {
        // Add college if name is provided
        if ($admin->addCollege($college_name)) {
            $message = "New college added successfully!";
            $message_class = 'alert-success';
        } else {
            $message = "Error: College already added.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "College name cannot be empty!";
        $message_class = 'alert-warning';
    }
}

// Handle search query
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add College</title>
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
                    <h2 class="mt-5">Add College</h2>

                    <!-- Check and display message -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert <?php echo $_SESSION['message_class']; ?> mt-3">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message']); unset($_SESSION['message_class']); ?>
                    <?php endif; ?>

                    <!-- Add College Form -->
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="college_name">College Name</label>
                            <input type="text" class="form-control" id="college_name" name="college_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="submit">Add College</button>
                    </form>

                    <!-- Search Bar -->
                    <form method="GET" action="" class="mt-5">
                        <div class="form-group">
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search for colleges">
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                    <!-- List of Colleges -->
                    <h3 class="mt-5">List of Colleges</h3>
                    <?php
                    // Get the list of colleges
                    $colleges = $admin->getColleges($search_query);

                    if ($colleges->rowCount() > 0) {
                        echo "<ul class='list-group mt-3'>";
                        while ($row = $colleges->fetch(PDO::FETCH_ASSOC)) {
                            $college_id = $row['id'];
                            $college_name = htmlspecialchars($row['college_name']);

                            echo "<li class='list-group-item'>
                                    <h4>$college_name</h4>";

                            // Fetch courses for this college
                            $courses = $admin->getCoursesByCollege($college_id);

                            if ($courses->rowCount() > 0) {
                                echo "<table class='table mt-3'>
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Description</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                while ($course = $courses->fetch(PDO::FETCH_ASSOC)) {
                                    $course_id = $course['id'];
                                    echo "<tr>
                                            <td>" . htmlspecialchars($course['course_code']) . "</td>
                                            <td>" . htmlspecialchars($course['course_description']) . "</td>
                                            <td>
                                                <a href='edit_course.php?course_id=$course_id' class='btn btn-warning btn-sm'>Edit</a>
                                                <a href='delete_course.php?course_id=$course_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this course?\")'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                                echo "</tbody></table>";
                            } else {
                                echo "<div class='alert alert-info'>No courses added yet.</div>";
                            }

                            echo "<a href='add_course.php?college_id=$college_id' class='btn btn-secondary mt-3'>Add Course</a>
                            </li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<div class='alert alert-info mt-3'>No colleges found</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
