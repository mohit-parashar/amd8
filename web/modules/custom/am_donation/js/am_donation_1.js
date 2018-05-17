/**
 * @file
 * Doantion Form.
 */
(function($) {
    Drupal.behaviors.am_donation = {
        attach: function(context, settings) {
            if (context == document) {
                // Popover
                if ($(".tip.d-psn").length) {
                    $(".tip.d-psn").popover({
                        html: true,
                        triger: 'focus',
                        content: function() {
                            return $(this).next('div').html();
                        }
                    });
                }
                if ($(".tip.d-cvv").length) {
                    $(".tip.d-cvv").popover({
                        html: true,
                        triger: 'focus',
                        content: function() {
                            return $(this).next('div').html();
                        }
                    });
                }
                if ($(".tip.d-gsc").length) {
                    $(".tip.d-gsc").popover({
                        html: true,
                        triger: 'focus',
                        content: function() {
                            return $(this).next('div').html();
                        }
                    });
                }

                function validate_form() {
                    clear_require_error();
                    var mailing_country = get_mailing_country();
                    var billing_country = get_billing_country();
                    var succeed = false;
                    var required = new Array();
                    if (get_amount() == 0) {
                        required.push('#edit-other-amount');
                    } else {
                        if (get_amount() < 10) {
                            set_amount_error();
                        }
                    }
                    if (get_designate_gift()) {
                        if (get_choose_designation() == 'Other') {
                            if (get_other_designation() == '') {
                                required.push('#edit-other-designation');
                            }
                        }
                        if (get_choose_designation() == 'null') {
                            required.push('#edit-choose-designation');
                        }
                    }
                    // Personal Information
                    if (get_first_name() == '') {
                        required.push('#edit-name-first');
                    }
                    if (get_last_name() == '') {
                        required.push('#edit-name-last');
                    }
                    if (get_donor_email() == '') {
                        required.push('#edit-donor-email');
                    }
                    // Mailing Information
                    if (mailing_country == 'none') {
                        required.push('#edit-mailing-country');
                    }
                    if (get_mailing_address_1() == '') {
                        required.push('#edit-mailing-address-1');
                    }
                    // if (get_mailing_address_2() == '') {
                    //   required.push('#edit-mailing-address-2');
                    // }
                    if (get_mailing_city() == '') {
                        required.push('#edit-mailing-city');
                    }
                    // State
                    if (mailing_country == 'US') {
                        if (get_mailing_state() == 'none') {
                            required.push('#edit-mailing-state');
                        }
                    } else if (mailing_country == 'CA') {
                        if (get_mailing_province() == 'none') {
                            required.push('#edit-mailing-province');
                        }
                    } else if (mailing_country != 'none') {
                        if (get_mailing_state_province() == '') {
                            required.push('#edit-mailing-state-province');
                        }
                    } else {
                        if (get_mailing_state_province() == '') {
                            required.push('#edit-mailing-state-province');
                        }
                    }
                    if (get_mailing_postal_code() == '') {
                        required.push('#edit-mailing-postal-code');
                    }
                    // Credit card
                    if (get_credit_card_name() == '') {
                        required.push('#edit-credit-card-name');
                    }
                    if (get_credit_card_number() == '') {
                        required.push('#edit-credit-card-number');
                    }
                    if (get_credit_card_expiration_month() == '') {
                        required.push('#edit-credit-card-expiration-month');
                    }
                    if (get_credit_card_expiration_year() == '') {
                        required.push('#edit-credit-card-expiration-year');
                    }
                    if (get_credit_card_civ() == '') {
                        required.push('#edit-credit-card-civ');
                    }
                    // Billing Information
                    if (get_different_billing_address()) {
                        if (billing_country == 'none') {
                            required.push('#edit-billing-country');
                        }
                        if (get_billing_address_1() == '') {
                            required.push('#edit-billing-address-1');
                        }
                        // if (get_billing_address_2() == '') {
                        //   required.push('#edit-billing-address-2');
                        // }
                        if (get_billing_city() == '') {
                            required.push('#edit-billing-city');
                        }
                        // State
                        if (billing_country == 'US') {
                            if (get_billing_state() == 'none') {
                                required.push('#edit-billing-state');
                            }
                        } else if (billing_country == 'CA') {
                            if (get_billing_province() == 'none') {
                                required.push('#edit-billing-province');
                            }
                        } else if (billing_country != 'none') {
                            if (get_billing_state_province() == '') {
                                required.push('#edit-billing-state-province');
                            }
                        } else {
                            if (get_billing_state_province() == '') {
                                required.push('#edit-billing-state-province');
                            }
                        }
                        if (get_billing_postal_code() == '') {
                            required.push('#edit-billing-postal-code');
                        }
                    }
                    if (required.length === 0) {
                        // var is_valid_email = validate_email();
                        // if (is_valid_email) {
                        //   succeed = true;
                        // }
                        if (get_amount() >= 10) {
                            succeed = true;
                        } else {
                            set_min_amount_error();
                        }
                    } else {
                        // Set error
                        set_require_error(required);
                    }
                    return succeed;
                }

                function set_require_error(required) {
                    // alert(required.join('\n'));
                    focus_element = '';
                    jQuery.each(required, function(index, value) {
                        if (index == 0) {
                            focus_element = value;
                        };
                        // console.log(value);
                        jQuery(value).parent().addClass('error');
                        jQuery(value).parent().append("<span class='required-error'>Required</span>");
                    });
                    // Set focus to required element
                    jQuery(focus_element).focus();
                    enable_submit();
                }

                function set_amount_error() {
                    focus_element = '#edit-other-amount';
                    // console.log(focus_element);
                    jQuery(focus_element).parent().addClass('error');
                    jQuery(focus_element).parent().append("<span class='required-error'>Minimum amount: $10</span>");
                    // Set focus to required element
                    // $(focus_element).focus();
                }
                jQuery("#edit-other-amount").focusout(function() {
                    if (get_amount() < 10) {
                        clear_amount_error();
                        set_amount_error();
                    } else {
                        clear_amount_error();
                    }
                });

                function set_min_amount_error() {
                    focus_element = '#edit-other-amount';
                    alert('Minimum amount must be atleast $10');
                    // Set focus to other amount element
                    jQuery(focus_element).focus();
                    enable_submit();
                }

                function validate_email() {
                    var $email = jQuery('form input[name="donor_email'); //change form to id or containment selector
                    var re = /^(([^<>()[]\.,;:s@"]+(.[^<>()[]\.,;:s@"]+)*)|(".+"))@(([[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}])|(([a-zA-Z-0-9]+.)+[a-zA-Z]{2,}))$/igm;
                    if ($email.val() == '' || !re.test($email.val())) {
                        alert('Please enter a valid email address.');
                        enable_submit();
                        return false;
                    } else {
                        return true;
                    }
                }

                function clear_require_error() {
                    jQuery(document).find('span.required-error').remove();
                    jQuery(document).find('div.error').removeClass('error');
                }

                function clear_amount_error() {
                    jQuery(document).find('span.required-error').remove();
                    jQuery(document).find('div.error').removeClass('error');
                }
                jQuery(document).change(function() {
                    var m = jQuery("#edit-mailing-country").val();
                    if (m == 'US') {
                        mailingShowState();
                    } else if (m == 'CA') {
                        mailingShowProvision();
                    } else {
                        mailingShowStateProvision();
                    }
                    var b = jQuery("#edit-billing-country").val();
                    if (b == 'US') {
                        billingShowState();
                    } else if (b == 'CA') {
                        billingShowProvision();
                    } else {
                        billingShowStateProvision();
                    }
                    var d = jQuery('#edit-choose-designation').val();
                    if (d != 'Other') {
                        jQuery('#edit-other-designation').val('');
                    }
                });
                jQuery(document).ready(function() {
                    // Gift, designation
                    jQuery('#edit-designate-gift').on('click', function() {
                        if (get_designate_gift()) {
                            jQuery('#edit-choose-designation').prop('selectedIndex', 1);
                        } else {
                            jQuery('#edit-choose-designation').prop('selectedIndex', 0);
                            // jQuery('#edit-other-designation').hide();
                        }
                    });
                    // Billing Country
                    var different_billing_address = jQuery('#edit-different-billing-address');
                    jQuery(different_billing_address).on('click', function() {
                        resetBillingAddress();
                    });
                    // Default mailing/billing country
                    set_mailing_country('US', '');
                    set_billing_country('US', '');
                });
                mailingShowStateProvision();
                billingShowStateProvision();

                function mailingShowState() {
                    jQuery(".form-item-mailing-state").show();
                    jQuery(".form-item-mailing-province").hide();
                    jQuery(".form-item-mailing-state-province").hide();
                    // jQuery("#edit-mailing-state").prop('required', true);
                    // jQuery("#edit-mailing-state-province").prop('required', false);
                    // jQuery("#edit-mailing-province").prop('required', false);
                    jQuery('#edit-mailing-province').prop('selectedIndex', 0);
                    jQuery('#edit-mailing-state-province').val('');
                }

                function mailingShowProvision() {
                    jQuery(".form-item-mailing-state").hide();
                    jQuery(".form-item-mailing-province").show();
                    jQuery(".form-item-mailing-state-province").hide();
                    // jQuery("#edit-mailing-state").prop('required', false);
                    // jQuery("#edit-mailing-state-province").prop('required', false);
                    // jQuery("#edit-mailing-province").prop('required', true);
                    jQuery('#edit-mailing-state').prop('selectedIndex', 0);
                    jQuery('#edit-mailing-state-province').val('');
                }

                function mailingShowStateProvision() {
                    jQuery(".form-item-mailing-state").hide();
                    jQuery(".form-item-mailing-province").hide();
                    jQuery(".form-item-mailing-state-province").show();
                    // jQuery("#edit-mailing-state").prop('required', false);
                    // jQuery("#edit-mailing-state-province").prop('required', true);
                    // jQuery("#edit-mailing-province").prop('required', false);
                    jQuery('#edit-mailing-province').prop('selectedIndex', 0);
                    jQuery('#edit-mailing-state').prop('selectedIndex', 0);
                }

                function billingShowState() {
                    jQuery(".form-item-billing-state").show();
                    jQuery(".form-item-billing-province").hide();
                    jQuery(".form-item-billing-state-province").hide();
                    if (jQuery('#edit-different-billing-address').is(":checked")) {
                        jQuery("#edit-billing-state").prop('required', true);
                        jQuery("#edit-billing-state-province").prop('required', false);
                        jQuery("#edit-billing-province").prop('required', false);
                    } else if (jQuery('#edit-different-billing-address').is(":not(:checked)")) {
                        jQuery("#edit-billing-state").prop('required', false);
                    }
                    jQuery('#edit-billing-province').prop('selectedIndex', 0);
                    jQuery('#edit-billing-state-province').val('');
                }

                function billingShowProvision() {
                    jQuery(".form-item-billing-state").hide();
                    jQuery(".form-item-billing-province").show();
                    jQuery(".form-item-billing-state-province").hide();
                    if (jQuery('#edit-different-billing-address').is(":checked")) {
                        jQuery("#edit-billing-state").prop('required', false);
                        jQuery("#edit-billing-state-province").prop('required', false);
                        jQuery("#edit-billing-province").prop('required', true);
                    } else if (jQuery('#edit-different-billing-address').is(":not(:checked)")) {
                        jQuery("#edit-billing-province").prop('required', false);
                    }
                    jQuery('#edit-billing-state').prop('selectedIndex', 0);
                    jQuery('#edit-billing-state-province').val('');
                }

                function billingShowStateProvision() {
                    jQuery(".form-item-billing-state").hide();
                    jQuery(".form-item-billing-province").hide();
                    jQuery(".form-item-billing-state-province").show();
                    if (jQuery('#edit-different-billing-address').is(":checked")) {
                        jQuery("#edit-billing-state").prop('required', false);
                        jQuery("#edit-billing-state-province").prop('required', true);
                        jQuery("#edit-billing-province").prop('required', false);
                    } else if (jQuery('#edit-different-billing-address').is(":not(:checked)")) {
                        jQuery("#edit-billing-state-province").prop('required', false);
                    }
                    jQuery('#edit-billing-province').prop('selectedIndex', 0);
                    jQuery('#edit-billing-state').prop('selectedIndex', 0);
                }

                function resetMailingCountryState() {
                    mailingShowStateProvision();
                    jQuery('#edit-mailing-state-province').val('');
                    jQuery('#edit-mailing-country').prop('selectedIndex', 0);
                }

                function resetMailingAddress() {
                    resetMailingCountryState();
                    jQuery('#edit-mailing-address-1').val('');
                    jQuery('#edit-mailing-address-2').val('');
                    jQuery('#edit-mailing-city').val('');
                    jQuery('#edit-mailing-postal-code').val('');
                }

                function resetBillingCountryState() {
                    billingShowStateProvision();
                    jQuery('#edit-billing-state-province').val('');
                    jQuery('#edit-billing-country').prop('selectedIndex', 0);
                }

                function resetBillingAddress() {
                    resetBillingCountryState();
                    jQuery('#edit-billing-address-1').val('');
                    jQuery('#edit-billing-address-2').val('');
                    jQuery('#edit-billing-city').val('');
                    jQuery('#edit-billing-postal-code').val('');
                }
                // Amount Section
                jQuery(document).ready(function() {
                    jQuery('form').each(function() { this.reset() });
                    jQuery('input').attr('autocomplete', 'off');
                    if (jQuery('#edit-amount').length) {
                        var total = jQuery('#edit-amount input[name=amount]:checked').val();
                        //var total = 100;
                        jQuery('div.total-value span').html(total);
                        toggle_mailing_description(total);
                        // Add custom attributes
                        addOnetimeAttribute();
                        addMonthlyAttribute();
                        show_onetime_total_text();
                        hide_monthly_total_message();
                    };
                });
                var total_value = jQuery('div.total-value span');
                jQuery('input#edit-other-amount').on('input propertychange paste', function() {
                    // do your stuff
                    if (jQuery(this).val() != '') {
                        total_value.html(jQuery(this).val());
                        toggle_mailing_description(jQuery(this).val());
                    } else {
                        total_value.html('0');
                        toggle_mailing_description(0);
                    }
                });
                jQuery('input#edit-other-amount').focus(function() {
                    jQuery('#edit-amount label').removeClass('ui-state-active');
                    jQuery('input[name="amount"]').prop("checked", false);
                    if (jQuery(this).val() == '') {
                        total_value.html('0');
                    } else {
                        total_value.html(jQuery(this).val());
                    }
                    toggle_mailing_description(0);
                });
                jQuery('#edit-amount').click(function() {
                    clear_amount_error();
                    if (jQuery('#edit-amount label').hasClass('ui-state-active')) {
                        jQuery('input#edit-other-amount').val('');
                        var total = jQuery('#edit-amount label.ui-state-active').next('input').val();
                        total_value.html(total);
                        toggle_mailing_description(total);
                    }
                });
                jQuery('#edit-payment-type-monthly').click(function() {
                    toggle_gift_amount_monthly();
                    show_monthly_total_text();
                    show_monthly_total_message();
                    set_total(get_amount());
                    toggle_mailing_description(get_amount());
                });
                jQuery('#edit-payment-type-one-time').click(function() {
                    toggle_gift_amount_onetime();
                    show_onetime_total_text();
                    hide_monthly_total_message();
                    set_total(get_amount());
                    toggle_mailing_description(get_amount());
                });

                function show_onetime_total_text() {
                    jQuery('div.total-value.monthly').hide();
                    jQuery('div.total-value.onetime').show();
                }

                function show_monthly_total_text() {
                    jQuery('div.total-value.onetime').hide();
                    jQuery('div.total-value.monthly').show();
                }

                function show_monthly_total_message() {
                    jQuery('div.monthly-total-message').show();
                }

                function hide_monthly_total_message() {
                    jQuery('div.monthly-total-message').hide();
                }
                // add 'monthly-amount' attribute to amount inputs with amounts
                function addMonthlyAttribute() {
                    var mon_amounts = [];
                    $('#edit-monthly-donation-amount input').each(function(){
                        mon_amounts.push($(this).val());
                    });
                    var amount_wrapper = jQuery('#edit-amount--wrapper');
                    amount_wrapper.find('input').each(function(index) {
                        jQuery(this).attr('monthly-amount', mon_amounts[index]);
                    });
                }

                function addOnetimeAttribute() {
                    var amount_wrapper = jQuery('#edit-amount--wrapper');
                    amount_wrapper.find('input').each(function(index) {
                        jQuery(this).attr('onetime-amount', jQuery(this).val());
                    });
                }

                function toggle_gift_amount_monthly() {
                    // Swap values
                    var amount_wrapper = jQuery('#edit-amount--wrapper');
                    amount_wrapper.find('input').each(function(index) {
                        // var temp = jQuery(this).val();
                        var monthly_amount = jQuery(this).attr('monthly-amount');
                        jQuery(this).attr('value', monthly_amount);
                        // jQuery(this).attr('monthly-amount',temp);
                        // Update labels
                        jQuery(this).prev().find('span.ui-button-text').text('$' + monthly_amount);
                    });
                }

                function toggle_gift_amount_onetime() {
                    // Swap values
                    var amount_wrapper = jQuery('#edit-amount--wrapper');
                    amount_wrapper.find('input').each(function(index) {
                        // var temp = jQuery(this).val();
                        var onetime_amount = jQuery(this).attr('onetime-amount');
                        jQuery(this).attr('value', onetime_amount);
                        // jQuery(this).attr('monthly-amount',temp);
                        // Update labels
                        jQuery(this).prev().find('span.ui-button-text').text('$' + onetime_amount);
                    });
                }
                // Toggle Mailing Information Description edit-mailing-information--description
                function toggle_mailing_description(total) {
                    var description = jQuery('#edit-mailing-information--description');
                    var payment_type = get_payment_type();
                    if (payment_type == 'one-time') {
                        if (total >= 200) {
                            description.show();
                        } else {
                            description.hide();
                        }
                    } else {
                        if ((total * 12) >= 200) {
                            description.show();
                        } else {
                            description.hide();
                        }
                    }
                }
                setTimeout(function(){
                    // Autofill Personal information for loggedin user.
                    if (drupalSettings.am_donation.logged_in) {

                        console.log(drupalSettings.am_donation.psn);
                        jQuery('#edit-print-subscription-number').val(drupalSettings.am_donation.psn);
                        var is_valid = verify_customer_number();
                        if (is_valid) {
                            verify_customer_data();
                        };
                        jQuery('#edit-name-first').val(drupalSettings.am_donation.first_name);
                        jQuery('#edit-name-last').val(drupalSettings.am_donation.last_name);
                        jQuery('#edit-donor-email').val(drupalSettings.am_donation.mail);
                    };
                }, 500);
                // Form submit call
                jQuery('#process-donation').click(function() {
                    disable_submit();
                    if (validate_form()) {
                        saveDonorInfo();
                    }
                });
                // Disable form submit button
                function disable_submit() {
                    jQuery('#process-donation').prop('disabled', 'disabled');
                    jQuery('#process-donation').val('Processing...');
                }

                function enable_submit() {
                    jQuery('#process-donation').removeAttr('disabled');
                    jQuery('#process-donation').val('Donate now');
                }
                // Save Donor Info
                function saveDonorInfo() {
                    //emailRegister();
                    jQuery.ajax({
                        type: 'POST',
                        url: '/donation/donorinfo/ajax',
                        data: {
                            amount: get_amount(),
                            billing_address_1: get_billing_address_1(),
                            billing_address_2: get_billing_address_2(),
                            billing_city: get_billing_city(),
                            billing_country: get_billing_country(),
                            billing_postal_code: get_billing_postal_code(),
                            billing_province: get_billing_province(),
                            billing_state: get_billing_state(),
                            billing_state_province: get_billing_state_province(),
                            choose_designation: get_choose_designation(),
                            other_designation: get_other_designation(),
                            comments: get_comments(),
                            designate_gift: get_designate_gift(),
                            different_billing_address: get_different_billing_address(),
                            donor_email: get_donor_email(),
                            donor_phone: get_donor_phone(),
                            print_subscription_number: get_psn(),
                            gift_anonymous: get_gift_anonymous(),
                            mailing_address_1: get_mailing_address_1(),
                            mailing_address_2: get_mailing_address_2(),
                            mailing_city: get_mailing_city(),
                            mailing_country: get_mailing_country(),
                            mailing_postal_code: get_mailing_postal_code(),
                            mailing_province: get_mailing_province(),
                            mailing_state: get_mailing_state(),
                            mailing_state_province: get_mailing_state_province(),
                            first_name: get_first_name(),
                            last_name: get_last_name(),
                            payment_type: get_payment_type(),
                            phone: get_donor_phone(),
                            transaction_fee: get_transaction_fee(),
                            bsd_error: '',
                            bsd_response: '',
                            is_free_subscription_entitled: '',
                            is_renew_subscription: '',
                            is_create_subscription: '',
                            is_processed_via_cron: '',
                            is_donation_success: '',
                            sfs_create_response: '',
                            is_sfs_create_success: '',
                            sfs_order_id: '',
                            sfs_renew_response: '',
                            is_sfs_renew_success: '',
                            sfs_address_update_response: '',
                            is_sfs_address_updated: '',
                            is_sfs_address_update_success: '',
                            is_recurring_acknowledge: is_recurring_acknowledge(),
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("success");
                            // console.log(data);
                            // console.log("Donation processed");
                            // sid = 1;
                            // arr = data.split(":");
                            // sid = arr[1];
                            processDonation(data);
                        },
                        error: function(data) {
                            // console.log("error");
                            // console.log(data);
                        }
                    });
                }
                // BSD Email Register
                function emailRegister() {
                    jQuery.ajax({
                        type: 'POST',
                        url: '/am_bsd_tools/email_register_ajax',
                        data: {
                            email: get_donor_email(),
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("emailRegister success");
                            // console.log(data);
                        },
                        error: function(data) {
                            var data = data;
                            // console.log("emailRegister error");
                            // console.log(data);
                        }
                    });
                }
                // Register donor
                function registerDonor() {
                    jQuery.ajax({
                        type: 'POST',
                        url: '/donation/register/donor/ajax',
                        data: {
                            donor_email: get_donor_email(),
                            first_name: get_first_name(),
                            last_name: get_last_name(),
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("Donor Register success");
                            // console.log(data);
                        },
                        error: function(data) {
                            var data = data;
                            // console.log("Donor Register error");
                            // console.log(data);
                        }
                    });
                }
                // Process Donation
                function processDonation(sid) {
                    var sid = sid;
                    var am_data_re = {
                        slug: 'amdonation',
                        firstname: get_first_name(),
                        lastname: get_last_name(),
                        addr1: get_mailing_address_1(),
                        city: get_mailing_city(),
                        // state_cd: get_mailing_state_province(),
                        state_cd: get_mailing_state_by_country(get_mailing_country()),
                        zip: get_mailing_postal_code(),
                        country: get_mailing_country(),
                        amount: get_amount(),
                        amount_other: get_amount(),
                        email: get_donor_email(),
                        phone: get_donor_phone(),
                        recurring_acknowledge: is_recurring_acknowledge(),
                        cc_number: get_credit_card_number(),
                        cc_expir_month: get_credit_card_expiration_month(),
                        cc_expir_year: get_credit_card_expiration_year(),
                        cc_cvv: get_credit_card_civ(),
                        billingcountry: get_billing_country(),
                        billingaddress1: get_billing_address_1(),
                        billingaddress2: get_billing_address_2(),
                        billingcity: get_billing_city(),
                        billingstateprovince: get_billing_state_by_country(get_billing_country()),
                        billingpostalcode: get_billing_postal_code(),
                        designation: get_designation(),
                        comments: get_comments(),
                        designategift: get_designate_gift(),
                        giftanonymous: get_gift_anonymous(),
                        transactionfee: get_transaction_fee(),
                        confirmed: '1',
                    }
                    var am_data = {
                        slug: 'amdonation',
                        firstname: get_first_name(),
                        lastname: get_last_name(),
                        addr1: get_mailing_address_1(),
                        city: get_mailing_city(),
                        // state_cd: get_mailing_state_province(),
                        state_cd: get_mailing_state_by_country(get_mailing_country()),
                        zip: get_mailing_postal_code(),
                        country: get_mailing_country(),
                        amount: get_amount(),
                        amount_other: get_amount(),
                        email: get_donor_email(),
                        phone: get_donor_phone(),
                        cc_number: get_credit_card_number(),
                        cc_expir_month: get_credit_card_expiration_month(),
                        cc_expir_year: get_credit_card_expiration_year(),
                        cc_cvv: get_credit_card_civ(),
                        billingcountry: get_billing_country(),
                        billingaddress1: get_billing_address_1(),
                        billingaddress2: get_billing_address_2(),
                        billingcity: get_billing_city(),
                        billingstateprovince: get_billing_state_by_country(get_billing_country()),
                        billingpostalcode: get_billing_postal_code(),
                        designation: get_designation(),
                        comments: get_comments(),
                        designategift: get_designate_gift(),
                        giftanonymous: get_gift_anonymous(),
                        transactionfee: get_transaction_fee(),
                        confirmed: '1',
                    }
                    if (is_recurring_acknowledge()) {
                        am_data_post = am_data_re;
                    } else {
                        am_data_post = am_data;
                    }
                    jQuery.ajax({
                        type: 'POST',
                        async: false,
                        //url: 'https://diasparktest.cp.bsd.net/page/cde/Api/Charge/v1',
                          url: 'https://americamedia.cp.bsd.net/page/cde/Api/Charge/v1',
                        //cache: true,
                        // url: '/bsdresponse.php',
                        data: am_data_post,
                        success: function(data) {
                            // console.log("Donation success");
                            // console.log(data);
                            var response = data;
                            var response = JSON.stringify(response);
                            // console.log(response);
                            update_bsd_response(response, sid, get_payment_type(), get_psn());
                        },
                        error: function(data) {
                            var data = data;
                            // console.log("Donation error");
                            // console.log(data);
                            var err_response = data;
                            var err_response = JSON.stringify(err_response);
                            update_bsd_error(sid,err_response);
                            enable_submit();
                            // For validation errors
                            if (data.responseJSON.code == "validation") {
                                var obj = jQuery.parseJSON(data.responseText);
                                var error = '';
                                jQuery.each(obj.field_errors, function(index, value) {
                                    error += value.field + ' : ' + value.message + '\n';
                                });
                                alert(obj.message + "\n\n" + error);
                            } else if(data.responseJSON.code == "gateway") {
                                var obj = jQuery.parseJSON(data.responseText);
                                var error = '';
                                // console.log(obj.gateway_response.message);
                                alert(obj.gateway_response.message);
                            }else{
                                // Other errors
                                alert('Error processing your contribution. Please try again after sometime.');
                            }
                        }
                    });
                }

                function update_bsd_error(sid,err_response) {
                    var sid = sid;
                    jQuery.ajax({
                        type: 'POST',
                        url: '/donation/donorinfo/ubsde/ajax',
                        data: {
                            bsd_error: err_response,
                            sid: sid,
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("success");
                        },
                        error: function(data) {
                            // console.log("error");
                        }
                    });
                }

                function update_bsd_response(response, sid, payment_type, print_subscription_number) {
                    var sid = sid;
                    var response = jQuery.trim(response);
                    response = jQuery.base64.encode(response);
                    jQuery("<form style='display:none' method='POST' action='/donation/donorinfo/ubsdr/ajax'><input type='text' name='bsd_response'  value='" + response + "'><input type='text' name='sid' value=" + sid + "><input type='text' name='payment_type' value=" + payment_type + "><input type='text' name='print_subscription_number' value=" + print_subscription_number + "></form>").appendTo('body').submit();
                    // jQuery.ajax({
                    //   type: 'POST',
                    //   url: '/donation/donorinfo/ubsdr/ajax',
                    //   data: {
                    //     bsd_response: response,
                    //     sid: sid,
                    //     payment_type: payment_type,
                    //     print_subscription_number: print_subscription_number,
                    //   }
                    // });
                }

                function set_is_free_subscription(sid) {
                    var sid = sid;
                    jQuery.ajax({
                        type: 'POST',
                        url: '/donation/donorinfo/sifs/ajax',
                        data: {
                            is_free_subscription: '1',
                            sid: sid,
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("success");
                        },
                        error: function(data) {
                            // console.log("error");
                        }
                    });
                }

                function set_is_create_subscription(sid) {
                    var sid = sid;
                    jQuery.ajax({
                        type: 'POST',
                        url: '/donation/donorinfo/sics/ajax',
                        data: {
                            is_create_subscription: '1',
                            sid: sid,
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("success");
                        },
                        error: function(data) {
                            // console.log("error");
                        }
                    });
                }

                function set_is_renew_subscription(sid) {
                    var sid = sid;
                    jQuery.ajax({
                        type: 'POST',
                        url: '/donation/donorinfo/sirs/ajax',
                        data: {
                            is_renew_subscription: '1',
                            sid: sid,
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("success");
                        },
                        error: function(data) {
                            // console.log("error");
                        }
                    });
                }

                function provide_extend_subscription(amount, sid) {
                    var sid = sid;
                    var is_valid_PSN = verify_customer_number();
                    if (get_payment_type() == 'one-time') {
                        if (amount >= 200) {
                            if (is_valid_PSN) {
                                // alert('Subscription will be extended for provided PSN.');
                                set_is_renew_subscription(sid);
                                // renew_subscription();
                            } else {
                                // alert('A new Subscription will be provided.');
                                set_is_create_subscription(sid);
                                // set_subscription_data();
                            }
                            set_is_free_subscription(sid);
                            return true;
                        } else {
                            // alert('Donation amount is less than 200');
                            return false;
                        }
                    } else {
                        // Monthly Payment
                        if ((amount * 12) >= 200) {
                            if (is_valid_PSN) {
                                // alert('Subscription will be extended for provided PSN.');
                                set_is_renew_subscription(sid);
                                // renew_subscription();
                            } else {
                                // alert('A new Subscription will be provided.');
                                set_is_create_subscription(sid);
                                // set_subscription_data();
                            }
                            set_is_free_subscription(sid);
                            return true;
                        } else {
                            // alert('Donation amount is less than 200');
                            return false;
                        }
                    }
                }
                // Print subscription number verify event
                jQuery("#edit-print-subscription-number").focusout(function() {

                    jQuery(this).parent().find('p').remove();
                    // Tab country fix
                    if (jQuery(this).val() == "") {
                        return;
                    }
                    var is_valid = verify_customer_number();
                    // alert(is_valid);
                    if (is_valid) {
                        verify_customer_data();
                    };
                });

                function verify_customer_number() {
                    var succeed = false;
                    var psn = get_psn();
                    if (psn != '') {
                        // Verify PSN
                        jQuery.ajax({
                            type: 'POST',
                            async: false,
                            url: '/am_sfs_integration/verify_customer_number',
                            data: {
                                psn: psn,
                            },
                            //contentType: 'application/json; charset=utf-8',
                            //dataType:'json',
                            success: function(data) {
                                // console.log(data);
                                if (data == '1') {
                                    // alert('Subscription number is valid');
                                    successPSN();
                                    succeed = true;
                                    // console.log("psn success true");
                                    // set_subscription_data();
                                } else {
                                    // alert('Invalid subscription number');
                                    errorPSN();
                                }
                            },
                            error: function(data) {
                                // console.log("psn error");
                            }
                        });
                    };
                    if (succeed == false) {
                        resetMailingAddress();
                    };
                    return succeed;
                }

                function successPSN() {
                    var psn_input = jQuery('#edit-print-subscription-number');
                    psn_input.parent().find('p').remove();
                    psn_input.parent().append("<p style='color:green;'>Valid PSN.</p>");
                    return true;
                }

                function errorPSN() {
                    var psn_input = jQuery('#edit-print-subscription-number');
                    psn_input.val('');
                    psn_input.parent().find('p').remove();
                    psn_input.parent().append("<p style='color:red;'>Please provide valid PSN. Leave blank if not available.</p>")
                    psn_input.focus();
                    return false;
                }
                // function renew_subscription() {
                //   var psn = get_psn();
                //   jQuery.ajax({
                //     type: 'POST',
                //     url: '/am_sfs_integration/renew_subscription',
                //     data: {
                //       psn: psn,
                //     },
                //     //contentType: 'application/json; charset=utf-8',
                //     //dataType:'json',
                //     success: function(data) {
                //       console.log("renew subscription success");
                //       var data = jQuery.parseJSON(data);
                //       console.log(data['valid']);
                //       console.log(data['message']);
                //     },
                //     error: function(data) {
                //       console.log("renew subscription error");
                //     }
                //   });
                // }
                function set_subscription_data() {
                    jQuery.ajax({
                        type: 'POST',
                        url: '/am_sfs_integration/set_subscription_data',
                        data: {
                            FirstName: get_first_name(),
                            LastName: get_last_name(),
                            Company: '',
                            Address1: get_mailing_address_1(),
                            Address2: get_mailing_address_2(),
                            City: get_mailing_city(),
                            StateProv: get_mailing_state_by_country(get_mailing_country()),
                            ZipPostal: get_mailing_postal_code(),
                            CountryCode: get_mailing_country(),
                            Email: get_donor_email(),
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("Create subscription success");
                            var data = jQuery.parseJSON(data);
                            // console.log(data['valid']);
                            // console.log(data['message']);
                            // console.log(data['orderID']);
                        },
                        error: function(data) {
                            // console.log("Create subscription error");
                        }
                    });
                }

                function verify_customer_data() {
                    var psn = get_psn();
                    jQuery.ajax({
                        type: 'POST',
                        url: '/am_sfs_integration/verify_customer_data',
                        data: {
                            psn: psn,
                        },
                        //contentType: 'application/json; charset=utf-8',
                        //dataType:'json',
                        success: function(data) {
                            // console.log("verify customer data success");
                            var data = jQuery.parseJSON(data);
                            // console.log(data);
                            autofill_mailing_address(data);
                        },
                        error: function(data) {
                            // console.log("verify customer data success");
                        }
                    });
                }

                function autofill_mailing_address(mailing_info) {
                    set_mailing_address_1(mailing_info['Address1']);
                    set_mailing_address_2(mailing_info['Address2']);
                    set_mailing_city(mailing_info['City']);
                    set_mailing_postal_code(mailing_info['Zip']);
                    set_mailing_country(mailing_info['Country'], mailing_info['State']);
                }

                function is_recurring_acknowledge() {
                    var payment_type = get_payment_type();
                    if (payment_type == 'one-time') {
                        return 0;
                    } else {
                        return 1;
                    }
                }
                // Getters
                function get_payment_type() {
                    var payment_type = jQuery("input[name='payment_type']:checked").val();
                    return payment_type;
                }

                function get_amount() {
                    var amount = 0;
                    // Options Amount
                    if (jQuery("input[name='amount']:checked").val()) {
                        amount = jQuery("input[name='amount']:checked").val();
                    }
                    // Other Amount
                    if (jQuery('input#edit-other-amount').val() != '') {
                        amount = jQuery('input#edit-other-amount').val();
                    }
                    return amount;
                }

                function get_gift_anonymous() {
                    var gift_anonymous = jQuery("input[name='gift_anonymous']").is(":checked");
                    return gift_anonymous;
                }

                function get_designate_gift() {
                    var designate_gift = jQuery("input[name='designate_gift']").is(":checked");
                    return designate_gift;
                }

                function get_choose_designation() {
                    var designation = jQuery("#edit-choose-designation option:selected").val();
                    return designation;
                }

                function get_other_designation() {
                    var other_designation = jQuery('#edit-other-designation').val();
                    return other_designation;
                }

                function get_designation() {
                    var designation = get_choose_designation();
                    if (designation == 'Other') {
                        designation = get_other_designation();
                    }
                    return designation;
                }

                function get_comments() {
                    var comments = jQuery('#edit-comments').val();
                    return comments;
                }
                // Get Personal Information
                function get_first_name() {
                    var first_name = jQuery('#edit-name-first').val();
                    return jQuery.trim(first_name);
                }

                function get_last_name() {
                    var last_name = jQuery('#edit-name-last').val();
                    return jQuery.trim(last_name);
                }

                function get_donor_email() {
                    var donor_email = jQuery('#edit-donor-email').val();
                    return jQuery.trim(donor_email);
                }

                function get_donor_phone() {
                    var donor_phone = jQuery('#edit-donor-phone').val();
                    return jQuery.trim(donor_phone);
                }
                // Print subscription number
                function get_psn() {
                    var donor_psn = jQuery('#edit-print-subscription-number').val();
                    return jQuery.trim(donor_psn);
                }
                // Get Mailing Address
                function get_mailing_country() {
                    var mailing_country = jQuery("#edit-mailing-country option:selected").val();
                    return mailing_country;
                }

                function get_mailing_address_1() {
                    var mailing_address_1 = jQuery('#edit-mailing-address-1').val();
                    return jQuery.trim(mailing_address_1);
                }

                function get_mailing_address_2() {
                    var mailing_address_2 = jQuery('#edit-mailing-address-2').val();
                    return jQuery.trim(mailing_address_2);
                }

                function get_mailing_city() {
                    var mailing_city = jQuery('#edit-mailing-city').val();
                    return jQuery.trim(mailing_city);
                }

                function get_mailing_state_province() {
                    var mailing_state_province = jQuery('#edit-mailing-state-province').val();
                    return jQuery.trim(mailing_state_province);
                }

                function get_mailing_province() {
                    var mailing_province = jQuery("#edit-mailing-province option:selected").val();
                    return mailing_province;
                }

                function get_mailing_state() {
                    var mailing_state = jQuery("#edit-mailing-state option:selected").val();
                    return mailing_state;
                }

                function get_mailing_state_by_country(country) {
                    var state;
                    if (country == 'US') {
                        state = get_mailing_state(state);
                    } else if (country == 'CA') {
                        state = get_mailing_province(state);
                    } else {
                        state = get_mailing_state_province(state);
                    }
                    return state;
                }

                function get_mailing_postal_code() {
                    var mailing_postal_code = jQuery('#edit-mailing-postal-code').val();
                    return jQuery.trim(mailing_postal_code);
                }
                // Different billing address
                function get_different_billing_address() {
                    var different_billing_address = jQuery('#edit-different-billing-address').is(":checked");
                    return different_billing_address;
                }
                // Get Billing Address
                function get_billing_country() {
                    var billing_country = jQuery("#edit-billing-country option:selected").val();
                    if (!get_different_billing_address()) {
                        billing_country = '';
                    };
                    return billing_country;
                }

                function get_billing_address_1() {
                    var billing_address_1 = jQuery('#edit-billing-address-1').val();
                    if (!get_different_billing_address()) {
                        billing_address_1 = '';
                    };
                    return jQuery.trim(billing_address_1);
                }

                function get_billing_address_2() {
                    var billing_address_2 = jQuery('#edit-billing-address-2').val();
                    if (!get_different_billing_address()) {
                        billing_address_2 = '';
                    };
                    return jQuery.trim(billing_address_2);
                }

                function get_billing_city() {
                    var billing_city = jQuery('#edit-billing-city').val();
                    if (!get_different_billing_address()) {
                        billing_city = '';
                    };
                    return jQuery.trim(billing_city);
                }

                function get_billing_state_province() {
                    var billing_state_province = jQuery('#edit-billing-state-province').val();
                    if (!get_different_billing_address()) {
                        billing_state_province = '';
                    };
                    return jQuery.trim(billing_state_province);
                }

                function get_billing_province() {
                    var billing_province = jQuery("#edit-billing-province option:selected").val();
                    if (!get_different_billing_address()) {
                        billing_province = '';
                    };
                    return billing_province;
                }

                function get_billing_state() {
                    var billing_state = jQuery("#edit-billing-state option:selected").val();
                    if (!get_different_billing_address()) {
                        billing_state = '';
                    };
                    return billing_state;
                }

                function get_billing_state_by_country(country) {
                    var state;
                    if (country == 'US') {
                        state = get_billing_state(state);
                    } else if (country == 'CA') {
                        state = get_billing_province(state);
                    } else {
                        state = get_billing_state_province(state);
                    }
                    return state;
                }

                function get_billing_postal_code() {
                    var billing_postal_code = jQuery('#edit-billing-postal-code').val();
                    if (!get_different_billing_address()) {
                        billing_postal_code = '';
                    };
                    return jQuery.trim(billing_postal_code);
                }

                function set_billing_country(country, state) {
                    if (country != '') {
                        jQuery("#edit-billing-country").val(country);
                        if (country == 'US') {
                            set_billing_state(state);
                        } else if (country == 'CA') {
                            set_billing_province(state);
                        } else {
                            set_billing_state_province(state);
                        }
                    } else {
                        resetBillingCountryState();
                    }
                }
                // Get Payment Information
                function get_credit_card_name() {
                    var credit_card_name = jQuery('#edit-credit-card-name').val();
                    return jQuery.trim(credit_card_name);
                }

                function get_credit_card_number() {
                    var credit_card_number = jQuery('#edit-credit-card-number').val();
                    return jQuery.trim(credit_card_number);
                }

                function get_credit_card_expiration_month() {
                    var credit_card_expiration_month = jQuery("#edit-credit-card-expiration-month option:selected").val();
                    return credit_card_expiration_month;
                }

                function get_credit_card_expiration_year() {
                    var credit_card_expiration_year = jQuery("#edit-credit-card-expiration-year option:selected").val();
                    return credit_card_expiration_year;
                }

                function get_credit_card_civ() {
                    var credit_card_civ = jQuery('#edit-credit-card-civ').val();
                    return credit_card_civ;
                }

                function get_transaction_fee() {
                    var transaction_fee = jQuery('#edit-transaction-fee').is(":checked");
                    return transaction_fee;
                }
                // setters
                function set_mailing_address_1(address1) {
                    jQuery('#edit-mailing-address-1').val(address1);
                }

                function set_mailing_address_2(address2) {
                    jQuery('#edit-mailing-address-2').val(address2);
                }

                function set_mailing_city(city) {
                    jQuery('#edit-mailing-city').val(city);
                }

                function set_mailing_postal_code(zip) {
                    jQuery('#edit-mailing-postal-code').val(zip);
                }

                function set_mailing_country(country, state) {
                    if (country != '') {
                        jQuery("#edit-mailing-country").val(country);
                        if (country == 'US') {
                            set_mailing_state(state);
                        } else if (country == 'CA') {
                            set_mailing_province(state);
                        } else {
                            set_mailing_state_province(state);
                        }
                    } else {
                        resetMailingCountryState();
                    }
                }

                function set_mailing_state_province(state) {
                    if (state != '') {
                        jQuery('#edit-mailing-state-province').val(state);
                    };
                    mailingShowStateProvision();
                }

                function set_mailing_province(state) {
                    if (state != '') {
                        jQuery("#edit-mailing-province").val(state);
                    };
                    mailingShowProvision();
                }

                function set_mailing_state(state) {
                    if (state != '') {
                        jQuery("#edit-mailing-state").val(state);
                    };
                    mailingShowState();
                }

                function set_total(total) {
                    jQuery('div.total-value span').text(total);
                }

                function set_billing_state_province(state) {
                    if (state != '') {
                        jQuery('#edit-billing-state-province').val(state);
                    };
                    billingShowStateProvision();
                }

                function set_billing_province(state) {
                    if (state != '') {
                        jQuery("#edit-billing-province").val(state);
                    };
                    billingShowProvision();
                }

                function set_billing_state(state) {
                    if (state != '') {
                        jQuery("#edit-billing-state").val(state);
                    };
                    billingShowState();
                }
            }
        }
    }

    // Allow certain special character on donation form.
    jQuery('#webform-submission-donation-form input[type="text"]').keyup(function() {
        var yourInput = jQuery(this).val();
        re = /[`~!@$%^*|+\=?;:<>\{\}\[\]]/gi;
        var isSplChar = re.test(yourInput);
        if(isSplChar) {
            var no_spl_char = yourInput.replace(/[`~!@$%^*|+\=?;:'",.<>\{\}\[\]]/gi, '');
            jQuery(this).val(no_spl_char);
        }
    });

})(jQuery);
 