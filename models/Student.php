<?php
class Student {
    private $conn;
    private $table_name = "students";

    public $student_id;
    public $user_id;
    public $student_code;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $phone;
    public $address;
    public $faculty;
    public $major;
    public $year_level;
    public $gpa;
    public $profile_image;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // สร้างนักศึกษาใหม่
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, student_code=:student_code, 
                      first_name=:first_name, last_name=:last_name,
                      date_of_birth=:date_of_birth, gender=:gender,
                      phone=:phone, address=:address, faculty=:faculty,
                      major=:major, year_level=:year_level, status=:status";

        $stmt = $this->conn->prepare($query);

        // ทำความสะอาดข้อมูล
        $this->student_code = htmlspecialchars(strip_tags($this->student_code));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":student_code", $this->student_code);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":faculty", $this->faculty);
        $stmt->bindParam(":major", $this->major);
        $stmt->bindParam(":year_level", $this->year_level);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // อ่านข้อมูลนักศึกษาทั้งหมด
    public function readAll() {
        $query = "SELECT s.*, u.username, u.email 
                  FROM " . $this->table_name . " s
                  LEFT JOIN users u ON s.user_id = u.user_id
                  ORDER BY s.student_code DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // อ่านข้อมูลนักศึกษาตาม ID
    public function readOne() {
        $query = "SELECT s.*, u.username, u.email 
                  FROM " . $this->table_name . " s
                  LEFT JOIN users u ON s.user_id = u.user_id
                  WHERE s.student_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->student_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->user_id = $row['user_id'];
            $this->student_code = $row['student_code'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->gender = $row['gender'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->faculty = $row['faculty'];
            $this->major = $row['major'];
            $this->year_level = $row['year_level'];
            $this->gpa = $row['gpa'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // อัปเดตข้อมูลนักศึกษา
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET first_name=:first_name, last_name=:last_name,
                      date_of_birth=:date_of_birth, gender=:gender,
                      phone=:phone, address=:address, faculty=:faculty,
                      major=:major, year_level=:year_level, status=:status
                  WHERE student_id=:student_id";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':faculty', $this->faculty);
        $stmt->bindParam(':major', $this->major);
        $stmt->bindParam(':year_level', $this->year_level);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':student_id', $this->student_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ลบนักศึกษา
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->student_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ค้นหานักศึกษา
    public function search($keyword) {
        $query = "SELECT s.*, u.username, u.email 
                  FROM " . $this->table_name . " s
                  LEFT JOIN users u ON s.user_id = u.user_id
                  WHERE s.student_code LIKE ? 
                     OR s.first_name LIKE ? 
                     OR s.last_name LIKE ?
                     OR s.faculty LIKE ?
                     OR s.major LIKE ?
                  ORDER BY s.student_code DESC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $keyword);
        $stmt->bindParam(4, $keyword);
        $stmt->bindParam(5, $keyword);
        $stmt->execute();
        return $stmt;
    }

    // นับจำนวนนักศึกษา
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
