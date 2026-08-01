/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var uploaderAlert = $("#uploaderAlert");
var searchForm = $("#searchForm");
var objectForm = $("#objectForm");
var uploadImgForm = $("#uploadImgForm");
var uploadVidForm = $("#uploadVidForm");

var readTable = $('#readTable');


//*********************** ************** ********

$(document).ready(function () {

    $(document).on('click', '#showCreateDivButton', function () {
        fillRoles();
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

    $(document).on('click', '#categoriesDiv input:checkbox', function () {
        var user_id = objectForm.find("#id").val();
        var category_id = $(this).val();
        var element = $(this);
        //this test is reversed because when clicking on checkbox it's change then we test check status
        if (!$(this).is(":checked")) {
            //this checkbox was checked
            deleteUser_Category(user_id, category_id);
        } else {
            //this checkbox was not checked
            createUser_Category(user_id, category_id, element);
        }
    });
    $(document).on('click', '#objectForm input:checkbox', function () {

        //this test is reversed because when clicking on checkbox it's change then we test check status
        if (!$(this).is(":checked")) {
            //this checkbox was checked
            $(this).val("0");
        } else {
            //this checkbox was not checked
            $(this).val("1");
        }
    });

    $(document).on('click', '.edit-object', function () {
        var id = $(this).attr("id");
        var role = $(this).attr("role");
        fillRoles();
        if (role == roleChef) {
            fillCategories()
            fillObject(id, true);
        } else {
            fillObject(id, false);
        }
    });

    objectForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            createObject();
        }
    });

    $(document).on('click', '.delete-object', function () {
        var id = $(this).attr("id");

        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteObject(id, $(this).parents('tr'))
        }
    });

    $(document).on('click', '#editObjectButton', function () {
        event.preventDefault();
        if (validateForm(objectForm)) {
            var id = objectForm.find("#id").val();
            updateObject(id);
        }
    });

    search("");
    //****************** FUNCTIONS *********************//

    function search(query) {
        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=searchUser&search=" + query,
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
                            language: dataTableLanguage, //definde in global.js
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
                         language: dataTableLanguage, //definde in global.js
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
                            { "data": "username" },
                            { "data": "role" },
                            {
                                "data": null,
                                render: function (data, type, row) {
                                    return row.name + ' ' + row.familyName
                                }

                            },

                            { "data": "companyName" },

                            {
                                "data": 'updateDate',
                                "className": 'dt-nowrap',
                                render: function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        return moment(row.updateDate).format("DD-MM-YYYY HH:mm");
                                    }
                                    // For sorting and type detection, return the raw value
                                    return row.updateDate;
},
                            },
                            
                        ],
                    });

                }
                $(".dTButton").append("<button id='showCreateDivButton' class='btn btn-sm btn-outline-success '><i class ='fas fa-plus fa-1_5x'></i></button>");
                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }
    function fillObject(id, displayCategories) {
        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getUserById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    data[0].connected == 1 ? objectForm.find("#connected").prop('checked', true) : objectForm.find("#connected").prop('checked', false);
                    objectForm.find("#id").val(data[0].id);
                    objectForm.find("#username").val(data[0].username);
                    //                    objectForm.find("#password").val(data[0].password);
                    objectForm.find("#name").val(data[0].name);
                    objectForm.find("#familyName").val(data[0].familyName);
                    objectForm.find("#email").val(data[0].email);
                    objectForm.find("#connected").val(data[0].connected);
                    objectForm.find("#role_id").val(data[0].role_id);
                    objectForm.find("#printer_id").val(data[0].printer_id == null ? "null" : data[0].printer_id);
                }
                fillUserCategory(data[0].id);
                prepareToEditObject(displayCategories);
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillCategories(isRoleChef, object_id) {

        $.ajax({
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            async: false,
            dataType: dataType,
            data: "function=getAllCategoriesByPreparation&preparation=1",
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    $("#categoriesDiv").find(".form-check").remove();
                    for (var i = 0; i < data.length; i++) {
                        $("#categoriesDiv").append(
                            "<div class='form-check form-check-inline largeCheckBox pl-5'>" +
                            "<input class='form-check-input' type='checkbox' value=" + data[i].id + ">" +
                            "<label class='form-check-label '>" + data[i].category + "</label>" +
                            "</div>"
                        );
                    }
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });

    }
    function fillRoles() {
        $.ajax({
            url: "php/JsonRole.php",
            type: "POST",
            dataType: dataType,
            jsonp: false,
            async: false,
            data: "function=getAllRolesExceptAdmin",
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    $("#role_id").find("option").remove();
                    for (var i = 0; i < data.length; i++) {
                        $("#role_id").append("<option value=" + data[i].id + ">" + data[i].role + "</option>");
                    }
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillUserCategory(user_id) {
        $.ajax({
            url: "php/JsonUser_Category.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllCategoriesOfUser&user_id=" + user_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    }
                } else {

                    for (var i = 0; i < data.length; i++) {

                        $("#categoriesDiv input:checkbox[value=" + data[i].category_id + "]").prop('checked', true);
                    }
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function deleteUser_Category(user_id, category_id) {
        $.ajax({
            url: "php/JsonUser_Category.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=delete&user_id=" + user_id + "&category_id=" + category_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msgOperationReussie);
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });

    }

    function createUser_Category(user_id, category_id, element) {
        $.ajax({
            url: "php/JsonUser_Category.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=create&user_id=" + user_id + "&category_id=" + category_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                    element.prop('checked', false);
                } else {
                    showAlertSuccess(objectAlert, JsTranslations.msgOperationReussie);
                }
                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    // creat function
    function createObject() {
        objectForm.find("#id").val('0');

        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=create&" + objectForm.serialize() + "&connected=" + $("#connected").val(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, JsTranslations.msgUserExist);
                    } else {
                        if (data.message === licence_limited) {
                            showAlertFailed(objectAlert, JsTranslations.msgUserLicenceLimited);
                        } else {
                            showAlertFailed(objectAlert, data.message);
                        }
                    }
                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_added.replace("{object}", data[0].username));
                    objectForm.find("#id").val(data[0].id);
                    if (data[0].role === roleChef) {
                        $("#categoriesDiv").removeClass("d-none");
                        fillCategories();
                    }
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
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=update&" + objectForm.serialize() + "&connected=" + $("#connected").val(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, msgUserExist);
                    } else {
                        if (data.message === licence_limited) {
                            showAlertFailed(objectAlert, msgUserLicenceLimited);
                        } else {
                            showAlertFailed(objectAlert, data.message);
                        }
                    }
                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_updated.replace("{object}", data[0].username));

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

    function deleteObject(id, tr) {

        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=delete&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {
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

    function prepareToViewAllObjects() {
        $("#allObjectsDiv").removeClass("d-none");
        $("#formDiv").addClass("d-none");
        $("#categoriesDiv").addClass("d-none");
        objectForm[0].reset();
    }
    function prepareToAddObject() {
        $("#formDiv").removeClass("d-none");
        $("#password").val("");
        $("#categoriesDiv").addClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").removeClass("d-none");
        $("#editObjectButton").addClass("d-none");
    }
    function prepareToEditObject(displayCategories) {
        $("#formDiv").removeClass("d-none");
        displayCategories ? $("#categoriesDiv").removeClass("d-none") : '';
        $("#editObjectButton").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").addClass("d-none");

    }

});