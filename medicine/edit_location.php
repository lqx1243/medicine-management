<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("location.manage");
?>
<?php
/* --------------------------
    获取 id 并检查是否存在
--------------------------- */
if (!isset($_GET['id'])) {
    die(t("missing_location_id"));
}

$location_id = intval($_GET['id']);

/* --------------------------
    如果提交表单 → 处理更新
--------------------------- */
$update_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST["name"];
    $description = $_POST["description"];

    $old = $conn->query("SELECT name, description FROM locations WHERE location_id = $location_id LIMIT 1");
    $old_location = $old ? $old->fetch_assoc() : null;

    $stmt = $conn->prepare("
        UPDATE locations
        SET name=?, description=?
        WHERE location_id=?
    ");

    $stmt->bind_param("ssi", $name, $description, $location_id);

    if ($stmt->execute()) {
        $old_name = $old_location['name'] ?? '';
        $old_description = $old_location['description'] ?? '';
        $detail = "更新存放位置：位置ID={$location_id}，名称={$old_name}→{$name}，描述={$old_description}→{$description}";
        write_log($conn, "update_location", null, $detail);

        header("Location: location_list.php?updated=1");
        exit();
    } else {
        $update_message = "<div class='alert alert-danger'>" . sprintf(t("update_failed"), $stmt->error) . "</div>";
    }

    $stmt->close();
}

/* --------------------------
    读取当前位置信息
--------------------------- */
$sql = "SELECT * FROM locations WHERE location_id = $location_id LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die(t("location_not_found"));
}

$location = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("edit_location_title") ?></title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-warning d-flex justify-content-between align-items-center">
            <h3><?= sprintf(t("edit_location_heading"), htmlspecialchars($location["name"])) ?></h3>
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
                    <label class="form-label"><?= t("location_name_label") ?> *</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($location['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= t("description") ?></label>
                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($location['description']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-success"><?= t("save_changes") ?></button>
                <a href="location_list.php" class="btn btn-secondary"><?= t("return") ?></a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
