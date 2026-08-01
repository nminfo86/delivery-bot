/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//*********************** ************** ********
var myAlert = $("#myAlert");

// Global chart instances
var salesChartInstance = null;
var topObjectsChartInstance = null;
var topCategoriesChartInstance = null;
var salesByCategoryChartInstance = null;
var topEarningsArticlesChartInstance = null;
var topEarningsCategoriesChartInstance = null;

var currentYear = new Date().getFullYear();
var startDateOfYear = "01-01-" + currentYear;
var endDateOfYear = "31-12-" + currentYear;

var startDateOfMonth = moment().startOf('month').format("DD-MM-YYYY");
var endDateOfMonth = moment().endOf('month').format("DD-MM-YYYY");

$(document).ready(function () {



    // ******************************** Charts js code ***********************//

    // Chart.js v3 plugin registration
    Chart.register(ChartDataLabels);

    getDailySales(10);

    // Call getSalesByCategoryByPeriod with the current month as default
    getSalesByCategoryByPeriod(startDateOfMonth, endDateOfMonth);

    // Call other functions with the current month as default
    getTopSoldObjects(startDateOfMonth, endDateOfMonth, "quantity");
    getTopSoldCategories(startDateOfMonth, endDateOfMonth, "quantity");

    getTopEarningsArticles(startDateOfMonth, endDateOfMonth);
    
    getTopEarningsCategories(startDateOfMonth, endDateOfMonth);

    //************************************************************************


    getUser($("#information").val());

    $('#choosePeriod').on('click', function (e) {
        e.preventDefault();

        // Create a custom input element
        var inputDiv = document.createElement("div");
        inputDiv.innerHTML = '<input id="swal-days-input" type="number" min="1" value="30" class="swal-content__input text-center" style="width:100%;padding:8px; font-size: 1.2rem;">';

        swal({
            title: JsTranslations.choose_last_days_title,
            content: inputDiv,
            buttons: {
                cancel: JsTranslations.cancel_button,
                confirm: JsTranslations.validate_button
            }
        }).then((value) => {
            if (value) {
                var days = Number($("#swal-days-input").val());
                if (isNaN(days) || days <= 0) {
                    swal(JsTranslations.oops, JsTranslations.invalid_days_warning, "warning");
                    return;
                }


                getDailySales(days);
            }
        });
    });

    $('#chooseCategoryPeriod').on('click', function (e) {
        e.preventDefault();

        // Create custom input elements for dates
        var inputDiv = document.createElement("div");
        inputDiv.innerHTML = `
            <label for="swal-start-date">${JsTranslations.start_date_label}</label>
            <input id="swal-start-date" type="text" class="swal-content__input" style="width:100%;padding:8px;margin-bottom:8px;">
            <label for="swal-end-date">${JsTranslations.end_date_label}</label>
            <input id="swal-end-date" type="text" class="swal-content__input" style="width:100%;padding:8px;">
        `;

        swal({
            title: JsTranslations.choose_period_title,
            content: inputDiv,
            buttons: {
                cancel: JsTranslations.cancel_button,
                confirm: JsTranslations.validate_button
            }
        }).then((value) => {
            if (value) {
                var startDate = $("#swal-start-date").val();
                var endDate = $("#swal-end-date").val();
                // Use moment.js for validation and formatting
                if (!startDate || !endDate) {
                    swal(JsTranslations.error_title, JsTranslations.missing_dates_error, "error");
                    return;
                }
                if (!moment(startDate, "DD-MM-YYYY", true).isValid() || !moment(endDate, "DD-MM-YYYY", true).isValid()) {
                    swal(JsTranslations.error_title, JsTranslations.invalid_date_format_error, "error");
                    return;
                }
                if (moment(startDate, "DD-MM-YYYY").isAfter(moment(endDate, "DD-MM-YYYY"))) {
                    swal(JsTranslations.oops, JsTranslations.start_after_end_warning, "warning");
                    return;
                }
                getSalesByCategoryByPeriod(startDate, endDate);
            }
        });

        // Activate jQuery UI datepicker after swal is rendered
        setTimeout(function () {
            // Find the swal content container
            var swalContent = $(".swal-content")[0] || document.body;
            $("#swal-start-date, #swal-end-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeMonth: true,
                changeYear: true,
                maxDate: 0,
                appendTo: swalContent // <-- This line fixes the position
            });
            var today = moment().format("DD-MM-YYYY");
            $("#swal-start-date").val(startDateOfMonth);
            $("#swal-end-date").val(endDateOfMonth);
        }, 100);
    });

    $('#chooseTopObjects').on('click', function (e) {
        e.preventDefault();
        showPeriodAndSortSwal({
            title: JsTranslations.top_objects_options_title,
            defaultStart: startDateOfYear,
            defaultEnd: endDateOfYear,
            defaultSort: "quantity"
        }, function (startDate, endDate, sortBy) {
            getTopSoldObjects(startDate, endDate, sortBy);
        });
    });

    $('#chooseTopCategories').on('click', function (e) {
        e.preventDefault();
        showPeriodAndSortSwal({
            title: JsTranslations.top_categories_options_title,
            defaultStart: startDateOfYear,
            defaultEnd: endDateOfYear,
            defaultSort: "quantity"
        }, function (startDate, endDate, sortBy) {
            getTopSoldCategories(startDate, endDate, sortBy);
        });
    });

    $('#chooseTopEarningsArticles').on('click', function (e) {
        e.preventDefault();

        // Create custom input elements for dates
        var inputDiv = document.createElement("div");
        inputDiv.innerHTML = `
            <label for="swal-start-date">${JsTranslations.start_date_label}</label>
            <input id="swal-start-date" type="text" class="swal-content__input" style="width:100%;padding:8px;margin-bottom:8px;">
            <label for="swal-end-date">${JsTranslations.end_date_label}</label>
            <input id="swal-end-date" type="text" class="swal-content__input" style="width:100%;padding:8px;">
        `;

        swal({
            title: JsTranslations.choose_period_title,
            content: inputDiv,
            buttons: {
                cancel: JsTranslations.cancel_button,
                confirm: JsTranslations.validate_button
            }
        }).then((value) => {
            if (value) {
                var startDate = $("#swal-start-date").val();
                var endDate = $("#swal-end-date").val();
                // Use moment.js for validation and formatting
                if (!startDate || !endDate) {
                    swal(JsTranslations.error_title, JsTranslations.missing_dates_error, "error");
                    return;
                }
                if (!moment(startDate, "DD-MM-YYYY", true).isValid() || !moment(endDate, "DD-MM-YYYY", true).isValid()) {
                    swal(JsTranslations.error_title, JsTranslations.invalid_date_format_error, "error");
                    return;
                }
                if (moment(startDate, "DD-MM-YYYY").isAfter(moment(endDate, "DD-MM-YYYY"))) {
                    swal(JsTranslations.oops, JsTranslations.start_after_end_warning, "warning");
                    return;
                }
                getTopEarningsArticles(startDate, endDate);
            }
        });

        // Activate jQuery UI datepicker after swal is rendered
        setTimeout(function () {
            // Find the swal content container
            var swalContent = $(".swal-content")[0] || document.body;
            $("#swal-start-date, #swal-end-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeMonth: true,
                changeYear: true,
                maxDate: 0,
                appendTo: swalContent // <-- This line fixes the position
            });
            var today = moment().format("DD-MM-YYYY");
            $("#swal-start-date").val(startDateOfMonth);
            $("#swal-end-date").val(endDateOfMonth);
        }, 100);
    });
    $('#chooseTopEarningsCategories').on('click', function (e) {
        e.preventDefault();

        // Create custom input elements for dates
        var inputDiv = document.createElement("div");
        inputDiv.innerHTML = `
            <label for="swal-start-date">${JsTranslations.start_date_label}</label>
            <input id="swal-start-date" type="text" class="swal-content__input" style="width:100%;padding:8px;margin-bottom:8px;">
            <label for="swal-end-date">${JsTranslations.end_date_label}</label>
            <input id="swal-end-date" type="text" class="swal-content__input" style="width:100%;padding:8px;">
        `;

        swal({
            title: JsTranslations.choose_period_title,
            content: inputDiv,
            buttons: {
                cancel: JsTranslations.cancel_button,
                confirm: JsTranslations.validate_button
            }
        }).then((value) => {
            if (value) {
                var startDate = $("#swal-start-date").val();
                var endDate = $("#swal-end-date").val();
                // Use moment.js for validation and formatting
                if (!startDate || !endDate) {
                    swal(JsTranslations.error_title, JsTranslations.missing_dates_error, "error");
                    return;
                }
                if (!moment(startDate, "DD-MM-YYYY", true).isValid() || !moment(endDate, "DD-MM-YYYY", true).isValid()) {
                    swal(JsTranslations.error_title, JsTranslations.invalid_date_format_error, "error");
                    return;
                }
                if (moment(startDate, "DD-MM-YYYY").isAfter(moment(endDate, "DD-MM-YYYY"))) {
                    swal(JsTranslations.oops, JsTranslations.start_after_end_warning, "warning");
                    return;
                }
                getTopEarningsCategories(startDate, endDate);
            }
        });

        // Activate jQuery UI datepicker after swal is rendered
        setTimeout(function () {
            // Find the swal content container
            var swalContent = $(".swal-content")[0] || document.body;
            $("#swal-start-date, #swal-end-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeMonth: true,
                changeYear: true,
                maxDate: 0,
                appendTo: swalContent // <-- This line fixes the position
            });
            var today = moment().format("DD-MM-YYYY");
            $("#swal-start-date").val(startDateOfMonth);
            $("#swal-end-date").val(endDateOfMonth);
        }, 100);
    });

    // ************* FUNCTIONS ******************//
    function getDailySales(daysBack) {
        $.ajax({
            url: "php/JsonDashBoard.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getDailySales&daysBack=" + daysBack,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(myAlert, data.message);
                } else {
                    var chartData = [];
                    var labels = [];
                    var dates = [];

                    for (var i in data) {
                        chartData.push(data[i].earning);
                        dates.push(data[i].date); // This is the actual date (e.g., 10-06-2025)

                        // Use moment.js to format the date name
                        if (JsCmsLanguage === 'ar') {
                            labels.push(moment(data[i].date, "YYYY-MM-DD").locale('ar').format('dddd'));
                        } else if (JsCmsLanguage === 'fr') {
                            labels.push(moment(data[i].date, "YYYY-MM-DD").locale('fr').format('dddd'));
                        }
                        else {
                            labels.push(data[i].day);
                        }
                    }
                    createSalesChart(chartData, labels, dates);

                    // Set the period text
                    $("#salesPeriod").text(
                        JsTranslations.last_days_period.replace("{days}", daysBack)
                    );
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function getSalesByCategoryByPeriod(startDate, endDate) {
        // If startDate and endDate are provided, use them; otherwise, use the default period

        $.ajax({
            url: "php/JsonDashBoard.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=salesByCategoryByPeriod&startDate=" + startDate + "&endDate=" + endDate,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {
                        salesByCategoryChartInstance = resetChartInstance(salesByCategoryChartInstance, "#salesByCategoryChart");
                        $("#categoryPeriod").text("");
                    }
                    showAlertFailed(myAlert, data.message);
                } else {
                    var chartData = [];
                    var labels = [];
                    for (var i in data) {
                        chartData.push(data[i].percentage);
                        labels.push(data[i].category);
                    }
                    createSalesByCategoryChart(chartData, labels);
                    // Set the period text
                    $("#categoryPeriod").text(
                        JsTranslations.period_range
                            .replace("{start}", moment(startDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                            .replace("{end}", moment(endDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                    );
                }

                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }

        });
    }


    function getTopSoldObjects(startDate, endDate, sortBy) {
        $.ajax({
            url: "php/JsonDashBoard.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getTopSoldObjects&startDate=" + startDate + "&endDate=" + endDate + "&sortBy=" + sortBy,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {
                        topObjectsChartInstance = resetChartInstance(topObjectsChartInstance, "#topObjectsChart");
                        $("#topObjectsPeriod").text("");
                    }
                    showAlertFailed(myAlert, data.message);
                } else {
                    var chartData = [];
                    var labels = [];
                    for (var i in data) {
                        // Assuming data[i].quantity is the quantity sold and data[i].title is the object title
                        // Check if sortBy is 'amount' or 'quantity' and push the appropriate value
                        if (sortBy === "amount") {
                            chartData.push(data[i].amount);
                            let label = data[i].title + " - " + formatNumber(data[i].amount);
                            if (JsCmsLanguage === 'ar') {
                                labels.push(label + " " + cmsCurrency);
                            } else {
                                labels.push(label + " " + cmsCurrency);
                            }
                        } else {
                            chartData.push(data[i].quantity);
                            labels.push(data[i].title + " - " + formatNumber(data[i].quantity));
                        }
                    }
                    createTopObjectsChart(chartData, labels, sortBy);
                    // Set the period text
                    $("#topObjectsPeriod").text(
                        JsTranslations.period_range
                            .replace("{start}", moment(startDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                            .replace("{end}", moment(endDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                    );
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function getTopSoldCategories(startDate, endDate, sortBy) {
        $.ajax({
            url: "php/JsonDashBoard.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getTopSoldCategories&startDate=" + startDate + "&endDate=" + endDate + "&sortBy=" + sortBy,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {
                        topCategoriesChartInstance = resetChartInstance(topCategoriesChartInstance, "#topCategoriesChart");
                        $("#topCategoriesPeriod").text("");
                    }
                    showAlertFailed(myAlert, data.message);
                } else {
                    var chartData = [];
                    var labels = [];
                    for (var i in data) {
                        if (sortBy === "amount") {
                            chartData.push(data[i].amount);
                            let label = data[i].category + " - " + formatNumber(data[i].amount);
                            if (JsCmsLanguage === 'ar') {
                                labels.push(label + " " + cmsCurrency);
                            } else {
                                labels.push(label + " " + cmsCurrency);
                            }
                        } else {
                            chartData.push(data[i].quantity);
                            labels.push(data[i].category + " - " + formatNumber(data[i].quantity));
                        }
                    }
                    createTopCategoriesChart(chartData, labels, sortBy);
                    // Set the period text
                    $("#topCategoriesPeriod").text(
                        JsTranslations.period_range
                            .replace("{start}", moment(startDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                            .replace("{end}", moment(endDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                    );
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    function getTopEarningsArticles(startDate, endDate) {
        $.ajax({
            url: "php/JsonDashBoard.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getTopEarningArticles&startDate=" + startDate + "&endDate=" + endDate,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {
                        topEarningsArticlesChartInstance = resetChartInstance(topEarningsArticlesChartInstance, "#topEarningsArticlesChart");
                        $("#topEarningsArticlesPeriod").text("");
                    }
                    showAlertFailed(myAlert, data.message);
                } else {
                    var chartData = [];
                    var labels = [];
                    for (var i in data) {
                        chartData.push(data[i].earning);
                        // Show attribute values if present
                        let label = data[i].title;
                        labels.push(label);
                    }
                    createTopEarningsArticlesChart(chartData, labels);
                    // Set the period text if you want
                    $("#topEarningsArticlesPeriod").text(
                        JsTranslations.period_range
                            .replace("{start}", moment(startDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                            .replace("{end}", moment(endDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                    );
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }
    function getTopEarningsCategories(startDate, endDate) {
        $.ajax({
            url: "php/JsonDashBoard.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getTopEarningCategories&startDate=" + startDate + "&endDate=" + endDate,
            beforeSend: function () {
                $("#divLoadingcms").removeClass("d-none");
            },
            success: function (data) {
                if (data.state === "f") {
                    if (data.message === noDataFound) {
                        topEarningsCategoriesChartInstance = resetChartInstance(topEarningsCategoriesChartInstance, "#topEarningsCategoriesChart");
                        $("#topEarningsCategoriesPeriod").text("");
                    }
                    showAlertFailed(myAlert, data.message);
                } else {
                    var chartData = [];
                    var labels = [];
                    for (var i in data) {
                        chartData.push(data[i].earning);
                        // Show attribute values if present
                        let label = data[i].category;
                        labels.push(label);
                    }
                    createTopEarningsCategoriesChart(chartData, labels);
                    // Set the period text if you want
                    $("#topEarningsCategoriesPeriod").text(
                        JsTranslations.period_range
                            .replace("{start}", moment(startDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                            .replace("{end}", moment(endDate, "DD-MM-YYYY").format("DD-MM-YYYY"))
                    );
                }
                $("#divLoadingcms").addClass("d-none");
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#divLoadingcms").addClass("d-none");
            }
        });
    }

    // *********************** CHART.JS  **************************************
    function createSalesChart(data, labels, dates) {
        var ctx = $('#earningsChart');
        if (salesChartInstance) {
            salesChartInstance.destroy();
        }
        salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'C.A',
                    data: data,
                    borderWidth: 3,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    lineTension: 0.3,
                    fill: true,
                    // Store the dates array as custom data for tooltips
                    dates: dates
                }]
            },
            options: {
                // Chart.js v3 RTL support
                locale: JsCmsLanguage,
                rtl: JsCmsLanguage === 'ar',
                textDirection: JsCmsLanguage === 'ar' ? 'rtl' : 'ltr',
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (context) {
                                var item = context[0];
                                var dataset = item.chart.data.datasets[item.datasetIndex];
                                var date = dataset.dates[item.dataIndex];
                                var dayLabel = item.label;
                                return dayLabel + " " + moment(date, "YYYY-MM-DD").format("DD-MM-YYYY");
                            }
                        }
                    },
                    datalabels: {
                        formatter: function (value, context) {
                            return Number(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                        },
                        display: function (context) {
                            // Hide datalabels if screen width is 600px or less (mobile)
                            return window.innerWidth > 600;
                        },
                        align: 'top',
                        anchor: 'center',
                        offset: 8,
                        color: '#4e73df',
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    },
                    legend: {
                        display: false,
                        labels: {
                            color: '#555',
                            font: {
                                size: 12
                            },
                            padding: 15,
                            textAlign: JsCmsLanguage === 'ar' ? 'right' : 'left',
                            align: JsCmsLanguage === 'ar' ? 'end' : 'start',
                        },
                        position: JsCmsLanguage === 'ar' ? 'left' : 'right',
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: JsCmsLanguage === 'ar' ? 'right' : 'left',
                        ticks: {
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            color: '#555',
                            callback: function (value, index, values) {
                                return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            lineWidth: 0.5
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                weight: 'bold',
                                size: 12
                            },
                            color: '#555',
                            align: JsCmsLanguage === 'ar' ? 'end' : 'start',
                        },
                        grid: {
                            display: false
                        },
                        position: 'bottom',
                        reverse: JsCmsLanguage === 'ar',
                    }
                }
            }
        });
    }

    function createSalesByCategoryChart(data, labels) {
        var ctx = $('#salesByCategoryChart');
        if (salesByCategoryChartInstance) {
            salesByCategoryChartInstance.destroy();
        }
        salesByCategoryChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e",
                        "#e74a3b", "#858796", "#5a5c69", "#e83e8c"]
                }]
            },
            options: {
                // Chart.js v3 RTL support
                locale: JsCmsLanguage,
                rtl: JsCmsLanguage === 'ar',
                textDirection: JsCmsLanguage === 'ar' ? 'rtl' : 'ltr',
                plugins: {
                    datalabels: {
                        formatter: function (value, context) {
                            return Number(value) + "%";
                        },
                        display: true, // Always show labels
                        color: 'white',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        textStrokeColor: 'rgba(0, 0, 0, 0.5)',
                        textStrokeWidth: 1,
                        align: 'right',
                        anchor: 'center'
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#555',
                            font: {
                                size: 12
                            },
                            padding: 15,
                            textAlign: JsCmsLanguage === 'ar' ? 'right' : 'left'
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                cutout: '50%' // Chart.js v3 syntax for doughnut
            }
        });
    }


    function createTopObjectsChart(data, labels, sortBy) {
        var ctx = $('#topObjectsChart');
        if (topObjectsChartInstance) {
            topObjectsChartInstance.destroy();
        }
        topObjectsChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: sortBy === "amount" ? 'Valeur' : 'Quantité',
                    data: data,
                    borderWidth: 0,
                    backgroundColor: "#36b9cc",
                }]
            },
            options: {
                locale: JsCmsLanguage,
                rtl: JsCmsLanguage === 'ar',
                textDirection: JsCmsLanguage === 'ar' ? 'rtl' : 'ltr',
                indexAxis: 'y',
                plugins: {
                    datalabels: {
                        formatter: function (value, context) {
                            if (sortBy === "amount") {
                                return formatNumber(value) + " " + cmsCurrency;
                            }
                            return value;
                        },
                        display: false,
                        align: 'end',
                        anchor: 'end',
                        offset: 4,
                        color: '#333',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: false,
                        beginAtZero: true,
                        position: 'bottom',
                        reverse: JsCmsLanguage === 'ar',
                        ticks: {
                            callback: function (value, index, values) {
                                if (sortBy === "amount") {
                                    if (JsCmsLanguage === 'ar') {
                                        return formatNumber(value) + " " + cmsCurrency;
                                    }
                                    return formatNumber(value) + " " + cmsCurrency;
                                }
                                return value;
                            },
                            color: '#555',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        position: JsCmsLanguage === 'ar' ? 'right' : 'left',
                        ticks: {
                            color: '#555',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function createTopCategoriesChart(data, labels, sortBy) {
        var ctx = $('#topCategoriesChart');
        if (topCategoriesChartInstance) {
            topCategoriesChartInstance.destroy();
        }
        topCategoriesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: sortBy === "amount" ? 'Valeur' : 'Quantité',
                    data: data,
                    borderWidth: 0,
                    backgroundColor: [
                        // "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e",
                        // "#e74a3b", "#858796", "#5a5c69", "#e83e8c",
                        // "#20c9a6", "#3abaf4"
                         "#4e73df"
                    ]
                }]
            },
            options: {
                locale: JsCmsLanguage,
                rtl: JsCmsLanguage === 'ar',
                textDirection: JsCmsLanguage === 'ar' ? 'rtl' : 'ltr',
                indexAxis: 'y',
                plugins: {
                    datalabels: {
                        formatter: function (value, context) {
                            if (sortBy === "amount") {
                                return formatNumber(value) + " " + cmsCurrency;
                            }
                            return value;
                        },
                        display: false,
                        align: 'end',
                        anchor: 'end',
                        offset: 4,
                        color: '#333',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: false,
                        beginAtZero: true,
                        position: 'bottom',
                        reverse: JsCmsLanguage === 'ar',
                        ticks: {
                            callback: function (value, index, values) {
                                if (sortBy === "amount") {
                                    if (JsCmsLanguage === 'ar') {
                                        return formatNumber(value) + " " + cmsCurrency;
                                    }
                                    return formatNumber(value) + " " + cmsCurrency;
                                }
                                return value;
                            },
                            color: '#555',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        position: JsCmsLanguage === 'ar' ? 'right' : 'left',
                        ticks: {
                            color: '#555',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function createTopEarningsArticlesChart(data, labels) {
        var ctx = $('#topEarningsArticlesChart');
        if (topEarningsArticlesChartInstance) {
            topEarningsArticlesChartInstance.destroy();
        }
        topEarningsArticlesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: JsTranslations.earnings_label || 'Earnings',
                    data: data,
                    borderWidth: 0,
                    backgroundColor: [
                        "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e",
                        "#e74a3b", "#858796", "#5a5c69", "#e83e8c",
                        "#20c9a6", "#3abaf4"
                    ]
                }]
            },
            options: {
                locale: JsCmsLanguage,
                rtl: JsCmsLanguage === 'ar',
                textDirection: JsCmsLanguage === 'ar' ? 'rtl' : 'ltr',
                indexAxis: 'x', // vertical bars
                plugins: {
                    datalabels: {
                        formatter: function (value, context) {
                           return '\u200E' + formatNumber(value) + ' ' + cmsCurrency;
                        },
                        display: function (context) {
                            // Hide datalabels if screen width is 768px or less (mobile)
                            // return window.innerWidth > 760; 
                            return false
                        },
                        align: 'end',
                        anchor: 'end',
                        offset: 10,
                        color: '#333',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        rotation: -45 // <-- Incline the value labels
                    },
                    legend: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        position: 'bottom',
                        reverse: JsCmsLanguage === 'ar',
                        ticks: {
                            color: '#555',
                            font: {
                                size: 12
                            },
                            maxRotation: 45, // <-- Incline the x-axis labels (titles)
                            minRotation: 20
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        position: JsCmsLanguage === 'ar' ? 'right' : 'left',
                        ticks: {
                            color: '#555',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function createTopEarningsCategoriesChart(data, labels) {
        var ctx = $('#topEarningsCategoriesChart');
        if (topEarningsCategoriesChartInstance) {
            topEarningsCategoriesChartInstance.destroy();
        }
        topEarningsCategoriesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: JsTranslations.earnings_label || 'Earnings',
                    data: data,
                    borderWidth: 0,
                    backgroundColor: [
                        "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e",
                        "#e74a3b", "#858796", "#5a5c69", "#e83e8c",
                        "#20c9a6", "#3abaf4"
                    ]
                }]
            },
            options: {
                locale: JsCmsLanguage,
                rtl: JsCmsLanguage === 'ar',
                textDirection: JsCmsLanguage === 'ar' ? 'rtl' : 'ltr',
                indexAxis: 'x', // vertical bars
                plugins: {
                    datalabels: {
                        formatter: function (value, context) {
                           return '\u200E' + formatNumber(value) + ' ' + cmsCurrency;
                        },
                        display: function (context) {
                            // Hide datalabels if screen width is 768px or less (mobile)
                            // return window.innerWidth > 760; 
                            return false
                        },
                        align: 'end',
                        anchor: 'end',
                        offset: 10,
                        color: '#333',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        rotation: -45 // <-- Incline the value labels
                    },
                    legend: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        position: 'bottom',
                        reverse: JsCmsLanguage === 'ar',
                        ticks: {
                            color: '#555',
                            font: {
                                size: 12
                            },
                            maxRotation: 45, // <-- Incline the x-axis labels (titles)
                            minRotation: 20
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        position: JsCmsLanguage === 'ar' ? 'right' : 'left',
                        ticks: {
                            color: '#555',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    //********************************************************************

    function getUser(username) {

        $.ajax({
            url: "php/JsonUser.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getUserByUsername&" + "username=" + username,
            success: function (data) {
                if (data.state === "f") {
                    showAlertFailed(myAlert, data.message);
                } else {
                    $(".welcome-user h3").append(
                        JsTranslations.welcome_user
                            .replace("{familyName}", data[0].familyName)
                            .replace("{name}", data[0].name)
                    );
                }
            },
            error: function (jqXHR, exception) {
                showAlertFailed(myAlert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }

        });
    }

    function formatNumber(num) {
        
        // return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1 ');
    // Ensure it's a number and format with comma as thousands separator and dot as decimal
    return Number(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function showPeriodAndSortSwal(options, callback) {
        // options: { title, defaultStart, defaultEnd, defaultSort }
        var inputDiv = document.createElement("div");
        inputDiv.innerHTML = `
            <label>${JsTranslations.start_date_label}</label>
            <input id="swal-start-date" type="text" style="width:100%;padding:8px;margin-bottom:8px;">
            <label>${JsTranslations.end_date_label}</label>
            <input id="swal-end-date" type="text" style="width:100%;padding:8px;">
            <label>${JsTranslations.criteria_label}</label>
            <select id="swal-sort-by" style="width:100%;padding:8px;">
                <option value="quantity">${JsTranslations.quantity_option}</option>
                <option value="amount">${JsTranslations.amount_option}</option>
            </select>
        `;
        swal({
            title: options.title || JsTranslations.choose_period_title,
            content: inputDiv,
            buttons: {
                cancel: JsTranslations.cancel_button,
                confirm: JsTranslations.validate_button
            }
        }).then((value) => {
            if (value) {
                var startDate = $("#swal-start-date").val();
                var endDate = $("#swal-end-date").val();

                // Use moment.js for validation and formatting
                if (!startDate || !endDate) {
                    swal(JsTranslations.oops, JsTranslations.missing_dates_error, "warning");
                    return;
                }
                if (moment(startDate, "DD-MM-YYYY").isAfter(moment(endDate, "DD-MM-YYYY"))) {
                    swal(JsTranslations.oops, JsTranslations.start_after_end_warning, "warning");
                    return;
                }

                var sortBy = $("#swal-sort-by").val();
                callback(startDate, endDate, sortBy);
            }
        });

        setTimeout(function () {
            // Find the swal content container
            var swalContent = $(".swal-content")[0] || document.body;
            $("#swal-start-date, #swal-end-date").datepicker({
                dateFormat: "dd-mm-yy",
                changeMonth: true,
                changeYear: true,
                maxDate: 0,
                appendTo: swalContent // <-- This line fixes the position
            });
            var today = moment().format("DD-MM-YYYY");
            $("#swal-start-date").val(startDateOfMonth);
            $("#swal-end-date").val(endDateOfMonth);
            $("#swal-sort-by").val(options.defaultSort || "quantity");
        }, 100);
    }

    //function to reset chart instance and clear canvas before creating a new chart
    function resetChartInstance(instance, canvasSelector) {
    if (instance) {
        instance.destroy();
    }

    var canvas = $(canvasSelector)[0];
    if (canvas) {
        var context = canvas.getContext("2d");
        context.clearRect(0, 0, canvas.width, canvas.height);
    }

    return null;
}

});
