<?php
// Include the necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and their role
if ($_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

// Get the student ID from the URL
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Fetch the student details from the database using getStudentById
    $student = $admin->getStudentById($student_id);

    if ($student->rowCount() > 0) {
        $student_data = $student->fetch(PDO::FETCH_ASSOC);
        $last_name = $student_data['last_name'];
        $first_name = $student_data['first_name'];
        $middle_initial = $student_data['middle_initial'];
        $email = $student_data['email'];
        $year_level = $student_data['year_level'];
        $course_id = $student_data['course_id'];
    } else {
        // Redirect to dashboard if student not found
        header("Location: students.php");
        exit;
    }
} else {
    header("Location: students.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student'])) {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $email = trim($_POST['email']);
    $year_level = $_POST['year_level'];
    $course_id = $_POST['course_id'];
    $password = trim($_POST['password']);

    if (!empty($last_name) && !empty($first_name) && !empty($email) && !empty($year_level) && !empty($course_id)) {
        // Check if password is empty (do not update if empty)
        if (empty($password)) {
            // Update without password change
            if ($admin->updateStudent($student_id, $last_name, $first_name, $middle_initial, $email, $year_level, $course_id, null)) {
                $_SESSION['message'] = "Student updated successfully!";
                $_SESSION['message_class'] = 'alert-success';
                header("Location: students.php");
                exit;
            } else {
                $message = "Error: Student could not be updated.";
                $message_class = 'alert-danger';
            }
        } else {
            // Update with password change
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($admin->updateStudent($student_id, $last_name, $first_name, $middle_initial, $email, $year_level, $course_id, $hashed_password)) {
                $_SESSION['message'] = "Student updated successfully!";
                $_SESSION['message_class'] = 'alert-success';
                header("Location: students.php");
                exit;
            } else {
                $message = "Error: Student could not be updated.";
                $message_class = 'alert-danger';
            }
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
    <title>Edit Student</title>
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
                    <h2>Edit Student</h2>

                    <!-- Display success or error messages -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?> mt-3">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_initial">Middle Initial</label>
                            <input type="text" class="form-control" id="middle_initial" name="middle_initial" value="<?php echo htmlspecialchars($middle_initial); ?>" maxlength="1">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password (leave blank to keep current password)">
                        </div>

                        <div class="form-group">
                            <label for="year_level">Year Level</label>
                            <select class="form-control" id="year_level" name="year_level" required>
                                <option value="1st Year" <?php echo ($year_level == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($year_level == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($year_level == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo ($year_level == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="course_id">Course</label>
                            <select class="form-control" id="course_id" name="course_id" required>
                                <?php
                                // Fetch courses for the teacher's college
                                $courses = $admin->getCoursesByCollegeId($_SESSION['college_id']);
                                while ($course = $courses->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $course['id'] . "' " . ($course['id'] == $course_id ? 'selected' : '') . ">" . htmlspecialchars($course['course_code']) . " - " . htmlspecialchars($course['course_description']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
