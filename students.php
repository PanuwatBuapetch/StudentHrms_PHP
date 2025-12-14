<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);
$message = "";
$message_type = "";

// Handle Actions
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Delete Student
    if($action == 'delete' && isset($_GET['id'])) {
        $student->student_id = $_GET['id'];
        if($student->delete()) {
            $message = "ลบข้อมูลนักศึกษาเรียบร้อยแล้ว";
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
            // Create User first
            $user = new User($db);
            $user->username = $_POST['student_code'];
            $user->password = $_POST['password'];
            $user->email = $_POST['email'];
            $user->role = 'student';
            
            $user_id = $user->create();
            
            if($user_id) {
                // Create Student
                $student->user_id = $user_id;
                $student->student_code = $_POST['student_code'];
                $student->first_name = $_POST['first_name'];
                $student->last_name = $_POST['last_name'];
                $student->date_of_birth = $_POST['date_of_birth'];
                $student->gender = $_POST['gender'];
                $student->phone = $_POST['phone'];
                $student->address = $_POST['address'];
                $student->faculty = $_POST['faculty'];
                $student->major = $_POST['major'];
                $student->year_level = $_POST['year_level'];
                $student->status = 'active';
                
                if($student->create()) {
                    $message = "เพิ่มข้อมูลนักศึกษาเรียบร้อยแล้ว";
                    $message_type = "success";
                } else {
                    $message = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
                    $message_type = "danger";
                }
            }
        } elseif($_POST['action'] == 'edit') {
            $student->student_id = $_POST['student_id'];
            $student->first_name = $_POST['first_name'];
            $student->last_name = $_POST['last_name'];
            $student->date_of_birth = $_POST['date_of_birth'];
            $student->gender = $_POST['gender'];
            $student->phone = $_POST['phone'];
            $student->address = $_POST['address'];
            $student->faculty = $_POST['faculty'];
            $student->major = $_POST['major'];
            $student->year_level = $_POST['year_level'];
            $student->status = $_POST['status'];
            
            if($student->update()) {
                $message = "อัปเดตข้อมูลนักศึกษาเรียบร้อยแล้ว";
                $message_type = "success";
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
                $message_type = "danger";
            }
        }
    }
}

// Search
$search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
if($search_keyword) {
    $stmt = $student->search($search_keyword);
} else {
    $stmt = $student->readAll();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการนักศึกษา - Student HRMS</title>
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
                    <a href="students.php" class="active">
                        <i class="bi bi-people-fill"></i> จัดการนักศึกษา
                    </a>
                    <a href="courses.php">
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
                        <span class="navbar-brand mb-0 h1">จัดการนักศึกษา</span>
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

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0"><i class="bi bi-people-fill"></i> รายชื่อนักศึกษา</h5>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                        <i class="bi bi-plus-circle"></i> เพิ่มนักศึกษา
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search Bar -->
                            <form method="GET" class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="ค้นหา รหัสนักศึกษา, ชื่อ, คณะ, สาขา..." 
                                           value="<?php echo htmlspecialchars($search_keyword); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i> ค้นหา
                                    </button>
                                    <?php if($search_keyword): ?>
                                        <a href="students.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> ล้าง
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <!-- Students Table -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>รหัสนักศึกษา</th>
                                            <th>ชื่อ-นามสกุล</th>
                                            <th>คณะ</th>
                                            <th>สาขา</th>
                                            <th>ชั้นปี</th>
                                            <th>GPA</th>
                                            <th>สถานะ</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if($stmt->rowCount() > 0) {
                                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $status_badge = $row['status'] == 'active' ? 'success' : 'secondary';
                                                $status_text = $row['status'] == 'active' ? 'กำลังศึกษา' : 
                                                              ($row['status'] == 'graduated' ? 'จบการศึกษา' : 'ไม่ใช้งาน');
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['student_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['faculty']); ?></td>
                                            <td><?php echo htmlspecialchars($row['major']); ?></td>
                                            <td><?php echo $row['year_level']; ?></td>
                                            <td><span class="badge bg-info"><?php echo number_format($row['gpa'], 2); ?></span></td>
                                            <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="viewStudent(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" 
                                                        onclick="editStudent(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="?action=delete&id=<?php echo $row['student_id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>ไม่พบข้อมูลนักศึกษา</td></tr>";
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

    <!-- Add Student Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">เพิ่มนักศึกษาใหม่</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสนักศึกษา *</label>
                                <input type="text" class="form-control" name="student_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสผ่าน *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อ *</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">นามสกุล *</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">วันเกิด</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เพศ</label>
                                <select class="form-select" name="gender">
                                    <option value="male">ชาย</option>
                                    <option value="female">หญิง</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">คณะ *</label>
                                <input type="text" class="form-control" name="faculty" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สาขา *</label>
                                <input type="text" class="form-control" name="major" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชั้นปี *</label>
                                <select class="form-select" name="year_level" required>
                                    <option value="1">ปี 1</option>
                                    <option value="2">ปี 2</option>
                                    <option value="3">ปี 3</option>
                                    <option value="4">ปี 4</option>
                                </select>
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

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">แก้ไขข้อมูลนักศึกษา</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="student_id" id="edit_student_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">รหัสนักศึกษา</label>
                                <input type="text" class="form-control" id="edit_student_code" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อ *</label>
                                <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">นามสกุล *</label>
                                <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">วันเกิด</label>
                                <input type="date" class="form-control" name="date_of_birth" id="edit_date_of_birth">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เพศ</label>
                                <select class="form-select" name="gender" id="edit_gender">
                                    <option value="male">ชาย</option>
                                    <option value="female">หญิง</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">คณะ *</label>
                                <input type="text" class="form-control" name="faculty" id="edit_faculty" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สาขา *</label>
                                <input type="text" class="form-control" name="major" id="edit_major" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชั้นปี *</label>
                                <select class="form-select" name="year_level" id="edit_year_level" required>
                                    <option value="1">ปี 1</option>
                                    <option value="2">ปี 2</option>
                                    <option value="3">ปี 3</option>
                                    <option value="4">ปี 4</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" name="status" id="edit_status">
                                    <option value="active">กำลังศึกษา</option>
                                    <option value="inactive">ไม่ใช้งาน</option>
                                    <option value="graduated">จบการศึกษา</option>
                                </select>
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

    <!-- View Student Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">รายละเอียดนักศึกษา</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">รหัสนักศึกษา</label>
                            <h5 id="view_student_code"></h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">ชื่อ-นามสกุล</label>
                            <h5 id="view_fullname"></h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">วันเกิด</label>
                            <p id="view_date_of_birth"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">เพศ</label>
                            <p id="view_gender"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">เบอร์โทรศัพท์</label>
                            <p id="view_phone"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p id="view_email"></p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">ที่อยู่</label>
                            <p id="view_address"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">คณะ</label>
                            <h6 id="view_faculty"></h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">สาขา</label>
                            <h6 id="view_major"></h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">ชั้นปี</label>
                            <p id="view_year_level"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">GPA</label>
                            <h5><span class="badge bg-primary" id="view_gpa"></span></h5>
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
        function editStudent(student) {
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_student_code').value = student.student_code;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_date_of_birth').value = student.date_of_birth;
            document.getElementById('edit_gender').value = student.gender;
            document.getElementById('edit_phone').value = student.phone || '';
            document.getElementById('edit_address').value = student.address || '';
            document.getElementById('edit_faculty').value = student.faculty;
            document.getElementById('edit_major').value = student.major;
            document.getElementById('edit_year_level').value = student.year_level;
            document.getElementById('edit_status').value = student.status;
            
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        function viewStudent(student) {
            document.getElementById('view_student_code').textContent = student.student_code;
            document.getElementById('view_fullname').textContent = student.first_name + ' ' + student.last_name;
            document.getElementById('view_date_of_birth').textContent = student.date_of_birth || 'ไม่ระบุ';
            document.getElementById('view_gender').textContent = student.gender === 'male' ? 'ชาย' : (student.gender === 'female' ? 'หญิง' : 'อื่นๆ');
            document.getElementById('view_phone').textContent = student.phone || 'ไม่ระบุ';
            document.getElementById('view_email').textContent = student.email || 'ไม่ระบุ';
            document.getElementById('view_address').textContent = student.address || 'ไม่ระบุ';
            document.getElementById('view_faculty').textContent = student.faculty;
            document.getElementById('view_major').textContent = student.major;
            document.getElementById('view_year_level').textContent = 'ปี ' + student.year_level;
            document.getElementById('view_gpa').textContent = parseFloat(student.gpa).toFixed(2);
            
            var modal = new bootstrap.Modal(document.getElementById('viewModal'));
            modal.show();
        }
    </script>
</body>
</html>
