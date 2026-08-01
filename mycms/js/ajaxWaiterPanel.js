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
var ordersTableClasswithDétailTable = "col-lg-5 col-md-4 col-sm-12 col-xs-12";
var subOrdersarray = new Array();


//*********************** ************** ********

$(document).ready(function () {
    initHMI();


 // Auto-refresh the page every 30 seconds, reset timer on user interaction
 (function() {
    var refreshInterval = chefPanelRefrechTimer; // 10 seconds
    var refreshTimeout;
    function scheduleRefresh() {
        clearTimeout(refreshTimeout);
        refreshTimeout = setTimeout(function() {
            window.location.reload();
        }, refreshInterval);
    }
    ['mousemove', 'keydown', 'click', 'focus'].forEach(function(event) {
        window.addEventListener(event, scheduleRefresh);
    });
    scheduleRefresh();
})();



    //Search IN START SUBORDERS TABLE
    $("#searchInput").keyup(function () {
        if ($("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()), orderStateReady);
            }
        }
    });

    $("#searchButton").click(function () {
        if (!$("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()), orderStateReady);
            }
        }
    });

    $("#ordersDiv .searcheTable").change(function () {
        //Hide Order Details when search by place
        $("#orderDetail").addClass("d-none");
        initHMI();
        var query = $(this).val() != "NULL" ? $("#ordersDiv .searcheTable option:selected").val() : "";
        search(query, 0);
    });

    //******* Qte minus, plus, delete and comment action buttons*********** */
    $(document).on('click', '.plus', function () {
        var suborder_id = $(this).closest("tr").attr('id');
        var customQteInput = $(this).closest('.custom-qty').find(".custom-qty-input");

        // Change Qte input value
        // customQteInput.val(parseInt(customQteInput.val()) + 1);

        //Show minus span and hide delete
        // $(this).closest("tr").find(".delete").addClass("d-none");
        // $(this).closest("tr").find(".minus").removeClass("d-none");


        //Prevent input more than 100 qte
        if (customQteInput.val() > 100) {
            customQteInput.val(100);
        }
        updateSuborderQte(suborder_id, parseInt(customQteInput.val()) + 1);
    });

    $(document).on('click', '.minus', function () {
        var suborder_id = $(this).closest("tr").attr('id');
        var customQteInput = $(this).closest('.custom-qty').find(".custom-qty-input");

        // Change Qte input value
        // customQteInput.val(parseInt(customQteInput.val()) - 1);


        //Prevent input less than 1 qte
        if (customQteInput.val() == 0) {
            customQteInput.val(1);
        }

        updateSuborderQte(suborder_id, parseInt(customQteInput.val()) - 1);
    });

    $(document).on('click', '.delete', function () {

        var suborder_id = $(this).closest("tr").attr("id");
        var ordere_id = $(this).closest("tr").attr("ordere_id");
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
                    deleteSubOrder(suborder_id, ordere_id, false)
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
        var ordere_id = $(this).closest("tr").attr("id");
        getSubOrders(ordere_id);

        //css color for table row management
        allOrdersTable.find("tr[class='table-info']").removeClass("table-info");
        $(this).closest("tr").addClass("table-info");
    });

    $(document).on('click', '.validOrderBtn', function () {
        var ordere_id = $(this).attr("id");
        var orderdetails = $("#orderDetail h4").text();
        // subOrdersarray = {};
        subOrdersTable.find("tbody tr[id]").each(function () {
            var tmp = {};
            tmp.id = $(this).attr("id");
            tmp.quantity = $(this).find(".custom-qty-input").val();
            tmp.comment = $(this).find("textarea").val();
            subOrdersarray.push(tmp);
        })
        updateOrderValidation(ordere_id, 1);

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
                    //                        cancelOrder(ordere_id);
                    subOrdersTable.find("tr:gt(0)[id]").each(function () {
                        deleteSubOrder($(this).attr("id"), $(this).attr("ordere_id"), true);

                    });
                }
            });
    });

    search("", 0);

    //****************** FUNCTIONS *********************//
    function search(query, valid) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllOrdersOfDayByValidation&search=" + query + "&valid=" + valid,
            beforeSend: function () {

                $("#divLoadingcms").removeClass("d-none");
                allOrdersTable.find("tr").remove();

            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                     // Set count to 0 if failed
                     $("#orderCountWaiterPanel").text(0);
                } else {
                    var uniqueIds = [];
                    for (var i = 0; i < data.length; i++) {

                        allOrdersTable.append(
                            "<tr id = " + data[i].id + ">" +
                            "<td class ='td-getDetails'>" +
                            "<button class='btn btn-sm btn-info getDetails margin-right-1em'>" +
                            "<i class='fa fa-bars fa-lg' style='margin-right: 5px'></i>" +
                            "</button>" +
                            "</td>" +
                            "<td class='td-tableName'>" + (data[i].tableName == null ? "Code: " + data[i].code : data[i].tableName) + "</td>" +
                            "<td>" + (data[i].place === orderPlaceCarryWith ? JsTranslations.take_away : JsTranslations.on_table) + "</td>" +

                            "</tr>");

                             // Collect unique suborder ids
                             if (uniqueIds.indexOf(data[i].id) === -1) {
                                uniqueIds.push(data[i].id);
                            }
                    }
                     // Show the number of unique suborders in the badge
                     $("#orderCountWaiterPanel").text(uniqueIds.length);
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#orderCountWaiterPanel").text(0);
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }
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
                    
                     var place= getPlaceValue(data[0].place, data[0].tableName, data[0].table_id);
                    $("#orderDetail h4").text(JsTranslations.order + " " + (data[0].tableName == null ? data[0].code  + " " + place : data[0].tableName))

                    $(".validOrderBtn").attr("id", data[0].ordere_id);
                    $(".cancelOrderBtn").attr("id", data[0].ordere_id);

                    for (var i = 0; i < data.length; i++) {

                        subOrdersTable.append(
                            "<tr id = " + data[i].id
                            + " table_id = " + data[i].table_id
                            + " ordere_id = " + data[i].ordere_id
                            + " accept_supplement=" + data[i].acceptSupplement
                            + " supplement=" + data[i].supplement
                            + ">" +
                            "<td class='title'>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) + "</td>" +
                            "<td>" +
                            "<div id='custom-qty' class='custom-qty d-flex'>" +
                            (data[i].quantity == 1 ? "<span class='minus d-none'>-</span>" : "<span class='minus'>-</span>") +
                            (data[i].quantity == 1 ? "<span class='delete'><i class='far fa-trash-alt'></i></span>" : "<span class='delete d-none'><i class='far fa-trash-alt'></i></span>") +
                            "<input  type='number' disabled  class='custom-qty-input ' name='quantity=' value=" + data[i].quantity +
                            " step='1' min='1' max='100'>" +
                            "<span class='plus'>+</span>" +
                            "</div>" +
                            "</td>" +
                            "<td>" + data[i].subTotal + "</td>" +
                            "<td class='comment text-center'>" +
                            "<span class='fas fa-comment-dots commentSubOrdere' style='font-size:1.5em; cursor: pointer; margin-right: 10px;'></span>" +
                            "</td>" +
                            "<td class='d-none'>" +
                            "<textarea type='textarea' rows='1' cols='30' name='push_title'>" + data[i].subComment + "</textarea>" +
                            "</td>" +
                            "</tr>")
                    }
                    $("#orderDetail h3").text(JsTranslations.total +" : " + formatAmount(data[0].orderePrice));
                }
                window.location = "#validOrderBtn";
                $("#divLoadingcms").addClass("d-none");

                // Fill supplements
                //                subOrdersTable.find("tbody  > tr[id]").each(function () {
                //                    fillSupplements($(this).attr("id"));
                //                });
                //
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function fillSupplements(suborder_id) {
        $.ajax({
            url: "php/JsonSupplement.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSupplementsOfSuborder&suborder_id=" + suborder_id,
            beforeSend: function () {
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message == noDataFound) {
                    } else {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {
                    subOrdersTable.find("tbody  > tr[id='" + data[0].suborder_id + "']").after(
                        "<tr class='supplement' style='display: contents;'>" +
                        "<td>" + createSupplementsNode(data) +
                        "</td>" +
                        "</tr>");
                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoading").addClass("d-none");
            }
        });

    }

    function createSupplementsNode(data) {
        var iTageContent = JsTranslations.supplements + " : " + "<br>";
        for (var i = 0; i < data.length; i++) {
            if ((data.length == 1) || (i == data.length - 1)) {
                iTageContent = iTageContent + data[i].quantity + " " + data[i].title;
            } else {
                iTageContent = iTageContent + data[i].quantity + " " + data[i].title + "<br>";
            }
        }
        return "<i>" + iTageContent + "</i>";
    }

    function updateSuborderQte(suborder_id, newQte) {
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

                    getSubOrders(data[0].ordere_id);
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

    function updateOrderValidation(id, valid) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateOrder&id=" + id + "&valid=" + valid,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    $("#divLoadingcms").addClass("d-none");
                    var subOrdArr = JSON.stringify(subOrdersarray);
                    swal({
                        title: JsTranslations.code +" : " + data[0].code,
                        text: JsTranslations.msgCommandeTableMenu,
                        icon: "success",
                        buttons: {
                            catch: {
                                text: JsTranslations.e_ticket,
                                value: "ImpChef",
                                className: "btn-success",
                            },
                        },
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                    })
                        .then((value) => {
                            switch (value) {
                                case "ImpChef":
                                    printChefSubOrders(subOrdArr, orderStateStarted);
                                    break;
                                default:
                            }
                        });
                    //********************************************* */






                    //                                });
                }
                $("#divLoadingcms").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
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
                    window.location = "waiterPanel.php";
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function cancelOrder(id) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=cancelOrder&id=" + id,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    $("#divLoadingcms").addClass("d-none");

                    window.location = "waiterPanel.php";
                    //                                });
                }
                $("#divLoadingcms").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }

    function deleteSubOrder(id, ordere_id, cancel_all) {

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
                        window.location = "waiterPanel.php";
                    } else {
                        if (!cancel_all) { // cancel_all variable used to determin whther the user want to cancel the order completely
                            getSubOrders(ordere_id);
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

    function initHMI() {
        $("#ordersDiv").removeClass(ordersTableClasswithDétailTable);
        $("#ordersDiv").addClass(ordersTableClassAlone);
        //        $("#orderDetail").addClass("d-none");
    }

});