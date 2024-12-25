<?php
class Login {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    public function checkAdminLogin($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, password FROM admins WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adminData && password_verify($password, $adminData['password'])) {
            return $adminData['id'];
        }
        return false;
    }

    // Check if teacher login credentials are correct
    public function checkTeacherLogin($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, password, college_id FROM teachers WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $teacherData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($teacherData && password_verify($password, $teacherData['password'])) {
            return $teacherData;
        }
        return false;
    }

    // Get college name by college_id
    public function getCollegeName($college_id) {
        $stmt = $this->conn->prepare("SELECT college_name FROM college WHERE id = :college_id");
        $stmt->bindParam(':college_id', $college_id, PDO::PARAM_INT);
        $stmt->execute();
        $collegeData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $collegeData['college_name'];
    }

    
    public function checkStudentLogin($email, $password) {
        // SQL query to fetch the student record with the provided email
        $sql = "SELECT id, year_level, course_id, password FROM students WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
    
        // Fetch the student data
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($student) {
            // Verify the password
            if (password_verify($password, $student['password'])) {
                // Return student data including year_level and course_id
                return [
                    'id' => $student['id'],
                    'year_level' => $student['year_level'],
                    'course_id' => $student['course_id']
                ];
            }
        }
    
        // Return false if login fails
        return false;
    }


    public function getColleges() {
        $stmt = $this->conn->prepare("SELECT id, college_name FROM college");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get courses by college ID for the filtered dropdown
    public function getCoursesByCollege($collegeId) {
        $stmt = $this->conn->prepare("SELECT id, course_description FROM course WHERE college_id = :college_id");
        $stmt->bindParam(':college_id', $collegeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insert a new sign-up request into the pending_students table
    public function addPendingStudent($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO pending_students (last_name, first_name, middle_initial, email, password, year_level, college_id, course_id)
            VALUES (:last_name, :first_name, :middle_initial, :email, :password, :year_level, :college_id, :course_id)
        ");
        $stmt->execute([
            ':last_name' => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':middle_initial' => $data['middle_initial'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT), // Hash the password
            ':year_level' => $data['year_level'],
            ':college_id' => $data['college_id'],
            ':course_id' => $data['course_id']
        ]);
    }
    
    
}
?>
