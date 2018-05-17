/**
 * @file
 * Subscription Form.
 */
(function($) {
    Drupal.behaviors.am_subscribe = {
        attach: function(context, settings) {
            if (context == document) {
                // Subscribe Form
                $(".form-item.radio.ckd  label ").append("<div class='radio-control-indicator'></div>");
                $(".tab-grp ").append("<div class='hr'></div>");
                $('input.digital-subscription').click(function() {
                    $('.intrinsic-container-1').addClass('hidden');
                    $('.digital-subscription-container').removeClass('hidden');
                });
                $('input.print-subscription').click(function() {
                    $('.intrinsic-container-1').removeClass('hidden');
                    $('.digital-subscription-container').addClass('hidden');
                });
                window.addEventListener("message", function(e) {
                    if (e.origin != 'https://sfsdata.com') {
                        return;
                    }
                    var sfs_response = JSON.stringify(e.data);
                    recordSubscription(e.data.custid, e.data.email, e.data.orderid, sfs_response, e.data.firstname, e.data.lastname);
                }, false);
                // $('a').click(function() {
                //     var e = {
                //         orderid: "WG3423",
                //         custid: "",
                //         email: "gaurav.agrawal1993@mailinator.com",
                //         firstname: "HetalTest",
                //         lastname: "SagarTest",
                //     };
                //     var sfs_response = '{"orderid":"WG3423","custid":"","email":"gaurav.agrawal1993@mailinator.com","firstname":"HetalTest","lastname":"SagarTest"}';
                //     recordSubscription(e.custid, e.email, e.orderid, sfs_response, e.firstname, e.lastname);
                // });
            }

            function recordSubscription(custid, email, orderid, sfs_response, firstname, lastname) {
                jQuery.ajax({
                    type: 'POST',
                    url: '/subscription/record-subscription/ajax',
                    data: {
                        custid: custid,
                        email: email,
                        orderid: orderid,
                        sfs_response: sfs_response,
                        firstname: firstname,
                        lastname: lastname,
                    },
                    success: function(data) {
                        createEmailCookie(email);
                        var check_type = orderid.substring(0, 2);
                        if (check_type == 'WS') {
                            window.location.href = '/thank-you-becoming-subscribing-member';
                        } else {
                            window.location.href = '/thank-you-gift-subscription';
                        }
                    },
                    error: function(data) {
                        var data = data;
                    }
                });
            }

            function createEmailCookie(email) {
                // Create Email Cookie
                var date = new Date();
                date.setTime(date.getTime() + (15 * 1000));
                jQuery.cookie('am-sub-emailcookie', email, {
                    expires: date,
                    // path: '/',
                    // domain: document.domain
                });
            }
        }
    }
})(jQuery);
