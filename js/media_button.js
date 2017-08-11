jQuery(function ($) {

    $(document).ready(function () {
		$('#insert-sws-media').click(open_media_window);
	});

    function open_media_window() {
		 wp.media({
			 frame: 'post',
			 state: 'insert',
			 multiple: false
		}).open();
		 $(".media-menu-item:contains(SWS Infographics)").trigger('click');
	}
});