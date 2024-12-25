<?php

// Include necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and has the correct role
if ($_SESSION['user_role'] !== 'teacher') {
    // Redirect to login page or error page
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

$teacher_college_id = $_SESSION['college_id']; // Teacher's associated college ID
$message = '';
$message_class = '';

// Get the subject_id from the URL
if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']); // Ensure the subject_id is an integer

    // Fetch the existing schedule for the subject
    $schedule = $admin->getScheduleBySubjectId($subject_id);

    // If the schedule exists, populate the form with current schedule data
    if ($schedule) {
        $start_time = $schedule['start_time'];
        $end_time = $schedule['end_time'];
        $teacher_id = $schedule['teacher_id'];
    }
} else {
    // If no subject_id is provided in the URL, redirect back to the courses page
    header("Location: teacher_dashboard.php");
    exit;
}

// Check and display messages
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get new schedule data from the form submission
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $teacher_id = $_POST['teacher_id'];
    $day_of_week = trim($_POST['day_of_week']);

    // Validate the inputs
    if (empty($start_time) || empty($end_time) || empty($teacher_id)) {
        $message = 'Please fill in all fields.';
        $message_class = 'alert-danger';
    } else {
        // Update the schedule
        if ($admin->updateSchedule($subject_id, $start_time, $end_time, $teacher_id, $day_of_week)) {
            $message = 'Schedule updated successfully!';
            $message_class = 'alert-success';
        } else {
            $message = 'Failed to update schedule.';
            $message_class = 'alert-danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Container for the page layout -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main content -->
            <div class="col-md-9">
                <div class="container mt-5">
                    <h2>Edit Schedule</h2>

                    <!-- Display success or error messages -->
                    <?php if (!empty($message)): ?>
                        <div class="alert <?php echo $message_class; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Check if the schedule exists -->
                    <?php if (isset($schedule) && $schedule): ?>
                        <form method="POST" action="edit_schedule.php?subject_id=<?php echo $subject_id; ?>">

                            <!-- Start Time -->
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="time" class="form-control" name="start_time" id="start_time" value="<?php echo htmlspecialchars($start_time); ?>" required>
                            </div>

                            <!-- End Time -->
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" class="form-control" name="end_time" id="end_time" value="<?php echo htmlspecialchars($end_time); ?>" required>
                            </div>

                            <!-- Day of the Week -->
                            <div class="form-group">
                                <label for="day_of_week">Day of the Week</label>
                                <select name="day_of_week" id="day_of_week" class="form-control" required>
                                    <option value="">Select a Day</option>
                                    <?php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    foreach ($days as $day) {
                                        $selected = (isset($schedule['day']) && $schedule['day'] === $day) ? 'selected' : '';
                                        echo "<option value=\"$day\" $selected>$day</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Teacher Selection -->
                            <div class="form-group">
                                <label for="teacher_id">Select Teacher</label>
                                <select name="teacher_id" id="teacher_id" class="form-control" required>
                                    <option value="">Select Teacher</option>
                                    <?php
                                    $teachers = $admin->getTeachersByCollegeId($teacher_college_id);
                                    while ($teacher = $teachers->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = ($teacher['id'] == $teacher_id) ? 'selected' : '';
                                        echo "<option value='" . $teacher['id'] . "' $selected>" . htmlspecialchars($teacher['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Schedule</button>
                        </form>
                    <?php else: ?>
                        <!-- Display a message if no schedule is found -->
                        <div class="alert alert-warning">
                            No schedule found for this subject. Please add a schedule first.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
