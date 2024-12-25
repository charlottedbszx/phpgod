<?php
// Include the necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and their role
if ($_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$admin = new Admin($db);

$teacher_college_id = $_SESSION['college_id']; // Teacher's associated college ID

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);
    
    // Fetch course_id associated with the subject (you can adjust this based on your schema)
    $course = $admin->getCourseBySubjectId($subject_id);
    if ($course) {
        $course_id = $course['course_id']; // Assuming the course ID is fetched successfully
    } else {
        // Handle the case when no course is found for the subject (optional)
        $error_message = "No course found for the selected subject.";
    }
    
    // Check if a schedule already exists for this subject
    $existing_schedule = $admin->getScheduleBySubjectId($subject_id); // Assume getScheduleBySubjectId method exists
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = intval($_POST['subject_id']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']);
    $teacher_id = intval($_POST['teacher_id']);
    $day_of_week = trim($_POST['day_of_week']); // Fetch the day of the week

    // Automatically fetch course_id based on the subject_id
    $course = $admin->getCourseBySubjectId($subject_id);
    $course_id = $course['course_id']; // Ensure this matches your actual column

    if (!empty($start_time) && !empty($end_time) && $teacher_id > 0 && !empty($day_of_week)) {
        if ($admin->setSchedule($subject_id, $start_time, $end_time, $day_of_week, $teacher_id, $course_id)) {
            $_SESSION['message'] = "Schedule set successfully!";
            $_SESSION['message_class'] = 'alert-success';
            header("Location: schedules.php");
            exit;
        } else {
            $error_message = "Failed to set schedule.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}


// Fetch teachers from the same college
$teachers = $admin->getTeachersByCollegeId($teacher_college_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Schedule</title>
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
                    <h2>Set Schedule for Subject</h2>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <?php if ($existing_schedule): ?>
                        <div class="alert alert-info">A schedule has already been set for this subject.</div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">

                            <!-- Start Time -->
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="time" name="start_time" id="start_time" class="form-control" required>
                            </div>

                            <!-- End Time -->
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" name="end_time" id="end_time" class="form-control" required>
                            </div>


                            <div class="form-group">
                            <label for="day_of_week">Day of the Week</label>
                            <select name="day_of_week" id="day_of_week" class="form-control" required>
                                <option value="">Select a Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>

                            <!-- Teacher Selection -->
                            <div class="form-group">
                                <label for="teacher_id">Select Teacher</label>
                                <select name="teacher_id" id="teacher_id" class="form-control" required>
                                    <option value="">Select a Teacher</option>
                                    <?php while ($teacher = $teachers->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Schedule</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
