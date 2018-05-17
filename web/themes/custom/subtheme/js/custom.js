// JavaScript Document
jQuery(document).ready(function(){
	jQuery(window).scroll(function() {    
		var topHead = jQuery(window).scrollTop();
		if (topHead >= 31) {
			jQuery(".headerNav").addClass("fixHeader");
			jQuery(".contentArea").addClass("stickyTop");
		}else{
			jQuery(".headerNav").removeClass("fixHeader");
			jQuery(".contentArea").removeClass("stickyTop");
		}
	});
	
	jQuery('a[data-toggle="collapse"], button[data-toggle="collapse"]').on('click',function(){				
				var objectID=jQuery(this).data('target');	
				jQuery('a[data-toggle="collapse"]').parent('li').removeClass('active');						
				if(jQuery(objectID).hasClass('in')){
           jQuery(objectID).collapse('hide');
					 
				}else{
					 jQuery('.collapse').collapse('hide');
           jQuery(objectID).collapse('show');
					 jQuery(this).parent('li').addClass('active');
				}
 	});
	jQuery(document).on('click', '[data-toggle="lightbox"]', function(event) {
			event.preventDefault();
			jQuery(this).ekkoLightbox();
	});	
	// audio player controls
	var audioPlayer = jQuery('#audioPlayer'),
			audioTrack = jQuery('#audioPlayer').get(0),
			audioPlayBtn =	jQuery('.audioBlock .playPauseBtn'),
			soundOnOff =	jQuery('.volumeBtn'),
			seekBar = jQuery('.seekBar'),
			trackTime,
			trackDuration;			
			//audioTrack.muted ? soundOnOff.addClass('muted') : soundOnOff.removeClass('muted');
			
	audioPlayBtn.on('click', function(event) {
			event.preventDefault();
			jQuery(this).toggleClass('playing');
			if (audioTrack.paused == false) {
					audioTrack.pause();
			} else {
					audioTrack.play();
			}
	});	
	soundOnOff.on('click', function(event) {
		if(audioTrack.muted){
			 jQuery(this).removeClass('muted');
			 audioTrack.muted=false;
		}else{
			 jQuery(this).addClass('muted');
			 audioTrack.muted=true;
		}
	});
	audioPlayer.on("timeupdate", function() {
		var time = this.currentTime;
    var minutes = Math.floor(time / 60);   
    var seconds = Math.floor(time);
		trackTime = minutes +':'+ seconds;
		trackDuration = this.duration;
		console.log(trackTime +' :: '+ trackDuration);
	});
	// audio player controls end
	
	// video player controls start
	var videoPlayer = jQuery('#videoPlayer'),
			videoTrack = jQuery('#videoPlayer').get(0),
			videoPlayBtn = jQuery('.videoBlock .playPauseBtn');
			var videoPlaying = function(){				
						event.preventDefault();						
						if (videoTrack.paused == false) {
							  jQuery(videoPlayBtn).removeClass('videoPlaying');
								videoTrack.pause();								
						}else {
							  jQuery(videoPlayBtn).addClass('videoPlaying');
								videoTrack.play();								
						}				
			};			
			videoPlayBtn.on('click', function(event){videoPlaying();});	
			videoPlayer.on('click', function(event){videoPlaying();});	
	// video player controls end
	
});