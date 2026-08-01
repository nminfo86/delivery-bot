/* 
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var objectAlert = $("#objectAlert");
var uploaderAlert = $("#uploaderAlert");
var searchForm = $("#searchForm");
var objectForm = $("#objectForm");
var uploadImgForm = $("#uploadImgForm");
var uploadVidForm = $("#uploadVidForm");

var readTable = $('#readTable');


//*********************** ************** ********

$(document).ready(function () {


    // Handle color palette selection
    $('.color-option').on('click', function () {
        var selectedColor = $(this).data('color');

        // Remove selected class from all options
        $('.color-option').removeClass('selected');

        // Add selected class to clicked option
        $(this).addClass('selected');

        // Update hidden input value
        $('#color').val(selectedColor);
    });

    $(document).on('click', '#showCreateDivButton', function () {
        fillDisplaySelect(1);
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

    $(document).on('click', '#attributesDiv input:checkbox', function () {
        var category_id = objectForm.find("#id").val();
        var attribute_id = $(this).val();
        var element = $(this);
        //this test is reversed because when clicking on checkbox it's change then we test check status
        if (!$(this).is(":checked")) {
            //this checkbox was checked
            if (confirm(JsTranslations.msgConfirmDeleteAttributeFromCategory)) {
                deleteCategory_Attribute(category_id, attribute_id);
            }
        } else {
            //this checkbox was not checked
            createCategory_Attribute(category_id, attribute_id, element);
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
        fillDisplaySelect(0);
        fillAttributes();
        fillObject(id);
    });

    $(document).on('click', '#mediaTypeVideo', function () {
        $("#mediaTypeImage").prop('checked', false);
        $("#uploadImgDiv").addClass("d-none");
        $("#uploadVidDiv").removeClass("d-none");
    });

    $(document).on('click', '#mediaTypeImage', function () {
        $("#mediaTypeVideo").prop('checked', false);
        $("#uploadImgDiv").removeClass("d-none");
        $("#uploadVidDiv").addClass("d-none");
    });

    $(document).on('click', '.delete-media', function () {
        var id = $(this).closest(".media").attr("id");
        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteMedia(id);
        }
    });

    objectForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(objectForm)) {
            createObject();
        }
    });

    $(document).on('click', '.delete-object', function () {
        var id = $(this).attr("id");
        if (confirm(JsTranslations.msgConfirmDelete)) {
            deleteObject(id, $(this).parents('tr'))
        }
    });

    $(document).on('click', '#editObjectButton', function () {
        event.preventDefault();
        if (validateForm(objectForm)) {
            var id = objectForm.find("#id").val();
            updateObject(id);
        }
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
    uploadVidForm.submit(function (event) {
        event.preventDefault();
        if (validateForm(uploadVidForm)) {
            uploadVid();
            uploadVidForm[0].reset();
        }
    });

    search("");
    //****************** FUNCTIONS *********************//

    function search(query) {
        $.ajax({
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=searchCategory&search=" + query,
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
                                + "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block 'l>"
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
                            { "data": "category" },
                            {
                                "data": "color",
                                render: function (data, type, row) {
                                    return "<div style='width: 30px; height: 20px; background-color: " + row.color +
                                        "; border-radius: 2px; display: inline-block;'></div> ";
                                }
                            },
                            { "data": "display" },
                            {
                                "data": "available",
                                render: function (data, type, row) {
                                    return row.available == '1' ? JsTranslations.available : JsTranslations.not_available;
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
                $(".dTButton").append("<button id='showCreateDivButton' class='btn btn-sm btn-outline-success '><i class ='fas fa-plus fa-1_5x'></i></button>");
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
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getCategoryById&id=" + id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    objectForm.find("#id").val(data[0].id);
                    data[0].prepare == 1 ? objectForm.find("#prepare").prop('checked', true) : objectForm.find("#prepare").prop('checked', false);
                    data[0].available == 1 ? objectForm.find("#available").prop('checked', true) : objectForm.find("#available").prop('checked', false);
                    data[0].supplement == 1 ? objectForm.find("#supplement").prop('checked', true) : objectForm.find("#supplement").prop('checked', false);
                    data[0].acceptSupplement == 1 ? objectForm.find("#acceptSupplement").prop('checked', true) : objectForm.find("#acceptSupplement").prop('checked', false);
                    objectForm.find("#category").val(data[0].category);
                    objectForm.find("#prepare").val(data[0].prepare);
                    objectForm.find("#available").val(data[0].available);
                    objectForm.find("#supplement").val(data[0].supplement);
                    objectForm.find("#acceptSupplement").val(data[0].acceptSupplement);
                    setInitialColor(data[0].color);
                    objectForm.find("#display").val(data[0].display);

                    if ((data[0].categoryCover) !== null) {

                        //test whether the media is an Image or Video format
                        if (!(data[0].categoryCover).startsWith("http") && (data[0].categoryCover) !== null) {
                            $("#showImgDiv").append(
                                "<div class = 'media' id=" + data[0].id + ">" +
                                "<div class = 'media-left'>" +
                                "<img src='" + resolveMediaUrlJs(data[0].categoryCover) + "' alt='" + data[0].category + "' class='img-fluid img-thumbnail'>" +
                                "</div>" +
                                "<div class = 'media-body'>" +
                                "<h4 class = 'media-heading d-none d-sm-table-cell'>" + data[0].category + "</h4>" +
                                "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                                "</div>" +
                                "</div>"
                            );
                        } else {
                            $("#showVidDiv").append(
                                "<div class = 'media' id=" + data[0].id + ">" +
                                "<div class = 'media-left'>" +
                                "<iframe class = 'embed-responsive-item' src = '" + resolveMediaUrlJs(data[0].categoryCover) + "'></iframe>" +
                                "</div>" +
                                "<div class = 'media-body'>" +
                                "<h4 class = 'media-heading d-none d-sm-table-cell'>" + data[0].category + "</h4>" +
                                "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
                                "</div>" +
                                "</div>"
                            );
                        }
                    }
                    fillAttributesCategory(data[0].id);
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

    function fillAttributes() {

        $.ajax({
            url: "php/JsonAttribute.php",
            type: "POST",
            jsonp: false,
            async: false,
            dataType: dataType,
            data: "function=getAllAttributes",
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {

                    $("#attributesDiv").find(".form-check").remove();
                    for (var i = 0; i < data.length; i++) {
                        $("#attributesDiv").append(
                            "<div class='form-check form-check-inline largeCheckBox pl-5'>" +
                            "<input class='form-check-input' type='checkbox' value=" + data[i].id + ">" +
                            "<label class='form-check-label '>" + data[i].attribute + "</label>" +
                            "</div>"
                        );
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
    function fillDisplaySelect(j) {
        // j is used to determine whther is fill to update or fill to Insert

        $.ajax({
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            async: false,
            dataType: dataType,
            data: "function=getCountCategories",
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {
                    $("#display option[value!=0]").remove();

                    for (var i = 1; i <= parseInt((data[0].number)) + j; i++) {
                        $("#display option[value=0]").after(
                            "<option value=" + i + ">" + i + "</option>"
                        );
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

    function fillAttributesCategory(category_id) {
        $.ajax({
            url: "php/JsonCategory_Attribute.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getAllAttributesOfCategory&category_id=" + category_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message !== noDataFound) {
                        showAlertFailed(objectAlert, data.message);
                    }
                } else {

                    for (var i = 0; i < data.length; i++) {

                        $("#attributesDiv input:checkbox[value=" + data[i].attribute_id + "]").prop('checked', true);
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

    function deleteCategory_Attribute(category_id, attribute_id) {
        $.ajax({
            url: "php/JsonCategory_Attribute.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=delete&category_id=" + category_id + "&attribute_id=" + attribute_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(uploaderAlert, data.message);
                } else {
                    showAlertSuccess(objectAlert, JsTranslations.msgOperationReussie);
                }
                $("#loadingImage").addClass("d-none");

            },
            error: function (jqXHR, exception) {
                showAlertFailed(uploaderAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }
        });

    }

    function createCategory_Attribute(category_id, attribute_id, element) {
        $.ajax({
            url: "php/JsonCategory_Attribute.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=create&category_id=" + category_id + "&attribute_id=" + attribute_id,
            beforeSend: function () {
                $("#loadingImage").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === data_exist) {
                        showAlertFailed(objectAlert, JsTranslations.msgHaveAttribute);
                    } else {
                        showAlertFailed(objectAlert, data.message);
                    }
                    element.prop('checked', false);
                } else {
                    showAlertSuccess(objectAlert, JsTranslations.msgOperationReussie);
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
        var categoryId = 0;
        var formdata = new FormData(uploadImgForm[0]); //form element
        formdata.append('function', "uploadMedia");
        categoryId = objectForm.find("#id").val();
        formdata.append('id', categoryId);

        $.ajax({
            url: "php/JsonCategory.php",
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
                    if (data.message === have_media) {
                        showAlertFailed(uploaderAlert, JsTranslations.msghaveMedia);
                    } else {
                        showAlertFailed(uploaderAlert, data.message);
                    }
                } else {
                    //                    showAlertSuccess(uploaderAlert, "Media transférée avec succès :)");

                    $("#showImgDiv").append(
                        "<div class = 'media' id=" + data[0].id + ">" +
                        "<div class = 'media-left'>" +
                        "<img src='" + resolveMediaUrlJs(data[0].categoryCover) + "' alt='" + data[0].category + "' class='img-fluid img-thumbnail'>" +
                        "</div>" +
                        "<div class = 'media-body'>" +
                        "<h4 class = 'media-heading d-none d-sm-table-cell'>" + data[0].category + "</h4>" +
                        "<button class='btn btn-danger delete-media'><i class='far fa-trash-alt fa-lg'></i></button>" +
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

    // function that creat Videos media
    function uploadVid() {
        var categoryId = 0;
        //      var formdata = new FormData(uploadVidForm[0]); //form element
        var formdata = new FormData(); //form element

        var videoId = $("#video-media").val();
        categoryId = objectForm.find("#id").val();

        formdata.append('function', "uploadMedia");
        formdata.append('categoryCover', videoId);
        formdata.append('id', categoryId);

        $.ajax({
            url: "php/JsonCategory.php",
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
                    if (data.message === have_media) {
                        showAlertFailed(uploaderAlert, JsTranslations.msghaveMedia);
                    } else {
                        showAlertFailed(uploaderAlert, data.message);
                    }
                } else {

                    $("#showVidDiv").append(
                        "<div class = 'media' id=" + data[0].id + ">" +
                        "<div class = 'media-left'>" +
                        "<iframe class = 'embed-responsive-item' src = '" + resolveMediaUrlJs(data[0].categoryCover) + "'></iframe>" +
                        "</div>" +
                        "<div class = 'media-body'>" +
                        "<h4 class = 'media-heading d-none d-sm-table-cell'>" + data[0].category + "</h4>" +
                        "<button class='btn btn-danger delete-media'><i class='fa fa-trash-o fa-lg'></i></button>" +
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
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=createCategory&" + objectForm.serialize() + "&prepare=" + $("#prepare").val()
                + "&available=" + $("#available").val() + "&supplement=" + $("#supplement").val() + "&acceptSupplement=" + $("#acceptSupplement").val(),
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
                    showAlertSuccess(objectAlert, JsTranslations.msg_object_added.replace('{object}', data[0].category));
                    objectForm.find("#id").val(data[0].id);
                    $("#attributesDiv").removeClass("d-none");
                    fillAttributes();
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
        //        +($("#prepare").is(":checked")?"":"&prepare=0")
        $.ajax({
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=updateCategory&" + objectForm.serialize() + "&prepare=" + $("#prepare").val()
                + "&available=" + $("#available").val() + "&supplement=" + $("#supplement").val() + "&acceptSupplement=" + $("#acceptSupplement").val(),
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
                    showAlertSuccess(objectAlert, JsTranslations.msg_object_updated.replace('{object}', data[0].category));
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
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteCategory&id=" + id,
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
            url: "php/JsonCategory.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=deleteCategoryCover&" + "id=" + id,
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

    // Set initial selected color
    function setInitialColor(color) {
        $('.color-option').removeClass('selected');
        $('.color-option[data-color="' + color + '"]').addClass('selected');
        $('#color').val(color);
    }

    function prepareToViewAllObjects() {
        $("#allObjectsDiv").removeClass("d-none");
        $("#formDiv").addClass("d-none");
        $("#attributesDiv").addClass("d-none");
        $("#uploaderDiv").addClass("d-none");
        $("#showMediaDiv").addClass("d-none");
        $("#showImgDiv").empty();
        $("#showVidDiv").empty();
        objectForm[0].reset();
    }
    function prepareToAddObject() {
        $("#formDiv").removeClass("d-none");
        $("#attributesDiv").addClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").removeClass("d-none");
        $("#editObjectButton").addClass("d-none");
    }
    function prepareToEditObject() {
        $("#formDiv").removeClass("d-none");
        $("#attributesDiv").removeClass("d-none");
        $("#showMediaDiv").removeClass("d-none");
        $("#uploaderDiv").removeClass("d-none");
        $("#editObjectButton").removeClass("d-none");
        $("#allObjectsDiv").addClass("d-none");
        $("#addObjectButton").addClass("d-none");
    }

});