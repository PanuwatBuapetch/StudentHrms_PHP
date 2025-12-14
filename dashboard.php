<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/Course.php';

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);
$course = new Course($db);

$total_students = $student->count();
$total_courses = 0;

$course_stmt = $course->readAll();
$total_courses = $course_stmt->rowCount();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student HRMS</title>
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
        .stat-card {
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .stat-icon {
            font-size: 48px;
            opacity: 0.8;
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
                    <a href="dashboard.php" class="active">
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
                    <?php if($_SESSION['role'] == 'admin'): ?>
                    <a href="reports.php">
                        <i class="bi bi-file-earmark-text-fill"></i> รายงาน
                    </a>
                    <?php endif; ?>
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
                <!-- Top Navbar -->
                <nav class="navbar navbar-custom navbar-expand-lg">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">Dashboard</span>
                        <div class="ms-auto">
                            <span class="navbar-text me-3">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                            </span>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                            <p class="text-muted">ภาพรวมระบบบริหารจัดการนักศึกษา</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card bg-primary bg-gradient text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $total_students; ?></h3>
                                        <p class="mb-0">นักศึกษาทั้งหมด</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card bg-success bg-gradient text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo $total_courses; ?></h3>
                                        <p class="mb-0">วิชาเรียนทั้งหมด</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-book-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card bg-info bg-gradient text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">0</h3>
                                        <p class="mb-0">การลงทะเบียน</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-clipboard-check-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card bg-warning bg-gradient text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0">0</h3>
                                        <p class="mb-0">อาจารย์ผู้สอน</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> กิจกรรมล่าสุด</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <i class="bi bi-person-plus-fill text-success"></i>
                                            เพิ่มนักศึกษาใหม่เข้าระบบ
                                            <small class="text-muted float-end">เมื่อสักครู่</small>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-book-fill text-info"></i>
                                            เพิ่มวิชา CS301 Database Systems
                                            <small class="text-muted float-end">1 ชั่วโมงที่แล้ว</small>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="bi bi-pencil-square text-warning"></i>
                                            อัปเดตข้อมูลนักศึกษา
                                            <small class="text-muted float-end">2 ชั่วโมงที่แล้ว</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> กิจกรรมวันนี้</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle-fill"></i>
                                        ยังไม่มีกิจกรรมที่กำหนดไว้สำหรับวันนี้
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> เมนูด่วน</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <a href="students.php?action=add" class="btn btn-outline-primary btn-lg w-100 mb-2">
                                                <i class="bi bi-person-plus-fill d-block" style="font-size: 32px;"></i>
                                                เพิ่มนักศึกษา
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="courses.php?action=add" class="btn btn-outline-success btn-lg w-100 mb-2">
                                                <i class="bi bi-book-fill d-block" style="font-size: 32px;"></i>
                                                เพิ่มวิชา
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="enrollments.php" class="btn btn-outline-info btn-lg w-100 mb-2">
                                                <i class="bi bi-clipboard-check-fill d-block" style="font-size: 32px;"></i>
                                                ลงทะเบียน
                                            </a>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="reports.php" class="btn btn-outline-warning btn-lg w-100 mb-2">
                                                <i class="bi bi-file-earmark-text-fill d-block" style="font-size: 32px;"></i>
                                                รายงาน
                                            </a>
                                        </div>
                                    </div>
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
