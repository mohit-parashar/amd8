(function($) {
    Drupal.behaviors.article_custom = {
        attach: function(context, settings) {
            $('#edit-field-by-author-0-target-id').on('blur', function() {
                var str = $(this).val();
                var res = str.split("(");
                var res1 = res[1].split(")");
                var nid = res1[0];
                var baseurl = window.location.origin;
                var querystring = nid;
                $.ajax({
                    method: "POST",
                    url: baseurl + "/article_custom_author_bio/" + nid,
                    data: {
                        nid: nid
                    },
                    success: function(msg) {
                        $("textarea[data-drupal-selector='edit-field-author-bio-0-value']").val(msg);
                        CKEDITOR.instances['edit-field-author-bio-0-value'].setData(msg);
                    }
                });
            });
            if ($('div.authorBlockBottom.focused').length) {
                $('div.authorBlockBottom.focused').parent().removeClass('multi-author-grid');
            }
            if (typeof(drupalSettings.article_custom.article_type) != "undefined" && drupalSettings.article_custom.article_type !== null) {
                var article_type = drupalSettings.article_custom.article_type;
                if (article_type == 'author') {
                    $('div.container.author').find('div.col-md-4.container-right').addClass('author-focused');
                }
            }
        }
    };
})(jQuery);
