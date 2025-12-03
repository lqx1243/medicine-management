<?php require_once "auth/check.php"; ?>
<?php
/* --------------------------
    数据库连接配置
--------------------------- */
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "medicine_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

/* --------------------------
    获取 id 并检查是否存在
--------------------------- */
if (!isset($_GET['id'])) {
    die("缺少药品 ID");
}

$drug_id = intval($_GET['id']);

/* --------------------------
    如果提交表单 → 处理更新
--------------------------- */
$update_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $type = $_POST["type"];
    $spec = $_POST["spec"];
    $storage = $_POST["storage_requirement"];
    $remark = $_POST["remark"];

    $stmt = $conn->prepare("
        UPDATE drugs 
        SET name=?, type=?, spec=?, storage_requirement=?, remark=?
        WHERE drug_id=?
    ");

    $stmt->bind_param("sssssi", $name, $type, $spec, $storage, $remark, $drug_id);

    if ($stmt->execute()) {
        // 修改成功，跳回列表页
        header("Location: drugs_list.php?updated=1");
        exit();
    } else {
        $update_message = "<div class='alert alert-danger'>更新失败：" . $stmt->error . "</div>";
    }

    $stmt->close();
}

/* --------------------------
    读取当前药品信息
--------------------------- */
$sql = "SELECT * FROM drugs WHERE drug_id = $drug_id LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("找不到该药品");
}

$drug = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>编辑药品</title>

    <!-- Bootstrap 美化 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-warning">
            <h3>编辑药品：<?php echo htmlspecialchars($drug["name"]); ?></h3>
        </div>

        <div class="card-body">

            <?php echo $update_message; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">药品名称 *</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($drug['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">剂型</label>
                    <input type="text" class="form-control" name="type" value="<?php echo htmlspecialchars($drug['type']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">规格</label>
                    <input type="text" class="form-control" name="spec" value="<?php echo htmlspecialchars($drug['spec']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">保存要求</label>
                    <input type="text" class="form-control" name="storage_requirement" value="<?php echo htmlspecialchars($drug['storage_requirement']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">备注</label>
                    <textarea class="form-control" name="remark" rows="3"><?php echo htmlspecialchars($drug['remark']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">保存修改</button>
                <a href="drugs_list.php" class="btn btn-secondary">返回</a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
