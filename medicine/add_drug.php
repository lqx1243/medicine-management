<?php require_once "auth/check.php"; ?>
<?php
/* --------------------------
    数据库连接配置
--------------------------- */
$host = "localhost";
$user = "root";       // XAMPP 默认
$pass = "";           // XAMPP 默认
$dbname = "medicine_system";

$conn = new mysqli($host, $user, $pass, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

/* --------------------------
    处理表单提交
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $type = $_POST["type"];
    $spec = $_POST["spec"];
    $storage = $_POST["storage_requirement"];
    $remark = $_POST["remark"];

    // 使用预处理语句（防 SQL 注入）
    $stmt = $conn->prepare("
        INSERT INTO drugs (name, type, spec, storage_requirement, remark)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssss", $name, $type, $spec, $storage, $remark);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>药品已成功添加！</div>";
    } else {
        $message = "<div class='alert alert-danger'>添加失败：" . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>添加药品</title>

    <!-- Bootstrap 美化 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>添加药品</h3>
        </div>

        <div class="card-body">

            <!-- 显示添加结果（成功/失败） -->
            <?php echo $message; ?>

            <form method="POST" action="add_drug.php">

                <div class="mb-3">
                    <label class="form-label">药品名称 *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">剂型（注射剂 / 片剂 / 胶囊剂等）</label>
                    <input type="text" class="form-control" name="type">
                </div>

                <div class="mb-3">
                    <label class="form-label">规格（如：0.5g*10片/盒）</label>
                    <input type="text" class="form-control" name="spec">
                </div>

                <div class="mb-3">
                    <label class="form-label">保存要求（如：避光、2-8°C、冷藏）</label>
                    <input type="text" class="form-control" name="storage_requirement">
                </div>

                <div class="mb-3">
                    <label class="form-label">备注（可选）</label>
                    <textarea class="form-control" name="remark" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-success">提交</button>
                <a href="dashboard.php" class="btn btn-secondary">返回</a>

            </form>
        </div>
    </div>

</div>

</body>
</html>
