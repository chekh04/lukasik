$(function () {
	function updateScrollProgress() {
		var scrollTop = $(window).scrollTop();
		var circumference = 289;
		var scrollTop = $(window).scrollTop();
		var documentHeight = $(document).height();
		var windowHeight = $(window).height();
		var maxScroll = documentHeight - windowHeight;

		var scrollPx = circumference * (1 - (scrollTop / maxScroll));

		$('#back-top').find('.btn-back-top').attr('style', '--scroll-progress: ' +  Math.round(scrollPx) + 'px;');
	}
	updateScrollProgress();
	$('#back-top .btn-back-top').click(function () {
		$('body,html').animate({
			scrollTop: 0
		}, 400);
		return false;
	});
	if ($(window).scrollTop() > 600) {
		$('#back-top').fadeIn(0);
		if(viewport().width > 991){
			if ($('.fixed-goods-bar').length) {
				$('.fixed-goods-bar').addClass('show-back-top');
			}
		}
		if(viewport().width <= 767){
			if ($('#fm-fixed-mobile-bottom.fm_type_design_bottom_3').length || $('#fm-fixed-mobile-bottom.fm_type_design_bottom_2').length) {
				$('#fm-fixed-mobile-bottom.fm_type_design_bottom_3, #fm-fixed-mobile-bottom.fm_type_design_bottom_2').addClass('show-back-top');
			}
		}
		if(viewport().width <= 991){
			if ($('#fm-fixed-mobile-bottom.fm_type_design_bottom_1').length) {
				$('#back-top').addClass('back-top-design-1');
			}
		}
	}
	$(window).scroll(function () {
		if ($(this).scrollTop() > 600) {
			$('#back-top').fadeIn();
			if(viewport().width > 991){
				if ($('.fixed-goods-bar').length) {
					$('.fixed-goods-bar').addClass('show-back-top');
				}
			}
			if(viewport().width <= 767){
				if ($('#fm-fixed-mobile-bottom.fm_type_design_bottom_3').length || $('#fm-fixed-mobile-bottom.fm_type_design_bottom_2').length) {
					$('#fm-fixed-mobile-bottom.fm_type_design_bottom_3, #fm-fixed-mobile-bottom.fm_type_design_bottom_2').addClass('show-back-top');
				}
			}
			if(viewport().width <= 991){
				if ($('#fm-fixed-mobile-bottom.fm_type_design_bottom_1').length) {
					$('#back-top').addClass('back-top-design-1');
				}
			}
		} else {
			$('#back-top').fadeOut();
			if(viewport().width > 991){
				if ($('.fixed-goods-bar').length) {
					$('.fixed-goods-bar').removeClass('show-back-top');
				}
			}
			if ($('#fm-fixed-mobile-bottom.fm_type_design_bottom_3').length || $('#fm-fixed-mobile-bottom.fm_type_design_bottom_2').length) {
				$('#fm-fixed-mobile-bottom.fm_type_design_bottom_3, #fm-fixed-mobile-bottom.fm_type_design_bottom_2').removeClass('show-back-top');
			}
		}
		updateScrollProgress();
	});
});

$('.promo-slider .swiper').each(function(index, element) {
	var setting = $(element).data();
		card_prev = $(element).closest('.promo-slider').find('.promo-slider__arrow_prev')[0],
		card_next = $(element).closest('.promo-slider').find('.promo-slider__arrow_next')[0],
		card_pagination = $(element).closest('.promo-slider').find('.promo-slider__pagination')[0],
		delay = setting.delay;
		autoplay_status = setting.autoplay;
		pagination_status = setting.pagination;
		navigation_status = setting.navigation;

	var sliderCard = new Swiper(element, {
		watchSlidesVisibility: true,
		watchSlidesProgress: true,
		watchOverflow: true,
		observer: true,
		observeParents: true,
		nested: false,
		slidesPerView: 1,
		effect: 'fade',
		parallax: true,
		loop: true,
		autoplay: {
			delay: delay,
			disableOnInteraction: false,
			pauseOnMouseEnter: true,
			enabled: autoplay_status
		},
		fadeEffect: {
			crossFade: true
		},
		navigation: {
			nextEl: card_next,
			prevEl: card_prev,
			enabled: navigation_status
		},
		pagination: {
			el: card_pagination,
			type: 'bullets',
			clickable: true,
			enabled: pagination_status,
		},
		speed:500,
	});
});

$(document).on('mouseenter','.pro_sticker_popover', function(e){
	var $element_popover = $(this);
	e.preventDefault();
	$('.pop_sticker').not(this).popover('destroy');
	if ($element_popover.closest('.product-thumb').length != 0) {
		$element_popover.popover({ placement: 'auto bottom', trigger: "manual" , container: 'body', html: true, animation:false});
	}
	if ($element_popover.closest('.general-image').length != 0) {
		$element_popover.popover({ placement: 'auto right', trigger: "manual" , container: 'body', html: true, animation:false});
	}
	if ($element_popover.closest('.right-block').length != 0) {
		$element_popover.popover({ placement: 'auto bottom', trigger: "manual" , container: 'body', html: true, animation:false});
	}
	$element_popover.popover('show');
	$element_popover.popover().data('bs.popover').tip().addClass('popover-sticker');
}).on('mouseleave','.popover.popover-sticker,.pro_sticker_popover', function(e){
	var $element_popover = $(this);
	setTimeout(function () {
		if (!$(".popover:hover").length) {
			$element_popover.popover("destroy");
		}
	}, 100);
});

$(document).on("mouseenter touchstart", ".ch-g-dots .ch-g-line", function() {
	var ch_sel_img = $(this).closest(".gallery-images").find("img").removeClass("active-image").eq($(this).index());
	if (ch_sel_img.attr('data-src')) {
		ch_sel_img.attr("src", ch_sel_img.attr("data-src")).removeAttr("data-src");
	}

	ch_sel_img.addClass("active-image").siblings().removeClass("active-image");
	$(this).closest(".gallery-images").find(".ch-g-dots .ch-g-line").eq($(this).index()).addClass("active-line").siblings().removeClass("active-line");
});

/* Agree to Terms */
$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			html  = '<div id="modal-agree" class="modal fade">';
			html += '  <div class="modal-dialog modal-xl chm-modal modal-dialog-centered">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header">';
			html += '        <div class="modal-title">' + $(element).text() + '</div>';
			html += '        <button type="button" class="close-modal" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');

			$(document).on('hide.bs.modal', '#modal-agree.modal.fade', function () {
				$('#modal-agree').remove();
			});
		}
	});
});
function removeViewed($url){
	$.ajax({
		url: $url,
		dataType : 'json',
		success : function(json) {
			if (json['success']) {
				$('.content_viewed').load('index.php?route=upstore/viewed_product/loadViewedProduct');
				$('.btn-viewed-pc .viewed-quantity').load('index.php?route=upstore/viewed_product/quantityViewedProduct', function(quantity_viewed) {
					if(quantity_viewed ==''){
						$(".btn-viewed-pc .viewed-quantity").html('0');
						$('.content_viewed').toggle().toggleClass('open-viewed');
					}
				});
			}
		}
	});
}

function loadViewedProduct() {

	$('body').addClass('no-scroll');
	$('html').append('<div class="up-bg-viewed active hidden-xs hidden-sm"></div>');
	$('.sidebar-viewed').removeClass('d-none');

	if ($('.sidebar-viewed').length === 0) {
		$('body').append('<div class="sidebar-viewed"></div>');
		setTimeout(function () {
			$('.sidebar-viewed').load('index.php?route=upstore/viewed_product/loadViewedProduct', function() {
				$('.sidebar-viewed').addClass('open-viewed');
			});
		}, 50);
	} else {
		setTimeout(function () {
			$('.sidebar-viewed').addClass('open-viewed');
		}, 20);
	}

	setTimeout(function () {
		const $sideMenu = $('.sidebar-viewed__content');
		const $sideMenuHeader = $('.sidebar-viewed__header');
		$sideMenu.on('scroll', function() {
			if ($sideMenu.scrollTop() > 0) {
				$sideMenuHeader.css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.06)');
				$sideMenuHeader.removeClass('no-shadow');
			} else {
				$sideMenuHeader.css('box-shadow', 'none');
				$sideMenuHeader.addClass('no-shadow');
			}
		});
	}, 400);
};

$(document).on('click', '.sidebar-viewed .btn-close-viewed, .up-bg-viewed', function () {
	$('.sidebar-viewed').removeClass('open-viewed');
	setTimeout(function () {
		$('.sidebar-viewed').addClass('d-none');
		$('.up-bg-viewed').remove();
		$('body').removeClass('no-scroll');
	}, 200);
});

function get_modal_callbacking() {
	$.ajax({
		type:'get',
		url:'index.php?route=extension/module/upstore_callback',
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			$('html body').append('<div id="modal-callback" class="modal fade" role="dialog">'+ data +'</div>');
			$('#modal-callback').modal('show');

			$(document).on('hide.bs.modal', '#modal-callback.modal.fade', function () {
				$('#modal-callback').remove();
			});
		}
	});
}

function upstoreNotifyPrice(product_id) {
	$.ajax({
		type:'get',
		url:'index.php?route=extension/module/upstore_notify_price&product_id='+ product_id,
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			$('html body').append('<div id="modal-notify-price" class="modal fade" role="dialog">'+ data +'</div>');
			$('#modal-notify-price').modal('show');

			$(document).on('hide.bs.modal', '#modal-notify-price.modal.fade', function () {
				$('#modal-notify-price').remove();
			});
		}
	});
}

function upstoreNotifyStock(product_id) {
	$.ajax({
		type:'get',
		url:'index.php?route=extension/module/upstore_notify_stock&product_id='+ product_id,
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			$('html body').append('<div id="modal-notify-stock" class="modal fade" role="dialog">'+ data +'</div>');
			$('#modal-notify-stock').modal('show');

			$(document).on('hide.bs.modal', '#modal-notify-stock.modal.fade', function () {
				$('#modal-notify-stock').remove();
			});
		}
	});
}

function fastorder_open(product_id) {
	$.ajax({
		type:'get',
		url:'index.php?route=extension/module/upstore_newfastorder&product_id='+product_id,
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			$('html body').append('<div id="modal-quickorder" class="modal fade" role="dialog">'+ data +'</div>');
			$('#modal-quickorder').modal('show');

			$(document).on('hide.bs.modal', '#modal-quickorder.modal.fade', function () {
				$('#modal-quickorder').remove();
			});
		}
	});
}

function fastorder_open_cart() {
	$.ajax({
		type:'get',
		url:'index.php?route=extension/module/upstore_newfastordercart',
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			$('html body').append('<div id="modal-quickorder" class="modal fade" role="dialog">'+ data +'</div>');
			$('#modal-quickorder').modal('show');

			$(document).on('hide.bs.modal', '#modal-quickorder.modal.fade', function () {
				$('#modal-quickorder').remove();
			});
		}
	});
}

function popupFormReviewStore() {
	$.ajax({
		type:'get',
		url:'index.php?route=product/upstore_reviews_store/popupFormReviewStore',
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			$('html body').append('<div id="modal-review-store" class="modal fade" role="dialog">'+ data +'</div>');
			$('#modal-review-store').modal('show');

			$(document).on('hide.bs.modal', '#modal-review-store.modal.fade', function () {
				$('#modal-review-store').remove();
			});
		}
	});
}

function quickview_open(id) {
	$.ajax({
		type:'post',
		data:'quickview29=1',
		url:'index.php?route=product/product&product_id='+id,
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			html  = '<div id="modal-quickview" class="modal fade" role="dialog">';
			html += '	<div class="modal-dialog chm-modal modal-dialog-centered modal-qv">';
			html += '		<div class="modal-content">'+ data +'</div>';
			html += '	</div>';
			html += '</div>';

			$('html body').append(html);
			$('#modal-quickview').modal('show');

			$(document).on('hide.bs.modal', '#modal-quickview.modal.fade', function () {
				$('#modal-quickview').remove();
			});
		}
	});
}

function banner_link_open(link) {
	$('#modal-desc-banner').remove();
	creatOverlayLoadPage(true);
	$.ajax({
		url: link,
		type: 'get',
		dataType: 'html',
		success: function(data) {
			creatOverlayLoadPage(false);
			var data = $(data);

			h1 = data.find('h1').text();

			description = data.find('#content .p-content').html();

			html  = '<div id="modal-desc-banner" class="modal fade" role="dialog">';
			html += '	<div class="modal-dialog chm-modal modal-dialog-centered">';
			html += '		<div class="modal-content">';
			html += '			<div class="modal-header">';
			html += '				<div class="modal-title">'+ h1 +'</div>';
			html += '				<button type="button" class="close-modal" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button>';
			html += '			</div>';
			html += '			<div class="modal-body">'+ description +'</div>';
			html += '		</div>';
			html += '	</div>';
			html += '</div>';

			$('html body').append(html);
			$('#modal-desc-banner').modal('show');

			$(document).on('hide.bs.modal', '#modal-desc-banner.modal.fade', function () {
				$('#modal-desc-banner').remove();
			});
		}
	});
}

$(document).on('click', '#login-popup, #login-popup-mob, .i_am_registered', function (e) {
	e.preventDefault();
	var href = $(this).attr('data-load-url');
	$.get(href, function(data) {
		$('<div id="login-form-popup" class="modal fade" role="dialog">' + data + '</div>').modal('show');
	});
});

$(document).on('hide.bs.modal', '#login-form-popup.modal.fade', function (e) {
	$('#login-form-popup').remove();
});

function handleFieldNotifications(selector, notifications) {
	var fields = [];

	$(selector).find('.form-control').each(function() {
		var name = $(this).attr('name');
		if (name) {
			fields.push(name);
		}
	});

	for (var key in notifications) {
		if (notifications.hasOwnProperty(key)) {
			var fieldName = key;
			var message = notifications[key];

			var $field = $(selector).find('[name="' + fieldName + '"]');
			var $formGroup = $field.closest('.form-group');

			if ($field.length > 0) {
				if (fieldName === 'agree') {
					if (message) {
						$field.parent().addClass('us-error-agree');
					}
				} else {
					if (message) {
						$field.addClass('error_input');
						$formGroup.append('<div class="us-text-error">' + message + '</div>');
						$formGroup.append('<div class="us-error-icon"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/form-icon/error-icon.svg"></div>');
						$field.removeClass('success_input');
					} else {
						$field.addClass('success_input');
						$formGroup.append('<div class="us-success-icon"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/form-icon/success-icon.svg"></div>');
						$field.removeClass('error_input');
					}
				}
			}
		}
	}

	fields.forEach(function(fieldName) {
		if (!notifications.hasOwnProperty(fieldName)) {
			var $field = $(selector).find('[name="' + fieldName + '"]');
			var $formGroup = $field.closest('.form-group');

			if ($field.length > 0 && $field.val() != '' && fieldName !== 'agree') {
				$field.addClass('success_input');
				$formGroup.append('<div class="us-success-icon"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/form-icon/success-icon.svg"></div>');
			}
		}
	});
}

function showModalWithMessage(message) {
	html  = '<div id="modal-success-message" class="modal fade">';
	html += '  <div class="modal-dialog">';
	html += '    <div class="modal-content ch-modal-success">';
	html += '      <div class="modal-body"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"> <div class="text-modal-block">' + message + '</div><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button></div>';
	html += '    </div>';
	html += '  </div>';
	html += '</div>';

	$('body').append(html);

	setTimeout(function () {
		$('#modal-success-message').modal('show');
	}, 700);

	$(document).on('hide.bs.modal', '#modal-success-message.modal.fade', function () {
		$('#modal-success-message').remove();
	});
}

(function($) {
	$.fn.autocompleteSerach = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keyup', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Show
			this.show = function() {
				var $this = $(this);
				var $searchAutocomplete = $this.siblings('div.search_autocomplete');
				var voice_search_width = 0;

				if ($this.closest('.search-top').find('.group_voice_search').length) {
					voice_search_width = $this.closest('.search-top').find('.group_voice_search').outerWidth();
				}

				if($this.closest('.search-top').length){
					var pos = $this.position();
					var left = pos.left + ($this.outerWidth() / 2) - ($searchAutocomplete.outerWidth() / 2) + (voice_search_width / 2);

					$searchAutocomplete.css({
						top: pos.top + $this.outerHeight(),
						left: left
					});
				}

				$searchAutocomplete.show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('div.search_autocomplete').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 300, this);
			}

			// Response
			this.response = function(json) {

				html = '';

				if (json['c'] && json['c'].length) {
					html += '<li class="search_categories">';
					html += '	<div class="search_categories_box">';
					html += '		<div class="search_categories_title">'+ json['text_categories'] +'</div>';
					html += '		<div class="search_category_items">';
					for (i = 0; i < json['c'].length; i++) {
					html += ' 			<a href="'+ json['c'][i].href +'"><span>'+ json['c'][i].name +'</span></a>';
					}
					html += '		</div>';
					html += '	</div>';
					html += '</li>';
				}

				if (json['p'] && json['p'].length) {
					for (i = 0; i < json['p'].length; i++) {
						this.items[json['p'][i]['value']] = json['p'][i];
					}

					for (i = 0; i < json['p'].length; i++) {

						if(json['p'][i].product_id != 0){
							html += '<li>';
							html += '<a href="'+ json['p'][i].href +'" class="autosearch_link">';
							html += '	<div class="ajaxadvance">';
							html += '		<div class="image">';
												if(json['p'][i].image){
							html += '		<img title="'+ json['p'][i].name +'" src="'+ json['p'][i].image +'"/>';
												}
							html += '	</div>';
							html += '	<div class="content">';
							html += '		<div class="search__left_block">';
							html += '			<div class="name">'+ json['p'][i].name +'</div>';
													if(json['p'][i].show_model){
							html += 	'			<div class="model">' + json['p'][i].model +'</div>';
													}
													if(json['p'][i].show_manufacturer){
							html += 	'			<div class="manufacturer">'+ json['p'][i].manufacturer +'</div>';
													}
													if(json['p'][i].display_stock_status){
							html += 	'			<div class="ch-stock-status"><span class="stock_status ' + (json['p'][i].quantity > 0 ? 'instock' : 'outofstock') + '">'+ json['p'][i].stock_status +'</span></div>';
													}
													if (json['p'][i].show_rating && json['p'][i].rating > 0) {
							html += '			<div class="ratings"> ';
														for (var k = 1; k <= 5; k++) {
														if (json['p'][i].rating < k) {
							html +='					<span class="product-rating-star"><svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 8.75L3.47287 9.81573C2.73924 10.2014 1.88181 9.57846 2.02192 8.76155L2.40907 6.50431L0.769082 4.90572C0.175565 4.32718 0.503075 3.31921 1.3233 3.20002L3.5897 2.87069L4.60326 0.816985C4.97008 0.0737394 6.02992 0.0737402 6.39674 0.816986L7.4103 2.87069L9.67671 3.20002C10.4969 3.31921 10.8244 4.32718 10.2309 4.90572L8.59093 6.50431L8.97808 8.76155C9.11819 9.57846 8.26076 10.2014 7.52713 9.81573L5.5 8.75Z" fill="#EFEFEF"/></svg></span>';
														} else {
							html +='					<span class="product-rating-star"><svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 8.75L3.47287 9.81573C2.73924 10.2014 1.88181 9.57846 2.02192 8.76155L2.40907 6.50431L0.769082 4.90572C0.175565 4.32718 0.503075 3.31921 1.3233 3.20002L3.5897 2.87069L4.60326 0.816985C4.97008 0.0737394 6.02992 0.0737402 6.39674 0.816986L7.4103 2.87069L9.67671 3.20002C10.4969 3.31921 10.8244 4.32718 10.2309 4.90572L8.59093 6.50431L8.97808 8.76155C9.11819 9.57846 8.26076 10.2014 7.52713 9.81573L5.5 8.75Z" fill="#E5DB77"/></svg></span>';
														}
														}
							html += '			</div>';
													}
							html += '		</div>';
							html += '		<div class="search__right_block">';
													if(json['p'][i].show_price){
							html += 	'			<div class="price">';
														if (!json['p'][i].special) {
							html += 					json['p'][i].price;
														} else {
							html += '				<span class="price-old">'+ json['p'][i].price +'</span> <span class="price-new">'+ json['p'][i].special +'</span>';
														}
							html += '			</div>';
													}
							html += '		</div>';
							html +='		</div>';
							html += '	</div>'
							html += '</a>'

							if(json['p'][i].product_details){
							/*Product fix Right*/
							html += '<div class="dropdown_search_item_product container-module d-none">';
							//html += '	<div class="product__item">';
							html += '		<div class="product-thumb dflex flex-column">';
							html += ' 			<div class="image">';
							html += '			<div class="stickers-ns">';
														if (json['p'][i].on_off_sticker_special == 1 && json['p'][i].special) {
							html += '					<div class="sticker-ns special">'+ json['p'][i].text_sticker_special +'</div>';
														}
														if (json['p'][i].on_off_percent_discount == 1 && json['p'][i].special) {
							html += '					<span class="sticker-ns special">'+ json['p'][i].skidka+' %</span>';
														}
														if (json['p'][i].on_off_sticker_topbestseller == 1 && (json['p'][i].top_bestsellers >= json['p'][i].limit_bestseller)) {
							html += '					<div class="sticker-ns bestseller">'+ json['p'][i].text_sticker_bestseller +'</div>';
														}
														if (json['p'][i].on_off_sticker_popular == 1 && (json['p'][i].viewed >= json['p'][i].limit_popular)) {
							html += '					<div class="sticker-ns popular">'+ json['p'][i].text_sticker_popular +'</div>';
														}
														if ((json['p'][i].on_off_sticker_newproduct == 1) && json['p'][i].sticker_new_prod ) {
							html += '					<div class="sticker-ns newproduct">'+ json['p'][i].text_sticker_newproduct +'</div>';
														}
							html += '			</div>';

							html += '				<a ' + (json['p'][i].image_hm ? 'class="gallery-images"' : '') + ' href="'+ json['p'][i].href +'">';
							html += '					<img class="img-responsive' + (json['p'][i].image_hm && json['p'][i].image_hm.length > 0 ? ' ch-g-image active-image' : '') + '" ' + (json['p'][i].image_h ? 'data-additional-hover="'+ json['p'][i].image_h +'"' : '') + ' decoding="async" width="240" height="240" loading="lazy" src="'+ json['p'][i].thumb +'" alt="'+ json['p'][i].name +'" />';
															if(json['p'][i].image_hm && json['p'][i].image_hm.length > 0){
							html += '						<div class="ch-g-items">';
																var line = '';
																for (var j = 0; j < json['p'][i].image_hm.length; j++) {
																	var dopImage = json['p'][i].image_hm[j];
							html += '							<img class="ch-g-image img-responsive" decoding="async" width="240" height="240" loading="lazy" data-src="' + dopImage + '" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="' + json['p'][i].name + '" />';
																	line += '<div class="ch-g-line"></div>';
																}
							html += '						</div>';
							html += '						<div class="ch-g-dots">';
							html += '							<div class="ch-g-line active-line"></div>';
							html += 								line;
							html += '						</div>';
															}
							html += '				</a>';
														if (json['p'][i].show_special_timer_module == 1 && json['p'][i].special ) {
							html += '				<div class="action-timer" data-date-end="'+ json['p'][i].date_end +'"></div>';
														}
							html += '				<div class="addit-action">';
							html += '					<div class="quickview"><button aria-label="Quickview" class="btn btn-quickview" title="'+ json['p'][i].text_quickview +'" onclick="quickview_open('+ json['p'][i].product_id +');"><i class="up-icon-quickview" aria-hidden="true"></i></button></div>';
															if (json['p'][i].setting_module.status_fastorder && json['p'][i].setting_module.status_fastorder == 1 && json['p'][i].show_fastorder && json['p'][i].show_buy_button) {
							html += '						<div class="quick-order">';
							html += '							<button aria-label="Fastorder" class="btn btn-fastorder" title="'+ json['p'][i].text_fastorder +'" type="button" '+ json['p'][i].disabled_fastorder +' onclick="fastorder_open('+ json['p'][i].product_id +');"><i class="up-icon-fastorder" aria-hidden="true"></i></button>';
							html += '						</div>';
															}
															if (json['p'][i].setting_module.status_compare && json['p'][i].setting_module.status_compare == 1) {
							html += '						<div class="compare"><button aria-label="Compare" class="btn btn-compare" type="button" title="'+ json['p'][i].button_compare +'" onclick="compare.add('+ json['p'][i].product_id +');"><i class="up-icon-compare" aria-hidden="true"></i></button></div>';
															}
															if (json['p'][i].setting_module.status_wishlist && json['p'][i].setting_module.status_wishlist == 1) {
							html += '						<div class="wishlist"><button aria-label="Wishlist" class="btn btn-wishlist" type="button" title="'+ json['p'][i].button_wishlist +'" onclick="wishlist.add('+ json['p'][i].product_id +');"><i class="up-icon-wishlist" aria-hidden="true"></i></button></div>';
															}
							html += '				</div>';

							html += '			</div>';
							html += '			<div class="caption dflex flex-column flex-grow-1">';
							html += '				<div class="product-name"><a href="'+ json['p'][i].href +'">'+ json['p'][i].name +'</a></div>';
														if (json['p'][i].setting_module && json['p'][i].setting_module.status_rating == 1) {
							html += '					<div class="rating mb-10 d-flex align-items-center">';
							html += '						<div class="rating-stars d-flex align-items-center">';
																$.each(json['p'][i].rating_stars, function(index, star_width) {
							html += '							<div class="rating-star up-icon">';
							html += '								<div class="rating-star-active up-icon" style="width:' + star_width + '%"></div>';
							html += '							</div>';
																});
							html += '						</div>';
							html += '						<div class="product-reviews d-flex align-items-center">';
							html += '							<i class="up-icon-18 up-icon-message" aria-hidden="true"></i>';
																	if (json['p'][i].setting_module && json['p'][i].setting_module.status_quantity_reviews == 1 && json['p'][i].reviews > 0) {
							html += '							<span class="total-reviews d-flex align-items-center justify-content-center">'+ json['p'][i].reviews +'</span>';
																	}
							html += '						</div>';
							html += '					</div>';
														}
							html += '				<div class="mb-10 justify-content-between dflex">';
							html += '					<div class="product-model">'+ json['p'][i].text_model +''+ json['p'][i].model +'</div>';
															if (json['p'][i].show_stock_status) {
																if (json['p'][i].quantity <= 0) {
							html += '						<div class="stock-status outofstock">'+ json['p'][i].stock_status +'</div>';
																} else {
							html += '						<div class="stock-status up-icon instock">'+ json['p'][i].text_instock +'</div>';
																}
															}
							html += '				</div>';
							html += '					<div class="price-actions-box dflex flex-wrap mt-auto">';
																if(json['p'][i].price){
																	if (json['p'][i].settings_upstore.quantity_btn_module && json['p'][i].settings_upstore.quantity_btn_module == 1 && json['p'][i].setting_module.status_actions && json['p'][i].show_buy_button) {
							html += '								<div class="quantity_plus_minus">';
							html += '									<span class="add-up add-action">';
							html += '										<svg xmlns="http://www.w3.org/2000/svg" width="7" height="5" fill="none" viewBox="0 0 7 5">';
							html += '											<path fill="#000" fill-rule="evenodd" d="M3.826 2.144a.5.5 0 00-.707.004L.856 4.438a.5.5 0 01-.712-.704l2.264-2.289a1.5 1.5 0 012.121-.012L6.852 3.73a.5.5 0 11-.704.711L3.826 2.144z" clip-rule="evenodd"/>';
							html += '										</svg>';
							html += '									</span>';
							html += '									<input type="text" class="quantity-num form-control" name="quantity" value="'+ json['p'][i].minimum +'" data-minimum="'+ json['p'][i].minimum +'" ' + (json['p'][i].settings_upstore.quantity_multiple === 1 && json['p'][i].minimum > 1 ? 'disabled' : '') + '>';
							html += '									<span class="add-down add-action">';
							html += '										<svg xmlns="http://www.w3.org/2000/svg" width="7" height="5" fill="none" viewBox="0 0 7 5">';
							html += '											<path fill="#000" fill-rule="evenodd" d="M3.174 2.856a.5.5 0 00.707-.004L6.144.562a.5.5 0 01.712.704L4.592 3.555a1.5 1.5 0 01-2.121.012L.148 1.27A.5.5 0 11.852.559l2.322 2.297z" clip-rule="evenodd"/>';
							html += '										</svg>';
							html += '									</span>';
							html += '								</div>';
																	}
							html += '							<div class="price ' + (json['p'][i].setting_module.status_actions ? 'mb-0' : '') + '" data-price-value="'+ json['p'][i].price_value+'" data-special-value="' + json['p'][i].special_value + '">';
																		if (!json['p'][i].special) {
							html += '									<span class="price_value">'+ json['p'][i].price +'</span>';
																		} else {
							html += '									<span class="price-old"><span class="price_value">'+ json['p'][i].price +'</span></span>';
							html += '									<span class="price-new"><span class="special_value">'+ json['p'][i].special +'</span></span>';
																		}
																		if(json['p'][i].tax){
							html += '									<span class="price-tax">'+ json['p'][i].text_tax + json['p'][i].tax +'</span>';
																		}
							html += '							</div>';
																}
							html += '						<div class="cart">';
																	if(json['p'][i].show_buy_button){
							html += '							<button aria-label="Add to cart" class="btn btn-general squircle' + (json['p'][i].in_cart ? ' is-active' : '') + '" type="button" ' + (json['p'][i].quantity <= 0 && json['p'][i].disable_cart_button ? 'disabled' : 'onclick="cart.add(' + json['p'][i].product_id + ',this)"') + '><i class="up-icon-22 up-icon-cart" aria-hidden="true"></i></button>';
																	} else {
							html += '							<button aria-label="notify stock" class="btn btn-general squircle' + (json['p'][i].in_waitlist ? ' is-active' : '') + '" type="button" onclick="upstoreNotifyStock('+ json['p'][i].product_id +');"><i class="up-icon-22 up-icon-notify" aria-hidden="true"></i></button>';
																	}
							html += '						</div>';
							html += '					</div>';
							html += '			</div>';
							html += '		</div>';
							//html += '	</div>';
							html += '</div>';
							/*END Product fix Right*/
							}

							html += '</li>'
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('div.search_autocomplete').find('ul.autosearch').html(html);
			}



			if(!$(this).next().hasClass('search_autocomplete')){
				$(this).after('<div class="search_autocomplete"><div class="autocomplete-wrapper"><ul class="list-unstyled autosearch"></ul></div></div>');
			}

			setTimeout(function () {
				$('.mobile-sidebar-search .autocomplete-wrapper').on('scroll', function() {
					if ($('.mobile-sidebar-search .autocomplete-wrapper').scrollTop() > 0) {
						$('.mobile-sidebar-search__content').addClass('active-shadow');
					} else {
						$('.mobile-sidebar-search__content').removeClass('active-shadow');
					}
				});
			}, 200);

			$(this).siblings('div.search_autocomplete').find('ul.autosearch').on('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);

$(document).on('mouseenter', '.header-search .autosearch li:not(.search_categories)', function(e) {
	if (matchMedia('only screen and (min-width: 1200px)').matches ) {
		$('.header-search .autosearch li').removeClass('is-active');
		var $currentLi = $(this);
		$currentLi.find('.dropdown_search_item_product').removeClass('d-none');
		setTimeout(function () {
			$currentLi.addClass('is-active');
			$currentLi.find('.dropdown_search_item_product').removeClass('d-none');
		}, 20);
		changeAddToCartBtn();
		changeWishlistBtn();
		changeCompareBtn();
	}
}).on('mouseleave', '.header-search .autosearch li', function(e) {
	if (matchMedia('only screen and (min-width: 1200px)').matches ) {
		var $currentLi = $(this);
		$('.header-search .autosearch li').removeClass('is-active');
		setTimeout(function () {
			$currentLi.find('.dropdown_search_item_product').addClass('d-none');
		}, 20);
	}
});



$(document).on('click', '.livesearch input[name="search"]', function () {
	$(this).closest('.header-search').find('input[name="search"]').autocompleteSerach({source:getAjaxLiveSearch});
});

function getAjaxLiveSearch(request, response){
	$.ajax({
		url: 'index.php?route=extension/module/upstore_autosearch/ajaxLiveSearch&filter_name=' +  encodeURIComponent(request),
		dataType : 'json',
		success : function(json) {
			// response($.map(json, function(item) {
			// 	return item;
			// }));
			response(json);

			setTimeout(function () {
				addTimer();
			}, 100);
		}
	});
}

$(function() {
	var recognizing = false
	var timeout;
	var recognition;
	var parentElement;

	if (!('webkitSpeechRecognition' in window)) {
		$('.group_voice_search').addClass('d-none');
	} else {
		$('.group_voice_search').removeClass('d-none');

		function getCookie(name) {
			let matches = document.cookie.match(new RegExp(
				"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
				));
			return matches ? decodeURIComponent(matches[1]) : undefined;
		}

		let recognition_lang = getCookie('language');

		if (recognition_lang) {
			let lang_parts = recognition_lang.split('-');
			if (lang_parts.length === 2) {
				recognition_lang = lang_parts[0].toLowerCase() + '-' + lang_parts[1].toUpperCase();
			}
		} else {
			recognition_lang = 'en-GB';
		}

		var search_start = false;
		var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
		var searchInput = $(".header-search input[name='search']");
		var searchButton = $(".header-search button.btn-search");

		recognition = new SpeechRecognition();
		recognition.interimResults = true;
		recognition.continuous = true;
		recognition.lang = recognition_lang;

		recognition.onstart = function() {
			recognizing = true;
			startTimeout();
		};

		recognition.onerror = function(event) {
			if (event.error == 'no-speech') {
				console.log('no-speech');
			}
			if (event.error == 'audio-capture') {
				console.log('audio-capture');
			}
			console.log(event);
			if (event.error == 'not-allowed') {
				console.log("Произошла ошибка при получении доступа к микрофону: ", event.error);
			}
		};

		recognition.onend = function() {
			recognizing = false;
			clearTimeout(timeout);
		};

		recognition.onresult = function(event) {

			search_start = false;
			var interim_transcript = '';
			var final_transcript = '';

			for (var i = event.resultIndex; i < event.results.length; ++i) {
				if (event.results[i].isFinal) {
					final_transcript += event.results[i][0].transcript + ' ';
				} else {
					interim_transcript += event.results[i][0].transcript;
				}
			}

			final_transcript = final_transcript.trim();
			interim_transcript = interim_transcript.trim();

			if (final_transcript) {
				parentElement.find("input[name='search']").val(final_transcript);
			} else {
				parentElement.find("input[name='search']").val(interim_transcript);
			}

			if (event.results[event.resultIndex].isFinal) {
				search_start = true;
				setTimeout(function() {
					if (search_start) {
						var inputField = parentElement.find("input[name='search']");
						inputField.autocompleteSerach({source:getAjaxLiveSearch});
						inputField.trigger('focus');
					}
				}, 200);
				resetTimeout();
			}

		};

		recognition.onaudiostart = function() {
			$('.btn-voice-search').addClass("active-speak");
			addDots();
		};

		recognition.onaudioend = function() {
			$('.btn-voice-search').removeClass("active-speak");
			$('.search-voice__dots').each(function() {
				$(this).remove();
			});
		};

		$(document).on('click', '.btn-voice-search', function(){
			parentElement = $(this).closest('.header-search');
			startRecognition();
		});

		function addDots() {
			dots = '<div class="search-voice__dots"><span class="search-voice__dots-item search-voice__dots-item_color_blue"></span><span class="search-voice__dots-item search-voice__dots-item_color_red"></span><span class="search-voice__dots-item search-voice__dots-item_color_orange"></span><span class="search-voice__dots-item search-voice__dots-item_color_green"></span></div>';
			$('.btn-voice-search:visible').append(dots);
		}

		function startRecognition() {
			if (recognizing) {
				recognition.stop();
				return;
			}
			searchInput.val('');
			recognition.start();
		}

		function startTimeout() {
			timeout = setTimeout(function() {
				if (recognizing) {
					recognition.stop();
				}
			}, 30000);
		}

		function resetTimeout() {
			clearTimeout(timeout);
			startTimeout();
		}
	}
});

$(document).on('click', '.product-thumb .add-up.add-action', function () {
	var $input = $(this).closest('.quantity_plus_minus').find('.quantity-num');
	if($input.prop('disabled') != true){
		var data_min_val = 1;
	} else {
		var data_min_val = parseInt($input.data('minimum'));
	}
	var count = parseInt($input.val()) + data_min_val;
	$input.val(count);
	$input.change();
});

$(document).on('click', '.product-thumb .add-down.add-action', function () {
	var $input = $(this).closest('.quantity_plus_minus').find('.quantity-num');
	if($input.prop('disabled') != true){
		var data_min_val = 1;
	} else {
		var data_min_val = parseInt($input.data('minimum'));
	}
	var count = parseInt($input.val()) - data_min_val;
	count = count < parseInt($input.data('minimum')) ? parseInt($input.data('minimum')) : count;
	$input.val(count);
	$input.change();
});

$(document).on('change', '.product-thumb .quantity-num, .product-thumb .options input[type="checkbox"], .product-thumb .options input[type="radio"], .product-thumb .options select', function() {
	recalcQuantity(this);
});

$(document).on('input', '.product-thumb .quantity-num', function () {
	validateQuantity(this);
});

function validateQuantity(elem){
	input = $(elem);
	var minimum = input.data('minimum');
	var count = $(elem).val().replace(/[^\d]/g, '');
	if (count == '') count = minimum;
	if (count == '0') count = minimum;
	input.val(count);
	input.change();
}

function price_format(n){
	c = chSetting.currency_autocalc.decimals != 0 ? chSetting.currency_autocalc.decimals : '';
	d = chSetting.currency_autocalc.decimal_point;
	t = chSetting.currency_autocalc.thousand_point;
	s_left = chSetting.currency_autocalc.symbol_left;
	s_right = chSetting.currency_autocalc.symbol_right;
	//n = n * chSetting.currency_autocalc.value;
	i = parseInt(n = Math.abs(n).toFixed(c)) + '';
	j = ((j = i.length) > 3) ? j % 3 : 0;
	return s_left + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '') + s_right;
}

function recalcQuantity(element, autocalc = true) {

	elem = $(element).closest('.product-thumb');

	if(elem.length) {
		var quantity = (typeof(elem.find('.quantity-num').val()) != 'undefined' ? elem.find('.quantity-num').val() : 1);
		var minval = elem.find('.quantity-num').data('minimum');
		quantity = quantity.replace(/[^\d]/g, '');
		if (quantity == '') quantity = minval;
		if (quantity == '0') quantity = minval;

		var main_price = Number(elem.find('.price').data('price-value')),
			 special = elem.find('.price').data('special-value');

		if(special !=''){
			var special_price = Number(special);
		} else {
			var special_price = false;
		}
		var options_price = 0;
		elem.find('input:checked, option:selected').each(function() {
			if ($(this).data('option-prefix') == '=') {
				options_price += Number($(this).data('option-price'));
				main_price = 0;
				special_price = 0;
			}
			if ($(this).data('option-prefix') == '+') {
				options_price += Number($(this).data('option-price'));
			}
			if ($(this).data('option-prefix') == '-') {
				options_price -= Number($(this).data('option-price'));
			}

			if ($(this).data('option-prefix') == '*') {
				 options_price *= Number($(this).data('option-price'));
				 main_price *= Number($(this).data('option-price'));
				 special_price *= Number($(this).data('option-price'));
			}

		});

		main_price += options_price;

		special_price += options_price;

		if(special !=''){
			special_coefficient = parseFloat(elem.find('.price').data('price-value'))/parseFloat(elem.find('.price').data('special-value'));
			main_price = special_price * special_coefficient;
			special_price *= quantity;
		}

		main_price *= quantity;


		if(autocalc){
			if(elem.find('.price_value').length === 1){
				var start_price = parseFloat(elem.find('.price_value').html().replace(/[^\d\.\,]/g, ''));

				$({val:start_price}).animate({val:main_price}, {
					duration: 400,
					step: function(val) {
						elem.find('.price_value').html(price_format(val));
					}
				});

				if(special !=''){
					var start_price = parseFloat(elem.find('.special_value').html().replace(/[^\d\.\,]/g, ''));
					$({val:start_price}).animate({val:special_price}, {
						duration: 400,
						step: function(val) {
							elem.find('.special_value').html(price_format(val));
						}
					});
				}
			}
		} else {
			if(elem.find('.price_value').length === 1){
				elem.find('.price_value').html(price_format(main_price));
			}
			if(elem.find('.special_value').length === 1){
				elem.find('.special_value').html(price_format(special_price));
			}
		}
	}
}

$(function () {
	if(chSetting.settings_upstore.price_recalc != 0){
		$('.quantity_plus_minus > .quantity-num').each(function () {
			recalcQuantity(this, false);
		});
	}
});