<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
?>
<?php
/* --------------------------
    删除药品（如果 URL 中包含 delete）
--------------------------- */
$delete_message = "";

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // 删除药品（会触发外键级联删除 stock、batches）
    $conn->query("DELETE FROM drugs WHERE drug_id = $delete_id");

    $delete_message = "<div class='alert alert-success'>药品已成功删除。</div>";
}

/* --------------------------
    读取所有药品
--------------------------- */
$sql = "SELECT * FROM drugs ORDER BY drug_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>药品列表</title>

    <!-- Bootstrap 美化 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h3>药品列表</h3>
        </div>

        <div class="card-body">

            <!-- 显示删除提示 -->
            <?php echo $delete_message; ?>

            <a href="add_drug.php" class="btn btn-primary mb-3">➕ 添加药品</a>

            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>药品名称</th>
                        <th>剂型</th>
                        <th>规格</th>
                        <th>保存要求</th>
                        <th>备注</th>
                        <th>添加时间</th>
                        <th style="width: 150px;">操作</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                    <tr>
                        <td><?php echo $row['drug_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo htmlspecialchars($row['spec']); ?></td>
                        <td><?php echo htmlspecialchars($row['storage_requirement']); ?></td>
                        <td><?php echo htmlspecialchars($row['remark']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>

                        <td>
                            <a class="btn btn-warning btn-sm" href="edit_drug.php?id=<?php echo $row['drug_id']; ?>">
                                编辑
                            </a>

                            <a class="btn btn-danger btn-sm"
                                onclick="return confirm('确认删除该药品吗？')"
                                href="drugs_list.php?delete=<?php echo $row['drug_id']; ?>">
                                删除
                            </a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>暂无药品，请添加。</td></tr>";
                }
                ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-secondary">返回</a>    
        </div>
    </div>

</div>

</body>
</html>
