/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var searchForm = $("#searchForm");
var objectForm = $("#objectForm");

// var readTable = $('#readTable');

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




    $(document).on('click', '#filter', function () {
        var startDate = $("#start_date").val();
        var endDate = $("#end_date").val();
        var category_id = $(".searcheCategory").val()
        if (category_id == 0) {
            swal(JsTranslations.oops, JsTranslations.report_select_category, "warning");
        } else {
            if (
                moment(startDate, "DD-MM-YYYY", true).isValid() &&
                moment(endDate, "DD-MM-YYYY", true).isValid() &&
                moment(startDate, "DD-MM-YYYY").isSameOrBefore(moment(endDate, "DD-MM-YYYY"))
            ) {
                getSalesdByAttributes(startDate, endDate, category_id);
            } else {
                swal(JsTranslations.oops, JsTranslations.msgDateNonValide, "warning");
            }
        }

    });




    // getAllCharges();
    //****************** FUNCTIONS *********************//

    function getSalesdByAttributes(startDateFilter, endDateFilter, category_id) {

        //if dates are defined in filter we search with them
        //Otherwise we search for current month
        //for checkout roles we search only for current date charges
        var startDate = startDateFilter;
        var endDate = endDateFilter;

        var attributChartData = {};

        $.ajax({
            url: "php/JsonReport.php",
            type: "POST",
            // async: false,
            jsonp: false,
            dataType: dataType,
            data: "function=getSalesByAttributes&startDate=" + startDate + "&endDate=" + endDate + "&category_id=" + category_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    } else {
                        var readTable = $('#readTable').DataTable({
                            language: dataTableLanguage, //definde in global.js
                            responsive: true,
                            destroy: true,
                            paging: false,
                            "dom": "<'row flex-nowrap'"
                                + "<'col-lg-4 col-sm-4 col-xs-4 'l>"
                                + "<'col-lg-8 col-sm-8 col-xs-8 'f>"
                                + ">rt" + "<'total-footer-charges'>p",
                        }).clear().draw();
                    }
                } else {
                    var readTable = $('#readTable').DataTable({
                       language: dataTableLanguage, //definde in global.js
                        responsive: true,
                        destroy: true,
                        scrollY: '50vh',
                        scrollCollapse: true,
                        paging: false,
                        "dom": "<'row flex-nowrap'"
                            + "<'col-lg-4 col-sm-4 col-xs-4 d-sm-block'l>"
                            + "<'col-lg-8 col-sm-8 col-xs-8'f>"
                            + ">rt" + "<'total-footer-charges'>p",
                        "order": [2, 'asc'],
                        "data": data,
                        "columns": [
                            { "data": 'category' },
                            { "data": "title" },
                            { "data": "attributeValue" },
                            { "data": "total_qty", },
                            {
                                "data": "total_price",
                                render: function (data, type, row) {
                                    return formatAmount(data);
                                }
                            },
                        ],
                        rowGroup: {
                            startRender: null,
                            endRender: function (rows, group) {

                                var totalQty = rows
                                    .data()
                                    .pluck('total_qty')
                                    .reduce(function (a, b) {
                                        return ((a * 1) + (b * 1));
                                    }, 0);

                                var totalPrice = rows
                                    .data()
                                    .pluck('total_price')
                                    .reduce(function (a, b) {
                                        return currency(a).add(b);
                                    }, 0);

                                //This is added to fill HiGhChart data
                                attributChartData[group] = totalQty
                                //
                                return $('<tr/>')
                                    .append('<td colspan="3">'+ JsTranslations.report_io_total + ' ' + group + '</td>')
                                    .append('<td>' + totalQty + '</td>')
                                    // .append('<td/>')
                                    .append('<td>' + formatAmount(totalPrice) + '</td>');

                            },
                            dataSrc: ['attributeValue']
                        },

                        "footerCallback": function (row, data, start, end, display) {
                            var api = this.api();
                            // Total over all pages
                            total_price = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                                return currency(a).add(b);
                            }, 0);
                            total_qty = api.column(3, { search: 'applied' }).data().reduce(function (a, b) {
                                return ((a * 1) + (b * 1));
                            }, 0);

                            // Update footer
                            $(".total-footer-charges").html(
                                (JsTranslations.sales_category_qty_total + " : " + "<b>"+ total_qty  + "</b>" 
                               + " __ " + JsTranslations.sales_category_price_total+ " : " + "<b>"+ formatAmount(total_price)) +"</b>"
                            );
                        }
                    });

                    createChart(attributChartData)

                    //Update HighChart on every user filter
                    readTable.on('draw', function () {
                        createChart(attributChartData)
                        attributChartData = {}
                    });

                    //Init attributes data to be loaded again when user filter data
                    attributChartData = {}
                    //
                    $("#loadingImage").addClass("d-none");
                    updateDateTextTitle();
                }

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });

    }

    function createChart(dataArray) {
        var myChart = Highcharts.chart('container', {
            chart: {
                type: 'pie',
            },
            title: {
                text: undefined,
            },
            plotOptions: {
                dataLabels: {
                    enabled: true
                },
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            series: [
                {
                    name: JsTranslations.amount_option,
                    data: mapDataToHighCharts(dataArray),
                },
            ],
        });
    }
    //This function maps any array data to HighCharts 
    function mapDataToHighCharts(dataArray) {
        // And map it to the format highcharts uses
        return $.map(dataArray, function (val, key) {
            return {
                name: key,
                y: val,
            };
        });
    }

    function updateDateTextTitle() {
        if ($("#start_date").val() == $("#end_date").val()) {
            $("#dateTextTitle").text($("#start_date").val())
        } else {
            $("#dateTextTitle").text($("#start_date").val() + " => " + $("#end_date").val());
        }
    }
});