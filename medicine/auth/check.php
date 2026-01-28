<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/permissions.php";
require_once __DIR__ . "/../config/i18n.php";

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

ensure_user_role($conn);
?>
