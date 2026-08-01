<?php
require_once("php/functions.php");
require_once(__DIR__ . "/php/init.php");
require_once(__DIR__ . "/php/Config.php");
confirmLoggedIn();
accessControl("superAdmin,admin");
?>


<html lang="<?= $cmsHtmlConfig['lang'] ?>" dir="<?= $cmsHtmlConfig['dir'] ?>">
<!-- head -->
<?php include "includes/head.php"; ?>


<body id="Attributes Management" dir="<?= $cmsBodyConfig['dir'] ?>" class="<?= $cmsBodyConfig['class'] ?>">

    <!--Start Header-->
    <?php include "includes/header.php"; ?>
    <!-- header -->
    <!--End Header-->

    <section class="container">
        <div class="welcome-user">
            <h3><?= T('attributes_heading') ?></h3>
        </div>
        <div id="formDiv" class="mt-3">

            <!-- Attributes Section -->
            <div class="row mt-5">
                <div class="col-lg-6 col-12 mb-4">
                    <h4 class="mb-3"><?= T('section_heading') ?></h4>
                    <table id="attributesTable" class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th><?= T('attributes_table_name') ?></th>
                                <th><?= T('attributes_table_action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Static Data Example -->
                            <tr data-id="1">
                                <td><?= T('attributes_table_color') ?></td>
                                <td><button class="btn btn-danger btn-sm delete-btn"><i
                                            class="fas fa-trash"></i></button></td>
                            </tr>
                            <tr data-id="2">
                                <td><?= T('attributes_table_size') ?></td>
                                <td><button class="btn btn-danger btn-sm delete-btn"><i
                                            class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Attribute Values Section -->
                <div class="col-lg-6 col-12 mb-4">
                    <h4 class="mb-3"><?= T('values_section_heading') ?></h4>
                    <table id="attributeValuesTable" class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th><?= T('values_table_value') ?></th>
                                <th><?= T('values_table_action') ?></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!--Start Footer-->
    <?php // include "includes/footer.php"; ?>
    <!-- footer -->
    <!--End Footer-->

    <?php include "includes/leg.php"; ?>
    <!-- leg -->

    <script>
        $(document).ready(function() {

            // Initial load
            loadAttributes();


            const attributesTable = $('#attributesTable').DataTable({
                language: dataTableLanguage, //definde in global.js
                responsive: true,
                destroy: true,
                dom: "<'row flex-nowrap'" +
                    "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'>" +
                    "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>" +
                    "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>" +
                    ">rtp",
                columnDefs: [{
                    targets: 0,
                    type: 'num'
                }],
                order: [
                    [0, 'desc']
                ],
                info: false,
                paging: false
            });
            // Add custom add button for attributesTable after draw (like ajaxCategory.js)
            function appendAttributeAddButton() {
                if ($('#addAttributeButton').length === 0) {
                    $(".dTButton").eq(0).append("<button id='addAttributeButton' class='btn btn-sm btn-outline-success'><i class='fas fa-plus fa-1_5x'></i></button>");
                }
            }
            attributesTable.on('draw', appendAttributeAddButton);
            appendAttributeAddButton();
            // Add click handler for addAttributeButton
            $(document).on('click', '#addAttributeButton', function() {
                const newRow = attributesTable.row.add([
                    `<input type=\"text\" class=\"form-control form-control-sm\" placeholder= "${JsTranslations.attributes_table_name}">`,
                    `<button class=\"btn btn-primary btn-sm add-btn\"><i class=\"fas fa-check\"></i></button>
                 <button class=\"btn btn-danger btn-sm delete-btn\"><i class=\"fas fa-trash\"></i></button>`
                ]).draw(false).node();
                $(newRow).prependTo('#attributesTable tbody');
                $(newRow).find('input').focus();
                $(newRow).find('.add-btn').on('click', function() {
                    const newValue = $(newRow).find('input').val().trim();
                    if (newValue) {
                        handleAddOrEditAction(
                            "php/JsonAttribute.php",
                            `function=create&attribute=${encodeURIComponent(newValue)}`,
                            function() {
                                loadAttributes();
                            }
                        );
                    } else {
                        swal(JsTranslations.oops, JsTranslations.msgNameEmpty, "warning");
                    }
                });
                $(newRow).find('input').on('keypress', function(e) {
                    if (e.which === 13) {
                        const newValue = $(this).val().trim();
                        if (newValue) {
                            handleAddOrEditAction(
                                "php/JsonAttribute.php",
                                `function=create&attribute=${encodeURIComponent(newValue)}`,
                                function() {
                                    loadAttributes();
                                }
                            );
                        } else {
                            swal(JsTranslations.oops, JsTranslations.msgNameEmpty, "warning");
                        }
                    }
                });
            });

            const attributeValuesTable = $('#attributeValuesTable').DataTable({
               language: dataTableLanguage, //definde in global.js
                responsive: true,
                destroy: true,
                dom: "<'row flex-nowrap'" +
                    "<'col-lg-2 col-sm-3 col-xs-4 dTButton pl-3'>" +
                    "<'col-lg-6 col-sm-7 col-xs-8 pr-0'f>" +
                    "<'col-lg-4 col-sm-2 col-xs-2 d-none d-sm-block'l>" +
                    ">rtp",
                columnDefs: [{
                    targets: 0,
                    type: 'num'
                }],
                order: [
                    [0, 'desc']
                ],
                info: false,
                paging: false
            });
            // Add custom add button for attributeValuesTable after draw (like ajaxCategory.js)
            function appendAttributeValueAddButton() {
                if ($('#addAttributeValueButton').length === 0) {
                    $(".dTButton").eq(1).append("<button id='addAttributeValueButton' class='btn btn-sm btn-outline-success'><i class='fas fa-plus fa-1_5x'></i></button>");
                }
            }
            attributeValuesTable.on('draw', appendAttributeValueAddButton);
            appendAttributeValueAddButton();
            // Add click handler for addAttributeValueButton
            $(document).on('click', '#addAttributeValueButton', function() {
                const newRow = attributeValuesTable.row.add([
                    `<input type=\"text\" class=\"form-control form-control-sm\" placeholder=\"${JsTranslations.values_table_value}\">`,
                    `<button class=\"btn btn-primary btn-sm add-btn\"><i class=\"fas fa-check\"></i></button>
                 <button class=\"btn btn-danger btn-sm delete-btn\"><i class=\"fas fa-trash\"></i></button>`
                ]).draw(false).node();
                $(newRow).find('input').focus();
                $(newRow).find('.add-btn').on('click', function() {
                    const newValue = $(newRow).find('input').val().trim();
                    const attributeId = $('#attributesTable tbody tr.table-primary').data('id');
                    if (newValue && attributeId) {
                        handleAddOrEditAction(
                            "php/JsonAttribute_Value.php",
                            `function=create&attributeValue=${encodeURIComponent(newValue)}&attribute_id=${attributeId}`,
                            function() {
                                loadAttributeValues(attributeId);
                            }
                        );
                    } else {
                        swal(JsTranslations.oops, JsTranslations.attribut_value_id_missed, "error");
                    }
                });
                $(newRow).find('input').on('keypress', function(e) {
                    if (e.which === 13) {
                        const newValue = $(this).val().trim();
                        const attributeId = $('#attributesTable tbody tr.table-primary').data('id');
                        if (newValue && attributeId) {
                            handleAddOrEditAction(
                                "php/JsonAttribute_Value.php",
                                `function=create&attributeValue=${encodeURIComponent(newValue)}&attribute_id=${attributeId}`,
                                function() {
                                    loadAttributeValues(attributeId);
                                }
                            );
                        } else {
                            swal(JsTranslations.oops, JsTranslations.attribut_value_id_missed, "error");
                        }
                    }
                });
            });

            // Fetch and display attributes from the database
            function loadAttributes() {
                $.ajax({
                    url: "php/JsonAttribute.php",
                    type: "POST",
                    dataType: "JSON",
                    data: "function=getAllAttributes",
                    beforeSend: function() {
                        $("#divLoading").removeClass("d-none");
                    },
                    success: function(response) {
                        if (response.state === "f" && response.message !== noDataFound) {
                            swal(JsTranslations.oops, response.message, "error");
                        } else {
                            attributesTable.clear(); // Clear previous data
                            response.forEach(attr => {
                                const rowNode = attributesTable.row.add([
                                    attr.attribute,
                                    `<div class='btn-group' role='group'>` +
                                    `<button class="btn btn-info btn-sm mr-2 edit-btn" data-id="${attr.id}"><i class="fas fa-edit"></i></button>` +
                                    `<button class="btn btn-danger btn-sm delete-btn" data-id="${attr.id}"><i class="fas fa-trash"></i></button>` +
                                    `</div>`
                                ]).draw(false).node();
                                $(rowNode).attr("data-id", attr.id); // Ensure data-id is set for each row
                            });
                        }
                        $("#divLoading").addClass("d-none");
                    },
                    error: function(jqXHR, exception) {
                        swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
                        $("#divLoading").addClass("d-none");
                    }
                });
            }

            // Fetch and display attribute values based on selected attribute
            function loadAttributeValues(attributeId) {
                $.ajax({
                    url: "php/JsonAttribute_Value.php",
                    type: "POST",
                    dataType: "JSON",
                    data: `function=getAllAttributeValuesByAttributeID&attribute_id=${attributeId}`,
                    beforeSend: function() {
                        $("#divLoading").removeClass("d-none");
                    },
                    success: function(response) {
                        if (response.state === "f" && response.message !== noDataFound) {
                            swal(JsTranslations.oops, response.message, "error");
                        } else {
                            if (response.message === noDataFound) {
                                attributeValuesTable.clear().draw(); // Clear previous data
                            } else {
                                attributeValuesTable.clear().draw(); // Clear previous data
                                response.forEach(value => {
                                    const rowNode = attributeValuesTable.row.add([
                                        value.attributeValue,
                                        `<div class='btn-group' role='group'>` +
                                        `<button class="btn btn-info btn-sm mr-2 edit-btn" data-id="${value.id}"><i class="fas fa-edit"></i></button>` +
                                        `<button class="btn btn-danger btn-sm delete-btn" data-id="${value.id}"><i class="fas fa-trash"></i></button>` +
                                        `</div>`
                                    ]).draw(false).node();
                                    $(rowNode).attr("data-id", value
                                        .id); // Ensure data-id is set for each row
                                });
                            }
                        }
                        $("#divLoading").addClass("d-none");
                    },
                    error: function(jqXHR, exception) {
                        swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
                        $("#divLoading").addClass("d-none");
                    }
                });
            }

            // Reusable function to handle adding and updating attributes or attribute values
            function handleAddOrEditAction(url, data, successCallback) {
                $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "JSON",
                    data: data,
                    beforeSend: function() {
                        $("#divLoading").removeClass("d-none");
                    },
                    success: function(response) {
                        if (response.state === "f") {
                            swal(JsTranslations.oops, response.message, "error");
                        } else {
                            swal(JsTranslations.modal_title_success, JsTranslations.msgOperationReussie, "success");
                            successCallback(response);
                        }
                        $("#divLoading").addClass("d-none");
                    },
                    error: function(jqXHR, exception) {
                        swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
                        $("#divLoading").addClass("d-none");
                    }
                });
            }

            // Handle add button click for Attributes Table
            $('#attributesTable tbody').on('click', '.add-btn', function() {
                const attributeId = $(this).data('id');
                swal(JsTranslations.modal_title_add_value, {
                    content: "input",
                }).then((value) => {
                    if (value) {
                        handleAddOrEditAction(
                            "php/JsonAttribute.php",
                            `function=create&attribute=${encodeURIComponent(value)}`,
                            function() {
                                loadAttributes(); // Reload attributes after successful creation
                            }
                        );
                    }
                });
            });

            // Handle add button click for Attribute Values Table
            $('#attributeValuesTable tbody').on('click', '.add-btn', function() {
                const attributeId = $('#attributesTable tbody tr.table-primary').data('id');
                swal(JsTranslations.modal_title_add_value, {
                    content: "input",
                }).then((value) => {
                    if (value) {
                        handleAddOrEditAction(
                            "php/JsonAttribute_Value.php",
                            `function=create&attributeValue=${encodeURIComponent(value)}&attribute_id=${attributeId}`,
                            function() {
                                loadAttributeValues(attributeId); // Reload attribute values
                            }
                        );
                    }
                });
            });


            // Handle edit button click for Attributes Table
            $('#attributesTable tbody').on('click', '.edit-btn', function() {
                const attributeId = $(this).data('id');
                const currentRow = $(this).closest('tr');
                const currentValue = currentRow.find('td:first').text();

                swal(JsTranslations.modal_title_update_value, {
                    content: {
                        element: "input",
                        attributes: {
                            value: currentValue
                        }
                    },
                }).then((newValue) => {
                    if (newValue && newValue.trim() !== "") {
                        handleAddOrEditAction(
                            "php/JsonAttribute.php",
                            `function=update&id=${attributeId}&attribute=${encodeURIComponent(newValue)}`,
                            function() {
                                loadAttributes(); // Reload attributes after successful update
                            }
                        );
                    } else {
                        swal(JsTranslations.oops, JsTranslations.msgNameEmpty, "warning");
                    }
                });
            });

            // Handle edit button click for Attribute Values Table
            $('#attributeValuesTable tbody').on('click', '.edit-btn', function() {
                const valueId = $(this).data('id');
                const currentRow = $(this).closest('tr');
                const currentValue = currentRow.find('td:first').text();

                swal(JsTranslations.modal_title_update_value, {
                    content: {
                        element: "input",
                        attributes: {
                            value: currentValue
                        }
                    },
                }).then((newValue) => {
                    if (newValue && newValue.trim() !== "") {
                        handleAddOrEditAction(
                            "php/JsonAttribute_Value.php",
                            `function=update&id=${valueId}&attributeValue=${encodeURIComponent(newValue)}`,
                            function() {
                                const attributeId = $(
                                        '#attributesTable tbody tr.table-primary')
                                    .data('id');
                                loadAttributeValues(attributeId); // Reload attribute values after successful update
                            }
                        );
                    } else {
                        swal(JsTranslations.oops, JsTranslations.msgNameEmpty, "warning");
                    }
                });
            });

            // Delete attribute
            $('#attributesTable').on('click', '.delete-btn', function() {
                const attributeId = $(this).data('id');
                if (confirm(JsTranslations.msgConfirmDelete)) {
                    $.ajax({
                        url: "php/JsonAttribute.php",
                        type: "POST",
                        data: `function=delete&id=${attributeId}`,
                        success: function(response) {
                            // Ensure response is parsed correctly
                            if (typeof response === "string") {
                                response = JSON.parse(response);
                            }

                            // Check for state "f" (failure) in a case-insensitive manner
                            if (response.state === "f") {
                                swal(JsTranslations.oops, response.message || JsTranslations.msgErrorAccured, "warning");
                            } else {
                                loadAttributes(); // Reload attributes on success
                            }
                            $("#divLoading").addClass("d-none");
                        },
                        error: function(jqXHR, exception) {
                            swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception),
                                "error");
                            $("#divLoading").addClass("d-none");
                        }
                    });
                }
            });

            // Delete attribute value
            $('#attributeValuesTable').on('click', '.delete-btn', function() {
                const valueId = $(this).data('id');
                if (confirm(JsTranslations.msgConfirmDelete)) {
                    $.ajax({
                        url: "php/JsonAttribute_Value.php",
                        type: "POST",
                        data: `function=delete&id=${valueId}`,
                        success: function(response) {
                            // Ensure response is parsed correctly
                            if (typeof response === "string") {
                                response = JSON.parse(response);
                            }

                            // Check for state "f" (failure) in a case-insensitive manner
                            if (response.state && response.state.toLowerCase() === "f") {
                                swal(JsTranslations.oops, response.message || JsTranslations.msgErrorAccured, "warning");
                            } else {
                                const attributeId = $('#attributesTable tbody tr.table-primary').data('id');
                                loadAttributeValues(attributeId); // Reload attribute values on success
                            }
                            $("#divLoading").addClass("d-none");
                        },
                        error: function(jqXHR, exception) {
                            swal(JsTranslations.oops, getAjaxErrorMessage(jqXHR, exception), "error");
                            $("#divLoading").addClass("d-none");
                        }
                    });
                }
            });

            // Handle row click to highlight with blue color
            $('#attributesTable tbody').on('click', 'tr', function() {

                // Prevent row selection if the row contains an input field (during creation)
                if ($(this).find('input').length > 0) {
                    return;
                }

                $('#attributesTable tbody tr').removeClass('table-primary');
                $(this).addClass('table-primary');

                const attributeId = $(this).data('id'); // Retrieve the data-id of the clicked row
                if (attributeId) {
                    loadAttributeValues(attributeId);
                } else {
                    swal(JsTranslations.oops, "Attribute ID is undefined.", "error");
                }
            });

            // attributeValue Table  Handle row click to highlight with blue color
            $('#attributeValuesTable tbody').on('click', 'tr', function() {
                $('#attributeValuesTable tbody tr').removeClass('table-primary');
                $(this).addClass('table-primary');
            });

        });
    </script>
</body>

</html>