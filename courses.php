<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/Course.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$message = "";
$message_type = "";

// Handle Actions
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if($action == 'delete' && isset($_GET['id'])) {
        $course->course_id = $_GET['id'];
        if($course->delete()) {
            $message = "ลบข้อมูลวิชาเรียบร้อยแล้ว";
            $message_type = "success";
        } else {
            $message = "เกิดข้อผิดพลาดในการลบข้อมูล";
            $message_type = "danger";
        }
    }
}

// Handle Form Submit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        if($_POST['action'] == 'add') {
            $course->course_code = $_POST['course_code'];
            $course->course_name = $_POST['course_name'];
            $course->description = $_POST['description'];
            $course->credits = $_POST['credits'];
            $course->faculty = $_POST['faculty'];
            $course->department = $_POST['department'];
            
            if($course->create()) {
                $message = "เพิ่มข้อมูลวิชาเรียบร้อยแล้ว";
                $message_type = "success";
            } else {
                $message = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
                $message_type = "danger";
            }
        } elseif($_POST['action'] == 'edit') {
            $course->course_id = $_POST['course_id'];
            $course->course_name = $_POST['course_name'];
            $course->description = $_POST['description'];
            $course->credits = $_POST['credits'];
            $course->faculty = $_POST['faculty'];
            $course->department = $_POST['department'];
            
            if($course->update()) {
                $message = "อัปเดตข้อมูลวิชาเรียบร้อยแล้ว";
                $message_type = "success";
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
                $message_type = "danger";
            }
        }
    }
}

$stmt = $course->readAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการวิชา - Student HRMS</title>
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
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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
                    <a href="courses.php" class="active">
                        <i class="bi bi-book-fill"></i> จัดการวิชา
                    </a>
                    <a href="enrollments.php">
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
                        <span class="navbar-brand mb-0 h1">จัดการวิชา</span>
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

                    <div class="mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> เพิ่มวิชาใหม่
                        </button>
                    </div>

                    <!-- Courses Grid -->
                    <div class="row">
                        <?php
                        if($stmt->rowCount() > 0) {
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card course-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($row['course_code']); ?></h5>
                                        <span class="badge bg-primary"><?php echo $row['credits']; ?> หน่วยกิต</span>
                                    </div>
                                    <h6 class="card-subtitle mb-3 text-muted"><?php echo htmlspecialchars($row['course_name']); ?></h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($row['faculty']); ?><br>
                                            <i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($row['department']); ?>
                                        </small>
                                    </p>
                                    <?php if($row['description']): ?>
                                    <p class="card-text"><small><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</small></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white">
                                    <button class="btn btn-sm btn-info" 
                                            onclick="viewCourse(<?php echo $row['course_id']; ?>, '<?php echo htmlspecialchars($row['course_code']); ?>', '<?php echo htmlspecialchars($row['course_name']); ?>', '<?php echo htmlspecialchars($row['description']); ?>', <?php echo $row['credits']; ?>, '<?php echo htmlspecialchars($row['faculty']); ?>', '<?php echo htmlspecialchars($row['department']); ?>')">
                                        <i class="bi bi-eye"></i> ดูรายละเอียด
                                    </button>
                                    <button class="btn btn-sm btn-warning" 
                                            onclick="editCourse(<?php echo $row['course_id']; ?>, '<?php echo htmlspecialchars($row['course_code']); ?>', '<?php echo htmlspecialchars($row['course_name']); ?>', '<?php echo htmlspecialchars($row['description']); ?>', <?php echo $row['credits']; ?>, '<?php echo htmlspecialchars($row['faculty']); ?>', '<?php echo htmlspecialchars($row['department']); ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?action=delete&id=<?php echo $row['course_id']; ?>" 
                                       class="btn btn-sm btn-danger float-end" 
                                       onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบวิชานี้?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">ยังไม่มีข้อมูลวิชา</div></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">เพิ่มวิชาใหม่</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสวิชา *</label>
                                <input type="text" class="form-control" name="course_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">จำนวนหน่วยกิต *</label>
                                <input type="number" class="form-control" name="credits" min="1" max="6" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">ชื่อวิชา *</label>
                                <input type="text" class="form-control" name="course_name" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">คำอธิบายรายวิชา</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">คณะ *</label>
                                <input type="text" class="form-control" name="faculty" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ภาควิชา *</label>
                                <input type="text" class="form-control" name="department" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">แก้ไขข้อมูลวิชา</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="course_id" id="edit_course_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสวิชา *</label>
                                <input type="text" class="form-control" id="edit_course_code" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">จำนวนหน่วยกิต *</label>
                                <input type="number" class="form-control" name="credits" id="edit_credits" min="1" max="6" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">ชื่อวิชา *</label>
                                <input type="text" class="form-control" name="course_name" id="edit_course_name" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">คำอธิบายรายวิชา</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">คณะ *</label>
                                <input type="text" class="form-control" name="faculty" id="edit_faculty" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ภาควิชา *</label>
                                <input type="text" class="form-control" name="department" id="edit_department" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Course Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">รายละเอียดวิชา</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">รหัสวิชา</label>
                            <h5 id="view_course_code"></h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">จำนวนหน่วยกิต</label>
                            <h5><span class="badge bg-primary" id="view_credits"></span></h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">ชื่อวิชา</label>
                            <h5 id="view_course_name"></h5>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">คำอธิบายรายวิชา</label>
                            <p id="view_description"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">คณะ</label>
                            <h6 id="view_faculty"></h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">ภาควิชา</label>
                            <h6 id="view_department"></h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewCourse(id, code, name, description, credits, faculty, department) {
            document.getElementById('view_course_code').textContent = code;
            document.getElementById('view_course_name').textContent = name;
            document.getElementById('view_description').textContent = description || 'ไม่มีคำอธิบาย';
            document.getElementById('view_credits').textContent = credits + ' หน่วยกิต';
            document.getElementById('view_faculty').textContent = faculty;
            document.getElementById('view_department').textContent = department;
            
            var modal = new bootstrap.Modal(document.getElementById('viewModal'));
            modal.show();
        }

        function editCourse(id, code, name, description, credits, faculty, department) {
            document.getElementById('edit_course_id').value = id;
            document.getElementById('edit_course_code').value = code;
            document.getElementById('edit_course_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_credits').value = credits;
            document.getElementById('edit_faculty').value = faculty;
            document.getElementById('edit_department').value = department;
            
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
    </script>
</body>
</html>
