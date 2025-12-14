<?php
session_start();

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Student.php';

$error_message = "";
$success_message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    
    // ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
    if($user->usernameExists()) {
        $error_message = "ชื่อผู้ใช้นี้มีในระบบแล้ว";
    } elseif($user->emailExists()) {
        $error_message = "อีเมลนี้มีในระบบแล้ว";
    } elseif($_POST['password'] !== $_POST['confirm_password']) {
        $error_message = "รหัสผ่านไม่ตรงกัน";
    } else {
        $user->password = $_POST['password'];
        $user->role = 'student'; // กำหนดเป็น student โดยอัตโนมัติ
        
        $user_id = $user->create();
        
        if($user_id) {
            // สร้างข้อมูลนักศึกษา
            $student = new Student($db);
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
                $success_message = "สมัครสมาชิกเรียบร้อยแล้ว! กรุณาเข้าสู่ระบบ";
            } else {
                $error_message = "เกิดข้อผิดพลาดในการสร้างข้อมูลนักศึกษา";
            }
        } else {
            $error_message = "เกิดข้อผิดพลาดในการสร้างบัญชีผู้ใช้";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Student HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header i {
            font-size: 48px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="form-header">
                <i class="bi bi-person-plus-fill"></i>
                <h2>สมัครสมาชิก</h2>
                <p class="text-muted">Student HRMS</p>
            </div>

            <?php if($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill"></i> <?php echo $success_message; ?>
                    <a href="index.php" class="alert-link">คลิกที่นี่เพื่อเข้าสู่ระบบ</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <h5 class="mb-3"><i class="bi bi-shield-lock"></i> ข้อมูลบัญชีผู้ใช้</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">รหัสนักศึกษา *</label>
                        <input type="text" class="form-control" name="student_code" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ชื่อผู้ใช้ *</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">อีเมล *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">รหัสผ่าน *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ยืนยันรหัสผ่าน *</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3"><i class="bi bi-person-badge"></i> ข้อมูลส่วนตัว</h5>
                <div class="row">
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
                </div>

                <hr class="my-4">

                <h5 class="mb-3"><i class="bi bi-building"></i> ข้อมูลการศึกษา</h5>
                <div class="row">
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

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> สมัครสมาชิก
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> กลับไปหน้าเข้าสู่ระบบ
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
