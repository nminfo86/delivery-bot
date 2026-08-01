<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once(__DIR__ . "/php/JsonUser.php");
require_once(__DIR__ . "/php/JsonTable.php");
require_once(__DIR__ . "/php/User.php");
require_once(__DIR__ . "/php/JsonDashBoard.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("admin,checkout");

// if (isset($_SESSION["user_id"])) {
//     $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
// }

$userRole = isset($_SESSION["role"]) ? $_SESSION["role"] : "";

$tables = JsonTable::getAllTables(False);
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Checkout panel" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="container-lg">

        <div class="welcome-user d-none">
            <h3><?= T('checkout_title') ?></h3>
        </div>
        <div id="allOrdersDiv">
            <form id="searchForm" class="form-row mb-1">
                <div class="validation-div hide-div">
                </div>

                <div class="input-group">
                    <div class="input-group mb-3 start-date">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control" id="start_date" placeholder=""
                            value="<?php echo date('d-m-Y'); ?>">
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-2">
                        <?php fillTablesCms($tables) ?>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="searchInput" name="startSubOrder"
                            placeholder="<?= T('search_placeholder') ?>" validation="SPECIAL-MIXED">
                    </div>

                </div>
            </form>
            <div class="row">
                <div id="ordersDiv" class="col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="table-responsive">
                        <table id="allOrderstable" class='table table-hover'> </table>
                    </div>
                </div>
                <div id="orderDetail" class="col-lg-7 col-md-6 col-sm-12 col-12 d-none mt-xs-3 mt-md-3">
                    <h4 class="mb-3"></h4>
                    <div class="table-responsive">
                        <table id="subOrderstable" class='table table-hover'>
                            <tr>
                                <th><?= T('orders_table_article') ?></th>
                                <th><?= T('orders_table_quantity') ?></th>
                                <th><?= T('orders_table_amount') ?></th>
                                <th><?= T('orders_table_action') ?></th>
                            </tr>
                        </table>
                    </div>
                    <h3></h3>

                    <div class="btn-toolbar justify-content-between mb-3">

                        <!-- Only Admin can cancel orders -->
                        <?php if ($userRole == Config::$roleAdmin): ?>
                            <button id="" type="button" class="btn btn-danger btn-sm  cancelOrderBtn">
                                <i class="fas far fa-trash-alt fa-lg" style="margin-right: 5px"
                                    id="cancelOrderBtn"></i><?= T('cancel_order_btn') ?>
                            </button>
                        <?php endif; ?>

                        <button id="printChef" type="button" class="btn btn-warning btn-sm printChef">
                            <i class="fas fa-print fa-1_5x" style="margin-right: 5px"></i><?= T('print_chef_btn') ?>
                        </button>
                        <button type="button" class="btn btn-info btn-sm reprintAllBtn">
                            <i class="fas fa-print fa-1_5x" style="margin-right: 5px"></i><?= T('reprint_all_btn') ?>
                        </button>
                        <button table_id="" ordere_id="" type="button" class="btn btn-success btn-sm  printClient">
                            <i class="fas fa-print fa-1_5x" style="margin-right: 5px"></i><?= T('print_client_btn') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php";  
    ?>
    <!--Start Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script language="JavaScript" src="js/ajaxCheckoutHistory.js?v=<?= filemtime('js/ajaxCheckoutHistory.js') ?>">
    </script>
</body>

</html>