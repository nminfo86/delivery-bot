<!--Start Header-->
<!--Navbar -->
<?php

//get the cms currency from the database and use it in all pages
require_once(__DIR__ . "/../php/JsonLicence.php");


//Load cms Currency
//This will init the static $variable Config::$cmsCurrency to prevent accessing too much to DB
// initCmsCurrency();

$companiesHeaderCms  = JsonCompany::getAllCompanies(FALSE);
$role = $_SESSION['role'] ?? '';

?>
<!-- Set a global variable for language translation to use every where in JS files -->
<script>
    //All this data are initialized in init.php
    var cmsCurrency = "<?= Config::$cmsCurrency ?>"; //DA, EUR, ...
    var JsCmsLanguage = "<?= Config::$cmsLanguage ?>"; //fr, en, ar

    if (typeof window.JsTranslations === "undefined" || window.JsTranslations === null) {
        window.JsTranslations = <?= json_encode(Config::$langStrings, JSON_UNESCAPED_UNICODE) ?>;
    }

    //for retrieve media path from database in JS
    window.CMS_MEDIA_URL_BASE = "<?= rtrim(Config::$media_url_base, '/') ?>";
    window.CMS_MEDIA_PATH_SUFFIX = "<?= Config::$media_path_suffix ?>";

</script>

<nav class="mb-1 navbar navbar-expand-lg navbar-dark bg-info">

    <div class="container">

        <!-- Left navbar elemnts -->
        <ul class="navbar-nav rtl flex-row ml-3">
            <!-- Chef user panel -->
            <li class="nav-item dropdown chefOptions d-none mr-3">
                <a class="nav-link" href="chefPanel.php"><img src="images/misc/chef-svgrepo.svg" alt="">
                    <span id="suborderCountChefPanel" class="badge badge-danger" style="vertical-align: top;"></span>
                </a>
            </li>

            <!-- Waiter panel -->
            <li class="nav-item  waiterOptions d-none mr-3">
                <a class="nav-link" href="waiterPanel.php"><img src="images/misc/waiter.svg" alt="">
                    <span id="orderCountWaiterPanel" class="badge badge-danger" style="vertical-align: top;"></span>
                </a>
            </li>

            <!-- Waiter History panel -->
            <li class="nav-item  waiterOptions d-none mr-3">
                <a class="nav-link" href="waiterHistory.php">
                    <i class="fas fa-history fa-1_5x"></i>
                    <span id="suborderCountWaiterHistory" class="badge badge-danger" style="vertical-align: top;"></span>
                </a>
            </li>
            
            <!-- Waiter Menu Panel -->
            <li class="nav-item waiterOptions d-none mr-3">
                <a href="checkoutMenu.php" class="nav-link"><i class="fas fa-solar-panel fa-1_5x"></i></a>
            </li>

            <!-- Checkout  panel -->
            <li class="nav-item dropdown checkoutOptions d-none mr-3">
                <a class="nav-link" href="checkoutPanel.php">
                    <i class="fas fa-cash-register fa-1_5x"></i>
                    <span id="orderCountCheckoutPanel" class="badge badge-danger" style="vertical-align: top;"></span>
                </a>
            </li>
            <!-- Checkout History panel -->
            <li class="nav-item  checkoutOptions d-none mr-3">
                <a class="nav-link" href="checkoutHistory.php"><i class="fas fa-history fa-1_5x"></i></a>
            </li>
            <!-- Checkout MENU panel -->
            <li class="nav-item checkoutOptions d-none mr-3">
                <a href="checkoutMenu.php" class="nav-link"><i class="fas fa-solar-panel fa-1_5x"></i></a>
            </li>
            <!-- Admin panel eatSmartly CMS -->
            <li>
                <a title="<?= T('dashboard') ?>" class="navbar-brand d-none bouhezilaCms" href="adminPanel.php"><i
                        class="fas fa-chart-line fa-1_5x"></i></a>
            </li>

        </ul>

        <!-- This is added because when connecting with superAdmin in FoodCourt mode, 
             there are some pages that must be desabled from admin and enables to superAdmin 
             for control access all this is based on companies number in database -->
        <!-- superAdmin nav bar Collapse -->
        <button class="navbar-toggler ml-2 superAdminOptions d-none" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent-0" aria-controls="navbarSupportedContent-0" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent-0">
            <ul class="navbar-nav mr-1">
                <li class=" nav-item dropdown superAdminOptions active d-none">
                    <a class="nav-link dropdown-toggle" href="" id="dropdown00" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><?php echo T('configuration'); ?></a>
                    <div class="dropdown-menu" aria-labelledby="dropdown00">
                        <a href="superAdminPanel.php" class="dropdown-item"><?php echo T('company'); ?></a>
                        <?php
                        if ((is_countable($companiesHeaderCms) ? count($companiesHeaderCms) : 0) > 1) {
                            echo '<a class="dropdown-item" href="attributeManagement.php">' . T('attributes') . '</a>';
                            echo '<a class="dropdown-item" href="tableManagement.php">' . T('tables') . '</a>';
                            echo '<a class="dropdown-item" href="pizzaVariants.php">' . T('pizza_variantes') . '</a>';
                            echo '<a class="dropdown-item" href="vatManagement.php">' . T('vat') . '</a>';
                        }
                        ?>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Right navbar elemnts -->
        <!-- ************************* Admin navbar collapse **************************** -->
        <button class="navbar-toggler ml-2 adminOptions d-none" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent-1" aria-controls="navbarSupportedContent-1" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Admin nav bar Collapse -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent-1">
            <ul class="navbar-nav rtl mr-1">
                <li class=" nav-item dropdown  adminOptions active d-none">
                    <a class="nav-link dropdown-toggle" href="" id="dropdown01" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><?php echo T('menu'); ?></a>
                    <div class="dropdown-menu" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="categoryManagement.php"><?php echo T('categories'); ?></a>
                        <a class="dropdown-item" href="objectManagement.php"><?php echo T('articles'); ?></a>
                        <?php
                        if ((is_countable($companiesHeaderCms) ? count($companiesHeaderCms) : 0) <= 1) {
                            echo '<a class="dropdown-item" href="attributeManagement.php">' . T('attributes') . '</a>';
                        }
                        ?>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav rtl">
                <li class="nav-item dropdown adminOptions active d-none">
                    <a class="nav-link dropdown-toggle" href="" id="dropdown02" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><?php echo T('reports'); ?></a>
                    <div class="dropdown-menu" aria-labelledby="dropdown02">
                        <a class="dropdown-item" href="checkoutHistory.php"><?php echo T('orders'); ?></a>
                        <a class="dropdown-item" href="__report_io.php"><?php echo T('i/o'); ?></a>
                        <a class="dropdown-item" href="__report_sales_by_attributes.php"><?php echo T('sales_by_categories'); ?></a>
                        <a class="dropdown-item" href="__report_earnings.php"><?php echo T('earnings'); ?></a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav rtl">
                <li class="nav-item dropdown adminOptions active d-none">
                    <a class="nav-link dropdown-toggle" href="" id="dropdown03" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false"><?php echo T('configuration')?></a>
                    <div class="dropdown-menu" aria-labelledby="dropdown03">
                        <a class="dropdown-item" href="typeChargeManagement.php"><?php echo T('expense_types')?></a>

                        <?php
                        if ((is_countable($companiesHeaderCms) ? count($companiesHeaderCms) : 0) <= 1) {
                            echo '<a class="dropdown-item" href="tableManagement.php">' . T('tables') . '</a>';
                            echo '<a class="dropdown-item" href="pizzaVariants.php">' . T('pizza_variantes') . '</a>';
                            echo '<a class="dropdown-item" href="vatManagement.php">' . T('vat') . '</a>';
                        }
                        ?>
                            <a class="dropdown-item" href="printerManagement.php"><?php echo T('printers')?></a>
                        <?php
                            if ((is_countable($companiesHeaderCms) ? count($companiesHeaderCms) : 0) <= 1) {
                            echo '<a class="dropdown-item" href="superAdminPanel.php">' . T('company') . '</a>';
                            }
                         ?>
                    </div>
                </li>
            </ul>
        </div>

        <!-- MENU Show Detail button toggler -->
        <ul class="navbar-nav ml-auto nav-flex-icons checkoutOptions waiterOptions d-none">
            <li class="nav-item mr-5 d-lg-none" id="showDetailToggler" style="cursor: pointer;">
                <a class="nav-link"><i class="fas fa-shopping-basket" style="font-size: 1.6em;"></i></a>
            </li>
        </ul>

        <!-- Charges Mngmt -->
        <ul class="navbar-nav rtl adminOptions checkoutOptions d-none mr-2">
            <li class="nav-item">
                <a title="<?= T('expenses_title') ?>" class="nav-link" href="chargeManagement.php"><i class="fas fa-receipt fa-1_5x"></i></a>
            </li>
        </ul>
        <!-- users Panel toggler button -->
        <ul class="navbar-nav rtl adminOptions d-none">
            <li class="nav-item "><a title="<?= T('user_mgmt') ?>" href="userManagement.php" class="nav-link"><i class="fas fa-users fa-1_5x"></i></a>
            </li>
        </ul>
        <!-- backup -->
        <?php
        if ((is_countable($companiesHeaderCms) ? count($companiesHeaderCms) : 0) <= 1) {
            echo '<ul class="navbar-nav rtl adminOptions checkoutOptions d-none mr-2">
            <li class="nav-item">
                <a title="' . T('backup_title') . '" class="nav-link" href="../backup.php" target="_blank"><i class="fas fa-save fa-1_5x"></i></a>
            </li>
        </ul>';
        } 
        else {
            echo '<ul class="navbar-nav rtl superAdminOptions  d-none mr-2">
            <li class="nav-item">
                <a title="' . T('backup_title') . '" class="nav-link" href="../backup.php" target="_blank"><i class="fas fa-save fa-1_5x"></i></a>
            </li>
        </ul>';
        }
        ?>
        <!-- Logout icon -->
        <ul class="navbar-nav rtl">
            <li class="nav-item "><a title="<?= T('logout') ?>" href="../mycms/logout.php" class="nav-link"><i
                        class="fas fa-power-off fa-1_5x"></i></a>
            </li>
        </ul>

    </div>
</nav>
<!--/.Navbar -->

<!-- Needed for acces controle over javascript pages -->
<input id="sessionRole" type="text" class='d-none'
    value="<?php echo isset($_SESSION["role"]) ? $_SESSION["role"] : "" ?>">
<input id="sessionCompanyId" type="text" class='d-none'
    value="<?php echo isset($_SESSION["company_id"]) ? $_SESSION["company_id"] : "" ?>">

<!-- Needed for currency to use it over all javascript pages -->
<input id="headerCmsCurrency" type="text" class='d-none'
    value="<?php echo Config::$cmsCurrency ?>">
 

<!-- *************************************  START Supplement Modal  *************************************************-->

<div class="modal fade" id="supplementModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="exampleModalLabel" style="margin-left: 15%;">Supplements ..
                    إضافات</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="supplement-table clearfix">
                    <table class="table table-responsive">
                        <tbody>
                           
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal" id="closeSupplementModalBtn"
                    value="">fermer - أغلق</button>
            </div>
        </div>
    </div>
</div>


<!-- *************************************  Supplement Modal  *************************************************-->

<div id="divLoadingcms" class="d-none">

    <img src="images/misc/loading.svg ">

</div>

<!--End Header-->