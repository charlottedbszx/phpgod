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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password)) {
        if ($admin->addAdmin($first_name, $last_name, $email, $password)) {
            $_SESSION['message'] = "Admin added successfully!";
            $_SESSION['message_class'] = 'alert-success';
            header("Location: admin.php");
            exit;
        } else {
            $message = "Error: Could not add admin.";
            $message_class = 'alert-danger';
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
    <title>Add Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Side Navigation -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container mt-5">
                    <h2>Add New Admin</h2>

                    <!-- Display message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?>"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <!-- Admin Form -->
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary">Add Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
