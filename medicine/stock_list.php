<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("stock.view");
?>
<?php
/* --------------------------
    库存重新计算功能
--------------------------- */
$recalc_message = "";

if (isset($_GET['recalc'])) {
    require_permission("stock.manage");

    // 1. 获取所有药品所有存放位置
    $stockItems = $conn->query("
        SELECT stock_id, drug_id, location_id
        FROM stock
    ");

    while ($row = $stockItems->fetch_assoc()) {
        $drug_id = intval($row['drug_id']);
        $location_id = intval($row['location_id']);
        $stock_id = intval($row['stock_id']);

        // 2. 汇总该药品在该位置所有批次数量
        $sumResult = $conn->query("
            SELECT COALESCE(SUM(quantity), 0) AS total
            FROM batches
            WHERE drug_id = $drug_id
            AND location_id = $location_id
        ");

        $sum = $sumResult->fetch_assoc()['total'];

        // 3. 更新库存数量
        $conn->query("
            UPDATE stock
            SET quantity = $sum
            WHERE stock_id = $stock_id
        ");
    }

    $recalc_message = "<div class='alert alert-success'>" . t("stock_recalc_done") . "</div>";
}


/* --------------------------
    删除库存
--------------------------- */
$delete_message = "";

if (isset($_GET['delete'])) {
    require_permission("stock.manage");
    $delete_id = intval($_GET['delete']);

    $conn->query("DELETE FROM stock WHERE stock_id = $delete_id");

    $delete_message = "<div class='alert alert-success'>" . t("stock_deleted") . "</div>";
}

/* --------------------------
    查询库存数据（JOIN）
--------------------------- */
$sql = "
    SELECT 
        s.stock_id,
        s.quantity,
        s.unit,
        s.min_quantity,
        s.updated_at,
        d.name AS drug_name,
        l.name AS location_name
    FROM stock s
    JOIN drugs d ON s.drug_id = d.drug_id
    LEFT JOIN locations l ON s.location_id = l.location_id
    ORDER BY s.updated_at DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title><?= t("stock_list_title") ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-info text-white">
            <h3><?= t("stock_list_title") ?></h3>
        </div>

        <div class="card-body">

            <!-- 删除提示 -->
            <?php echo $delete_message; ?>
            <?php echo $recalc_message; ?>


            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="d-flex gap-2">
                    <?php if (user_can("stock.manage")): ?>
                        <a href="add_stock.php" class="btn btn-primary">➕ <?= t("add_stock") ?></a>
                        <a href="stock_list.php?recalc=1" class="btn btn-secondary">
                            🔄 <?= t("stock_recalc") ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div>
                    <a class="text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                    <span class="text-muted mx-1">|</span>
                    <a class="text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
                </div>
            </div>
            <table class="table table-bordered table-striped align-middle sortable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th><?= t("drug_name") ?></th>
                        <th><?= t("location_name") ?></th>
                        <th><?= t("quantity") ?></th>
                        <th><?= t("unit") ?></th>
                        <th><?= t("min_quantity") ?></th>
                        <th><?= t("updated_at") ?></th>
                        <th style="width:150px;"><?= t("actions") ?></th>
                    </tr>
                </thead>

                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        // 判断库存是否不足
                        $low = ($row['quantity'] < $row['min_quantity']);
                ?>
                    <tr class="<?php echo $low ? 'table-danger' : ''; ?>">

                        <td><?php echo $row['stock_id']; ?></td>

                        <td><?php echo htmlspecialchars($row['drug_name']); ?></td>

                        <td>
                            <?php 
                                echo $row['location_name'] ? 
                                htmlspecialchars($row['location_name']) : 
                                "<span class='text-muted'>" . t("not_set") . "</span>";
                            ?>
                        </td>

                        <td>
                            <?php 
                                echo $row['quantity'];
                                if ($low) echo " <span class='badge bg-danger'>" . t("low") . "</span>";
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                        <td><?php echo $row['min_quantity']; ?></td>
                        <td><?php echo $row['updated_at']; ?></td>

                        <td>
                            <?php if (user_can("stock.manage")): ?>
                                <a class="btn btn-warning btn-sm"
                                    href="edit_stock.php?id=<?php echo $row['stock_id']; ?>">
                                    <?= t("edit") ?>
                                </a>

                                <a class="btn btn-danger btn-sm"
                                    onclick="return confirm('<?= t("confirm_delete_stock") ?>');"
                                    href="stock_list.php?delete=<?php echo $row['stock_id']; ?>">
                                    <?= t("delete") ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted"><?= t("no_permission") ?></span>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>" . t("no_stock_records") . "</td></tr>";
                }
                ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-secondary"><?= t("return") ?></a>
        </div>
    </div>

</div>
<script src="assets/js/sortable.min.js"></script>
</body>
</html>
