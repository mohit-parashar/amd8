// JavaScript Document


(function($) {
    // Change login or register to post coment text.
    if ( ! ($('#comment-form').length) ) {
        $('li.comment-forbidden').remove();
        $('.showCommentSection article ul.links.inline.list-inline').html('<li class="comment-reply"><a href="" data-toggle="modal" data-target="#loginModal">Reply</a></li>');
    }
    // Subscription thank you page thanks message.
    if (!!jQuery.cookie('am-sub-emailcookie')) {
        $('.region.region-full-content-1').prepend('<div class="alert alert-dismissable fade in welcome-alert"><div class="container"><a class="close" data-dismiss="alert" aria-label="close">×</a><span class="message">Your transaction was successfull. A confirmation email has been sent to ' + jQuery.cookie('am-sub-emailcookie') + ' with the details.</span></div></div>');
        $.removeCookie("am-sub-emailcookie");
    }
    var clk = getUrlParameter('cl');
    if (clk == 1) {
        jQuery('#loginModal').modal({
            backdrop: 'static',
            keyboard: true
        });
        var email = '';
        var email = drupalSettings.paywall.oneallemail;
        var why_content = $('#block-loginwhycontent').find('div.whyContent');
        why_content.find('div.already-acount').remove();
        why_content.append("<div class='already-acount' id='AMwhyAnswerL' style='color:red'>Your email address " + email + " is already registered with an account. Please login to that account by sending a login link, using your password, or selecting the social network you created your account.</div>");
    }
    /*my-account page email pref toggle starts*/
    $(".email-pref .text a").click(function() {
        $(".email-pref-wrap").show("slow");
        $(this).parent().hide('slow');
    });
    $(".email-pref-wrap span.close").click(function() {
        $(".email-pref .text").show("slow");
        $(this).parent().hide('slow');
    });
    /*my-account page email pref toggle ends*/
    /*my account membership page starts*/
    $(".contact-detail-wrap .text.purple.right").click(function() {
        $(".contact-wrap").show("slow");
        $(this).parent().hide('slow');
    });
    $(".contact-wrap-toggle").click(function() {
        $(".contact-detail-wrap").show("slow");
        $(this).parent().hide('slow');
    });
    /*my account membership page  toggle ends*/
    $('#user-form').submit(function() {
        $('input[type="submit"]').val('Processing...');
        $('input[type="submit"]').prop('disabled', 'disabled');
    });
    $('.filterBtn').click(function() {
        if ($(this).hasClass('collapsed')) {
            $(this).text('Hide filters');
        } else {
            $(this).text('Show filters');
        }
    });
    var url = window.location.href; // Returns full URL
    var url1 = url.split('?');
    var url2 = url1[0].split('/');
    //for magazine page
    if (url1[1] == undefined && $("#edit-field-iss-date-value").length != 0) {
        $("#views-exposed-form-magazine-page-1 #edit-field-iss-date-value").val((new Date).getFullYear());
    }
    var actions = '';
    if (url2[3] != 'taxonomy') {
        if (url2[5] != undefined) {
            var actions = '/' + url2[3] + '/' + url2[4] + '/' + url2[5];
        } else {
            if (url2[4] != undefined) {
                var actions = '/' + url2[3] + '/' + url2[4];
            } else {
                var actions = '/' + url2[3];
            }
        }
        $('#views-exposed-form-category-list-page-page-1').attr('action', actions);
    } else {
        if (url2[5] != undefined) {
            var actions = '/' + url2[3] + '/' + url2[4] + '/' + url2[5];
        } else {
            var actions = '/' + url2[3] + '/' + url2[4];
        }
        $('#views-exposed-form-category-list-page-page-1').attr('action', actions);
    }
    $('.form-item-field-publication-date-value-min label').text("Start Date");
    $('.form-item-field-publication-date-value-max label').text("End Date");
    $("#edit-field-iss-date-value").removeClass("form-control");
    if ($('#edit-field-publication-date-value-min').length != 0) {
        $("#edit-field-publication-date-value-min").datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $("#edit-field-publication-date-value-max").datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function(dateText, inst) {
                //$(".views-exposed-form").submit();
                $(this).closest('form').submit();
            }
        });
    }
    // Search page date filter
    if ($('#edit-field-start-date').length != 0) {
        // Set defauld value of hidden date field
        if ($('#edit-field-start-date').val() == '') {
            //var date11 = new Date($(".views-exposed-form .start_date").val());
            var start_date = $(".views-exposed-form .start_date").val();
            if (start_date == 'Start Date') {
                var date11 = '1900-01-01';
            } else {
                var date11 = new Date(start_date);
            }
            /*$("#edit-field-start-date").datepicker({
                dateFormat: 'yy-mm-dd'
            }).datepicker('setDate', date11);
            $("#edit-field-start-date").val($("#edit-field-start-date").val() + " 00:00:00");*/
        }
        if ($('#edit-field-end-date').val() == '') {
            var date22 = new Date($(".views-exposed-form .end_date").val());
            /*$("#edit-field-end-date").datepicker({
                dateFormat: 'yy-mm-dd'
            }).datepicker('setDate', date22);
            $("#edit-field-end-date").val($("#edit-field-end-date").val() + " 23:59:59");*/
        }
        $(".views-exposed-form .start_date").datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: "1900:+0",
            dateFormat: 'M dd, yy',
            onSelect: function(dateText, inst) {
                var date1 = new Date(dateText);
                var newDate = date1.toString('M dd, yy');

                $(".views-exposed-form .end_date").datepicker("option", "minDate", date1);
                if ($(".views-exposed-form .end_date").val() == '') {
                    $(".views-exposed-form .end_date").val($(this).val());
                }

                $("#edit-field-start-date").datepicker({
                    dateFormat: 'yy-mm-dd'
                }).datepicker('setDate', date1);
                $("#edit-field-start-date").val($("#edit-field-start-date").val() + " 00:00:00");
            }
        });
        $(".views-exposed-form .end_date").datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: "1900:+0",
            dateFormat: 'M dd, yy',
            onSelect: function(dateText, inst) {
                var date1 = new Date(dateText);
                $(".views-exposed-form .start_date").datepicker("option", "maxDate", date1);
                if ($(".views-exposed-form .start_date").val() == '') {
                    $(".views-exposed-form .start_date").val($(this).val());
                }

                $("#edit-field-end-date").datepicker({
                    dateFormat: 'yy-mm-dd'
                }).datepicker('setDate', date1);
                $("#edit-field-end-date").val($("#edit-field-end-date").val() + " 23:59:59");
            }
        });
    }
    $('.path-search .views-exposed-form .search-form-submit').click(function() {
        $(this).closest('form').submit();
    });

    //Submit search form on enter
    $("input").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            if ($(this).closest('form').attr('id') == 'views-exposed-form-search-term-page-1' ||
                $(this).closest('form').attr('id') == 'views-exposed-form-category-list-page-page-1' ||
                $(this).closest('form').attr('id') == 'views-exposed-form-articles-list-page-page-1' ||
                $(this).closest('form').attr('id') == 'views-exposed-form-articles-list-page-page-2' ||
                $(this).closest('form').attr('id') == 'views-exposed-form-articles-list-page-page-3' ||
                $(this).closest('form').attr('id') == 'views-exposed-form-articles-list-page-page-4' ||
                $(this).closest('form').attr('id') == 'am-search-block-form') {
                    $(this).closest('form').submit();
            }
            if ($(this).closest('form').attr('id') == 'registration-form') {
                $('.submit_button_login #edit-submit').trigger('click');
            }
            if ($(this).closest('form').attr('id') == 'create-account-form') {
                $('.submit_button_login #edit-create-submit').trigger('click');
            }
        }
    });

    $('.views-exposed-form .form-actions').hide();
    $('.views-exposed-form select').change(function() {
        $(this).closest('form').submit();
    });
    jQuery(window).scroll(function() {
        var topHead = jQuery(window).scrollTop();
        if (topHead >= 31) {
            jQuery(".headerNav").addClass("fixHeader");
            jQuery(".contentArea").addClass("stickyTop");
        } else {
            jQuery(".headerNav").removeClass("fixHeader");
            jQuery(".contentArea").removeClass("stickyTop");
        }
    });
    jQuery('a[data-toggle="collapse"], button[data-toggle="collapse"]').on('click', function() {
        var objectID = jQuery(this).data('target');
        jQuery('a[data-toggle="collapse"]').parent('li').removeClass('active');
        if (jQuery(objectID).hasClass('in')) {
            jQuery(objectID).collapse('hide');
        } else {
            jQuery('.collapse').collapse('hide');
            jQuery(objectID).collapse('show');
            jQuery(this).parent('li').addClass('active');
        }
    });
    jQuery(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        jQuery(this).ekkoLightbox();
    });
    // video player controls start
    var videoPlayer = jQuery('#videoPlayer'),
        videoTrack = jQuery('#videoPlayer').get(0),
        videoPlayBtn = jQuery('.videoBlock .playPauseBtn');
    var videoPlaying = function() {
        event.preventDefault();
        if (videoTrack.paused == false) {
            jQuery(videoPlayBtn).removeClass('videoPlaying');
            videoTrack.pause();
        } else {
            jQuery(videoPlayBtn).addClass('videoPlaying');
            videoTrack.play();
        }
    };
    videoPlayBtn.on('click', function(event) {
        videoPlaying();
    });
    videoPlayer.on('click', function(event) {
        videoPlaying();
    });
    // video player controls end
    // audio player controls - By Gaurav
    var audioPlayer = jQuery('#audioPlayer'),
        /* audioTrack = jQuery('#audioPlayer').get(0),*/
        audioPlayBtn = jQuery('.audioBlock .playPauseBtn'),
        soundOnOff = jQuery('.volumeBtn'),
        seekBar = jQuery('.seekBar'),
        trackTime,
        trackDuration;
    audioPlayBtn.on('click', function(event) {
        event.preventDefault();
        jQuery(this).toggleClass('playing');
        /*audioTrack = jQuery(this).siblings('audio').get(0);*/
        audioTrack = jQuery(this).siblings('.audiofield').children('audio').get(0);

        if (audioTrack.paused == false) {
            audioTrack.pause();
        } else {
            audioTrack.play();
            /*audioPlayer = jQuery(this).siblings('audio');*/
            audioPlayer = jQuery(this).siblings('.audiofield').children('audio');
            audioPlayer.on('timeupdate', function() {
                jQuery(this).parent('.audiofield').siblings('.seeBarTime').find('.seekBar').attr("value", this.currentTime / this.duration);
                jQuery(this).parent('.audiofield').siblings('.seeBarTime').find('.runTime.playTime').html(parseInt(this.currentTime / 60, 10) + ":" + parseInt(this.currentTime % 60));
            });
        }
    });
    soundOnOff.on('click', function(event) {
        /*audioTrack = jQuery(this).parent('.audioBottom').siblings('audio').get(0);*/
        audioTrack = jQuery(this).parent('.audioBottom').siblings('.audiofield').children('audio').get(0);
        if (audioTrack.muted) {
            jQuery(this).removeClass('muted');
            audioTrack.muted = false;
        } else {
            jQuery(this).addClass('muted');
            audioTrack.muted = true;
        }
    });
    jQuery('audio').each(function() {
        if (this.readyState > 0) {
            var minutes = parseInt(this.duration / 60, 10);
            var seconds = parseInt(this.duration % 60);
            jQuery(this).parent('.audioBottom').siblings('.seeBarTime').find('.playTime.duration').html(minutes + ":" + seconds);
        }
    });
    var player = document.querySelector("audio");
    var progressBar = document.querySelector("progress");
    if (progressBar) progressBar.addEventListener("click", seek);

    function seek(e) {
        var percent = e.offsetX / this.offsetWidth;
        player.currentTime = percent * player.duration;
        progressBar.value = percent / 100;
    }
    // audio player controls - By Gaurav ENDS
    //Body Image colorbox
    jQuery('article figure').each(function() {
        var img = jQuery(this).find('img');
        var src = jQuery(this).find('img').prop('src');
        jQuery(this).find('img').wrap('<a href="' + src + '" class="colorbox cboxElement">');
    });
    // Changes for login popup
    // Login, Email, Forgot Password : Start
    jQuery('.path-registration .loginModel .form-submit').on('click', function() {
        localStorage.setItem('amform', 'amemailform');
    });
    jQuery('.path-registration .AMloginBlock .form-submit').on('click', function() {
        localStorage.setItem('amform', 'amloginform');
    });
    jQuery('.path-registration .AMloginPasswordBlock .form-submit').on('click', function() {
        localStorage.setItem('amform', 'amforgotform');
    });
    jQuery(".path-registration .usePassword").on('click', function() {
        jQuery(".path-registration .pageContent > div.row").hide();
        jQuery(".path-registration .AMloginPasswordBlock").hide();
        jQuery(".path-registration .AMloginBlock").show();
        //localStorage.setItem('amform', 'amloginform');
    });
    jQuery(".path-registration .loginLink").on('click', function() {
        jQuery(".path-registration .pageContent > div.row").show();
        jQuery(".path-registration .AMloginBlock").hide();
        //localStorage.setItem('amform', 'amemailform');
    });
    jQuery(".path-registration .forgotPasswordLink").on('click', function() {
        jQuery(".path-registration .pageContent > div.row").hide();
        jQuery(".path-registration .AMloginBlock").hide();
        jQuery(".path-registration .AMloginPasswordBlock").show();
        //localStorage.setItem('amform', 'amforgotform');
    });
    jQuery('#block-toprightmenu').on('click', 'li:first', function() {
        localStorage.removeItem('amform');
    });
    if (localStorage.getItem('amform') == 'amloginform') {
        jQuery(".path-registration .pageContent > div.row").hide();
        jQuery(".path-registration .AMloginPasswordBlock").hide();
        jQuery(".path-registration .AMloginBlock").show();
    } else if (localStorage.getItem('amform') == 'amemailform') {
        jQuery(".path-registration .pageContent > div.row").show();
        jQuery(".path-registration  .AMloginBlock").hide();
    } else if (localStorage.getItem('amform') == 'amforgotform') {
        jQuery(".path-registration .pageContent > div.row").hide();
        jQuery(".path-registration .AMloginBlock").hide();
        jQuery(".path-registration .AMloginPasswordBlock").show();
    } else {
        jQuery(".path-registration .pageContent > div.row").show();
    }
    // Login, Email, Forgot Password: END
    // Changes for login popup - Ends
    //Image expand Icon
    jQuery('a.colorbox.cboxElement').each(function() {
        jQuery(this).append('<span class="iconExpand"></span>');
    });
    jQuery(document).ready(function() {
        if ($("a.colorbox.cboxElement").length) {
            jQuery('a.colorbox.cboxElement').colorbox();
        }
    });
    $('.filterBtn').click(function() {
        if ($(this).hasClass('collapsed')) {
            $(this).text('Hide filters');
        } else {
            $(this).text('Show filters');
        }
    });
    //check if user is logged in or not
    //get class name from Body user-logged-in
    //only do this for mobile
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        // some code..
        var is_logged_in = $('body').hasClass('user-logged-in');
        if (is_logged_in) {
            $('.navbar-nav .loggedin').show();
            $('.navbar-nav .notloggedin').hide();
        } else {
            $('.navbar-nav .loggedin').hide();
            $('.navbar-nav .notloggedin').show();
        }
    }
    //this is to focus on search box when user clicks on serach button
    jQuery('.searchLink').click(function() {
        jQuery('.form-search').focus();
    });
    //this is to focus on newsletter box when user clicks on newsletter button
    jQuery('.newsLetterLink').click(function() {
        jQuery('.newsLetterWrapper .form-email').focus();
    });
    //***********password placeholder and email field position********
    jQuery('input[type=password].password-field').attr('placeholder', 'Leave blank unless you would like to set a new password');
    //jQuery('.form-item-field-first-name-0-value').before(jQuery('.form-item-mail'));
    //**********code ends here************
    //**********************Rowwise equal height*******************
    var equalHieght = function() {
        if (jQuery(window).width() >= 1024) {
            // Rowwise equal height
            // Select and loop the container element of the elements you want to equalise
            jQuery('.reactionsNonDetailsWrapper .listingRow').each(function() {
                // Cache the highest
                var highestBox = 0;
                // Select and loop the elements you want to equalise
                jQuery('.homeListing', this).each(function() {
                    // If this box is higher than the cached highest then store it
                    if (jQuery(this).height() > highestBox) {
                        highestBox = jQuery(this).height();
                    }
                });
                // Set the height of all those children to whichever was highest
                jQuery('.homeListing', this).height(highestBox);
            });
        } else {
            jQuery('.reactionsNonDetailsWrapper .listingRow .homeListing').removeAttr('style')
        }
    }
    equalHieght();
    jQuery(document).resize(function(e) {
        equalHieght();
    });
    jQuery(window).resize(function(e) {
        equalHieght();
    });
    //**********************Code Ends Here for Rowwise equal height *******************
    //***************************Code For Point Counter Point module Hover Effect***********************************************//
    jQuery('.pointCpointContent .view-content div:first-child .homeListing').on({
        'mouseover': function() {
            jQuery('.thumbImages .leftThumb').css('background', '#5534a6');
            jQuery('.thumbImages .leftThumb img').css('opacity', 0.6);
        },
        'mouseout': function() {
            jQuery('.thumbImages .leftThumb').css('background', 'none');
            jQuery('.thumbImages .leftThumb img').css('opacity', 1);
        }
    });
    jQuery('.pointCpointContent .view-content div:last-child .homeListing').on({
        'mouseover': function() {
            jQuery('.thumbImages .rightThumb').css('background', '#5534a6');
            jQuery('.thumbImages .rightThumb img').css('opacity', 0.6);
        },
        'mouseout': function() {
            jQuery('.thumbImages .rightThumb').css('background', 'none');
            jQuery('.thumbImages .rightThumb img').css('opacity', 1);
        }
    });
    //***************************Code Ends Here For Hover Effect***********************************************//
    //********Code for confirm password visibility field***************//
    if (jQuery('div.password-confirm')) {
        jQuery('div.password-confirm').css("visibility", "hidden");
    }

    $(".login-access").on('click', function (e) {
        setTimeout(function(){$("body").addClass("modal-open");}, 500);
    });




        /*$( document ).on( "click", "#edit-first-name, #edit-last-name, #edit-candidate-mail, #edit-password", function() {
            jQuery("html, body").animate({
                scrollTop: 0
                }, 100);
            return false;
        });*/

        $( document ).on( "click", ".modal .form-control", function() {
            jQuery("html, body").animate({
                scrollTop: 0
                }, 100);
            return false;
        });

        


    //*******Code ends here******//
})(jQuery);

function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
}

function featuredClick() {
    jQuery("#alphabetical_article").removeClass('active');
    jQuery("#featured_article").addClass('active');
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    window.location.replace(baseUrl + "voices?field_is_featured_value=1");
}

function alphabeticalClick() {
    jQuery("#featured_article").removeClass('active');
    jQuery("#alphabetical_article").addClass('active');
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    window.location.replace(baseUrl + "voices?field_is_featured_value=All&sort_by=title&sort_order=ASC");
}

function mostRecentClick() {
    jQuery("#alphabetical_article1").removeClass('active');
    jQuery("#featured_article1").addClass('active');
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    var url = getUrlParameter('search_api_fulltext');
    var d1 = getUrlParameter('field_start_date');
    var d2 = getUrlParameter('field_end_date');
    if (url == undefined) {
        url = '';
    }
    if (d1 == undefined) {
        d1 = '';
    }
    if (d2 == undefined) {
        d2 = '';
    }
    window.location.replace(baseUrl + "search?search_api_fulltext=" + url + "&sort_by=field_publication_date&field_start_date=" + d1 + "&field_end_date=" + d2 + "&sort_order=DESC");
}

function mostReleventClick() {
    jQuery("#featured_article1").removeClass('active');
    jQuery("#alphabetical_article1").addClass('active');
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    var url = getUrlParameter('search_api_fulltext');
    var d1 = getUrlParameter('field_start_date');
    var d2 = getUrlParameter('field_end_date');
    if (url == undefined) {
        url = '';
    }
    if (d1 == undefined) {
        d1 = '';
    }
    if (d2 == undefined) {
        d2 = '';
    }
    window.location.replace(baseUrl + "search?search_api_fulltext=" + url + "&sort_by=search_api_relevance&field_start_date=" + d1 + "&field_end_date=" + d2 + "&sort_order=DESC");
}

function clearSearchFilter() {
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    window.location.replace(baseUrl + "search");
}

function clearTermFilter() {
    var url = window.location.href; // Returns full URL
    var url1 = url.split('?');
    window.location.replace(url1[0]);
}

function handle(e) {
    if (e.keyCode === 13) {
        e.preventDefault(); // Ensure it is only this code that rusn
        var txt = $("#edit-keys").val();
        var baseUrl = window.location.protocol + "//" + window.location.host + "/";
        window.location.replace(baseUrl + "search?search_api_fulltext=" + txt + "&sort_by=search_api_relevance&sort_order=DESC");
    }
}

(function ($) {
    var touchstart;
    var touchend;
    jQuery('.scrollableArea .expanded.dropdown').on('touchstart', function(e) {
        touchstart = e.originalEvent.touches[0].clientX;
        //console.log(touchstart);
    });
    jQuery('.scrollableArea .expanded.dropdown').on('touchend', function(e) {
        touchend = e.originalEvent.changedTouches[0].clientX;
        //console.log(touchend );
        if (touchend == touchstart) {
            //console.log("Yesss"+jQuery(this).find('a').attr('href'));
            window.location = jQuery(this).find('a').attr('href');
        }
    });
    /*select form elemnts with smaller width*/
    $(".form-info-wrap div").each(function() {
        if ($(this).width() < 300) {
            $(this).addClass("small");
        }
    });
    $(window).on('load', function() {
        if ($("#block-views-block-our-voices-page-block-4").length) {
            $('#block-dfptaglistingrightrailatf').addClass('with-ad-padding');
        }

        // Refresh google ad.
        /*googletag.pubads().refresh();*/
         if (window.location.pathname.indexOf("/comment") >= 0) {
            $(".showCommentHeading a").trigger("click");
            setTimeout( function() {
                var hash = window.location.hash.substring(1);
                if (hash) {
                    // void some browsers issue
                    window.scrollTo(0, 0);
                    $('html,body').animate({scrollTop: parseInt($("#"+hash).offset().top) - parseInt(95)}, 'slow');
                }
            }, 500);
        }
    });
}(jQuery));

(function ($) {
    jQuery('#loginModal #block-donateorsubscribemessage').hide();
    $(document).on('hide.bs.modal','#loginModal', function () {
        jQuery('#loginModal').removeClass( "donate-subscribe" );
        jQuery('#loginModal #block-donateorsubscribemessage').hide();
    });

    jQuery(document).ready(function() {
        var url = window.location.href; // Returns full URL
        var url1 = url.split('?');
        var url2 = url1[0].split('/');
        if (url2[3] == 'search' && url1[1] == undefined) {
            jQuery('#edit-sort-by').val("search_api_relevance");
        }
    });

    jQuery('.searchLink').click(function(){
        jQuery('#block-search-block input[name="search_api_fulltext"]').focus();
    });

    if (!jQuery.cookie("appeal_bottom_hat")) {

        $(".region.region-bottom-hat").css("margin-bottom", "112px");
        jQuery('.am-bottom-hat a.btn-close').click(function(){
            jQuery(".region.region-bottom-hat").css("margin-bottom", "0");
            jQuery('.am-bottom-hat').hide();

            var exp_time = new Date();
            var minutes = 30;
            exp_time.setTime(exp_time.getTime() + (minutes * 60 * 1000));

            jQuery.cookie("appeal_bottom_hat", 1, {
                expires : exp_time,
                path    : '/',
                secure  : true
              });
        });
    }
    else {
        jQuery('.am-bottom-hat').hide();
        jQuery(".region.region-bottom-hat").css("margin-bottom", "0");
    }

    // TODO: Need to add browser size
    if (window.matchMedia('(min-width:992px) and (max-width:1024px)').matches) {
        // do functionality on screens smaller than 768px
        if (jQuery('.heroImageWrapper .container-right').height() > 370){
            jQuery('.heroImageWrapper .container-right .heroImageListing.homeListing').last().hide();
        }
    }
    else if (window.matchMedia('(min-width:1025px) and (max-width:1300px)').matches) {
        if (jQuery('.heroImageWrapper .container-right').height() > 530){
            jQuery('.heroImageWrapper .container-right .heroImageListing.homeListing').last().hide();
        }
    }
    else {
        if (jQuery('.heroImageWrapper .container-right').height() > 464){
            jQuery('.heroImageWrapper .container-right .heroImageListing.homeListing').last().hide();
        }
    }

}(jQuery));
