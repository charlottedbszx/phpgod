<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="./another.css" rel="stylesheet" >
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

</head>
<body>
    
<div id="sidebar" class="shadow w-64 h-full bg-blue-100 !bg-blue-100 rounded-tr-xl rounded-br-xl">

    <?php
    if ($_SESSION['user_role'] === 'admin') {
    ?>
    <h3 class="text-center font-bold text-3xl pt-10">Admin Panel</h3>
    <div class="text-bold pt-7 pl-8 sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="colleges.php">Colleges</a>
        <a href="teacher.php">Teachers</a>
        <a href="admin.php">Administrator</a>

        <div class="pt-32 font-bold">
            <a href="../logout.php" 
               class="logout-button text-white px-4 py-2 rounded shadow-md">
               Log Out
            </a>
        </div>
    </div>
    <?php
    }
    ?>
    <?php
    if ($_SESSION['user_role'] === 'teacher') {
    ?>
    <h3 class="text-center font-bold text-3xl pt-10">Teacher Panel</h3>
    <div class="text-bold pt-7 pl-8 sidebar">
        <a href="../teacher/courses.php">Courses</a>
        <a href="../teacher/schedules.php">Schedules</a>
        <a href="../teacher/students.php">Students</a>
        <a href="../teacher/request.php">Requests</a>
        <div class="pt-32 font-bold">
            <a href="../logout.php" 
               class="logout-button text-white px-4 py-2 rounded shadow-md">
               Log Out
            </a>
        </div>
    </div>
    <?php
    }
    ?>

<?php
    if ($_SESSION['user_role'] === 'student') {
    ?>
    <h3 class="text-center font-bold text-3xl pt-10">Student Panel</h3>
    <div class="text-bold pt-7 pl-8 sidebar">
        <a href="user.php">My Schedules</a>
        <a href="profile.php">Profile</a>
        <a href="edit_password.php">Edit Password</a>
        <div class="pt-32 font-bold">
            <a href="../logout.php" 
               class="logout-button text-white px-4 py-2 rounded shadow-md">
               Log Out
            </a>
        </div>
    </div>
    <?php
    }
    ?>
</div>

<script>
    document.querySelectorAll('.sidebarcolor a').forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            document.querySelectorAll('.sidebarcolor a').forEach(link => link.classList.remove('active'));
            // Add active class to the clicked link
            this.classList.add('active');
        });
    });
</script>

</body>
</html>
