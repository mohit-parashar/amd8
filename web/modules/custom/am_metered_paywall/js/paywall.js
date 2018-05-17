/**
 * @file
 * Paywall integration.
 */
(function($) {
    Drupal.behaviors.am_paywall = {
        attach: function(context, settings) {
            if (context == document) {
                // Paywall
                var metered_node_types = [];
                var metered_anonymous_paywall_status = drupalSettings.paywall.metered_anonymous_paywall_status;
                var metered_authenticate_paywall_status = drupalSettings.paywall.metered_authenticate_paywall_status;
                var metered_node_types = drupalSettings.paywall.metered_node_types;
                var metered_limit = drupalSettings.paywall.metered_limit;
                var current_user_state = drupalSettings.paywall.current_user_state;
                var current_content_type = drupalSettings.paywall.current_content_type;
                var metered_limit_auth = drupalSettings.paywall.metered_limit_auth;
                var metered_limit_email = drupalSettings.paywall.metered_limit_email;
                var is_norSubscriber_norDonor = drupalSettings.paywall.is_norSubscriber_norDonor;
                var paywall_env = (drupalSettings.paywall.env == 'live') ? '47' : '10';
                // var cookie_expire = drupalSettings.paywall.cookie_expire;
                var nid = drupalSettings.paywall.current_nid;
                // Get cookies status
                // !! will turn the falsy values to false. This will turn 0 to false!
                var emailcookiestatus = !!$.cookie('emailcookie');
                var meteredValuetatus = !!$.cookie('meteredValue');
                var authcookiestatus = !!$.cookie('authcookie');
                var partialauthcookiestatus = !!$.cookie('partialauthcookie');
                var paywallcookiestatus = !!$.cookie('paywallcookie');
                // var cookieExpiry = cookie_expire;
                var has_access = true;
                var show_email_not_confirmed = false;
                if (metered_node_types[current_content_type] != 0) {
                    // console.log("is in array");
                }
                if (!authcookiestatus && !partialauthcookiestatus && current_user_state == 0 && !isFacebookApp()) {
                    // If user is anonymous
                    // Create cookie 'meteredValue' if not exists Or update node ids in cookie upto limit.
                    // If email provided, then create a new cookie 'emailProvided'
                    if (metered_anonymous_paywall_status == 1) {
                        if (metered_node_types[current_content_type] != 0) {
                            var cookieArray = $.cookie('meteredValue') ? $.makeArray($.cookie('meteredValue').split(',')) : [];
                            if (!emailcookiestatus) {
                                if (cookieArray.length) {
                                    // If they've already gotten to read the article grant them access
                                    if (cookieArray.length >= metered_limit && $.inArray(nid, cookieArray) == -1) {
                                        has_access = false;
                                        // Create paywall cookie.
                                        createPaywalCookie();
                                        // Create unique cookie to track user.
                                        //var unique_cookie = createUniqueCookie();
                                        // alert(cookieArray);
                                        //insert_record(unique_cookie,cookieArray);
                                        check_paywall_abandon(cookieArray, nid);
                                    }
                                    // If the nid is not in the array and they're not over the limit.
                                    else if ($.inArray(nid, cookieArray) == -1 && !(cookieArray.length > metered_limit)) {
                                        cookieArray.push(nid); // add new nid to cookie.
                                        $.cookie('meteredValue', cookieArray.join(','), {
                                            // expires: new Date(2018, 10, 29, 11, 00, 00),
                                            expires: ExpireCookieOneMonth(),
                                            path: '/',
                                            domain: document.domain
                                        });
                                        // Remove payawall flag cookie if any.
                                        removePaywalCookie();
                                    }
                                }
                                else {
                                    $.cookie('meteredValue', nid, {
                                        expires: ExpireCookieOneMonth(),
                                        path: '/',
                                        domain: document.domain
                                    });
                                }
                            } else {
                                // Email cookie exists
                                var emailcookieArray = $.cookie('emailcookie') ? $.makeArray($.cookie('emailcookie').split(',')) : [];
                                // If they've already gotten to read the article grant them access
                                if (emailcookieArray.length >= metered_limit_email && $.inArray(nid, emailcookieArray) == -1) {
                                    has_access = false;
                                    show_email_not_confirmed = true;
                                }
                                // If the nid is not in the array and they're not over the limit.
                                else if ($.inArray(nid, emailcookieArray) == -1 && !(emailcookieArray.length > metered_limit_email)) {
                                    emailcookieArray.push(nid); // add new nid to cookie.
                                    $.cookie('emailcookie', emailcookieArray.join(','), {
                                        expires: ExpireCookieOneMonth(),
                                        path: '/',
                                        domain: document.domain
                                    });
                                }
                            }
                        }
                        if (!has_access && !show_email_not_confirmed) {
                            // alert('you have no access');
                            show_paywall_create_account_popup();

                            jQuery('#loginModal').modal({
                                backdrop: 'static',
                                keyboard: false
                            });
                            jQuery('#loginModal').find('.close').hide();
                            // jQuery("#loginModal .create-why").hide();
                            // jQuery("#loginModal .Join-reader-community").show();
                        } else if (!has_access && show_email_not_confirmed) {
                            confirm_your_email();

                            jQuery('#loginModal').modal({
                                backdrop: 'static',
                                keyboard: false
                            });
                            jQuery('#loginModal').find('.close').hide();
                        }
                    }
                } else {
                    // user is authenticated
                    // Email is verified
                    // Delete the anonymous paywall cookies if any.
                    if (metered_authenticate_paywall_status == 1) {
                        // if paywall for authenticate users is enable.
                        if (emailcookiestatus || meteredValuetatus) {
                            if (emailcookiestatus) {
                                $.removeCookie('emailcookie', {
                                    path: '/',
                                    domain: document.domain
                                });
                            };
                            if (meteredValuetatus) {
                                $.removeCookie('meteredValue', {
                                    path: '/',
                                    domain: document.domain
                                });
                            };
                        };
                        if (metered_node_types[current_content_type] != 0) {
                            // If user visits x no. of unique articles, show donation and subscription popup if user doesnot have subscription and donation role.
                            if (is_norSubscriber_norDonor) {
                                // Create cookie for authenticated user - 'authcookie'.
                                createAuthCookie();
                                var authcookieArray = $.cookie('authcookie') ? $.makeArray($.cookie('authcookie').split(',')) : [];
                                // If they've already gotten to read the article grant them access
                                if (authcookieArray.length >= metered_limit_auth && $.inArray(nid, authcookieArray) == -1) {
                                    has_access = false;
                                }
                                // If the nid is not in the array and they're not over the limit.
                                else if ($.inArray(nid, authcookieArray) == -1 && !(authcookieArray.length > metered_limit_auth)) {
                                    authcookieArray.push(nid); // add new nid to cookie.
                                    $.cookie('authcookie', authcookieArray.join(','), {
                                        expires: ExpireCookieTwentyYear(),
                                        path: '/',
                                        domain: document.domain
                                    });
                                }
                            }
                        }
                        if (!has_access && is_norSubscriber_norDonor) {
                            //alert('You are neither subscriber or donor. Please Subscribe or donate.');
                            //below two line is added to hide login and other div for anonymous user. as now anonymous user can also see donation/subscription popup.

                            jQuery('#loginModal section').hide();
                            jQuery('#loginModal').addClass("donate-subscribe");

                            jQuery('#loginModal #block-donateorsubscribemessage').show();

                            jQuery('#loginModal').modal({
                                backdrop: 'static',
                                keyboard: true
                            });
                            removeAuthCookie();
                            $.cookie('partialauthcookie', nid, {
                                expires: ExpireCookieTwentyYear(),
                                path: '/',
                                domain: document.domain
                            });

                        }
                    }
                } //Authenticated user paywall ends
                // Change submit input text if print subscription number is available.
                $("input[type=checkbox]#edit-is-print-subscriber").change(function() {
                    if ($(this).prop('checked')) {
                        $("#loginModal form#create-account-form .submit_button_login input:submit").val("Link my account");
                    } else {
                        $("#loginModal form#create-account-form .submit_button_login input:submit").val("Submit");
                    }
                });
                /**
                 * createEmailProvideCookie function sets new cookie 'emailcookie'.
                 * This is triggered when user provides his email id when meteredvalue cookie limit is reached
                 */
                $.fn.createEmailProvideCookie = function() {
                    // Create Email Cookie
                    if (!emailcookiestatus) {
                        $.cookie('emailcookie', nid, {
                            expires: ExpireCookieOneMonth(),
                            path: '/',
                            domain: document.domain
                        });
                    };
                };

                $.fn.activeUserOnBsd = function(mail) {
                    console.log(mail);
                    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
                    var xmlData = '';
                    xmlData = '<?xml version="1.0" encoding="utf-8"?><api><cons><cons_group id="15" /><cons_email><email>' + mail + '</email><email_type>personal</email_type><is_subscribed>1</is_subscribed><is_primary>1</is_primary></cons_email><cons_field id="' + paywall_env + '"><value>Yes</value></cons_field></cons></api>';
                    $.ajax({
                        type: "POST",
                        url: baseUrl + 'am_bsd_tools/set_constituent_data',
                        data: xmlData,
                        contentType: "text/xml",
                        success: function(result) {
                            console.log(result);
                        },
                        async: false
                    });
                };

                $.fn.byPassPayWall = function() {
                    if (emailcookiestatus) {
                        $.removeCookie('emailcookie', {
                            path: '/',
                            domain: document.domain
                        });
                    };
                    if (meteredValuetatus) {
                        $.removeCookie('meteredValue', {
                            path: '/',
                            domain: document.domain
                        });
                    };
                    removePaywalCookie();
                    createAuthCookie();
                    return false;
                };

                /**
                 * Function creates a unique timestamp cookie'.
                 *
                 */
                // function createUniqueCookie() {
                //     if (document.cookie.indexOf('am_an_') == -1) {
                //  var random_number = Math.floor(Math.random()*900) + 100;
                //  var d = new Date();
                //  var unique_cookie = 'am_an_'+random_number+''+d.getTime()+''+random_number;
                //     $.cookie(unique_cookie, nid, {
                //       expires: new Date(2018, 10, 29, 11, 00, 00),
                //       path: '/',
                //       domain: document.domain
                //     });
                // }
                //     return unique_cookie;
                // }
                /**
                 * createPaywalCookie indicates if user has reached paywall.
                 *
                 */
                function createPaywalCookie() {
                    // Create Paywall Cookie
                    if (!paywallcookiestatus) {
                        $.cookie('paywallcookie', 1, {
                            expires: ExpireCookieOneMonth(),
                            path: '/',
                        });
                    };
                }

                function removePaywalCookie() {
                    $.removeCookie('paywallcookie', {
                        path: '/',
                    });
                }
                /**
                 * createAuthCookie function sets new cookie 'authcookie'.
                 *
                 */
                function createAuthCookie() {
                    // Create Email Cookie
                    if (!authcookiestatus) {
                        $.cookie('authcookie', nid, {
                            expires: ExpireCookieTwentyYear(),
                            path: '/',
                            domain: document.domain
                        });
                    };
                }

                function removeAuthCookie() {
                    $.removeCookie('authcookie', {
                        path: '/',
                        domain: document.domain
                    });
                }
                /**
                 * confirm_your_email function displays email confirmation popup.
                 * This is triggered when emailcookie is reached to limit.
                 */
                function confirm_your_email() {
                    $("#loginModal .one-time-login").show();
                    $("#loginModal .forgot-password").hide();
                    $("#loginModal .user-login").hide();
                    $("#loginModal .create-account").hide();
                    $("#loginModal .social-login-block-form").hide();
                    $("#loginModal .create-why").hide();
                    $("#loginModal .login-why").hide();
                    $("#loginModal .registration-form.one-time-login.block").css('width', '100%');
                    $("#loginModal .registration-form.one-time-login.block").css('border', 'none');
                    $("#loginModal .form-type-checkbox.form-item-has-password").hide();
                    $("#loginModal .create-account-link").hide();
                    // $("#loginModal .submit_button_login input:submit").val("Send me a new registration link");
                    $("#loginModal .submit_button_login input:submit").val("Confirm your email address");
                    $("#loginModal input#edit-form-ty").val(1);
                    $("#loginModal .one-time-login").addClass('request-new-mail');
                    $("#loginModal .email-not-confirmed").show();
                    $('#loginModal input#edit-has-password').prop('checked', false);
                }
                /**
                 * show_paywall_create_account_popup function displays create account popup.
                 * This is triggered when meteredValue is reached to limit.
                 */
                function show_paywall_create_account_popup() {
                    $("#loginModal .one-time-login").hide();
                    $("#loginModal .forgot-password").hide();
                    $("#loginModal .user-login").hide();
                    $("#loginModal .create-account").show();
                    $('.social_login iframe').css('height', '160px');
                    $("#loginModal .social-login-block-form").show();
                    // Message Content
                    $("#loginModal .create-why").hide();
                    $("#loginModal .email-not-confirmed").hide();
                    $("#loginModal .Join-reader-community").show();
                    $("#loginModal .login-why").hide();
                    $("section.create-account").addClass("create-account-paywall");
                    $("div.create-account-link").removeClass("create-account-link").addClass("create-account-link-paywall");
                }
                $(".create-account-link-paywall").on('click', function() {
                    $("#loginModal .one-time-login").hide();
                    $("#loginModal .forgot-password").hide();
                    $("#loginModal .user-login").hide();
                    $("#loginModal .create-account").show();
                    $("#loginModal .social-login-block-form").show();
                    // Message Content
                    $("#loginModal .login-why").hide();
                    $("#loginModal .create-why").hide();
                    $("#loginModal .Join-reader-community").show();
                });
                // function insert_record(unique_cookie,cookieArray){
                //     // Insert unique cookie
                //     if (!(unique_cookie === undefined)) {
                //         $.ajax({
                //             type: 'POST',
                //             url: '/paywall/statistics/insert_record/ajax',
                //             data: {
                //               unique_cookie: unique_cookie,
                //               first_threshold_metered_nodes: cookieArray.join(),
                //               first_threshold_last_metered_node: cookieArray[cookieArray.length-1],
                //             },
                //             //contentType: 'application/json; charset=utf-8',
                //             //dataType:'json',
                //             success: function(data) {
                //               console.log("success");
                //             },
                //             error: function(data) {
                //               console.log("error");
                //             }
                //           });
                //     }
                // }
                function paywall_statistics_record(cookieArray, event, current_nid) {
                    jQuery.ajax({
                        type: 'POST',
                        async: false,
                        url: '/paywall/statistics/record/ajax',
                        data: {
                            nodes: cookieArray.join(),
                            event: event,
                            current_nid: current_nid,
                        },
                        success: function(data) {
                            console.log("success");
                        },
                        error: function(data) {
                            console.log("error");
                        }
                    });
                }

                function check_paywall_abandon(cookieArray, nid) {
                    $(window).on('unload', function() {
                        if (!!$.cookie('paywallcookie')) {
                            // Record abandon statistics
                            paywall_statistics_record(cookieArray, 0, nid);
                        }
                    });
                }

                function ExpireCookieOneMonth() {
                    // One month
                    var date = new Date();
                    date.setTime(date.getTime() + (43800 * 60 * 1000));
                    return date;
                }


                function ExpireCookieTwentyYear() {
                    // One month
                    var date = new Date();
                    date.setTime(date.getTime() + (3600 * 1000 * 24 * ((365 * 20) + (20/4))));
                    return date;
                }

                // Check if site open in facebook app browser.
                function isFacebookApp() {
                    var ua = navigator.userAgent || navigator.vendor || window.opera;
                    return (ua.indexOf("FBAN") > -1) || (ua.indexOf("FBAV") > -1);
                }
            }
        }
    }
})(jQuery);
