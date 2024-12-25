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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $year_level = $_POST['year_level'];
    $course_id = $_POST['course_id'];

    if (!empty($last_name) && !empty($first_name) && !empty($email) && !empty($password) && !empty($year_level) && !empty($course_id)) {
        // Add student
        if ($admin->addStudent($last_name, $first_name, $middle_initial, $email, $password, $year_level, $course_id, $teacher_college_id)) {
            $_SESSION['message'] = "Student added successfully!";
            $_SESSION['message_class'] = 'alert-success';
            header("Location: students.php");
            exit;
        } else {
            $message = "Error: Student could not be added.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "All fields are required!";
        $message_class = 'alert-warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
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
                <div class="container mt-5">
                    <h2>Add Student</h2>

                    <!-- Display success or error messages -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?> mt-3">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_initial">Middle Initial</label>
                            <input type="text" class="form-control" id="middle_initial" name="middle_initial" maxlength="1">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="year_level">Year Level</label>
                            <select class="form-control" id="year_level" name="year_level" required>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="course_id">Course</label>
                            <select class="form-control" id="course_id" name="course_id" required>
                                <?php
                                // Fetch courses within the teacher's college
                                $courses = $admin->getCoursesByCollegeId($teacher_college_id);
                                while ($course = $courses->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $course['id'] . "'>" . htmlspecialchars($course['course_code']) . " - " . htmlspecialchars($course['course_description']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
