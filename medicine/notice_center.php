<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("notice.view");
?>
<?php
$delete_message = "";

// 删除批次
if (isset($_GET['delete'])) {
    require_permission("batch.manage");
    $delete_id = intval($_GET['delete']);

    // 1. 取出数量和位置（用于扣减库存）
    $batch_res = $conn->query("SELECT drug_id, quantity, location_id FROM batches WHERE batch_id = $delete_id");

    if ($batch_res && $batch_res->num_rows > 0) {
        $batch = $batch_res->fetch_assoc();

        $drug_id = $batch['drug_id'];
        $qty     = $batch['quantity'];
        $loc     = $batch['location_id'];

        // 2. 删除批次
        $conn->query("DELETE FROM batches WHERE batch_id = $delete_id");

        // 3. 扣减库存
        if ($loc !== null) {
            $conn->query("
                UPDATE stock 
                SET quantity = GREATEST(quantity - $qty, 0)
                WHERE drug_id = $drug_id AND location_id = $loc
            ");
        }

        $delete_message = "<div class='alert alert-success'>" . t("batch_deleted_notice") . "</div>";
    }
}

/* ------------------------------------------
    1. 查询临期批次（有效期 <= 30天 或 已过期）
------------------------------------------- */
$exp_sql = "
    SELECT 
        b.batch_id,
        b.batch_number,
        b.expire_date,
        b.quantity,
        d.name AS drug_name,
        l.name AS location_name,
        DATEDIFF(b.expire_date, CURDATE()) AS days_left
    FROM batches b
    JOIN drugs d ON b.drug_id = d.drug_id
    LEFT JOIN locations l ON b.location_id = l.location_id
    WHERE DATEDIFF(b.expire_date, CURDATE()) <= 30
    ORDER BY b.expire_date ASC
";


$exp_result = $conn->query($exp_sql);


/* ------------------------------------------
    2. 查询库存不足药品（quantity < min_quantity）
------------------------------------------- */
$low_sql = "
    SELECT 
        s.stock_id,
        s.quantity,
        s.min_quantity,
        s.unit,
        d.name AS drug_name,
        l.name AS location_name
    FROM stock s
    JOIN drugs d ON s.drug_id = d.drug_id
    LEFT JOIN locations l ON s.location_id = l.location_id
    WHERE s.quantity < s.min_quantity
    ORDER BY s.quantity ASC
";

$low_result = $conn->query($low_sql);

?>

<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="UTF-8">
    <title><?= t("notice_title") ?></title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .section-card {
            border-radius: 12px;
        }

        .expired {
            background-color: #f8d7da !important;
        }

        .warning {
            background-color: #fff3cd !important;
        }
    </style>
</head>

<body>

    <div class="container mt-5">

        <h1 class="fw-bold mb-4 text-center">⚠️ <?= t("notice_center") ?></h1>

        <div class="text-end mb-3">
            <a class="text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
            <span class="text-muted mx-1">|</span>
            <a class="text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
        </div>

        <!-- ============================= -->
        <!-- 一：临期批次 -->
        <!-- ============================= -->
        <div class="card mb-5 shadow section-card">
            <div class="card-header bg-warning">
                <h3 class="m-0">📋 <?= t("notice_batch_title") ?></h3>
            </div>

            <div class="card-body">
                <?= $delete_message ?>


                <?php if ($exp_result->num_rows == 0): ?>
                    <p class="text-success"><?= t("notice_no_batch") ?></p>
                <?php else: ?>

                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th><?= t("drug_name") ?></th>
                                <th><?= t("batch_number") ?></th>
                                <th><?= t("expire_date") ?></th>
                                <th><?= t("days_left") ?></th>
                                <th><?= t("location_name") ?></th>
                                <th><?= t("quantity") ?></th>
                                <th><?= t("actions") ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $exp_result->fetch_assoc()):
                                $days = $row['days_left'];

                                if ($days < 0) {
                                    $row_class = "expired";
                                    $status = t("expired");
                                } elseif ($days <= 30) {
                                    $row_class = "warning";
                                    $status = sprintf(t("only_left"), $days);
                                }
                            ?>
                                <tr class="<?= $row_class ?>">
                                    <td><?= $row['batch_id'] ?></td>
                                    <td><?= htmlspecialchars($row['drug_name']) ?></td>
                                    <td><?= htmlspecialchars($row['batch_number']) ?></td>
                                    <td><?= $row['expire_date'] ?></td>
                                    <td><?= $status ?></td>
                                    <td><?= $row['location_name'] ? htmlspecialchars($row['location_name']) : '<span class="text-muted">' . t("not_set") . '</span>' ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td>
                                        <?php if (user_can("batch.manage")): ?>
                                            <a href="edit_batch.php?id=<?= $row['batch_id'] ?>" class="btn btn-warning btn-sm"><?= t("edit") ?></a>

                                            <a class="btn btn-danger btn-sm"
                                                onclick="return confirm('<?= t("confirm_delete_batch") ?>')"
                                                href="notice_center.php?delete=<?= $row['batch_id'] ?>">
                                                <?= t("delete") ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted"><?= t("no_permission") ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>
                        </tbody>

                    </table>

                <?php endif; ?>
            </div>
        </div>


        <!-- ============================= -->
        <!-- 二：库存不足药品 -->
        <!-- ============================= -->
        <div class="card shadow section-card">
            <div class="card-header bg-danger text-white">
                <h3 class="m-0">📦 <?= t("notice_stock_title") ?></h3>
            </div>

            <div class="card-body">
                <?php if ($low_result->num_rows == 0): ?>
                    <p class="text-success"><?= t("notice_no_stock") ?></p>
                <?php else: ?>

                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th><?= t("drug_name") ?></th>
                                <th><?= t("location_name") ?></th>
                                <th><?= t("current_stock") ?></th>
                                <th><?= t("min_quantity") ?></th>
                                <th><?= t("unit") ?></th>
                                <th><?= t("actions") ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $low_result->fetch_assoc()): ?>
                                <tr class="expired">
                                    <td><?= $row['stock_id'] ?></td>
                                    <td><?= htmlspecialchars($row['drug_name']) ?></td>
                                    <td><?= $row['location_name'] ? htmlspecialchars($row['location_name']) : '<span class="text-muted">' . t("not_set") . '</span>' ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= $row['min_quantity'] ?></td>
                                    <td><?= htmlspecialchars($row['unit']) ?></td>
                                    <td>
                                        <?php if (user_can("stock.manage")): ?>
                                            <a href="edit_stock.php?id=<?= $row['stock_id'] ?>" class="btn btn-warning btn-sm"><?= t("edit") ?></a>
                                        <?php else: ?>
                                            <span class="text-muted"><?= t("no_permission") ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>

                    </table>

                <?php endif; ?>

            </div>
        </div>
        <a href="dashboard.php" class="btn btn-secondary"><?= t("return") ?></a>
    </div>

</body>

</html>
