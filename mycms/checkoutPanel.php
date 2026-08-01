<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/JsonUser.php");
require_once(__DIR__ . "/php/JsonTable.php");
require_once(__DIR__ . "/php/User.php");
require_once(__DIR__ . "/php/Charge.php");
require_once(__DIR__ . "/php/JsonDashBoard.php");
require_once(__DIR__ . "/php/JsonReport.php");
require_once(__DIR__ . "/php/JsonVat.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("checkout");

// if (isset($_SESSION["user_id"])) {
//     $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
// }
$userRole = isset($_SESSION["role"]) ? $_SESSION["role"] : "";

$tables = JsonTable::getAllTables(False);

$todaySales = (float)(JsonDashBoard::getTotalSales(date("Y-m-d"), date("Y-m-d")) ?? 0.0);
$todayVats = (float)(JsonReport::getTotalVatByDate(date("Y-m-d"), date("Y-m-d"), false) ?? 0.0);
$todayRealSales = $todaySales + $todayVats; //Real Cash sales inclcuding VAT 


$todayDecaisseCharges = (float)(JsonDashBoard::getTotalDecaisseCharges(date("Y-m-d"), date("Y-m-d"), 1) ?? 0.0);
$caisse = $todayRealSales - $todayDecaisseCharges;

if (Config::$vatEnabled):
    $vats = JsonVat::getAllVats(false);
endif;

//This function fill VATs as radio buttons
function fillVats($vats)
{
    foreach ((array) $vats as $i => $vat) {

        if ($i == 0) {
            echo "<div class='attributeValuesRadioGroup'>";
            echo "<div class='page-header'>";
            echo "<h4>" . T('vat') . "</h4>";
            echo "</div>";
            echo "<label>" . $vat[Vat::$col_rate] . "%" .
                "<input type='radio' checked name='vat_id' value=" . $vat[Vat::$col_id] . ">" .
                "</label>";
        } else {
            echo "<label>" .  $vat[Vat::$col_rate] . "%" .
                "<input type='radio' name='vat_id' value=" . $vat[Vat::$col_id] . ">" .
                "</label>";
        }

        // Close div after last item
        if ($i == (sizeof((array) $vats) - 1)) {
            echo "</div>";
        }
    }
}

?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Checkout Panel" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="container-lg">

        <div class="welcome-user d-none">
            <h3><?= T('pay_orders') ?></h3>
        </div>
        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <div class="alert alert-info d-block text-center caisseAlert" role="alert"
            style="display: table; font-size: 16px;">
            <span">
                <?php echo (T('date_prefix') . date("d-m-Y"));
                ?>
                </span>
                <span id="caisseText" dir="rtl">
                    <?= T('cash') ?>
                    <span dir="ltr"><?= number_format($caisse, 2, ".", " ") ?></span> <?php echo Config::$cmsCurrency ?>
                </span>
                <span id="sailesText" dir="rtl">
                    <?= T('sales') ?>
                    <span dir="ltr"><?php echo number_format($todayRealSales, 2, ".", " ") ?></span> <?php echo Config::$cmsCurrency ?>
                </span>

                <span> <?php echo ("__"); ?> </span>

                <span id="chargesText" dir="rtl">
                    <?php echo T('charges') ?>
                    <span dir="ltr"><?= number_format($todayDecaisseCharges, 2, ".", " ") ?></span> <?php echo Config::$cmsCurrency ?>
                </span>

        </div>

        <!-- create div for vats  -->
        <div class="vatDiv mb-15 d-none">
            <?php fillVats($vats); ?>
        </div>

        <div id="allOrdersDiv">
            <form id="searchForm" class="form-row">
                <div class="validation-div hide-div">
                </div>
                <div class="input-group">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-2">
                        <?php fillTablesCms($tables) ?>
                    </div>
                    <div class="input-group rtl mb-3">
                        <input type="text" class="form-control" id="searchInput" name="startSubOrder"
                            placeholder="<?= T('search_placeholder') ?>" validation="SPECIAL-MIXED">
                        <div class="input-group-append">
                            <div class="input-group-text btn-outline-info">
                                <input id="searchCheckBox" type="checkbox">
                            </div>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-outline-info" type="button" id="searchButton"><?= T('go') ?></button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div id="ordersDiv" class="">

                    <table id="allOrderstable" class='table table-hover'> </table>
                </div>
                <div id="orderDetail" class="col-lg-7 col-md-6 col-sm-12 col-xs-12 d-none mt-xs-3 mt-md-3">
                    <h4 class="mb-3"></h4>
                    <table id="subOrderstable" class='table table-hover'>
                        <tr>
                            <th><?= T('code') ?></th>
                            <th><?= T('article') ?></th>
                            <th><?= T('qty') ?></th>
                            <th><?= T('amount') ?></th>
                            <th><?= T('action') ?></th>
                        </tr>
                    </table>
                    <h3 style="font-size : 1.5rem;"></h3>

                    <div class="btn-toolbar justify-content-between mb-3">

                        <?php if (Config::$vatEnabled): ?>
                            <button type="button" class="btn btn-info btn-sm addVat">
                                <i class="fas fa-receipt" style="margin-right: 5px"></i><?= T('vat') ?>
                            </button>
                        <?php endif; ?>

                        <?php //if ($userRole == Config::$roleAdmin): ?>
                            <button id="" type="button" class="btn btn-danger btn-sm  cancelOrderBtn">
                                <i class="fas far fa-trash-alt fa-lg" style="margin-right: 5px"
                                    id="cancelOrderBtn"></i><?= T('cancel_order_btn') ?>
                            </button>
                        <?php //endif; ?>

                        <button id="" type="button" class="btn btn-warning btn-sm printChef">
                            <i class="fas fa-print fa-lg" style="margin-right: 5px" id="printChef"></i><?= T('validate') ?>
                        </button>
                        <button type="button" class="btn btn-info btn-sm reprintAllBtn">
                            <i class="fas fa-print fa-1_5x" style="margin-right: 5px"></i><?= T('reprint_all_btn') ?>
                        </button>
                        <button table_id="" ordere_id="" type="button" class="btn btn-success btn-sm  payOrderBtn">
                            <i class="fas fa-check fa-lg" style="margin-right: 5px" id="printClient"></i><?= T('pay') ?>
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
    <script src="js/ajaxCheckoutPanel.js?v=<?= filemtime('js/ajaxCheckoutPanel.js') ?>"></script>
</body>

</html>