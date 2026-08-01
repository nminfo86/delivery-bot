//$('#product_details_slider .item img').each(function() {
//  var imgSrc = $(this).attr('src');
//  $(this).parent().css({'background-image': 'url('+imgSrc+')'});
//  $(this).remove();
//});



$('[data-toggle="tooltip"]').tooltip();

var year = "&copy; 2015" + "-" + new Date().getFullYear() + " ";
$("#copyrightCms").html(year);

//Change the head title depending the body id of every page
$("#headtitle").append($("body").attr('id'));


// initialise moment plugin to french format

moment.locale('fr');

// Currency default format
// var dinarCurrency = value => currency(value, { separator: ' ', decimal: '.', symbol: '' });

// function formatAmount(amount) {
//     var formatted = parseFloat(amount).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

//     return formatted + " " + cmsCurrency; // e.g. "150.00 DZD"

// }
function formatAmount(amount) {
    // Normalize input to number (handles "12 345,67", "12,345.67", etc.)
    var n = (typeof amount === 'number') ? amount : extractNumber(String(amount));
    if (!isFinite(n)) n = 0;

    // Format with '.' decimal and spaces as thousands
    var formatted = n.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        useGrouping: true
    }).replace(/,/g, ' ');

    // Fix RTL jumbled order: isolate number as LTR, then anchor with LRM
    var LRI = '\u2066', PDI = '\u2069', LRM = '\u200E';
    return LRI + formatted + PDI + LRM + ' ' + cmsCurrency;
}
//Helper to extract Number Value from Amounts that have Currency, ...
//Used in Js Reports i/o
function extractNumber(str) {
    // Remove currency symbols, spaces, and non-numeric characters except comma and dot
    str = str.replace(/[^\d.,-]/g, '');
    // Replace comma with dot for decimal if needed (for French format)
    if (str.indexOf(',') > -1 && str.indexOf('.') === -1) {
        str = str.replace(',', '.');
    } else if (str.indexOf(',') > -1 && str.indexOf('.') > -1) {
        // Remove thousand separator
        str = str.replace(/\./g, '').replace(',', '.');
    }
    var num = parseFloat(str);
    return isNaN(num) ? 0 : num;
}

var dataTableLanguage = {
    sLengthMenu: "_MENU_",
    search: "<i class='fas fa-search'></i>",
    searchPlaceholder: JsTranslations.dT_searchPlaceholder,
    sProcessing: JsTranslations.dT_sProcessing,
    sZeroRecords: JsTranslations.dT_sZeroRecords,
    sInfo: JsTranslations.dT_sInfo,
    sInfoEmpty: JsTranslations.dT_sInfoEmpty,
    sInfoFiltered: JsTranslations.dT_sInfoFiltered,
    sSearch: JsTranslations.dT_sSearch,
    oPaginate: {
        sFirst: JsTranslations.T_oPaginate_sFirst,
        sPrevious: JsTranslations.dT_oPaginate_sPrevious,
        sNext: JsTranslations.dT_oPaginate_sNext,
        sLast: JsTranslations.dT_oPaginate_sLast,
    }
};


var chefPanelRefrechTimer = 15000; // 15 sec

//**************** GLOBAL VARIABLES AND MESSAGES ***********************//
//this two variables must be the same  as radio input elements values in ArticleManagement.php

// look in header.php for cmsCurrency
// var cmsCurrency = $("#headerCmsCurrency").val();


var MediaTypeImage = "IMG";
var MediaTypeVideo = "VID";
//

var orderPlaceOnTable = "onlocal";
var orderPlaceCarryWith = "Emporter";

var orderStateNew = "NEW";
var orderStateStarted = "STARTED";
var orderStateValid = "VALID";
var orderStateReady = "READY";
var orderStateNotified = "NOTIFIED"; //is used in Tv
var orderStateDelivred = "DELIVRED"; //is used in waiterHistory
var orderStatePayed = "PAYED";



var roleAdmin = "admin";
var roleChef = "chef";
var roleWaiter = "waiter";
var roleCheckout = "checkout";


var category_Pizza = "Pizza";

var category_1_4_Pizza = "1/4_Pizza";
var category_1_2_Pizza = "1/2_Pizza";


var user_error = "Ooops! there was a problem.";
var noDataFound = "no-data-found";
var data_exist = "data-exist";
var licence_limited = "licence-limited";
var licence_not_created = "licence_not_created";
var have_media = "have-media";
var last_price = "last-price";
var last_subOrder = "last-subOrder";
var userNotFound = "user-not-found";
var user_still_blocked = "user-still-blocked";
var user_still_connected = "user-still-connected";
var user_define_password = "define-password";


//**************** GLOBAL FUNCTIONS ********************//
function initAlert(alert) {
    alert.addClass("d-none");
}
function autoRemoveAlert(alert) {
    window.setTimeout(function () {
        alert.addClass("d-none");
    }, 6000);
}
function showAlertSuccess(alert, message) {
    alert.removeClass("d-none");
    alert.text(message);
    alert.removeClass("alert-danger");
    alert.removeClass("alert-warning");
    alert.addClass("alert-success");
    autoRemoveAlert(alert);
}
function showAlertFailed(alert, message) {
    alert.removeClass("d-none");
    alert.text(message);
    alert.removeClass("alert-success");
    alert.removeClass("alert-danger");

    if (message.includes("Cannot delete")) {
        alert.addClass("alert-warning");
    } else {
        alert.addClass("alert-danger");
        autoRemoveAlert(alert);
    }
}

// This function is used to get error message for all ajax calls
function getAjaxErrorMessage(jqXHR, exception) {
    var msg = '';
    if (jqXHR.status === 0) {
        msg = 'Not connect.\n Verify Network.';
    } else if (jqXHR.status === 404) {
        msg = 'Requested page not found. [404]';
    } else if (jqXHR.status === 500) {
        msg = 'Internal Server Error [500].';
    } else if (exception === 'parsererror') {
        msg = 'Requested JSON parse failed.';
    } else if (exception === 'timeout') {
        msg = 'Time out error.';
    } else if (exception === 'abort') {
        msg = 'Ajax request aborted.';
    } else if (exception === 'null') {
        msg = '';
    } else {
        msg = 'Uncaught Error.\n' + jqXHR.responseText;
    }
    return msg;
}


//resolve file system path of media links stored in DB
// Compute app base (without /mycms or deeper admin paths)
function cmsMediaBase() {
    var b = (window.CMS_MEDIA_URL_BASE || '').replace(/\/+$/,''); // trim trailing slashes
    // strip '/mycms' and anything after it
    b = b.replace(/\/mycms(?:\/.*)?$/i, '');
    return b;
}

// Resolve media URL on the client
function resolveMediaUrlJs(stored) {
    if (!stored) return '';
    var s = String(stored);
    if (/^https?:\/\//i.test(s)) return s; // absolute

    var legacy = (window.CMS_MEDIA_PATH_SUFFIX || '../..');
    if (s.indexOf(legacy) === 0) {
        s = s.substring(legacy.length); // strip legacy '../../'
    }
    // ensure single leading slash on relative part
    var rel = ('/' + s).replace(/\/{2,}/g, '/');
    var base = cmsMediaBase(); // e.g. '' or '/eatsmartly'

    return base + rel;
}

//this function is used to get order place value, it used in checkoutPanel.js, checkoutHistory.js, and waiterPanel.js
function getPlaceValue(place, tableName, table_id) {
    var placeValue = "";
    //if place is onTable
    if (place === orderPlaceOnTable && table_id !== null && tableName !== null) {
        placeValue = JsTranslations.on_table;
    }
    //if place is take away
     if (place === orderPlaceOnTable && table_id == null && tableName == null) {
            placeValue = "";
     }
    //  if place is not table and not take away
    if (place === orderPlaceCarryWith && table_id == null && tableName == null) {
        placeValue = JsTranslations.take_away ;
    }
    return placeValue;
}

switch ($("#sessionRole").val()) {
    case "superAdmin":
        $(".superAdminOptions").removeClass("d-none");
        break;
    case "admin":
        $(".bouhezilaCms").removeClass("d-none");
        $(".adminOptions").removeClass("d-none");
        //    $(".chefOptions").removeClass("d-none");
        //    $(".checkoutOptions").removeClass("d-none");
        //    $(".waiterOptions").removeClass("d-none");
        break;
    case "chef":
        $(".chefOptions").removeClass("d-none");
        break;
    case "waiter":
        $(".waiterOptions").removeClass("d-none");
        break;
    case "checkout":
        $(".checkoutOptions").removeClass("d-none");
        break;
    default:
}
$("#searchMobileNav").autocomplete({
    source: "mycms/php/JsonObject.php",
    select: function (e, ui) {
        e.preventDefault();
        window.location = cmsMediaBase() +"/product-details.php?object_id=" + ui.item.value + "&company_id=" + ui.item.company_id;
    }
});
$("#searchHeader").autocomplete({
    source: "mycms/php/JsonObject.php",
    select: function (e, ui) {
        e.preventDefault();
        window.location = cmsMediaBase() +"/product-details.php?object_id=" + ui.item.value + "&company_id=" + ui.item.company_id;
    }
});
$("#searchIndex").autocomplete({
    source: "mycms/php/JsonObject.php",
    select: function (e, ui) {
        e.preventDefault();
        window.location = cmsMediaBase() +"/product-details.php?object_id=" + ui.item.value + "&company_id=" + ui.item.company_id;
    }
});

function getFullDateTime() {
    var today = new Date();

    var date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();

    var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();

    var dateTime = date + ' ' + time;
    return dateTime;
}

//This is for closing menu window in mobile view after showing modal
$("#progression").click(function () {
    $("header").removeClass("bp-xs-on");
});

//This function return media query test by passing the screen size as parameter
function isMediaQuery(screenSize) {
    var mq = window.matchMedia("(max-width:" + screenSize + ")");

    if (mq.matches) {
        return true;
    } else {
        return false;
    }
}

// Show app version popup — relies on _appVersion set by the including page
function showAppVersion() {
    if (typeof _appVersion === 'undefined') return;

    var el = document.createElement('div');
    el.style.cssText = 'line-height:2; font-size:13px; text-align:left;';
    el.innerHTML =
        '<b>Version&nbsp;&nbsp;:</b> V' + _appVersion.current_version + '<br>' +
        '<b>Released&nbsp;:</b> '        + _appVersion.last_stable_update + '<br>' ;

    swal({
        title: _appVersion.app_name,
        content: el,
        type: 'info',
        confirmButtonColor: '#ffc107'
    });
}

//Function that make custom qte input functionnal using + and - buttons work
//Custom qte input is added to swal 
function toggleCustomQteInputMinusPlus() {
    $(document).on('click', '.sw-plus', function () {
        var customQteInput = $(this).closest('.custom-qty').find(".custom-qty-input");

        // Change Qte input value
        customQteInput.val(parseInt(customQteInput.val()) + 1);

        //Prevent input more than 100 qte
        if (customQteInput.val() > 100) {
            customQteInput.val(100);
        }
    });

    $(document).on('click', '.sw-minus', function () {
        var customQteInput = $(this).closest('.custom-qty').find(".custom-qty-input");

        // Change Qte input value
        customQteInput.val(parseInt(customQteInput.val()) - 1);

        //Prevent input less than 1 qte
        if (customQteInput.val() == 0) {
            customQteInput.val(1);
        }
    });
}

