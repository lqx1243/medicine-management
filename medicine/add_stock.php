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
    处理表单提交
--------------------------- */
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $drug_id = $_POST["drug_id"];
    $location_id = $_POST["location_id"];
    $quantity = $_POST["quantity"];
    $unit = $_POST["unit"];
    $min_quantity = $_POST["min_quantity"];

    $stmt = $conn->prepare("
        INSERT INTO stock (drug_id, location_id, quantity, unit, min_quantity)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iiisi", $drug_id, $location_id, $quantity, $unit, $min_quantity);

    if ($stmt->execute()) {
        header("Location: add_stock.php?success=1");
        exit();
    } else {
        $message = "<div class='alert alert-danger'>添加失败：" . $stmt->error . "</div>";
    }

    $stmt->close();
}

/* --------------------------
    获取药品列表
--------------------------- */
$drug_list = $conn->query("SELECT drug_id, name FROM drugs ORDER BY name ASC");

/* --------------------------
    获取存放位置列表
--------------------------- */
$location_list = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>添加库存</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>添加库存</h3>
        </div>

        <div class="card-body">

            <!-- 成功提示 -->
            <?php
            if (isset($_GET['success'])) {
                echo "<div class='alert alert-success'>库存添加成功！</div>";
            }
            ?>

            <?php echo $message; ?>

            <form method="POST" action="add_stock.php">

                <!-- 药品选择 -->
                <div class="mb-3">
                    <label class="form-label">药品 *</label>
                    <select class="form-select" name="drug_id" required>
                        <option value="">请选择药品</option>
                        <?php
                        if ($drug_list->num_rows > 0) {
                            while ($row = $drug_list->fetch_assoc()) {
                                echo "<option value='{$row['drug_id']}'>{$row['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- 存放位置 -->
                <div class="mb-3">
                    <label class="form-label">存放位置 *</label>
                    <select class="form-select" name="location_id" required>
                        <option value="">请选择位置</option>
                        <?php
                        if ($location_list->num_rows > 0) {
                            while ($row = $location_list->fetch_assoc()) {
                                echo "<option value='{$row['location_id']}'>{$row['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- 数量 -->
                <div class="mb-3">
                    <label class="form-label">数量 *</label>
                    <input type="number" class="form-control" name="quantity" required>
                </div>

                <!-- 单位 -->
                <div class="mb-3">
                    <label class="form-label">单位（如：盒 / 瓶 / 支）</label>
                    <input type="text" class="form-control" name="unit">
                </div>

                <!-- 库存下限提醒 -->
                <div class="mb-3">
                    <label class="form-label">库存下限（低于此数量时提醒）</label>
                    <input type="number" class="form-control" name="min_quantity" value="0">
                </div>

                <button type="submit" class="btn btn-success">提交</button>
                <a href="dashboard.php" class="btn btn-secondary">返回</a>

            </form>
        </div>
    </div>

</div>

</body>
</html>
