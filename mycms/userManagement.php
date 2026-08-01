<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once("php/Printer.php");
require_once("php/JsonPrinter.php");
confirmLoggedIn();
accessControl("admin");
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->
<?php
$printers = JsonPrinter::getAllPrinters(false);

function fillPrinters($printers)
{
    echo "<select validation='SELECTED' name='printer_id' id='printer_id' placeholder='Imprimantes' class='form-control'>";
    echo "<option value = 'null' selected = 'selected'>Imprimantes</option>";
    if ($printers != '') {
        foreach ((array) $printers as $i => $printer) {
            echo "<option value ='" . $printer[Printer::$col_id] . "'>" . $printer[Printer::$col_printerName] . "</option>";
        }
    }
    echo "</select>";
}
?>

<body id="‘User Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('user_mgmt') ?></h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> <?= T('alert_close') ?></button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif" alt="<?= T('loading_alt') ?>"></div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div id="readTableDiv">
                <table id="readTable" class='table hover' style='width:100%;'>
                    <thead class="thead-light">
                        <tr>
                            <th data-priority="2"><?= T('users_table_action') ?></th>
                            <th data-priority="0"><?= T('users_table_user') ?></th>
                            <th data-priority="1"><?= T('users_table_role') ?></th>
                            <th data-priority="3"><?= T('users_table_name') ?></th>
                            <th data-priority="4"><?= T('users_table_company') ?></th>
                            <th data-priority="5"><?= T('users_table_date') ?></th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--END ALL OBJECTS DIV-->

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="d-none mt-3">
            <div class="row mb-5">
                <button id="showAllObjectsButton" class="btn btn-info mr-auto mr-3"><?= T('view_users') ?></button>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" id="id" name="id" class="d-none">
                </div>

                <div class="form-group row custom-form-group">
                    <div class="col-sm-2"><?= T('connected') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox" style="padding-left: 5px;">
                                <input id="connected" type="checkbox" name="connected" class="form-controle" value="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="username" class="col-sm-2 col-form-label"><?= T('username') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="<?= T('username') ?>" validation="NOTEMPTY,TRIM,SPECIAL-MIXED">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="password" class="col-sm-2 col-form-label"><?= T('password') ?></label>
                    <div class="col-sm-10">
                        <input type="password" value="NULL" class="form-control" id="password" name="password"
                            placeholder="<?= T('password') ?>" validation="NOTEMPTY,TRIM">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label"><?= T('name') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" placeholder="<?= T('name') ?>"
                            validation="">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="familyName" class="col-sm-2 col-form-label"><?= T('family_name') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="familyName" name="familyName" placeholder="<?= T('family_name') ?>"
                            validation="">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label"><?= T('email') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="email" name="email" placeholder="<?= T('email') ?>"
                            validation="EMAIL">
                    </div>
                </div>

                <div class="form-group row custom-form-group">
                    <label for="role_id" class="col-sm-2 col-form-label"><?= T('role') ?></label>
                    <div class="col-sm-10" style="width: 200px;">
                        <select class="form-control" name="role_id" id="role_id" validation="SELECTED"
                            placeholder="<?= T('select_role') ?>">
                            <option rivalue="0" selected="selected"><?= T('role') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group row custom-form-group">
                    <label for="display" class="col-sm-2 col-form-label"><?= T('printer') ?></label>
                    <div class="col-sm-10" style="width: 200px;">
                        <?php fillPrinters($printers); ?>
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <button type="submit" class="btn btn-success d-none mr-auto" id="addObjectButton">
                        <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_btn') ?></button>
                    <button class="btn btn-success d-none mr-auto" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit') ?></button>
                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->
        <!--Start Prepare Categories Div-->
        <div id="categoriesDiv" class="text-center d-none">
            <div class="welcome-user">
                <h3><?= T('categories') ?></h3>
            </div>
        </div>
        <!--End attributes Div-->

    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php"; 
    ?>
    <!--Start Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/ajaxUser.js?v=<?= filemtime('js/ajaxUser.js') ?>"></script>
</body>

</html>