/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var searchForm = $('#search');
var allOrdersTable = $('#allOrderstable');
var subOrdersTable = $('#subOrderstable');
var ordersTableClassAlone = "col-lg-12 col-md-12 col-sm-12 col-xs-12";
var ordersTableClasswithDétailTable = "col-lg-5 col-md-6 col-sm-12 col-xs-12";

//*********************** ************** ********

$(document).ready(function () {
    initHMI();
    //Search IN Orders TABLE
    $("#searchInput").keyup(function () {
        if ($("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()), 0, "ASC");
            }
        }
    });

    $("#searchButton").click(function () {
        if (!$("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                allOrdersTable.find("tr").remove();
                search($.trim($("#searchInput").val()), 0, "ASC");
            }
        }
    });

    $("#allOrdersDiv .searcheTable").change(function () {
        //Hide Order Details when search by place
        $("#orderDetail").addClass("d-none");
        initHMI();
        var query = $(this).val() != "NULL" ? $("#allOrdersDiv .searcheTable option:selected").val() : "";
        search(query, 0, "ASC");
    });

    //*******  minus, plus, delete and comment action buttons*********** */

    $(document).on('click', '.minus', function () {
        var suborder_id = $(this).closest("tr").attr('id');
        var customQteInput = $(this).closest("tr").find(".custom-qty-input");
        var table_id = $(this).closest("tr").attr("table_id");
        var ordere_id = $(this).closest("tr").attr("ordere_id");

        // Change Qte input value
        // customQteInput.val(parseInt(customQteInput.val()) - 1);


        //Prevent input less than 1 qte
        if (customQteInput.val() == 0) {
            customQteInput.val(1);
        }

        updateSuborderQte(suborder_id, parseInt(customQteInput.val()) - 1, ordere_id, table_id);
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
    });

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

    $(document).on('click', ".commentSubOrdere", function () {
        // var regexp = new RegExp(/^[\u0600-\u06FFéèàù!?';:.,"\w\s-]+$/);
        var id = $(this).closest("tr").attr("id");
        swal(JsTranslations.add_chef_comment, {
            content: "input",
            icon: "info",
            buttons: {
                confirm: JsTranslations.validate_button
            },
        })
            .then((value) => {
                //This tests the validation of subOrder comment
                if (`${value}` !== '') {

                    $(this).closest("tr").find("textarea").text(`${value}`);
                    updateSuborderComment(id, `${value}`);
                }
            });
        $(".swal-content__input").val($(this).closest("tr").find("textarea").text())
    });

    $(document).on('click', '.getDetails', function () {
        var table_id = $(this).closest("tr").attr("table_id");
        var ordere_id = $(this).closest("tr").attr("ordere_id");

        table_id === 'null' ? getSubOrders(ordere_id) : getSubOrdersTable(table_id);

        //css color for table row management
        allOrdersTable.find("tr[class='table-info']").removeClass("table-info");
        $(this).closest("tr").addClass("table-info");

    });

    $(document).on('click', '.addVat', function () {

        var html = $(document).find(".vatDiv").html();
        var ordere_id = $("#orderDetail").attr("ordere_id");
        var table_id = $("#orderDetail").attr("table_id");

            //create Vats div
            var vatDiv = document.createElement("div");

            vatDiv.innerHTML = html;
            swal({
                content: vatDiv,
                buttons: {
                    confirm: {
                        text: JsTranslations.validate_button,
                        value: "Ajouter",
                    },
                    cancelVat: {
                        text: JsTranslations.cancel_vat,
                        value: "CancelVat",
                        className: "cancel-vat",
                    },
                    cancel: {
                        text: JsTranslations.cancel_button,
                        value: null,
                        visible: true,
                        className: "cancel",
                    },
                }
            }).then((value) => {
                switch (value) {
                    case "Ajouter":

                        var vat_id = $(".swal-content").find("input:checked").val();

                        if (ordere_id !== 'null') {
                            updateOrderVat(ordere_id, vat_id);

                        } else if (table_id !== 'null') {

                            updateTableVat(table_id, vat_id);
                        }

                        break;
                    case "CancelVat":
                        // Handle cancel VAT action
                        if (ordere_id !== 'null') {
                            updateOrderVat(ordere_id, "NULL");

                        } else if (table_id !== 'null') {

                            updateTableVat(table_id, "NULL");
                        }
                        break;
                    default:
                }
            });
    });

    $(document).on('click', '.payOrderBtn', function () {

        var orderPrice = $("#orderDetail h3 .total-ttc").text();

        swal({
            title: orderPrice,
            text: (JsTranslations.modal_title_pay),
            icon: "info",
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: JsTranslations.cancel_button
               
            },
        })
            .then((willReady) => {
                if (willReady) {
                    // ********************** Paye Ordere ************************
                    updateOrderPayement($(this).attr("table_id"), $(this).attr("ordere_id"));
                    // ********************** Paye Ordere ************************

                    if ($(this).attr("table_id") !== "null") {
                        freeTable($(this).attr("table_id"));
                    }
                }
            });
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

    $(document).on('click', '.cancelOrderBtn', function () {
        var orderdetails = $("#orderDetail h4").text();

        swal({
            title: orderdetails,
            text: (JsTranslations.ch_menu_confirm_cancel),
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

                    subOrdersTable.find("tr:gt(0)[id]").each(function () {
                        deleteSubOrder($(this).attr("id"), $(this).attr("ordere_id"), null, true);

                    });
                }
            });
    });

    search("", 0, "ASC");

    //****************** FUNCTIONS *********************//
    function search(query, payed, orderBy) {

        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllOrdersOfDayByPayement&search=" + query + "&payed=" + payed + "&orderBy=" + orderBy,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

                allOrdersTable.empty();
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                     // Set count to 0 if failed
                     $("#orderCountCheckoutPanel").text(0);
                } else {
                    var uniqueIds = [];
                    for (var i = 0; i < data.length; i++) {

                        // getPlaceValue(place, tableName, table_id);


                        allOrdersTable.append(
                            "<tr table_id = " + data[i].table_id + " ordere_id = " + data[i].id + ">" +
                            "<td>" +
                            "<button class='btn btn-sm btn-info getDetails margin-right-1em'>" +
                            "<i class='fas fa-bars fa-lg' style='margin-right: 5px'></i>" +
                            "</button>" +
                            "</td>" +
                             "<td class='td-tableName'>" + (data[i].tableName === null ? "Code: " + data[i].code : data[i].tableName) + "</td>" +
                            "<td class='d-none d-sm-table-cell'>" + formatAmount(data[i].tablePrice) + "</td>" +
                             "<td>" + getPlaceValue(data[i].place, data[i].tableName, data[i].table_id) + "</td>" +
                            "</tr>");

                            // Collect unique suborder ids
                            if (uniqueIds.indexOf(data[i].id) === -1) {
                                uniqueIds.push(data[i].id);
                            }
                    }
                    // Show the number of unique suborders in the badge
                    $("#orderCountCheckoutPanel").text(uniqueIds.length);
                }

                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#orderCountCheckoutPanel").text(0);
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    //This function is used to retrieve suborders of emporter order
    function getSubOrders(ordere_id) {
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
                    $("#orderDetail h4").text(JsTranslations.order + " " + data[0].code + "  " +
                        getPlaceValue(data[0].place, data[0].tableName, data[0].table_id));

                        //fill buttons and order details attributes for further use
                    $(".payOrderBtn").attr("table_id", data[0].table_id == null ? "null" : data[0].table_id);
                    $(".payOrderBtn").attr("ordere_id", ordere_id);

                    //used to manage vats
                     $("#orderDetail").attr("ordere_id", ordere_id);
                     $("#orderDetail").attr("table_id", "null");
                    //

                    for (var i = 0; i < data.length; i++) {

                        subOrdersTable.append(
                            "<tr id = " + data[i].id
                            + " table_id = " + data[i].table_id
                            + " ordere_id = " + data[i].ordere_id
                            + " accept_supplement=" + data[i].acceptSupplement
                            + " prepare=" + data[i].prepare
                            + " supplement=" + data[i].supplement
                            + ">" +
                            "<td>" + data[i].code + "</td>" +
                            "<td class='title'>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) + "</td>" +
                            "<td>" +
                            "<div id='custom-qty' class='custom-qty d-flex'>" +
                            "<input  type='number' disabled  class='custom-qty-input ' name='quantity=' value=" + data[i].quantity +
                            " step='1' min='1' max='100'>" +
                            // "<span class='plus'>+</span>" +
                            "</div>" +
                            "</td>" +
                            "<td>" + data[i].subTotal + "</td>" +
                            "<td class='text-center d-flex action'>" +
                            "<button class='btn btn-sm btn-warning printSubOrder mr-1' style='" + (!data[i].prepare ? "visibility:hidden;" : "") + "'>" +
                            "<i class='fas fa-print fa-1_5x' style='margin: 5px'></i>" +
                            "</button>" +
                            "<button class='btn btn-sm btn-danger delete'>" +
                            "<i class='far fa-trash-alt fa-1_5x' style='margin: 5px'></i>" +
                            "</button>" +
                            "</td>" +
                            // "<td class='text-center d-flex action'>" +
                            // // "<span class='fas fa-comment-dots fa-1_5x commentSubOrdere' style = 'cursor: pointer; margin-right: 10px;'>" +
                            // "</span>" +
                            // "<div id='custom-qty' class='custom-qty'>" +
                            // // (data[i].quantity == 1 ? "<span class='minus d-none'>-</span>" : "<span class='minus'>-</span>") +
                            // // (data[i].quantity == 1 ? "<span class='delete'><i class='far fa-trash-alt'></i></span>" : "<span class='delete d-none'><i class='far fa-trash-alt'></i></span>") +
                            // // "<span class='plus'>+</span>" +
                            // "</div>" +
                            // "</td>" +
                            // "<td class='d-none'>" +
                            // "<textarea type='textarea' rows='1' cols='30' name='push_title'>" + data[i].subComment + "</textarea>" +
                            // "</td>" +
                            "</tr>");

                    }

                    // $("#orderDetail h3").text( JsTranslations.total + " : " + Intl.NumberFormat().format(data[0].orderePrice) + " " + cmsCurrency);
                    
                    var vatId = data[0].vat_id;
                    var orderePrice = parseFloat(data[0].orderePrice) || 0;
                    var vatAmount = parseFloat(data[0].vatAmount) || 0;
                    var totalTtc = parseFloat(data[0].totalTtc) || 0;


                    if (vatId && vatId !== null && vatId !== 'NULL') {
                        
                        var html = "<div class='order-totals'>" +
                            "<div class='total-ht'><strong>" + (JsTranslations.total_ht_label) + ":</strong> " + formatAmount(orderePrice) + "</div>" +
                            "<div class='total-vat'><strong>" + (JsTranslations.total_tva_label) + ":</strong> " + formatAmount(vatAmount) + "</div>" +
                            "<div class='total-ttc font-weight-bold'><strong>" + (JsTranslations.total_ttc_label) + ":</strong> " + formatAmount(totalTtc) + "</div>" +
                            "</div>";
                        
                        $("#orderDetail h3").html(html);
                    } else {
                        var html = "<div class='order-totals'>" +
                            "<div class='total-ttc'>" + (JsTranslations.total) + " : " + formatAmount(totalTtc) + "</div>" +
                            "</div>";
                        $("#orderDetail h3").html(html);
                    }
                }
                window.location = "#validOrderBtn";
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    //This function is used to retrieve suborders of table
    function getSubOrdersTable(table_id) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSubOrdersOfTableLabel&table_id=" + table_id,
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

                    // fill buttons and order details attributes for further use
                    $("#orderDetail h4").text(JsTranslations.order + " " + data[0].tableName);

                    $(".payOrderBtn").attr("table_id", data[0].table_id == null ? "null" : data[0].table_id);
                    $(".payOrderBtn").attr("ordere_id", "null");

                    //used to manage vats
                     $("#orderDetail").attr("ordere_id", "null");
                     $("#orderDetail").attr("table_id", data[0].table_id == null ? "null" : data[0].table_id);

                     //
                    var seenOrders = {};
                    var tableOrderePrice = 0;
                    var tableVatAmount = 0;
                    var tableTotalTtc = 0;

                    for (var i = 0; i < data.length; i++) {

                        // aggregate once per order id
                        var ordId = data[i].ordere_id;
                        if (!seenOrders[ordId]) {
                            seenOrders[ordId] = true;
                            tableOrderePrice += parseFloat(data[i].orderePrice) || 0;
                            tableVatAmount += parseFloat(data[i].vatAmount) || 0;
                            tableTotalTtc += parseFloat(data[i].totalTtc) || 0;
                        }
                        subOrdersTable.append(
                            "<tr id = " + data[i].id
                            + " table_id = " + data[i].table_id
                            + " ordere_id = " + data[i].ordere_id
                            + " accept_supplement=" + data[i].acceptSupplement
                            + " prepare=" + data[i].prepare
                            + " supplement=" + data[i].supplement
                            + ">" +
                            "<td>" + data[i].code + "</td>" +
                            "<td class='title'>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) + "</td>" +
                            "<td>" +
                            "<div id='custom-qty' class='custom-qty d-flex'>" +
                            "<input  type='number' disabled  class='custom-qty-input ' name='quantity=' value=" + data[i].quantity +
                            " step='1' min='1' max='100'>" +
                            // "<span class='plus'>+</span>" +
                            "</div>" +
                            "</td>" +
                            "<td>" + data[i].subTotal + "</td>" +
                            "<td class='text-center d-flex action'>" +
                            "<button class='btn btn-sm btn-warning printSubOrder mr-1' style='" + (!data[i].prepare ? "visibility:hidden;" : "") + "'>" +
                            "<i class='fas fa-print fa-1_5x' style='margin: 5px'></i>" +
                            "</button>" +
                            "<button class='btn btn-sm btn-danger delete'>" +
                            "<i class='far fa-trash-alt fa-1_5x' style='margin: 5px'></i>" +
                            "</button>" +
                            "</td>" +
                            //"<td class='text-center d-flex'>" +
                            // "<span class='fas fa-comment-dots commentSubOrdere' style='font-size:1.5em; cursor: pointer; margin-right: 10px;'></span>" +
                            //"<div id='custom-qty' class='custom-qty'>" +
                            // (data[i].quantity == 1 ? "<span class='minus d-none'>-</span>" : "<span class='minus'>-</span>") +
                            // (data[i].quantity == 1 ? "<span class='delete'><i class='far fa-trash-alt'></i></span>" : "<span class='delete d-none'><i class='far fa-trash-alt'></i></span>") +
                            // "<span class='plus'>+</span>" +
                            // "</div>" +
                            // "</td>" +
                            //"<td class='d-none'>" +
                               // "<textarea type='textarea' rows='1' cols='30' name='push_title'>" + data[i].subComment + "</textarea>" +
                            //"</td>" +
                            "</tr>")
                    }
                    // $("#orderDetail h3").text(JsTranslations.total + " " + formatAmount(tablePrice));
                    if (tableVatAmount > 0) {
                        var html = "<div class='order-totals'>" +
                            "<div class='total-ht'><strong>" + (JsTranslations.total_ht_label) + ":</strong> " + formatAmount(tableOrderePrice) + "</div>" +
                            "<div class='total-vat'><strong>" + (JsTranslations.total_tva_label) + ":</strong> " + formatAmount(tableVatAmount) + "</div>" +
                            "<div class='total-ttc font-weight-bold'><strong>" + (JsTranslations.total_ttc_label) + ":</strong> " + formatAmount(tableTotalTtc) + "</div>" +
                            "</div>";
                    } else {
                        var html = "<div class='order-totals'>" +
                            "<div class='total-ttc'>" + (JsTranslations.total) + " : " + formatAmount(tableTotalTtc) + "</div>" +
                            "</div>";
                    }
                    $("#orderDetail h3").html(html);
                }

                window.location = "#payOrderBtn";
                $("#divLoadingcms").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function updateSuborderQte(suborder_id, newQte, ordere_id, table_id) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateSubOrderQte&id=" + suborder_id + "&quantity=" + newQte,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    swal(JsTranslations.oops, JsTranslations.user_error, "error");

                } else {

                    table_id === 'null' ? getSubOrders(ordere_id) : getSubOrdersTable(table_id);
                }
                $("#divLoading").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function updateSuborderComment(suborder_id, comment) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateSubOrderComment&id=" + suborder_id + "&comment=" + comment,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    swal(JsTranslations.oops, JsTranslations.user_error, "error");
                } else {
                }
                $("#divLoading").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
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

    function updateOrderPayement(table_id, ordere_id) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateOrderPayementAndPrint&table_id=" + table_id + "&ordere_id=" + ordere_id,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error").then((willReady) => {
                        if (willReady) {
                            window.location = "checkoutPanel.php";
                        }
                    });;
                } else {
                    $("#divLoadingcms").addClass("d-none");
                    window.location = "checkoutPanel.php";
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }

     function updateOrderVat(ordere_id, vat_id) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateOrderVatID&id=" + ordere_id +  "&vat_id=" + vat_id,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
                // $("#orderDetail h4").addClass('d-none');
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    //After update order success, update suborders
                    
                    getSubOrders(data[0].id)
                
                    //********************************************* */

                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateOrderVat()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

     function updateTableVat(table_id, vat_id) {

        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateTableVatID&table_id=" + table_id +  "&vat_id=" + vat_id,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
                // $("#orderDetail h4").addClass('d-none');
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {

                         
                    getSubOrdersTable(data[0].table_id)
                
                    //********************************************* */

                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateTableVat()", "error");
                $("#divLoading").addClass("d-none");
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
                        window.location = "checkoutPanel.php";
                    } else {
                        if (!cancel_all) { // cancel_all variable used to determin whther the user want to cancel the order completely
                            table_id === 'null' ? getSubOrders(ordere_id) : getSubOrdersTable(table_id);
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

    function freeTable(table_id) {

        $.ajax({
            url: "php/JsonTable.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateTable&id=" + table_id + "&tableFree=1",
            beforeSend: function () {
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
            }
        });
    }

    function initHMI() {
        $("#ordersDiv").removeClass(ordersTableClasswithDétailTable);
        $("#ordersDiv").addClass(ordersTableClassAlone);
        //        $("#orderDetail").addClass("d-none");
    }

});