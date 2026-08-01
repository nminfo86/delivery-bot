/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var uploaderAlert = $("#uploaderAlert");
var optionsAlert = $("#optionsAlert");
var userAlert = $("#userAlert");
var searchForm = $("#searchForm");
var objectForm = $("#objectForm");
var uploadImgForm = $("#uploadImgForm");
var adminForm = $("#adminForm");

var readTable = $('#readTable');

 // Directory Picker State
    let dirPickerCurrentPath = 'ROOT';
    const $dirPickerModal = $('#dirPickerModal');
    const $dirPickerList = $('#dirPickerList');
    const $dirPickerCurrentPathElem = $('#dirPickerCurrentPath');
    const $dirPickerSelectBtn = $('#dirPickerSelectBtn');
//*********************** ************** ********

$(document).ready(function () {

    $(document).on('click', '#showCreateDivButton', function () {
        prepareToAddObject()
    });
    $(document).on('click', '#showAllObjectsButton', function () {
        prepareToViewAllObjects();
    });

    $("#searchInput").keyup(function () {
        if ($("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()));
            }
        }
    });

    $("#searchButton").click(function () {
        if (!$("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()));
            }
        }
    });

    $(document).on('click', '.edit-object', function () {
        var id = $(this).attr("id");
        fillObject(id);
    });

    $(document).on('click', '.generateRandomCode', function (event) {
        event.preventDefault();
        $("#carryCode").val(Math.floor(Math.random() * (9999 - 1000)) + 1000);
    });

    $(document).on('click', '#companyCover', function () {
        $("#logo").prop('checked', false);
        $("#company-media").attr('data-type', 'cover');
    });

    $(document).on('click', '#logo', function () {
        $("#companyCover").prop('checked', false);
        $("#company-media").attr('data-type', 'logo');
    });

    $(document).on('click', '.delete-media', function () {
        var id = $(this).closest(".media").attr("id");
        var dataType = $(this).closest(".media").attr("data-type");

        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteMedia(id, dataType);
        }
    });
    //company form submit
    objectForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            createObject();
        }
    });
    //adminForm submit
    adminForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(adminForm)) {
            createAdmin();
        }
    });

    $(document).on('click', '.delete-object', function () {
        var id = $(this).attr("id");
        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteObject(id, $(this).parents('tr'))
        }
    });

    $(document).on('click', '#editObjectButton', function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            var id = objectForm.find("#id").val();
            updateObject(id);
        }
    });

    // Handle optionForm submit
    $("#optionForm").submit(function (event) {
        event.preventDefault();
        var companyId = objectForm.find("#id").val();
        var printChef = $("#printChef").val();
        var printClient = $("#printClient").val();
        var printArabicRecipe = $("#printArabicRecipe").val();
        var orderCapability = $("#orderCapability").val();
        var cmsCurrency = $("#cmsCurrency").val();
        var cmsLanguage = $("#cmsLanguage").val();
         var backupBasePath = $("#backupBasePath").val();

        $.ajax({
            url: "php/JsonLicence.php",
            type: "POST",
            dataType: dataType,
            data: {
                function: "updateLicenceOptions",
                company_id: companyId,
                printChef: printChef,
                printClient: printClient,
                printArabicRecipe: printArabicRecipe,
                orderCapability: orderCapability,
                cmsCurrency: cmsCurrency,
                cmsLanguage: cmsLanguage,
                backupBasePath: backupBasePath
            },
            beforeSend: function () {
                $("#loadingImage3").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "s") {
                    showAlertSuccess(optionsAlert, JsTranslations.licence_options_updated);
                } else {
                    showAlertFailed(optionsAlert, data.message);
                }
                $("#loadingImage3").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(optionsAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage3").addClass("d-none");
            }
        });
    });

    $(document).on('click', '#editAdminButton', function (event) {
        event.preventDefault();
        if (validateForm(adminForm)) {
            updateAdmin();
        }
    });

    $(document).on('click', '#deleteAdminButton', function (event) {
        event.preventDefault();
        if (validateForm(adminForm)) {
            deleteAdmin();
        }
    });

    //*************** START UPLOAD IMAGES FILES ****************//
    //************ UPLOAD IMAGES SUBMIT FORM *****//
    uploadImgForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(uploadImgForm)) {
            uploadImg();
            uploadImgForm[0].reset();
        }
    });



    //if user is superAdmin, show all companies 
    if (isSuperAdmin()) {
        search("");

        //if user is not superAdmin, show only his company
    } else {
        var companyId = $("#sessionCompanyId").val();
        if (companyId) {
            fillObject(companyId);
            loadLicenceOptions(companyId);
        }
    }
    //****************** FUNCTIONS *********************//

    // Helper: check if user is superAdmin
    function isSuperAdmin() {
        return $("#sessionRole").val() === "superAdmin";
    }

    function search(query) {
        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=searchCompany&search=" + query,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");

                readTable.DataTable().clear().draw();
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    } else {
                        readTable.DataTable({
                            language: dataTableLanguage, //defined in global.js
                            responsive: true,
                            destroy: true,
                            "dom": "<'row flex-nowrap'"
                                + "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'>"
                                + "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>"
                                + "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                                + ">rtp",
                        }).clear().draw();
                    }
                } else {
                    readTable.DataTable({
                        language: dataTableLanguage, //defined in global.js
                        responsive: true,
                        destroy: true,
                        "dom": "<'row flex-nowrap'"
                            + "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'>"
                            + "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>"
                            + "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                            + ">rtp",
                        "order": [4, 'desc'],

                        "data": data,
                        "columns": [
                            {
                                "data": "id",
                                render: function (data, type, row) {
                                    return (
                                        "<div class='btn-group' role='group'>" +
                                        "<button class='btn btn-info mr-2 edit-object' id=" + row.id + " role=" + row.role + "> <i class='fas fa-edit'></i></button > " +
                                        "<button class='btn btn-danger delete-object' id=" + row.id + "><i class='fas fa-trash'></i></button >" +
                                        "</div>");
                                },

                            },
                            { "data": "companyName" },
                            { "data": "companyDescription" },
                            { "data": "phone" },
                            { "data": "email" },
                            {
                                "data": 'updateDate',
                                "className": 'dt-nowrap',
                                render: function (data, type, row) {
                                    return moment(row.updateDate).format("DD-MM-YYYY HH:mm");
                                }
                            },
                            
                        ],
                    });

                }
                // Only append Add button if user is superAdmin
                // if (isSuperAdmin()) {
                $(".dTButton").append("<button id='showCreateDivButton' class='btn btn-sm btn-outline-success '><i class ='fas fa-plus fa-1_5x'></i></button>");
                // }
                // // Hide all delete buttons if not superAdmin
                // if (!isSuperAdmin()) {
                //     $(".delete-object").hide();
                // }
                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillObject(id) {
        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getCompanyById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {

                    objectForm.find("#id").val(data[0].id);
                    objectForm.find("#companyName").val(data[0].companyName);
                    objectForm.find("#companyDescription").val(data[0].companyDescription);
                    objectForm.find("#address").val(data[0].address);
                    objectForm.find("#phone").val(data[0].phone);
                    objectForm.find("#email").val(data[0].email);
                    objectForm.find("#gps").val(data[0].gps);
                    objectForm.find("#carryCode").val(data[0].carryCode);

                    if ((data[0].logo) !== null) {
                        $("#showLogoDiv").append(
                            "<div class = 'media'  data-type='logo' id=" + data[0].id + ">" +
                            "<div class = 'media-left'>" +
                            "<img src='" + resolveMediaUrlJs(data[0].logo) + "' alt='" + data[0].companyName + "' class='img-fluid img-thumbnail'>" +
                            "</div>" +
                            "<div class = 'media-body'>" +
                            "<h4 class = 'media-heading d-none d-sm-table-cell'>" + "logo" + "</h4>" +
                            "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                            "</div>" +
                            "</div>"
                        );
                    }
                    if ((data[0].companyCover) !== null) {
                        $("#showCoverDiv").append(
                            "<div class = 'media' data-type='cover' id=" + data[0].id + ">" +
                            "<div class = 'media-left'>" +
                            "<img src='" + resolveMediaUrlJs(data[0].companyCover) + "' alt='" + data[0].companyName + "' class='img-fluid img-thumbnail'>" +
                            "</div>" +
                            "<div class = 'media-body'>" +
                            "<h4 class = 'media-heading d-none d-sm-table-cell'>" + "cover" + "</h4>" +
                            "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                            "</div>" +
                            "</div>"
                        );
                    }
                    prepareToEditObject();
                    loadLicenceOptions(id);
                    fillAdmin(data[0].id);
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }
    // creat function
    function createObject() {
        objectForm.find("#id").val('0');

        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=createCompany&" + objectForm.serialize(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, JsTranslations.msgObjectExists);
                    } else {
                        showAlertFailed(objectAlert, data.message);
                    }
                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_added.replace("{object}", data[0].companyName));
                    objectForm.find("#id").val(data[0].id);

                    $("#adminDiv").removeClass("d-none");
                    $("#addAdminButton").removeClass("d-none");
                    $("#uploaderDiv").removeClass("d-none");
                    $("#showMediaDiv").removeClass("d-none");
                    search("");
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }

        });
    }

    function updateObject(id) {
        //        +($("#prepare").is(":checked")?"":"&prepare=0")
        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateCompany&" + objectForm.serialize(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, msgObjectExists);
                    } else {
                        showAlertFailed(objectAlert, data.message);
                    }

                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_updated.replace("{object}", data[0].companyName));
                    search("");
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }

        });
    }

    // @param tr is the table row to be deleted 
    function deleteObject(id, tr) {
        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteCompany&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {
                    //remove the row that has been deleted
                    readTable.DataTable().row(tr).remove().draw(false)

                    showAlertSuccess(objectAlert, JsTranslations.msgObjectDelete);
                }
                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function createAdmin() {
        adminForm.find("#idAdmin").val('0');

        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=create&" + adminForm.serialize() + "&company_id=" + objectForm.find("#id").val(),
            beforeSend: function () {
                $("#loadingImage3").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(userAlert, JsTranslations.msgUserExist);
                    } else {
                        if (data.message === licence_not_created) {
                            showAlertFailed(userAlert, JsTranslations.msgUserLicenceNotCreated);
                        } else {
                            if (data.message === user_define_password) {
                                showAlertFailed(userAlert, JsTranslations.user_password_error);
                            } else {
                                showAlertFailed(userAlert, data.message);
                            }
                        }
                    }
                } else {

                    showAlertSuccess(userAlert, JsTranslations.msg_object_added.replace("{object}", data[0].username));
                    adminForm.find("#idAdmin").val(data[0].id);

                    $("#addAdminButton").addClass("d-none");
                    $("#editAdminButton").removeClass("d-none");
                    $("#deleteAdminButton").removeClass("d-none");
                }
                $("#loadingImage3").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(userAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage3").addClass("d-none");
            }

        });
    }

    function fillAdmin(company_id) {
        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAdminUserOfCompany&company_id=" + company_id,
            beforeSend: function () {
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {

                        $("#addAdminButton").removeClass("d-none");
                        $("#editAdminButton").addClass("d-none");
                        $("#deleteAdminButton").addClass("d-none");
                        adminForm[0].reset();
                    } else {
                        showAlertFailed(objectAlert, data.message);
                    }
                } else {
                    adminForm.find("#idAdmin").val(data[0].id);
                    adminForm.find("#username").val(data[0].username);
                    adminForm.find("#password").val("NULL");
                    adminForm.find("#name").val(data[0].name);
                    adminForm.find("#familyName").val(data[0].familyName);
                    adminForm.find("#email").val(data[0].email);
                    adminForm.find("#role_id").val(data[0].role_id);
                    //Show edit and delete buttons
                    $("#addAdminButton").addClass("d-none");
                    $("#editAdminButton").removeClass("d-none");
                    $("#deleteAdminButton").removeClass("d-none");
                }
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function updateAdmin() {
        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=update&" + adminForm.serialize() + "&company_id=" + objectForm.find("#id").val(),
            beforeSend: function () {
                $("#loadingImage3").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(userAlert, msgUserExist);
                    } else {
                        if (data.message === licence_limited) {
                            showAlertFailed(userAlert,  JsTranslations.msgUserLicenceLimited);
                        } else {
                            showAlertFailed(userAlert, data.message);
                        }
                    }
                } else {
                    showAlertSuccess(userAlert, JsTranslations.msg_object_updated.replace("{object}", data[0].username));
                    adminForm.find("#idAdmin").val(data[0].id);
                }
                $("#loadingImage3").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(userAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage3").addClass("d-none");
            }
        });
    }

    function deleteAdmin() {
        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=delete&" + adminForm.serialize(),
            beforeSend: function () {
                $("#loadingImage3").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(userAlert, data.message);
                } else {
                    showAlertSuccess(userAlert, "Utilisateur supprimé ");
                    adminForm[0].reset();
                    $("#addAdminButton").removeClass("d-none");
                    $("#editAdminButton").addClass("d-none");
                    $("#deleteAdminButton").addClass("d-none");
                }
                $("#loadingImage3").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(userAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage3").addClass("d-none");
            }
        });
    }

    // function that upload object images to server
    function uploadImg() {
        var companyId = 0;
        var data_Type = "";
        var formdata = new FormData(uploadImgForm[0]); //form element
        companyId = objectForm.find("#id").val();
        data_Type = uploadImgForm.find("#company-media").attr("data-type");

        formdata.append('function', "uploadMedia");
        formdata.append('id', companyId);
        formdata.append('dataType', data_Type);

        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: formdata,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#loadingImage2").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === have_media) {
                        showAlertFailed(uploaderAlert, JsTranslations.msghaveMedia);
                    } else {
                        showAlertFailed(uploaderAlert, data.message);
                    }
                } else {
                  
                    if (((data[0].logo) !== null) && ($("#showLogoDiv").children().length === 0)) {
                        $("#showLogoDiv").append(
                            "<div class = 'media'  data-type='logo' id=" + data[0].id + ">" +
                            "<div class = 'media-left'>" +
                            "<img src='" + resolveMediaUrlJs(data[0].logo) + "' alt='" + data[0].companyName + "' class='img-fluid img-thumbnail'>" +
                            "</div>" +
                            "<div class = 'media-body'>" +
                            "<h4 class = 'media-heading d-none d-sm-table-cell'>" + "logo" + "</h4>" +
                            "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                            "</div>" +
                            "</div>"
                        );
                    }
                    if (((data[0].companyCover) !== null) && ($("#showCoverDiv").children().length === 0)) {
                        $("#showCoverDiv").append(
                            "<div class = 'media' data-type='cover' id=" + data[0].id + ">" +
                            "<div class = 'media-left'>" +
                            "<img src='" + resolveMediaUrlJs(data[0].companyCover) + "' alt='" + data[0].companyName + "' class='img-fluid img-thumbnail'>" +
                            "</div>" +
                            "<div class = 'media-body'>" +
                            "<h4 class = 'media-heading d-none d-sm-table-cell'>" + "cover" + "</h4>" +
                            "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                            "</div>" +
                            "</div>"
                        );
                    }
                }
                $("#loadingImage2").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, jqXHR.toString() + " " + exception.toString());
                //                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function deleteMedia(id, data_type) {
        $.ajax({
            url: "php/JsonCompany.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteCompanyMedia&" + "id=" + id + "&dataType=" + data_type,
            beforeSend: function () {

                $("#loadingImage2").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    $(".media[data-type=" + data_type + "]").remove();
                }
                $("#loadingImage2").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function prepareToViewAllObjects() {
        $("#allObjectsDiv").removeClass("d-none");
        $("#formDiv").addClass("d-none");
        $("#adminDiv").addClass("d-none");
        $("#optionsDiv").addClass("d-none");
        $("#uploaderDiv").addClass("d-none");
        $("#showMediaDiv").addClass("d-none");
        $("#showLogoDiv").empty();
        $("#showCoverDiv").empty();
        objectForm[0].reset();
    }

    function prepareToAddObject() {
        $("#formDiv").removeClass("d-none");
        $("#addAdminButton").removeClass("d-none");
        $("#optionsDiv").addClass("d-none");
        $("#editAdminButton").addClass("d-none");
        $("#deleteAdminButton").addClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").removeClass("d-none");
        $("#editObjectButton").addClass("d-none");

    }

    function prepareToEditObject() {
        $("#formDiv").removeClass("d-none");
        $("#adminDiv").removeClass("d-none");
        $("#optionsDiv").removeClass("d-none");
        $("#showMediaDiv").removeClass("d-none");
        $("#uploaderDiv").removeClass("d-none");
        $("#editObjectButton").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").addClass("d-none");

    }

    // Load licence options when needed (e.g., when showing the options form)
    function loadLicenceOptions(companyId) {
        $.ajax({
            url: "php/JsonLicence.php",
            type: "POST",
            dataType: dataType,
            data: { function: "getLicence", company_id: companyId },
            success: function (data) {
                if (data && data[0]) {
                    $("#printChef").val(data[0].printChef == 1 ? "1" : "0");
                    $("#printClient").val(data[0].printClient == 1 ? "1" : "0");
                    $("#printArabicRecipe").val(data[0].printArabicRecipe == 1 ? "1" : "0");
                    $("#orderCapability").val(data[0].orderCapability == 1 ? "1" : "0");
                    $("#cmsCurrency").val(data[0].cmsCurrency);
                    $("#cmsLanguage").val(data[0].cmsLanguage);
                    $("#backupBasePath").val(data[0].backupBasePath);
                }
            }
        });
    }


    //--- START DIRECTORY PICKER LOGIC ---//

    function loadDirectory(path) {
        $.getJSON('php/dirPicker.php', { path: path })
            .done(function (data) {
                if (!data.ok) {
                    alert('Error: ' + data.error);
                    return;
                }
                dirPickerCurrentPath = data.path;
                $dirPickerCurrentPathElem.text(data.path);
                $dirPickerList.empty();
                if (data.dirs.length === 0) {
                    $dirPickerList.append('<div class="list-group-item">No sub-directories found.</div>');
                }
                data.dirs.forEach(function (dir) {
                    const item = $('<a href="#" class="list-group-item list-group-item-action"></a>');
                    item.html('<i class="fa fa-folder" style="margin-right: 10px;"></i> ' + dir.name);
                    item.data('path', dir.path);
                    $dirPickerList.append(item);
                });
            })
            .fail(function () {
                alert('Failed to load directory listing.');
            });
    }

    $('#browseBackupPathBtn').on('click', function () {
        loadDirectory('ROOT');
        $dirPickerModal.modal('show');
    });

    $dirPickerList.on('click', '.list-group-item-action', function (e) {
        e.preventDefault();
        const path = $(this).data('path');
        loadDirectory(path);
    });

    $('#dirPickerUpBtn').on('click', function () {
        if (dirPickerCurrentPath === 'ROOT' || !dirPickerCurrentPath) return;
        // Basic parent directory logic
        let parentPath = dirPickerCurrentPath.replace(/\\/g, '/').replace(/\/$/, '');
        parentPath = parentPath.substring(0, parentPath.lastIndexOf('/'));
        if (parentPath === '' || /^[A-Z]:$/.test(parentPath)) {
             loadDirectory('ROOT');
        } else {
             loadDirectory(parentPath);
        }
    });

    $dirPickerSelectBtn.on('click', function () {
        if (dirPickerCurrentPath && dirPickerCurrentPath !== 'ROOT') {
            $('#backupBasePath').val(dirPickerCurrentPath);
            $dirPickerModal.modal('hide');
        }
    });

    //--- END DIRECTORY PICKER LOGIC ---//

});