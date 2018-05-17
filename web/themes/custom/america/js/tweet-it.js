( function($) {
	$('.contentTweetBlock').prepend('<blockquote class="iconQuote"></blockquote>');
	$('.contentTweetBlock').append('<div class="tweetlLink"><div class="twitter"><span class="iconTwitter"></span><span class="text">Tweet this</span></div></div>');
	$(".contentTweetBlock").on("click", function() {
		var str;
		var t = $(this).text().trim();
		var url = window.location.href;
		//str=$t.replace(/^"/, "");
		str= t.replace(/Tweet this$/, "");
		var tweet = str.substr(0, 100) + ' '+ url + ' via @americamag';
		var left = ($(window).width() / 2) - (900 / 2);
		var top = ($(window).height() / 2) - (600 / 2);
		window.open('https://twitter.com/intent/tweet?text='+ tweet + '', '_blank', "height = 500, width = 500, top=" + top + ", left=" + left);
	});
	$(".am-bookmark .Disabled a").prop('title', 'Bookmark this');
	$(".am-bookmark .Enabled a").prop('title', 'Remove Bookmark');
}(jQuery));
