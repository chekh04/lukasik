document.addEventListener('DOMContentLoaded', function() {
	$(function(){
		if ($('.showmore_on .pagination li.active').next('li').length > 0) {
			$('.pagination').before('<div id="showmore" class="box-showmore"><button onclick="showmore()" class="chm-btn chm-btn-grey chm-px-lg chm-lg chm-lg-rounded" type="button"><svg class="chm-icon-showmore" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6m5.84 7.57a10 10 0 1 1-.57-8.38"/></svg><span class="chm-btn-text">'+ chSetting.text_showmore +'</span></button></div>');
		}
	});
});

function showmore() {
	var $next = $('.showmore_on .pagination li.active').next('li');
	$('#showmore .chm-btn').addClass('active-load');
	if ($next.length == 0) { return; }

    $.get($next.find('a').attr('href'), function (data) {

        	$data = $(data);
        	var $container = $('.category-page');
			var $products = $data.find('.category-page > div');
			var $product_img = $products.find('a > img');

			$product_img.each(function () {
				if ($(this).attr('data-additional-hover')) {
					var img_src = $(this).attr('data-additional-hover');
					$(this).addClass('main-img');
					$(this).after('<img src="'+img_src+'" class="additional-img-hover img-responsive" title="'+$(this).attr('alt')+'" />');
				}
			});

			$container.append($products);
			displayView.init();

			$('#showmore .chm-btn').removeClass('active-load');
			$('.col-sm-12.text-right').html($data.find('.col-sm-12.text-right'));
			$('.pagination').html($data.find('.pagination > *'));
			if ($('.showmore_on .pagination li.active').next('li').length == 0) {
				$('#showmore').hide();
			}
    }, "html");

    setTimeout(function () {
    	if (typeof addTimer == 'function') {
    		addTimer();
    	}
    	if (typeof loadEditorplus == 'function') {
			loadEditorplus();
		}
    }, 1000);

    return false;
}