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

// Check if an admin_id is provided
if (isset($_GET['admin_id'])) {
    $adminId = $_GET['admin_id'];

    // Fetch the admin's current details
    $adminData = $admin->getAdminById($adminId);

    // If admin not found, redirect to the admin list page
    if (!$adminData) {
        $_SESSION['message'] = "Admin not found.";
        $_SESSION['message_class'] = "alert-danger";
        header("Location: admin.php");
        exit();
    }

    // Initialize form data
    $first_name = $adminData['first_name'];
    $last_name = $adminData['last_name'];
    $email = $adminData['email'];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Update the admin's details
        if ($admin->updateAdmin($adminId, $first_name, $last_name, $email, $password)) {
            $_SESSION['message'] = "Admin updated successfully.";
            $_SESSION['message_class'] = "alert-success";
            header("Location: admin.php");
            exit();
        } else {
            $_SESSION['message'] = "Email Already Exists.";
            $_SESSION['message_class'] = "alert-danger";
        }
    }
} else {
    $_SESSION['message'] = "Invalid admin ID.";
    $_SESSION['message_class'] = "alert-danger";
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <div class="col-md-9">
                <div class="container">
                    <h2 class="mt-5">Edit Admin</h2>

                    <!-- Display message -->
                    <?php if (!empty($_SESSION['message'])): ?>
                        <div class="alert <?php echo $_SESSION['message_class']; ?>">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_class']); ?>
                    <?php endif; ?>

                    <!-- Edit Admin Form -->
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password (Leave blank to keep unchanged)</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
