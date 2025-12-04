<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
?>
<?php
/* --------------------------
    删除位置（检查是否被使用）
--------------------------- */
$message = "";

if (isset($_GET["delete"])) {
    $location_id = intval($_GET["delete"]);

    // 检查是否被库存记录使用
    $check_stock = $conn->query("
        SELECT 1 FROM stock WHERE location_id = $location_id LIMIT 1
    ");

    // 检查是否被批次使用
    $check_batch = $conn->query("
        SELECT 1 FROM batches WHERE location_id = $location_id LIMIT 1
    ");

    if ($check_stock->num_rows > 0 || $check_batch->num_rows > 0) {
        $message = "<div class='alert alert-danger'>该位置正在被使用，无法删除。</div>";
    } else {
        $conn->query("DELETE FROM locations WHERE location_id = $location_id");
        $message = "<div class='alert alert-success'>位置已删除。</div>";
    }
}

/* --------------------------
    查询所有位置
--------------------------- */
$result = $conn->query("SELECT * FROM locations ORDER BY location_id DESC");
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>存放位置列表</title>

    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-secondary text-white">
            <h3>存放位置列表</h3>
        </div>

        <div class="card-body">

            <?= $message ?>

            <a href="add_location.php" class="btn btn-primary mb-3">➕ 添加位置</a>

            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>位置名称</th>
                        <th>描述</th>
                        <th style="width:150px;">操作</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row["location_id"] ?></td>
                        <td><?= htmlspecialchars($row["name"]) ?></td>
                        <td><?= htmlspecialchars($row["description"]) ?></td>
                        <td>
                            <a class="btn btn-danger btn-sm"
                               onclick="return confirm('确定删除该位置？')"
                               href="location_list.php?delete=<?= $row['location_id'] ?>">
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
