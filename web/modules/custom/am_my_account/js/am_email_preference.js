// Show loader image on ajax start.
function startloading() {
   jQuery('.form-submit').attr('disabled', true);
    jQuery('#loading').css('display', 'block');
    jQuery('#success_tic').css('display', 'none');
}
// Hide loader image on ajax stop.
function stoploading() {
    jQuery('.form-submit').removeAttr('disabled');
    jQuery('#loading').css('display', 'none');
    jQuery('#success_tic').css('display', 'inline-block');
    setTimeout(function() {
        jQuery('#success_tic').css('display', 'none');
    }, 3000);
}
// Show loader image on ajax start.
function sfsstartloading() {
    jQuery('#edit-sfs-update-contact').val('Processing...');
    jQuery('.form-submit').attr('disabled', true);
    jQuery('#loading-member').css('display', 'inline-block');
}
// Hide loader image on ajax stop.
function sfsstoploading() {
    jQuery('#edit-sfs-update-contact').val('Update Contact');
    jQuery('.form-submit').removeAttr('disabled');
    jQuery('#loading-member').css('display', 'none');
}
(function($) {
    // Get Base url.
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    // Update email preference.
    $('#edit-update-preference').click(function() {
        var addgroup = '';
        var addGroupCount = 0;
        var cons_id = $('#edit-field-cons-id-0-value').val();
        startloading();
        $('#edit-email-preference--wrapper input[type=checkbox]').each(function() {
            var postdata = {
                cons_id: cons_id,
                group_id: $(this).val()
            };
            if ($(this).is(":checked")) {
                addGroupCount++;
                addgroup += '<cons_group id="' + $(this).val() + '" />';
                $(this).attr('prechecked', true);
            } else {
                $.ajax({
                    type: "POST",
                    url: baseUrl + 'am_bsd_tools/remove_cons_ids_from_group',
                    data: postdata,
                    success: function(result) {
                        $("#edit-email-preference-" + result).attr('prechecked', false);
                        console.log(result);
                    },
                    async: false
                });
            }
        });
        if (addGroupCount > 0) {
            var xmlData = '<?xml version="1.0" encoding="utf-8"?><api><cons id="' + cons_id + '">' + addgroup + '</cons></api>';
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
        }
        stoploading();
        return false;
    });
    // Update sfs contact details on my account page.
    $('#edit-sfs-update-contact').click(function() {
        //$('#edit-sfs-address1').val('10261754');
        var subscription_no = $('#edit-field-print-subscription-number-0-value').val().trim();
        var FirstName = $('#edit-field-first-name-0-value').val().trim();
        var LastName = $('#edit-field-last-name-0-value').val().trim();
        var address1 = $('#edit-sfs-address1').val().trim();
        var address2 = $('#edit-sfs-address2').val().trim();
        var city = $('#edit-sfs-city').val().trim();
        var state = $('#edit-sfs-state').val().trim();
        var ZipPostal = $('#edit-sfs-zipcode').val().trim();
        var country = $('#edit-sfs-country').val();
        var email = $('#edit-mail').val();
        var telephone = $('#edit-sfs-telephone').val();
        if (address1 == '') {
            alert('Address 1 is required');
            $('#edit-sfs-address1').val('');
            $("#edit-sfs-address1").focus();
            return false;
        }
        if (city == '') {
            alert('city is required');
            $('#edit-sfs-city').val('');
            $("#edit-sfs-city").focus();
            return false;
        }
        if (state == '') {
            alert('State is required');
            $('#edit-sfs-state').val('');
            $("#edit-sfs-state").focus();
            return false;
        }
        sfsstartloading();
        var postdata = {
            CustomerID: subscription_no,
            FirstName: FirstName,
            LastName: LastName,
            //Company : '',
            Address1: address1,
            Address2: address2,
            City: city,
            StateProv: state,
            ZipPostal: ZipPostal,
            CountryCode: country,
            Email: email,
            Phone: telephone,
        };
        $.ajax({
            type: "POST",
            url: baseUrl + 'am_sfs_integration/update_address',
            data: postdata,
            success: function(result) {
                $("#edit-email-preference-" + result).attr('prechecked', false);
                if (result) {
                    $('#sfs_add').html($('#edit-sfs-address1').val() + ' ' + $('#edit-sfs-address2').val() + ' <br>' + $('#edit-sfs-city').val() + ' ' + $('#edit-sfs-state').val() + ' ' + $('#edit-sfs-zipcode').val() + ' <br>' + $('#edit-sfs-country').val());
                    setTimeout(function() {
                        sfsstoploading();
                        $(".contact-wrap-toggle").trigger("click");
                    }, 3000);
                }
                console.log(result);
            },
            error: function(result) {
                alert('Update failed');
                console.log(result);
                sfsstoploading();
            },
            async: false
        });
        return false;
    });
})(jQuery);
(function($) {
    // Set previous state of email preference when user click on unsubscribe checkbox.
    $('#edit-unsubscribe-me').click(function() {
        if ($(this).is(":checked")) {
            $('#edit-email-preference--wrapper input:checkbox').removeAttr('checked');
        } else {
            $('#edit-email-preference--wrapper input[type=checkbox]').each(function() {
                var val = $(this).attr('prechecked');
                if (val) {
                    $(this).trigger('click');
                }
            });
        }
    });
    // Get base url of site.
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    // Get email preference from BSD system.
    $('#edit-email-preference--wrapper input[type=checkbox]').each(function() {
        var cons_id = $('#edit-field-cons-id-0-value').val();
        var postdata = {
            cons_id: cons_id,
            group_id: $(this).val()
        };
        $.ajax({
            type: "POST",
            url: baseUrl + 'am_bsd_tools/get_constituents_by_id',
            data: postdata,
            success: function(result) {
                if (result != '0') {
                    $("#edit-email-preference-" + result).attr('checked', true);
                    $("#edit-email-preference-" + result).attr('prechecked', true);
                }
            },
        });
    });
    $('#edit-pass-pass1').removeAttr('placeholder');
    $('#edit-pass-pass1').after('<div class="pass-hint"><label class="text">Leave blank unless you would like to set a new password</label></div>');
})(jQuery);
