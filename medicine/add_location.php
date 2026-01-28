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

        $stmt = $conn->prepare("
            INSERT INTO locations (name, description)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $description);

        if ($stmt->execute()) {
            $detail = "新增存放位置：名称={$name}";
            if ($description !== "") {
                $detail .= "，描述={$description}";
            }
            write_log($conn, "add_location", null, $detail);
            header("Location: location_list.php?added=1");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>" . sprintf(t("add_failed"), $stmt->error) . "</div>";
        }

        $stmt->close();
    } else {
        $message = "<div class='alert alert-danger'>" . t("location_name_required") . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("location_add_title") ?></title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h3><?= t("location_add_title") ?></h3>
            <div>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                <span class="text-white-50 mx-1">|</span>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
            </div>
        </div>

        <div class="card-body">

            <?= $message ?>

            <form method="POST">

                <!-- 位置名称 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("location_name_label") ?> *</label>
                    <input type="text" class="form-control" name="name" required placeholder="<?= t("location_name_placeholder") ?>">
                </div>

                <!-- 描述 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("description_optional") ?></label>
                    <textarea class="form-control" name="description" rows="3" placeholder="<?= t("description_placeholder") ?>"></textarea>
                </div>

                <button type="submit" class="btn btn-success"><?= t("add") ?></button>
                <a href="dashboard.php" class="btn btn-secondary"><?= t("return") ?></a>

            </form>

        </div>
    </div>

</div>
</body>
</html>
