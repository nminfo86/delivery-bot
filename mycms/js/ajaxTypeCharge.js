/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var searchForm = $("#searchForm");
var objectForm = $("#objectForm");

var readTable = $('#readTable');


//*********************** ************** ********

$(document).ready(function () {

    $(document).on('click', '#showCreateDivButton', function () {
        prepareToAddObject();
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

    objectForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            createObject();
        }
    });

    $(document).on('click', '.delete-object', function () {
        var id = $(this).attr("id");
        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteObject(id, $(this).parents('tr'));
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
            url: "php/JsonType_Charge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=searchTypeCharge&search=" + query,
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
                        language: dataTableLanguage, //definde in global.js
                        responsive: true,
                        destroy: true,
                         "dom": "<'row flex-nowrap'"
                                + "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'>"
                                + "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>"
                                + "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                                + ">rtp",
                        "order": [0, 'asc'],
                        "data": data,
                        "columns": [
                             {
                                "data": "id",
                                render: function (data, type, row) {
                                    return (
                                        "<div class='btn-group' role='group'>" +
                                        "<button class='btn btn-info mr-2 edit-object' id=" + row.id + "> <i class='fas fa-edit'></i></button > " +
                                        "<button class='btn btn-danger delete-object' id=" + row.id + "><i class='fas fa-trash'></i></button >" +
                                        "</div>");
                                }
                            },
                            { "data": "typeCharge" },
                            {
                                "data": 'creationDate',
                                "className": 'dt-nowrap',
                                render: function (data, type, row) {
                                    return moment(row.creationDate).format("DD-MM-YYYY HH:mm");
                                }
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

    function fillObject(id) {
        $.ajax({
            url: "php/JsonType_Charge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getTypeChargeById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {
                    objectForm.find("#id").val(data[0].id);
                    objectForm.find("#typeCharge").val(data[0].typeCharge);
                    prepareToEditObject();
                }
                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function createObject() {
        objectForm.find("#id").val('0');
        $.ajax({
            url: "php/JsonType_Charge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=createTypeCharge&" + objectForm.serialize(),
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
                    showAlertSuccess(objectAlert, JsTranslations.msg_object_added.replace("{object}", data[0].typeCharge));
                    objectForm.find("#id").val(data[0].id);
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
        $.ajax({
            url: "php/JsonType_Charge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateTypeCharge&" + objectForm.serialize(),
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
                    showAlertSuccess(objectAlert, JsTranslations.msg_object_updated.replace("{object}", data[0].typeCharge));
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
            url: "php/JsonType_Charge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteTypeCharge&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {
                    readTable.DataTable().row(tr).remove().draw(false);
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
        objectForm[0].reset();
    }
    function prepareToAddObject() {
        $("#formDiv").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").removeClass("d-none");
        $("#editObjectButton").addClass("d-none");
    }
    function prepareToEditObject() {
        $("#formDiv").removeClass("d-none");
        $("#editObjectButton").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").addClass("d-none");
    }

});