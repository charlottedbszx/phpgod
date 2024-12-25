
<?php
require_once 'classes/database.class.php';
require_once 'classes/login.class.php';
require_once 'classes/admin.class.php';
session_start(); // Ensure session is started as the first thing

// Check if the user is already logged in
if (isset($_SESSION['user_role'])) {
    // Redirect to the appropriate dashboard based on their role
    if ($_SESSION['user_role'] == 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else if ($_SESSION['user_role'] == 'teacher') {
        header("Location: teacher/courses.php");
        exit;
    } else if ($_SESSION['user_role'] == 'student') {
        header("Location: test.php");
        exit;
    }
}

// Include necessary files
require_once 'classes/database.class.php';
require_once 'classes/login.class.php';

// Create a new Database connection
$db = new Database();
$login = new Login($db);

// Define error and success message variables
$message = '';
$message_class = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email and password are provided
    if (!empty($email) && !empty($password)) {
        // Check if the email exists in the admins table
        $adminId = $login->checkAdminLogin($email, $password);

        if ($adminId) {
            // Admin login successful
            $_SESSION['admin_id'] = $adminId;
            $_SESSION['user_role'] = 'admin'; // Assign role as admin
            header("Location: admin/dashboard.php");
            exit;
        }

        // Check if the email exists in the teachers table
        $teacherData = $login->checkTeacherLogin($email, $password);

        if ($teacherData) {
            // Teacher login successful
            $_SESSION['teacher_id'] = $teacherData['id'];
            $_SESSION['user_role'] = 'teacher'; // Assign role as teacher

            // Fetch and store the teacher's college_id and college_name
            $_SESSION['college_id'] = $teacherData['college_id'];
            $collegeName = $login->getCollegeName($teacherData['college_id']);
            $_SESSION['college_name'] = $collegeName;

            header("Location: teacher/courses.php");
            exit;
        }

        // Check if the email exists in the students table
        // Check if the email exists in the students table
        $studentData = $login->checkStudentLogin($email, $password);

        if ($studentData) {
            // Student login successful
            $_SESSION['student_id'] = $studentData['id'];
            $_SESSION['user_role'] = 'student'; // Assign role as student

            // Fetch and store the student's year_level and course_id
            $_SESSION['year_level'] = $studentData['year_level'];
            $_SESSION['course_id'] = $studentData['course_id'];

            header("Location: student/user.php");
            exit;
        }


        // If no match, show error
        $message = "Invalid email or password.";
        $message_class = 'alert-danger';
    } else {
        $message = "Please enter both email and password.";
        $message_class = 'alert-warning';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-5">
                <h2 class="text-center">Login</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_class; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter Email">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter Password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                <p>Don't have an Account? <a href="signup.php"><u>Create Account</u></a></p>
            </div>
        </div>
    </div>
</body>
</html>
