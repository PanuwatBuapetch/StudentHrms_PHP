<?php
class Enrollment {
    private $conn;
    private $table_name = "enrollments";

    public $enrollment_id;
    public $student_id;
    public $assignment_id;
    public $enrollment_date;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // สร้างการลงทะเบียนใหม่
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET student_id=:student_id, assignment_id=:assignment_id, 
                      status=:status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":assignment_id", $this->assignment_id);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // อ่านการลงทะเบียนทั้งหมด
    public function readAll() {
        $query = "SELECT e.*, 
                         s.student_code, s.first_name, s.last_name,
                         c.course_code, c.course_name, c.credits,
                         ta.semester, ta.academic_year, ta.section
                  FROM " . $this->table_name . " e
                  INNER JOIN students s ON e.student_id = s.student_id
                  INNER JOIN teaching_assignments ta ON e.assignment_id = ta.assignment_id
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  ORDER BY e.enrollment_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // อ่านการลงทะเบียนตามนักศึกษา
    public function readByStudent($student_id) {
        $query = "SELECT e.*, 
                         c.course_code, c.course_name, c.credits,
                         ta.semester, ta.academic_year, ta.section,
                         t.first_name as teacher_first_name, 
                         t.last_name as teacher_last_name
                  FROM " . $this->table_name . " e
                  INNER JOIN teaching_assignments ta ON e.assignment_id = ta.assignment_id
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  LEFT JOIN teachers t ON ta.teacher_id = t.teacher_id
                  WHERE e.student_id = :student_id
                  ORDER BY ta.semester DESC, c.course_code";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt;
    }

    // ตรวจสอบว่าลงทะเบียนซ้ำหรือไม่
    public function checkDuplicate($student_id, $assignment_id) {
        $query = "SELECT enrollment_id FROM " . $this->table_name . " 
                  WHERE student_id = :student_id 
                  AND assignment_id = :assignment_id 
                  AND status != 'dropped'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":assignment_id", $assignment_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // อัปเดตสถานะการลงทะเบียน
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                  SET status=:status
                  WHERE enrollment_id=:enrollment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':enrollment_id', $this->enrollment_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ลบการลงทะเบียน
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE enrollment_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->enrollment_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // นับจำนวนการลงทะเบียนในแต่ละวิชา
    public function countByAssignment($assignment_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE assignment_id = :assignment_id 
                  AND status = 'enrolled'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":assignment_id", $assignment_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
