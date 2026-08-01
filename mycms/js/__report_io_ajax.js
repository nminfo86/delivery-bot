/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var objectForm = $("#objectForm");

var sailesTable = $('#sailesTable');
var chargesTable = $('#chargesTable');
currentVatAmount = 0;
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
            {
                'dateFormat': 'dd-mm-yy',
                'changeMonth': true,
                'changeYear': true
            },
            { maxDate: 0 }
        ));

    //************** */
    $(document).on('click', '#caisse', function () {
        //this test is reversed because when clicking on checkbox it's change then we test check status
        if ($("#caisseCheckBox").attr("value") == "0") {
            //this checkbox was checked
            $("#caisseCheckBox").attr("value", "1");
            $("#faCheck").removeClass("d-none");
            $("#nonFaCheck").addClass("d-none");

            var todaye = moment().format("DD-MM-YYYY");
            $("#start_date").val(todaye);
            $("#end_date").val(todaye);
            getSalesByDate(todaye, todaye);
            getChargesByDate(todaye, todaye, 1);

        } else {
            //this checkbox was not checked
            $("#caisseCheckBox").attr("value", "0");
            $("#nonFaCheck").removeClass("d-none");
            $("#faCheck").addClass("d-none");

            var startMonth = moment().startOf('month').format("DD-MM-YYYY");
            var endMonth = moment().endOf('month').format("DD-MM-YYYY");
            $("#start_date").val(startMonth);
            $("#end_date").val(endMonth);

            getSalesByDate();
            getChargesByDate();
        }

    });

    $(document).on('click', '#reset', function () {
        $("#start_date").val(moment().startOf('month').format("DD-MM-YYYY"));
        $("#end_date").val(moment().endOf('month').format("DD-MM-YYYY"));

        $("#caisseCheckBox").attr("value", "0");
        $("#faCheck").addClass("d-none");
        $("#nonFaCheck").removeClass("d-none");
        getSalesByDate();
        getChargesByDate();
        getVatByDate();

    });

    $(document).on('click', '#filter', function () {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        if (moment(startDate, "DD-MM-YYYY").isSameOrBefore(moment(endDate, "DD-MM-YYYY"))) {
            getSalesByDate(startDate, endDate);
            if ($("#caisseCheckBox").attr("value") == "1") {
                getChargesByDate(startDate, endDate, 1);
            } else {
                getChargesByDate(startDate, endDate, 0);
            }

            // getVatByDate(startDate, endDate);
            
        } else {

            swal(JsTranslations.oops, JsTranslations.start_after_end_warning, "warning");
        }

    });

    $(document).on('click', '#printSuborders', function () {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
       
        if (moment(startDate, "DD-MM-YYYY").isSameOrBefore(moment(endDate, "DD-MM-YYYY"))) {
            printSales(startDate, endDate);
        } else {
            swal(JsTranslations.oops, JsTranslations.msgDateNonValide, "warning");
        }

    });

     $(document).on('click', '#printCharges', function () {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        var decaisse = $("#caisseCheckBox").attr("value") == "1" ? 1 : 0;

        if (moment(startDate, "DD-MM-YYYY").isSameOrBefore(moment(endDate, "DD-MM-YYYY"))) {
            printCharges(startDate, endDate, decaisse);
        } else {
            swal(JsTranslations.oops, JsTranslations.msgDateNonValide, "warning");
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


    getSalesByDate();
    getChargesByDate();


    //****************** FUNCTIONS *********************//

    function getSalesByDate(startDateFilter, endDateFilter) {

        //if dates are defined in filter we search with them
        //Otherwise we search for current month
        var startDate = startDateFilter == undefined ? moment().startOf('month').format("DD-MM-YYYY") : startDateFilter;
        var endDate = endDateFilter == undefined ? moment().endOf('month').format("DD-MM-YYYY") : endDateFilter;

        $.ajax({
            url: "php/JsonReport.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSalesByDate&startDate=" + startDate + "&endDate=" + endDate,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    } else {
                        sailesTable.DataTable({
                            language: dataTableLanguage, //definde in global.js
                            responsive: true,
                            destroy: true,
                            "dom":
                                "t<'total-footer-sales'>p",
                        }).clear().draw();;
                    }
                } else {
                    currentVatAmount = parseFloat(data.totalVat) || 0;
                    var rows = data.data || [];
                    $("#tfs").text("");
                    $("#tvat").text("");
                    $("#tfs_ttc").text("");
                    
                    sailesTable.DataTable({
                        language: dataTableLanguage, //definde in global.js
                        responsive: true,
                        destroy: true,
                        "dom":
                            "t<'total-footer-sales'>p",
                        // "t<'d-flex align-items-center justify-content-between'<'total-footer-sales'>p>",
                        "order": [2, 'desc'],

                        "data": rows,
                        "columns": [
                            {
                                "data": 'title',
                            },
                            {
                                "data": "sumQte",
                                "className": 'dt-body-center'
                            },                        
                            {
                                "data": "sumValue",
                                render: function (data, type, row) {
                                    return formatAmount(data);
                                }
                            },
                        ],
                        "drawCallback": function (settings) {
                            ajustTablesheight(sailesTable, chargesTable);
                        },
                        "footerCallback": function (row, data, start, end, display) {
                            var api = this.api();


                            // Total over all pages
                            total = api.column(2).data().reduce(function (a, b) {
                                return currency(a).add(b);
                            }, 0);

                            // update footer: include VAT and sales-with-VAT spans

                            //we used "tfs_vat" to be able to calculate difference with charges because in case we have VAT 
                            // we need to use total with VAT to calculate difference and not total without VAT
                            var footerHtml = JsTranslations.report_io_sales + " : <span id=tfs_ttc>" + formatAmount(total) + "</span>";

                            if (Number(currentVatAmount) && Number(currentVatAmount) !== 0) {
                                footerHtml = JsTranslations.report_io_sales + " : <span id=tfs>" + formatAmount(total) + "</span><br/>" +
                                    (JsTranslations.total_tva_label) + " : <span id=tvat>" + formatAmount(currentVatAmount) + "</span><br/>" +
                                    (JsTranslations.total_ttc_label) + " : <span id=tfs_ttc>" + formatAmount(currency(total).add(currentVatAmount)) + "</span>"
                            }
                            $(".total-footer-sales").html(footerHtml);

                            }
                        });

                }
                $("#loadingImage").addClass("d-none");
                calculateDifference();
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }
    function getChargesByDate(startDateFilter, endDateFilter, decaisse) {

        //if dates are defined in filter we search with them
        //Otherwise we search for current month
        var startDate = startDateFilter == undefined ? moment().startOf('month').format("DD-MM-YYYY") : startDateFilter;
        var endDate = endDateFilter == undefined ? moment().endOf('month').format("DD-MM-YYYY") : endDateFilter;

        // custom data to choose whether user clicked for caisse or not
        var data = "function=getAllCharges&startDate=" + startDate + "&endDate=" + endDate;
        if (decaisse == 1) {
            data = "function=getAllChargesByDecaisse&startDate=" + startDate + "&endDate=" + endDate + "&decaisse=" + decaisse;
        }

        $.ajax({
            url: "php/JsonCharge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: data,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    } else {
                        chargesTable.DataTable({
                            language: dataTableLanguage, //definde in global.js
                            responsive: true,
                            destroy: true,
                            "dom":
                                "t<'total-footer-charges'>p",
                        }).clear().draw();;
                    }
                } else {
                    $("#tfc").text("");
                    chargesTable.DataTable({
                        language: dataTableLanguage, //definde in global.js
                        responsive: true,
                        destroy: true,
                        "dom":
                            "t<'total-footer-charges'>p",
                        "order": [2, 'desc'],
                        "data": data,
                        "columns": [
                            { "data": "typeCharge" },
                            {
                                "data": 'dateTime',
                                render: function (data, type, row) {
                                    return moment(row.creationDate).calendar();
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
                        ],
                        "drawCallback": function (settings) {
                            ajustTablesheight(sailesTable, chargesTable);
                        },
                        "footerCallback": function (row, data, start, end, display) {
                            var api = this.api();

                            // Remove the formatting to get integer data for summation
                            // var intVal = function (i) {
                            //     return typeof i === 'string' ? i.replace(/[\ ]/g, '') * 1 :
                            //         typeof i === 'number' ? i : 0;
                            // };

                            // Total over all pages
                            total = api.column(2).data().reduce(function (a, b) {
                                // return intVal(a) + intVal(b);
                                return currency(a).add(b);
                            }, 0);

                            // Update footer
                            $(".total-footer-charges").html(
                                (JsTranslations.report_io_expense +" : " + "<span id=tfc>" + formatAmount(total) + "</span>")
                                // ("Charges : " + "<span id=tfc>" + Intl.NumberFormat().format(total) + "</span>" + " DZD")
                            );
                        }
                    });
                }
                $("#loadingImage").addClass("d-none");
                updateDateTextTitle();
                calculateDifference();

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });

    }

    function printSales(startDateFilter, endDateFilter){
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=printPaidSubOrdersSummaryOfDay&startDate=" + startDateFilter + "&endDate=" + endDateFilter,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                        swal(JsTranslations.modal_title_empty, data.message, "warning");
                    } else {
                        swal(JsTranslations.oops, data.message, "warning");
                    }
                } else {
                   swal(JsTranslations.modal_title_success, JsTranslations.msgOperationReussie, 'success');

                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        }); 

      
    }

     function printCharges(startDateFilter, endDateFilter, decaisse) {
        $.ajax({
            url: "php/JsonCharge.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=printChargesSummaryOfDay&startDate=" + startDateFilter + "&endDate=" + endDateFilter + "&decaisse=" + decaisse,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                        swal(JsTranslations.modal_title_empty, data.message, "warning");
                    } else {
                        swal(JsTranslations.oops, data.message, "warning");
                    }
                } else {
                    swal(JsTranslations.modal_title_success, JsTranslations.msgOperationReussie, 'success');
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

    function calculateDifference() {

        var sales = $("#tfs_ttc").text();
        var charges = $("#tfc").text();
        var salesNum = extractNumber(sales);
        var chargesNum = extractNumber(charges);
        var diff = currency(salesNum).subtract(chargesNum);

        $("#diffrence").html(JsTranslations.report_io_result + " : " + formatAmount(diff))
        if ($("#caisseCheckBox").attr("value") == "1") {
            $("#diffrence").html(JsTranslations.report_io_cash+ " : " + formatAmount(diff))
        }
    }
 
    function ajustTablesheight(table1, table2) {
        table1Height = table1.css("height");
        table1Height = table1Height.replace('px', '');

        table2Height = table2.css("height");
        table2Height = table2Height.replace('px', '');

        var mq = window.matchMedia("(max-width: 768px)");
        if (!mq.matches) {
            if (parseInt(table1Height) > parseInt(table2Height)) {
                table2.css("height", table1Height + "px");
            } else {
                table1.css("height", table2Height + "px");
            }

        }
    }

});