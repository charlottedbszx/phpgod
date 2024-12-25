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

// Get the teacher ID from the query string
$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;

// Redirect if no valid teacher ID is provided
if ($teacher_id <= 0) {
    $_SESSION['message'] = "Invalid teacher ID.";
    $_SESSION['message_class'] = 'alert-danger';
    header("Location: teacher.php");
    exit;
}

// Fetch teacher details
$teacher = $admin->getTeacherById($teacher_id);
if (!$teacher) {
    $_SESSION['message'] = "Teacher not found.";
    $_SESSION['message_class'] = 'alert-danger';
    header("Location: teacher.php");
    exit;
}

// Update teacher details
$message = '';
$message_class = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Get the password (if entered)

    if (!empty($first_name) && !empty($last_name) && !empty($email)) {
        // Check if password is empty (do not update if empty)
        if (empty($password)) {
            // Update without password change
            if ($admin->updateTeacher($teacher_id, $first_name, $last_name, $middle_name, $email, null)) {
                $_SESSION['message'] = "Teacher updated successfully!";
                $_SESSION['message_class'] = 'alert-success';
                header("Location: teacher.php");
                exit;
            } else {
                $message = "Error: Could not update teacher.";
                $message_class = 'alert-danger';
            }
        } else {
            // Update with password change
            if ($admin->updateTeacher($teacher_id, $first_name, $last_name, $middle_name, $email, $password)) {
                $_SESSION['message'] = "Teacher updated successfully!";
                $_SESSION['message_class'] = 'alert-success';
                header("Location: teacher.php");
                exit;
            } else {
                $message = "Error: Could not update teacher.";
                $message_class = 'alert-danger';
            }
        }
    } else {
        $message = "Please fill in all required fields!";
        $message_class = 'alert-warning';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container">
                <h2 class="mt-5">Edit Teacher</h2>

                    <!-- Display message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?>"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($teacher['middle_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password (leave empty to keep unchanged)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary">Update Teacher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
