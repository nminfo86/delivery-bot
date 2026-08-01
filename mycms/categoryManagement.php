<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
confirmLoggedIn();
accessControl("admin");
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Category Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('category_management_title') ?></h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif"></div>

        <!--START ALL OBJECTS DIV-->
        <div id="allObjectsDiv" class="mt-3">
            <div id="readTableDiv">
                <table id="readTable" class='table hover' style='width:100%;' dir="<?= $cmsBodyConfig['dir'] ?>">
                    <thead class="thead-light">
                        <tr>
                            <th data-priority="3"><?= T('action_label') ?></th>
                            <th data-priority="0"><?= T('category_label') ?></th>
                            <th data-priority="1"><?= T('color_label') ?></th>
                            <th data-priority="2"><?= T('display_label') ?></th>
                            <th data-priority="4"><?= T('available_label') ?></th>
                            <th data-priority="5"><?= T('company_label') ?></th>
                            <th data-priority="6"><?= T('date_label') ?></th>
                            
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--END ALL OBJECTS DIV-->

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="d-none mt-3 ">
            <div class="row mb-5">
                <button id="showAllObjectsButton" class="btn btn-info mr-auto mr-3"><?= T('view_categories') ?></button>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" id="id" name="id" class="d-none">
                </div>
                <div class="form-group row">
                    <label for="category" class="col-sm-2 col-form-label"><?= T('category_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="category" name="category" placeholder="<?= T('category_label') ?>"
                            validation="NOTEMPTY,SPECIAL-MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2"><?= T('prepare_label') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox" style="padding-left: 5px;">
                                <input id="prepare" type="checkbox" name="prepare" class="form-controle" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2"><?= T('supplement_label') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox" style="padding-left: 5px;">
                                <input id="supplement" type="checkbox" name="supplement" class="form-controle"
                                    value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2"><?= T('accept_supplement_label') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox" style="padding-left: 5px;">
                                <input id="acceptSupplement" type="checkbox" name="acceptSupplement"
                                    class="form-controle" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="display" class="col-sm-2 col-form-label"><?= T('display_label') ?></label>
                    <div class="col-sm-10" style="width: 200px;">
                        <select class="form-control" name="display" id="display" validation="SELECTED"
                            placeholder="<?= T('display_label') ?>">
                            <option value="0" selected="selected"><?= T('display_order_option') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="color" class="col-sm-2 col-form-label"><?= T('color_label') ?></label>
                    <div class="col-sm-10">
                        <input type="hidden" class="form-control" id="color" name="color" value="#2980b9">
                        <div id="colorPalette" class="color-palette">
                            <div class="color-option" data-color="#2c3e50" style="background-color: #2c3e50;" title="Dark Blue"></div>
                            <div class="color-option" data-color="#34495e" style="background-color: #34495e;" title="Dark Gray"></div>
                            <div class="color-option" data-color="#7f8c8d" style="background-color: #7f8c8d;" title="Gray"></div>
                            <div class="color-option" data-color="#95a5a6" style="background-color: #95a5a6;" title="Light Gray"></div>
                            <div class="color-option" data-color="#8e44ad" style="background-color: #8e44ad;" title="Purple"></div>
                            <div class="color-option" data-color="#9b59b6" style="background-color: #9b59b6;" title="Light Purple"></div>
                            <div class="color-option" data-color="#3498db" style="background-color: #3498db;" title="Blue"></div>
                            <div class="color-option" data-color="#2980b9" style="background-color: #2980b9;" title="Dark Blue"></div>
                            <div class="color-option" data-color="#1abc9c" style="background-color: #1abc9c;" title="Turquoise"></div>
                            <div class="color-option" data-color="#16a085" style="background-color: #16a085;" title="Dark Turquoise"></div>
                            <div class="color-option" data-color="#27ae60" style="background-color: #27ae60;" title="Green"></div>
                            <div class="color-option" data-color="#2ecc71" style="background-color: #2ecc71;" title="Light Green"></div>
                            <div class="color-option" data-color="#f39c12" style="background-color: #f39c12;" title="Orange"></div>
                            <div class="color-option" data-color="#e67e22" style="background-color: #e67e22;" title="Dark Orange"></div>
                            <div class="color-option" data-color="#d35400" style="background-color: #d35400;" title="Burnt Orange"></div>
                            <div class="color-option" data-color="#e74c3c" style="background-color: #e74c3c;" title="Red"></div>
                            <div class="color-option" data-color="#c0392b" style="background-color: #c0392b;" title="Dark Red"></div>
                            <div class="color-option" data-color="#922b21" style="background-color: #922b21;" title="Maroon"></div>
                            <div class="color-option" data-color="#8b4513" style="background-color: #8b4513;" title="Saddle Brown"></div>
                            <div class="color-option" data-color="#a0522d" style="background-color: #a0522d;" title="Sienna"></div>
                            <div class="color-option" data-color="#cd853f" style="background-color: #cd853f;" title="Peru"></div>
                            <div class="color-option" data-color="#daa520" style="background-color: #daa520;" title="Goldenrod"></div>
                            <div class="color-option" data-color="#b8860b" style="background-color: #b8860b;" title="Dark Goldenrod"></div>
                            <div class="color-option" data-color="#556b2f" style="background-color: #556b2f;" title="Dark Olive Green"></div>
                            <div class="color-option" data-color="#6b8e23" style="background-color: #6b8e23;" title="Olive Drab"></div>
                            <div class="color-option" data-color="#008b8b" style="background-color: #008b8b;" title="Dark Cyan"></div>
                            <div class="color-option" data-color="#4682b4" style="background-color: #4682b4;" title="Steel Blue"></div>
                            <div class="color-option" data-color="#483d8b" style="background-color: #483d8b;" title="Dark Slate Blue"></div>
                            <div class="color-option" data-color="#8b008b" style="background-color: #8b008b;" title="Dark Magenta"></div>
                            <div class="color-option" data-color="#800080" style="background-color: #800080;" title="Purple"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2"><?= T('available_label') ?></div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <div class="checkbox checkbox-inline largeCheckBox" style="padding-left: 5px;">
                                <input id="available" type="checkbox" name="available" class="form-controle" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <button type="submit" class="btn btn-success d-none mr-auto" id="addObjectButton">
                        <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_button') ?></button>
                    <button class="col-2 btn btn-success d-none mr-auto" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_button') ?></button>
                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->
        <!--Start attributes Div-->
        <div id="attributesDiv" class="text-center d-none ">
            <div class="welcome-user">
                <h3><?= T('attributes_title') ?></h3>
            </div>
        </div>
        <!--End attributes Div-->

        <!--START UPLOADER DIV-->
        <div id="uploaderDiv" class="resize-div text-center d-none">
            <div class="welcome-user">
                <h3><?= T('media_uploader_title') ?></h3>
            </div>
            <div id="uploaderAlert" class="alert alert-dismissable d-none resize-div">
                <button type="button" class="close" data-dismiss="alert" aria-d-none="true"> &times;</button>
            </div>

            <div id="mediaTypesDiv" class="pl-5">
                <div class="form-check form-check-inline largeCheckBox">
                    <input class="form-check-input" type="radio" value="IMG" name="Image" id="mediaTypeImage" checked>
                    <label class="form-check-label "><?= T('image_radio') ?></label>
                </div>
                <div class="form-check form-check-inline largeCheckBox pl-5">
                    <input class="form-check-input" type="radio" value="VID" name="Video" id="mediaTypeVideo">
                    <label class="form-check-label "><?= T('video_radio') ?></label>
                </div>

            </div>

            <div id="loadingImage2" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader2.gif">
            </div>
            <div id="uploadImgDiv" class="upload-div">
                <form id="uploadImgForm" action="#" class="uploadImgForm">
                    <div class="validation-div hide-div text-left resize-div">
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-7 col-xs-12">
                            <input type="file" id="category-media" name="category-media" accept="image/*"
                                class="form-control" validation="IMAGE">
                        </div>
                        <div class="col-sm-5 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-upload fa-lg"
                                    style="margin-right: 5px"></i><?= T('upload_button') ?></button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="uploadVidDiv" class="upload-div d-none">
                <form id="uploadVidForm" action="#" class="uploadVidForm">
                    <div class="validation-div hide-div text-left resize-div">
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-7 col-xs-12">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control input-sm" name="categoryCover" id="video-media"
                                    placeholder="YouTube video id" validation="NOTEMPTY,MIXED">
                            </div>
                        </div>
                        <div class="col-sm-5 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-info "><i class="fa fa-upload fa-lg"
                                    style="margin-right: 5px"></i><?= T('embed_button') ?></button>
                        </div>
                    </div>
                </form>
            </div>
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
    <script src="js/ajaxCategory.js?v=<?= filemtime('js/ajaxCategory.js') ?>"></script>
</body>

</html>