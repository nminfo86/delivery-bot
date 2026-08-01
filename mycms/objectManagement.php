<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("admin");
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>">
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Article Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('articles_heading') ?></h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> <?= T('alert_close') ?></button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif" alt="<?= T('loading_alt') ?>"></div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div id="readTableDiv">
                <table id="readTable" class='table hover' style='width:100%;'>
                    <thead class="thead-light">
                        <tr>
                            <th data-priority="2"><?= T('table_action') ?></th>
                            <th data-priority="0"><?= T('table_article') ?></th>
                            <th data-priority="1"><?= T('table_category') ?></th>
                            <th data-priority="3"><?= T('table_available') ?></th>
                            <th data-priority="4"><?= T('table_company') ?></th>
                            <th data-priority="5"><?= T('table_date') ?></th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--END ALL OBJECTS DIV-->

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="d-none mt-3">
            <div class="row mb-5">
                <button id="showAllObjectsButton" class="btn btn-info mr-auto mr-3"><?= T('show_articles_btn') ?></button>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" value="0" id="id" name="id" class="d-none">
                </div>
                <div class="form-group row">
                    <label for="title" class="col-sm-2 col-form-label"><?= T('article_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="title" name="title" placeholder="<?= T('article_placeholder') ?>"
                            validation="NOTEMPTY">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label"><?= T('description_label') ?></label>
                    <div class="col-sm-10">
                        <textarea id="description" class="form-control" rows="5" placeholder="<?= T('description_placeholder') ?>"
                            name="description" validation=""></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="basePrice" class="col-sm-2 col-form-label"><?= T('base_price_label') ?></label>
                    <div class="col-sm-10">
                        <input id="basePrice" class="form-control" placeholder="<?= T('base_price_placeholder') ?>" name="basePrice"
                            validation="NOTEMPTY,PRICE">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="baseCost" class="col-sm-2 col-form-label"><?= T('base_cost_label') ?></label>
                    <div class="col-sm-10">
                        <input id="baseCost" class="form-control" placeholder="<?= T('base_cost_placeholder') ?>" name="baseCost"
                            validation="PRICE">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="observation" class="col-sm-2 col-form-label"><?= T('observation_label') ?></label>
                    <div class="col-sm-10">
                        <input id="observation" class="form-control" placeholder="<?= T('observation_placeholder') ?>" name="observation"
                            validation="">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="category_id" class="col-sm-2 col-form-label"><?= T('category_label') ?></label>
                    <div class="col-sm-10">
                        <select class="form-control" name="category_id" id="category_id">
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2"><?= T('available_label') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox" style="padding-left: 5px;">
                                <input id="objAvailable" type="checkbox" name="objAvailable" class="form-controle"
                                    value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <button type="submit" class="btn btn-success d-none mr-auto" id="addObjectButton">
                        <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_btn') ?></button>
                    <button type="submit" class="btn btn-success d-none mr-auto" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_btn') ?></button>
                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->

        <!--START PRICE MANAGEMENT DIV-->
        <div id="priceDiv" class="text-center d-none">
            <div class="welcome-user">
                <h3><?= T('price_heading') ?></h3>
            </div>
            <div id="priceAlert" class="alert alert-dismissable d-none resize-div">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> <?= T('price_alert_close') ?></button>
            </div>
            <form id="priceForm" class="mt-3">
                <div class="validation-div text-left hide-div">
                </div>
                <div class="row">
                    <div class="col-lg-3 col-sm-4 form-group">
                        <select id="attribute_values" class="form-control" validation="SELECTED"
                            placeholder="<?= T('attribute_select_placeholder') ?>">
                            <!--<option value="" disabled selected>Select Attribute</option>-->
                            <option value="0" selected="selected"><?= T('attribute_option_choose') ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-sm-2 form-group">
                        <input id="price" class="form-control" placeholder="<?= T('price_placeholder') ?>" name="price"
                            validation="NOTEMPTY,PRICE" data-toggle="tooltip" title="<?= T('price_placeholder') ?>">
                    </div>
                    <div class="col-lg-3 col-sm-2 form-group">
                        <input id="cost" class="form-control" placeholder="<?= T('cost_placeholder') ?>" name="cost"
                            validation="PRICE" data-toggle="tooltip" title="<?= T('cost_placeholder') ?>">
                    </div>
                    <div id="priceButtons" class="col-lg-3 col-sm-4 form-group" style="margin-top: 5px;">
                        <button class="btn btn-sm btn-success add-price margin-right-1em">
                            <i class="fas fa-plus fa-lg"></i></button>
                        <button class="btn btn-sm btn-info edit-price margin-right-1em">
                            <i class="fas fa-edit fa-lg"></i></button>
                        <button class="btn btn-sm btn-danger delete-price margin-right-1em">
                            <i class="fas fa-times fa-lg"></i></button>
                    </div>
                </div>
            </form>
        </div>
        <!--END PRICE MANAGEMENT DIV-->

        <!--START UPLOADER DIV-->
        <div id="uploaderDiv" class="resize-div text-center d-none">
            <div class="welcome-user">
                <h3><?= T('media_heading') ?></h3>
            </div>
            <div id="uploaderAlert" class="alert alert-dismissable d-none resize-div">
                <button type="button" class="close" data-dismiss="alert" aria-d-none="true"> <?= T('media_alert_close') ?></button>
            </div>

            <div id="mediaTypesDiv" class="pl-5">
                <div class="form-check form-check-inline largeCheckBox">
                    <input class="form-check-input" type="radio" value="IMG" name="Image" id="mediaTypeImage" checked>
                    <label class="form-check-label "><?= T('image_label') ?></label>
                </div>
                <div class="form-check form-check-inline largeCheckBox pl-5">
                    <input class="form-check-input" type="radio" value="VID" name="Video" id="mediaTypeVideo">
                    <label class="form-check-label "><?= T('video_label') ?></label>
                </div>
                <!--                    <div class="form-check form-check-inline largeCheckBox pl-5">
                        <input class="form-check-input" type="radio" value="VID" name="Video" id="mediaTypeVideo" >
                        <label class="form-check-label "><?= T('video_label') ?></label>
                    </div>-->

            </div>

            <div id="loadingImage2" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader2.gif" alt="<?= T('upload_alt') ?>">
            </div>
            <div id="uploadImgDiv" class="upload-div">
                <form id="uploadImgForm" action="#" class="uploadImgForm">
                    <div class="validation-div hide-div text-left resize-div">
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-5 col-xs-12">
                            <input type="file" id="object-media" name="object-media" accept="image/*"
                                class="form-control" validation="IMAGE">
                        </div>
                        <div class="form-group col-sm-5 col-xs-12">
                            <input type="text" class="form-control" name="mediaDescription" id="mediaDescription"
                                placeholder="<?= T('media_description_placeholder') ?>" validation="SPECIAL-MIXED,TRIM">
                        </div>
                        <div class="form-group col-sm-2 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-upload fa-lg"
                                    style="margin-right: 5px"></i><?= T('upload_btn') ?></button>
                        </div>
                    </div>
                </form>
            </div>
            <!--                <div id="uploadVidDiv" class="upload-div d-none">
                    <form id="uploadVidForm" action="#" class="uploadVidForm">
                        <div class="validation-div hide-div text-left resize-div">
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-5 col-xs-12">
                                <input type="text" class="form-control input-sm" name="objectCover" id="video-media" placeholder="YouTube video id" validation="NOTEMPTY,MIXED">
                            </div>
                            <div class="form-group col-sm-5 col-xs-12">
                                <input type="text" class="form-control input-sm" name="mediaDescription" id="video-description" placeholder="<?= T('media_description_placeholder') ?>" validation="SPECIAL-MIXED,TRIM">
                            </div>
                            <div class="col-sm-2 col-xs-12">
                                <button type="submit" class="btn btn-sm btn-info "><i class="fa fa-upload fa-lg" style="margin-right: 5px"></i><?= T('upload_btn') ?></button>
                            </div>
                        </div>
                    </form>
                </div>-->
        </div>
        <!--END UPLOADER DIV-->

        <!--START SHOW MEDIA DIV-->
        <div id="showMediaDiv" class="row d-none">

            <div id="showImgDiv" class="col col-md-6 col-sm-6 col-xs-12">

            </div>
            <div id="showVidDiv" class="col col-md-6 col-sm-6 col-xs-12">

            </div>
        </div>
        <!--END SHOW MEDIA DIV-->
    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php"; 
    ?>
    <!--Start Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/ajaxObject.js?v=<?= filemtime('js/ajaxObject.js') ?>"></script>
</body>

</html>