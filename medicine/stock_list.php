<?php require_once "auth/check.php"; ?>
<?php
/* --------------------------
    数据库连接配置
--------------------------- */
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "medicine_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

/* --------------------------
    库存重新计算功能
--------------------------- */
$recalc_message = "";

if (isset($_GET['recalc'])) {

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

    $recalc_message = "<div class='alert alert-success'>库存已根据批次数据重新计算并更新！</div>";
}


/* --------------------------
    删除库存
--------------------------- */
$delete_message = "";

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    $conn->query("DELETE FROM stock WHERE stock_id = $delete_id");

    $delete_message = "<div class='alert alert-success'>库存记录已删除。</div>";
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
    <title>库存列表</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-info text-white">
            <h3>库存列表</h3>
        </div>

        <div class="card-body">

            <!-- 删除提示 -->
            <?php echo $delete_message; ?>
            <?php echo $recalc_message; ?>


            <a href="add_stock.php" class="btn btn-primary mb-3">➕ 添加库存</a>
            <a href="stock_list.php?recalc=1" class="btn btn-secondary mb-3">
                🔄 重新计算库存
            </a>
            <table class="table table-bordered table-striped align-middle sortable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>药品名称</th>
                        <th>存放位置</th>
                        <th>数量</th>
                        <th>单位</th>
                        <th>下限</th>
                        <th>最后更新</th>
                        <th style="width:150px;">操作</th>
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
                                "<span class='text-muted'>未设置</span>";
                            ?>
                        </td>

                        <td>
                            <?php 
                                echo $row['quantity'];
                                if ($low) echo " <span class='badge bg-danger'>不足</span>";
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                        <td><?php echo $row['min_quantity']; ?></td>
                        <td><?php echo $row['updated_at']; ?></td>

                        <td>
                            <a class="btn btn-warning btn-sm"
                                href="edit_stock.php?id=<?php echo $row['stock_id']; ?>">
                                编辑
                            </a>

                            <a class="btn btn-danger btn-sm"
                                onclick="return confirm('确认删除该库存记录？');"
                                href="stock_list.php?delete=<?php echo $row['stock_id']; ?>">
                                删除
                            </a>
                        </td>

                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>暂无库存记录。</td></tr>";
                }
                ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-secondary">返回</a>
        </div>
    </div>

</div>
<script src="assets/js/sortable.min.js"></script>
</body>
</html>
