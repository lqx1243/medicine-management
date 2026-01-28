<?php
require_once "auth/check.php";
require_once "config/db.php"; //数据库连接
require_once "config/permissions.php";
require_once "config/i18n.php";
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="UTF-8">
    <title><?= t("system_name") ?> - <?= t("dashboard_title") ?></title>

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
            <h1 class="fw-bold"><?= t("system_name") ?></h1>
            <p class="text-muted"><?= t("select_feature") ?></p>
        </div>

        <div class="card shadow main-card p-4">
            
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3><?= t("dashboard_title") ?></h3>
                <div class="d-flex align-items-center gap-2">
                    <a class="text-white text-decoration-none" href="<?= language_switch_url("zh") ?>"><?= t("language_zh") ?></a>
                    <span class="text-white-50">|</span>
                    <a class="text-white text-decoration-none" href="<?= language_switch_url("en") ?>"><?= t("language_en") ?></a>
                    <a href="auth/logout.php" class="btn btn-light btn-sm"><?= t("logout") ?></a>
                </div>
            </div><br>

            <div class="row g-4">

                <!-- 库存管理 -->
                <?php if (user_can("stock.view")): ?>
                    <div class="col-md-3">
                        <a href="stock_list.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm">
                                <div class="menu-icon text-primary text-center">📦</div>
                            <h5 class="text-center mt-3"><?= t("stock_list") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (user_can("stock.manage")): ?>
                    <div class="col-md-3">
                        <a href="add_stock.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm">
                                <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3"><?= t("add_stock") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- 批次管理 -->
                <?php if (user_can("batch.view")): ?>
                    <div class="col-md-3">
                        <a href="batch_list.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm">
                                <div class="menu-icon text-warning text-center">📋</div>
                            <h5 class="text-center mt-3"><?= t("batch_list") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (user_can("batch.manage")): ?>
                    <div class="col-md-3">
                        <a href="add_batch.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm">
                                <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3"><?= t("add_batch") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- 药品管理 -->
                <?php if (user_can("drug.view")): ?>
                    <div class="col-md-3">
                        <a href="drugs_list.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm mt-3">
                                <div class="menu-icon text-info text-center">💊</div>
                            <h5 class="text-center mt-3"><?= t("drug_list") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (user_can("drug.manage")): ?>
                    <div class="col-md-3">
                        <a href="add_drug.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm mt-3">
                                <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3"><?= t("add_drug") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- 存放位置管理 -->
                <?php if (user_can("location.view")): ?>
                    <div class="col-md-3">
                        <a href="location_list.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm mt-3">
                                <div class="menu-icon text-secondary text-center">📍</div>
                            <h5 class="text-center mt-3"><?= t("location_list") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (user_can("location.manage")): ?>
                    <div class="col-md-3">
                        <a href="add_location.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm mt-3">
                                <div class="menu-icon text-success text-center">➕</div>
                            <h5 class="text-center mt-3"><?= t("add_location") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (user_can("notice.view")): ?>
                    <div class="col-md-3">
                        <a href="notice_center.php" class="text-decoration-none text-dark">
                            <div class="card menu-card p-3 shadow-sm">
                                <div class="menu-icon text-danger text-center">⚠️</div>
                            <h5 class="text-center mt-3"><?= t("notice_center") ?></h5>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</body>

</html>
