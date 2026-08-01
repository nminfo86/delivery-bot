//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";



$(document).ready(function () {


    $(".searcheCompany").change(function () {
        //Hide Order Details when search by place

        var company_id = $(".searcheCompany option:selected").val();
        // alert(company_id);
        getCategoryByName(company_id, category_1_4_Pizza);
        getCategoryByName(company_id, category_1_2_Pizza);
    });

    $(document).on('click', '.generate', function (event) {
        event.preventDefault();

        var company_id = $(".searcheCompany").val();
        var category_id = $(this).closest("form").find("input").attr("category_id");
        if ((company_id != '') && (category_id != '')) {
            generateVariantes(company_id, category_id)
        }
    });
    $(document).on('click', '.delete', function (event) {
        event.preventDefault();

        var company_id = $(".searcheCompany").val();
        var category_id = $(this).closest("form").find("input").attr("category_id");
        if ((company_id != '') && (category_id != '')) {
            if (confirm(JsTranslations.msgConfirmDelete)) {
                deleteVariantes(company_id, category_id)
            }
           
        }
    });

    //******************** Functions *************************************/



    function getCategoryByName(company_id, category) {

        $.ajax({
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getCategoryByName&company_id=" + company_id + "&category=" + category,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                    $("form input").val("");
                    $("form input").attr("category_id", "");
                } else {
                    if (category == category_1_4_Pizza) {
                        $("#1_4_Pizza").val(data[0].category)
                        $("#1_4_Pizza").attr("category_id", data[0].id)
                    } else {
                        $("#1_2_Pizza").val(data[0].category)
                        $("#1_2_Pizza").attr("category_id", data[0].id)
                    }
                }
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - " + "getCategoryByName()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function generateVariantes(company_id, category_id) {

        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=generateVariants&company_id=" + company_id + "&category_id=" + category_id,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "warning");
                    }
                } else {
                    swal(JsTranslations.nice, JsTranslations.pizza_var_generate_success, "success");
                }
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - " + "generateVariantes()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

    function deleteVariantes(company_id, category_id) {

        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteVariants&company_id=" + company_id + "&category_id=" + category_id,
            beforeSend: function () {
                $("#divLoading").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {
                    swal(JsTranslations.nice, JsTranslations.pizza_var_delete_success, "success");
                }
            },
            error: function (jqXHR, exception) {
                swal("ajaxError!", getAjaxErrorMessage(jqXHR, exception) + " - " + "generateVariantes()", "error");
                $("#divLoading").addClass("d-none");
            }
        });
    }

});