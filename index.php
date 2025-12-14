<?php
session_start();

// ถ้าเข้าสู่ระบบแล้ว redirect ไปหน้า dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/User.php';

$error_message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    
    if($user->login()) {
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        $_SESSION['email'] = $user->email;
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Student HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
        }
        .login-right {
            padding: 60px 40px;
        }
        .logo {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 500;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="row g-0">
                <div class="col-md-6 login-left">
                    <div class="text-center">
                        <div class="logo">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h2>Student HRMS</h2>
                        <p class="mt-4">ระบบบริหารจัดการข้อมูลนักศึกษา</p>
                        <p>จัดการข้อมูลนักศึกษา อาจารย์ และวิชาเรียนได้อย่างมีประสิทธิภาพ</p>
                    </div>
                </div>
                <div class="col-md-6 login-right">
                    <h3 class="mb-4">เข้าสู่ระบบ</h3>
                    
                    <?php if($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="กรอกชื่อผู้ใช้" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="กรอกรหัสผ่าน" required>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                            </button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                ใช้งานครั้งแรก? <a href="register.php">สมัครสมาชิก</a>
                            </small>
                        </div>
                    </form>

                    <hr class="my-4">
                    
                    <div class="alert alert-info">
                        <strong>ข้อมูลทดสอบ:</strong><br>
                        Username: <code>admin</code><br>
                        Password: <code>password</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
