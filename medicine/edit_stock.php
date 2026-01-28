<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("stock.manage");
?>
<?php
/* --------------------------
    检查 ID
--------------------------- */
if (!isset($_GET["id"])) die(t("missing_stock_id"));

$stock_id = intval($_GET["id"]);

/* --------------------------
    获取库存信息（含药品、位置）
--------------------------- */
$sql = "
    SELECT 
        s.*,
        d.name AS drug_name,
        l.name AS location_name
    FROM stock s
    JOIN drugs d ON s.drug_id = d.drug_id
    LEFT JOIN locations l ON s.location_id = l.location_id
    WHERE s.stock_id = $stock_id
    LIMIT 1
";

$res = $conn->query($sql);

if ($res->num_rows === 0) die(t("stock_not_found"));

$stock = $res->fetch_assoc();

/* 方便使用的变量 */
$drug_id      = intval($stock["drug_id"]);
$location_id  = intval($stock["location_id"]);
$quantity     = intval($stock["quantity"]);
$min_quantity = intval($stock["min_quantity"]);
$unit         = $stock["unit"];

/* --------------------------
    获取位置列表
--------------------------- */
$locations = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

/* --------------------------
    提交更新
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_quantity     = intval($_POST["quantity"]);
    $new_min_quantity = intval($_POST["min_quantity"]);
    $new_unit         = $_POST["unit"];
    $new_location_id  = intval($_POST["location_id"]);

    $stmt = $conn->prepare("
    UPDATE stock
    SET min_quantity = ?, unit = ?, location_id = ?
    WHERE stock_id = ?
    ");
    $stmt->bind_param("issi", $new_min_quantity, $new_unit, $new_location_id, $stock_id);

    if ($stmt->execute()) {
        $detail = "更新库存：库存ID={$stock_id}，药品ID={$drug_id}，位置ID={$location_id}→{$new_location_id}，单位={$unit}→{$new_unit}，下限={$min_quantity}→{$new_min_quantity}";
        write_log($conn, "update_stock", $drug_id, $detail);
        header("Location: stock_list.php?updated=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>" . sprintf(t("update_failed"), $stmt->error) . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="UTF-8">
    <title><?= t("edit_stock_title") ?></title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h3><?= sprintf(t("edit_stock_heading"), htmlspecialchars($stock["drug_name"])) ?></h3>
                <div>
                    <a class="text-white text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                    <span class="text-white-50 mx-1">|</span>
                    <a class="text-white text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
                </div>
            </div>

            <div class="card-body">

                <?= $message ?>

                <form method="POST">

                    <!-- 药品名称 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("drug_name") ?></label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($stock["drug_name"]) ?>" disabled>
                    </div>

                    <!-- 数量（只读显示） -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("current_quantity") ?></label>
                        <input type="number" class="form-control bg-light"
                            value="<?= $quantity ?>" readonly>
                    </div>

                    <!-- 下限 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("min_quantity_label") ?></label>
                        <input type="number" class="form-control" name="min_quantity"
                            value="<?= $min_quantity ?>" required>
                    </div>

                    <!-- 单位 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("unit_required") ?></label>
                        <input type="text" class="form-control" name="unit"
                            value="<?= htmlspecialchars($unit) ?>" placeholder="<?= t("unit_label") ?>" required>
                    </div>

                    <!-- 位置 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("location_label") ?></label>
                        <select class="form-select" name="location_id" required>
                            <option value=""><?= t("select_location_placeholder") ?></option>
                            <?php while ($loc = $locations->fetch_assoc()): ?>
                                <option value="<?= $loc["location_id"] ?>"
                                    <?= ($loc["location_id"] == $location_id) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($loc["name"]) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success"><?= t("save_changes") ?></button>
                    <a href="stock_list.php" class="btn btn-secondary"><?= t("return") ?></a>
                </form>

            </div>

        </div>

    </div>

</body>

</html>
