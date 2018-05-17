/**
 * @file
 * The video_embed_field colorbox integration.
 */
(function($) {
    Drupal.behaviors.am_widgets = {
        attach: function(context, settings) {
            // Popup
            $('#loginModal input[type=submit]').click(function() {
                $('.login-message').html('');
            });
            if ($('.social_login iframe').length) {
                $('.social_login iframe').css('height','auto');
            }
            // Set data attritute for Registration link to open it in a popupu
            if (window.location.pathname !== '/registration') {
                $('#block-toprightmenu ul li:first-child a').attr('data-toggle', 'modal');
                $('#block-toprightmenu ul li:first-child a').attr('data-target', '#loginModal');
                $('#block-toprightmenu ul li:first-child a').removeAttr("href");
                //$('#block-toprightmenu ul li:first-child a').attr('href','#');
                $('#block-america-main-menu ul li.login.notloggedin a').attr('data-toggle', 'modal');
                $('#block-america-main-menu ul li.login.notloggedin a').attr('data-target', '#loginModal');
                $('#block-america-main-menu ul li.login.notloggedin a').removeAttr("href");
                //$('#block-america-main-menu ul li.login a').attr('href','#');
                $('li.comment-forbidden a').attr('data-toggle', 'modal');
                $('li.comment-forbidden a').attr('data-target', '#loginModal');
                $('li.comment-forbidden a').attr('href', '');
            }
            // Set data attritute for Registration link to open it in a popupu
            // Reload parent page after successful login
            $('.close-modal').on('click', function() {
                window.parent.jQuery('#loginModal .close').trigger({
                    type: 'click'
                });
                window.parent.location.reload();
            });
            // Reload parent page after successful login
            // Clear error message in case of login/onetime login/forgot password switch
            $('.loginLink').on('click', function() {
                $('.login-message').html('');
            });
            $('.forgotPasswordLink').on('click', function() {
                $('.login-message').html('');
            });
            $('.usePassword').on('click', function() {
                $('.login-message').html('');
            });
            // Clear error message in case of login/onetime login/forgot password switch
            // To open Policy page in parent window
            $('a.readPolicies').click(function(e) {
                e.preventDefault();
                window.top.location.href = $(this).attr('href');
            });
            // In case of error message forgot password link
            $('.AMloginBlock .login-message li.item a').click(function(e) {
                e.preventDefault();
                $('.login-message a.close').trigger('click');
                $(".AMloginBlock").hide();
                $(".AMloginPasswordBlock").show();
            });
            // Login, Email, Forgot Password : Start
            $('#loginModal .one-time-login .form-submit').on('click', function() {
                localStorage.setItem('amform', 'amemailform');
            });
            $('#loginModal .user-login .form-submit').on('click', function() {
                localStorage.setItem('amform', 'amloginform');
            });
            $('#loginModal .forgot-password .form-submit').on('click', function() {
                localStorage.setItem('amform', 'amforgotform');
            });
            $('#loginModal .create-account .form-submit').on('click', function() {
                localStorage.setItem('amform', 'amcreateaccountform');
            });
            $(".usePassword").on('click', function() {
                $("#loginModal .one-time-login").hide();
                $("#loginModal .forgot-password").hide();
                $("#loginModal .create-account").hide();
                $("#loginModal .user-login").show();
            });
            $(".loginLink").on('click', function() {
                $("#loginModal .one-time-login").show();
                $("#loginModal .forgot-password").hide();
                $("#loginModal .user-login").hide();
                $("#loginModal .create-account").hide();
                $("#loginModal .social-login-block-form").show();
                // Message Content
                $("#loginModal .create-why").hide();
                $("#loginModal .Join-reader-community").hide();
                $("#loginModal .login-why").show();
            });
            $(".forgotPasswordLink").on('click', function() {
                $("#loginModal .one-time-login").hide();
                $("#loginModal .forgot-password").show();
                $("#loginModal .user-login").hide();
                $("#loginModal .create-account").hide();
            });
            $(".create-account-link").on('click', function() {
                $("#loginModal .one-time-login").hide();
                $("#loginModal .forgot-password").hide();
                $("#loginModal .user-login").hide();
                $("#loginModal .create-account").show();
                $("#loginModal .social-login-block-form").hide();
                // Message Content
                $("#loginModal .login-why").hide();
                $("#loginModal .create-why").show();
            });
            $('#block-toprightmenu').on('click', 'li:first', function() {
                localStorage.removeItem('amform');
                login_popupu_reset();
            });
            if (localStorage.getItem('amform') == 'amloginform') {
                $("#loginModal .one-time-login").hide();
                $("#loginModal .forgot-password").hide();
                $("#loginModal .user-login").show();
                $("#loginModal .create-account").hide();
            } else if (localStorage.getItem('amform') == 'amemailform') {
                $("#loginModal .one-time-login").show();
                $("#loginModal .forgot-password").hide();
                $("#loginModal .user-login").hide();
                $("#loginModal .create-account").hide();
            } else if (localStorage.getItem('amform') == 'amforgotform') {
                $("#loginModal .one-time-login").hide();
                $("#loginModal .forgot-password").show();
                $("#loginModal .user-login").hide();
                $("#loginModal .create-account").hide();
            } else if (localStorage.getItem('amform') == 'amcreateaccountform') {
                $("#loginModal .one-time-login").hide();
                $("#loginModal .forgot-password").hide();
                $("#loginModal .user-login").hide();
                $("#loginModal .create-account").show();
            } else {
                login_popupu_reset();
            }
            // Login, Email, Forgot Password: END
            // Popup Ends
            // Video Widget
            $('.videoGalleryBlock .video_widget_switch').click(function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
				var url1=$(this).attr('id');//AN changes on 13/09/2017 for youtube tilte;
							
                // var title = $(this).attr('title');
                var title = $(this).next('.vdoTitle').children('.video-widget-small-title').text();
                var created = $(this).next('.vdoTitle').children('.video-widget-small-created').html();
                $(this).closest('.videoGalleryBlock').find(".video-widget-big-title").html('<a href="'+url1+'">'+title+'</a>');////AN changes on 13/09/2017 for youtube tilte(url1);
                $(this).closest('.videoGalleryBlock').find(".video-widget-big-created").html(created);
                $(this).closest('.videoGalleryBlock').find(".video-widget-big iframe")[0].src = parseVideo(url) + "?autoplay=1";
                $(this).closest('.videoGalleryBlock').find(".video-widget-small").css("display", "block");
			    $(this).closest('.videoGalleryBlock').find(".video-widget-big-img").css("display", "none");
			    $(this).closest('.videoGalleryBlock').find(".video-embed-field-responsive-video").css("display", "block");
                $(this).closest(".video-widget-small").css("display", "none");
            });
            $(".videoGalleryBlock .video-widget-small .video-widget-small-title").click(function() {
                $(this).closest(".video-widget-small").children(".video_widget_switch").trigger('click');
            });
            $(".videoGalleryBlock .video-widget-big .video-widget-big-img img").click(function() {
                $(this).closest(".video-widget-big-img").css("display", "none");
				
				$(this).closest(".video-widget-big").children(".video-embed-field-responsive-video").css("display", "block");
				
					var VideoUrl = $(this).closest(".video-widget-big").children(".video-embed-field-responsive-video").children("iframe")[0].src;
					
					VideoUrl = VideoUrl.replace('autoplay=0', 'autoplay=1');
					$(this).closest(".video-widget-big").children(".video-embed-field-responsive-video").children("iframe")[0].src = VideoUrl;
				
            });
            $(".videoGalleryBlock .video-widget-big .video-widget-big-img span.play-video").click(function() {
                $(this).closest('.video-widget-big-img').find("img").trigger('click');
            });
            // Audio Widget
            $('.audioGalleryBlock .audio_widget_switch').click(function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var title = $(this).attr('title');
                var desc = $(this).next('.audio-widget-small-content').children('.audio-widget-small-desc').html();
                var series = $(this).next('.audio-widget-small-content').children('.audio-widget-small-series').html();
                var created = $(this).next('.audio-widget-small-content').children('.audio-widget-small-created').html();
                $(this).closest('.audioGalleryBlock').find(".audio-widget-big-title").html(title);
                $(this).closest('.audioGalleryBlock').find(".audio-widget-big-desc").html(desc);
                $(this).closest('.audioGalleryBlock').find(".audio-widget-big-series").html(series);
                $(this).closest('.audioGalleryBlock').find(".audio-widget-big-created").html(created);
                $(this).closest('.audioGalleryBlock').find("audio").attr("src", url);
                $(this).closest('.audioGalleryBlock').find("audio").attr("autoplay", "autoplay");
                $(this).closest('.audioGalleryBlock').find(".audio-widget-small").css("display", "block");
                $(this).closest('.audio-widget-small').css("display", "none");
                $(this).closest('.audioGalleryBlock').find(".playPauseBtn").trigger('click');
                $(this).closest('.audioGalleryBlock').find(".playPauseBtn").addClass('playing');
            });
            /* Video Widget
      $('.block-views-blockvideo-widget-block-1 .video_widget_switch').click(function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var title = $(this).attr('title');
        var created = $(this).next('.vdoTitle').children('.video-widget-small-created').html();

        $(".block-views-blockvideo-widget-block-1 .video-widget-big .video-widget-big-title").html(title);
        $(".block-views-blockvideo-widget-block-1 .video-widget-big .video-widget-big-created").html(created);
        $(".block-views-blockvideo-widget-block-1 .video-widget-big iframe")[0].src = parseVideo(url)+"?autoplay=1";
        $(".block-views-blockvideo-widget-block-1 .view-footer .video-widget-small").css("display","block");
        $(".block-views-blockvideo-widget-block-1 .video-widget-big .video-widget-big-img").css("display","none");
        $(".block-views-blockvideo-widget-block-1 .video-widget-big .video-embed-field-responsive-video").css("display","block");
        $(this).closest(".video-widget-small").css("display","none");
      });

      $(".block-views-blockvideo-widget-block-1 .video-widget-small .video-widget-small-title").click(function(){
        $(this).closest(".video-widget-small").children(".video_widget_switch").trigger('click');
      });

      $(".block-views-blockvideo-widget-block-1 .video-widget-big .video-widget-big-img img").click(function(){
        $(this).closest(".video-widget-big-img").css("display","none");
        $(this).closest(".video-widget-big").children(".video-embed-field-responsive-video").css("display","block");
        var VideoUrl = $(this).closest(".video-widget-big").children(".video-embed-field-responsive-video").children("iframe")[0].src;
        VideoUrl = VideoUrl.replace('autoplay=0', 'autoplay=1');
        $(this).closest(".video-widget-big").children(".video-embed-field-responsive-video").children("iframe")[0].src = VideoUrl;
      });
      */
            // Audio Widget //
            /*
      $('.block-views-blockaudio-widget-block-1 .audio_widget_switch').click(function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var title = $(this).attr('title');
        var desc = $(this).next('.audio-widget-small-content').children('.audio-widget-small-desc').html();
        var series = $(this).next('.audio-widget-small-content').children('.audio-widget-small-series').html();
        var created = $(this).next('.audio-widget-small-content').children('.audio-widget-small-created').html();

        $(".block-views-blockaudio-widget-block-1 .audio-widget-big .audio-widget-big-title").html(title);
        $(".block-views-blockaudio-widget-block-1 .audio-widget-big .audio-widget-big-desc").html(desc);
        $(".block-views-blockaudio-widget-block-1 .audio-widget-big .audio-widget-big-series").html(series);
        $(".block-views-blockaudio-widget-block-1 .audio-widget-big .audio-widget-big-created").html(created);
        $(".block-views-blockaudio-widget-block-1 .audio-widget-big audio").attr("src", url);
        $(".block-views-blockaudio-widget-block-1 .audio-widget-big audio").attr("autoplay", "autoplay");
        $(".block-views-blockaudio-widget-block-1 .view-footer .audio-widget-small").css("display","block");
        $(this).closest(".audio-widget-small").css("display","none");

        $('.block-views-blockaudio-widget-block-1 .audio-widget-big .audioBlock .playPauseBtn').trigger('click');
        $('.block-views-blockaudio-widget-block-1 .audio-widget-big .audioBlock .playPauseBtn').addClass('playing');
      });
      */
        }
    };

    function parseVideo(url) {
        // - Supported YouTube URL formats:
        //   - http://www.youtube.com/watch?v=My2FRPA3Gf8
        //   - http://youtu.be/My2FRPA3Gf8
        //   - https://youtube.googleapis.com/v/My2FRPA3Gf8
        // - Supported Vimeo URL formats:
        //   - http://vimeo.com/25451551
        //   - http://player.vimeo.com/video/25451551
        // - Also supports relative URLs:
        //   - //player.vimeo.com/video/25451551
        url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);
        if (RegExp.$3.indexOf('youtu') > -1) {
            var type = 'youtube';
            var embedUrl = "https://www.youtube.com/embed/" + RegExp.$6
        } else if (RegExp.$3.indexOf('vimeo') > -1) {
            var type = 'vimeo';
            var embedUrl = "https://player.vimeo.com/video/" + RegExp.$6
        }
        return embedUrl;
    }

    function login_popupu_reset() {
        jQuery("#loginModal .one-time-login").show();
        jQuery("#loginModal .forgot-password").hide();
        jQuery("#loginModal .user-login").hide();
        jQuery("#loginModal .create-account").hide();
        jQuery("#loginModal .social-login-block-form").show();
        // Message Content
        jQuery("#loginModal .create-why").hide();
        jQuery("#loginModal .email-not-confirmed").hide();
        jQuery("#loginModal .Join-reader-community").hide();
        jQuery("#loginModal .login-why").show();
        jQuery('.social_login iframe').css('height', '160px');
    }
    // initialize loginpopup
    login_popupu_reset();
    if ($(".tip").length) {
        $(".tip").popover({
            html: true,
            triger: 'focus',
            content: function() {
                return $(this).next('div').html();
            }
        });
    }
})(jQuery);
