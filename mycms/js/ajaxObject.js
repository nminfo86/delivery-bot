/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var uploaderAlert = $("#uploaderAlert");
var priceAlert = $("#priceAlert");
var searchForm = $("#searchForm");
var objectForm = $("#objectForm");
var priceForm = $("#priceForm");
var uploadImgForm = $("#uploadImgForm");
var uploadVidForm = $("#uploadVidForm");

var readTable = $('#readTable');


//*********************** ************** ********

$(document).ready(function () {

    $(document).on('click', '#showCreateDivButton', function () {
        fillCategories()
        prepareToAddObject()
    });
    $(document).on('click', '#showAllObjectsButton', function () {
        prepareToViewAllObjects();
    });

    $("#searchInput").keyup(function () {
        if ($("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()));
            }
        }
    });

    $("#searchButton").click(function () {
        if (!$("#searchCheckBox").is(':checked')) {
            if (validateForm(searchForm)) {
                search($.trim($("#searchInput").val()));
            }
        }
    });

    $(document).on('click', '#objectForm input:checkbox', function () {

        //this test is reversed because when clicking on checkbox it's change then we test check status
        if (!$(this).is(":checked")) {
            //this checkbox was checked
            $(this).val("0");
        } else {
            //this checkbox was not checked
            $(this).val("1");
        }
    });

    $(document).on('click', '.edit-object', function () {
        var id = $(this).attr("id");
        fillCategories();
        fillObject(id);
    });

    $(document).on('click', '#mediaTypeVideo', function () {
        $("#mediaTypeImage").prop('checked', false);
        $("#object-media").attr("accept", "video/*");
        $("#object-media").attr("validation", "FILES,VIDEO");
        //        $("#uploadImgDiv").addClass("d-none");
        //        $("#uploadVidDiv").removeClass("d-none");
    });

    $(document).on('click', '#mediaTypeImage', function () {
        $("#mediaTypeVideo").prop('checked', false);
        $("#object-media").attr("accept", "image/*");
        $("#object-media").attr("validation", "FILES,IMAGE");
    });

    $(document).on('click', '.delete-media', function () {
        var id = $(this).closest(".media").attr("id");
        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteMedia(id);
        }
    });
    $(document).on('click', '.cover-media', function () {
        var object_id = objectForm.find("#id").val();
        var id = $(this).closest(".media").attr("id");
        setMediaCover(id, object_id);
    });

    objectForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
          
           if (parseFloat($("#baseCost").val()) > parseFloat($("#basePrice").val())) {

                swal(JsTranslations.modal_title_becarefull, JsTranslations.msg_cost_price_error, "warning");
            } else {
                createObject();
            }
        }
    });

    $(document).on('click', '.delete-object', function () {
        var id = $(this).attr("id");
        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteObject(id, $(this).parents('tr'))
        }
    });

    $(document).on('click', '#editObjectButton', function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            var id = objectForm.find("#id").val();
          if (parseFloat($("#baseCost").val()) > parseFloat($("#basePrice").val())) {

                swal(JsTranslations.modal_title_becarefull, JsTranslations.msg_cost_price_error, "warning");
            } else {
                updateObject(id);
            }
        }
    });
    //************** PRICE MANAGEMENT ****************************//
    $(document).on('click', '.add-price', function (event) {
        event.preventDefault();
        if (validateForm(priceForm)) {
            var object_id = objectForm.find("#id").val();
            var attribute_value_id = $("#priceForm select").val();
            var price = $("#priceForm input[name='price']").val();
            var cost = $("#priceForm input[name='cost']").val();
            if (parseFloat(cost) > parseFloat(price)) {
                swal(JsTranslations.modal_title_becarefull, JsTranslations.msg_cost_price_error, "warning");
            } else {
                addPrice(object_id, attribute_value_id, price, cost);
            }
        }
    });
    $(document).on('click', '.edit-price', function (event) {
        event.preventDefault();
        if (validateForm(priceForm)) {
            var object_id = objectForm.find("#id").val();
            var attribute_value_id = $("#priceForm select").val();
            var price = $("#priceForm input[name='price']").val();
            var cost = $("#priceForm input[name='cost']").val();
            
            if (parseFloat(cost) > parseFloat(price)) {
                swal(JsTranslations.modal_title_becarefull, JsTranslations.msg_cost_price_error, "warning");
            } else {
                updatePrice(object_id, attribute_value_id, price, cost);
            }
        }
    });
    $(document).on('click', '.delete-price', function (event) {
        event.preventDefault();
        if (validateForm(priceForm)) {
            var object_id = objectForm.find("#id").val();
            var attribute_value_id = $("#priceForm select").val();
            deletePrice(object_id, attribute_value_id);
        }

    });

    $(document).on('change', '#attribute_values', function () {
        var object_id = objectForm.find("#id").val();
        var attribute_value_id = $(this).val();
        fillPrice(object_id, attribute_value_id);

    });

    $(document).on('click', '#printAllObjectsPrices', function () {

        printAllObjectsPrices();


    });

    //*************** START UPLOAD IMAGES FILES ****************//
    //************ UPLOAD IMAGES SUBMIT FORM *****//
    uploadImgForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(uploadImgForm)) {
            uploadImg();
            uploadImgForm[0].reset();
        }
    });

    //************ EMBED Videos SUBMIT FORM **********//
    //    uploadVidForm.submit(function (event) {
    //        event.preventDefault();
    //        if (validateForm(uploadVidForm)) {
    //            uploadVid();
    //            uploadVidForm[0].reset();
    //        }
    //    });

    search("");
    //****************** FUNCTIONS *********************//

    function search(query) {
        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=searchObject&search=" + query,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");

                readTable.DataTable().clear().draw();
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    } else {
                        readTable.DataTable({
                            language: dataTableLanguage, //definde in global.js
                            responsive: true,
                            destroy: true,
                            "dom": "<'row flex-nowrap'"
                                + "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'B>"
                                + "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>"
                                + "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                                + ">rtp",
                        }).clear().draw();
                    }
                } else {
                    readTable.DataTable({
                        language: dataTableLanguage, //definde in global.js
                        responsive: true,
                        destroy: true,
                        "dom": "<'row flex-nowrap'"
                            + "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'B>"
                            + "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>"
                            + "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>"
                            + ">rtp",
                        "order": [4, 'desc'],

                        "data": data,
                        "columns": [
                            {
                                "data": "id",
                                render: function (data, type, row) {
                                    return (
                                        "<div class='btn-group' role='group'>" +
                                        "<button class='btn btn-info mr-2 edit-object' id=" + row.id + " role=" + row.role + "> <i class='fas fa-edit'></i></button > " +
                                        "<button class='btn btn-danger delete-object' id=" + row.id + "><i class='fas fa-trash'></i></button >" +
                                        "</div>");
                                },

                            },
                            { "data": "title" },
                            { "data": "category" },
                            {
                                "data": "objAvailable",
                                render: function (data, type, row) {
                                    return row.objAvailable == '1' ? JsTranslations.available : JsTranslations.not_available;
                                }
                            },
                            { "data": "companyName" },

                            {
                                "data": 'updateDate',
                                "className": 'dt-nowrap',
                                render: function (data, type, row) {
                                    if (type === 'display' || type === 'filter') {
                                        return moment(row.updateDate).format("DD-MM-YYYY HH:mm");
                                    }
                                    // For sorting and type detection, return the raw date in ISO format
                                    return row.updateDate;
                                }
                            },
                            
                        ],
                    });

                }
                $(".dTButton").empty().append(
                    "<div class='btn-group' role='group'>" +
                    "<button id='showCreateDivButton' class='btn btn-sm btn-outline-success '><i class ='fas fa-plus fa-1_5x'></i></button>" +
                    "<button id='printAllObjectsPrices' class='btn btn-sm btn-outline-warning ml-2' title='" + JsTranslations.sale_price_print + "'><i class='fas fa-print fa-1_5x'></i></button>" +
                    "</div>"
                );
                $("#loadingImage").addClass("d-none");
            },

            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillObject(id) {
        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getObjectById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {
                    objectForm.find("#id").val(data[0].id);
                    objectForm.find("#title").val(data[0].title);
                    objectForm.find("#description").val(data[0].description);
                    objectForm.find("#basePrice").val(data[0].basePrice);
                    objectForm.find("#baseCost").val(data[0].baseCost);
                    objectForm.find("#observation").val(data[0].observation);
                    //FIll the category of object
                    objectForm.find("#category_id").val(data[0].category_id);
                    objectForm.find("#objAvailable").val(data[0].objAvailable);
                    //Fill availability of Object
                    data[0].objAvailable == 1 ? objectForm.find("#objAvailable").prop('checked', true) : objectForm.find("#objAvailable").prop('checked', false)
                    //File category id in attr for preventing price management problems
                    fillAttributesOfCategory(data[0].category_id);
                    fillMediasOfObject(data[0].id);
                    prepareToEditObject();
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    // function that upload object images to server
    function uploadImg() {
        var mediaType = "";
        var mediaPosition = "";
        var objectId = 0;
        var formdata = new FormData(uploadImgForm[0]); //form element


        //get mediaTypeId value ********
        $("#mediaTypesDiv").find("input[type='radio']").each(function (index, elem) {
            if ($(this).is(':checked')) {
                mediaType = $(this).val();
            }
        });
        //
        formdata.append('function', 'uploadMedia');
        objectId = objectForm.find("#id").val();
        formdata.append('mediaType', mediaType);
        formdata.append('mediaPosition', mediaPosition);
        formdata.append('object_id', objectId);

        $.ajax({
            url: "php/JsonMedia.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: formdata,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#loadingImage2").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(uploaderAlert, JsTranslations.msgmediaExists);
                    } else {
                        showAlertFailed(uploaderAlert, data.message);
                    }
                } else {
                    //                    showAlertSuccess(uploaderAlert, "Media transférée avec succès :)");

                    if (data[0].mediaType === MediaTypeImage) {
                        $("#showImgDiv").append(
                            "<div class = 'media' id=" + data[0].id + ">" +
                            "<div class = 'media-left'>" +
                            "<img src='" + resolveMediaUrlJs(data[0].media) + "' alt='" + data[0].mediaDescription + "' class='img-responsive img-thumbnail'>" +
                            "</div>" +
                            "<div class = 'media-body'>" +
                            "<h4 class = 'media-heading d-none-xs'>" + data[0].mediaDescription + "</h4>" +
                            "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                            "<button class='btn  btn-info cover-media'><i class='far fa-image fa-lg'></i></button>" +
                            "</div>" +
                            "</div>"
                        );
                    } else {
                        $("#showVidDiv").append(
                            "<div class = 'media' id=" + data[0].id + ">" +
                            "<div class = 'media-left'>" +
                            " <div class='embed-responsive embed-responsive-4by3'>" +
                            "<video src='" + resolveMediaUrlJs(data[0].media) + "' controls></video>" +
                            "</div>" +
                            "<div class = 'media-body'>" +
                            "<h4 class = 'media-heading d-none-xs'>" + data[0].mediaDescription + "</h4>" +
                            "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                            "<button class='btn  btn-info cover-media'><i class='far fa-image fa-lg'></i></button>" +
                            "</div>" +
                            "</div>"
                        );
                    }

                }
                $("#loadingImage2").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });

    }

    // function that creat Videos media
    function uploadVid() {
        var mediaType = "";
        var mediaPosition = "";
        var objectId = 0;
        //        var formdata = new FormData(uploadVidForm[0]); //form element
        var formdata = new FormData(); //form element

        //get mediaTypeId value ********
        $("#mediaTypesDiv").find("input[type='radio']").each(function (index, elem) {
            if ($(this).is(':checked')) {
                mediaType = $(this).val();
            }
        });
        //
        var videoId = $("#video-media").val();
        var description = $("#video-description").val();
        objectId = objectForm.find("#id").val();

        formdata.append('function', "uploadMedia");
        formdata.append('media', videoId);
        formdata.append('mediaDescription', description);

        formdata.append('mediaType', mediaType);
        formdata.append('mediaPosition', mediaPosition);
        formdata.append('object_id', objectId);

        $.ajax({
            url: "php/JsonMedia.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: formdata,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#loadingImage2").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(uploaderAlert, JsTranslations.msgmediaExists);
                    } else {
                        showAlertFailed(uploaderAlert, data.message);
                    }
                } else {

                    $("#showVidDiv").append(
                        "<div class = 'media' id=" + data[0].id + ">" +
                        "<div class = 'media-left'>" +
                        "<iframe class = 'embed-responsive-item' src = '" + data[0].media + "'></iframe>" +
                        "</div>" +
                        "<div class = 'media-body'>" +
                        "<h4 class = 'media-heading d-none-xs'>" + data[0].mediaDescription + "</h4>" +
                        "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                        "<button class='btn  btn-info cover-media'><i class='far fa-image fa-lg'></i></button>" +
                        "</div>" +
                        "</div>"

                    );
                }
                $("#loadingImage2").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });

    }

    // creat function
    function createObject() {

        objectForm.find("#id").val('0');
        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=createObject&" + objectForm.serialize() + "&objAvailable=" + $("#objAvailable").val(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, JsTranslations.msgObjectExists);
                    } else {
                        showAlertFailed(objectAlert, data.message);
                    }

                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_added.replace("{object}", data[0].title));

                    objectForm.find("#id").val(data[0].id);

                    fillAttributesOfCategory(data[0].category_id);

                    $("#uploaderDiv").removeClass("d-none");
                    $("#showMediaDiv").removeClass("d-none");

                    search("");
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }

        });
    }

    function updateObject(id) {
        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateObject&" + objectForm.serialize() + "&objAvailable=" + $("#objAvailable").val(),
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {

                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, JsTranslations.msgObjectExists);
                    } else {
                        showAlertFailed(objectAlert, data.message);
                    }

                } else {

                    showAlertSuccess(objectAlert, JsTranslations.msg_object_updated.replace("{object}", data[0].title));
                    objectForm.find("#id").val(data[0].id);
                    fillAttributesOfCategory(data[0].category_id);
                    search("");
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }

        });
    }

    function deleteObject(id, tr) {

        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteObject&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(objectAlert, data.message);
                } else {

                    //remove the row that has been deleted
                    readTable.DataTable().row(tr).remove().draw(false)

                    showAlertSuccess(objectAlert, JsTranslations.msgObjectDelete);
                }
                $("#loadingImage").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function deleteMedia(id) {
        $.ajax({
            url: "php/JsonMedia.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteMedia&" + "id=" + id,
            beforeSend: function () {

                $("#loadingImage2").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    $(".media[id=" + id + "]").remove();
                }
                $("#loadingImage2").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function setMediaCover(id, object_id) {
        $.ajax({
            url: "php/JsonMedia.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=setMediaCover&" + "id=" + id + "&object_id=" + object_id,
            beforeSend: function () {
                $("#loadingImage2").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {
                    $(".media").find(".cover-media").removeClass("active");
                    $(".media[id=" + id + "]").find(".cover-media").addClass("active");
                }
                $("#loadingImage2").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function fillMediasOfObject(object_id) {
        $.ajax({
            url: "php/JsonMedia.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllMediasOfObject&object_id=" + object_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    for (var i = 0; i < data.length; i++) {

                        if ((data[i].mediaType) === MediaTypeImage) {
                            $("#showImgDiv").append(
                                "<div class = 'media' id=" + data[i].id + ">" +
                                "<div class = 'media-left'>" +
                                "<img src='" + resolveMediaUrlJs(data[i].media) + "' alt='" + data[i].mediaDescription + "' class='img-responsive img-thumbnail'>" +
                                "</div>" +
                                "<div class = 'media-body'>" +
                                "<h4 class = 'media-heading d-none-xs'>" + data[i].mediaDescription + "</h4>" +
                                "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                                "<button class='btn  btn-info cover-media'><i class='far fa-image fa-lg'></i></button>" +
                                "</div>" +
                                "</div>"
                            );
                        }
                        if ((data[i].mediaType) === MediaTypeVideo) {
                            $("#showVidDiv").append(
                                "<div class = 'media' id=" + data[i].id + ">" +
                                "<div class = 'media-left'>" +
                                " <div class='embed-responsive embed-responsive-16by9'>" +
                                "<video src='" + resolveMediaUrlJs(data[i].media) + "' controls></video>" +
                                "</div>" +
                                "<div class = 'media-body'>" +
                                "<h4 class = 'media-heading d-none-xs'>" + data[i].mediaDescription + "</h4>" +
                                "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                                "<button class='btn  btn-info cover-media'><i class='far fa-image fa-lg'></i></button>" +
                                "</div>" +
                                "</div>"
                            );
                        }
                    }
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillCategories() {
        $.ajax({
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            async: false,
            dataType: dataType,
            data: "function=getAllCategories",
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    $("#category_id").find("option").remove();
                    for (var i = 0; i < data.length; i++) {
                        $("#category_id").append("<option value=" + data[i].id + ">" + data[i].category + "</option>");
                    }
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillAttributesOfCategory(category_id) {
        $.ajax({
            url: "php/JsonCategory_Attribute.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAttributesAndAttributeValuesOfCategory&category_id=" + category_id,
            beforeSend: function () {
                $("#loadingImage2").addClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(priceAlert, data.message);
                    } else {
                        $("#priceDiv").addClass("d-none");
                    }
                } else {
                    //Show price div
                    $("#priceDiv").removeClass("d-none");
                    //remove any optgroupe with it's options
                    $("#attribute_values").find("optgroup").remove();
                    //fill attributes and attributes values
                    for (var i = 0; i < data.length; i++) {

                        //                        $("#attribute_values:not(:has(optgroup[attribute_id="+data[i].attribute_id+"]))").append(
                        //                                "<optgroup label="+ data[i].attribute+" attribute_id="+data[i].attribute_id+"></optgroup>");

                        //if attribute don't exist in select tag, so fill it in
                        if (!$("#attribute_values optgroup[attribute_id=" + data[i].attribute_id + "]").length) {
                            $("#attribute_values").append(
                                "<optgroup label=" + data[i].attribute + " attribute_id=" + data[i].attribute_id + "></optgroup>");
                            $("#attribute_values optgroup[attribute_id=" + data[i].attribute_id + "]").append(
                                "<option value=" + data[i].attribute_value_id + ">" + data[i].attributeValue + "</option>");

                            //Else , if attribute exist we fill only it's attribute values
                        } else {
                            $("#attribute_values optgroup[attribute_id=" + data[i].attribute_id + "]").append(
                                "<option value=" + data[i].attribute_value_id + ">" + data[i].attributeValue + "</option>");
                        }

                    }
                }
                $("#loadingImage2").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(priceAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }

    function fillPrice(object_id, attributeValue_id) {
        $.ajax({
            url: "php/JsonPrice.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getPrice&object_id=" + object_id + "&attributeValue_id=" + attributeValue_id,
            beforeSend: function () {
                $("#loadingImage2").addClass("d-none");
                $("#priceForm input[name='price']").val("");
                $("#priceForm input[name='cost']").val("");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(priceAlert, data.message);
                    }
                } else {
                    $("#priceForm input[name='price']").val(data[0].price);
                    $("#priceForm input[name='cost']").val(data[0].cost);
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(priceAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function addPrice(object_id, attributeValue_id, price, cost) {
        $.ajax({
            url: "php/JsonPrice.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=addPrice&object_id=" + object_id
                + "&attributeValue_id=" + attributeValue_id
                + "&price=" + price
                + "&cost=" + cost,
            beforeSend: function () {
                $("#loadingImage2").addClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === data_exist) {
                        showAlertFailed(priceAlert, data_exist);
                    } else {
                        showAlertFailed(priceAlert, data.message);
                    }
                } else {

                    showAlertSuccess(priceAlert, JsTranslations.msgOperationReussie);
                    $("#loadingImage").addClass("d-none");

                }
            },
            error: function (jqXHR, exception) {
                showAlertFailed(priceAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    //
    function updatePrice(object_id, attributeValue_id, price, cost) {
        $.ajax({
            url: "php/JsonPrice.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updatePrice&object_id=" + object_id
                + "&attributeValue_id=" + attributeValue_id
                + "&price=" + price
                + "&cost=" + cost,
            beforeSend: function () {
                $("#loadingImage2").addClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(priceAlert, data.message);
                } else {
                    showAlertSuccess(priceAlert, JsTranslations.msgOperationReussie);
                    $("#loadingImage").addClass("d-none");

                }
            },
            error: function (jqXHR, exception) {
                showAlertFailed(priceAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function deletePrice(object_id, attributeValue_id) {
        $.ajax({
            url: "php/JsonPrice.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deletePrice&object_id=" + object_id + "&attributeValue_id=" + attributeValue_id,
            beforeSend: function () {
                $("#loadingImage2").addClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(priceAlert, data.message);
                } else {
                    $("#priceForm input").val("");
                    if (data.message === last_price) {
                        showAlertSuccess(priceAlert, JsTranslations.msgOperationReussie + " " + JsTranslations.msgLastPrice);
                    } else {
                        showAlertSuccess(priceAlert, JsTranslations.msgOperationReussie);
                    }
                    $("#loadingImage").addClass("d-none");

                }
            },
            error: function (jqXHR, exception) {
                showAlertFailed(priceAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage2").addClass("d-none");
            }
        });
    }

    function printAllObjectsPrices() {

        $.ajax({
            url: "php/JsonObject.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=printAllObjectsPrices",
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                        swal(JsTranslations.modal_title_empty, data.message, "warning");
                    } else {
                        swal(JsTranslations.oops, data.message, "error");
                    }
                } else {
                    swal(JsTranslations.modal_title_success, JsTranslations.msgOperationReussie, 'success');

                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(objectAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });
    }


    function prepareToViewAllObjects() {
        $("#allObjectsDiv").removeClass("d-none");
        $("#formDiv").addClass("d-none");
        $("#priceDiv").addClass("d-none");
        $("#uploaderDiv").addClass("d-none");
        $("#showMediaDiv").addClass("d-none");
        $("#showImgDiv").empty();
        $("#showVidDiv").empty();
        objectForm[0].reset();
    }
    function prepareToAddObject() {
        $("#formDiv").removeClass("d-none");
        $("#addObjectButton").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#editObjectButton").addClass("d-none");
        $("#priceDiv").addClass("d-none");
    }
    function prepareToEditObject() {
        $("#formDiv").removeClass("d-none");
        $("#showMediaDiv").removeClass("d-none");
        $("#uploaderDiv").removeClass("d-none");
        $("#editObjectButton").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").addClass("d-none");
        $("#priceDiv").addClass("d-none");

    }

});