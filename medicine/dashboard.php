<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="UTF-8">
    <title>药品管理系统 - 首页</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fa;
        }

        .main-card {
            border-radius: 12px;
        }

        .menu-card {
            transition: 0.2s;
            cursor: pointer;
        }

        .menu-card:hover {
            background: #f0f8ff;
            transform: translateY(-2px);
        }

        .menu-icon {
            font-size: 32px;
        }
    </style>
</head>

<body>

    <div class="container mt-5">

        <div class="text-center mb-4">
            <h1 class="fw-bold">药品管理系统</h1>
            <p class="text-muted">请选择需要使用的功能</p>
        </div>

        <div class="card shadow main-card p-4">
            
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3>首页面板</h3>
                <a href="auth/logout.php" class="btn btn-light btn-sm">退出</a>
            </div><br>

            <div class="row g-4">

                <!-- 库存管理 -->
                <div class="col-md-3">
                    <a href="stock_list.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm">
                            <div class="menu-icon text-primary text-center">📦</div>
                            <h5 class="text-center mt-3">库存列表</h5>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="add_stock.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm">
                            <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3">添加库存</h5>
                        </div>
                    </a>
                </div>

                <!-- 批次管理 -->
                <div class="col-md-3">
                    <a href="batch_list.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm">
                            <div class="menu-icon text-warning text-center">📋</div>
                            <h5 class="text-center mt-3">批次列表</h5>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="add_batch.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm">
                            <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3">添加批次</h5>
                        </div>
                    </a>
                </div>

                <!-- 药品管理 -->
                <div class="col-md-3">
                    <a href="drugs_list.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm mt-3">
                            <div class="menu-icon text-info text-center">💊</div>
                            <h5 class="text-center mt-3">药品列表</h5>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="add_drug.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm mt-3">
                            <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3">添加药品</h5>
                        </div>
                    </a>
                </div>

                <!-- 存放位置管理 -->
                <div class="col-md-3">
                    <a href="location_list.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm mt-3">
                            <div class="menu-icon text-secondary text-center">📍</div>
                            <h5 class="text-center mt-3">位置列表</h5>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="add_location.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm mt-3">
                            <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3">添加位置</h5>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="notice_center.php" class="text-decoration-none text-dark">
                        <div class="card menu-card p-3 shadow-sm">
                            <div class="menu-icon text-danger text-center">⚠️</div>
                            <h5 class="text-center mt-3">药品提醒中心</h5>
                        </div>
                    </a>
                </div>

            </div>

        </div>

    </div>

</body>

</html>