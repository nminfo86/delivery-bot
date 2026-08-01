<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once(__DIR__ . "/php/JsonUser.php");
require_once(__DIR__ . "/php/User.php");
require_once(__DIR__ . "/php/JsonDashBoard.php");
require_once(__DIR__ . "/php/JsonCharge.php");
confirmLoggedIn();
accessControl("admin");

if (isset($_SESSION["user_id"])) {
    $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
}
$salesDay = JsonDashBoard::getTotalSales(date("Y-m-d"), date("Y-m-d"));
$salesMonth = JsonDashBoard::getTotalSales(date("Y-m-1"), date("Y-m-t"));
$chargesDay = JsonDashBoard::getTotalCharges(date("Y-m-d"), date("Y-m-d"));
$chargesMonth  = JsonDashBoard::getTotalCharges(date("Y-m-1"), date("Y-m-t"));
$earningsDay = JsonDashBoard::getTotalEarnings(date("Y-m-d"), date("Y-m-d"));
$earningsMonth  = JsonDashBoard::getTotalEarnings(date("Y-m-1"), date("Y-m-t"));

// Normalize possible nulls to floats to avoid deprecation in PHP 8.1+
$salesDay = (float)($salesDay ?? 0);
$salesMonth = (float)($salesMonth ?? 0);
$chargesDay = (float)($chargesDay ?? 0);
$chargesMonth = (float)($chargesMonth ?? 0);
$earningsDay = (float)($earningsDay ?? 0);
$earningsMonth = (float)($earningsMonth ?? 0);

?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Admin-panel" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">
    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <!-- Page Wrapper -->
    <div class="main-content-wrapper">
        <!--loading image-->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Cards Content Row -->
                    <div class="row mt-3 cards">

                        <!-- Sales (Day) Card Example -->
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <div class="card border-left-primary shadow py-2">
                                <div class="card-body dB-card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs text-primary text-uppercase mb-1"
                                                style="white-space: nowrap;">
                                                <?php echo T('sales_day'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span dir="ltr"><?php echo number_format($salesDay, 2, ".", " "); ?></span> <?php echo Config::$cmsCurrency; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sales (Monthly) Card Example -->
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <div class="card border-left-success shadow py-2">
                                <div class="card-body dB-card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs text-primary text-uppercase mb-1"
                                                style="white-space: nowrap;">
                                                <?php echo T('sales_month'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span dir="ltr"><?php echo number_format($salesMonth, 2, ".", " "); ?></span> <?php echo Config::$cmsCurrency; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Expenses (Daily) Card Example -->
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <div class="card border-left-info shadow py-2">
                                <div class="card-body dB-card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs text-success text-uppercase mb-1" style="white-space: nowrap;">
                                                <?php echo T('expenses_day'); ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span dir="ltr"><?php echo number_format($chargesDay, 2, ".", " "); ?></span> <?php echo Config::$cmsCurrency; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Expenses (Monthly) Card Example -->
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <div class="card border-left-secondary shadow py-2">
                                <div class="card-body dB-card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs text-success text-uppercase mb-1" style="white-space: nowrap;">
                                                <?php echo T('expenses_month'); ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span dir="ltr"><?php echo number_format($chargesMonth, 2, ".", " "); ?></span> <?php echo Config::$cmsCurrency; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Earnings (Daily) Card Example -->
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <div class="card border-left-info shadow py-2">
                                <div class="card-body dB-card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs text-warning text-uppercase mb-1"
                                                style="white-space: nowrap;">
                                                <?php echo T('earnings_day'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span dir="ltr"><?php echo number_format($earningsDay, 2, ".", " "); ?></span> <?php echo Config::$cmsCurrency; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings (Monthly) Card Example -->
                        <div class="col-lg-4 col-md-6 col-6 mb-3">
                            <div class="card border-left-warning shadow py-2">
                                <div class="card-body dB-card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs text-warning text-uppercase mb-1"
                                                style="white-space: nowrap;">
                                                <?php echo T('earnings_month'); ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span dir="ltr"><?php echo number_format($earningsMonth, 2, ".", " "); ?></span> <?php echo Config::$cmsCurrency;  ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cards Content Row -->

                    <!-- CA et Sources de gains Charts Content Row -->
                    <div class="row charts">

                        <!-- Area Chart Evolution chiffre d'affaires -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo T('evolution_of_sales'); ?>
                                        <span id="salesPeriod" class="font-weight-normal"></span>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" id="choosePeriod"><?php echo T('configure_period'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area rtl-charts">
                                        <canvas id="earningsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart sales par Catégorie -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo T('sources_of_gains'); ?>
                                        <span id="categoryPeriod" class="font-weight-normal"></span>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" id="chooseCategoryPeriod"><?php echo T('configure_period'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2 rtl-charts">
                                        <canvas id="salesByCategoryChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Charts Content Row -->

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Top 10 Sold Categories -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo T('top_10_categories_sold'); ?>
                                        <span id="topCategoriesPeriod" class="font-weight-normal"></span>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" id="chooseTopCategories"><?php echo T('configure_period'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-bar rtl-charts">
                                        <canvas id="topCategoriesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top 10 Sold Objects -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo T('top_10_articles_sold'); ?>
                                        <span id="topObjectsPeriod" class="font-weight-normal"></span>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" id="chooseTopObjects"><?php echo T('configure_period'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="topObjectsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Top earnings categories -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo T('top_20_earnings_categories'); ?>
                                        <span id="topEarningsCategoriesPeriod" class="font-weight-normal"></span>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" id="chooseTopEarningsCategories"><?php echo T('configure_period'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-bar rtl-charts">
                                        <canvas id="topEarningsCategoriesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top earnings articles -->
                    <div class="row">

                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary"><?php echo T('top_20_earnings_articles'); ?>
                                        <span id="topEarningsArticlesPeriod" class="font-weight-normal"></span>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <a class="dropdown-item" href="#" id="chooseTopEarningsArticles"><?php echo T('configure_period'); ?></a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-bar rtl-charts">
                                        <canvas id="topEarningsArticlesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- /.container-fluid -->

                </div>
                <!-- End of Main Content -->

                <!-- Footer -->
                <?php include "includes/footer.php"; ?>
                <!-- head -->
                <!-- End of Footer -->

            </div>
            <!-- End of Content Wrapper -->

        </div>
        <!-- End of Page Wrapper -->

        <!-- Scroll to Top Button-->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
        <?php include "includes/leg.php"; ?>
        <!-- Leg -->
        <script src="js/ajaxAdminPanel.js?v=<?= filemtime('js/ajaxAdminPanel.js') ?>"></script>

</body>

</html>