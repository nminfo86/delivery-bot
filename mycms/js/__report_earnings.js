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

            if (
                moment(startDate, "DD-MM-YYYY", true).isValid() &&
                moment(endDate, "DD-MM-YYYY", true).isValid() &&
                moment(startDate, "DD-MM-YYYY").isSameOrBefore(moment(endDate, "DD-MM-YYYY"))
            ) {
                getSalesdByAttributes(startDate, endDate);
            } else {
                swal(JsTranslations.oops, JsTranslations.msgDateNonValide, "warning");
            }
     

    });
     $(document).on('click', '#reset', function () {
        $("#start_date").val(moment().startOf('month').format("DD-MM-YYYY"));
        $("#end_date").val(moment().endOf('month').format("DD-MM-YYYY"));

         getSalesdByAttributes();

    });



getSalesdByAttributes()
    //****************** FUNCTIONS *********************//

    function getSalesdByAttributes(startDateFilter, endDateFilter) {

        //if dates are defined in filter we search with them
        //Otherwise we search for current month
        //for checkout roles we search only for current date charges
        var startDate = startDateFilter == undefined ? moment().startOf('month').format("DD-MM-YYYY") : startDateFilter;
        var endDate = endDateFilter == undefined ? moment().endOf('month').format("DD-MM-YYYY") : endDateFilter;

        $.ajax({
            url: "php/JsonReport.php",
            type: "POST",
            // async: false,
            jsonp: false,
            dataType: dataType,
            data: "function=getSalesByAttributes2&startDate=" + startDate + "&endDate=" + endDate ,
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
                            {
                                "data": "total_cost",
                                render: function (data, type, row) {
                                    return formatAmount(data);
                                }
                            },
                            
                            {
                                "data": "total_earning",
                                render: function (data, type, row) {
                                    return formatAmount(data);
                                }
                            },
                            
                        ],
                     

                        "footerCallback": function (row, data, start, end, display) {
                            var api = this.api();
                            // Total over all pages
                           
                             total_price = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                                return currency(a).add(b);
                            }, 0);
                            total_cost = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                                return currency(a).add(b);
                            }, 0);
                            total_earning = api.column(6, { search: 'applied' }).data().reduce(function (a, b) {
                                return currency(a).add(b);
                            }, 0);
                           
                            

                            // Update footer
                            $(".total-footer-charges").html(
                                
                                JsTranslations.sales_category_price_total + " : <b>" + formatAmount(total_price) + "</b><br>" +
                                JsTranslations.sales_category_cost_total + " : <b>" + formatAmount(total_cost) + "</b><br>" +
                                JsTranslations.sales_category_earnings_total + " : <b>" + formatAmount(total_earning) + "</b>"
                            );
                        }
                    });

                 
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


    function updateDateTextTitle() {
        if ($("#start_date").val() == $("#end_date").val()) {
            $("#dateTextTitle").text($("#start_date").val())
        } else {
            $("#dateTextTitle").text($("#start_date").val() + " => " + $("#end_date").val());
        }
    }
});