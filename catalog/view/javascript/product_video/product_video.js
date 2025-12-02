if (typeof jQuery.magnificPopup === 'undefined') {
	const link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = 'catalog/view/javascript/jquery/magnific/magnific-popup.css';
	link.type = 'text/css';
	document.head.appendChild(link);

	const script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js';
	document.head.appendChild(script);
}

$(document).on('click', '.video-popup-icon', function(e){
	var videoLinks = $(this).data('videos');
	if (typeof videoLinks === 'string') {
		videoLinks = JSON.parse(videoLinks);
	}

	var items = [];
	$.each(videoLinks, function(index, link) {
		if (link.match(/\.(mp4|webm|ogg)$/)) {

			items.push({
				src: '<div class="video-wrapper"><video><source src="' + link + '" type="video/mp4"></video></div>',
				type: 'inline'
			});
		} else {
			items.push({
				src: 'https://www.youtube.com/watch?v=' + link,
				type: 'iframe'
			});
		}
	});

	$.magnificPopup.open({
		items: items,
		closeOnBgClick: true,
		gallery: {
			enabled: true
		},
		mainClass: 'mfp-product-video',
		type: 'image',
		callbacks: {
			open: function() {
				setTimeout(function () {
				$('.mfp-content').find('video').each(function() {
					$(this).attr('controls', 'controls');
				});
				}, 100);
			},
			change: function() {
				setTimeout(function () {
				$('.mfp-content').find('video').each(function() {
					$(this).attr('controls', 'controls');
				});
				}, 100);
			},
		}
	});
});
