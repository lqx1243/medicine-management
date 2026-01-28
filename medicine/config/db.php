<?php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "medicine_system";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) die("DB连接失败：" . $conn->connect_error);

$conn->set_charset("utf8mb4");

function write_log($conn, $action, $drug_id, $detail, $username = null)
{
    if ($username === null && isset($_SESSION["user"])) {
        $username = $_SESSION["user"];
    }

    if ($username === null || $username === "") {
        $username = "系统";
    }

    if ($drug_id === null) {
        $sql = "
        INSERT INTO logs (action, detail, username)
        VALUES ('$action', '$detail', '$username')
    ";
    } else {
        $sql = "
        INSERT INTO logs (action, drug_id, detail, username)
        VALUES ('$action', $drug_id, '$detail', '$username')
    ";
    }

    $conn->query($sql);

}
?>
