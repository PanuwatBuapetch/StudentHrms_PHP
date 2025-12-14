<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $course_id;
    public $course_code;
    public $course_name;
    public $description;
    public $credits;
    public $faculty;
    public $department;

    public function __construct($db) {
        $this->conn = $db;
    }

    // สร้างวิชาใหม่
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET course_code=:course_code, course_name=:course_name, 
                      description=:description, credits=:credits,
                      faculty=:faculty, department=:department";

        $stmt = $this->conn->prepare($query);

        $this->course_code = htmlspecialchars(strip_tags($this->course_code));
        $this->course_name = htmlspecialchars(strip_tags($this->course_name));

        $stmt->bindParam(":course_code", $this->course_code);
        $stmt->bindParam(":course_name", $this->course_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":credits", $this->credits);
        $stmt->bindParam(":faculty", $this->faculty);
        $stmt->bindParam(":department", $this->department);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // อ่านข้อมูลวิชาทั้งหมด
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY course_code";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // อ่านข้อมูลวิชาตาม ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE course_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->course_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->course_code = $row['course_code'];
            $this->course_name = $row['course_name'];
            $this->description = $row['description'];
            $this->credits = $row['credits'];
            $this->faculty = $row['faculty'];
            $this->department = $row['department'];
            return true;
        }
        return false;
    }

    // อัปเดตข้อมูลวิชา
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET course_name=:course_name, description=:description,
                      credits=:credits, faculty=:faculty, department=:department
                  WHERE course_id=:course_id";

        $stmt = $this->conn->prepare($query);

        $this->course_name = htmlspecialchars(strip_tags($this->course_name));

        $stmt->bindParam(':course_name', $this->course_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':credits', $this->credits);
        $stmt->bindParam(':faculty', $this->faculty);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':course_id', $this->course_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ลบวิชา
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE course_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->course_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
