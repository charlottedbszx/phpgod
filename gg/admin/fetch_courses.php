<?php
require_once '../classes/database.class.php';
require_once '../classes/login.class.php';


$db = new Database();
$login = new login($db);

if (isset($_GET['college_id'])) {
    $collegeId = intval($_GET['college_id']);
    $courses = $login->getCoursesByCollege($collegeId);

    foreach ($courses as $course) {
        echo "<option value='{$course['id']}'>{$course['course_description']}</option>";
    }
}
?>
