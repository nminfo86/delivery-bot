/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");

var lastSubOrder_id = 0;
var place = $(".searcheTable option:selected").text();
//*********************** ************** ********

$(document).ready(function () {


    $(".searcheTable").change(function () {
        place = $(".searcheTable option:selected").text();

    });


    $(document).on('click', '#testBtn', function () {
        search();
        $(".tv-cols").removeClass("d-none");
        $(".clock-center").remove();
        window.setInterval(function () {
            search();
        }, 6500);

        $(this).text("activated ..!");
    });



    function search() {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSubOrdersOfTv",
            beforeSend: function () {

            },
            success: function (data) {
                if (data.state === "f") {
                     
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    } else {
                      
                        $("#tv-new-orders-div h1").text("")
                        $("#tv-new-orders-div h2").text("")
                    }
                } else {

                    //If it is new Ready subOrder or it is the first time we show the Tv data
                    if ((data[0].id != lastSubOrder_id) || (lastSubOrder_id == 0)) {

                        //If the user did not select place to track order we show data of all tables and Emporter orders
                        if (place == JsTranslations.place) {

                            // $("#tv-new-orders-div h1").text(data[0].table_id == null ? "Emporter " + data[0].code : data[0].tableName)
                            // $("#tv-new-orders-div h2").text(data[0].title + (data[0].attributeValue != null ? " " + data[0].attributeValue : ''))
                            updateNewOrderDiv(data[0]);
                            lastSubOrder_id = data[0].id

                            $("#tv-old-orders-div").children().remove();
                            for (var i = 1; i < data.length; i++) {
                                updateOldOrdersDiv(data[i]);
                                // $("#tv-old-orders-div").append(
                                //     "<div>" +
                                //     "<h2>" + (data[i].table_id == null ? ("Emporter " + data[i].code) : data[i].tableName) + "</h2>" +
                                //     "<h4>" + data[i].title + (data[i].attributeValue != null ? " " + data[i].attributeValue : '') + "</h4>" +
                                //     "</div>"
                                // );
                            }
                            // updateSubOrderStatus(data[0].id, orderStateNotified, 0);
                            notifyUser();
                            //If user select to truck one table we show anly that table data
                        } else {

                            //If place is not emporter then we show only the specified table
                            if (place != JsTranslations.take_away) {
                                if (data[0].tableName == place) {
                                    // $("#tv-new-orders-div h1").text(data[0].table_id == null ? "Emporter " + data[0].code : data[0].tableName)
                                    // $("#tv-new-orders-div h2").text(data[0].title + (data[0].attributeValue != null ? " " + data[0].attributeValue : ''))
                                    updateNewOrderDiv(data[0]);
                                    lastSubOrder_id = data[0].id

                                    notifyUser();
                                }
                                $("#tv-old-orders-div").children().remove();
                                for (var i = 1; i < data.length; i++) {
                                    if (data[i].tableName == place) {
                                        updateOldOrdersDiv(data[i]);
                                        // $("#tv-old-orders-div").append(
                                        //     "<div>" +
                                        //     "<h2>" + (data[i].table_id == null ? ("Emporter " + data[i].code) : data[i].tableName) + "</h2>" +
                                        //     "<h4>" + data[i].title + (data[i].attributeValue != null ? " " + data[i].attributeValue : '') + "</h4>" +
                                        //     "</div>"
                                        // );
                                    }
                                }

                                //If place is emporter then we show only emporter subOrders
                            } else {
                                data[0].place = getPlaceValue(data[0].place, data[0].tableName, data[0].table_id);
                                if (data[0].place == JsTranslations.take_away) {
                                    // $("#tv-new-orders-div h1").text(data[0].table_id == null ? "Emporter " + data[0].code : data[0].tableName)
                                    // $("#tv-new-orders-div h2").text(data[0].title + (data[0].attributeValue != null ? " " + data[0].attributeValue : ''))
                                    updateNewOrderDiv(data[0]);
                                    lastSubOrder_id = data[0].id

                                    notifyUser();

                                }
                                $("#tv-old-orders-div").children().remove();
                                for (var i = 1; i < data.length; i++) {
                                    data[i].place = getPlaceValue(data[i].place, data[i].tableName, data[i].table_id);

                                    if (data[i].place == JsTranslations.take_away) {
                                        updateOldOrdersDiv(data[i]);
                                        // $("#tv-old-orders-div").append(
                                        //     "<div>" +
                                        //     "<h2>" + (data[i].table_id == null ? ("Emporter " + data[i].code) : data[i].tableName) + "</h2>" +
                                        //     "<h4>" + data[i].title + (data[i].attributeValue != null ? " " + data[i].attributeValue : '') + "</h4>" +
                                        //     "</div>"
                                        // );
                                    }
                                }
                            }
                        }
                    }
                }

            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - search()", "error");
                $("#loadingImage").addClass("d-none");
            }
        });
    }


    function updateSubOrderStatus(id, progression, print) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateSubOrderProgression&id=" + id + "&progression=" + progression
                + "&print=" + print,
            beforeSend: function () {

            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error")
                } else {
                    notifyUser();
                }
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - updateSubOrderStatus()", "error");
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }


    function updateNewOrderDiv(data) {
        $("#tv-new-orders-div h1").text(data.table_id == null ? JsTranslations.take_away+" " + data.code : data.tableName+" " + data.code)
        $("#tv-new-orders-div h2").text(data.title + (data.attributeValue != null ? " " + data.attributeValue : ''))

    }
    function updateOldOrdersDiv(data) {
        $("#tv-old-orders-div").append(
            "<div>" +
            "<h2>" + (data.table_id == null ? (JsTranslations.take_away+" " + data.code) : data.tableName+" " + data.code) + "</h2>" +
            "<h4>" + data.title + (data.attributeValue != null ? " " + data.attributeValue : '') + "</h4>" +
            "</div>"
        );
    }

    function notifyUser() {
        $("#mySound")[0].play();
        navigator.vibrate([400, 100, 250]);
    }
});