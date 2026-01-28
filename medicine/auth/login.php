<?php
session_start();
require_once "../config/db.php";

// 如果已经登录 → 跳主页
if (isset($_SESSION["user"])) {
    header("Location: ../dashboard.php");
    exit();
}

// 检查是否有“保持登录” Cookie
if (isset($_COOKIE["remember_token"])) {
    $token = $_COOKIE["remember_token"];
    list($username, $hash) = explode(":", $token);

    // 从数据库获取用户
    $sql = "SELECT password_hash, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($row = $result->fetch_assoc()) {
        $password_hash = $row['password_hash'];
        $role = $row['role'];
    }

    if ($result->num_rows > 0) {

        if (hash_equals($hash, hash_hmac("sha256", $username, "your_secret_key"))) {
            // cookie有效 → 自动登录
            $_SESSION["user"] = $username;
            $_SESSION["role"] = $role ?: "viewer";
            header("Location: ../dashboard.php");
            exit();
        }
    }
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]);

    $sql = "SELECT password_hash, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($row = $result->fetch_assoc()) {
        $password_hash = $row['password_hash'];
        $role = $row['role'];
    }

    if ($result->num_rows === 1) {

        if (password_verify($password, $password_hash)) {

            $_SESSION["user"] = $username;
            $_SESSION["role"] = $role ?: "viewer";

            // “保持登录” Cookie
            if ($remember) {
                $token = $username . ":" . hash_hmac("sha256", $username, "your_secret_key");
                setcookie("remember_token", $token, time() + (86400 * 7), "/", "", false, true);
            }

            header("Location: ../dashboard.php");
            exit();
        }
    }

    $error = "用户名或密码错误。";
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:400px;">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>系统登录</h3>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">用户名</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">密码</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember">
                    <label class="form-check-label">保持登录（7天）</label>
                </div>

                <button class="btn btn-primary w-100" type="submit">登录</button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
