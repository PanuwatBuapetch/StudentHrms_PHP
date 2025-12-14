<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/Enrollment.php';
require_once 'models/TeachingAssignment.php';

$database = new Database();
$db = $database->getConnection();

$enrollment = new Enrollment($db);
$assignment = new TeachingAssignment($db);
$message = "";
$message_type = "";

// Handle Actions
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if($action == 'drop' && isset($_GET['id'])) {
        $enrollment->enrollment_id = $_GET['id'];
        $enrollment->status = 'dropped';
        if($enrollment->updateStatus()) {
            $message = "ยกเลิกการลงทะเบียนเรียบร้อยแล้ว";
            $message_type = "success";
        } else {
            $message = "เกิดข้อผิดพลาดในการยกเลิก";
            $message_type = "danger";
        }
    }
}

// Handle Form Submit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action']) && $_POST['action'] == 'enroll') {
        $student_id = $_POST['student_id'];
        $assignment_id = $_POST['assignment_id'];
        
        // ตรวจสอบว่าลงทะเบียนซ้ำหรือไม่
        if($enrollment->checkDuplicate($student_id, $assignment_id)) {
            $message = "คุณได้ลงทะเบียนวิชานี้แล้ว";
            $message_type = "warning";
        } else {
            // ตรวจสอบจำนวนที่นั่ง
            $enrolled_count = $enrollment->countByAssignment($assignment_id);
            
            // ดึงข้อมูล max_students (ในที่นี้กำหนดเป็น 40)
            $max_students = 40;
            
            if($enrolled_count >= $max_students) {
                $message = "วิชานี้เต็มแล้ว";
                $message_type = "danger";
            } else {
                $enrollment->student_id = $student_id;
                $enrollment->assignment_id = $assignment_id;
                $enrollment->status = 'enrolled';
                
                if($enrollment->create()) {
                    $message = "ลงทะเบียนเรียบร้อยแล้ว";
                    $message_type = "success";
                } else {
                    $message = "เกิดข้อผิดพลาดในการลงทะเบียน";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Get student info if logged in as student
$current_student_id = null;
if($_SESSION['role'] == 'student') {
    $student = new Student($db);
    $query_student = "SELECT s.* FROM students s WHERE s.user_id = :user_id";
    $stmt_find = $db->prepare($query_student);
    $stmt_find->bindParam(':user_id', $_SESSION['user_id']);
    $stmt_find->execute();
    $student_data = $stmt_find->fetch(PDO::FETCH_ASSOC);
    if($student_data) {
        $current_student_id = $student_data['student_id'];
    }
}

// Get all enrollments or student-specific enrollments
if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'teacher') {
    $stmt = $enrollment->readAll();
} elseif($current_student_id) {
    $stmt = $enrollment->readByStudent($current_student_id);
} else {
    // Create empty statement if no student found
    $stmt = $db->prepare("SELECT * FROM enrollments WHERE 1=0");
    $stmt->execute();
}

// Get available courses
$stmt_courses = $assignment->readAvailable();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนเรียน - Student HRMS</title>
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
        .course-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .seats-available {
            font-size: 0.9rem;
        }
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
                    <a href="enrollments.php" class="active">
                        <i class="bi bi-clipboard-check-fill"></i> ลงทะเบียน
                    </a>
                    <a href="grades.php">
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
                        <span class="navbar-brand mb-0 h1">ระบบลงทะเบียนเรียน</span>
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

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-4" id="enrollmentTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="available-tab" data-bs-toggle="tab" 
                                    data-bs-target="#available" type="button">
                                <i class="bi bi-search"></i> วิชาที่เปิดลงทะเบียน
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="enrolled-tab" data-bs-toggle="tab" 
                                    data-bs-target="#enrolled" type="button">
                                <i class="bi bi-bookmark-check-fill"></i> วิชาที่ลงทะเบียนแล้ว
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="enrollmentTabContent">
                        <!-- Available Courses Tab -->
                        <div class="tab-pane fade show active" id="available">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-book-half"></i> วิชาที่เปิดลงทะเบียน</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        if($stmt_courses->rowCount() > 0) {
                                            while($course = $stmt_courses->fetch(PDO::FETCH_ASSOC)) {
                                                $enrolled = $course['enrolled_count'];
                                                $max = $course['max_students'] ?? 40;
                                                $available = $max - $enrolled;
                                                $percentage = ($enrolled / $max) * 100;
                                                
                                                $badge_color = 'success';
                                                if($percentage >= 80) $badge_color = 'danger';
                                                elseif($percentage >= 50) $badge_color = 'warning';
                                        ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card course-card h-100" onclick="enrollCourse(<?php echo $course['assignment_id']; ?>, '<?php echo $course['course_code']; ?>', '<?php echo htmlspecialchars($course['course_name']); ?>')">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($course['course_code']); ?></h5>
                                                        <span class="badge bg-info"><?php echo $course['credits']; ?> หน่วยกิต</span>
                                                    </div>
                                                    <h6 class="card-subtitle mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h6>
                                                    
                                                    <p class="mb-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($course['faculty']); ?><br>
                                                            <i class="bi bi-person"></i> 
                                                            <?php 
                                                            if($course['teacher_first_name']) {
                                                                echo htmlspecialchars($course['teacher_first_name'] . ' ' . $course['teacher_last_name']);
                                                            } else {
                                                                echo 'ยังไม่กำหนดอาจารย์';
                                                            }
                                                            ?>
                                                        </small>
                                                    </p>
                                                    
                                                    <div class="mb-2">
                                                        <small class="text-muted">ที่นั่งคงเหลือ:</small>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar bg-<?php echo $badge_color; ?>" 
                                                                 style="width: <?php echo $percentage; ?>%">
                                                                <?php echo $enrolled; ?>/<?php echo $max; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <span class="badge bg-<?php echo $badge_color; ?> seats-available">
                                                        <?php echo $available; ?> ที่นั่งว่าง
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                            }
                                        } else {
                                            echo '<div class="col-12"><div class="alert alert-info">ยังไม่มีวิชาเปิดลงทะเบียน</div></div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enrolled Courses Tab -->
                        <div class="tab-pane fade" id="enrolled">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-bookmark-check-fill"></i> วิชาที่ลงทะเบียนแล้ว</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>รหัสวิชา</th>
                                                    <th>ชื่อวิชา</th>
                                                    <th>หน่วยกิต</th>
                                                    <th>ภาคเรียน</th>
                                                    <th>ปีการศึกษา</th>
                                                    <th>สถานะ</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if($stmt->rowCount() > 0) {
                                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $status_badge = $row['status'] == 'enrolled' ? 'success' : 
                                                                       ($row['status'] == 'completed' ? 'primary' : 'secondary');
                                                        $status_text = $row['status'] == 'enrolled' ? 'กำลังเรียน' : 
                                                                      ($row['status'] == 'completed' ? 'เรียนจบแล้ว' : 'ยกเลิก');
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                                    <td><?php echo $row['credits']; ?></td>
                                                    <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['academic_year']); ?></td>
                                                    <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                                    <td>
                                                        <?php if($row['status'] == 'enrolled'): ?>
                                                        <a href="?action=drop&id=<?php echo $row['enrollment_id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะถอนวิชานี้?')">
                                                            <i class="bi bi-x-circle"></i> ถอน
                                                        </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>ยังไม่มีการลงทะเบียน</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enroll Modal -->
    <div class="modal fade" id="enrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">ยืนยันการลงทะเบียน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="enroll">
                    <input type="hidden" name="assignment_id" id="modal_assignment_id">
                    <div class="modal-body">
                        <?php if($_SESSION['role'] == 'admin'): ?>
                        <div class="mb-3">
                            <label class="form-label">เลือกนักศึกษา *</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">-- เลือกนักศึกษา --</option>
                                <?php
                                $student = new Student($db);
                                $stmt_students = $student->readAll();
                                while($s = $stmt_students->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="'.$s['student_id'].'">'.$s['student_code'].' - '.$s['first_name'].' '.$s['last_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="student_id" value="<?php echo $current_student_id; ?>">
                        <?php endif; ?>
                        
                        <p>คุณต้องการลงทะเบียนวิชา:</p>
                        <div class="alert alert-info">
                            <strong><span id="modal_course_code"></span></strong><br>
                            <span id="modal_course_name"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">ยืนยันการลงทะเบียน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function enrollCourse(assignmentId, courseCode, courseName) {
            document.getElementById('modal_assignment_id').value = assignmentId;
            document.getElementById('modal_course_code').textContent = courseCode;
            document.getElementById('modal_course_name').textContent = courseName;
            
            var modal = new bootstrap.Modal(document.getElementById('enrollModal'));
            modal.show();
        }
    </script>
</body>
</html>
