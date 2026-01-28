<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_permission("drug.view");
?>
<?php
/* --------------------------
    删除药品（如果 URL 中包含 delete）
--------------------------- */
$delete_message = "";

if (isset($_GET['delete'])) {
    require_permission("drug.manage");
    $delete_id = intval($_GET['delete']);

    // 删除药品（会触发外键级联删除 stock、batches）
    $conn->query("DELETE FROM drugs WHERE drug_id = $delete_id");

    $delete_message = "<div class='alert alert-success'>" . t("drug_deleted") . "</div>";
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
    <title><?= t("drug_list_title") ?></title>

    <!-- Bootstrap 美化 -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h3><?= t("drug_list_title") ?></h3>
        </div>

        <div class="card-body">

            <!-- 显示删除提示 -->
            <?php echo $delete_message; ?>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <?php if (user_can("drug.manage")): ?>
                        <a href="add_drug.php" class="btn btn-primary">➕ <?= t("add_drug") ?></a>
                    <?php endif; ?>
                </div>
                <div>
                    <a class="text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                    <span class="text-muted mx-1">|</span>
                    <a class="text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
                </div>
            </div>

            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th><?= t("drug_name") ?></th>
                        <th><?= t("drug_type") ?></th>
                        <th><?= t("drug_spec") ?></th>
                        <th><?= t("storage_requirement") ?></th>
                        <th><?= t("remark") ?></th>
                        <th><?= t("created_at") ?></th>
                        <th style="width: 150px;"><?= t("actions") ?></th>
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
                            <?php if (user_can("drug.manage")): ?>
                                <a class="btn btn-warning btn-sm" href="edit_drug.php?id=<?php echo $row['drug_id']; ?>">
                                    <?= t("edit") ?>
                                </a>

                                <a class="btn btn-danger btn-sm"
                                    onclick="return confirm('<?= t("confirm_delete_drug") ?>')"
                                    href="drugs_list.php?delete=<?php echo $row['drug_id']; ?>">
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
                    echo "<tr><td colspan='8' class='text-center'>" . t("no_drug_records") . "</td></tr>";
                }
                ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-secondary"><?= t("return") ?></a>    
        </div>
    </div>

</div>

</body>
</html>
