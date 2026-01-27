<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
?>
<?php

/* --------------------------
    处理表单提交
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $drug_id      = intval($_POST["drug_id"]);
    $batch_number = $_POST["batch_number"];
    $expire_date  = $_POST["expire_date"];
    $quantity     = intval($_POST["quantity"]);
    $location_id  = intval($_POST["location_id"]);

    $stmt = $conn->prepare("
        INSERT INTO batches (drug_id, batch_number, expire_date, quantity, location_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("issii", $drug_id, $batch_number, $expire_date, $quantity, $location_id);

    if ($stmt->execute()) {
        /* --- 批次添加成功后，更新库存 --- */
        $check = $conn->query("
            SELECT stock_id, quantity 
            FROM stock 
            WHERE drug_id=$drug_id AND location_id=$location_id
            LIMIT 1
        ");

        if ($check->num_rows > 0) {
            // 已有库存 → 增加数量
            $row = $check->fetch_assoc();
            $new_qty = $row["quantity"] + $quantity;

            $conn->query("UPDATE stock SET quantity=$new_qty WHERE stock_id={$row['stock_id']}");
        } else {
            // 没有库存记录 → 直接新建
            $conn->query("
                INSERT INTO stock (drug_id, location_id, quantity, unit, min_quantity)
                VALUES ($drug_id, $location_id, $quantity, '未知', 1)
            ");
        }

        $detail = "新增批次：药品ID={$drug_id}，批号={$batch_number}，有效期={$expire_date}，数量={$quantity}，位置ID={$location_id}";
        write_log($conn, "add_batch", $drug_id, $detail);

        header("Location: add_batch.php?success=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>添加失败：" . $stmt->error . "</div>";
    }

    $stmt->close();
}

/* --------------------------
    获取药品列表
--------------------------- */
$drugs = $conn->query("SELECT drug_id, name FROM drugs ORDER BY name ASC");

?>

<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="UTF-8">
    <title>添加批次</title>

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <!-- Select2 CSS -->
    <link href="assets/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery（Select2 依赖） -->
    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="assets/js/select2.min.js"></script>

</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>添加批次</h3>
            </div>
            <div class="card-body">

                <?php
                if (isset($_GET['success'])) {
                    echo "<div class='alert alert-success'>批次添加成功！</div>";
                }
                echo $message;
                ?>

                <form method="POST">

                    <!-- 药品选择 -->
                    <div class="mb-3">
                        <label class="form-label">药品 *</label>
                        <select class="form-select" name="drug_id" id="drug_select" required>
                            <option value="">请选择药品</option>
                            <?php while ($row = $drugs->fetch_assoc()): ?>
                                <option value="<?= $row['drug_id'] ?>"><?= $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- 批号 -->
                    <div class="mb-3">
                        <label class="form-label">批号（可选）</label>
                        <input type="text" class="form-control" name="batch_number">
                    </div>

                    <!-- 有效期 -->
                    <div class="mb-3">
                        <label class="form-label">有效期 *</label>
                        <input type="date" class="form-control" name="expire_date" lang="en" required>
                    </div>

                    <!-- 数量 -->
                    <div class="mb-3">
                        <label class="form-label">数量 *</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>

                    <!-- 存放位置 -->
                    <div class="mb-3">
                        <label class="form-label">存放位置 *</label>
                        <select class="form-select" name="location_id" required>
                            <option value="">请选择位置</option>
                            <?php
                            $loc = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");
                            while ($row = $loc->fetch_assoc()):
                            ?>
                                <option value="<?= $row['location_id'] ?>"><?= $row['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">提交</button>
                    <a href="dashboard.php" class="btn btn-secondary">返回</a>

                </form>

            </div>
        </div>

    </div>

    <script>
        $(document).ready(function() {
            $('#drug_select').select2({
                placeholder: "搜索药品名称…",
                allowClear: true
            });
        });
    </script>

</body>

</html>
