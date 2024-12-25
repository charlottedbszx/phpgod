<?php
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is a teacher
if ($_SESSION['user_role'] !== 'teacher') {
    die("Access denied. This page is only accessible to teachers.");
}

$db = new Database();
$admin = new Admin($db);

// Fetch the logged-in teacher's college ID (assuming it's stored in the session)
if (!isset($_SESSION['college_id'])) {
    die("Access denied. College not specified.");
}
$college_id = $_SESSION['college_id'];

// Handle Accept/Reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $student_id = $_POST['student_id'];
    try {
        if ($_POST['action'] == 'accept') {
            $admin->acceptStudent($student_id);
            $message = "Student accepted successfully!";
            $message_class = "alert-success";
        } elseif ($_POST['action'] == 'reject') {
            $admin->rejectStudent($student_id);
            $message = "Student rejected successfully!";
            $message_class = "alert-success";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_class = "alert-danger";
    }
}

// Handle Search
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch pending students for the teacher's college
$pending_students = $admin->searchPendingStudentsByCollege($college_id, $search_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Requests</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-5">
    <h2 class="text-center">Pending Student Requests</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $message_class; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Side Navigation -->
        <div class="col-md-3">
            <?php require_once '../includes/side_nav.php'; ?>
        </div>

        <!-- Main Content (Student Requests Table) -->
        <div class="col-md-9">
            <!-- Search Form -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, year level, or course" value="<?php echo htmlspecialchars($search_query); ?>">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Year Level</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pending_students)): ?>
                        <?php foreach ($pending_students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                <td><?php echo htmlspecialchars($student['course_description']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                                    </form>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No pending requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
