/* 
 * This is the ajax of the environement management page
 */
//**************************** PARAMS************************************/
//******************** AJAX PARAMS********/
var dataType = "JSON";

//******************** GENARL PARAMS********/
var alert = $("#myAlert");
var form = $("#loginForm");
//*********************** ************** ********

$(document).ready(function () {

    form.submit(function (event) {
        event.preventDefault();
        if (validateForm(form)) {

            getUserByUsernamePsw($("#username").val(), ($("#password").val()));

            form[0].reset();
            init_myValidation(form);
        }
    });

    //************* FUNCTIONS *********************//

    function getUserByUsernamePsw(username, password) {
        $.ajax({
            url: "php/Authentication.php",
            type: "POST",
            jsonp: false,
            dataType: dataType,
            data: "function=getUserByUsernamePassword&" + "username=" + username + "&password=" + password,
            success: function (data) {
                if (data.state === "f") {
                    if (data.message.search(userNotFound) >= 0) {
                        showAlertFailed(alert, JsTranslations.msgUserNotFound);
                    }
                    if (data.message.search(noDataFound) >= 0) {
                        showAlertFailed(alert, JsTranslations.msgPswNotMatch);
                    }
                    if (data.message.search(user_still_blocked) >= 0) {
                        showAlertFailed(alert, JsTranslations.msgUserStillBlocked);
                    }
                    if (data.message.search(user_still_connected) >= 0) {
                        showAlertFailed(alert, JsTranslations.msgUserStillConnected);
                    }

                    if (data.message.search(JsTranslations.user_error) >= 0) {
                        showAlertFailed(alert, data.message);
                    }
                } else {
                    showAlertSuccess(alert, username + " " + JsTranslations.msgUserRedirect);
                    alert.append("  <img src='images/misc/ajax-loader.gif' style='text-align:right'>")
                    window.setTimeout(function () {
                        document.location = "odkhol.php"
                    }, 3000);
                }
            },
            error: function (jqXHR, exception) {
                showAlertFailed(alert, getAjaxErrorMessage(jqXHR, exception));
                $("#loadingImage").addClass("d-none");
            }

        });
    }

});
