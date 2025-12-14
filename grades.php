<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/Grade.php';
require_once 'models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();

$grade = new Grade($db);
$enrollment = new Enrollment($db);
$message = "";
$message_type = "";

// Get student info if logged in as student
$current_student_id = null;
$current_student_gpa = 0;
if($_SESSION['role'] == 'student') {
    $student = new Student($db);
    $stmt_student = $student->readAll();
    while($row = $stmt_student->fetch(PDO::FETCH_ASSOC)) {
        if($row['user_id'] == $_SESSION['user_id']) {
            $current_student_id = $row['student_id'];
            $current_student_gpa = $row['gpa'];
            break;
        }
    }
}

// Handle Form Submit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        if($_POST['action'] == 'add_grade') {
            $midterm = floatval($_POST['midterm_score']);
            $final = floatval($_POST['final_score']);
            $assignment = floatval($_POST['assignment_score']);
            
            // คำนวณคะแนนรวม (30% midterm + 40% final + 30% assignment)
            $total = ($midterm * 0.3) + ($final * 0.4) + ($assignment * 0.3);
            
            // คำนวณเกรด
            $grade_result = Grade::calculateGrade($total);
            
            // ตรวจสอบว่ามีคะแนนอยู่แล้วหรือไม่
            $existing_grade = $grade->readByEnrollment($_POST['enrollment_id']);
            
            if($existing_grade) {
                // อัปเดตคะแนน
                $grade->grade_id = $existing_grade['grade_id'];
                $grade->enrollment_id = $_POST['enrollment_id'];
                $grade->midterm_score = $midterm;
                $grade->final_score = $final;
                $grade->assignment_score = $assignment;
                $grade->total_score = $total;
                $grade->letter_grade = $grade_result['letter'];
                $grade->grade_point = $grade_result['point'];
                $grade->comments = $_POST['comments'];
                
                if($grade->update()) {
                    // อัปเดต GPA
                    $student_id = $_POST['student_id'];
                    $new_gpa = Grade::calculateGPA($student_id, $db);
                    
                    // อัปเดต GPA ในตาราง students
                    $update_gpa = $db->prepare("UPDATE students SET gpa = :gpa WHERE student_id = :student_id");
                    $update_gpa->bindParam(':gpa', $new_gpa);
                    $update_gpa->bindParam(':student_id', $student_id);
                    $update_gpa->execute();
                    
                    $message = "อัปเดตคะแนนเรียบร้อยแล้ว";
                    $message_type = "success";
                } else {
                    $message = "เกิดข้อผิดพลาดในการอัปเดตคะแนน";
                    $message_type = "danger";
                }
            } else {
                // เพิ่มคะแนนใหม่
                $grade->enrollment_id = $_POST['enrollment_id'];
                $grade->midterm_score = $midterm;
                $grade->final_score = $final;
                $grade->assignment_score = $assignment;
                $grade->total_score = $total;
                $grade->letter_grade = $grade_result['letter'];
                $grade->grade_point = $grade_result['point'];
                $grade->comments = $_POST['comments'];
                
                if($grade->create()) {
                    // อัปเดต GPA
                    $student_id = $_POST['student_id'];
                    $new_gpa = Grade::calculateGPA($student_id, $db);
                    
                    // อัปเดต GPA ในตาราง students
                    $update_gpa = $db->prepare("UPDATE students SET gpa = :gpa WHERE student_id = :student_id");
                    $update_gpa->bindParam(':gpa', $new_gpa);
                    $update_gpa->bindParam(':student_id', $student_id);
                    $update_gpa->execute();
                    
                    $message = "บันทึกคะแนนเรียบร้อยแล้ว";
                    $message_type = "success";
                } else {
                    $message = "เกิดข้อผิดพลาดในการบันทึกคะแนน";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Get grades
if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'teacher') {
    $stmt = $grade->readAll();
} else {
    $stmt = $grade->readByStudent($current_student_id);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการเรียน - Student HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .gpa-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        .gpa-value {
            font-size: 4rem;
            font-weight: bold;
        }
        .grade-A { color: #28a745; font-weight: bold; }
        .grade-B { color: #17a2b8; font-weight: bold; }
        .grade-C { color: #ffc107; font-weight: bold; }
        .grade-D { color: #fd7e14; font-weight: bold; }
        .grade-F { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <h4><i class="bi bi-mortarboard-fill"></i> Student HRMS</h4>
                </div>
                <nav>
                    <a href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="students.php">
                        <i class="bi bi-people-fill"></i> จัดการนักศึกษา
                    </a>
                    <a href="courses.php">
                        <i class="bi bi-book-fill"></i> จัดการวิชา
                    </a>
                    <a href="enrollments.php">
                        <i class="bi bi-clipboard-check-fill"></i> ลงทะเบียน
                    </a>
                    <a href="grades.php" class="active">
                        <i class="bi bi-bar-chart-fill"></i> ผลการเรียน
                    </a>
                    <hr style="border-color: rgba(255,255,255,0.2);">
                    <a href="profile.php">
                        <i class="bi bi-person-circle"></i> โปรไฟล์
                    </a>
                    <a href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <nav class="navbar navbar-custom navbar-expand-lg">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">ผลการเรียน</span>
                        <div class="ms-auto">
                            <span class="navbar-text">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </span>
                        </div>
                    </div>
                </nav>

                <div class="p-4">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($_SESSION['role'] == 'student'): ?>
                    <!-- GPA Card for Students -->
                    <div class="gpa-card">
                        <h3>เกรดเฉลี่ยสะสม (GPA)</h3>
                        <div class="gpa-value"><?php echo number_format($current_student_gpa, 2); ?></div>
                        <p class="mb-0">คะแนนเต็ม 4.00</p>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> 
                                        <?php echo ($_SESSION['role'] == 'student') ? 'ผลการเรียนของฉัน' : 'ผลการเรียนทั้งหมด'; ?>
                                    </h5>
                                </div>
                                <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'teacher'): ?>
                                <div class="col-auto">
                                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#gradeModal">
                                        <i class="bi bi-plus-circle"></i> บันทึกคะแนน
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <?php if($_SESSION['role'] != 'student'): ?>
                                            <th>รหัสนักศึกษา</th>
                                            <th>ชื่อ-นามสกุล</th>
                                            <?php endif; ?>
                                            <th>รหัสวิชา</th>
                                            <th>ชื่อวิชา</th>
                                            <th>กลางภาค</th>
                                            <th>ปลายภาค</th>
                                            <th>งาน</th>
                                            <th>รวม</th>
                                            <th>เกรด</th>
                                            <th>GPA</th>
                                            <?php if($_SESSION['role'] != 'student'): ?>
                                            <th>จัดการ</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($stmt->rowCount() > 0) {
                                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $grade_class = 'grade-' . substr($row['letter_grade'], 0, 1);
                                        ?>
                                        <tr>
                                            <?php if($_SESSION['role'] != 'student'): ?>
                                            <td><?php echo htmlspecialchars($row['student_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <?php endif; ?>
                                            <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                            <td><?php echo number_format($row['midterm_score'], 2); ?></td>
                                            <td><?php echo number_format($row['final_score'], 2); ?></td>
                                            <td><?php echo number_format($row['assignment_score'], 2); ?></td>
                                            <td><?php echo number_format($row['total_score'], 2); ?></td>
                                            <td><span class="<?php echo $grade_class; ?>"><?php echo $row['letter_grade']; ?></span></td>
                                            <td><?php echo number_format($row['grade_point'], 2); ?></td>
                                            <?php if($_SESSION['role'] != 'student'): ?>
                                            <td>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="editGrade(<?php echo $row['grade_id']; ?>, <?php echo $row['enrollment_id']; ?>, <?php echo $row['midterm_score']; ?>, <?php echo $row['final_score']; ?>, <?php echo $row['assignment_score']; ?>, '<?php echo htmlspecialchars($row['comments']); ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            $colspan = ($_SESSION['role'] == 'student') ? '8' : '11';
                                            echo "<tr><td colspan='$colspan' class='text-center'>ยังไม่มีผลการเรียน</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if($_SESSION['role'] == 'student'): ?>
                    <!-- Grade Summary -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> เกณฑ์การให้คะแนน</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>คะแนน</th>
                                                        <th>เกรด</th>
                                                        <th>GPA</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>80-100</td>
                                                        <td><span class="grade-A">A</span></td>
                                                        <td>4.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>75-79</td>
                                                        <td><span class="grade-B">B+</span></td>
                                                        <td>3.50</td>
                                                    </tr>
                                                    <tr>
                                                        <td>70-74</td>
                                                        <td><span class="grade-B">B</span></td>
                                                        <td>3.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>65-69</td>
                                                        <td><span class="grade-C">C+</span></td>
                                                        <td>2.50</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>คะแนน</th>
                                                        <th>เกรด</th>
                                                        <th>GPA</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>60-64</td>
                                                        <td><span class="grade-C">C</span></td>
                                                        <td>2.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>55-59</td>
                                                        <td><span class="grade-D">D+</span></td>
                                                        <td>1.50</td>
                                                    </tr>
                                                    <tr>
                                                        <td>50-54</td>
                                                        <td><span class="grade-D">D</span></td>
                                                        <td>1.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>0-49</td>
                                                        <td><span class="grade-F">F</span></td>
                                                        <td>0.00</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="mb-0"><strong>สัดส่วนคะแนน:</strong> กลางภาค 30%, ปลายภาค 40%, งาน 30%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">บันทึกคะแนน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_grade">
                    <input type="hidden" name="grade_id" id="grade_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">เลือกการลงทะเบียน *</label>
                            <select class="form-select" name="enrollment_id" id="enrollment_id" required>
                                <option value="">-- เลือกการลงทะเบียน --</option>
                                <?php
                                $enrollment = new Enrollment($db);
                                $stmt_enrollments = $enrollment->readAll();
                                while($e = $stmt_enrollments->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="'.$e['enrollment_id'].'" data-student-id="'.$e['student_id'].'">';
                                    echo $e['student_code'].' - '.$e['first_name'].' '.$e['last_name'].' - ';
                                    echo $e['course_code'].' '.$e['course_name'];
                                    echo '</option>';
                                }
                                ?>
                            </select>
                            <input type="hidden" name="student_id" id="student_id">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">คะแนนกลางภาค (30%) *</label>
                                <input type="number" class="form-control" name="midterm_score" 
                                       id="midterm_score" min="0" max="100" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">คะแนนปลายภาค (40%) *</label>
                                <input type="number" class="form-control" name="final_score" 
                                       id="final_score" min="0" max="100" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">คะแนนงาน (30%) *</label>
                                <input type="number" class="form-control" name="assignment_score" 
                                       id="assignment_score" min="0" max="100" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            คะแนนรวมจะถูกคำนวณอัตโนมัติจากสูตร: (กลางภาค × 0.3) + (ปลายภาค × 0.4) + (งาน × 0.3)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกคะแนน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update student_id when enrollment is selected
        document.getElementById('enrollment_id').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var studentId = selectedOption.getAttribute('data-student-id');
            document.getElementById('student_id').value = studentId;
        });

        function editGrade(gradeId, enrollmentId, midterm, final, assignment, comments) {
            document.getElementById('grade_id').value = gradeId;
            document.getElementById('enrollment_id').value = enrollmentId;
            document.getElementById('midterm_score').value = midterm;
            document.getElementById('final_score').value = final;
            document.getElementById('assignment_score').value = assignment;
            document.getElementById('comments').value = comments;
            
            // Trigger change event to set student_id
            document.getElementById('enrollment_id').dispatchEvent(new Event('change'));
            
            var modal = new bootstrap.Modal(document.getElementById('gradeModal'));
            modal.show();
        }
    </script>
</body>
</html>
