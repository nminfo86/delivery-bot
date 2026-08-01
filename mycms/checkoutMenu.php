<?php
require_once(__DIR__ . "/php/functions.php");
require_once(__DIR__ . "/php/JsonUser.php");
require_once(__DIR__ . "/php/JsonTable.php");
require_once(__DIR__ . "/php/User.php");
require_once(__DIR__ . "/php/Vat.php");
require_once(__DIR__ . "/php/JsonVat.php");
require_once(__DIR__ . "/php/JsonDashBoard.php");
require_once(__DIR__ . "/php/JsonCategory_Attribute.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("checkout,waiter");

if (isset($_SESSION["user_id"])) {
    $user = JsonUser::getUserById($_SESSION["user_id"], FALSE);
}
$tables = JsonTable::getAllTables(False);

if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
}
// unset($_SESSION["ordere_id"]);
$categories = JsonCategory::getAllCategories($company_id, false);


if (Config::$vatEnabled):
    $vats = JsonVat::getAllVats(false);
endif;

//Fill categories in top Menu
function fillAllCategoriesMenu($categories)
{
    foreach ((array)$categories as $i => $category) {

        if ($i == 0) {
            echo "<div class='active mb-2 flex-fill' categoryId=" . $category[Category::$col_id] . ">" . shrinkCategoryNames($category[Category::$col_category]) . "</div>";
        } else {
            echo "<div categoryId=" . $category[Category::$col_id] . " class='mb-2 flex-fill'>" . shrinkCategoryNames($category[Category::$col_category]) . "</div>";
        }
    }
}

//Create div for each category and fill articles in it
function fillArticlesOfCategoryMenu($company_id, $categories)
{
    foreach ((array)$categories as $i => $category) {

        if ($i == 0) {
            echo "<div categoryId='" . $category[Category::$col_id] . "'
            class='row row-cols-2 row-cols-lg-4 row-cols-md-4 row-cols-sm-4 row-cols-xs-2'>";
            // echo "<div categoryId='" . $category[Category::$col_id] . "'
            // class='row row-cols-2 row-cols-lg-4 row-cols-md-3 row-cols-sm-4 row-cols-xs-2' style='overflow-y: auto;
            // position: relative; height: 750px;'>";
        } else {

            echo "<div categoryId='" . $category[Category::$col_id] . "'
            class='row row-cols-2 row-cols-lg-4 row-cols-md-4 row-cols-sm-4 row-cols-xs-2 d-none'>";
            // echo "<div categoryId='" . $category[Category::$col_id] . "'
            // class='row row-cols-2 row-cols-lg-4 row-cols-md-3 row-cols-sm-4 row-cols-xs-2 d-none' style='overflow-y: auto;
            // position: relative; height: 750px;'>";
        }
        fillArticlesOfCategory($company_id, $category[Category::$col_id]);

        echo  "</div>";
    }
}

//Fill Articles in category div
function fillArticlesOfCategory($company_id, $category_id)
{
    $objects = JsonObject::getAllObjectsByCategoryId($company_id, $category_id, false);

    foreach ((array)$objects as $i => $object) {

        //Get attributes an their values with prices
        $attributeAndAttributeValues = JsonCategory_Attribute::getAttributesAndAttributeValuesAndPriceOfObject($object[Objet::$col_category_id], $object[Objet::$col_id], false);

        echo "<div class='col mb-3 cardDiv' 
                object_id=" . $object[Objet::$col_id]
            . " category_id=" . $object[Objet::$col_category_id]
            . " object_price=" . $object[Objet::$col_basePrice]
            . " is_supplement =" . $object[Category::$col_supplement]
            . " accept_supplement =" . $object[Category::$col_acceptSupplement] .
            ">";
        echo "<div class='card border-radion-0'>";

        // Check if article has background image
        if (!empty($object[Media::$col_media]) && $object[Media::$col_media] !== null) {
            // Has image - use background image with footer
            echo "<div class='card-bg' style='background-image: url(" . resolveMediaUrl($object[Media::$col_media]) . ");'></div>";
            echo "<div class='card-footer text-center'>" . $object[Objet::$col_title] . "</div>";
        } else {
            // No image - use category color with centered white text
            $categoryColor = isset($object[Category::$col_color]) ? $object[Category::$col_color] : '#2c3e50';
            echo "<div class='card-bg-no-image' style='background-color: " . $categoryColor . ";'>";
            echo "<span '>" . $object[Objet::$col_title] . "</span>";
            echo "</div>";
        }

        echo "</div>";
        //Fill atributes an their values with prices
        echo "<div class='attribute_values d-none' >";
        fillAttributeAndAttributeValues($attributeAndAttributeValues);
        echo "</div>";
        echo "</div>";
    }
}

//This function fill attributes and their values ob object under in cardDiv
function fillAttributeAndAttributeValues($attributeAndAttributeValues)
{

    foreach ((array) $attributeAndAttributeValues as $i => $data) {

        // fill the first attribute and the first attributeValue in Div
        if ($i == 0) {
            echo "<div class='attributeValuesRadioGroup'>";
            echo "<div class='page-header' attribute_id=" . $data[Attribute_Value::$col_attribute_id] . ">";
            echo "<h4>" . $data[CmsAttribute::$col_attribute] . "</h4>";
            echo "</div>";
            echo "<label>" . $data[Attribute_Value::$col_attributeValue] .
                "<input type='radio' checked name='attributeValue_id'  price = " . $data[Price::$col_price] . " value=" . $data[Attribute_Value::$col_id] . ">" .
                "</label>";
        } else {
            // IF is not the first array, we test whether the attribute is same , if it is we fill only their children(attributeValues)
            if ($attributeAndAttributeValues[$i][Attribute_Value::$col_attribute_id] == $attributeAndAttributeValues[$i - 1][Attribute_Value::$col_attribute_id]) {
                echo "<label>" . $data[Attribute_Value::$col_attributeValue] .
                    "<input type='radio' name='attributeValue_id' price = " . $data[Price::$col_price] . " value=" . $data[Attribute_Value::$col_id] . ">" .
                    "</label>";
            } else {
                // IF it is an other attribute we fill it and it's first children (attributeValue)
                echo "<div class='attributeValuesRadioGroup'>";
                echo "<div class='page-header' attribute_id=" . $data[Attribute_Value::$col_attribute_id] . ">";
                echo "<h4>" . $data[CmsAttribute::$col_attribute] . "</h4>";
                echo "</div>";
                echo "<label>" . $data[Attribute_Value::$col_attributeValue] .
                    "<input type='radio' name='attributeValue_id' price = " . $data[Price::$col_price] . " value=" . $data[Attribute_Value::$col_id] . ">" .
                    "</label>";
            }
        }                    // IF it is the last
        if ($i == (sizeof((array) $attributeAndAttributeValues) - 1)) {
            echo "</div>";
        }
    }
}

//This function fill VATs as radio buttons
function fillVats($vats)
{
    foreach ((array) $vats as $i => $vat) {

        if ($i == 0) {
            echo "<div class='attributeValuesRadioGroup'>";
            echo "<div class='page-header'>";
            echo "<h4>" . T('vat') . "</h4>";
            echo "</div>";
            echo "<label>" . $vat[Vat::$col_rate] . "%" .
                "<input type='radio' checked name='vat_id' value=" . $vat[Vat::$col_id] . ">" .
                "</label>";
        } else {
            echo "<label>" .  $vat[Vat::$col_rate] . "%" .
                "<input type='radio' name='vat_id' value=" . $vat[Vat::$col_id] . ">" .
                "</label>";
        }

        // Close div after last item
        if ($i == (sizeof((array) $vats) - 1)) {
            echo "</div>";
        }
    }
}

?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Checkout Menu" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->
    <section id="containerMenu">

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>

        <!-- create div for vats  -->
        <div class="vatDiv mb-15 d-none">
            <?php fillVats($vats); ?>
        </div>
        <div id="allOrdersDiv">
            <div class="row">
                <div id="menuDiv" class="col-xl-8 col-lg-7 col-md-12 col-sm-12 col-xs-col-12">
                    <div id="categoriesMenu" class="d-flex flex-row mb-3">
                        <?php fillAllCategoriesMenu($categories); ?>
                    </div>

                    <div id="articlesMenu" class="mt-2">
                        <?php fillArticlesOfCategoryMenu($company_id, $categories); ?>
                    </div>
                </div>
                <div id="orderDetail" class="col-xl-4 col-lg-5 col-md-12 col-sm-12 col-xs-12">
                    <table id="subOrderstable" class='table table-hover'
                        ordere_id="<?php echo (isset($_SESSION["ordere_id"]) ? $_SESSION["ordere_id"] : '') ?>">
                        <tr>
                            <th><?= T('ch_menu_article') ?></th>
                            <th><?= T('ch_menu_qty') ?></th>
                            <th><?= T('ch_menu_price') ?></th>
                            <th><?= T('ch_menu_msg') ?></th>
                        </tr>
                    </table>
                    <h4></h4>
                    <div class="cart-summary">

                        <form id="orderForm" autocomplete="off">
                            <div class="validation-div hide-div">
                            </div>
                            <input class="d-none" name="id" value="">
                            <input class="d-none" name="place" value="<?php echo Config::$orderPlaceOnTable ?>">
                            <input class="d-none" name="table_id" value="NULL" tablecode="NULL">
                            <input class="d-none" name="company_id" value="<?php echo $_SESSION['company_id'] ?>">

                            <!-- set input valid to 1 to validate ordere directly from here
                            see updateOrdere in JsonOrder.php -->
                            <input class="d-none" name="valid" value="1">

                            <div class="tableDiv mb-15">
                                <?php fillTablesCms($tables); ?>
                            </div>
                    </div>
                    </form>

                    <div class="btn-toolbar justify-content-between mb-3">

                        <?php if (Config::$vatEnabled): ?>
                            <button type="button" class=" border-radion-0 btn btn-success addVat mb-3">
                                <i class="fas fa-receipt" style="margin-right: 5px"></i><?= T('vat') ?>
                            </button>
                        <?php endif; ?>

                        <button type="button" class=" border-radion-0 btn btn-info  printChefAndClient mb-3">
                            <i class="fas fa-print" style="margin-right: 5px"></i><?= T('ch_menu_validate_client') ?>
                        </button>
                        <button type="button" class=" border-radion-0 btn btn-warning printChefOnly mb-3">
                            <i class="fas fa-print " style="margin-right: 5px"></i><?= T('ch_menu_validate') ?>
                        </button>
                        <button type="button" class=" border-radion-0 btn btn-danger cancelOrdere mb-3">
                            <i class="far far fa-trash-alt" style="margin-right: 5px">
                            </i><?= T('ch_menu_cancel') ?>
                        </button>
                    </div>
                </div>


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
    <script src="js/ajaxCheckoutMenu.js?v=<?= filemtime('js/ajaxCheckoutMenu.js') ?>"></script>
</body>

</html>