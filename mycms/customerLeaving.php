
<?php
require_once (__DIR__ . "/php/functions.php");
require_once (__DIR__ . "/php/JsonUser.php");
require_once (__DIR__ . "/php/JsonTable.php");
require_once (__DIR__ . "/php/User.php");
confirmLoggedIn();
//accessControl("waiter");

if (isset($_SESSION["user_id"])) {
    $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
}
$tables = JsonTable::getAllTables(False);
function fillTablesWaiter($tables) {


    foreach ((array) $tables as $i => $table) {
        
                            echo"<div class='col-6 col-md-3 col-sm-4 col-xs-6'>";
                                echo"<div class='circle 'id='".$table[Table::$col_id]."'>";
                                    echo"<a href='#' ><h2><small>".$table[Table::$col_tableName]."</small>"
                                            . "<p>".$table[Table::$col_tableCode]."</p></h2></a>";
                                echo"</div>";
                            echo"</div>";  
                                     
    }

}
?>
<html>
    <?php include "includes/head.php"; ?> <!-- head -->

    <body id="Customer leaving panel">

        <!--Start Header-->
        <?php include "includes/header.php"; ?> <!-- header -->
        <!--End Header-->
        <section class="container-lg">

            <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
                <button  type= "button"  class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
            </div>
            <a href="waiterPanel.php"  style="margin-left: auto;" ><button  type="button" class="btn btn-success btn-sm btn-block"  >
                                <i class="fas fa-walking" style="margin-right: 5px"></i>Valider commandes</button></a>
            <div id="allOrdersDiv">
                <div class= "row">
                    <?php fillTablesWaiter($tables)?>
<!--                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-01</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-02</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle ">
                            <a href="#"><h2><small>Table-03</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-04</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-04</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-04</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-04</small><p>065781</p></h2></a>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="circle">
                            <a href="#"><h2><small>Table-04</small><p>065781</p></h2></a>
                        </div>
                    </div>-->


                </div>
            </div>
        </section>

        <!--Start Footer-->
        <?php // include "includes/footer.php"; ?>
        <!--Start Footer-->

        <?php include "includes/leg.php"; ?> <!-- leg-->
        <script language="JavaScript" src="js/ajaxCustomerLeaving.js?v=<?= filemtime('js/ajaxCustomerLeaving.js') ?>"></script>
    </body>

</html>