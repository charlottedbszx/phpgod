<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    
    public function addCollege($college_name) {
        // Check if the college already exists
        $check_sql = "SELECT * FROM college WHERE college_name = :college_name";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bindParam(':college_name', $college_name, PDO::PARAM_STR);
        $check_stmt->execute();
    
        if ($check_stmt->rowCount() > 0) {
            return false; // College already exists
        }
    
        // Insert the new college
        $sql = "INSERT INTO college (college_name) VALUES (:college_name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':college_name', $college_name, PDO::PARAM_STR);
    
        try {
            $stmt->execute();
            return true; // College added successfully
        } catch (PDOException $e) {
            return false; // Error occurred
        }
    }
    

    public function getColleges($search_query = '') {
        $sql = "SELECT * FROM college";
    
        if (!empty($search_query)) {
            $sql .= " WHERE college_name LIKE :search_query 
                      OR EXISTS (SELECT 1 FROM course WHERE course.college_id = college.id 
                                 AND (course_code LIKE :search_query OR course_description LIKE :search_query))
                      OR EXISTS (SELECT 1 FROM students WHERE (students.first_name LIKE :search_query 
                                 OR students.last_name LIKE :search_query))";
        }   
    
        $stmt = $this->conn->prepare($sql);
    
        if (!empty($search_query)) {
            // Bind the search query to the prepared statement
            $stmt->bindValue(':search_query', '%' . $search_query . '%');
        }
    
        $stmt->execute();
        return $stmt;
    }


    public function deleteAdmin($admin_id) {
        try {
            // Prepare the delete query
            $sql = "DELETE FROM admins WHERE id = :admin_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    
            // Execute the query
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting admin: " . $e->getMessage());
            return false;
        }
    }
    
    
    

    public function getCoursesByCollege($college_id) {
        $sql = "SELECT * FROM course WHERE college_id = :college_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function addCourse($college_id, $course_code, $course_description) {
        // Check if the course already exists by searching for the same course code
        $sql_check = "SELECT COUNT(*) FROM course WHERE college_id = :college_id AND course_code = :course_code";
        
        // Prepare the statement
        $stmt_check = $this->conn->prepare($sql_check);
        
        // Bind the parameters to the query
        $stmt_check->bindParam(':college_id', $college_id, PDO::PARAM_INT);
        $stmt_check->bindParam(':course_code', $course_code, PDO::PARAM_STR);
        
        // Execute the check query
        $stmt_check->execute();
        
        // Fetch the result to see if any rows are returned
        $existing_course = $stmt_check->fetchColumn();
        
        // If the course exists, return false or a relevant error message
        if ($existing_course > 0) {
            return false; // Or you can return a specific message like "Course already exists"
        }
        
        // Prepare the SQL query to insert course data into the database
        $sql = "INSERT INTO course (college_id, course_code, course_description) VALUES (:college_id, :course_code, :course_description)";
        
        // Prepare the statement
        $stmt = $this->conn->prepare($sql);
        
        // Bind the parameters to the query
        $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
        $stmt->bindParam(':course_code', $course_code, PDO::PARAM_STR);
        $stmt->bindParam(':course_description', $course_description, PDO::PARAM_STR);
        
        // Execute the query and return true if successful
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateCourse($course_id, $course_code, $course_description) {
        $query = "UPDATE course SET course_code = ?, course_description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$course_code, $course_description, $course_id]);
    }
    
    public function deleteCourse($course_id) {
        $query = "DELETE FROM course WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$course_id]);
    }
    
    public function getCourseById($course_id) {
        // Prepare SQL query to select course by ID
        $query = "SELECT * FROM course WHERE id = :course_id LIMIT 1";
        $stmt = $this->conn->prepare($query);

        // Bind the course_id to the query parameter
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);

        // Execute the query and fetch the course data
        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);  // Return course data as an associative array
        }
        return false;  // Return false if course not found
    }

    public function getTeachersByCollege($college_id) {
        $sql = "SELECT id, first_name, last_name, middle_name, email FROM teachers WHERE college_id = :college_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function addTeacher($first_name, $last_name, $middle_name, $email, $password, $college_id) {
        try {
            // Check if the email already exists in the teachers table
            $checkSqlTeachers = "SELECT COUNT(*) FROM teachers WHERE email = :email";
            $checkStmtTeachers = $this->conn->prepare($checkSqlTeachers);
            $checkStmtTeachers->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmtTeachers->execute();
            $emailExistsTeachers = $checkStmtTeachers->fetchColumn();
    
            // Check if the email already exists in the admins table
            $checkSqlAdmins = "SELECT COUNT(*) FROM admins WHERE email = :email";
            $checkStmtAdmins = $this->conn->prepare($checkSqlAdmins);
            $checkStmtAdmins->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmtAdmins->execute();
            $emailExistsAdmins = $checkStmtAdmins->fetchColumn();
    
            // Check if the email already exists in the students table
            $checkSqlStudents = "SELECT COUNT(*) FROM students WHERE email = :email";
            $checkStmtStudents = $this->conn->prepare($checkSqlStudents);
            $checkStmtStudents->bindParam(':email', $email, PDO::PARAM_STR);
            $checkStmtStudents->execute();
            $emailExistsStudents = $checkStmtStudents->fetchColumn();
    
            // If email exists in any table, deny the addition
            if ($emailExistsTeachers > 0 || $emailExistsAdmins > 0 || $emailExistsStudents > 0) {
                return false; // Email already exists
            }
    
            // If email does not exist, proceed with the insertion
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO teachers (first_name, last_name, middle_name, email, password, role, college_id) 
                    VALUES (:first_name, :last_name, :middle_name, :email, :password, 'teacher', :college_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $stmt->bindParam(':middle_name', $middle_name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error adding teacher: " . $e->getMessage());
            return false;
        }
    }
    
    

    public function getTeacherById($teacher_id) {
        $sql = "SELECT * FROM teachers WHERE id = :teacher_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
    public function deleteTeacher($teacher_id)
    {
        try {
            $query = "DELETE FROM teachers WHERE id = :teacher_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting teacher: " . $e->getMessage());
            return false;
        }
    }

    public function getAllAdmins()
{
    try {
        $query = "SELECT * FROM admins"; // Assuming the table is called 'admins'
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    } catch (PDOException $e) {
        error_log("Error fetching admins: " . $e->getMessage());
        return false;
    }
}

public function addAdmin($first_name, $last_name, $email, $password)
{
    try {
        // Check if the email already exists in the admins table
        $query = "SELECT COUNT(*) FROM admins WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $adminResult = $stmt->fetchColumn();

        // Check if the email already exists in the teachers table
        $query = "SELECT COUNT(*) FROM teachers WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $teacherResult = $stmt->fetchColumn();

        // Check if the email already exists in the students table
        $query = "SELECT COUNT(*) FROM students WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $studentResult = $stmt->fetchColumn();

        // If the email already exists in any of the tables, return false
        if ($adminResult > 0 || $teacherResult > 0 || $studentResult > 0) {
            return false; // Email already exists in admins, teachers, or students
        }

        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the admin into the database
        $query = "INSERT INTO admins (first_name, last_name, email, password) 
                  VALUES (:first_name, :last_name, :email, :password)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error adding admin: " . $e->getMessage());
        return false;
    }
}


public function searchAdmins($searchTerm) {
    $query = "SELECT * FROM admins WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['search' => '%' . $searchTerm . '%']);
    return $stmt;
}

public function getAdminById($adminId) {
    $query = "SELECT * FROM admins WHERE id = :admin_id LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function updateAdmin($adminId, $firstName, $lastName, $email, $password) {
    try {
        // Check if the email already exists in the admins table for another admin
        $checkEmailQuery = "SELECT id FROM admins WHERE email = :email AND id != :adminId";
        $stmt = $this->conn->prepare($checkEmailQuery);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists for another admin
            return false;
        }

        // Check if the email already exists in the teachers table
        $checkTeacherQuery = "SELECT id FROM teachers WHERE email = :email";
        $stmt = $this->conn->prepare($checkTeacherQuery);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists for a teacher
            return false;
        }

        // Check if the email already exists in the students table
        $checkStudentQuery = "SELECT id FROM students WHERE email = :email";
        $stmt = $this->conn->prepare($checkStudentQuery);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists for a student
            return false;
        }

        // Prepare the update query for admins
        $updateQuery = "UPDATE admins SET first_name = :firstName, last_name = :lastName, email = :email";

        // Include password update if provided
        if (!empty($password)) {
            $updateQuery .= ", password = :password";
        }

        $updateQuery .= " WHERE id = :adminId";

        $stmt = $this->conn->prepare($updateQuery);

        // Bind parameters
        $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        }

        // Execute the query
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating admin: " . $e->getMessage());
        return false;
    }
}


public function updateTeacher($teacher_id, $first_name, $last_name, $middle_name, $email, $password = null) {
    try {
        // Check if the email already exists in the admins, teachers (excluding current teacher), or students table
        $sql_check = "
            SELECT id FROM teachers WHERE email = :email AND id != :teacher_id
            UNION
            SELECT id FROM admins WHERE email = :email
            UNION
            SELECT id FROM students WHERE email = :email";
        $stmt_check = $this->conn->prepare($sql_check);
        $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_check->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
        $stmt_check->execute();

        // If email exists in any of the tables, deny the update
        if ($stmt_check->rowCount() > 0) {
            return false; // Email exists in admins, teachers, or students table
        }

        // If password is provided, hash it; otherwise, don't update the password
        if ($password !== null) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE teachers 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        middle_name = :middle_name, 
                        email = :email, 
                        password = :password 
                    WHERE id = :teacher_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        } else {
            $sql = "UPDATE teachers 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        middle_name = :middle_name, 
                        email = :email 
                    WHERE id = :teacher_id";
            $stmt = $this->conn->prepare($sql);
        }

        // Bind the remaining parameters
        $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindParam(':middle_name', $middle_name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);

        // Execute the update
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error updating teacher: " . $e->getMessage());
        return false;
    }
}


public function getCoursesByCollegeId($college_id) {
    $query = "SELECT * FROM course WHERE college_id = :college_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

public function getSubjectsByCourse($course_id) {
    $query = "SELECT * FROM subject WHERE course_id = :course_id ORDER BY year_level";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

public function addSubject($course_id, $subject_name, $subject_code, $year) {
    // Check if subject already exists
    $checkQuery = "SELECT COUNT(*) FROM subject WHERE course_id = :course_id AND subject_code = :subject_code";
    $stmt = $this->conn->prepare($checkQuery);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject_code', $subject_code, PDO::PARAM_STR);
    $stmt->execute();

    // If subject exists, return false
    if ($stmt->fetchColumn() > 0) {
        return false; // Subject already exists
    }

    // Insert new subject with year
    $query = "INSERT INTO subject (course_id, subject_name, subject_code, year_level) 
              VALUES (:course_id, :subject_name, :subject_code, :year_level)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject_name', $subject_name, PDO::PARAM_STR);
    $stmt->bindParam(':subject_code', $subject_code, PDO::PARAM_STR);
    $stmt->bindParam(':year_level', $year, PDO::PARAM_INT);

    return $stmt->execute();
}

// Method to get subject details by subject ID
public function getSubjectById($subject_id) {
    $query = "SELECT * FROM subject WHERE id = :subject_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

public function editSubject($subject_id, $subject_name, $year_level, $subject_code) {
    // Check if the subject code already exists for a different subject
    $sql = "SELECT COUNT(*) FROM subject WHERE subject_code = :subject_code AND id != :subject_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':subject_code', $subject_code);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->execute();
    
    // If the subject code already exists for another subject, return false
    if ($stmt->fetchColumn() > 0) {
        return false; // Duplicate found
    }
    
    // Proceed with the update if no duplicate is found
    $sql = "UPDATE subject SET subject_name = :subject_name, year_level = :year_level, subject_code = :subject_code WHERE id = :subject_id";
    $stmt = $this->conn->prepare($sql);
    
    $stmt->bindParam(':subject_name', $subject_name);
    $stmt->bindParam(':year_level', $year_level);
    $stmt->bindParam(':subject_code', $subject_code);
    $stmt->bindParam(':subject_id', $subject_id);

    return $stmt->execute();
}

public function deleteSubject($subject_id) {
    $sql = "DELETE FROM subject WHERE id = :subject_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':subject_id', $subject_id);

    return $stmt->execute();
}

public function getSchedulesByCourse($course_id) {
    try {
        $query = "SELECT id, day, time FROM schedules WHERE course_id = :course_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt; // Returns the PDO statement
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

public function setSchedule($subject_id, $start_time, $end_time, $day_of_week, $teacher_id, $course_id) {
    $query = "INSERT INTO schedules (subject_id, start_time, end_time, day, teacher_id, course_id)
              VALUES (:subject_id, :start_time, :end_time, :day, :teacher_id, :course_id)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':day', $day_of_week);
    $stmt->bindParam(':teacher_id', $teacher_id);
    $stmt->bindParam(':course_id', $course_id);

    return $stmt->execute();
}



public function getTeachersByCollegeId($college_id) {
    $sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name 
            FROM teachers 
            WHERE college_id = :college_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

// In the Admin class
public function getCourseBySubjectId($subject_id) {
    $sql = "SELECT course_id FROM subject WHERE id = :subject_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC); // Return the course associated with the subject
}

public function getScheduleBySubjectId($subject_id) {
    $query = "SELECT schedules.start_time, schedules.end_time, schedules.teacher_id,schedules.day,
                     CONCAT(teachers.last_name, ', ', teachers.first_name, ' ', LEFT(teachers.middle_name, 1), '.') AS teacher_name,
                     course.course_description,
                     subject.subject_name
              FROM schedules
              JOIN teachers ON schedules.teacher_id = teachers.id
              JOIN course ON schedules.course_id = course.id
              JOIN subject ON schedules.subject_id = subject.id
              WHERE schedules.subject_id = :subject_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC); // Return the schedule details
}

public function getStudentsByCourseId($course_id) {
    $query = "SELECT students.id,students.last_name, students.first_name, students.middle_initial, students.email, students.year_level
              FROM students
              WHERE students.course_id = :course_id
              ORDER BY students.year_level asc";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt; // Return the PDO statement to fetch data
}

public function addStudent($last_name, $first_name, $middle_initial, $email, $password, $year_level, $course_id, $college_id) {
    try {
        // Check if the email already exists in the admins table
        $checkAdminQuery = "SELECT id FROM admins WHERE email = :email";
        $stmt = $this->conn->prepare($checkAdminQuery);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists for an admin
            return false;
        }

        // Check if the email already exists in the teachers table
        $checkTeacherQuery = "SELECT id FROM teachers WHERE email = :email";
        $stmt = $this->conn->prepare($checkTeacherQuery);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists for a teacher
            return false;
        }

        // Check if the email already exists in the students table
        $checkStudentQuery = "SELECT id FROM students WHERE email = :email";
        $stmt = $this->conn->prepare($checkStudentQuery);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists for a student
            return false;
        }

        // If the email does not exist, proceed to insert the student
        $query = "INSERT INTO students (last_name, first_name, middle_initial, email, password, year_level, course_id, college_id) 
                  VALUES (:last_name, :first_name, :middle_initial, :email, :password, :year_level, :course_id, :college_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_initial', $middle_initial);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':year_level', $year_level);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':college_id', $college_id);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error adding student: " . $e->getMessage());
        return false;
    }
}



public function updateSchedule($subject_id, $start_time, $end_time, $teacher_id, $day_of_week) {
    // SQL query to update the schedule for the given subject_id
    $query = "UPDATE schedules 
              SET start_time = :start_time, end_time = :end_time, teacher_id = :teacher_id, day = :day
              WHERE subject_id = :subject_id";
    
    $stmt = $this->conn->prepare($query);
    
    // Bind parameters to the SQL query
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindParam(':day', $day_of_week);
    $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);

    // Execute the query and return the result (number of rows affected)
    return $stmt->execute();
}

public function deleteSchedule($subject_id) {
    try {
        // Prepare the DELETE statement to remove the schedule
        $stmt = $this->conn->prepare("DELETE FROM schedules WHERE subject_id = :subject_id");
        $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);

        // Execute the query and return true if successful
        return $stmt->execute();
    } catch (PDOException $e) {
        // If there's an error, return false
        return false;
    }
}

public function getStudentById($student_id) {
    $sql = "SELECT * FROM students WHERE id = :student_id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

public function updateStudent($student_id, $last_name, $first_name, $middle_initial, $email, $year_level, $course_id, $password = null) {
    try {
        // Check if the email already exists in students, admin, or teacher tables, excluding the student's own email
        $emailQuery = "SELECT COUNT(*) FROM (
                        SELECT email FROM students WHERE email = :email AND id != :id
                        UNION
                        SELECT email FROM admins WHERE email = :email
                        UNION
                        SELECT email FROM teachers WHERE email = :email) AS email_check";
        
        $emailStmt = $this->conn->prepare($emailQuery);
        $emailStmt->bindParam(':email', $email);
        $emailStmt->bindParam(':id', $student_id);
        $emailStmt->execute();

        $emailCount = $emailStmt->fetchColumn();

        // If the email already exists in any of the tables, return false
        if ($emailCount > 0) {
            return false;  // Email already exists, deny the update
        }

        // Check if the year_level has changed
        $currentYearLevelQuery = "SELECT year_level FROM students WHERE id = :id";
        $currentYearLevelStmt = $this->conn->prepare($currentYearLevelQuery);
        $currentYearLevelStmt->bindParam(':id', $student_id);
        $currentYearLevelStmt->execute();
        $currentYearLevel = $currentYearLevelStmt->fetchColumn();

        if ($currentYearLevel != $year_level) {
            // If year_level has changed, delete the student's schedule
            $deleteScheduleQuery = "DELETE FROM student_schedule WHERE student_id = :student_id";
            $deleteScheduleStmt = $this->conn->prepare($deleteScheduleQuery);
            $deleteScheduleStmt->bindParam(':student_id', $student_id);
            $deleteScheduleStmt->execute();
        }

        // If password is provided, hash it and update the password
        if ($password !== null) {
            $query = "UPDATE students SET last_name = :last_name, first_name = :first_name, middle_initial = :middle_initial, 
                      email = :email, year_level = :year_level, course_id = :course_id, password = :password 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $password);
        } else {
            // Otherwise, update without changing the password
            $query = "UPDATE students SET last_name = :last_name, first_name = :first_name, middle_initial = :middle_initial, 
                      email = :email, year_level = :year_level, course_id = :course_id WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }

        // Bind the parameters
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_initial', $middle_initial);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':year_level', $year_level);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':id', $student_id);

        // Execute the query and check if the update was successful
        return $stmt->execute();  // Returns true or false based on execution success
    } catch (PDOException $e) {
        // Handle any errors
        error_log($e->getMessage());
        return false;
    }
}



public function deleteStudentById($student_id) {
    // Prepare the DELETE SQL query
    $query = "DELETE FROM students WHERE id = :student_id";

    // Prepare the statement
    $stmt = $this->conn->prepare($query);

    // Bind the student ID parameter
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);

    // Execute the query and return true if successful, false if failed
    return $stmt->execute();
}

public function getSubjectsByCourseAndYear($course_id, $year_level) {
    $query = "SELECT s.subject_name, c.course_code, s.year_level
              FROM subject s
              JOIN course c ON s.course_id = c.id
              WHERE s.course_id = :course_id AND s.year_level = :year_level
              ORDER BY s.year_level, s.subject_name";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->bindParam(':year_level', $year_level, PDO::PARAM_INT);
    $stmt->execute();   

    return $stmt;
}

public function getSubjectsByCourseAndYearLevel($course_id, $year_level) {
    // Query to fetch subjects by course_id and year_level
    $query = "SELECT subject_name FROM subject WHERE course_id = :course_id AND year_level = :year_level";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':year_level', $year_level);
    $stmt->execute();

    return $stmt;
}

public function getAvailableSubjects($student_id) {
    // Get the student's course_id and year_level from the session or other source
    $course_id = $_SESSION['course_id']; // Assuming the course_id is stored in the session
    $year_level = $_SESSION['year_level']; // Assuming the year_level is stored in the session

    // Query to get all subjects with schedule details
    $query = "
    SELECT 
        s.id, 
        s.subject_name, 
        s.subject_code, 
        s.course_id, 
        s.year_level, 
        sch.start_time, 
        sch.end_time, 
        sch.day
    FROM 
        subject s
    LEFT JOIN 
        schedules sch 
    ON 
        s.id = sch.subject_id
    WHERE 
        s.course_id = :course_id
        AND s.year_level = :year_level
        AND NOT EXISTS (
            SELECT 1 
            FROM student_schedule ss
            WHERE ss.subject_id = s.id 
            AND ss.student_id = :student_id
        )
        AND sch.start_time IS NOT NULL
        AND sch.end_time IS NOT NULL
        AND sch.start_time != '' 
        AND sch.end_time != ''
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id);  // Bind the course_id
    $stmt->bindParam(':year_level', $year_level);  // Bind the year_level
    $stmt->bindParam(':student_id', $student_id);  // Bind the student_id
    $stmt->execute();

    return $stmt;
}


public function addSubjectToSchedule($student_id, $subject_id) {
    // Query to insert the subject into the student's schedule
    $query = "INSERT INTO student_schedule (student_id, subject_id) 
              VALUES (:student_id, :subject_id)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':subject_id', $subject_id);

    return $stmt->execute();
}


public function getStudentSchedule($student_id) {
    // Query to fetch the student's schedule (the subjects they selected)
    $query = "SELECT s.subject_name, s.course_id,s.subject_code, s.year_level, sch.day, sch.start_time, sch.end_time
              FROM subject s
              JOIN student_schedule ss ON ss.subject_id = s.id
              LEFT JOIN schedules sch ON s.id = sch.subject_id
              WHERE ss.student_id = :student_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();

    return $stmt;
}

public function studentHasSchedule($student_id) {
    $query = "SELECT 1 FROM student_schedule WHERE student_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$student_id]);
    return $stmt->fetch() ? true : false;
}


// Inside the Admin class
public function getStudentFullName($student_id) {
    $query = "SELECT first_name, last_name FROM students WHERE id = :student_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        return $student['first_name'] . ' ' . $student['last_name'];
    }
    return '';
}


public function acceptStudent($student_id)
{
    $this->conn->beginTransaction();

    // Fetch student data from pending_students
    $query = "SELECT * FROM pending_students WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        throw new Exception("Student not found in pending_students.");
    }

    // Insert the student into the students table
    $insert_query = "INSERT INTO students (last_name, first_name, middle_initial, email, password, year_level, college_id, course_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $this->conn->prepare($insert_query)->execute([
        $student['last_name'], $student['first_name'], $student['middle_initial'],
        $student['email'], $student['password'], $student['year_level'],
        $student['college_id'], $student['course_id']
    ]);

    // Delete the student from pending_students
    $delete_query = "DELETE FROM pending_students WHERE id = ?";
    $this->conn->prepare($delete_query)->execute([$student_id]);

    $this->conn->commit();
}

public function rejectStudent($student_id)
{
    $query = "DELETE FROM pending_students WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$student_id]);
}

public function getPendingStudentsByCollege($college_id)
{
    $query = "SELECT ps.id, ps.last_name, ps.first_name, ps.year_level, c.course_description
              FROM pending_students ps
              INNER JOIN course c ON ps.course_id = c.id
              WHERE ps.college_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$college_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// In Admin class
public function getCourses($college_id, $search_query = '') {
    $sql = "SELECT * FROM course WHERE college_id = :college_id";

    if (!empty($search_query)) {
        $sql .= " AND (course_code LIKE :search_query OR course_description LIKE :search_query)";
    }

    $stmt = $this->conn->prepare($sql);

    // Bind the college_id to the query
    $stmt->bindValue(':college_id', $college_id);

    // Bind the search query if it's provided
    if (!empty($search_query)) {
        $stmt->bindValue(':search_query', '%' . $search_query . '%');
    }

    $stmt->execute();
    return $stmt;
}


public function getCoursesByCollegeIds($college_id, $search_term = '') {
    $query = "SELECT * FROM course WHERE college_id = :college_id";

    // If a search term is provided, add a condition to the query
    if (!empty($search_term)) {
        $query .= " AND (course_code LIKE :search OR course_description LIKE :search)";
    }

    $query .= " ORDER BY course_code ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);

    // Bind the search term if provided
    if (!empty($search_term)) {
        $search = '%' . $search_term . '%';
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt;
}

public function searchPendingStudentsByCollege($college_id, $search_query = "") {
    $query = "SELECT ps.id, ps.last_name, ps.first_name, ps.year_level, c.course_description
              FROM pending_students ps
              JOIN course c ON ps.course_id = c.id
              WHERE ps.college_id = :college_id";

    // Add search conditions if there's a query
    if (!empty($search_query)) {
        $query .= " AND (ps.last_name LIKE :search OR ps.first_name LIKE :search 
                        OR ps.year_level LIKE :search OR c.course_description LIKE :search)";
    }

    $query .= " ORDER BY ps.last_name ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);

    if (!empty($search_query)) {
        $search_query = "%$search_query%";
        $stmt->bindParam(':search', $search_query, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getStudentProfile($student_id) {
    $query = "SELECT s.first_name, s.last_name, s.middle_initial, s.year_level, c.course_description AS course
              FROM students s
              JOIN course c ON s.course_id = c.id
              WHERE s.id = :student_id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getStudentPassword($student_id) {
    $query = "SELECT password FROM students WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['password'];
}

public function updatePassword($student_id, $hashed_password) {
    $query = "UPDATE students SET password = :password WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $stmt->bindParam(':id', $student_id, PDO::PARAM_INT);
    return $stmt->execute();
}




    
}
?>
