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

<body id="printer Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('printer_mgmt') ?></h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif"></div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div id="readTableDiv">
                <table id="readTable" class='table hover' style='width:100%;'>
                    <thead class="thead-light">
                        <tr>
                             <th data-priority="2"><?= T('printer_action') ?></th>
                            <th data-priority="0"><?= T('printer_name') ?></th>
                            <th data-priority="1"><?= T('printer_ip') ?></th>
                            <th data-priority="6"><?= T('printer_port') ?></th>
                            <th data-priority="4"><?= T('printer_proto') ?></th>
                            <th data-priority="5"><?= T('printer_labelsize') ?></th>
                            <th data-priority="3"><?= T('printer_date') ?></th>                    
                           
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--END ALL OBJECTS DIV-->

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="d-none mt-3">
            <div class="row mb-5">
                <button id="showAllObjectsButton" class="btn btn-info mr-auto mr-3"><?= T('printer_view') ?></button>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" id="id" name="id" class="d-none">
                </div>

                <div class="form-group row">
                    <label for="username" class="col-sm-2 col-form-label"><?= T('printer_name') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="printerName" name="printerName"
                            placeholder=<?= T('printer_name') ?> validation="NOTEMPTY">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label"><?= T('printer_ip') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="printerIP" name="printerIP" placeholder=<?= T('printer_ip') ?>
                            validation="NOTEMPTY,IP">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="familyName" class="col-sm-2 col-form-label"><?= T('printer_port') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="printerPort" name="printerPort" placeholder=<?= T('printer_port') ?>
                            validation="NOTEMPTY,TRIM,NUMBERS" value="9100">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label"><?= T('printer_proto') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="printerProtocole" name="printerProtocole"
                            placeholder=<?= T('printer_proto') ?> validation="NOTEMPTY,TRIM,MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label"><?= T('printer_labelsize') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="labelSize" name="labelSize" 
                        placeholder=<?= T('printer_labelsize') ?> validation="NOTEMPTY,TRIM,MIXED">
                    </div>
                </div>

                <div class="form-group row mt-5 mb-3">
                    <button type="submit" class="btn btn-success d-none mr-auto" id="addObjectButton">
                        <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('printer_add') ?></button>
                    <button class="btn btn-success d-none mr-auto" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('printer_edit') ?></button>
                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->
        <!--Start Prepare Categories Div-->
        <div id="categoriesDiv" class="text-center d-none">
            <div class="welcome-user">
                <h3><?= T('printer_categories') ?></h3>
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
    <script src="js/ajaxPrinter.js?v=<?= filemtime('js/ajaxPrinter.js') ?>"></script>

    <style>
        .printer-led {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ccc;
            border: 1px solid #888;
            vertical-align: middle;
        }
        .printer-led.green { background: #28a745; }
        .printer-led.red { background: #dc3545; }
    </style>
</body>

</html>