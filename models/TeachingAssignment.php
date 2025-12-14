<?php
class TeachingAssignment {
    private $conn;
    private $table_name = "teaching_assignments";

    public $assignment_id;
    public $course_id;
    public $teacher_id;
    public $semester;
    public $academic_year;
    public $section;
    public $schedule;
    public $room;
    public $max_students;

    public function __construct($db) {
        $this->conn = $db;
    }

    // สร้าง assignment ใหม่
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET course_id=:course_id, 
                      teacher_id=:teacher_id,
                      semester=:semester,
                      academic_year=:academic_year,
                      section=:section,
                      schedule=:schedule,
                      room=:room,
                      max_students=:max_students";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":teacher_id", $this->teacher_id);
        $stmt->bindParam(":semester", $this->semester);
        $stmt->bindParam(":academic_year", $this->academic_year);
        $stmt->bindParam(":section", $this->section);
        $stmt->bindParam(":schedule", $this->schedule);
        $stmt->bindParam(":room", $this->room);
        $stmt->bindParam(":max_students", $this->max_students);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // อ่าน assignments ทั้งหมด
    public function readAll() {
        $query = "SELECT ta.*, 
                         c.course_code, c.course_name, c.credits,
                         t.first_name as teacher_first_name, 
                         t.last_name as teacher_last_name
                  FROM " . $this->table_name . " ta
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  LEFT JOIN teachers t ON ta.teacher_id = t.teacher_id
                  ORDER BY ta.academic_year DESC, ta.semester DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // อ่าน assignment ที่สามารถลงทะเบียนได้
    public function readAvailable() {
        $query = "SELECT ta.*, 
                         c.course_code, c.course_name, c.credits, c.faculty, c.department,
                         t.first_name as teacher_first_name, 
                         t.last_name as teacher_last_name,
                         (SELECT COUNT(*) FROM enrollments e 
                          WHERE e.assignment_id = ta.assignment_id 
                          AND e.status = 'enrolled') as enrolled_count
                  FROM " . $this->table_name . " ta
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  LEFT JOIN teachers t ON ta.teacher_id = t.teacher_id
                  ORDER BY c.course_code";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
