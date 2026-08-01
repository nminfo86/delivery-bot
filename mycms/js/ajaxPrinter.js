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

var readTable = $('#readTable');


//*********************** ************** ********

$(document).ready(function () {

    $(document).on('click', '#showCreateDivButton', function () {
        prepareToAddObject()
    });
    $(document).on('click', '#showAllObjectsButton', function () {
        prepareToViewAllObjects();
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


    // Move the Check Printer Status button next to the Add button and simplify it (no spinner)
    // Remove previous handler and add new one with loader
    $(document).on('click', '#checkPrinterStatusBtn', function () {
        $("#loadingImage").removeClass("d-none");
        var btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: 'php/JsonPrinter.php',
            type: 'POST',
            dataType: 'json',
            data: { function: 'checkPrintersConnection' },
            success: function (data) {
                if (data.state === 's' && data.results) {
                    var table = $('#readTable').DataTable();
                    var html = '<table class="table table-bordered table-sm"><thead><tr><th>Name</th><th>IP</th><th>Status</th></tr></thead><tbody>';
                    table.rows().every(function () {
                        var rowData = this.data();
                        var printerStatus = data.results.find(function (p) { return p.id == rowData.id; });
                        var statusIcon = '<i class="fas fa-circle" style="color:' + (printerStatus && printerStatus.status === 'connected' ? '#28a745' : '#dc3545') + '"></i>';
                        html += '<tr>' +
                            '<td>' + rowData.printerName + '</td>' +
                            '<td>' + rowData.printerIP + '</td>' +
                            '<td>' + statusIcon + ' ' + (printerStatus ? printerStatus.status : 'unknown') + '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                    swal({
                        title: JsTranslations.modal_title_printer_status,
                        content: $("<div></div>").html(html)[0],
                        icon: false,
                        buttons: {
                            confirm: {
                                text: JsTranslations.ok,
                                className: 'btn btn-primary'
                            }
                        },
                        closeOnClickOutside: true
                    });
                } else {
                    showAlertFailed(objectAlert, JsTranslations.msgCheckPrinterStatus);
                }
            },
            error: function () {
                showAlertFailed(objectAlert, JsTranslations.msgErrorCheckPrinter);
            },
            complete: function () {
                btn.prop('disabled', false);
                $("#loadingImage").addClass("d-none");
            }
        });
    });

    search("");
    //****************** FUNCTIONS *********************//

    function search(query) {
        $.ajax({
            url: "php/JsonPrinter.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=searchPrinter&search=" + query,
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
                                + "<'col-lg-4 col-sm-4 col-xs-4 dTButton '>"
                                + "<'col-lg-6 col-sm-6 col-xs-6 'f>"
                                + "<'col-lg-2 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                                + ">rtp",
                        }).clear().draw();
                    }
                } else {
                    readTable.DataTable({
                        language: dataTableLanguage, //defined in global.js
                        responsive: true,
                        destroy: true,
                        "dom": "<'row flex-nowrap'"
                                + "<'col-lg-4 col-sm-4 col-xs-4 dTButton '>"
                                + "<'col-lg-6 col-sm-6 col-xs-6 'f>"
                                + "<'col-lg-2 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                                + ">rtp",
                        "order": [5, 'desc'],

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
                            {
                                "data": "printerName",
                                "className": 'dt-nowrap',
                            },
                            { "data": "printerIP" },
                            { "data": "printerPort" },
                            { "data": "printerProtocole" },
                            { "data": "labelSize" },
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
                $(".dTButton").empty().append(
                    "<div class='btn-group' role='group'>" +
                    "<button id='showCreateDivButton' class='btn btn-sm btn-outline-success'><i class ='fas fa-plus fa-1_5x'></i></button>" +
                    "<button id='checkPrinterStatusBtn' class='btn btn-sm btn-outline-warning ml-2'  title='" + JsTranslations.printer_test + "'><i class='fas fa-network-wired fa-1_5x'></i></button>" +
                    "</div>"
                );
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
            url: "php/JsonPrinter.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getPrinterById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {


                    objectForm.find("#id").val(data[0].id);
                    objectForm.find("#printerName").val(data[0].printerName);
                    objectForm.find("#printerIP").val(data[0].printerIP);
                    objectForm.find("#printerPort").val(data[0].printerPort);
                    objectForm.find("#printerProtocole").val(data[0].printerProtocole);
                    objectForm.find("#labelSize").val(data[0].labelSize);
                }
                prepareToEditObject();
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
            url: "php/JsonPrinter.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=createPrinter&" + objectForm.serialize(),
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

                    showAlertSuccess(objectAlert,  JsTranslations.msg_object_added.replace("{object}", data[0].printerName));
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
            url: "php/JsonPrinter.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updatePrinter&" + objectForm.serialize(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, msgObjectExists);
                    } else {
                        if (data.message === licence_limited) {
                            showAlertFailed(objectAlert, JsTranslations.msgUserLicenceLimited);
                        } else {
                            showAlertFailed(objectAlert, data.message);
                        }
                    }
                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_updated.replace("{object}", data[0].printerName));

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
            url: "php/JsonPrinter.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deletePrinter&id=" + id,
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
        objectForm[0].reset();
    }
    function prepareToAddObject() {
        $("#formDiv").removeClass("d-none");
        $("#password").val("");
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