/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var searchForm = $('#search');
var orderForm = $('#orderForm');
var subOrdersTable = $('#subOrderstable');
var tablePrice = 0;
var subOrdersarray = new Array();

//*********************** ************** ********

$(document).ready(function () {

    // ============== Prepare Swal Custom qte input div to be placed in the swal modal =================
    var qteInputHtml =
        "<div id='sw-custom-qty' class='custom-qty d-flex justify-content-center flex-column'>" +
        "<div class='d-flex justify-content-center'>" +
        "<span class='sw-minus'>-</span>" +
        "<input type='number' class='custom-qty-input' name='quantity=' value='1' step='1' min='1' max='100'>" +
        "<span class='sw-plus'>+</span>" +
        "</div>" +
        "<div class='quick-qty-buttons d-flex flex-wrap justify-content-center mt-3'>" +
        "<div class='qty-preset-btn' data-qty='1'>01</div>" +
        "<div class='qty-preset-btn' data-qty='5'>05</div>" +
        "<div class='qty-preset-btn' data-qty='10'>10</div>" +
        "<div class='qty-preset-btn' data-qty='15'>15</div>" +
        "<div class='qty-preset-btn' data-qty='20'>20</div>" +
        "<div class='qty-preset-btn' data-qty='25'>25</div>" +
        "<div class='qty-preset-btn' data-qty='30'>30</div>" +
        "<div class='qty-preset-btn' data-qty='40'>40</div>" +
        "</div>" +
        "</div>";
    toggleCustomQteInputMinusPlus();

    // the CSS of the .quick-qty-buttons is in the file cmscss.css 



    // Add click handler for quick quantity buttons
    $(document).on('click', '.qty-preset-btn', function () {
        var qty = $(this).data('qty');
        $('.swal-content .custom-qty-input').val(qty);
        $('.qty-preset-btn').removeClass('active');
        $(this).addClass('active');
    });

    var qteInputDiv = document.createElement("div");
    qteInputDiv.innerHTML = qteInputHtml;

    // ========== End prepare swal custom qte div ==================

    // ===== Start Set order details height to active scrolle bar ===== 
    documentHeightOrdDet = document.body.clientHeight - document.body.clientHeight * 0.05;
    documentHeightArtMenu = document.body.clientHeight - document.body.clientHeight * 0.15;

    $("#containerMenu #orderDetail").css("height", documentHeightOrdDet + "px");
    $("#articlesMenu div[categoryid]").css("height", documentHeightArtMenu + "px");
    // ===== End Set order details height to active scrolle bar ===== 

    initHMI();

    $(document).on('click', '#showDetailToggler', function () {

        toggleOrderDetailDiv();

    });

    //Show category articles when user click on category menu
    $(document).on('click', '#categoriesMenu div', function () {

        $('#categoriesMenu').find("div").removeClass('active');
        $(this).addClass('active');
        var categoryId = $(this).attr("categoryId");

        $('#articlesMenu').find("div[categoryId]").addClass('d-none');
        $('#articlesMenu').find("div[categoryId='" + categoryId + "']").removeClass('d-none');

    });

    //Add object to order details table
    $(document).on('click', '.cardDiv', function () {
        var object_id = $(this).attr('object_id');
        var isSupplement = $(this).attr('is_supplement');
        var accept_supplement = $(this).attr('accept_supplement');
        var objectTitle = $(this).find('.card-footer').text();

        //check whether the object has attributes
        // jQuery .find return children of target element

        var html = $(this).find(".attribute_values").html();

        if (html == '') {

            if (isSupplement == '1') {

                createSubOrder(object_id, 'NULL', 1, 1);
            } else {
                // if (accept_supplement == '0') {
                //     swal({
                //         title: objectTitle,
                //         content: qteInputDiv,
                //         buttons: true,
                //         closeOnClickOutside: false,
                //         closeOnEsc: false,
                //     })
                //         .then((willStart) => {
                //             if (willStart) {
                //                 var qte = $(".swal-content").find("input").val();
                //                 createSubOrder(object_id, 'NULL', qte, 0);
                //             }
                //         });
                // } else {
                createSubOrder(object_id, 'NULL', 1, 0);
                // }
            }

        } else { //if Object has attributes

            //create AttributeValues div
            var attributesDiv = document.createElement("div");

            attributesDiv.innerHTML = html;

            // if object has attributes and it's a supplement 
            if (isSupplement == '1') {
                //if it is supplement we directly add it with the same attribute_id and quantity 
                //of last <tr> in subOrdersTable that accept supplements
                var attributeValue_id = subOrdersTable.find("tr[accept_supplement='1']").last().attr("attributeValue_id");

                if (attributeValue_id != undefined) {
                    createSubOrder(object_id, attributeValue_id, 1, 1);

                    //Add by nminfo to handle if it is a Restaurent not Pizzeria, 
                    //In Restaurents all categories are supplements so we accept to add supplements 
                    //that have attributes by showing attributes swal to user
                } else {

                    swal({
                        content: attributesDiv,
                        buttons: {
                            confirm: {
                                text: JsTranslations.validate_button,
                                value: "Ajouter",
                            },
                            cancel: {
                                text: JsTranslations.cancel_button
                            }
                        }
                    }).then((value) => {
                        switch (value) {
                            case "Ajouter":
                                var attributeValue_id = $(".swal-content").find("input:checked").val();

                                createSubOrder(object_id, attributeValue_id, 1, 0);
                                break;
                            default:
                        }
                    });
                }

                //if object has attributes and it's not a supplement we show attributes to user
            } else {
                swal({
                    content: attributesDiv,
                    buttons: {
                        confirm: {
                            text: JsTranslations.validate_button,
                            value: "Ajouter",
                        },
                        cancel: {
                            text: JsTranslations.cancel_button
                        }
                    }
                }).then((value) => {
                    switch (value) {
                        case "Ajouter":
                            var attributeValue_id = $(".swal-content").find("input:checked").val();
                            // if (accept_supplement == '0') {
                            //     swal({
                            //         title: objectTitle,
                            //         content: qteInputDiv,
                            //         buttons: true,
                            //         closeOnClickOutside: false,
                            //         closeOnEsc: false,

                            //     })
                            //         .then((willStart) => {
                            //             if (willStart) {
                            //                 var qte = $(".swal-content").find("input").val();
                            //                 createSubOrder(object_id, attributeValue_id, qte, 0);
                            //             }
                            //         });
                            // } else {
                            createSubOrder(object_id, attributeValue_id, 1, 0);
                            // }
                            break;
                        default:
                    }
                });
            }
        }
        // }
    });

    // Change attribute to attribute price when user click on Radio Button
    $(document).on('click', '#attribute_values input:radio', function () {

        $("#attribute_values h4").text($(this).attr("price") + " " + cmsCurrency);
    });

    //******* Qte minus, plus, delete and comment action buttons*********** */
    $(document).on('click', '.plus', function () {
        var suborder_id = $(this).closest("tr").attr('id');
        var customQteInput = $(this).closest('.custom-qty').find(".custom-qty-input");

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

        var subordere_id = $(this).closest("tr").attr('id');

        deleteSubOrder(subordere_id, false);
    });

    // Add click handler for custom quantity inputs
    $(document).on('click', '#subOrderstable .custom-qty-input', function (e) {
        var suborder_id = $(this).closest("tr").attr('id');
        var currentQty = parseInt($(this).val());

        // Clone the qte input div
        var modalQteDiv = document.createElement("div");
        modalQteDiv.innerHTML = qteInputHtml;

        // Set the initial value in the modal to match current quantity
        $(modalQteDiv).find('.custom-qty-input').val(currentQty);

        // Store the suborder_id directly on the #sw-custom-qty element
        $(modalQteDiv).find('#sw-custom-qty').attr('data-suborder-id', suborder_id);

        // Highlight the matching preset button if it exists
        $(modalQteDiv).find('.qty-preset-btn').removeClass('active');
        $(modalQteDiv).find('.qty-preset-btn[data-qty="' + currentQty + '"]').addClass('active');

        // Store the ID in a closure variable as backup
        var originalSuborderId = suborder_id;

        swal({
            title: JsTranslations.modal_title_update_qty,
            content: modalQteDiv,
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: JsTranslations.cancel_button

            },
            closeOnClickOutside: false,
            closeOnEsc: false,
        })
            .then((willUpdate) => {
                if (willUpdate) {
                    // Get suborder_id from the data attribute in the modal content
                    var storedSuborderId = $(".swal-content").find("#sw-custom-qty").attr('data-suborder-id');
                    var newQty = parseInt($(".swal-content").find(".custom-qty-input").val());

                    // Use the stored ID if available, otherwise fall back to the original captured ID
                    var finalSuborderId = storedSuborderId || originalSuborderId;

                    if (!finalSuborderId) {
                        console.error("Error: Could not determine suborder ID");
                        return;
                    }

                    if (newQty > 0 && newQty <= 100 && newQty !== currentQty) {
                        updateSuborderQte(finalSuborderId, newQty);
                    }
                }
            });
    });

    $(document).on('click', ".commentSubOrdere", function () {

        var id = $(this).closest("tr").attr("id");

        swal(JsTranslations.add_chef_comment, {
            content: "input",
            icon: "info",
            buttons: {
                confirm: JsTranslations.validate_button,
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

    $("#allOrdersDiv .searcheTable").change(function () {

        // var selectValue = $(this).val() != 'null' ? $("#allOrdersDiv .searcheTable option:selected").text() : "";
        var selectValue = $("#allOrdersDiv .searcheTable option:selected").val();
        // var selectValue2 = $("#allOrdersDiv .searcheTable option:selected").val();

        // alert (selectValue2)
        if (selectValue == orderPlaceCarryWith) {
            orderForm.find("input[name='place']").attr('value', orderPlaceCarryWith);
            orderForm.find("input[name='table_id']").attr('value', 'NULL');
        } else {
            orderForm.find("input[name='place']").attr('value', orderPlaceOnTable);
            orderForm.find("input[name='table_id']").attr('value', $(this).val());
            orderForm.find("input[name='table_id']").attr
                ('tablecode', $(this).find('option:selected').attr('tablecode'));
        }
    });
    //
    //************************************************************* */
    $(document).on('click', '.addVat', function () {

        var elemnts = subOrdersTable.find("tbody tr[id]").each(function () {

        }).get();

        if (elemnts.length > 0) {
            var html = $(document).find(".vatDiv").html();

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
                        updateOrderVat(vat_id)
                        break;
                    case "CancelVat":
                        // Handle cancel VAT action
                        updateOrderVat("NULL");
                        break;
                    default:
                }
            });
        }
    });

    $(document).on('click', '.printChefAndClient', function () {

        subOrdersarray = [];
        //Fill subOrdersArray
        var elemnts = subOrdersTable.find("tbody tr[id]").each(function () {
            var tmp = {};
            tmp.id = $(this).attr("id");
            tmp.quantity = $(this).find(".custom-qty-input").val();
            tmp.comment = $(this).find("textarea").val();
            subOrdersarray.push(tmp);
        }).get();

        if (elemnts.length > 0) {

            var tableCode = orderForm.find("input[name='table_id']").attr("tablecode");
            if ((orderForm.find("input[name='place']").val() == orderPlaceOnTable) &&
                (orderForm.find("input[name='table_id']").val() != "NULL")) {
                getTableStatus(tableCode, true, true);
            } else {
                updateOrder(true, true);
            }
        }

    });

    $(document).on('click', '.printChefOnly', function () {

        subOrdersarray = [];
        //Fill subOrdersArray
        var elemnts = subOrdersTable.find("tbody tr[id]").each(function () {
            var tmp = {};
            tmp.id = $(this).attr("id");
            tmp.quantity = $(this).find(".custom-qty-input").val();
            tmp.comment = $(this).find("textarea").val();
            subOrdersarray.push(tmp);
        }).get();

        if (elemnts.length > 0) { //subOrders table is not empty

            if ($("#sessionRole").val() == roleWaiter) { //If role is waiter we check that table is selected
                if ($("#allOrdersDiv .searcheTable").val() != "NULL") {
                    var tableCode = orderForm.find("input[name='table_id']").attr("tablecode");
                    if ((orderForm.find("input[name='place']").val() == orderPlaceOnTable) &&
                        (orderForm.find("input[name='table_id']").val() != "NULL")) {
                        getTableStatus(tableCode, true, false);
                    } else {
                        updateOrder(true, false);
                    }

                } else {
                    swal({
                        title: JsTranslations.modal_title_place,
                        text: JsTranslations.msgPlaceRequired,
                        icon: "warning",
                        buttons: {
                            confirm: JsTranslations.validate_button
                        }
                    });
                }
                //End check user Role
            } else {
                var tableCode = orderForm.find("input[name='table_id']").attr("tablecode");
                if ((orderForm.find("input[name='place']").val() == orderPlaceOnTable) &&
                    (orderForm.find("input[name='table_id']").val() != "NULL")) {
                    getTableStatus(tableCode, true, false);
                } else {
                    updateOrder(true, false);
                }
            }

        }

    });

    $(document).on('click', '.cancelOrdere', function () {

        swal({
            title: JsTranslations.modal_title_cancel,
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
                        deleteSubOrder($(this).attr("id"), subOrdersTable.attr('ordere_id'), true);

                    });
                }
            });
    });

    getSubOrders(subOrdersTable.attr('ordere_id'));

    //****************** FUNCTIONS *********************//    
    function createSubOrder(object_id, attributeValue_id, qte, isSupplement) {

        var data = ''
        if (attributeValue_id === 'NULL') {
            data = "function=createSubOrder"
                + "&object_id=" + object_id
                + "&quantity=" + qte
        } else {
            data = "function=createSubOrder"
                + "&object_id=" + object_id
                + "&attributeValue_id=" + attributeValue_id
                + "&quantity=" + qte;
        }

        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: data,
            beforeSend: function () {
                if (isMediaQuery("991px")) {
                    $("#divLoadingcms").removeClass("d-none");
                }
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        swal(JsTranslations.oops, JsTranslations.msgChoixDejaAjouté, "warning");
                    } else {
                        swal(JsTranslations.oops, JsTranslations.user_error, "error");
                    }

                } else {

                    if (isSupplement) {

                        var suborder_id = subOrdersTable.find("tr[accept_supplement='1']").last().attr("id")

                        if (suborder_id != undefined) {
                            addSupplement(data[0].ordere_id, suborder_id, data[0].object_id, data[0].id);
                        }
                    }
                    getSubOrders(data[0].ordere_id)
                    $(".swal-content").find("input").val(1);

                }

                //If is Mobile we show swal confirmation for better UX
                if (isMediaQuery("991px")) {
                    window.setTimeout(function () {
                        $("#divLoadingcms").addClass("d-none");
                    }, 225);
                } else {
                    $("#divLoadingcms").addClass("d-none");
                }

            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - createSubOrder()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }
    // 

    //Fill suborders of ordere in subordersTable
    function getSubOrders(ordere_id) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSubOrdersOfOrderLabel&ordere_id=" + ordere_id,
            beforeSend: function () {

                // $("#divLoadingcms").removeClass("d-none");
                subOrdersTable.find("tr:gt(0)").remove();
                $("#orderDetail h4").text(JsTranslations.total + " : 0 " + cmsCurrency);
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {

                    subOrdersTable.attr('ordere_id', data[0].ordere_id);
                    $("#orderForm input[name='id']").attr('value', data[0].ordere_id);

                    for (var i = 0; i < data.length; i++) {
                        subOrdersTable.append(
                            "<tr id = " + data[i].id +
                            " ordere_id = " + data[i].ordere_id +
                            " accept_supplement=" + data[i].acceptSupplement +
                            " supplement=" + data[i].supplement +
                            " attributeValue_id=" + data[i].attributeValue_id +
                            " quantity=" + data[i].quantity +
                            ">" +
                            "<td class='title'>" + data[i].title
                            + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) +
                            // + (data[i].attributeValue == null || data[i].supplement == '1' ? "" : " " + data[i].attributeValue) +
                            "</td>" +
                            "<td>" +
                            "<div id='custom-qty' class='custom-qty d-flex'>" +
                            (data[i].quantity == 1 ? "<span class='minus'>-</span>" : "<span class='minus'>-</span>") +
                            "<input type='number'  class='custom-qty-input' name='quantity=' value=" + data[i].quantity +
                            " step='1' min='1' max='100' style='cursor: pointer;'>" +
                            "<span class='plus'>+</span>" +
                            "</div>" +
                            "</td>" +
                            "<td>" + data[i].subTotal + "</td>" +
                            "<td class='comment text-center d-flex '>" +
                            "<div id='custom-qty' class='custom-qty mr-1 '>" +
                            (data[i].quantity == 1 ? "<span class='delete'><i class='far fa-trash-alt'></i></span>" : "<span class='delete'><i class='far fa-trash-alt'></i></span>") +
                            "</div>" +
                            (data[i].supplement == 0 ? "<span class='fas fa-comment-dots commentSubOrdere' style='font-size:1.5em; cursor: pointer;'></span>" : "") +
                            "</td>" +
                            "<td class='d-none'>" +
                            "<textarea type='textarea' rows='1' cols='30' name='push_title'>" + data[i].subComment + "</textarea>" +
                            "</td>" +
                            "</tr>")

                    }
                    // $("#orderDetail h4").text( JsTranslations.total + " : " + Intl.NumberFormat().format(data[0].orderePrice) + " " + cmsCurrency);
                    
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
                        
                        $("#orderDetail h4").html(html);
                    } else {
                        $("#orderDetail h4").text(JsTranslations.total + " : " + formatAmount(data[0].totalTtc));
                    }
                }

            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - getSubOrders(ordere_id)", "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function getTableStatus(tableCode, printChef, printClient) {

        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllOrdersOfTable&tableCode=" + tableCode,
            beforeSend: function () {
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message == noDataFound) {
                        updateOrder(printChef, printClient);
                    } else {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {
                    swal({
                        title: JsTranslations.modal_title_becarefull,
                        text: (JsTranslations.unpaid_orders_table_warning.replace('{tableName}', data[0].tableName)),
                        //                        text: ("Voulez vous Ajouter cette commande à cette table ?\n هل تريد إضافة هاذا الطلب على حساب هذه الطاولة ؟ "),
                        icon: "warning",
                        buttons: {
                            confirm: JsTranslations.validate_button,
                            cancel: JsTranslations.cancel_button

                        },
                    })
                        .then((willReady) => {
                            if (willReady) {

                                updateOrder(printChef, printClient);
                            }
                        });
                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - getTableStatus()", "error");
            }
        });
    }

    function updateOrder(printChef, printClient) {

        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateOrder&" + orderForm.serialize(),
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
                // $("#orderDetail h4").addClass('d-none');
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    //After update order success, update suborders
                    updateSubOrders(subOrdersarray);
                    if (orderForm.find("input[name='table_id']").val() > 0) {
                        ReserveTable(orderForm.find("input[name='table_id']").val());
                    }
                    $("#divLoading").addClass("d-none");


                    if (printClient === true) {
                        updateOrderPayement(data[0].table_id, data[0].id, printChef)
                    }
                    if ((printChef === true) && (printClient === false)) {
                        var subOrdArr = JSON.stringify(subOrdersarray);
                        printChefSubOrders(subOrdArr, orderStateStarted);
                    }
                    //********************************************* */

                    $(".swal-title").css("color", "orange");
                    $("#orderDetail h4").text("Total : 0 " + cmsCurrency);
                    toggleOrderDetailDiv();
                    initHMI();

                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateOrder()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function updateOrderVat(vat_id) {

        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateOrderVatID&id=" + subOrdersTable.attr('ordere_id') + "&vat_id=" + vat_id,
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

                    // $(".swal-title").css("color", "orange");
                    // toggleOrderDetailDiv();
                    // initHMI();

                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateOrderVat()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function updateOrderPayement(table_id, ordere_id, printChef) {
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
                            window.location = "checkoutMenu.php";
                        }
                    });
                } else {
                    $("#divLoadingcms").addClass("d-none");
                    if (printChef) {
                        var subOrdArr = JSON.stringify(subOrdersarray);
                        printChefSubOrders(subOrdArr, orderStateStarted);
                    }
                }
                subOrdersarray.length = 0;
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - updateOrderPayementAndPrint()", "error");
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
                    swal({
                        title: JsTranslations.order_validation,
                        text: JsTranslations.msgCommandeTableMenu,
                        icon: "success",
                        buttons: {
                            confirm: JsTranslations.validate_button,
                        }
                    })
                }
                subOrdersarray.length = 0;
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - printChefSubOrders()", "error");
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }
    
    function updateSubOrders(subordersArray) {

        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateSubOrders&array=" + JSON.stringify(subordersArray),
            beforeSend: function () {
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message + " -updateSubOrders() ", "error");
                } else {
                    //Do nothing
                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateSubOrders()", "error");
            }
        });
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
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateSuborderQte()", "error");
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
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - updateSuborderComment()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function deleteSubOrder(id, cancel_all) {

        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteSubOrder&id=" + id + "&deleteOrder=1",
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");

            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    if (data.message === last_subOrder) {
                        subOrdersTable.find("tr:gt(0)").remove();
                        $("#orderDetail h4").text("Total : 0 " + cmsCurrency);
                    } else {
                        if (!cancel_all) {
                            getSubOrders(subOrdersTable.attr('ordere_id'))
                        }
                    }
                }
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - deleteSubOrder()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function addSupplement(ordere_id, suborder_id, supplementObject_id, supplementSuborderID) {

        $.ajax({
            url: "php/JsonSupplement.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=createSupplement&ordere_id=" + ordere_id + "&suborder_id=" + suborder_id
                + "&supplementObject_id=" + supplementObject_id + "&supplementSuborderID=" + supplementSuborderID,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        swal({
                            title: JsTranslations.oops,
                            text: JsTranslations.msgChoixDejaAjouté,
                            icon: "warning",
                            buttons: {
                                confirm: JsTranslations.validate_button,
                            }
                        });
                    } else {
                        swal(JsTranslations.oops, JsTranslations.user_error, "error");
                    }

                } else {
                    //Do Nothing
                    //                    createSubOrder(true);
                }
                $("#divLoading").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - addSupplement()", "error");
                $("#divLoading").addClass("d-none");
            }

        });
    }

    function ReserveTable(table_id) {

        $.ajax({
            url: "php/JsonTable.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateTable&id=" + table_id + "&tableFree=0",
            beforeSend: function () {
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                }
            },
            error: function (jqXHR, exception) {
                swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception) + " - ReserveTable()", "error");
            }
        });
    }


    function toggleOrderDetailDiv() {

        if (isMediaQuery("991px")) {
            if ($("#containerMenu #orderDetail").css('left') === '1px') {
                $("#containerMenu #orderDetail").css('left', '-100%');
            } else {
                $("#containerMenu #orderDetail").css('left', '1px');
            }
        }
    }

    function initHMI() {

        //access controle for waiter
        if ($("#sessionRole").val() == roleWaiter) {
            $(".printChefAndClient").addClass("d-none");
            $(".addVat").addClass("d-none");
            $(".btn-toolbar").addClass("justify-content-around");

        }
        subOrdersTable.find("tr:gt(0)").remove();
        // $("#orderDetail h4").addClass('d-none');

        $("#allOrdersDiv .searcheTable").val('NULL');
    }


});