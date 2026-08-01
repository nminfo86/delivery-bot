<head>
    <meta charset="utf-8">
    <title id="headtitle">eatSmartly- </title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
    <link href="_rdf/css/bootstrap.min.css" rel="stylesheet">

    <!-- jqUi Theme css-->
    <link href="_plugins/css/jquery-ui.css" rel="stylesheet">

    <!--Font awesome CSS-->
    <link href="_plugins/css/all.min.css" rel="stylesheet">

    <!--PrintJS CSS-->
    <link href="_plugins/css/print.min.css" rel="stylesheet">

    <!--DataTable CSS-->
    <link href="_plugins/css/datatables.min.css" rel="stylesheet">

    <link href="css/myvalidation.css?v=<?= filemtime('css/myvalidation.css') ?>" rel="stylesheet">

    <link href="css/clock.css?v=<?= filemtime('css/clock.css') ?>" rel="stylesheet">

    <link href="css/cmscss.css?v=<?= filemtime('css/cmscss.css') ?>" rel="stylesheet">

    <?php
    if (Config::$cmsLanguage === 'ar') {
        echo "<link href=\"css/cmscss_rtl.css?v=" . filemtime('css/cmscss_rtl.css') . "\" rel=\"stylesheet\">";
    }
 
    ?>


    <!--<script src="_plugins/js/respond.js"></script>-->
    <!-- Favicon  -->
    <link rel="icon" href="<?= resolveMediaUrl('mycms/images/misc/eatsmartly_logo.png') ?>">
    <!-- Custom fonts for this template-->
    <!-- ********************** Dependences for dashboard *****************************-->


    <!-- Custom styles for this template-->
</head>

<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

?>