<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("stock.manage");
?>
<?php
/* --------------------------
    处理表单提交
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $drug_id = $_POST["drug_id"];
    $location_id = $_POST["location_id"];
    $quantity = $_POST["quantity"];
    $unit = $_POST["unit"];
    $min_quantity = $_POST["min_quantity"];

    $stmt = $conn->prepare("
        INSERT INTO stock (drug_id, location_id, quantity, unit, min_quantity)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iiisi", $drug_id, $location_id, $quantity, $unit, $min_quantity);

    if ($stmt->execute()) {
        $detail = "新增库存：药品ID={$drug_id}，位置ID={$location_id}，数量={$quantity}，单位={$unit}，下限={$min_quantity}";
        write_log($conn, "add_stock", intval($drug_id), $detail);
        header("Location: add_stock.php?success=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>" . sprintf(t("add_failed"), $stmt->error) . "</div>";
    }

    $stmt->close();
}

/* --------------------------
    获取药品列表
--------------------------- */
$drug_list = $conn->query("SELECT drug_id, name FROM drugs ORDER BY name ASC");

/* --------------------------
    获取存放位置列表
--------------------------- */
$location_list = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("add_stock_title") ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3><?= t("add_stock_title") ?></h3>
            <div>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                <span class="text-white-50 mx-1">|</span>
                <a class="text-white text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
            </div>
        </div>

        <div class="card-body">

            <!-- 成功提示 -->
            <?php
            if (isset($_GET['success'])) {
                echo "<div class='alert alert-success'>" . t("stock_add_success") . "</div>";
            }
            ?>

            <?php echo $message; ?>

            <form method="POST" action="add_stock.php">

                <!-- 药品选择 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("drug_label") ?></label>
                    <select class="form-select" name="drug_id" required>
                        <option value=""><?= t("select_drug") ?></option>
                        <?php
                        if ($drug_list->num_rows > 0) {
                            while ($row = $drug_list->fetch_assoc()) {
                                echo "<option value='{$row['drug_id']}'>{$row['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- 存放位置 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("location_label") ?></label>
                    <select class="form-select" name="location_id" required>
                        <option value=""><?= t("select_location") ?></option>
                        <?php
                        if ($location_list->num_rows > 0) {
                            while ($row = $location_list->fetch_assoc()) {
                                echo "<option value='{$row['location_id']}'>{$row['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- 数量 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("quantity_label") ?></label>
                    <input type="number" class="form-control" name="quantity" required>
                </div>

                <!-- 单位 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("unit_label") ?></label>
                    <input type="text" class="form-control" name="unit">
                </div>

                <!-- 库存下限提醒 -->
                <div class="mb-3">
                    <label class="form-label"><?= t("min_quantity_hint") ?></label>
                    <input type="number" class="form-control" name="min_quantity" value="0">
                </div>

                <button type="submit" class="btn btn-success"><?= t("submit") ?></button>
                <a href="dashboard.php" class="btn btn-secondary"><?= t("return") ?></a>

            </form>
        </div>
    </div>

</div>

</body>
</html>
