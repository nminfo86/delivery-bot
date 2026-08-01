<?php
require_once 'php/functions.php';
require_once 'php/JsonCompany.php';
require_once 'php/JsonTable.php';
require_once 'php/init.php';



$tables = JsonTable::getAllTables(False);
// Fetch company info (replace 6 with your company ID if needed)
$companyArr = JsonCompany::getCompanyById(6, false);
$company = $companyArr[0];
$companyLogo = !empty($company['logo']) ? $company['logo'] : 'mycms/images/misc/eatsmartly_logo_transp.png'; // fallback if no logo
?>
<!-- Set a global variable for language translation to use every where in JS files -->
<script>
    //All this data are initialized in init.php
    var cmsCurrency = "<?= Config::$cmsCurrency ?>"; //fr, en, ar
    var JsCmsLanguage = "<?= Config::$cmsLanguage ?>"; //fr, en, ar

    if (typeof window.JsTranslations === "undefined" || window.JsTranslations === null) {
        window.JsTranslations = <?= json_encode(Config::$langStrings, JSON_UNESCAPED_UNICODE) ?>;
    }
</script>

<html>
<?php include "includes/head.php"; ?>
<!-- head -->


<body id="tv-body">
    <audio id="mySound" class="d-none">
        <source src="sounds/sound3-3.mp3" type="audio/mp3">
    </audio>
    <section id="">
        <div class="clock-center">

            <div class="company-logo mb-3">
                <img src="<?= htmlspecialchars(resolveMediaUrl($companyLogo)) ?>" alt="Company Logo">
            </div>
            <div class="tables-select col col-8 col-md-2">
                <?php fillTablesCms($tables) ?>
            </div>
            <button type="button" id="testBtn" class="btn btn-outline-dark" >activate.</button>
        </div>

        <div class="row d-none tv-cols">

            <div class="col col-lg-8 col-12">

                <div id="tv-new-orders-div">
                    <h1 class='pop-outin'></h1>
                    <h2 class="lineUp"></h2>
                </div>
                <!-- <h1>Table-01</h1>
                <h2>Tacos Poulet XXL</h2> -->
            </div>
            <div class="col col-lg-4 col-12">
                <div class="company-logo text-center mb-3">
                    <img src="<?= htmlspecialchars(resolveMediaUrl($companyLogo)) ?>" alt="Company Logo">
                </div>
                <div id="tv-old-orders-div" class="">
                    <!-- <div>
                        <h2>Emporter M20</h2>
                        <h4>Pizza Boisee xxl</h4>
                    </div> -->
                </div>
            </div>

        </div>
    </section>
    <?php include "includes/leg.php"; ?>
    <script language="JavaScript" src="js/tv.js?v=<?= filemtime('js/tv.js') ?>"></script>


</body>

</html>