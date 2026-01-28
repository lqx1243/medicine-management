<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("batch.manage");
?>
<?php
/* --------------------------
    检查 id
--------------------------- */
if (!isset($_GET['id'])) {
    die(t("missing_batch_id"));
}

$batch_id = intval($_GET['id']);

/* --------------------------
    获取批次信息
--------------------------- */
$sql = "
    SELECT 
        b.*, 
        d.name AS drug_name,
        l.name AS location_name
    FROM batches b
    JOIN drugs d ON b.drug_id = d.drug_id
    LEFT JOIN locations l ON b.location_id = l.location_id
    WHERE b.batch_id = $batch_id
    LIMIT 1
";



$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die(t("batch_not_found"));
}

$batch = $result->fetch_assoc();

$old_quantity = intval($batch['quantity']);
$old_location = intval($batch['location_id']);
$drug_id      = intval($batch['drug_id']);
$location_name = $batch['location_name'] ?? t("not_set");
$old_batch_number = $batch['batch_number'];
$old_expire_date = $batch['expire_date'];

/* --------------------------
    表单提交 → 更新数据库
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $batch_number = $_POST["batch_number"];
    $expire_date = $_POST["expire_date"];
    $new_quantity = intval($_POST["quantity"]);

    /* --------------------------------------
        1. 更新批次数据
    --------------------------------------- */
    $stmt = $conn->prepare("
        UPDATE batches 
        SET batch_number=?, expire_date=?, quantity=?
        WHERE batch_id=?
    ");
    $stmt->bind_param("ssii", $batch_number, $expire_date, $new_quantity, $batch_id);

    if ($stmt->execute()) {

        /* --------------------------------------
            2. 联动：更新库存
               difference = 新数量 - 旧数量
        --------------------------------------- */
        $difference = $new_quantity - $old_quantity;

        // 必须有位置，数量变化不是0
        if ($old_location > 0 && $difference != 0) {

            $stock_res = $conn->query("
                SELECT stock_id, quantity 
                FROM stock 
                WHERE drug_id = $drug_id AND location_id = $old_location
                LIMIT 1
            ");

            if ($stock_res && $stock_res->num_rows > 0) {
                $s = $stock_res->fetch_assoc();

                $updated_qty = $s["quantity"] + $difference;
                if ($updated_qty < 0) $updated_qty = 0;

                $conn->query("
                    UPDATE stock 
                    SET quantity = $updated_qty
                    WHERE stock_id = {$s['stock_id']}
                ");
            }
        }

        $detail = "更新批次：批次ID={$batch_id}，药品ID={$drug_id}，批号={$old_batch_number}→{$batch_number}，有效期={$old_expire_date}→{$expire_date}，数量={$old_quantity}→{$new_quantity}";
        write_log($conn, "update_batch", $drug_id, $detail);

        /* --------------------------------------
            3. 最后再跳转
        --------------------------------------- */
        header("Location: batch_list.php?updated=1");
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
    <title><?= t("edit_batch_title") ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow">
            <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                <h3><?= sprintf(t("edit_batch_heading"), htmlspecialchars($batch["drug_name"])) ?></h3>
                <div>
                    <a class="text-decoration-none text-dark" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                    <span class="text-muted mx-1">|</span>
                    <a class="text-decoration-none text-dark" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
                </div>
            </div>

            <div class="card-body">

                <?= $message ?>

                <form method="POST">

                    <!-- 药品名称 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("drug_name") ?></label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($batch['drug_name']) ?>" disabled>
                    </div>

                    <!-- 批号 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("batch_number") ?></label>
                        <input type="text" class="form-control"
                            name="batch_number"
                            value="<?= htmlspecialchars($batch['batch_number']) ?>">
                    </div>

                    <!-- 有效期 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("expire_date_label") ?></label>
                        <input type="date" class="form-control" name="expire_date"
                            value="<?= $batch['expire_date'] ?>" required>
                    </div>

                    <!-- 数量 -->
                    <div class="mb-3">
                        <label class="form-label"><?= t("quantity_label") ?></label>
                        <input type="number" class="form-control" name="quantity"
                            value="<?= $batch['quantity'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= t("location_name") ?></label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($location_name) ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-success"><?= t("save_changes") ?></button>
                    <a href="batch_list.php" class="btn btn-secondary"><?= t("return") ?></a>

                </form>

            </div>
        </div>

    </div>

</body>

</html>
