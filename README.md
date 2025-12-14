# Student HRMS (Human Resource Management System)

ระบบบริหารจัดการข้อมูลนักศึกษา พัฒนาด้วย PHP, MySQL และ Bootstrap 5

## ฟีเจอร์หลัก

### 1. ระบบเข้าสู่ระบบ (Authentication)
- เข้าสู่ระบบด้วย Username และ Password
- ระบบจัดการสิทธิ์ผู้ใช้งาน (Admin, Teacher, Student)
- เข้ารหัสรหัสผ่านด้วย bcrypt

### 2. จัดการข้อมูลนักศึกษา (Student Management)
- เพิ่ม แก้ไข ลบ ข้อมูลนักศึกษา (CRUD)
- ค้นหานักศึกษาด้วยรหัสนักศึกษา ชื่อ คณะ สาขา
- แสดงข้อมูลนักศึกษาทั้งหมด
- จัดการสถานะนักศึกษา (กำลังศึกษา, จบการศึกษา, ไม่ใช้งาน)

### 3. จัดการวิชาเรียน (Course Management)
- เพิ่ม แก้ไข ลบ ข้อมูลวิชา
- แสดงรายละเอียดวิชา (รหัสวิชา, ชื่อวิชา, หน่วยกิต, คณะ, ภาควิชา)
- ค้นหาวิชาต่างๆ

### 4. Dashboard
- แสดงสถิติภาพรวมของระบบ
- จำนวนนักศึกษาทั้งหมด
- จำนวนวิชาทั้งหมด
- กิจกรรมล่าสุด
- เมนูด่วนสำหรับการใช้งาน

### 5. ระบบลงทะเบียนเรียน (Enrollment)
- ลงทะเบียนวิชาสำหรับนักศึกษา
- ดูวิชาที่ลงทะเบียน
- ยกเลิกการลงทะเบียน

### 6. ระบบผลการเรียน (Grades)
- บันทึกคะแนนสอบ
- คำนวณเกรด
- แสดง GPA

## เทคโนโลยีที่ใช้

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **Architecture:** MVC Pattern
- **Security:** PDO Prepared Statements, Password Hashing

## โครงสร้างไฟล์

```
student-hrms/
├── config/
│   └── database.php          # การเชื่อมต่อฐานข้อมูล
├── models/
│   ├── User.php              # Model สำหรับผู้ใช้
│   ├── Student.php           # Model สำหรับนักศึกษา
│   └── Course.php            # Model สำหรับวิชา
├── views/                    # (สำหรับแยก View ในอนาคต)
├── controllers/              # (สำหรับแยก Controller ในอนาคต)
├── assets/
│   ├── css/                  # ไฟล์ CSS
│   ├── js/                   # ไฟล์ JavaScript
│   └── images/               # รูปภาพ
├── uploads/                  # สำหรับอัปโหลดไฟล์
├── database.sql              # SQL สำหรับสร้างฐานข้อมูล
├── index.php                 # หน้าเข้าสู่ระบบ
├── dashboard.php             # หน้า Dashboard
├── students.php              # หน้าจัดการนักศึกษา
├── courses.php               # หน้าจัดการวิชา
├── enrollments.php           # หน้าลงทะเบียน
├── grades.php                # หน้าผลการเรียน
├── logout.php                # ออกจากระบบ
└── README.md                 # ไฟล์นี้
```

## การติดตั้งและใช้งาน

### 1. ความต้องการของระบบ
- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Web Server (Apache/Nginx)
- PDO PHP Extension

### 2. ขั้นตอนการติดตั้ง

#### ขั้นตอนที่ 1: ดาวน์โหลดและแตกไฟล์
```bash
# คัดลอกโปรเจคไปยังโฟลเดอร์ htdocs (สำหรับ XAMPP) หรือ www (สำหรับ WAMP)
```

#### ขั้นตอนที่ 2: สร้างฐานข้อมูล
1. เปิด phpMyAdmin (http://localhost/phpmyadmin)
2. สร้างฐานข้อมูลใหม่ชื่อ `student_hrms`
3. Import ไฟล์ `database.sql` เข้าสู่ฐานข้อมูล

หรือใช้คำสั่ง SQL:
```bash
mysql -u root -p < database.sql
```

#### ขั้นตอนที่ 3: ตั้งค่าการเชื่อมต่อฐานข้อมูล
แก้ไขไฟล์ `config/database.php`:
```php
private $host = "localhost";
private $db_name = "student_hrms";
private $username = "root";        // เปลี่ยนตามของคุณ
private $password = "";            // เปลี่ยนตามของคุณ
```

#### ขั้นตอนที่ 4: เข้าใช้งานระบบ
เปิดเว็บเบราว์เซอร์และไปที่:
```
http://localhost/student-hrms/
```

### 3. ข้อมูลเข้าสู่ระบบเริ่มต้น

**Admin Account:**
- Username: `admin`
- Password: `password`

## ฟีเจอร์ที่วางแผนไว้ (Future Enhancements)

- [ ] ระบบจัดการอาจารย์ผู้สอน
- [ ] ระบบตารางเรียน
- [ ] ระบบเช็คชื่อเข้าชั้นเรียน
- [ ] ระบบรายงานสถิติต่างๆ
- [ ] Export ข้อมูลเป็น Excel/PDF
- [ ] ระบบแจ้งเตือน (Notifications)
- [ ] อัปโหลดรูปโปรไฟล์
- [ ] ระบบค้นหาขั้นสูง
- [ ] Multi-language Support
- [ ] API สำหรับ Mobile App

## การรักษาความปลอดภัย

- เข้ารหัสรหัสผ่านด้วย bcrypt
- ใช้ PDO Prepared Statements ป้องกัน SQL Injection
- Sanitize input ด้วย htmlspecialchars
- Session Management
- CSRF Protection (ควรเพิ่ม)

## การแก้ไขปัญหาที่พบบ่อย

### ปัญหา: ไม่สามารถเชื่อมต่อฐานข้อมูล
**แก้ไข:** ตรวจสอบค่า username, password และชื่อฐานข้อมูลในไฟล์ `config/database.php`

### ปัญหา: หน้าเว็บแสดงผิดพลาด 404
**แก้ไข:** ตรวจสอบว่าไฟล์อยู่ในโฟลเดอร์ที่ถูกต้องและ Apache/Nginx ทำงานอยู่

### ปัญหา: Session ไม่ทำงาน
**แก้ไข:** ตรวจสอบว่า PHP session extension เปิดใช้งานอยู่

## License

MIT License - ใช้งานได้อย่างอิสระ

## ผู้พัฒนา

พัฒนาโดย: Claude (AI Assistant)
วันที่: 2025

## การสนับสนุน

หากพบข้อผิดพลาดหรือต้องการความช่วยเหลือ สามารถติดต่อได้ที่:
- Email: support@example.com
- GitHub Issues: (ถ้ามี repository)

---

**หมายเหตุ:** ระบบนี้เป็นระบบตัวอย่างสำหรับการเรียนรู้และพัฒนาต่อยอด ไม่แนะนำให้ใช้งานจริงโดยตรงในสภาพแวดล้อม Production โดยไม่มีการปรับปรุงความปลอดภัยเพิ่มเติม
