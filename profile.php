<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Student.php';

$database = new Database();
$db = $database->getConnection();

$message = "";
$message_type = "";

// Get user info
$user = new User($db);
$user->user_id = $_SESSION['user_id'];

// Get student info if role is student
$student_info = null;
if($_SESSION['role'] == 'student') {
    $student = new Student($db);
    $stmt = $student->readAll();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($row['user_id'] == $_SESSION['user_id']) {
            $student_info = $row;
            break;
        }
    }
}

// Handle password change
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if($_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $check_query = "SELECT password FROM users WHERE user_id = :user_id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':user_id', $_SESSION['user_id']);
        $check_stmt->execute();
        $user_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!password_verify($current_password, $user_data['password'])) {
            $message = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
            $message_type = "danger";
        } elseif($new_password !== $confirm_password) {
            $message = "รหัสผ่านใหม่ไม่ตรงกัน";
            $message_type = "danger";
        } elseif(strlen($new_password) < 6) {
            $message = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
            $message_type = "danger";
        } else {
            $user->user_id = $_SESSION['user_id'];
            if($user->changePassword($new_password)) {
                $message = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
                $message_type = "success";
            } else {
                $message = "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน";
                $message_type = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ - Student HRMS</title>
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
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            color: #667eea;
            font-size: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 4px solid rgba(255,255,255,0.3);
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
                    <a href="enrollments.php">
                        <i class="bi bi-clipboard-check-fill"></i> ลงทะเบียน
                    </a>
                    <a href="grades.php">
                        <i class="bi bi-bar-chart-fill"></i> ผลการเรียน
                    </a>
                    <hr style="border-color: rgba(255,255,255,0.2);">
                    <a href="profile.php" class="active">
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
                        <span class="navbar-brand mb-0 h1">โปรไฟล์ของฉัน</span>
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

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="profile-header">
                                    <div class="profile-avatar">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if($student_info): ?>
                                    <div class="mb-3">
                                        <label class="text-muted small">รหัสนักศึกษา</label>
                                        <p class="mb-0"><strong><?php echo htmlspecialchars($student_info['student_code']); ?></strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">ชื่อ-นามสกุล</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">คณะ</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($student_info['faculty']); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">สาขา</label>
                                        <p class="mb-0"><?php echo htmlspecialchars($student_info['major']); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">ชั้นปี</label>
                                        <p class="mb-0">ปี <?php echo $student_info['year_level']; ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">GPA</label>
                                        <p class="mb-0">
                                            <span class="badge bg-primary" style="font-size: 1rem;">
                                                <?php echo number_format($student_info['gpa'], 2); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> 
                                        คุณเป็น <?php echo htmlspecialchars($_SESSION['role']); ?> ของระบบ
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> ข้อมูลบัญชี</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="text-muted small">ชื่อผู้ใช้</label>
                                            <p class="mb-0"><strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="text-muted small">อีเมล</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="text-muted small">บทบาท</label>
                                            <p class="mb-0">
                                                <span class="badge bg-success">
                                                    <?php 
                                                    $role_text = [
                                                        'admin' => 'ผู้ดูแลระบบ',
                                                        'teacher' => 'อาจารย์',
                                                        'student' => 'นักศึกษา'
                                                    ];
                                                    echo $role_text[$_SESSION['role']];
                                                    ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if($student_info): ?>
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> ข้อมูลส่วนตัว</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="text-muted small">วันเกิด</label>
                                            <p class="mb-0"><?php echo $student_info['date_of_birth'] ? date('d/m/Y', strtotime($student_info['date_of_birth'])) : 'ไม่ระบุ'; ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="text-muted small">เพศ</label>
                                            <p class="mb-0">
                                                <?php 
                                                $gender_text = ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'];
                                                echo $gender_text[$student_info['gender']] ?? 'ไม่ระบุ';
                                                ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="text-muted small">เบอร์โทรศัพท์</label>
                                            <p class="mb-0"><?php echo $student_info['phone'] ?: 'ไม่ระบุ'; ?></p>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="text-muted small">ที่อยู่</label>
                                            <p class="mb-0"><?php echo $student_info['address'] ?: 'ไม่ระบุ'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0"><i class="bi bi-shield-lock"></i> เปลี่ยนรหัสผ่าน</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="change_password">
                                        <div class="mb-3">
                                            <label class="form-label">รหัสผ่านปัจจุบัน *</label>
                                            <input type="password" class="form-control" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">รหัสผ่านใหม่ *</label>
                                            <input type="password" class="form-control" name="new_password" 
                                                   minlength="6" required>
                                            <small class="text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">ยืนยันรหัสผ่านใหม่ *</label>
                                            <input type="password" class="form-control" name="confirm_password" required>
                                        </div>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bi bi-check-circle"></i> เปลี่ยนรหัสผ่าน
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
