<?php
namespace Calendar;
class User
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $verified;
    private $role;
    public function __construct($id = null, $username = null, $email = null, $password = null, $verified = '0', $role = 'user')
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->verified = $verified;
        $this->role = $role;
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    public function getRole()
    {
        return $this->role;
    }
    public function setRole($role)
    {
        $this->role = $role;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setUsername($username)
    {
        $this->username = $username;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPassword($password)
    {
        $this->password = $password;
    }
    public function getUserByLogin($EmailorUsername)
    {
        $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = get_pdo()->prepare($sql);
        $stmt->execute([$EmailorUsername, $EmailorUsername]);
        return $stmt->fetch();
    }
    public function getUser($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = get_pdo()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $this->id = $row["id"];
        $this->username = $row["username"];
        $this->email = $row["email"];
        $this->password = $row["password"];
        $this->verified = $row["verified"];
        $this->role = $row["role"];
        return $row;
    }
    public function getAll()
    {
        $sql = "SELECT * FROM users";
        $stmt = get_pdo()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function login($EmailorUsername, $password)
    {
        $login = $this->getUserByLogin($EmailorUsername);
        if ($login) {
            if (password_verify($password, $login['password'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['id'] = $login['id'];
                return true;
            }
        }
        return false;
    }
    public function logout()
    {
        session_destroy();
    }
    public function register($username = null, $email = null, $password = null)
    {
        if ($this->getUserByLogin($username) || $this->getUserByLogin($email)) {
            return false;
        }
        $sql = "INSERT into users (username, email, password) VALUES (?, ?, ?)";
        $stmt = get_pdo()->prepare($sql);
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return true;
    }
    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = get_pdo()->prepare($sql);
        return $stmt->execute([$id]);
    }
    public function updateRole($id, $role)
    {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = get_pdo()->prepare($sql);
        return $stmt->execute([$role, $id]);
    }
}