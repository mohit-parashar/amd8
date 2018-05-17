/****************Code for readmore ******************/
(function($) {
    $('.trimLastUserComment').hide();
    $(".show-more-comment").click(function () {
        $(this).toggleClass('down');
        if ($(this).text() == '...read more') {
            $(this).text('show less').prev().slideToggle(0, function() {
                if ($(this).is(':visible'))
                $(this).css('display','inline');
            });
        }
        else {
            $(this).text('...read more').prev().slideToggle(0); 
        }
    });
    $('.trimLastUserComment').slideUp(0);
})(jQuery);
/***************Readmore code end*********************/