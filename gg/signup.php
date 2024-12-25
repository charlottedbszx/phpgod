    <?php
    require_once 'classes/database.class.php';
    require_once 'classes/admin.class.php';
    require_once 'classes/login.class.php';

    $db = new Database();
    $admin = new Admin($db);
    $login = new Login($db);

    // Define error and success messages
    $message = '';
    $message_class = '';

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = [
            'last_name' => $_POST['last_name'],
            'first_name' => $_POST['first_name'],
            'middle_initial' => $_POST['middle_initial'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'year_level' => $_POST['year_level'],
            'college_id' => $_POST['college_id'],
            'course_id' => $_POST['course_id']
        ];

        try {
            $login->addPendingStudent($data);
            $message = "Sign-up request submitted! Please wait for admin approval.";
            $message_class = 'alert-success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_class = 'alert-danger';
        }
    }

    // Fetch colleges for the dropdown
    $colleges = $login->getColleges();

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Sign-Up</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 mt-5">
                <h2 class="text-center">Student Sign-Up</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_class; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="middle_initial">Middle Initial</label>
                        <input type="text" class="form-control" id="middle_initial" name="middle_initial" maxlength="1">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <select class="form-control" id="year_level" name="year_level" required>
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="college_id">College</label>
                        <select class="form-control" id="college_id" name="college_id" required>
                            <option value="">Select College</option>
                            <?php foreach ($colleges as $college): ?>
                                <option value="<?php echo $college['id']; ?>"><?php echo $college['college_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course_id">Course</label>
                        <select class="form-control" id="course_id" name="course_id" required>
                            <option value="">Select Course</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
                </form>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

    <script>
        // Update the courses dropdown based on the selected college
        $(document).ready(function () {
            $('#college_id').change(function () {
                var collegeId = $(this).val();
                if (collegeId) {
                    $.ajax({
                        url: 'admin/fetch_courses.php',
                        type: 'GET',
                        data: { college_id: collegeId },
                        success: function (response) {
                            $('#course_id').html(response);
                        }
                    });
                } else {
                    $('#course_id').html('<option value="">Select Course</option>');
                }
            });
        });
    </script>
    </body>
    </html>
