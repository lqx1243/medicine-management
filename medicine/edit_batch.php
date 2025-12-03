<?php require_once "auth/check.php"; ?>
<?php
/* --------------------------
    数据库连接
--------------------------- */
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "medicine_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("连接失败: " . $conn->connect_error);

/* --------------------------
    检查 id
--------------------------- */
if (!isset($_GET['id'])) {
    die("缺少批次 ID");
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
    die("未找到该批次记录");
}

$batch = $result->fetch_assoc();

$old_quantity = intval($batch['quantity']);
$old_location = intval($batch['location_id']);
$drug_id      = intval($batch['drug_id']);
$location_name = $batch['location_name'] ?? "未设置位置";

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

        /* --------------------------------------
            3. 最后再跳转
        --------------------------------------- */
        header("Location: batch_list.php?updated=1");
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
    <title>编辑批次</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow">
            <div class="card-header bg-warning">
                <h3>编辑批次：<?= htmlspecialchars($batch["drug_name"]) ?></h3>
            </div>

            <div class="card-body">

                <?= $message ?>

                <form method="POST">

                    <!-- 药品名称 -->
                    <div class="mb-3">
                        <label class="form-label">药品名称</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($batch['drug_name']) ?>" disabled>
                    </div>

                    <!-- 批号 -->
                    <div class="mb-3">
                        <label class="form-label">批号</label>
                        <input type="text" class="form-control"
                            name="batch_number"
                            value="<?= htmlspecialchars($batch['batch_number']) ?>">
                    </div>

                    <!-- 有效期 -->
                    <div class="mb-3">
                        <label class="form-label">有效期 *</label>
                        <input type="date" class="form-control" name="expire_date"
                            value="<?= $batch['expire_date'] ?>" required>
                    </div>

                    <!-- 数量 -->
                    <div class="mb-3">
                        <label class="form-label">数量 *</label>
                        <input type="number" class="form-control" name="quantity"
                            value="<?= $batch['quantity'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">存放位置</label>
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($location_name) ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-success">保存修改</button>
                    <a href="batch_list.php" class="btn btn-secondary">返回</a>

                </form>

            </div>
        </div>

    </div>

</body>

</html>