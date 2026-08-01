<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once("php/Charge.php");
require_once("php/JsonCharge.php");
require_once("php/Type_Charge.php");
require_once("php/JsonType_Charge.php");
confirmLoggedIn();
accessControl("admin");
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<style>

</style>

<body id="Charges Management">
    
<body id="Incomes & Expenses" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="chefContainer">
        <div class="panel-title text-center mt-3 mb-4">
            <h3><?php echo T('report_io_title')." : " ?><span id="dateTextTitle"></span>
           </h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div class="row mb-3 filterOptions justify-content-center">
                <div class="col col-lg-4 col-md-4 col-sm-4 col-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        
                         <input type="text" class="form-control form-control-sm text-center" id="start_date" placeholder=""
                            value="<?php echo date('1-m-Y')?>">
                    </div>
                </div>
                <div class="col col-lg-4 col-md-4 col-sm-4 col-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm text-center" id="end_date" placeholder=""
                            value="<?php echo  date('t-m-Y'); ?>">
                    </div>
                </div>
                <div class="col col-lg-4 col-md-4 col-sm-6 filterButtons">

                <!-- Caisse button Toggle -->
                    <button id="caisse" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-check-square mr-2 d-none" id="faCheck"></i>
                        <i class="far fa-square mr-2" id="nonFaCheck"></i>
                        <?= T('report_io_cash') ?>
                        <input type="checkbox" value="0" class="d-none" id="caisseCheckBox">
                    </button>
                <!-- Caisse button Toggle -->
                    <button id="filter" class="btn btn-outline-info" title='<?= T('report_io_filter') ?>'><i class="fas fa-check-double"></i></button>
                    <button id="printSuborders" class="btn btn-outline-success" title='<?= T('report_io_print_sales') ?>'><i class="fas fa-print"></i></button>
                    <button id="printCharges" class="btn btn-outline-secondary" title='<?= T('report_io_print_expenses') ?>'><i class="fas fa-print"></i></button>
                    <button id="reset" class="btn btn-outline-warning" title='<?= T('report_io_reset') ?>'><i class="fas fa-redo-alt"></i></button>
                </div>
            </div>
            <div class="row">
                <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                    <table id="sailesTable" class='table' style='width:100%;'>
                        <thead style="background-color: #c3e6cb;">
                            <tr>
                                <th data-priority="0"><?= T('report_io_article') ?></th>
                                <th data-priority="1"><?= T('report_io_qty') ?></th>     
                                <th data-priority="2"><?= T('report_io_amount') ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="col col-lg-6 col-md-6 col-sm-12 col-12">
                    <table id="chargesTable" class='table' style='width:100%;'>
                        <thead style="background-color: #ffeeba;">
                            <tr>
                                <th data-priority="0"><?= T('report_io_expense') ?></th>
                                <th data-priority="4"><?= T('report_io_date') ?></th>
                                <th data-priority="2"><?= T('report_io_amount') ?></th>
                                <th data-priority="5"><?= T('report_io_decaisse') ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
            <div id="diffrence" class="mt-2"></div>
        </div>
        <!--END ALL OBJECTS DIV-->

    </section>

    <?php include "includes/leg.php"; ?>
    <!--leg-->
    <script src="js/__report_io_ajax.js?v=<?= filemtime('js/__report_io_ajax.js') ?>"></script>
</body>

</html>
</body>

</html>