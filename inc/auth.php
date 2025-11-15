<?php
session_start();
require_once __DIR__ . '/db.php';

function login($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'role' => $row['role']
            ];
            return true;
        }
    }
    return false;
}

function require_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
}

function require_role($roles) {
    require_login();
    if (!in_array($_SESSION['user']['role'], (array)$roles)) {
        header("HTTP/1.1 403 Forbidden");
        echo "Akses ditolak!";
        exit();
    }
}

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
