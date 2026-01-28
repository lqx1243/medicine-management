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
if (!isset($_GET["id"])) die("缺少库存 ID");

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

if ($res->num_rows === 0) die("未找到库存记录。");

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
        $message = "<div class='alert alert-danger'>更新失败：" . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="UTF-8">
    <title>编辑库存</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h3>编辑库存：<?= htmlspecialchars($stock["drug_name"]) ?></h3>
            </div>

            <div class="card-body">

                <?= $message ?>

                <form method="POST">

                    <!-- 药品名称 -->
                    <div class="mb-3">
                        <label class="form-label">药品名称</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($stock["drug_name"]) ?>" disabled>
                    </div>

                    <!-- 数量（只读显示） -->
                    <div class="mb-3">
                        <label class="form-label">当前数量</label>
                        <input type="number" class="form-control bg-light"
                            value="<?= $quantity ?>" readonly>
                    </div>

                    <!-- 下限 -->
                    <div class="mb-3">
                        <label class="form-label">提醒下限 *</label>
                        <input type="number" class="form-control" name="min_quantity"
                            value="<?= $min_quantity ?>" required>
                    </div>

                    <!-- 单位 -->
                    <div class="mb-3">
                        <label class="form-label">单位 *</label>
                        <input type="text" class="form-control" name="unit"
                            value="<?= htmlspecialchars($unit) ?>" placeholder="瓶/盒/支" required>
                    </div>

                    <!-- 位置 -->
                    <div class="mb-3">
                        <label class="form-label">存放位置 *</label>
                        <select class="form-select" name="location_id" required>
                            <option value="">请选择...</option>
                            <?php while ($loc = $locations->fetch_assoc()): ?>
                                <option value="<?= $loc["location_id"] ?>"
                                    <?= ($loc["location_id"] == $location_id) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($loc["name"]) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">保存修改</button>
                    <a href="stock_list.php" class="btn btn-secondary">返回</a>
                </form>

            </div>

        </div>

    </div>

</body>

</html>
