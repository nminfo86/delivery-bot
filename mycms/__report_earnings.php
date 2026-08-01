<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once("php/JsonCategory.php");

confirmLoggedIn();
accessControl("admin,checkout");
?>

<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>">
<?php include "includes/head.php"; ?>
<!-- head -->
<?php
$categories = JsonCategory::getAllCategories($_SESSION["company_id"], false);
function fillCategories_small($categories)
{

    echo "<select validation='' placeholder='" . T('category_label') . "' class='form-control form-control-sm searcheCategory'>";
    echo "<option value = '0' selected = 'selected'>" . T('category_label') . "</option>";
    $categoryNumber = 0;
    foreach ((array) $categories as $i => $category) {
        echo "<option value ='" . $category[Category::$col_id] . "'>" . $category[Category::$col_category] . "</option>";
        $categoryNumber++;
    }
    echo "</select>";
}
?>

<body id="Expenses by category" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">
    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="Container">
        <div class="panel-title text-center mt-3 mb-4">
            <h3><?= T('sales_category_earnings_total') ?>: <span id="dateTextTitle"></span></h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif"></div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div class="row mb-3 filterOptions">

                <div class="col col-lg-5 col-md-5 col-sm-5 col-xs-5 pr-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="start_date" placeholder=""
                            value="<?php echo  date("01-m-Y"); ?>">
                    </div>
                </div>
                <div class="col col-lg-5 col-md-5 col-sm-5 col-xs-5 pr-0 pl-1">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="end_date" placeholder=""
                            value="<?php echo  date("t-m-Y"); ?>">
                    </div>
                </div>
                <div class="col col-lg-2 col-md-2 col-sm-2 col-xs-2 pl-1">
                    <button id="filter" class="btn btn-outline-info"><i class="fas fa-check-double"></i></button>
                    <button id="reset" class="btn btn-outline-warning" title='<?= T('report_io_reset') ?>'><i class="fas fa-redo-alt"></i></button>
                </div>
            </div>
            <div id="readTableDiv">
                <table id="readTable" class='table hover' style='width:100%;'>
                    <thead class="thead-dark">
                        <tr>
                            <th data-priority="6"><?= T('sales_category_cat') ?></th>
                            <th data-priority="0"><?= T('sales_category_art') ?></th>
                            <th data-priority="2"><?= T('sales_category_attr') ?></th>
                            <th data-priority="5"><?= T('sales_category_qty_total') ?></th>
                            <th data-priority="4"><?= T('sales_category_price_total') ?></th>  
                            <th data-priority="3"><?= T('sales_category_cost_total') ?></th>                        
                            <th data-priority="1"><?= T('sales_category_earnings_total') ?></th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
        <!--END ALL OBJECTS DIV-->

    </section>

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/__report_earnings.js?v=<?= filemtime('js/__report_earnings.js') ?>"></script>
</body>

</html>