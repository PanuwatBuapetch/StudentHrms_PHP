<?php
class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $password;
    public $email;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // สร้างผู้ใช้ใหม่
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, 
                      email=:email, role=:role";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // เข้าสู่ระบบ
    public function login() {
        $query = "SELECT user_id, username, password, email, role 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password, $row['password'])) {
            $this->user_id = $row['user_id'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // ตรวจสอบว่า username ซ้ำหรือไม่
    public function usernameExists() {
        $query = "SELECT user_id FROM " . $this->table_name . " 
                  WHERE username = :username LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // ตรวจสอบว่า email ซ้ำหรือไม่
    public function emailExists() {
        $query = "SELECT user_id FROM " . $this->table_name . " 
                  WHERE email = :email LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // เปลี่ยนรหัสผ่าน
    public function changePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password = :password 
                  WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
