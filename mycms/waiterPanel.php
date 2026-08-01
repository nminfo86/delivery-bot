<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/JsonUser.php");
require_once(__DIR__ . "/php/JsonTable.php");
require_once(__DIR__ . "/php/User.php");
require_once(__DIR__ . "/php/JsonDashBoard.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("waiter");

if (isset($_SESSION["user_id"])) {
    $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
}
$tables = JsonTable::getAllTables(False);
$companies = JsonCompany::getAllCompanies(False)
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>">
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Waiter Panel" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="container-lg">

        <div class="welcome-user d-none">
            <h3><?= T('pay_orders') ?></h3>
            <a href="customerLeaving.php" style="margin-left: auto;" class="d-none">
                <button type="button" class="btn btn-success btn-sm">
                    <i class="fas fa-walking" style="margin-right: 5px"></i><?= T('on_table') ?></button>
            </a>
        </div>
        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <div id="allOrdersDiv">
            <div class="row">
                <div id="ordersDiv" class="">
                    <form id="search" class="form-row">
                        <div class="validation-div hide-div">
                        </div>
                        <div class="col-lg-12 col-md-12 col-md-12 col-xs-12">
                            <?php fillTablesCms($tables) ?>
                        </div>
                        <div class="input-group mb-3 col-sm-8 d-none">
                            <input type="text" class="form-control" id="searchInput" name="order"
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
                    </form>
                    <table id="allOrderstable" class='table table-hover'>

                    </table>
                </div>
                <div id="orderDetail" class="col-lg-7 col-md-8 col-sm-12 col-xs-12 d-none" style="margin-top: 35px;">
                    <h4 class="mb-3"></h4>
                    <table id="subOrderstable" class='table table-hover'>
                        <tr>
                            <th><?= T('article') ?></th>
                            <th><?= T('qty') ?></th>
                            <th><?= T('amount') ?></th>
                            <th><?= T('action') ?></th>
                        </tr>
                    </table>
                    <h3></h3>
                    <div class="btn-toolbar justify-content-between mb-3">
                        <button id="" type="button" class="btn btn-success btn-sm  validOrderBtn">
                            <i class="fas fa-check fa-lg" style="margin-right: 5px" id="validOrderBtn"></i><?= T('validate') ?>
                        </button>
                        <button id="" type="button" class="btn btn-danger btn-sm  cancelOrderBtn">
                            <i class="fas far fa-trash-alt fa-lg" style="margin-right: 5px"
                                id="cancelOrderBtn"></i><?= T('cancel') ?> </button>
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
    <script language="JavaScript" src="js/ajaxWaiterPanel.js?v=<?= filemtime('js/ajaxWaiterPanel.js') ?>"></script>
</body>

</html>