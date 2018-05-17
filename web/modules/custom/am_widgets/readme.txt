This module needs Video and Audio View
dependency -
https://www.drupal.org/project/video_embed_field
https://www.drupal.org/project/audiofield




To do

//Add module dependency to info file
add js to pages where video widget view appears
// changed video/audio autoplay
//localhost for audio src
//Images and post date
//same functionality on image click and title click
Make it work for multiple instances


Ref

http://stackoverflow.com/questions/15468744/swapping-between-youtube-embeds-with-image-hyperlink-clicks

https://gist.github.com/yangshun/9892961


http://creative.wddemo.net/Demo/america/html/index.html




// popup

created a region in .info file
change in page.html.twig
stop redirect, remove why content from am_login
stop redirect, remove why content from am_registration and email validation
Put # for login link from cms
Assign login/email login/password block to Login region
create a block for why content
changed switching of login/email/forgot password from custom.js fixed in am_widget.js

To Do -

Responsive



----------------------------------------------------------------------
changes in page.html.twig

Put this code before </footer>
<!--- Popup -->    
    <div class="modal fade" id="loginModal">
		<div class="modal-dialog modal-md">				
			<div class="modal-content">									          
      			<div class="close-button">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
      			</div>
	            <div class="modal-body">
		            {% if page.Modal_login %}
                        {{ page.Modal_login}}
                    {% endif %}
                    <a href="#" class="close-modal"></a>
	            </div>	
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
    <!--- Popup -->