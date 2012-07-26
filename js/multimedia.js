jQuery(document).ready(function($) {
	$('.multimedia_box_dialog').multimedia_box();
});




(function($){
 $.fn.multimedia_box = function() {
	var currentBox = null;
	var currentDialog = null;
	var currentButtonHolder = null;

	var imageID;
	var currentImage;

	initialize();

	function initialize() {
		//Logic when media item is selected
		window.multimedia_send_to_editor = window.send_to_editor;
		window.send_to_editor = function( html ) {
			if (currentDialog) {
				currentImage = jQuery('img',html).attr('src');
				var imgClass = jQuery('img',html).attr('class');
				imageID = imgClass.substring(imgClass.lastIndexOf('wp-image-')+9);

				tb_remove();
				$( currentDialog ).parent().show();
				$(".ui-widget-overlay").show();
			} else {
				window.multimedia_send_to_editor(html);
			}
		}

		//Remove logic

		$( '.addnew' ).click(function( evt ) {
			evt.preventDefault();
			evt.stopPropagation();

			var box_id = $( this ).find( '.multimedia_id' ).val();
			var number = $( this ).closest( '.multimedia_box_holder' ).children( '.multimedia_box' ).not( '.addnew' ).length

			var html = '<div id="multimedia_box_' + box_id + '_' + number + '" class="multimedia_box">';

			html += '<input type="hidden" class="multimedia_box_type" name="' + box_id + '_type[' + number + ']" value="" />';
			html += '<input type="hidden" class="multimedia_box_imageID" name="' + box_id + '_imageID[' + number + ']" value="" />';
			html += '<input type="hidden" class="multimedia_box_moviecode" name="' + box_id + '_moviecode[' + number + ']" value="" />';

			html += '<input type="hidden" class="multimedia_box_image" name="' + box_id + '_imageURL[' + number + ']" value="" />';
			html += '<p class="multimedia_box_selected" style="display:none"><img src="" alt="" class="multimedia_box_image_src" /><a href="#" class="remove">Remove</a></p>';
			html += '<p class="multimedia_box_unselected">Select an item</p>';

			html += '</div>';
			
			$( this ).before( html );
		});
	}


    return this.each(function() {
		/* Dialog */
		dialog = $(this);
		buttonHolder = dialog.closest('.inside');

		$( dialog ).dialog({
			dialogClass:'wp-dialog',  
			autoOpen: false,
			modal: true,
			width: 500,
			close: function(event, ui) { currentBox = null; currentDialog = null; currentButtonHolder = null; currentImage; }
		});

		setOpenButton( buttonHolder, dialog );
		setRemoveButton( buttonHolder, dialog );
		setSaveButton( dialog );

		setSwitch( dialog );
		enableMedia( dialog );
		enableYoutube( dialog );
		enableVimeo( dialog );
    });

	function setOpenButton( buttonHolder, popup ) {
		//.not( '.addnew' )
		$( buttonHolder ).on( "click", '.multimedia_box', function( evt ) {
			currentBox = this;
			currentButtonHolder = buttonHolder;
			currentDialog = popup;

			var currentIndex = $(this).index();
			var type = $('.multimedia_box_type', this).val();

			if( 'youtube' == type ) {
				$('.multimedia_box_youtubecode', popup).val( $('.multimedia_box_moviecode', this).val() );
				multimedia_checkYoutubeCode( popup );
			}
			else if( 'vimeo' == type ) {
				$('.multimedia_box_vimeocode', popup).val( $('.multimedia_box_moviecode', this).val() );
				multimedia_checkVimeoCode( popup );
			}

			$( popup ).dialog( "open" );
			$( popup ).parent().show();
			$(".ui-widget-overlay").show();
		});	
	}

	function setRemoveButton( buttonHolder, popup ) {
		$( buttonHolder ).on( "click", '.multimedia_box .remove', function( evt ) {
			evt.preventDefault();
			evt.stopPropagation();
	
			var box = $(this).closest(".multimedia_box");
	
			$(this).closest(".multimedia_box_selected").hide();
			$(this).closest(".multimedia_box").find('.multimedia_box_unselected').show();
			
			$('.multimedia_box_imageID', box).val('');
			$('.multimedia_box_image', box).val('');
			$('.multimedia_box_youtube', box).val('');
		});
	}

	function setSaveButton( dialog ) {
		$( '.multimedia_box_savebutton', dialog ).click(function( evt ) {
			evt.preventDefault();
			var currentIndex = $(currentBox).index();

			$('.multimedia_box_unselected', currentBox).hide();

			if( $('.multimedia_box_media', currentDialog).hasClass('selected') ) {
				checkMultimediaSelected(currentBox);

				$('.multimedia_box_image_src', currentBox).attr("src", currentImage);
				$('.multimedia_box_image_src', currentBox).attr("alt", "Media image");

				$('.multimedia_box_type', currentBox).val( 'media' );
				$('.multimedia_box_image', currentBox).val( currentImage );
				$('.multimedia_box_imageID', currentBox).val( imageID );
			}
			else if( $('.multimedia_box_youtube', currentDialog).hasClass('selected') && currentImage != null ) {
				checkMultimediaSelected(currentBox);

				$('.multimedia_box_image_src', currentBox).attr("src", currentImage);
				$('.multimedia_box_image_src', currentBox).attr("alt", "Youtube image");

				$('.multimedia_box_type', currentBox).val( 'youtube' );
				$('.multimedia_box_image', currentBox).val( currentImage );
				$('.multimedia_box_moviecode', currentBox).val( $('.multimedia_box_youtubecode', dialog).val() );
			}
			else if( $('.multimedia_box_vimeo', currentDialog).hasClass('selected') && currentImage != null ) {
				checkMultimediaSelected(currentBox);

				$('.multimedia_box_image_src', currentBox).attr("src", currentImage);
				$('.multimedia_box_image_src', currentBox).attr("alt", "Vimeo image");

				$('.multimedia_box_type', currentBox).val( 'vimeo' );
				$('.multimedia_box_image', currentBox).val( currentImage );
				$('.multimedia_box_moviecode', currentBox).val( $('.multimedia_box_vimeocode', dialog).val() );
			}
			else {
				$('.multimedia_box_unselected', currentBox).show();
			}

			$( dialog ).dialog( "close" );
		});
	}

	function checkMultimediaSelected( object ) {
		if( $('.multimedia_box_selected', object).length == 0 ) {
			$(object).append('<p class="multimedia_box_selected"><img class="multimedia_box_image_src" /><a href="#" class="remove">Remove</a></p>');
		}

		$('.multimedia_box_selected', object).show();
	}


	function setSwitch( popup ) {
		$( '.multimedia_box2', popup ).click(function( evt ) {
			$( '.multimedia_box2', popup ).removeClass('selected');
		});

		/* Media select */
		$( '.multimedia_box_media', popup ).click(function( evt ) {
			$( '.multimedia_box_media', popup ).addClass('selected');
		});

		$( '.multimedia_box_youtube', popup ).click(function( evt ) {
			$( '.multimedia_box_youtube', popup ).addClass('selected');
		});

		$( '.multimedia_box_vimeo', popup ).click(function( evt ) {
			$( '.multimedia_box_vimeo', popup ).addClass('selected');
		});
	}


	function enableMedia( popup ) {
		$( '.multimedia_box_mediabutton', popup ).click(function( evt ) {
			evt.preventDefault();

			tb_show('Select Image', 'wp-admin/media-upload.php?type=image&amp;TB_iframe=true');
	
			$( popup ).parent().hide();
			$(".ui-widget-overlay").hide();
		});
	}

	function enableYoutube( popup) {
		$('.multimedia_box_youtubecode', popup).change(function() {
			multimedia_checkYoutubeCode( popup );
		});
	}

	function enableVimeo( popup) {
		$('.multimedia_box_vimeocode', popup).change(function() {
			multimedia_checkVimeoCode( popup );
		});
	}



	//Helper functions
	function multimedia_checkYoutubeCode( popup ) {
		var youtubeCode = $('.multimedia_box_youtubecode', popup).val();
		currentImage = null;

		if(youtubeCode) {
			$.getJSON('http://gdata.youtube.com/feeds/api/videos/'+ youtubeCode +'?alt=json', function(data) {
				currentImage = data.entry.media$group.media$thumbnail[0].url;
				$('.multimedia_box_youtubecode', popup).css('backgroundColor', '#99FF99');
			}).error(function() {
				$('.multimedia_box_youtubecode', popup).css('backgroundColor', '#FF3333');
			});	
		}
		else {
			$('.multimedia_box_youtubecode', popup).css('backgroundColor', '#FFFFFF');
		}
	}

	function multimedia_checkVimeoCode( popup ) {
		var vimeoCode = $('.multimedia_box_vimeocode', popup).val();
		currentImage = null;

		$('.multimedia_box_vimeocode', popup).css('backgroundColor', '#FFFFFF');

		if(vimeoCode) {
			$.getJSON('http://vimeo.com/api/v2/video/'+ vimeoCode +'.json?callback=?', function(data) {
				currentImage = data[0].thumbnail_medium;
				$('.multimedia_box_vimeocode', popup).css('backgroundColor', '#99FF99');
			})
			.error(function( errormessage ) {
				$('.multimedia_box_vimeocode', popup).css('backgroundColor', '#FF3333');
			})
		}
		else {
			$('.multimedia_box_vimeocode', popup).css('backgroundColor', '#FFFFFF');
		}
	}


 };
})(jQuery);