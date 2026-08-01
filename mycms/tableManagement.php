<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("superAdmin,admin");
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Table Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('table_mgmt') ?></h3>
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
                            <th data-priority="2"><?= T('action') ?></th>
                            <th data-priority="0"><?= T('table') ?></th>
                            <th data-priority="1"><?= T('code') ?></th>
                            <th data-priority="3"><?= T('date') ?></th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--END ALL OBJECTS DIV-->

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="d-none mt-3">
            <div class="row mb-5">
                <button id="showAllObjectsButton" class="btn btn-info mr-auto mr-3"><?= T('view_tables') ?></button>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group row">
                    <input type="text" id="id" name="id" class="d-none">
                </div>
                <div class="form-group row">
                    <label for="tableName" class="col-sm-2 col-form-label"><?= T('table') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="tableName" name="tableName" placeholder="<?= T('table') ?>"
                            validation="NOTEMPTY,SPECIAL-MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="tableCode" class="col-sm-2 col-form-label"><?= T('code') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="tableCode" name="tableCode" disabled=""
                            placeholder="<?= T('code') ?>" validation="TRIM" style="font-size: 22px; font-weight: 600;">
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <button type="submit" class="btn btn-success d-none mr-auto" id="addObjectButton">
                        <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_btn') ?></button>
                    <button class="btn btn-success d-none mr-auto" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_btn') ?></button>
                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->

    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php"; 
    ?>
    <!--Start Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/ajaxTable.js?v=<?= filemtime('js/ajaxTable.js') ?>"></script>
</body>

</html>