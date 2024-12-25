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

$message = '';
$message_class = '';

// Get the subject_id from the query string
if (!isset($_GET['subject_id']) || empty($_GET['subject_id'])) {
    header("Location: courses.php"); // Redirect to the teacher dashboard if no subject ID is provided
    exit;
}

$subject_id = $_GET['subject_id'];

// Fetch the subject details from the database
$subject = $admin->getSubjectById($subject_id);

if ($subject->rowCount() == 0) {
    header("Location: courses.php"); // Redirect to the dashboard if the subject is not found
    exit;
}

$subject_details = $subject->fetch(PDO::FETCH_ASSOC);
$subject_name = $subject_details['subject_name'];
$course_id = $subject_details['course_id'];
$year_level = $subject_details['year_level'];
$subject_code = $subject_details['subject_code']; // Get the subject code

// Handle form submission for editing the subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_subject'])) {
    $new_subject_name = trim($_POST['subject_name']);
    $new_year_level = $_POST['year_level'];
    $new_subject_code = trim($_POST['subject_code']); // Get the new subject code

    if ($admin->editSubject($subject_id, $new_subject_name, $new_year_level, $new_subject_code)) {
        $message = "Subject updated successfully!";
        $message_class = 'alert-success';
    } else {
        $message = "Error: Subject code is already in use!";
        $message_class = 'alert-danger';
    }

    if (!empty($new_subject_name) && !empty($new_year_level) && !empty($new_subject_code)) {
        // Update the subject if name, year level, and subject code are provided
        if ($admin->editSubject($subject_id, $new_subject_name, $new_year_level, $new_subject_code)) {
            $message = "Subject updated successfully!";
            $message_class = 'alert-success';
        } else {
            $message = "Error: Subject could not be updated.";
            $message_class = 'alert-danger';
        }
    } else {
        $message = "Subject name, year level, and subject code cannot be empty!";
        $message_class = 'alert-warning';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Subject</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $message_class; ?> mt-3">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Side Navigation -->
            <div class="col-md-3">
                <?php require_once '../includes/side_nav.php'; ?>
            </div>

            <!-- Main Content (Edit Subject Form) -->
            <div class="col-md-9">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="subject_name">Subject Name</label>
                        <input type="text" class="form-control" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="subject_code">Subject Code</label>
                        <input type="text" class="form-control" name="subject_code" value="<?php echo htmlspecialchars($subject_code); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <select class="form-control" name="year_level" required>
                            <option value="1" <?php echo ($year_level == 1) ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2" <?php echo ($year_level == 2) ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3" <?php echo ($year_level == 3) ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4" <?php echo ($year_level == 4) ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>
                    <button type="submit" name="edit_subject" class="btn btn-primary">Update Subject</button>
                    <a href="courses.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
