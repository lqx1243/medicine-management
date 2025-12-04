<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
?>
<?php
/* --------------------------
    删除批次（并扣减库存）
--------------------------- */
$delete_message = "";

if (isset($_GET['delete'])) {

    $delete_id = intval($_GET['delete']);

    // 1. 先查批次记录（必须在删除前）
    $batch_res = $conn->query("
        SELECT drug_id, quantity, location_id 
        FROM batches 
        WHERE batch_id = $delete_id
        LIMIT 1
    ");

    if ($batch_res && $batch_res->num_rows > 0) {
        $batch = $batch_res->fetch_assoc();

        $drug_id     = intval($batch['drug_id']);
        $qty         = intval($batch['quantity']);
        $location_id = isset($batch['location_id']) ? intval($batch['location_id']) : 0;

        // 2. 只有在有 location_id 的情况下才去更新库存
        if ($location_id > 0 && $drug_id > 0 && $qty > 0) {

            // 用预处理语句更新库存，防止语法问题
            $stmt = $conn->prepare("
                UPDATE stock 
                SET quantity = quantity - ?
                WHERE drug_id = ? AND location_id = ?
            ");
            $stmt->bind_param("iii", $qty, $drug_id, $location_id);
            $stmt->execute();
            $stmt->close();

            // 3. 防止库存变负
            $conn->query("UPDATE stock SET quantity = 0 WHERE quantity < 0");
        }
    }

    // 4. 最后删除批次记录
    $conn->query("DELETE FROM batches WHERE batch_id = $delete_id");

    $delete_message = "<div class='alert alert-success'>批次记录已删除（已同步更新库存）。</div>";
}


/* --------------------------
    查询批次
--------------------------- */
$sql = "
    SELECT 
        b.batch_id,
        b.batch_number,
        b.expire_date,
        b.quantity,
        d.name AS drug_name,
        DATEDIFF(b.expire_date, CURDATE()) AS days_left
    FROM batches b
    JOIN drugs d ON b.drug_id = d.drug_id
    ORDER BY b.expire_date ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>批次列表（有效期管理）</title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-warning">
            <h3>批次列表（有效期管理）</h3>
        </div>

        <div class="card-body">

            <?php echo $delete_message; ?>

            <a href="add_batch.php" class="btn btn-primary mb-3">➕ 添加批次</a>

            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>药品名称</th>
                        <th>批号</th>
                        <th>有效期</th>
                        <th>剩余天数</th>
                        <th>数量</th>
                        <th style="width:150px;">操作</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                while ($row = $result->fetch_assoc()):

                    $days = $row['days_left'];

                    if ($days < 0) {
                        $row_class = "table-danger";     // 已过期
                        $status = "已过期";
                    } elseif ($days <= 30) {
                        $row_class = "table-warning";     // 临期
                        $status = "临期";
                    } else {
                        $row_class = "";
                        $status = $days . " 天";
                    }

                ?>
                    <tr class="<?= $row_class ?>">
                        <td><?= $row["batch_id"] ?></td>
                        <td><?= htmlspecialchars($row["drug_name"]) ?></td>
                        <td><?= htmlspecialchars($row["batch_number"]) ?></td>
                        <td><?= $row["expire_date"] ?></td>
                        <td><?= $status ?></td>
                        <td><?= $row["quantity"] ?></td>

                        <td>
                            <a class="btn btn-warning btn-sm"
                                href="edit_batch.php?id=<?php echo $row['batch_id']; ?>">
                                编辑
                            </a>

                            <a class="btn btn-danger btn-sm"
                               onclick="return confirm('确定删除该批次？')"
                               href="batch_list.php?delete=<?= $row['batch_id'] ?>">
                               删除
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-secondary">返回</a>        
        </div>
    </div>

</div>

</body>
</html>
