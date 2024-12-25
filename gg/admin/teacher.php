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
    <title>Manage Teachers</title>
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
                    <h2 class="mt-5">Manage Teachers</h2>
                    <?php if (!empty($_SESSION['message'])): ?>
                        <div class="alert <?php echo $_SESSION['message_class']; ?>">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_class']); ?>
                    <?php endif; ?>


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
                    $colleges = $admin->getColleges($search_query);

                    if ($colleges->rowCount() > 0) {
                        echo "<ul class='list-group mt-3'>";
                        while ($row = $colleges->fetch(PDO::FETCH_ASSOC)) {
                            $college_id = $row['id'];
                            $college_name = htmlspecialchars($row['college_name']);

                            echo "<li class='list-group-item'>
                                    <h4>$college_name</h4>";

                            // Fetch teachers for this college
                            $teachers = $admin->getTeachersByCollege($college_id);

                            if ($teachers->rowCount() > 0) {
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
                                while ($teacher = $teachers->fetch(PDO::FETCH_ASSOC)) {
                                    $teacher_id = $teacher['id'];
                                    echo "<tr>
                                            <td>" . htmlspecialchars($teacher['first_name']) . "</td>
                                            <td>" . htmlspecialchars($teacher['last_name']) . "</td>
                                            <td>" . htmlspecialchars($teacher['email']) . "</td>
                                            <td>
                                                <a href='edit_teacher.php?teacher_id=$teacher_id' class='btn btn-warning btn-sm'>Edit</a>
                                                <a href='delete_teacher.php?teacher_id=$teacher_id' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this teacher?\")'>Delete</a>
                                            </td>
                                        </tr>";
                                }
                                echo "</tbody></table>";
                            } else {
                                echo "<div class='alert alert-info'>No teachers added yet.</div>";
                            }

                            echo "<a href='add_teacher.php?college_id=$college_id' class='btn btn-secondary mt-3'>Add Teacher</a>
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
                    