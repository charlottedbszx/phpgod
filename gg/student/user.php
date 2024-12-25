<?php

// Include necessary files
require_once '../classes/database.class.php';
require_once '../classes/admin.class.php';

session_start();

// Check if the user is logged in and their role is student
if ($_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

// Create a new Database connection
$db = new Database();
$user = new Admin($db);

// Get the student's course_id, year_level, and student_id from the session
$student_course_id = $_SESSION['course_id'];
$student_year_level = $_SESSION['year_level'];
$student_id = $_SESSION['student_id'];
$message = '';
$message_class = '';

// Fetch the student's full name
$full_name = $user->getStudentFullName($student_id);

// Check if the form is submitted to add subjects to the student's schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject_ids'])) {
    $subject_ids = $_POST['subject_ids']; // Array of selected subject IDs

    $success = true;
    foreach ($subject_ids as $subject_id) {
        $result = $user->addSubjectToSchedule($student_id, $subject_id);

        if (!$result) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $_SESSION['message'] = 'Selected subjects added to your schedule successfully!';
        $_SESSION['message_class'] = 'alert-success';
    } else {
        $_SESSION['message'] = 'Failed to add some subjects. Please try again.';
        $_SESSION['message_class'] = 'alert-danger';
    }

    header("Location: user.php");
    exit;
}

// Check if the student already has records in student_schedules
$student_has_schedule = $user->studentHasSchedule($student_id);

// Fetch available subjects only if the student doesn't already have a schedule
if (!$student_has_schedule) {
    $available_subjects = $user->getAvailableSubjects($student_id);
}

// Fetch the student's schedule
$schedule = $user->getStudentSchedule($student_id);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
                <div class="container">
                    <h2 class="mt-5">Subjects for <?php echo htmlspecialchars($full_name); ?></h2>

                    <!-- Display any messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert <?php echo $_SESSION['message_class']; ?> mt-3">
                            <?php echo htmlspecialchars($_SESSION['message']); ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_class']); ?>
                    <?php endif; ?>

                    <!-- Available Subjects -->
                    <?php if (!$student_has_schedule): ?>
                        <h3 class="mt-5">Available Subjects</h3>
                        <form action="" method="POST" id="subject-selection-form">
                            <?php
                            if ($available_subjects->rowCount() > 0) {
                                echo "<ul class='list-group mt-3'>";
                                while ($subject = $available_subjects->fetch(PDO::FETCH_ASSOC)) {
                                    $subject_name = htmlspecialchars($subject['subject_name']);
                                    $course_code = htmlspecialchars($subject['subject_code']);
                                    $year_level = htmlspecialchars($subject['year_level']);
                                    $day = htmlspecialchars($subject['day']);
                                    $start_time = htmlspecialchars($subject['start_time']);
                                    $end_time = htmlspecialchars($subject['end_time']);
                                    $subject_id = $subject['id'];
                                    $start_time_formatted = $start_time ? date("g:i A", strtotime($start_time)) : 'N/A';
                                    $end_time_formatted = $end_time ? date("g:i A", strtotime($end_time)) : 'N/A';



                                    echo "<li class='list-group-item'>
                                            <input type='checkbox' name='subject_ids[]' value='$subject_id' class='mr-2'>
                                            <span><strong>$subject_name</strong> - $year_level</span>
                                            <p>Subject Code: $course_code</p>
                                            <p>Day: $day</p>
                                            <p>Time: $start_time_formatted - $end_time_formatted</p>
                                          </li>";
                                }
                                echo "</ul>";
                                echo "<button type='submit' class='btn btn-primary mt-3'>Add Selected Subjects to Schedule</button>";
                            } else {
                                echo "<div class='alert alert-info mt-3'>No more subjects available for your course and year level.</div>";
                            }
                            ?>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mt-5">Please check your current schedule below.</div>
                    <?php endif; ?>

                    <!-- Student's Schedule -->
                    <h3 class="mt-5">Your Schedule</h3>
                    <?php
                    if ($schedule->rowCount() > 0) {
                        echo "<ul class='list-group mt-3'>";
                        while ($subject = $schedule->fetch(PDO::FETCH_ASSOC)) {
                            $subject_name = htmlspecialchars($subject['subject_name']);
                            $course_code = htmlspecialchars($subject['subject_code']);
                            $year_level = htmlspecialchars($subject['year_level']);
                            $day = htmlspecialchars($subject['day']);
                            $start_time = htmlspecialchars($subject['start_time']);
                            $end_time = htmlspecialchars($subject['end_time']);
                            $start_time_formatted = $start_time ? date("g:i A", strtotime($start_time)) : 'N/A';
                            $end_time_formatted = $end_time ? date("g:i A", strtotime($end_time)) : 'N/A';

                            echo "<li class='list-group-item'>
                                    <h4>$subject_name - Year $year_level</h4>
                                    <p>Course Code: $course_code</p>
                                    <p>Day: $day</p>
                                    <p>Time: $start_time_formatted - $end_time_formatted</p>
                                  </li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<div class='alert alert-info mt-3'>You haven't added any subjects to your schedule.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hide the form after submission
        const form = document.getElementById('subject-selection-form');
        if (form) {
            form.addEventListener('submit', function() {
                form.style.display = 'none';
            });
        }
    </script>
</body>
</html>
        