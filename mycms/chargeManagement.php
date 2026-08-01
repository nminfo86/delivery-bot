<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once("php/Charge.php");
require_once("php/JsonCharge.php");
require_once("php/Type_Charge.php");
require_once("php/JsonType_Charge.php");
confirmLoggedIn();
accessControl("admin,checkout");
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->
<?php
$typeCharge = JsonType_Charge::getAllTypeCharge($_SESSION['company_id'], false);

function fillTypeCharges($typeCharge)
{
    echo "<select validation='SELECTED' name='typeCharge_id' id='typeCharge_id' placeholder='" . T('type_expense') . "' class='form-control'>";
    echo "<option value = 'null' selected = 'selected'>" . T('type_expense') . "</option>";
    if ($typeCharge != '') {
        foreach ((array) $typeCharge as $i => $typeCharge) {
            echo "<option value ='" . $typeCharge[Type_Charge::$col_id] . "'>" . $typeCharge[Type_Charge::$col_typeCharge] . "</option>";
        }
    }
    echo "</select>";
}
?>

<body id="Expenses Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">
    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="container">
        <div class="panel-title text-center">
            <h3><?= T('expenses_title') ?>: <span id="dateTextTitle"></span></h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif"></div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div class="row mb-3 filterOptions d-none">
                <div class="col col-lg-5 col-md-5 col-sm-5 col-xs-5 pr-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="start_date" placeholder=""
                            value="<?php echo $_SESSION["role"] == Config::$roleCheckout ? date("d-m-Y") : date("01-m-Y"); ?>">
                    </div>
                </div>
                <div class="col col-lg-5 col-md-5 col-sm-4 col-xs-5 pr-0 pl-1">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-info text-white" id="basic-addon1"><i
                                    class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-sm" id="end_date" placeholder=""
                            value="<?php echo $_SESSION["role"] == Config::$roleCheckout ? date("d-m-Y") : date("t-m-Y"); ?>">
                    </div>
                </div>
                <div class="col col-lg-2 col-md-2 col-sm-3 col-xs-2 pl-1">
                    <button id="filter" class="btn btn-outline-info"><i class="fas fa-check-double"></i></button>
                    <button id="reset" class="btn btn-outline-warning"><i class="fas fa-redo-alt"></i></button>
                </div>
            </div>
            <div id="readTableDiv">
                <table id="readTable" class='table hover' style='width:100%;'>
                    <thead class="thead-dark">
                        <tr>
                            <th data-priority="3"><?= T('action_label') ?></th>
                            <th data-priority="0"><?= T('expense_label') ?></th>
                            <th data-priority="2"><?= T('date_label') ?></th>
                            <th data-priority="1"><?= T('amount_label') ?></th>
                            <th data-priority="5"><?= T('from_cashier_label') ?></th>
                            <th data-priority="6"><?= T('obs_label') ?></th>
                            <th data-priority="4"><?= T('created_label') ?></th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--END ALL OBJECTS DIV-->

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="d-none mt-3">
            <div class="row mb-5">
                <button id="showAllObjectsButton" class="btn btn-info mr-auto mr-3"><?= T('view_expenses_btn') ?></button>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" id="id" name="id" class="d-none">
                </div>
                <div class="form-group row custom-form-group">
                    <label for="typeCharge_id" class="col-sm-2 col-form-label"><?= T('expense_label') ?></label>
                    <div class="col-sm-10" style="width: 200px;">
                        <?php fillTypeCharges($typeCharge); ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="dateTime" class="col-sm-2 col-form-label"><?= T('date_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" value="<?php echo date("d-m-Y"); ?>" id="dateTime" name="dateTime"
                            class="form-control" placeholder="<?= T('date_placeholder') ?>" validation="NOTEMPTY"
                            <?php echo $_SESSION["role"] == Config::$roleCheckout ?  " readonly" : "" ?>>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="amount" class="col-sm-2 col-form-label"><?= T('amount_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="amount" name="amount" placeholder="<?= T('amount_placeholder'). ": 400, 4.5, .." ?>"
                            validation="NOTEMPTY,PRICE">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="observation" class="col-sm-2 col-form-label"><?= T('observation_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="observation" name="observation" placeholder=""
                            validation="">
                    </div>
                </div>
                <!-- if role is checkout we do not show the Decaissement input 
                and we force it to 1 in jsonCharge -->
                <div class="form-group row custom-form-group
                    <?php echo $_SESSION["role"] == Config::$roleCheckout ?  " d-none" : "" ?> ">
                    <div class=" col-sm-2"><?= T('from_cashier_label') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox pl-4 mt-1">
                                <input id="decaise" type="checkbox" name="decaise" class="form-controle" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <button type="submit" class="btn btn-success d-none mr-auto ml-3" id="addObjectButton">
                        <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_expense_btn') ?></button>
                    <button class="btn btn-success d-none mr-auto ml-3" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_expense_btn') ?></button>
                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->

    </section>

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/ajaxCharge.js?v=<?= filemtime('js/ajaxCharge.js') ?>"></script>
</body>

</html>