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

// Check if the subject_id is passed in the URL
if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);

    // Fetch the schedule for this subject
    $schedule = $admin->getScheduleBySubjectId($subject_id);
} else {
    // Redirect to the previous page or show an error if no subject_id is passed
    header("Location: teacher_dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Schedule</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main content -->
            <div class="col-md-9">
                <div class="container mt-5">
                    <h2>Schedule for Subject</h2>
                    
                    <?php if ($schedule): ?>
                        <table class="table table-bordered mt-4">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Teacher</th>
                                    <th>Subject</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($schedule['day']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['end_time']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['teacher_name']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['subject_name']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-danger mt-3">
                            No schedule found for this subject.
                        </div>
                    <?php endif; ?>

                    <a href="schedules.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
