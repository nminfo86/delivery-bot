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
var dataTable;

//*********************** ************** ********

$(document).ready(function () {

    //******* Init jqUi DatePicker element *****

    $("#start_date").datepicker(
        $.extend(
            {
                'dateFormat': 'dd-mm-yy',
                'changeMonth': true,
                'changeYear': true
            }
        ));
    $("#end_date").datepicker(
        $.extend(
            { 'dateFormat': 'dd-mm-yy' },
            { maxDate: 0 }
        ));

    $("#dateTime:not([readonly])").datepicker(
        $.extend(
            { 'dateFormat': 'dd-mm-yy' },
            { maxDate: 0 }
        ));
    //************** */

    $(document).on('click', '#showFilterOptions', function () {
        $(".filterOptions").toggleClass("d-none");
    });

    $(document).on('click', '#showCreateDivButton', function () {
        $("#dateTime").val(moment().format("DD-MM-YYYY"));
        prepareToAddObject();
    });

    $(document).on('click', '#showAllObjectsButton', function () {
        prepareToViewAllObjects();
    });

    $(document).on('click', '#filter', function () {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        if (
            moment(startDate, "DD-MM-YYYY").isSameOrBefore(moment(endDate, "DD-MM-YYYY"))
        ) {
            getAllCharges(startDate, endDate);
        } else {
            swal(JsTranslations.oops, JsTranslations.start_after_end_warning, "warning");
        }

    });
    $(document).on('click', '#reset', function () {
        $("#start_date").val(moment().startOf('month').format("DD-MM-YYYY"));
        $("#end_date").val(moment().endOf('month').format("DD-MM-YYYY"));
        getAllCharges();

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
            deleteObject(id);
        }
    });

    $(document).on('click', '#editObjectButton', function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            var id = objectForm.find("#id").val();
            updateObject(id);
        }
    });


    getAllCharges();
    //****************** FUNCTIONS *********************//

    function getAllCharges(startDateFilter, endDateFilter) {

        //if dates are defined in filter we search with them
        //Otherwise we search for current month
        //for checkout roles we search only for current date charges
        var startDate = startDateFilter == undefined ? moment().startOf('month').format("DD-MM-YYYY") : startDateFilter;
        var endDate = endDateFilter == undefined ? moment().endOf('month').format("DD-MM-YYYY") : endDateFilter;

        if ($("#sessionRole").val() == roleCheckout) {
            startDate = moment().format("DD-MM-YYYY");
            endDate = moment().format("DD-MM-YYYY");
        }

        $.ajax({
            url: "php/JsonCharge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllCharges&startDate=" + startDate + "&endDate=" + endDate,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
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
                                + ">rt" + "<'total-footer-charges'>p",
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
                            + ">rt" + "<'total-footer-charges'>p",
                        "order": [6, 'desc'],

                        "data": data,
                        "columns": [
                            {
                                "data": "id",
                                // "className": 'd-sm-flex',
                                render: function (data, type, row) {
                                    return (
                                        "<div class='btn-group' role='group'>" +
                                        "<button class='btn btn-info mr-2 edit-object' id=" + row.id + " role=" + row.role + "> <i class='fas fa-edit'></i></button > " +
                                        "<button class='btn btn-danger delete-object' id=" + row.id + "><i class='fas fa-trash'></i></button >" +
                                        "</div>");
                                },

                            },
                            { "data": "typeCharge" },
                            {
                                "data": 'dateTime',

                                render: function (data, type, row) {
                                    return moment(row.creationDate).format("DD-MM-YYYY HH:mm");
                                }

                            },
                            {
                                "data": "amount",
                                render: function (data, type, row) {
                                    return formatAmount(data);
                                }
                            },
                            {
                                "data": "decaise",
                                render: function (data, type, row) {
                                    return row.decaise == '1' ? JsTranslations.yes : "";
                                }
                            },
                            {
                                "data": "observation",
                                "width": "15%"
                            },
                            {
                                "data": 'creationDate',
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
                        "footerCallback": function (row, data, start, end, display) {
                            var api = this.api();

                            // Total over all pages
                            total = api.column(3, { search: 'applied' }).data().reduce(function (a, b) {
                                return currency(a).add(b);
                            }, 0);

                            // Update footer
                            $(".total-footer-charges").html(
                                (JsTranslations.report_io_total + " : " + formatAmount(total))
                            );
                        }
                    });

                }
                $(".dTButton").append(
                    "<button id='showCreateDivButton' class='btn btn-sm btn-outline-success mr-1'><i class ='fas fa-plus fa-1_5x'></i></button>");
                if ($("#sessionRole").val() != roleCheckout) {
                    $(".dTButton").append("<button id='showFilterOptions' class='btn btn-sm btn-outline-info'><i class ='fas fa-sliders-h fa-1_5x'></i></button>");
                }

                $("#loadingImage").addClass("d-none");
                updateDateTextTitle();
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }
    function fillObject(id) {
        $.ajax({
            url: "php/JsonCharge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getChargeById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    objectForm.find("#id").val(data[0].id);
                    objectForm.find("#typeCharge_id").val(data[0].typeCharge_id);
                    objectForm.find("#dateTime").attr("value", moment(data[0].dateTime).format("DD-MM-YYYY"));
                    objectForm.find("#amount").val(data[0].amount);
                    objectForm.find("#observation").val(data[0].observation);
                    objectForm.find("#decaise").val(data[0].decaise);
                    data[0].decaise == 1 ? objectForm.find("#decaise").prop('checked', true) : objectForm.find("#decaise").prop('checked', false);
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
            url: "php/JsonCharge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=create&" + objectForm.serialize() + "&decaise=" + $("#decaise").val(),
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

                    showAlertSuccess(objectAlert, JsTranslations.msgExpenseAdded);
                    getAllCharges();
                    objectForm[0].reset();

                    objectForm.find("#id").val(data[0].id);
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
            url: "php/JsonCharge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=update&" + objectForm.serialize() + "&decaise=" + $("#decaise").val(),
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
                    showAlertSuccess(objectAlert, JsTranslations.msgExpenseUpdated);
                    getAllCharges();
                }


                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function deleteObject(id) {

        $.ajax({
            url: "php/JsonCharge.php",
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
                    //remove the row that has been deleted
                    getAllCharges();
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
    function updateDateTextTitle() {
        if ($("#start_date").val() == $("#end_date").val()) {
            $("#dateTextTitle").text($("#start_date").val())
        } else {
            $("#dateTextTitle").text($("#start_date").val() + " => " + $("#end_date").val());
        }
    }
    function initTable(table) {

        table.find("tr[class='table-info']").removeClass("table-info");
        table.find("tr[class='table-success']").removeClass("table-success");
    }
    function prepareToViewAllObjects() {
        $("#allObjectsDiv").removeClass("d-none");
        $("#formDiv").addClass("d-none");
        $("#categoriesDiv").addClass("d-none");
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