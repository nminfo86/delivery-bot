<?php

require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once(__DIR__ . "/php/JsonRole.php");
require_once(__DIR__ . "/php/Role.php");
require_once(__DIR__ . "/php/Config.php");
confirmLoggedIn();
accessControl("superAdmin,admin");

$adminRoleID = JsonRole::getRoleByRole(Config::$roleAdmin, false)[0][Role::$col_id];
$userRole = isset($_SESSION["role"]) ? $_SESSION["role"] : ""
?>
<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>" ?>
<?php include "includes/head.php"; ?>
<!-- head -->

<body id="Company Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('company_title') ?> </h3>
        </div>

        <div id="objectAlert" class="alert alert-dismissable d-none text-center resize-div">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"> &times;</button>
        </div>
        <!--loading image-->
        <div id="loadingImage" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader.gif"></div>

        <!--START ALL OBJECTS DIV-->

        <!-- if role is superAdmin we create the allObjectDiv -->
        <?php if ($userRole === Config::$roleSuperAdmin) { ?>

            <div id="allObjectsDiv" class="mt-3">

                <div id="readTableDiv">
                    <table id="readTable" class='table hover' style='width:100%;'>
                        <thead class="thead-light">
                            <tr>
                                <th data-priority="2">Action</th>
                                <th data-priority="0"><?= T('company_label') ?></th>
                                <th data-priority="1"><?= T('desc_label') ?></th>
                                <th data-priority="3"><?= T('phone_label') ?></th>
                                <th data-priority="4"><?= T('email_label') ?></th>
                                <th data-priority="5">Date</th>
                                
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!--END ALL OBJECTS DIV-->
        <?php }  ?>

        <!--START FORM CREATE OBJECT DIV-->
        <div id="formDiv" class="mt-3<?php echo ($userRole === Config::$roleAdmin) ? '' : ' d-none'; ?>">
            <div class="row mb-5">
                <?php if ($userRole == Config::$roleSuperAdmin): ?>
                    <button id="showAllObjectsButton" class="btn btn-info ml-auto mr-3"><?= T('view_companies_btn') ?></button>
                <?php endif; ?>
            </div>
            <form id="objectForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" id="id" name="id" class="d-none">
                </div>
                <div class="form-group row">
                    <label for="companyName" class="col-sm-2 col-form-label"><?= T('company_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="companyName" name="companyName"
                            placeholder="<?= T('company_label') ?>" validation="NOTEMPTY">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="companyDescription" class="col-sm-2 col-form-label"><?= T('desc_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="companyDescription" name="companyDescription"
                            placeholder="<?= T('desc_label') ?>" validation="NOTEMPTY">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="address" class="col-sm-2 col-form-label"><?= T('address_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="address" name="address" placeholder="<?= T('address_label') ?>"
                            validation="NOTEMPTY">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="phone" class="col-sm-2 col-form-label"><?= T('phone_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="<?= T('phone_label') ?>"
                            validation="NOTEMPTY,PHONE">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label"><?= T('email_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="email" name="email" placeholder="<?= T('email_label') ?>"
                            validation="EMAIL">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="gps" class="col-sm-2 col-form-label"><?= T('gps_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="gps" name="gps" placeholder="<?= T('gps_label') ?>"
                            validation="TRIM,SPECIAL-MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="carryCode" class="col-sm-2 col-form-label"><?= T('take_away_label') ?></label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" readonly id="carryCode" name="carryCode"
                            placeholder="<?= T('take_away_label') ?>" validation="NOTEMPTY,NUMBERS">
                    </div>
                    <div class="col-sm-4 ">
                        <button class="btn btn-info generateRandomCode"><i class="fa fa-redo fa-lg"
                                style="margin-right: 5px"></i><?= T('generate_btn') ?></button>
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <?php if ($userRole == Config::$roleSuperAdmin): ?>

                        <button type="submit" class="btn btn-success d-none mr-auto" id="addObjectButton">
                            <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_btn') ?></button>

                    <?php endif; ?>

                    <button class="btn btn-success mr-auto<?php echo ($userRole === Config::$roleAdmin) ? '' : ' d-none'; ?>" id="editObjectButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_btn') ?></button>

                </div>
            </form>
        </div>
        <!--END FORM CREATE OBJECT DIV-->
   
        <!--Start Company Options Div-->
        <div id="optionsDiv" class="text-center d-none">
            <div class="welcome-user mb-3">
                <h3><?= T('options_title') ?></h3>
            </div>
            <div id="optionsAlert" class="alert alert-dismissable d-none resize-div">
                <button type="button" class="close" data-dismiss="alert" aria-d-none="true"> &times;</button>
            </div>
            <div id="loadingImage3" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader2.gif">
            </div>
            <form id="optionForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('print_chef_label') ?></label>
                    <div class="col-sm-10">
                        <select id="printChef" name="printChef" class="form-control">
                            <option value="1"><?= T('yes')?></option>
                            <option value="0"><?= T('no')?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('print_client_label') ?></label>
                    <div class="col-sm-10">
                        <select id="printClient" name="printClient" class="form-control">
                            <option value="1"><?= T('yes')?></option>
                            <option value="0"><?= T('no')?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('print_arabicRecipe_label') ?></label>
                    <div class="col-sm-10">
                        <select id="printArabicRecipe" name="printArabicRecipe" class="form-control">
                            <option value="1"><?= T('yes')?></option>
                            <option value="0"><?= T('no')?></option>
                        </select>
                    </div>
                </div>
                <?php //if ($userRole == Config::$roleSuperAdmin): ?>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('order_capability_label') ?></label>
                    <div class="col-sm-10">
                        <select id="orderCapability" name="orderCapability" class="form-control">
                            <option value="1"><?= T('yes')?></option>
                            <option value="0"><?= T('no')?></option>
                        </select>
                    </div>
                </div>
                <?php //endif; ?>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('currency_label') ?></label>
                    <div class="col-sm-10">
                        <select id="cmsCurrency" name="cmsCurrency" class="form-control">
                            <?php foreach (Config::$currencies as $code => $name): ?>
                                <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('language_label') ?></label>
                    <div class="col-sm-10">
                        <select id="cmsLanguage" name="cmsLanguage" class="form-control">
                            <option value="en">English</option>
                            <option value="fr">Francais</option>   
                            <option value="ar">العربية</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label"><?= T('backup_base_path_label') ?></label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="backupBasePath"
                                   name="backupBasePath"
                                   placeholder="<?= T('backup_base_path_ph') ?>"
                                   validation="TRIM"
                                   readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-secondary" id="browseBackupPathBtn">
                                    <i class="fa fa-folder-open" style="margin-right: 5px;"></i> <?= T('browse') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row mt-5 mb-3">
                    <button class="btn btn-info mr-auto" id="editOptionsButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_btn') ?></button>

                </div>
            </form>
        </div>
        <!--End Company Admin user creation Div-->

        <!-- Directory Picker Modal -->
        <div class="modal fade" id="dirPickerModal" tabindex="-1" role="dialog" aria-labelledby="dirPickerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dirPickerModalLabel"><?= T('select_folder_title') ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted"><strong><?= T('current_path') ?>:</strong> <code id="dirPickerCurrentPath"></code></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="dirPickerUpBtn">
                                <i class="fa fa-arrow-up" style="margin-right: 5px;"></i> <?= T('up_one_level') ?>
                            </button>
                        </div>
                        <div id="dirPickerList" class="list-group" style="max-height: 50vh; overflow-y: auto;">
                            <!-- Directory list will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= T('cancel') ?></button>
                        <button type="button" class="btn btn-primary" id="dirPickerSelectBtn"><?= T('select_this_folder') ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!--START UPLOADER DIV-->
        <div id="uploaderDiv" class="resize-div text-center<?php echo ($userRole === Config::$roleAdmin) ? '' : ' d-none'; ?>">
            <div class="welcome-user">
                <h3><?= T('media_uploader_title') ?></h3>
            </div>
            <div id="uploaderAlert" class="alert alert-dismissable d-none resize-div">
                <button type="button" class="close" data-dismiss="alert" aria-d-none="true"> &times;</button>
            </div>
            <div id="mediaTypesDiv" class="pl-5">
                <div class="form-check form-check-inline largeCheckBox">
                    <input class="form-check-input" type="radio" value="IMG" name="logo" id="logo" checked>
                    <label class="form-check-label "><?= T('logo_radio') ?></label>
                </div>
                <div class="form-check form-check-inline largeCheckBox pl-5">
                    <input class="form-check-input" type="radio" value="IMG" name="companyCover" id="companyCover">
                    <label class="form-check-label "><?= T('cover_radio') ?></label>
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
                            <input type="file" id="company-media" name="company-media" accept="image/*" data-type="logo"
                                class="form-control" validation="IMAGE">
                        </div>
                        <div class="col-sm-5 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-upload fa-lg"
                                    style="margin-right: 5px"></i><?= T('upload_btn') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!--END UPLOADER DIV-->

        <!--START SHOW MEDIA DIV-->
        <div id="showMediaDiv" class="row<?php echo ($userRole === Config::$roleAdmin) ? '' : ' d-none'; ?>">

            <div id="showLogoDiv" class="col col-lg-3 col-md-3 col-sm-4 col-xs-12">
            </div>
            <div id="showCoverDiv" class="col col-lg-3 col-md-3 col-sm-4 col-xs-12">
            </div>
        </div>
        <!--END SHOW MEDIA DIV-->

        <!--Start Company Admin user creation Div-->
        <div id="adminDiv" class="text-center<?php echo ($userRole === Config::$roleAdmin) ? '' : ' d-none'; ?>">
            <div class="welcome-user">
                <h3><?= T('admin_account_title') ?></h3>
            </div>
            <div id="userAlert" class="alert alert-dismissable d-none resize-div">
                <button type="button" class="close" data-dismiss="alert" aria-d-none="true"> &times;</button>
            </div>
            <div id="loadingImage3" class="d-none" style="text-align:center"><img src="images/misc/ajax-loader2.gif">
            </div>
            <form id="adminForm">
                <div class="validation-div hide-div">
                </div>
                <div class="form-group">
                    <input type="text" id="idAdmin" name="id" class="d-none">
                </div>

                <div class="form-group row">
                    <label for="username" class="col-sm-2 col-form-label"><?= T('username_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="<?= T('username_label') ?>" validation="NOTEMPTY,TRIM,SPECIAL-MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="password" class="col-sm-2 col-form-label"><?= T('password_label') ?></label>
                    <div class="col-sm-10">
                        <input type="password" value="NULL" class="form-control" id="password" name="password"
                            placeholder="<?= T('password_label') ?>" validation="NOTEMPTY,TRIM">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label"><?= T('name_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" placeholder="<?= T('name_label') ?>"
                            validation="NOTEMPTY,TRIM,SPECIAL-MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="familyName" class="col-sm-2 col-form-label"><?= T('family_name_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="familyName" name="familyName" placeholder="<?= T('family_name_label') ?>"
                            validation="NOTEMPTY,TRIM,SPECIAL-MIXED">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label"><?= T('admin_email_label') ?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="emailAdmin" name="email" placeholder="<?= T('admin_email_label') ?>"
                            validation="TRIM,EMAIL">
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="role_id" name="role_id" class="d-none" value="<?php echo $adminRoleID; ?>">
                </div>
                <div class="form-group">
                    <input type="text" id="printer_id" name="printer_id" class="d-none" value="null">
                </div>
                <div class="form-group row mt-5 mb-3">
                    <?php if ($userRole == Config::$roleSuperAdmin): ?>
                        <button type="submit" class="btn btn-success mr-auto d-none" id="addAdminButton">
                            <i class="fas fa-plus" style="margin-right: 5px"></i><?= T('add_btn') ?></button>
                    <?php endif; ?>

                    <button class="btn btn-info mr-auto<?php echo ($userRole === Config::$roleAdmin) ? '' : ' d-none'; ?>" id="editAdminButton">
                        <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('edit_btn') ?></button>

                    <?php if ($userRole == Config::$roleSuperAdmin): ?>
                        <button class="btn btn-danger mr-auto d-none" id="deleteAdminButton">
                            <i class="fas fa-edit" style="margin-right: 5px"></i><?= T('delete_btn') ?></button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <!--End Company Admin user creation Div-->

    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php"; 
    ?>
    <!--Start Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg-->
    <script src="js/ajaxSuperAdminPanel.js?v=<?= filemtime('js/ajaxSuperAdminPanel.js') ?>"></script>
</body>

</html>