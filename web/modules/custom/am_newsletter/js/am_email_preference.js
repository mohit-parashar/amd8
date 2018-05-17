// Show loader image on ajax start.
function startloading() {
    jQuery("body").addClass("am-form-overlay-active");
    jQuery('.form-submit').attr('disabled', true);
    jQuery('#loading').css('display','block');
}

// Hide loader image on ajax stop.
function stoploading() {
    jQuery("body").removeClass("am-form-overlay-active");
    jQuery('.form-submit').removeAttr('disabled');
    jQuery('#loading').css('display','none');
    jQuery('#success_message').html('<div class="alert alert-dismissable fade in welcome-alert"><a href="" class="close" data-dismiss="alert" aria-label="close">×</a><span class="message">Your changes have been saved.</span></div>');
}

// Show loader image on ajax start.
function startPageloading() {
    jQuery("body").addClass("am-form-overlay-active");
    jQuery('#loading').css('display','block');
    jQuery('#email_preference_wrap').css('display','none');
}

// Hide loader image on ajax stop.
function stopPageloading() {
    jQuery("body").removeClass("am-form-overlay-active");
    jQuery('#loading').css('display','none');
    jQuery('#email_preference_wrap').css('display','block');
}

(function($){
    // Get Base url.
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    // Update email preference.
    $('#edit-update-preference').click(function(){
        var addgroup = '';
        var addGroupCount = 0;
        var cons_id = $('#edit-field-cons-id-0-value').val();
        startloading();
        $('#edit-email-preference--wrapper input[type=checkbox]').each(function () {

            var postdata = { cons_id : cons_id , group_id : $(this).val() };
            if($(this).is(":checked")){
                addGroupCount++;
                addgroup += '<cons_group id="'+$(this).val()+'" />';
                $(this).attr('prechecked',true);
            } else {
            $.ajax({
            type: "POST",
            url: baseUrl+'am_bsd_tools/remove_cons_ids_from_group',
            data: postdata,
            success: function (result) {
                    $("#edit-email-preference-"+result).attr('prechecked',false);
                    console.log(result);
            },
            async: false
           });
        }
        });

    if(addGroupCount > 0) {
        var xmlData = '<?xml version="1.0" encoding="utf-8"?><api><cons id="'+cons_id+'">'+addgroup+'</cons></api>';
            $.ajax({
                type: "POST",
                url: baseUrl+'am_bsd_tools/set_constituent_data',
                data: xmlData,
                contentType: "text/xml",
                success: function (result) {
                    console.log(result);
                },
                async: false
           });
    }
    stoploading();
    return false;
    });

})(jQuery);

(function($){
    // Set previous state of email preference when user click on unsubscribe checkbox.
    $('#edit-unsubscribe-me').click(function(){
        if($(this).is(":checked")) {
         $('#edit-email-preference--wrapper input:checkbox').removeAttr('checked');
     } else {
        $('#edit-email-preference--wrapper input[type=checkbox]').each(function () {
            var val = $(this).attr('prechecked');
            if(val){
                $(this).trigger('click');
            }
        });
     }
    });
    // Get base url of site.
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    // Get email preference from BSD system.
    startPageloading();
    $('#edit-email-preference--wrapper input[type=checkbox]').each(function () {

        var cons_id = $('#edit-field-cons-id-0-value').val();
        var postdata = { cons_id : cons_id , group_id : $(this).val() };
            $.ajax({
                type: "POST",
                url: baseUrl+'am_bsd_tools/get_constituents_by_id',
                data: postdata,
                async: false,
                success: function (result) {
                    if (result != '0') {
                        $("#edit-email-preference-"+result).attr('checked',true);
                        $("#edit-email-preference-"+result).attr('prechecked',true);
                    }
                },
       });

    });
    stopPageloading();
})(jQuery);
