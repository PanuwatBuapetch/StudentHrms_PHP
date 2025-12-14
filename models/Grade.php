<?php
class Grade {
    private $conn;
    private $table_name = "grades";

    public $grade_id;
    public $enrollment_id;
    public $midterm_score;
    public $final_score;
    public $assignment_score;
    public $total_score;
    public $letter_grade;
    public $grade_point;
    public $comments;

    public function __construct($db) {
        $this->conn = $db;
    }

    // สร้างคะแนนใหม่
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET enrollment_id=:enrollment_id, 
                      midterm_score=:midterm_score,
                      final_score=:final_score,
                      assignment_score=:assignment_score,
                      total_score=:total_score,
                      letter_grade=:letter_grade,
                      grade_point=:grade_point,
                      comments=:comments,
                      graded_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":enrollment_id", $this->enrollment_id);
        $stmt->bindParam(":midterm_score", $this->midterm_score);
        $stmt->bindParam(":final_score", $this->final_score);
        $stmt->bindParam(":assignment_score", $this->assignment_score);
        $stmt->bindParam(":total_score", $this->total_score);
        $stmt->bindParam(":letter_grade", $this->letter_grade);
        $stmt->bindParam(":grade_point", $this->grade_point);
        $stmt->bindParam(":comments", $this->comments);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // อ่านคะแนนทั้งหมด
    public function readAll() {
        $query = "SELECT g.*, 
                         s.student_code, s.first_name, s.last_name,
                         c.course_code, c.course_name,
                         ta.semester, ta.academic_year
                  FROM " . $this->table_name . " g
                  INNER JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                  INNER JOIN students s ON e.student_id = s.student_id
                  INNER JOIN teaching_assignments ta ON e.assignment_id = ta.assignment_id
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  ORDER BY g.graded_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // อ่านคะแนนตามนักศึกษา
    public function readByStudent($student_id) {
        $query = "SELECT g.*, 
                         c.course_code, c.course_name, c.credits,
                         ta.semester, ta.academic_year
                  FROM " . $this->table_name . " g
                  INNER JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                  INNER JOIN teaching_assignments ta ON e.assignment_id = ta.assignment_id
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  WHERE e.student_id = :student_id
                  ORDER BY ta.academic_year DESC, ta.semester DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        return $stmt;
    }

    // อ่านคะแนนตาม enrollment_id
    public function readByEnrollment($enrollment_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE enrollment_id = :enrollment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":enrollment_id", $enrollment_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // อัปเดตคะแนน
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET midterm_score=:midterm_score,
                      final_score=:final_score,
                      assignment_score=:assignment_score,
                      total_score=:total_score,
                      letter_grade=:letter_grade,
                      grade_point=:grade_point,
                      comments=:comments,
                      graded_at=NOW()
                  WHERE grade_id=:grade_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':midterm_score', $this->midterm_score);
        $stmt->bindParam(':final_score', $this->final_score);
        $stmt->bindParam(':assignment_score', $this->assignment_score);
        $stmt->bindParam(':total_score', $this->total_score);
        $stmt->bindParam(':letter_grade', $this->letter_grade);
        $stmt->bindParam(':grade_point', $this->grade_point);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':grade_id', $this->grade_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // คำนวณเกรด
    public static function calculateGrade($total_score) {
        if ($total_score >= 80) {
            return ['letter' => 'A', 'point' => 4.00];
        } elseif ($total_score >= 75) {
            return ['letter' => 'B+', 'point' => 3.50];
        } elseif ($total_score >= 70) {
            return ['letter' => 'B', 'point' => 3.00];
        } elseif ($total_score >= 65) {
            return ['letter' => 'C+', 'point' => 2.50];
        } elseif ($total_score >= 60) {
            return ['letter' => 'C', 'point' => 2.00];
        } elseif ($total_score >= 55) {
            return ['letter' => 'D+', 'point' => 1.50];
        } elseif ($total_score >= 50) {
            return ['letter' => 'D', 'point' => 1.00];
        } else {
            return ['letter' => 'F', 'point' => 0.00];
        }
    }

    // คำนวณ GPA
    public static function calculateGPA($student_id, $conn) {
        $query = "SELECT g.grade_point, c.credits
                  FROM grades g
                  INNER JOIN enrollments e ON g.enrollment_id = e.enrollment_id
                  INNER JOIN teaching_assignments ta ON e.assignment_id = ta.assignment_id
                  INNER JOIN courses c ON ta.course_id = c.course_id
                  WHERE e.student_id = :student_id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        $total_points = 0;
        $total_credits = 0;

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_points += ($row['grade_point'] * $row['credits']);
            $total_credits += $row['credits'];
        }

        if($total_credits > 0) {
            return round($total_points / $total_credits, 2);
        }
        return 0.00;
    }
}
?>
