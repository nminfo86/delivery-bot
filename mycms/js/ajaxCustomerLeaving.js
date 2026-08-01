/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var searchForm = $('#search');

//*********************** ************** ********

$(document).ready(function () {
    initHMI();


    $(document).on('click', '.circle', function () {
        var table_id = $(this).attr("id");

        swal({
            title: "Liberer Table",
            text: ("Voulez vous vraiment liberer cette table ?"),
            icon: "warning",
            buttons: true,
        })
                .then((willReady) => {
                    if (willReady) {
                        checkExistNotReadyOrdersOnTable(table_id);

                    }
                });
    });

    //****************** FUNCTIONS *********************//

    function checkExistNotReadyOrdersOnTable(table_id) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=checkExistNotReadyOrdersOnTable&table_id=" + table_id,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");

            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {
                        updateOrdersCustomerLeftByTable(table_id);
                    } else {
                        swal("error!", data.message, "error");
                    }
                } else {
                    $("#divLoadingcms").addClass("d-none");

                    swal("Liberer Table", msgNotReadyOrdersExist, "error");
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

    function updateOrdersCustomerLeftByTable(table_id) {
        $.ajax({
            url: "php/JsonOrdere.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateOrdersCustomerLeftByTable&table_id=" + table_id,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    swal(JsTranslations.oops, data.message, "error");
                } else {
                    $("#divLoadingcms").addClass("d-none");

                    window.location = "customerLeaving.php";
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

    function initHMI() {
//        $("#orderDetail").addClass("d-none");
    }

});