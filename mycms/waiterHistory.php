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
$categories = JsonCategory::getAllCategoriesByPreparation($_SESSION["company_id"], 0, false);
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>">
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Waiter History" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="container-lg">

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <div id="allOrdersDiv">
            <form id="searchForm" class="form-row">
                <div class="validation-div hide-div">
                </div>
                <div class="input-group">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <?php fillTablesCms($tables) ?>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <?php fillCategories($categories) ?>
                    </div>
                </div>
            </form>
            <div class="row">
                <div id="starteTableDiv" class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-2">
                    <table id="startTable" class='table table-hover'></table>
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
    <script language="JavaScript" src="js/ajaxWaiterHistory.js?v=<?= filemtime('js/ajaxWaiterHistory.js') ?>"></script>
</body>

</html>