<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("location.manage");
?>
<?php
/* --------------------------
    表单提交处理
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name        = $_POST["name"];
    $description = $_POST["description"];

    if ($name != "") {

        $sql = "
            INSERT INTO locations (name, description)
            VALUES ('$name', '$description')
        ";
        if ($conn->query($sql)) {
            $detail = "新增存放位置：名称={$name}";
            if ($description !== "") {
                $detail .= "，描述={$description}";
            }
            write_log($conn, "add_location", null, $detail);
            header("Location: location_list.php?added=1");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>添加失败：" . $conn->error . "</div>";
        }

    } else {
        $message = "<div class='alert alert-danger'>位置名称不能为空。</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>添加存放位置</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h3>添加存放位置</h3>
        </div>

        <div class="card-body">

            <?= $message ?>

            <form method="POST">

                <!-- 位置名称 -->
                <div class="mb-3">
                    <label class="form-label">位置名称 *</label>
                    <input type="text" class="form-control" name="name" required placeholder="例如：A区-抽屉1、柜子上层、袋子3号">
                </div>

                <!-- 描述 -->
                <div class="mb-3">
                    <label class="form-label">描述（可选）</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="例如：阴凉干燥，避光保存，常用药分区"></textarea>
                </div>

                <button type="submit" class="btn btn-success">添加</button>
                <a href="dashboard.php" class="btn btn-secondary">返回</a>

            </form>

        </div>
    </div>

</div>
</body>
</html>
