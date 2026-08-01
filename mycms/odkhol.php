<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/init.php");

verify();

if (loggedIn()) {
    autoRedirect($_SESSION["role"]);
}

?>
<!-- Set a global variable for language translation to use every where in JS files -->
<script>
    var JsCmsLanguage = "<?= Config::$cmsLanguage ?>"; //fr, en, ar
    if (typeof window.JsTranslations === "undefined" || window.JsTranslations === null) {
        window.JsTranslations = <?= json_encode(Config::$langStrings, JSON_UNESCAPED_UNICODE) ?>;
    }
</script>


<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<!-- head -->
<?php include "includes/head.php"; ?>


<body id="Login" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <section class="container">

        <!--Start Header-->
        <!--End Header-->

        <div class="welcome-user mt-3">
            <h1><?php echo T('login') ?></h1>
        </div>

        <div id="myAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif"></div>

        <div id="formDiv" class="resize-div">
            <form id="loginForm" class="form-horizontal">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group row">
                    <label for="username" class="col-sm-3 col-form-label"><?php echo T('username') ?></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="username" id="username"
                            placeholder="<?php echo T('username') ?>" validation="NOTEMPTY,MIXED,TRIM">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="password" class="col-sm-3 col-form-label"><?php echo T('password') ?></label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" name="password" id="password"
                            placeholder="<?php echo T('password') ?>" validation="NOSPACE,NOTEMPTY">
                    </div>
                </div>
                <div class="form-group row">
                    <button type="submit" class=" col-xs-3 btn btn-success ml-auto"><?php echo T('login') ?></button>
                </div>
            </form>
        </div>

        <div class="container">
            <p id="gototop" class="pull-right"><a href="../index.php"><?php echo T('return_home') ?></a></p>
        </div>
        <!--Start Footer -->
        <?php include "includes/footer.php"; ?>
        <!--End Footer -->

    </section><!-- end container -->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/ajaxLogin.js?v=<?= filemtime('js/ajaxLogin.js') ?>"></script>
</body>

</html>