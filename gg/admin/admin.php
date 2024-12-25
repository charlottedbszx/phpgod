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

// Initialize search term
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
}

// Fetch all admins based on search query
$admins = $admin->searchAdmins($searchTerm);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
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
                    <h2 class="mt-5">Manage Admins</h2>
                    
                    <!-- Display message -->
                    <?php if (!empty($_SESSION['message'])): ?>
                        <div class="alert <?php echo $_SESSION['message_class']; ?>">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_class']); ?>
                    <?php endif; ?>

                    <!-- Search Form -->
                    <form method="POST" action="" class="form-inline mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Search admins" value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" class="btn btn-primary ml-2">Search</button>
                    </form>

                    <!-- Admins Table -->
                    <h3 class="mt-5">List of Admins</h3>
                    <?php
                    if ($admins->rowCount() > 0) {
                        echo "<table class='table mt-3'>
                                <thead>
                                    <tr>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>";

                        while ($admin_data = $admins->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($admin_data['first_name']) . "</td>
                                    <td>" . htmlspecialchars($admin_data['last_name']) . "</td>
                                    <td>" . htmlspecialchars($admin_data['email']) . "</td>
                                    <td>
                                        <a href='edit_admin.php?admin_id=" . $admin_data['id'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                        <a href='delete_admin.php?admin_id=" . $admin_data['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this admin?\")'>Delete</a>
                                    </td>
                                  </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<div class='alert alert-info mt-3'>No admins found</div>";
                    }
                    ?>

                    <!-- Button to Add Admin -->
                    <a href="add_admin.php" class="btn btn-primary mt-3">Add Admin</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
