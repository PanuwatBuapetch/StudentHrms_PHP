-- สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS student_hrms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_hrms;

-- ตารางผู้ใช้งาน (Users)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางนักศึกษา (Students)
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    phone VARCHAR(20),
    address TEXT,
    faculty VARCHAR(100),
    major VARCHAR(100),
    year_level INT,
    gpa DECIMAL(3,2) DEFAULT 0.00,
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางอาจารย์ (Teachers)
CREATE TABLE IF NOT EXISTS teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    teacher_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    phone VARCHAR(20),
    email VARCHAR(100),
    faculty VARCHAR(100),
    department VARCHAR(100),
    position VARCHAR(100),
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางวิชา (Courses)
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(200) NOT NULL,
    description TEXT,
    credits INT NOT NULL,
    faculty VARCHAR(100),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางการสอน (Teaching Assignments)
CREATE TABLE IF NOT EXISTS teaching_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    semester VARCHAR(20) NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    section VARCHAR(10),
    schedule TEXT,
    room VARCHAR(50),
    max_students INT DEFAULT 40,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางการลงทะเบียน (Enrollments)
CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assignment_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES teaching_assignments(assignment_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, assignment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางคะแนน (Grades)
CREATE TABLE IF NOT EXISTS grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    midterm_score DECIMAL(5,2),
    final_score DECIMAL(5,2),
    assignment_score DECIMAL(5,2),
    total_score DECIMAL(5,2),
    letter_grade VARCHAR(2),
    grade_point DECIMAL(3,2),
    comments TEXT,
    graded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ตารางการเข้าชั้นเรียน (Attendance)
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (enrollment_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ข้อมูลตัวอย่าง
-- Admin User
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hrms.com', 'admin');
-- รหัสผ่าน: password

-- ข้อมูลคณะและสาขา
INSERT INTO courses (course_code, course_name, description, credits, faculty, department) VALUES
('CS101', 'Introduction to Computer Science', 'พื้นฐานวิทยาการคอมพิวเตอร์', 3, 'วิทยาศาสตร์', 'คอมพิวเตอร์'),
('CS201', 'Data Structures', 'โครงสร้างข้อมูล', 3, 'วิทยาศาสตร์', 'คอมพิวเตอร์'),
('CS301', 'Database Systems', 'ระบบฐานข้อมูล', 3, 'วิทยาศาสตร์', 'คอมพิวเตอร์'),
('MATH101', 'Calculus I', 'แคลคูลัส 1', 3, 'วิทยาศาสตร์', 'คณิตศาสตร์'),
('ENG101', 'English Communication', 'การสื่อสารภาษาอังกฤษ', 3, 'มนุษยศาสตร์', 'ภาษาอังกฤษ');

-- ข้อมูล Teaching Assignments สำหรับทดสอบ (ไม่มีอาจารย์ในตัวอย่าง)
-- หมายเหตุ: teacher_id ถูกตั้งเป็น NULL เพราะยังไม่มีข้อมูลอาจารย์
INSERT INTO teaching_assignments (course_id, teacher_id, semester, academic_year, section, schedule, room, max_students) VALUES
(1, NULL, '1/2567', '2567', '01', 'จันทร์ 09:00-12:00', 'CS-201', 40),
(2, NULL, '1/2567', '2567', '01', 'อังคาร 13:00-16:00', 'CS-202', 40),
(3, NULL, '1/2567', '2567', '01', 'พุธ 09:00-12:00', 'CS-301', 35),
(4, NULL, '1/2567', '2567', '01', 'พฤหัสบดี 09:00-12:00', 'SC-101', 45),
(5, NULL, '1/2567', '2567', '01', 'ศุกร์ 13:00-16:00', 'HM-101', 50);
