/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// ********** FILE VALIDATION GLOBAL VARIABLES **********
var imageFileSize = 5120; //size in Kb  5mb max
var videoFileSize = 40960; //size in Kb 40mb max


//*******************************************************

$(function () {

    // Load myValidation css automatically
    //    var cssLink = $("<link href='../css/myvalidation.css' rel='stylesheet'>");
    //    $("head").append(cssLink);
});

//Main validation function
function validateForm(form) {
    var validDiv = form.find(".validation-div");

    init_myValidation(form);
    validDiv.append("<ul class='ul-validation'></ul>");
    //    form.find(".validation-div")
    form.find("input[validation] , select[validation], textarea[validation], div[validation]").each(function (index, elem) {

        var result = validate($(this));

        if (result.length !== 0) {
            //            $("#ul-validation").append(result);
            form.find(".ul-validation").append(result);

            //add css to element if it's not valide
            //            $(this).addClass("not-valide");
            $(this).css("border-color", "#d9534f");

            //remove error css from element when it's clicked
            $(this).click(function () {
                //                $(this).removeClass("not-valide");
                $(this).css("border-color", "#66afe9");
            });
        }


    });

    if (form.find(".ul-validation li").length > 0) {
        //Show errors div
        validDiv.removeClass("hide-div");
        window.setTimeout(function () {
            validDiv.addClass("hide-div");
        }, 5500);
        return false
    } else {
        return true
    }
}

//Function that validate an input element and return a list item with message
// **********************************************************************************************************************
//FILES,IMAGE,VIDEO,NOTEMPTY,SELECTED,NUMBERS,MIXED,SPECIAL-MIXED,PARAGRAPH,EMBED-LINK,NOSPACE,NAME
//EMAIL,PHONE,PRICE,TRIM
//******************************************************************************************************************

function validate(element) {
    var listElement = "";

    var rules = createArray(element.attr("validation"));

    for (i = 0; i < rules.length; i++) {

        // FILES VALIDATION, THIS MUST BE SET BEFORE ANY FILE TYPE VALIDATION

        //IT IS COMMANTED BEACAUSE WE SEPARATED IMAGES AND VIDEOS FILE SIZE

        // IMAGE TYPE FILE VALIDATION
        if (rules[i] === "IMAGE") {

            if (element.val() == '') {
                listElement = "<li> " + JsTranslations.validation_no_file_selected+"</li>";
                break;
            } else {

                var file = getInputValue(element);

                var type = file.type;
                var size = file.size / 1024;

                if (!validFileTypeImage(type)) {
                    listElement = "<li> " + JsTranslations.validation_image_type_invalid+ "</li>";
                    break;
                } else {

                    if (size > imageFileSize) {
                        listElement = "<li> " + JsTranslations.validation_image_too_large + " " + imageFileSize / 1024 + " Mb.</li>";
                        break;
                    }
                }
            }


        }

        if (rules[i] === "VIDEO") {
            if (element.val() == '') {
                listElement = "<li> " + JsTranslations.validation_no_file_selected+"</li>";
                break;
            } else {

                var file = getInputValue(element);

                var type = file.type;
                var size = file.size / 1024;

                if (!validFileTypeVideo(type)) {
                    listElement = "<li> " + JsTranslations.validation_video_type_invalid + "</li>";
                    break;
                } else {

                    if (size > videoFileSize) {
                        listElement = "<li> " + JsTranslations.validation_video_too_large +" " + videoFileSize / 1024 + " Mb.</li>";
                        break;
                    }
                }

            }
        }
        // For IP inputs
        if (rules[i] === "IP") {
            var regexp = new RegExp(/^((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}|USB)$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " + JsTranslations.validation_ip_invalid + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "NOTEMPTY") {
            if (getInputValue(element) === "") {
                listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " + JsTranslations.validation_not_empty+"</li>";
                break;
            }
        }
        // For Checkboxs and Radio Buttons inputs
        if (rules[i] === "SELECTED") {
            if ((element).is("div")) {
                var checkedboxes = element.not(":has(input:radio:checked)").length;
                if (checkedboxes > 0) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_select_choice + "</li>";
                }
            } else {
                if ((getInputValue(element) === "0") || (getInputValue(element) === 'null')) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_must_be_selected + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "NUMBERS") {
            var regexp = new RegExp(/^[0-9]+$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_numbers_only + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "MIXED") {
            var regexp = new RegExp(/^[\u0600-\u06FFéèàêù\w\s-]+$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_letters_or_numbers + "</li>";
                    break;
                }
            }
        }
        //THIS VALIDATE TITLES IN FRENCHE LANGUAGE PLUS !?'
        if (rules[i] === "SPECIAL-MIXED") {
            var regexp = new RegExp(/^[\u0600-\u06FFéèàêù!?'/@.,°"\w\s-]+$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_letters_numbers_special + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "PARAGRAPH") {
            var regexp = new RegExp(/^[\u0600-\u06FFéèàù!?'/@;":.,.,،,()"\w\s-]+$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " + JsTranslations.validation_paragraph + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "PASSWORD") {
            if (getInputValue(element) =="NULL" || getInputValue(element) == '') {
                listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " + JsTranslations.validation_password_required + "</li>";
                break;
            }
        }
        //THIS VALIDATE YOUTUBE EMBED LINKS
        if (rules[i] === "EMBED-LINK") {
            var regexp = new RegExp(/^[/:.\w\s]+$/);
            if (!regexp.test(getInputValue(element))) {
                listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> "  +  JsTranslations.validation_embed_link + "</li>";
                break;
            }
        }
        if (rules[i] === "NOSPACE") {
            var regexp = new RegExp(/^(?!\s)\S*$/);
            if (!regexp.test(getInputValue(element))) {
                listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " + JsTranslations.validation_no_spaces + "</li>";
                break;
            }
        }
        if (rules[i] === "NAME") {
            var regexp = new RegExp(/^['\u0600-\u06FFa-zãàáäâẽèéëêìíïîõòóöôùúüûñç\s-]+$/i);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_invalid + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "EMAIL") {
            var regexp = new RegExp(/^([^@]+?)@(([a-z0-9]-*)*[a-z0-9]+\.)+([a-z0-9]+)$/i);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_invalid + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "PHONE") {
            var regexp = new RegExp(/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_invalid + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "PRICE") {
            //            /^(\d*([.,](?=\d{1}))?\d+)+((?!\2)[.,]\d\d)?$/
            var regexp = new RegExp(/^(\d*([.](?=\d{1}))?\d+)?$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_invalid + "</li>";
                    break;
                }
            }
        }
        if (rules[i] === "TRIM") {
            var regexp = new RegExp(/^[^\s].*[^\s]$/);
            if (!getInputValue(element) == '') {
                if (!regexp.test(getInputValue(element))) {
                    listElement = "<li>" + "<b>" + element.attr("placeholder") + "</b> " +  JsTranslations.validation_no_trim_spaces + "</li>";
                    break;
                }
            }
        }
    }
    return listElement;
}

// function that create an array from the Validation attributes in input elements
function createArray(string) {

    var array = [];
    var word = "";
    for (i = 0; i < string.length; i++) {
        //alert(string.length);
        if ((string[i] !== ",") && ((i + 1) !== string.length)) {
            word = word + string[i];

        } else {
            if ((i + 1) == string.length) {
                word = word + string[i];
            }
            array.push(word);
            word = "";
        }

    }
    return array;
}

// function that get value of an input element regarding the type attribute
function getInputValue(element) {

    var value;
    switch (element.attr('type')) {
        case 'file':
            value = element[0].files[0];
            break;
        //                case 'radio':
        //                    value = $('input[name="' + element.attr('name') + '"]:checked').val();
        //                    break;
        default:
            value = element.val();
            break;
    }

    return value;

}

function validFileTypeImage(fileType) {

    if ($.inArray(fileType, ['image/png', 'image/gif', 'image/jpeg', 'image/jpg']) === -1) {
        return false;
    } else {
        return true;
    }
}
function validFileTypeVideo(fileType) {

    if ($.inArray(fileType, ['video/mp4']) === -1) {
        return false;
    } else {
        return true;
    }
}
// function that initialise the validation div and set the CSS of elements to défault one
function init_myValidation(form) {
    form.find(".validation-div").addClass("hide-div");
    form.find(".ul-validation").remove();
    form.find("input[validation] , select[validation], textarea[validation]").each(function (index, elem) {
        $(this).removeClass("not-valide");
    });
}