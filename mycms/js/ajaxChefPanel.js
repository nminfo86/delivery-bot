/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var searchFormStart = $("#searchStart");
var searchFormReady = $("#searchReady");
var startTable = $('#startTable');
var readyTable = $('#readyTable');
var supplementsNode = '';
//*********************** ************** ********

$(document).ready(function () {

    //Search IN START SUBORDERS TABLE
    // $("#searchInputStart").keyup(function () {
    //     if ($("#searchStartCheckBox").is(':checked')) {
    //         if (validateForm(searchFormStart)) {
    //             searchValid($.trim($("#searchInputStart").val()), orderStateValid);
    //         }
    //     }
    // });

    // $("#searchButtonStart").click(function () {
    //     if (!$("#searchStartCheckBox").is(':checked')) {
    //         if (validateForm(searchFormStart)) {
    //             searchValid($.trim($("#searchInputStart").val()), orderStateValid);
    //         }
    //     }
    // });

    // $("#rePrintBtn").click(function () {
    //     $("#rePrintTableDiv").removeClass("d-none");
    //     $("#ReadyTableDiv").addClass("d-none");
    // });

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


    $("#searchForm .searcheTable").change(function () {
        var query = $(this).val() != "NULL" ? $("#searchForm .searcheTable option:selected").val() : "";
        // searchValid(query, orderStateValid);
        searchStarted(query, orderStateStarted);
    });
    $("#searchForm .searcheCategory").change(function () {
        var query = $(this).val() != 0 ? $("#searchForm .searcheCategory option:selected").text() : "";
        //searchValid(query, orderStateValid);
        searchStarted(query, orderStateStarted);
    });

    //Update suborder to started 
    $(document).on('click', '#startTable tr', function () {
        var id = $(this).attr("id");
        var comment = $(this).find(".td-comment").text();
        var emplacement = $(this).find(".td-tableName").text();
        var message = "(" + $(this).find(".td-qte").text() + ")" + " "
            + $(this).find(".td-title").clone().children().remove().end().text(); + " ";
        var supplements = $(this).find(".td-title i").text();
        // var place = (emplacement.startsWith("T") ? emplacement : ($(this).find(".td-code").text()));
        // var qte = $(this).find(".td-qte").text();
        // var article = $(this).find(".td-title").clone().children().remove().end().text(); //.clone().children().remove().end() to get only text of <td> without <i> 

        swal({
            title: emplacement,
            text: (message),
            icon: "info",
            buttons: {
                confirm: JsTranslations.validate_button,
                cancel: JsTranslations.cancel_button
            }
        })
            .then((willStart) => {
                if (willStart) {
                    updateSubOrderStatus(id, orderStateStarted, 1);
                }
            });
        $(".swal-text").css("font-size", "x-large");
        $(".swal-text").append("<i style='font-size: 0.80em; color : #17a2b8;'><br>" + supplements + "</i>");
        $(".swal-text").append("<p style='margin-top:5px; font-weight:700;color : Orange; font-size: 1em;'>"
            + (comment == '' ? '' : "<i class='far fa-comments' style='font-size: 1.3em;'></i> " + comment) + "</p>");
    });

    //Update suborder to ready 
    $(document).on('click', '#readyTable tr', function () {
    var id = $(this).attr("id");
    var comment = $(this).find(".td-comment").text();
    var emplacement = $(this).find(".td-tableName").text();
    var message = "(" + $(this).find(".td-qte").text() + ")" + " "
        + $(this).find(".td-title").clone().children().remove().end().text(); + " ";
    var supplements = $(this).find(".td-title i").text();

    function showSwal() {
        swal({
            title: emplacement,
            text: (message),
            icon: "success",
            buttons: {
                cancel: {
                    text: JsTranslations.cancel_button,
                    value: null,
                    visible: true,
                    className: "",
                },
                Reprint: {
                    text: JsTranslations.modal_reprint_btn,
                    value: "Reprint",
                    className: "btn-warning",
                },
                Ready: {
                    text: JsTranslations.modal_ready_btn,
                    value: "Ready",
                    className: "btn-success",
                },
            },
        }).then((value) => {
            switch (value) {
                case "Ready":
                    updateSubOrderStatus(id, orderStateReady, 0);
                    break;
                case "Reprint":
                    rePrint(id);
                    showSwal(); // re-show the same swal
                    break;
                default:
                    break;
            }
        });

        $(".swal-text").css("font-size", "x-large");
        $(".swal-text").append("<i style='font-size: 0.80em; color : #17a2b8;'><br>" + supplements + "</i>");
        $(".swal-text").append("<p  style='margin-top:5px; font-weight:700;color : Orange; font-size: 1em;'>"
            + (comment == '' ? '' : "<i class='far fa-comments' style='font-size: 1.3em;'></i> " + comment) + "</p>");
    }

    showSwal();
});

    //Reprint label
    // $(document).on('click', '#rePrintTable tr', function () {
    //     var id = $(this).attr("id");
    //     var comment = $(this).find(".td-comment").text();
    //     var emplacement = $(this).find(".td-tableName").text();
    //     var message = "(" + $(this).find(".td-qte").text() + ")" + " "
    //         + $(this).find(".td-title").clone().children().remove().end().text(); + " ";
    //     var supplements = $(this).find(".td-title i").text();
    //     var place = (emplacement.startsWith("T") ? emplacement : ($(this).find(".td-code").text()));
    //     var qte = $(this).find(".td-qte").text();
    //     var article = $(this).find(".td-title").clone().children().remove().end().text(); //.clone().children().remove().end() to get only text of <td> without <i> 


    //     swal({
    //         title: emplacement,
    //         text: (message),
    //         icon: "success",
    //         buttons: true,
    //     })
    //         .then((willReady) => {
    //             if (willReady) {
    //                 rePrint(place, qte, article, supplements);
    //             }
    //         });
    //     $(".swal-text").css("font-size", "x-large");
    //     $(".swal-text").append("<i style='font-size: 0.80em; color : #17a2b8;'><br>" + supplements + "</i>");
    //     $(".swal-text").append("<p  style='margin-top:5px; font-weight:700;color : Orange; font-size: 1em;'>"
    //         + (comment == '' ? '' : "<i class='far fa-comments' style='font-size: 1.3em;'></i> " + comment) + "</p>");
    // });

    //searchValid("", orderStateValid);
    searchStarted("", orderStateStarted);
    //****************** FUNCTIONS *********************//
    function searchValid(query, status) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllSubOrdersOfDay&search=" + query + "&status=" + status + "&orderBy=ASC",
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

                startTable.find("tr").remove();
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {
                    for (var i = 0; i < data.length; i++) {

                        if ((i != 0) && (data[i].id == data[i - 1].id)) { //important 
                            //This tests whether suborder have more than one supplement, 
                            //if yes we only append supplement to etxtarea and do not add it to startTable

                            startTable.find("tr[id='" + data[i - 1].id + "'] .td-title i").append(
                                (data[i].suplTitle == null ? '' : ("-" + data[i].suplQuantity + data[i].suplTitle + "<br>"))
                            )
                        } else {
                            startTable.append(
                                "<tr id = " + data[i].id + ">" +
                                "<td class='td-title'>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) +
                                "<i>" + (data[i].suplTitle == null ? '' : ("-" + data[i].suplQuantity + data[i].suplTitle)) + "</i>" +
                                "</td>" +
                                 "<td class='td-qte'>" + data[i].quantity + "</td>" +
                                "<td class='td-tableName'>" + (data[i].tableName == null ? data[i].place : data[i].tableName) + "</td>" +                          
                                "<td class='td-code d-none'>" + data[i].code + "</td>" +
                                "<td class='td-supplement d-none'>" +
                                (data[i].suplTitle == null ? '' : (data[i].suplQuantity + " " + data[i].suplTitle + "<br>")) +
                                "</td>" +
                                "<td class='td-comment'>" +
                                data[i].subComment +
                                "</td>" +
                                "</tr>");
                        }
                    }
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function searchStarted(query, status) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllSubOrdersOfDay&search=" + query + "&status=" + status + "&orderBy=ASC",
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

                readyTable.find("tr").remove();
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                    // Set count to 0 if failed
                    $("#suborderCountChefPanel").text(0);
                } else {
                    var uniqueIds = [];
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].suplQuantity == '1') {
                            data[i].suplQuantity = ''
                        }
                        if ((i !== 0) && (data[i].id === data[i - 1].id)) { //important 
                            //This tests whether suborder have more than one supplement, 
                            //if yes we only append supplement to etxtarea and do not add it to startTable

                            readyTable.find("tr[id='" + data[i - 1].id + "'] .td-title i").append(
                                (data[i].suplTitle == null ? '' : (" -" + data[i].suplQuantity + data[i].suplTitle + "<br>"))
                            )
                        } else {
                             data[i].place = getPlaceValue(data[i].place, data[i].tableName, data[i].table_id);

                            readyTable.append(
                                "<tr id = " + data[i].id + ">" +
                                "<td class='td-title'>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) +
                                "<i>" + (data[i].suplTitle == null ? '' : (" -" + data[i].suplQuantity + data[i].suplTitle)) + "</i>" +
                                "</td>" +
                                 "<td class='td-qte'>" + data[i].quantity + "</td>" +
                                "<td class='td-tableName'>" + (data[i].tableName == null ? data[i].place : data[i].tableName) + "</td>" +                          
                                "<td class='td-code d-none'>" + data[i].code + "</td>" +
                                // "<td class='td-supplement d-none'>" +
                                // (data[i].suplTitle == null ? '' : (data[i].suplQuantity + " " + data[i].suplTitle + "<br>")) +
                                // "</td>" +
                                "<td class='td-comment'>" +
                                data[i].subComment +
                                "</td>" +
                                "</tr>");
                            // Collect unique suborder ids
                            if (uniqueIds.indexOf(data[i].id) === -1) {
                                uniqueIds.push(data[i].id);
                            }
                        }
                    }
                    // Show the number of unique suborders in the badge
                    $("#suborderCountChefPanel").text(uniqueIds.length);
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#suborderCountChefPanel").text(0);
                $("#divLoadingcms").addClass("d-none");
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
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error").then((willReady) => {
                        if (willReady) {
                            window.location = "chefPanel.php";
                        }
                    });
                } else {
                    //                    $("#divLoadingcms").addClass("d-none");

                    window.location = "chefPanel.php";
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
                            // Do nothing here
                            // window.location = "chefPanel.php";
                        }
                    });
                } else {
                    // Do nothing here
                    // window.location = "chefPanel.php";
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }

    function initTable(table) {

        table.find("tr[class='info']").removeClass("info");
        table.find("tr[class='success']").removeClass("success");
    }

    

});