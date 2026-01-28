<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("drug.manage");
?>
<?php
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
        $drug_id = $conn->insert_id;
        $detail = "新增药品：名称={$name}，剂型={$type}，规格={$spec}，保存要求={$storage}";
        if ($remark !== "") {
            $detail .= "，备注={$remark}";
        }
        write_log($conn, "add_drug", $drug_id, $detail);
        $message = "<div class='alert alert-success'>" . t("drug_add_success") . "</div>";
    } else {
        $message = "<div class='alert alert-danger'>" . sprintf(t("add_failed"), $stmt->error) . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("add_drug_title") ?></title>

    <!-- Bootstrap 美化 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3><?= t("add_drug_title") ?></h3>
            <div>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                <span class="text-white-50 mx-1">|</span>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
            </div>
        </div>

        <div class="card-body">

            <!-- 显示添加结果（成功/失败） -->
            <?php echo $message; ?>

            <form method="POST" action="add_drug.php">

                <div class="mb-3">
                    <label class="form-label"><?= t("drug_name_label") ?></label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("drug_type_hint") ?></label>
                    <input type="text" class="form-control" name="type">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("drug_spec_hint") ?></label>
                    <input type="text" class="form-control" name="spec">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("storage_requirement_hint") ?></label>
                    <input type="text" class="form-control" name="storage_requirement">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("remark_optional") ?></label>
                    <textarea class="form-control" name="remark" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-success"><?= t("submit") ?></button>
                <a href="dashboard.php" class="btn btn-secondary"><?= t("return") ?></a>

            </form>
        </div>
    </div>

</div>

</body>
</html>
