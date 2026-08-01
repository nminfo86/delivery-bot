/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var searchFormStart = $("#searchStart");
var startTable = $('#startTable');
//*********************** ************** ********

$(document).ready(function () {



    $("#searchForm .searcheTable").change(function () {
        var query = $(this).val() != "NULL" ? $("#searchForm .searcheTable option:selected").text() : "";
        search(query);
    });
    $("#searchForm .searcheCategory").change(function () {
        var query = $(this).val() != 0 ? $("#searchForm .searcheCategory option:selected").text() : "";
        search(query);
    });

    //Update suborder to started 
    $(document).on('click', '#startTable .deliver', function () {
        var id = $(this).closest("tr").attr("id");
        updateSubOrderStatus(id, orderStateDelivred, 0)
    });

    search("");
    //****************** FUNCTIONS *********************//
    function search(query) {
        $.ajax({
            url: "php/JsonSubOrder.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getSubOrdersOfWaiterHistory&search=" + query,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

                startTable.find("tr").remove();
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                    // Set count to 0 if failed
                    $("#suborderCountWaiterHistory").text(0);
                } else {
                    var uniqueIds = [];
                    for (var i = 0; i < data.length; i++) {
                        startTable.append(
                            "<tr style='cursor:context-menu;' id = " + data[i].id + ">" +
                            "<td class='td-tableName'>" + (data[i].tableName == null ? data[i].place : data[i].tableName) + "</td>" +
                            "<td class='td-qte'>" + data[i].quantity + "</td>" +
                            "<td class='td-title'>" + data[i].title + (data[i].attributeValue == null ? "" : " " + data[i].attributeValue) + "</td>" +
                            "<td class='td-comment'>" + data[i].subComment + "</td>" +
                            "<td class='deliver' style='cursor:pointer;'><i class='fas fa-share-square fa-2x'></i></td>" +

                            "</tr>"

                        );
                        // Collect unique suborder ids
                        if (uniqueIds.indexOf(data[i].id) === -1) {
                            uniqueIds.push(data[i].id);
                        }
                    }
                    // Show the number of unique suborders in the badge
                    $("#suborderCountWaiterHistory").text(uniqueIds.length);
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception), "error");
                $("#suborderCountWaiterHistory").text(0);
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
            data: "function=updateSubOrderProgression&id=" + id + "&progression=" + progression + "&print=" + print +
                "&place=" + "&qte=" + "&article=" + "&supplements=",
            beforeSend: function () {
                //                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error").then((willReady) => {
                        if (willReady) {
                            window.location = "chefPanel.php";
                        }
                    });
                } else {
                    search("");
                }
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