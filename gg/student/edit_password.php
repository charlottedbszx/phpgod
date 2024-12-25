<?php
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in as a student
if ($_SESSION['user_role'] !== 'student') {
    die("Access denied. This page is only accessible to students.");
}

// Fetch the logged-in student's ID (assuming it's stored in the session)
if (!isset($_SESSION['student_id'])) {
    die("Access denied. Student ID not specified.");
}

$student_id = $_SESSION['student_id'];

// Initialize database and student class
$db = new Database();
$student = new Admin($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the student's current password from the database
    $stored_password = $student->getStudentPassword($student_id);

    // Check if the current password is correct
    if (!password_verify($current_password, $stored_password)) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        if ($student->updatePassword($student_id, $hashed_password)) {
            $success = "Password updated successfully!";
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Side Navigation -->
        <div class="col-md-3 bg-light border-right py-4">
            <?php require_once '../includes/side_nav.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 py-4">
            <h2 class="text-center mb-4">Edit Password</h2>
            <div class="card mx-auto" style="max-width: 600px;">
                <div class="card-body">
                    <!-- Display success or error message -->
                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php } elseif (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php } ?>

                    <!-- Edit Password Form -->
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
