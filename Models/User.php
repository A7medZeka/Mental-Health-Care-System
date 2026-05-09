<?php
require_once __DIR__ . '/../Core/SingletonDatabase.php';
require_once __DIR__ . '/../Interfaces/UserRepositoryInterface.php';

class User implements UserRepositoryInterface {

    protected $user_id;
    protected $email;
    protected $password_hash;
    protected $role;
    protected $age;
    protected $gender;
    protected $created_at;
    protected $phone_number;
    protected $city;
    protected $national_id;
    protected $username;
    protected $first_name;
    protected $last_name;
    protected $conn;
    
    public function __construct() {
        $this->conn = SingletonDatabase::getInstance()->getConnection();
    }
    
    public function getUserById($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    public function getUserAge($user_id) {
        $stmt = $this->conn->prepare("SELECT age FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        return $user['age'] ?? '';
    }
    
    public function getUserGender($user_id) {
        $stmt = $this->conn->prepare("SELECT gender FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        return $user['gender'] ?? '';
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
}
