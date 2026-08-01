/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var searchForm = $('#search');
var allOrdersTable = $('#allOrderstable');
var subOrdersTable = $('#subOrderstable');
var ordersTableClassAlone = "col-lg-12 col-md-12 col-sm-12 col-12";
var ordersTableClasswithDétailTable = "col-lg-5 col-md-6 col-sm-12 col-12";



//*********************** ************** ********

$(document).ready(function () {
    initHMI();

    $("#start_date").datepicker(
        $.extend(
            {
                'dateFormat': 'dd-mm-yy',
                'changeMonth': true,
                'changeYear': true

            }
        )
    );
    $("#start_date").change(function () {
        search("", $("#start_date").val());
    });
    //Search IN Orders TABLE
    $("#searchInput").keyup(function () {

        if (validateForm(searchForm)) {
            search($.trim($("#searchInput").val()), $("#start_date").val());
        }
    });

    $("#allOrdersDiv .searcheTable").change(function () {
        //Hide Order Details when search by place

        initHMI();
        var query = $(this).val() != "NULL" ? $("#searchForm .searcheTable option:selected").val() : "";
        search(query, $("#start_date").val());
    });

    $(document).on('click', '.getDetails', function () {
        var ordere_id = $(this).closest("tr").attr("ordere_id");
        getSubOrdersOrder(ordere_id);

        //css color for table row management
        allOrdersTable.find("tr[class='table-info']").removeClass("table-info");
        $(this).closest("tr").addClass("table-info");

    });

    $(document).on('click', '.printChef', function () {
        var subOrdersarray = new Array();
        subOrdersTable.find("tbody tr[id]").each(function () {
            var tmp = {};
            tmp.id = $(this).attr("id");
            tmp.quantity = $(this).find(".custom-qty-input").val();
            tmp.comment = $(this).find("textarea").val();
            subOrdersarray.push(tmp);
        });
        var subOrdArr = JSON.stringify(subOrdersarray);
        swal({
            title: JsTranslations.print_chef_btn,
            text: (JsTranslations.print_chef_text),
            icon: "info",
             buttons: {
                confirm: JsTranslations.validate_button,
                cancel: JsTranslations.cancel_button
               
            },
        })
            .then((willReady) => {
                if (willReady) {

                    printChefSubOrders(subOrdArr, orderStateStarted);
                }
            });

    });

    $(document).on('click', '.reprintAllBtn', function () {
    swal({
        title: JsTranslations.modal_title_reprint,
        text: JsTranslations.reprint_ticket,
        icon: "info",
        buttons: {
            confirm: JsTranslations.validate_button,
            cancel: JsTranslations.cancel_button
        },
    }).then((willReady) => {
        if (willReady) {
            subOrdersTable.find("tr[id]").filter(function () {
                return $(this).attr("prepare") == "1";
            }).each(function () {
                rePrint($(this).attr("id"));
            });
        }
    });
});

    $(document).on('click', '.delete', function () {

        var suborder_id = $(this).closest("tr").attr("id");
        var ordere_id = $(this).closest("tr").attr("ordere_id");
        var table_id = $(this).closest("tr").attr("table_id");
        swal({
            title: JsTranslations.modal_title_cancel,
            text: JsTranslations.msgConfirmDelSubOrder,
            icon: "warning",
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: {
                    text: JsTranslations.cancel_button,
                    value: null,
                    visible: true,
                    className: "danger",
                    closeModal: true,
                },
            },
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    deleteSubOrder(suborder_id, ordere_id, table_id, false)
                }
            });
    })

    $(document).on('click', '.printSubOrder', function () {

        swal({
            title: JsTranslations.modal_title_reprint,
            text: (JsTranslations.reprint_ticket),
            icon: "info",
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: JsTranslations.cancel_button
            },
        })
            .then((willReady) => {
                if (willReady) {
                    // ********************** rePrint Ordere ************************
                    rePrint($(this).closest("tr").attr("id"));
                    // ********************** rePrint Ordere ************************
                }
            });
    });

    $(document).on('click', '.printClient', function () {

        swal({
            title: JsTranslations.modal_title_reprint,
            text: (JsTranslations.reprint_ticket),
            icon: "info",
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: JsTranslations.cancel_button
               
            },
        })
            .then((willReady) => {
                if (willReady) {
                    // ********************** rePrint Ordere ************************
                    rePrintOrder($(this).attr("ordere_id"));
                    // ********************** rePrint Ordere ************************
                }
            });
    });

    $(document).on('click', '.cancelOrderBtn', function () {
        var orderdetails = $("#orderDetail h4").text();

        swal({
            title: orderdetails,
            text: ("Voulez vous vraiment annuler cette commande ?!!"),
            icon: "warning",
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: {
                    text: JsTranslations.cancel_button,
                    value: null,
                    visible: true,
                    className: "danger",
                    closeModal: true,
                },
            },
            dangerMode: true,
        })
            .then((willReady) => {
                if (willReady) {
                    //                        cancelOrder(ordere_id);
                    subOrdersTable.find("tr:gt(0)[id]").each(function () {
                        deleteSubOrder($(this).attr("id"), $(this).attr("ordere_id"), true);

                    });
                }
            });
    });

    search("", $("#start_date").val());

    //****************** FUNCTIONS *********************//
    function search(query, date) {

        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllOrdersforHistory&search=" + query + "&date=" + date,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

                allOrdersTable.empty();
                $("#orderDetail").addClass("d-none");
                // Reset grid so the orders list takes full width when details are hidden
                $("#ordersDiv")
                    .removeClass(ordersTableClasswithDétailTable)
                    .addClass(ordersTableClassAlone);
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {
                    for (var i = 0; i < data.length; i++) {
                        var hour = new Date(data[i].updateDate).getHours() + ":" + new Date(data[i].updateDate).getMinutes();
                        allOrdersTable.append(
                            "<tr table_id = " + data[i].table_id + " ordere_id = " + data[i].id + ">" +
                            "<td>" +
                            "<button class='btn btn-sm btn-info getDetails margin-right-1em'>" +
                            "<i class='fas fa-history fa-1_5x' style='margin-right: 5px'></i>" +
                            "</button>" +
                            "</td>" +
                            "<td class='td-tableName'>"
                            + (data[i].tableName === null ? "Code: " + data[i].code : data[i].tableName + " : " + data[i].code)
                            + "<br>" + hour
                            + "</td>" +
                             "<td class='d-none d-sm-table-cell'>" + formatAmount(data[i].totalTtc) + "</td>" +
                            "<td>" + getPlaceValue(data[i].place, data[i].tableName, data[i].table_id) + "</td>" +
                            "</tr>");
                    }
                }

                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function getSubOrdersOrder(ordere_id) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSubOrdersOfOrderLabel&ordere_id=" + ordere_id,
            beforeSend: function () {

                $("#divLoadingcms").removeClass("d-none");
                subOrdersTable.find("tr:gt(0)").remove();
                $("#ordersDiv").removeClass(ordersTableClassAlone);
                $("#ordersDiv").addClass(ordersTableClasswithDétailTable);
                $("#orderDetail").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {

                    var place= getPlaceValue(data[0].place, data[0].tableName, data[0].table_id);
                    
                    $("#orderDetail h4").text(JsTranslations.order + " " + data[0].code + "  " +
                        (data[0].tableName == null ? place : data[0].tableName));

                    $(".printClient").attr("table_id", data[0].table_id == null ? "null" : data[0].table_id);
                    $(".printClient").attr("ordere_id", data[0].ordere_id);

                    for (var i = 0; i < data.length; i++) {

                        subOrdersTable.append(
                            "<tr id = " + data[i].id
                            + " table_id = " + data[i].table_id
                            + " ordere_id = " + data[i].ordere_id
                            + " accept_supplement=" + data[i].acceptSupplement
                            + " prepare=" + data[i].prepare
                            + " supplement=" + data[i].supplement
                            + ">" +
                            "<td>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) + "</td>" +
                            "<td>" + data[i].quantity + "</td>" +
                            "<td>" + data[i].subTotal + "</td>" +
                            "<td class='text-center d-flex action'>" +
                            "<button class='btn btn-sm btn-warning printSubOrder mr-1' style='" + (!data[i].prepare ? "visibility:hidden;" : "") + "'>"  +
                            "<i class='fas fa-print fa-1_5x' style='margin: 5px'></i>" +
                            "</button>" +
                            "<button class='btn btn-sm btn-danger delete'>" +
                            "<i class='far fa-trash-alt fa-1_5x' style='margin: 5px'></i>" +
                            "</button>" +
                            "</td>" +
                            // "<td>" +
                            // "<button class='btn btn-sm btn-warning printSubOrder margin-right-1em'>" +
                            // "<i class='fas fa-print fa-1_5x' style='margin: 5px'></i>" +
                            // "</button>" +
                            // "</td>" +
                            "</tr>"
                        )
                        if (data[i].suplTitle != null) {
                            subOrdersTable.append(
                                "<tr class='supplement' style='display: contents;' suborder_id='" + data[i].id + "'>" +
                                "<td>" +
                                "<i>" + (data[i].suplTitle == null ? '' : (" +" + data[i].suplQuantity + " " + data[i].suplTitle)) + "</i>" +
                                "</td>" +
                                "</tr>")
                        }
                    }
                     // $("#orderDetail h3").text( JsTranslations.total + " : " + Intl.NumberFormat().format(data[0].orderePrice) + " " + cmsCurrency);
                    
                    var vatId = data[0].vat_id;
                    var orderePrice = parseFloat(data[0].orderePrice) || 0;
                    var vatAmount = parseFloat(data[0].vatAmount) || 0;
                    var totalTtc = parseFloat(data[0].totalTtc) || 0;


                    if (vatId && vatId !== null && vatId !== 'NULL') {
                        
                        var html = "<div class='order-totals'>" +
                            "<div class='total-line'><strong>" + (JsTranslations.total_ht_label) + ":</strong> " + formatAmount(orderePrice) + "</div>" +
                            "<div class='total-line'><strong>" + (JsTranslations.total_tva_label) + ":</strong> " + formatAmount(vatAmount) + "</div>" +
                            "<div class='total-line font-weight-bold'><strong>" + (JsTranslations.total_ttc_label) + ":</strong> " + formatAmount(totalTtc) + "</div>" +
                            "</div>";
                        
                        $("#orderDetail h3").html(html);
                    } else {
                        $("#orderDetail h3").text(JsTranslations.total + " : " + formatAmount(data[0].totalTtc));
                    }
                }

                if (isMediaQuery("991px")) {
                    window.location = "#printChef";
                } else {
                    window.location = "#searchForm";
                }
                $("#divLoadingcms").addClass("d-none");

                accessControle();
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function rePrintOrder(ordere_id) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=rePrintOrder&&ordere_id=" + ordere_id,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error").then((willReady) => {
                        if (willReady) {
                            //Do nothing
                        }
                    });;
                } else {
                    //Do nothing
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }

    function rePrint(id) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=rePrint&id=" + id,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error").then((willReady) => {
                        if (willReady) {
                            //Do nothing
                        }
                    });
                } else {
                    //Do nothing
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }

    function deleteSubOrder(id, ordere_id, table_id, cancel_all) {

        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteSubOrder&id=" + id + "&deleteOrder=0",
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    if (data.message === last_subOrder) {
                        window.location = "checkoutHistory.php";
                    } else {
                        if (!cancel_all) { // cancel_all variable used to determin whether the user want to cancel the order completely
                            getSubOrdersOrder(ordere_id);
                        }
                    }
                }
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function printChefSubOrders(subOrdArray, progression) {

        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateAllSubOrderProgressionAndPrint&array="
                + subOrdArray
                + "&progression=" + progression,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    //Do nothing here
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function accessControle() {
        if ($("#sessionRole").val() != roleAdmin) {

            $(".start-date").addClass("d-none");
            $(".cancelOrderBtn").addClass("d-none");
            $(".delete").addClass("d-none");

        }
    }
    function initHMI() {
        accessControle();
        $("#ordersDiv").removeClass(ordersTableClasswithDétailTable);
        $("#ordersDiv").addClass(ordersTableClassAlone);
        //        $("#orderDetail").addClass("d-none");
    }

});