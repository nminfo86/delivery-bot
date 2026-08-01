<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/JsonUser.php");
require_once(__DIR__ . "/php/JsonTable.php");
require_once(__DIR__ . "/php/JsonCategory.php");
require_once(__DIR__ . "/php/User.php");
require_once(__DIR__ . "/php/User_Category.php");
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("chef");

if (isset($_SESSION["user_id"])) {
    $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
}
$tables = JsonTable::getAllTables(False);

$user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;
$categories = JsonCategory::getAllPrepareCategoriesByUserID($user_id, false);
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Chef Panel" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section class="chefContainer">

        <div class="welcome-user d-none">
            <h3>Préparation commandes</h3>
        </div>
        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <div id="allOrdersDiv">
            <form id="searchForm" class="form-row mb-1">
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
                <div id="starteTableDiv" class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mt-2 d-none">
                    <table id="startTable" class='table table-hover'></table>
                </div>

                <br>
                <div id="ReadyTableDiv" class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-1">
                    <table id="readyTable" class='table table-hover rtl'></table>
                </div>
                <div id="rePrintTableDiv" class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mt-2 d-none">
                    <table id="rePrintTable" class='table table-hover '>
                    </table>
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
    <script src="js/ajaxChefPanel.js?v=<?= filemtime('js/ajaxChefPanel.js') ?>"></script>
</body>

</html>