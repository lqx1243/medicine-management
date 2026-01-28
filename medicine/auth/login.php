<?php
session_start();
require_once "../config/db.php";
require_once "../config/i18n.php";

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
    $stmt = $conn->prepare("SELECT password_hash, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($password_hash, $role);

    if ($stmt->num_rows > 0 && $stmt->fetch()) {

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

    $stmt = $conn->prepare("SELECT password_hash, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($password_hash, $role);

    if ($stmt->num_rows === 1 && $stmt->fetch()) {

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

    $error = t("login_error");
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("login_title") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width:400px;">

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3><?= t("login_title") ?></h3>
            <div class="text-end">
                <a class="text-white text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                <span class="text-white-50 mx-1">|</span>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
            </div>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label"><?= t("username") ?></label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("password") ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember">
                    <label class="form-check-label"><?= t("remember_me") ?></label>
                </div>

                <button class="btn btn-primary w-100" type="submit"><?= t("login") ?></button>

            </form>

        </div>
    </div>

</div>

</body>
</html>
