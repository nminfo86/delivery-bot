<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once(__DIR__ . "/php/Config.php");
confirmLoggedIn();
accessControl("superAdmin,admin");



$companies = JsonCompany::getAllCompanies(false);

?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Pizza Variants" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('pizza_var_title') ?></h3>
        </div>
        <div id="formDiv" class="mt-3" style="margin-left:5%;">
            <article>
                <?= T('pizza_var_desc') ?>
                <ul>
                    <li><b>1.</b> <?= T('pizza_var_cond1') ?></li>
                    <b style="color: #17a2b8;">exemple </b>: <span style="color: #17a2b8;"><?= T('pizza_var_ex1') ?></span>
                    <li><b>2.</b> <?= T('pizza_var_cond2') ?></li>
                    <b style="color: #17a2b8;">exemple </b>: <span style="color: #17a2b8;"><?= T('pizza_var_ex2') ?></span>
                    <li><b>3.</b> <?= T('pizza_var_cond3') ?> </li>
                    <li><b>4.</b> <?= T('pizza_var_cond4') ?></li>
                </ul>
                <?= T('pizza_var_delete_info') ?>
            </article>

            <div class="col my-1">
                <?php fillCompanies($companies) ?>
            </div>
            <form>
                <div class="form-row align-items-center">

                    <div class="col my-1">
                        <input type="text" class="form-control" readonly id="1_4_Pizza" name="category_id"
                            category_id="">
                    </div>
                    <div class="col-auto my-1">
                        <button class="btn btn-info generate"><i class="fa fa-redo fa-lg"
                                style="margin-right: 5px"></i><?= T('pizza_var_generate_1_4') ?></button>
                    </div>
                    <div class="col-auto my-1">
                        <button class="btn btn-danger delete"><i class="far fa-trash-alt fa-lg"
                                style="margin-right: 5px"></i><?= T('pizza_var_delete_1_4') ?></button>
                    </div>
                </div>
            </form>
            <form>
                <div class="form-row align-items-center">
                    <div class="col my-1">
                        <input type="text" class="form-control" readonly id="1_2_Pizza" name="category_id"
                            category_id="">
                    </div>
                    <div class="col-auto my-1">
                        <button class="btn btn-info generate"><i class="fa fa-redo fa-lg"
                                style="margin-right: 5px"></i><?= T('pizza_var_generate_1_2') ?></button>
                    </div>
                    <div class="col-auto my-1">
                        <button class="btn btn-danger delete"><i class="far fa-trash-alt fa-lg"
                                style="margin-right: 5px"></i><?= T('pizza_var_delete_1_2') ?></button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php"; 
    ?>
    <!--Start Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/pizzaVariants.js?v=<?= filemtime('js/pizzaVariants.js') ?>"></script>
</body>

</html>