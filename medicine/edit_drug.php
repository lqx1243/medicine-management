<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("drug.manage");
?>
<?php
/* --------------------------
    获取 id 并检查是否存在
--------------------------- */
if (!isset($_GET['id'])) {
    die(t("missing_drug_id"));
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

    $old = $conn->query("SELECT name, type, spec, storage_requirement, remark FROM drugs WHERE drug_id = $drug_id LIMIT 1");
    $old_drug = $old ? $old->fetch_assoc() : null;

    $stmt = $conn->prepare("
        UPDATE drugs 
        SET name=?, type=?, spec=?, storage_requirement=?, remark=?
        WHERE drug_id=?
    ");

    $stmt->bind_param("sssssi", $name, $type, $spec, $storage, $remark, $drug_id);

    if ($stmt->execute()) {
        $old_name = $old_drug['name'] ?? '';
        $old_type = $old_drug['type'] ?? '';
        $old_spec = $old_drug['spec'] ?? '';
        $old_storage = $old_drug['storage_requirement'] ?? '';
        $old_remark = $old_drug['remark'] ?? '';

        $detail = "更新药品：药品ID={$drug_id}，名称={$old_name}→{$name}，剂型={$old_type}→{$type}，规格={$old_spec}→{$spec}，保存要求={$old_storage}→{$storage}，备注={$old_remark}→{$remark}";
        write_log($conn, "update_drug", $drug_id, $detail);
        // 修改成功，跳回列表页
        header("Location: drugs_list.php?updated=1");
        exit();
    } else {
        $update_message = "<div class='alert alert-danger'>" . sprintf(t("update_failed"), $stmt->error) . "</div>";
    }

    $stmt->close();
}

/* --------------------------
    读取当前药品信息
--------------------------- */
$sql = "SELECT * FROM drugs WHERE drug_id = $drug_id LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die(t("drug_not_found"));
}

$drug = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("edit_drug_title") ?></title>

    <!-- Bootstrap 美化 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-warning d-flex justify-content-between align-items-center">
            <h3><?= sprintf(t("edit_drug_heading"), htmlspecialchars($drug["name"])) ?></h3>
            <div>
                <a class="text-decoration-none text-dark" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                <span class="text-muted mx-1">|</span>
                <a class="text-decoration-none text-dark" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
            </div>
        </div>

        <div class="card-body">

            <?php echo $update_message; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label"><?= t("drug_name_label") ?></label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($drug['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("drug_type") ?></label>
                    <input type="text" class="form-control" name="type" value="<?php echo htmlspecialchars($drug['type']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("drug_spec") ?></label>
                    <input type="text" class="form-control" name="spec" value="<?php echo htmlspecialchars($drug['spec']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("storage_requirement") ?></label>
                    <input type="text" class="form-control" name="storage_requirement" value="<?php echo htmlspecialchars($drug['storage_requirement']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("remark") ?></label>
                    <textarea class="form-control" name="remark" rows="3"><?php echo htmlspecialchars($drug['remark']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-success"><?= t("save_changes") ?></button>
                <a href="drugs_list.php" class="btn btn-secondary"><?= t("return") ?></a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
