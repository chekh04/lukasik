function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

function setTheme(theme) {
	document.cookie = "theme=" + theme + "; path=/; max-age=31536000";

	$('html').removeClass('light-theme dark-theme').addClass(theme);

	$('.up-theme-btn').removeClass('active');
	if (theme === 'light-theme') {
		$('.light-theme-btn').addClass('active');
	} else {
		$('.dark-theme-btn').addClass('active');
	}

	$.ajax({
        url: 'index.php?route=upstore/theme/updateLogo',
        type: 'post',
        data: { theme: theme },
        dataType: 'json',
        success: function (json) {
            if (json.logo) {
                $('.up-header__logo-desktop img').attr('src', json.logo);
            }
            if (json.fm_logo) {
                $('.up-header__logo-mobile img').attr('src', json.fm_logo);
            }
        },
        error: function () {
            console.error('Error updating logo');
        }
    });
}

function viewport() {
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
}

$(document).on('click', '.header-cart-backdrop,.header-cart-close', function () {
	$('body').removeClass('no-scroll');
	$('.shopping-cart').removeClass('cart-is-open');
	setTimeout(function () {
		$('.cart-content').addClass('d-none');
	}, 100);
});

function stickyBtnCart() {
	var hcs = $('.header-cart-scroll'),
	checkScroll = function(elem) {
		return $(elem).prop('scrollHeight') - $(elem).innerHeight() - 55;
	};
	$('body').removeClass('cart-is-sticky');
	if (checkScroll(hcs) > 0) {
		$('body').addClass('cart-is-sticky');
	}

	hcs.on('scroll', function(){
		var st = $(this).scrollTop();
		if ( $(this).scrollTop() >= checkScroll($(this))) {
			$('body').removeClass('cart-is-sticky');
		} else {
			$('body').addClass('cart-is-sticky');
		}
	});
}

function openFixedCart(selector){
	var elem = $(selector).filter(':visible').first();

	$('.box-account, #language .btn-group, #currency .btn-group').removeClass('open');
	$('body').addClass('no-scroll');
	$('.cart-content').removeClass('d-none');
	setTimeout(function () {
		if($(elem).parent().find('.header-cart-fix-right').length == 0){
			$('.cart-content').load('index.php?route=common/cart/info .cart-content > *', function() {
				setTimeout(function () {
					$(elem).parent().addClass('cart-is-open');
					stickyBtnCart();
				}, 10);
			});
		} else {
			$(elem).parent().addClass('cart-is-open');
			stickyBtnCart();
		}
	}, 20);
}

function ch_cart_minus(elem){
	var $input = $(elem).filter(':visible');
	var count = parseInt($input.val()) - parseInt($input.attr('data-minimum'));
	new_count = count < parseInt($input.attr('data-minimum')) ? parseInt($input.attr('data-minimum')) : count;
	if(count >= new_count){
		$input.val(count).change();
	}
}

function ch_cart_plus(elem){
	var $input = $(elem).filter(':visible');
	var count = parseInt($input.val()) + parseInt($input.attr('data-minimum'));
	$input.val(count).change();
};

function updateQuantityCart(key,quantity){
	$.ajax({
		url: 'index.php?route=checkout/cart/editOne',
		type: 'post',
		data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
		dataType: 'json',
		success: function(json) {
			setTimeout(function () {
				$('.cart-total').html(json['total']);
				$('.shopping-cart .cart-content').load('index.php?route=common/cart/info .cart-content > *');
			}, 100);

			if (getURLVar('route') == 'checkout/cart' || location.pathname == '/cart/' || location.pathname == '/cart') {
				$.get('index.php?route=checkout/cart',function(data){
					$("h1").html($(data).find("h1").html());
					$(".cart-col-left").html($(data).find(".cart-col-left").html());
					$(".cart-col-right .table-cart").html($(data).find(".cart-col-right .table-cart").html());
				});
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
};

function swiperModule(selector, row_items = false){

	var bpoint = 2,
		bpoint_2 = 2,
		bpoint_3 = 3;

	if(row_items == 1){
		bpoint = 1;
		bpoint_2 = 1;
		bpoint_3 = 2;
	}
	if (viewport().width > 768) {
		bpoint = 1;
	}
	if (viewport().width > 1200) {
		bpoint_3 = 2;
	}

	var nx = $(selector).closest('.container-module').find('.next-prod')[0];
   var pr = $(selector).closest('.container-module').find('.prev-prod')[0];
   var sb = $(selector).find('.swiper-scrollbar')[0];
   var navigation = $(selector).closest('.container-module').find('.swiper-mod-navigation')[0];

	new Swiper(selector, {
		watchSlidesVisibility: true,
		watchSlidesProgress: true,
		watchOverflow: true,
		observer: true,
		observeParents: true,
		slidesPerView: 1,
		nested: true,
		speed: 400,
		breakpointsBase: 'container',
		grabCursor: true,
		scrollbar: {
		  el: sb,
		  draggable: true,
		},
		navigation: {
			nextEl: nx,
			prevEl: pr,
		},
		on: {
			afterInit: function () {
				setTimeout(function () {
					$(selector).addClass('swiper-visible');
				}, 500);
				updateNavigation();
			},
			resize: function () {
				setTimeout(function () {
					updateNavigation();
				}, 300);
			}
		},
		breakpoints: {
			350 : {
				slidesPerView: bpoint,
			},
			500 : {
				slidesPerView: bpoint_2,
			},
			710: {
				slidesPerView: bpoint_3,
			},
			992: {
				slidesPerView: 4,
			},
			1220: {
				slidesPerView: 5,
			}
		}
	});

	function updateNavigation() {
		var buttons = $(selector).closest('.container-module').find('.swiper-mod-navigation .swiper-button-lock');
		if (buttons.length === 2) {
			$(navigation).addClass('disabled-navigation');
		} else {
			$(navigation).removeClass('disabled-navigation');
		}
	}
}

function loading_masked(action) {
	if (action) {
		$('.loading_masked').html(chSetting.loading_masked_img);
		$('.loading_masked').show();
	} else {
		$('.loading_masked').html('');
		$('.loading_masked').hide();
	}
}

function creatOverlayLoadPage(action) {
	if (action) {
		$('body').prepend('<div id="messageLoadPage"></div>');
		$('#messageLoadPage').html(chSetting.loading_masked_img);
		$('#messageLoadPage').show();
	} else {
		$('#messageLoadPage').html('');
		$('#messageLoadPage').hide();
		$('#messageLoadPage').remove();
	}
}

function getShare(){
	if(!$('#modal-share').length){

		html  = '<div id="modal-share" class="modal fade">';
		html += '  <div class="modal-dialog chm-modal sm-modal-4 modal-dialog-centered">';
		html += '    <div class="modal-content">';
		html += '    <div class="modal-header">';
		html += '      <div class="modal-title">'+ chSetting.text_share +'</div><button type="button" class="close-modal" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button>';
		html += '    </div>';
		html += '      <div class="modal-body">';
		html += '			<div class="a2a_kit a2a_kit_size_32 a2a_default_style">';
		html += '				<a class="a2a_button_telegram"></a>';
		html += '				<a class="a2a_button_facebook"></a>';
		html += '				<a class="a2a_button_twitter"></a>';
		html += '				<a class="a2a_button_whatsapp"></a>';
		html += '				<a class="a2a_button_facebook_messenger"></a>';
		html += '				<a class="a2a_button_viber"></a>';
		html += '				<a class="a2a_button_skype"></a>';
		html += '				<a class="a2a_button_google_gmail"></a>';
		html += '			</div><script src="https://static.addtoany.com/menu/page.js"></script>';
		html += '    </div>';
		html += '  </div>';
		html += '</div>';

		$('body').append(html);
	}
	$('#modal-share').modal('show');
}

function changeAddToCartBtn(){
	var in_cart_pids = $('#cart .header-cart-fix-right').attr('data-pids');
	if(typeof(in_cart_pids) === 'undefined') return;
	in_cart_pids = String(in_cart_pids).split(',').map(Number);
	$('.btn-general:not(.is-active)').each(function(){
		if($(this).attr('onclick')){
			var pid = $(this).attr('onclick').replace(/\D+/g,'');
			if(in_cart_pids.indexOf(Number(pid)) !== -1){
				$(this).addClass('is-active').find('.text-cart-add').html(chSetting.text_in_cart);
			}
		} else if($(this).attr('data-pid')){
			var pid = $(this).attr('data-pid');
			if(in_cart_pids.indexOf(Number(pid)) !== -1){
				$(this).addClass('is-active').find('.text-cart-add').html(chSetting.text_in_cart);
			}
		}
	});
}

function changeWishlistBtn(){
	var w_ids = $('.box-wishlist').attr('data-w-ids');
	if(typeof(w_ids) === 'undefined') return;
	w_ids = String(w_ids).split(',').map(Number);
	$('.btn-wishlist').each(function(){
		var pid = $(this).attr('onclick').replace(/\D+/g,'');
		if(w_ids.indexOf(Number(pid)) !== -1){
			$(this).attr('onclick', $(this).attr('onclick').replace('wishlist.add', 'wishlist.remove')).attr('title', chSetting.text_btn_wishlist_active).attr('data-original-title', chSetting.text_btn_wishlist_active).addClass('is-active');
		}
	});
}

function changeCompareBtn(){
	var c_ids = $('.box-compare').attr('data-c-ids');
	if(typeof(c_ids) === 'undefined') return;
	c_ids = String(c_ids).split(',').map(Number);
	$('.btn-compare').each(function(){
		var pid = $(this).attr('onclick').replace(/\D+/g,'');
		if(c_ids.indexOf(Number(pid)) !== -1){
			$(this).attr('onclick', $(this).attr('onclick').replace('compare.add', 'compare.remove')).attr('title', chSetting.text_btn_compare_active).attr('data-original-title', chSetting.text_btn_compare_active).addClass('is-active');
		}
	});
}

if(chSetting.show_hc_search){
	$(function(){
		var id_category = $('#content select[name=\'category_id\']').find("option:selected").attr("value");
		var text_category = $('#content select[name=\'category_id\']').find("option:selected").html();
		if(id_category > 0){
			$('body').find('input[name=\'search_category_id\']').val(id_category);
			$('.btn-search-select').prop('title', text_category);
			$('body').find('li.sel-cat-search').removeClass('sel-cat-search');
			$('body').find("[data-idsearch='"+ id_category +"']").parent().addClass('sel-cat-search');
		}
	});
	$(document).on('click', '.header-search .categories a', function () {
		$('body').find('input[name=\'search_category_id\']').val($(this).attr('data-idsearch'));
		$('body').find('.btn-search-select').prop('title', $(this).html());
		$('body').find('li.sel-cat-search').removeClass('sel-cat-search');
		$('body').find("[data-idsearch='"+ $(this).attr('data-idsearch') +"']").parent().addClass('sel-cat-search');
	});
};

function setCookieView(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/;samesite=Lax;";
}

var chm_delay = (function () {
  var timers = {};
  return function (callback, ms, uniqueId) {
    if (!uniqueId) {
      uniqueId = "Don't call this twice without a uniqueId";
    }
    if (timers[uniqueId]) {
      clearTimeout (timers[uniqueId]);
    }
    timers[uniqueId] = setTimeout(callback, ms);
  };
})();

$(window).resize(function () {
    chm_delay(function(){
      displayView.resize();
    }, 50, "display_view_resize");
});

var displayView = {
	'init': function(){
		if (localStorage.getItem('display') == 'list') {
			displayView.list_view();
		} else if (localStorage.getItem('display') == 'price'){
			displayView.price_view();
		} else {
			displayView.grid_view();
		}

		$('#list-view').click(function() {
			displayView.list_view();
			localStorage.setItem('display_old', localStorage.getItem('display'));
			if (typeof ProStickerLoad === 'function') {
			  setTimeout ('ProStickerLoad()', 1500);
			}
		});

		$('#grid-view').click(function() {
			displayView.grid_view();
			localStorage.setItem('display_old', localStorage.getItem('display'));
			if (typeof ProStickerLoad === 'function') {
			  setTimeout ('ProStickerLoad()', 1500);
			}
		});

		$('#price-view').click(function() {
			displayView.price_view();
			localStorage.setItem('display_old', localStorage.getItem('display'));
			if (typeof ProStickerLoad === 'function') {
			  setTimeout ('ProStickerLoad()', 1500);
			}
		});
	},
	'list_view': function() {
		$('#content .product-layout').attr('class', 'product-layout product-list col-xs-12');
		$('#list-view').addClass('active');
		$('#grid-view, #price-view').removeClass('active');
		localStorage.setItem('display', 'list');
		setCookieView('display', 'list', 365);
	},
	'grid_view': function() {
		cols = $('#column-right, #column-left').length;

		if (cols == 2) {
			$('#content .product-layout').attr('class', 'product-layout product-grid col-xs-6 col-sm-6 col-md-6 col-lg-4');
		} else if (cols == 1) {
			$('#content .product-layout').attr('class', 'product-layout product-grid col-xs-6 col-sm-6 col-md-4 col-lg-4');
		} else {
			$('#content .product-layout').attr('class', 'product-layout product-grid col-xs-6 col-sm-6 col-md-3 col-lg-3 col-lg-1-5');
		}

		$('#grid-view').addClass('active');
		$('#list-view, #price-view').removeClass('active');

		localStorage.setItem('display', 'grid');
		setCookieView('display', 'grid', 365);
	},
	'price_view': function() {
		$('#content .product-layout').attr('class', 'product-layout product-price col-xs-12');
		$('#price-view').addClass('active');
		$('#list-view, #grid-view').removeClass('active');
		localStorage.setItem('display', 'price');
		setCookieView('display', 'price', 365);
	},
	'resize': function() {
		if (localStorage.getItem('display') != 'grid'){
			if (viewport().width < 1200) {
				localStorage.setItem('display_old', localStorage.getItem('display'));
				displayView.grid_view();
			}
		}
		if (localStorage.getItem('display_old') == 'price' && (viewport().width > 1200)){
			displayView.price_view();
		}
	}
}

$(document).ready(function () {
	displayView.init();
	changeAddToCartBtn();
	changeWishlistBtn();
	changeCompareBtn();

	$(document).on("click.bs.dropdown.data-api", "#cart", function (e) { e.stopPropagation() });

	if (viewport().width >= 992) {
		$('.up-header-phones').hover(function() {
			var $dropdown = $(this).find('.up-header-phones__dropdown');
			var $top = $(this).find('.up-header-phones__top');
			if ($dropdown.outerWidth() > $top.outerWidth()) {
				$dropdown.addClass('top-left-radius');
			}

			$(this).addClass('open');
		}, function() {
			$(this).removeClass('open');
			$(this).find('.up-header-phones__dropdown').removeClass('top-left-radius');
		});
	}


	if (viewport().width < 992) {
		$('.drop-icon-info').on('click', function(e) {
			e.preventDefault();
			var element_tel = $(this).parent().parent();
			if (element_tel.hasClass('open')) {
				$(this).find('.car-down').removeClass('rotate-icon-180');
				$(this).find('.car-down').addClass('rotate-icon-0');
				element_tel.removeClass('open');
			} else {
				$(this).find('.car-down').addClass('rotate-icon-180');
				$(this).find('.car-down').removeClass('rotate-icon-0');
				element_tel.addClass('open');
			}
		});
	}

	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// Currency
	$('#currency .currency-select').on('click', function(e) {
		e.preventDefault();

		$('#currency input[name=\'code\']').attr('value', $(this).attr('name'));

		$('#currency').submit();
	});

	// Language
	$('#language .dropdown-menu button').on('click', function(e) {
		e.preventDefault();

		$('#language input[name=\'code\']').attr('value', $(this).attr('name'));

		$('#language').submit();
	});

	/* Search */
	$('.btn-search').on('click', function() {
		url = $('base').attr('href') + 'index.php?route=product/search';

		var value = $(this).closest('.header-search').find('input[name=\'search\']').val();

		if (value) {
			url += '&search=' + encodeURIComponent(value);
		} else {
			url += '&search=';
		}

		var search_category_id = $('input[name=\'search_category_id\']').prop('value');
		if (search_category_id > 0) {
			url += '&category_id=' + encodeURIComponent(search_category_id) + '&sub_category=true';
		}

		location = url;
	});

	$('.search_word a').on('click', function() {
		$(this).parent().prev().find('.form-control[name=search]').val($(this).text());
		$(this).parent().prev().find('button.btn.btn-search').trigger('click');
	});

	$('.header-search input[name=\'search\']').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$(this).parent().find('button.btn.btn-search').trigger('click');
		}
	});

	if ($( document ).width()>767) {
		setTimeout(function () {
			$('a > img').each(function () {
				if ($(this).attr('data-additional-hover')) {
					var img_src = $(this).attr('data-additional-hover');
					$(this).addClass('main-img');
					$(this).after('<img src="'+img_src+'" class="additional-img-hover img-responsive" />');
				}
			});
		},3000);
	}

	$('[data-toggle=\'tooltip\']').tooltip({container: 'body',trigger: 'hover'});
	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body',trigger: 'hover'});
	});

	if(viewport().width > 768){
		$('body').click(function(){
		  $('[data-toggle="tooltip"]').tooltip('hide');
		});
	}
});

function getModalOptions(product_id, quantity){
	$.ajax({
		url: 'index.php?route=upstore/theme/checkOptions',
		type: 'post',
		data: 'product_id=' + product_id +'&quantity=' + quantity,
		dataType: 'json',
		success: function(json) {
			if (json['options']) {
				html  = '<div id="modal-options" class="modal fade" role="dialog">';
				html += '	<div class="modal-dialog chm-modal modal-dialog-centered">';
				html += '		<div class="modal-content">'+ json['options'] +'</div>';
				html += '	</div>';
				html += '</div>';

				$('html body').append(html);
				$('#modal-options').modal('show');

				$(document).on('hide.bs.modal', '#modal-options.modal.fade', function () {
					$('#modal-options').remove();
				});
			}
		}
	});
}

// Cart add remove functions
var chmAddCartTimeout_id = 0;
var cart = {
	'add': function(product_id, element) {

		var $elem = $(element).closest('.product-thumb'),
		quantity = (typeof($elem.find('.quantity-num').val()) != 'undefined' ? $elem.find('.quantity-num').val() : 1),
		options = $elem.find('.options input[type=\'radio\']:checked, .options input[type=\'checkbox\']:checked, .options select, .options input[type=\'text\'], .options textarea, .options input[type=\'hidden\']'),
		data = 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1);

		if (options.length) {
			data += '&'+options.serialize();
		}



		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: data,
			dataType: 'json',
			beforeSend: function() {
				clearTimeout(chmAddCartTimeout_id);
				$(element).find('.up-icon-cart').addClass('d-none');
				$(element).prepend('<svg class="up-spinner" viewBox="25 25 50 50"><circle stroke="currentColor" r="20" cy="50" cx="50"></circle></svg>');
			},
			complete: function() {
				setTimeout(function () {
					$(element).find('.up-spinner').remove();
					$(element).find('.up-icon-cart').removeClass('d-none');
				}, 300);
			},
			success: function(json) {
				$('.option-danger, .alert, .text-danger').remove();
				$('.form-group').removeClass('option-error');

				if (json['redirect'] && !chSetting.show_popup_options) {
					location = json['redirect'];
				}

				if(chSetting.show_popup_options){
					if (json['error']) {
						if (json['error']['option']) {
							if($('#modal-options').length){
								for (i in json['error']['option']) {
									var element = $('#input-modal-option' + i.replace('_', '-'));
									if (element.parent().hasClass('input-group')) {
										element.parent().parent().addClass('option-error');
									} else {
										element.parent().addClass('option-error');
									}

									$('#top').before('<div class="alert option-danger"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/warning-icon.svg"><div class="text-modal-block">' + json['error']['option'][i] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
								}
							} else {
								getModalOptions(product_id, quantity);
							}
						}
						chmAddCartTimeout_id = setTimeout(function () {
							$('.option-danger, .alert, .text-danger').remove();
						}, 7000);
					}
				}

				if (json['success']) {
					if(chSetting.show_popup_options){
						$('#modal-options').modal('hide');
					}

					if(json['popup_design']=='1'){
						setTimeout(function () {
							fastorder_open_cart();
						}, 300);
					} else if(json['popup_design']=='0') {
						html  = '<div id="modal-addcart" class="modal fade" role="dialog">';
						html += '  <div class="modal-dialog" style="overflow:hidden">';
						html += '    <div class="modal-content">';
						if(json['show_onepagecheckout']=='1'){
						html += '      	<div class="modal-body"><div class="text-center mb-20">' + json['success'] + '</div><div class="text-center mb-20"><img src="'+ json['image_cart'] +'"  /><br></div><div class="dflex flex-wrap justify-content-between align-items-center"><button data-dismiss="modal" class="chm-btn chm-btn-grey chm-px-lg xs-w-100 sm-w-auto xs-mb-20 sm-mb-0">'+ chSetting.button_shopping +'</button><a href=' + chSetting.link_onepcheckout + ' class="chm-btn chm-btn-primary chm-px-lg xs-w-100 sm-w-auto">'+ chSetting.button_checkout +'</a></div></div>';
						} else {
						html += '      	<div class="modal-body"><div class="text-center mb-20">' + json['success'] + '</div><div class="text-center mb-20"><img src="'+ json['image_cart'] +'"  /><br></div><div class="dflex flex-wrap  justify-content-between align-items-center"><button data-dismiss="modal" class="chm-btn chm-btn-grey chm-px-lg xs-w-100 sm-w-auto xs-mb-20 sm-mb-0">'+ chSetting.button_shopping +'</button><a href=' + chSetting.link_checkout + ' class="chm-btn chm-btn-primary chm-px-lg xs-w-100 sm-w-auto">'+ chSetting.button_checkout +'</a></div></div>';
						}
						html += '    </div>';
						html += '  </div>';
						html += '</div>';
						$('body').append(html);
						$('#modal-addcart').modal('show');
					} else if(json['popup_design'] == '2') {
						$('#top').before('<div class="alert add_product_alert"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"><div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');

					}
					chmAddCartTimeout_id = setTimeout(function () {
						$('.option-danger, .alert, .text-danger,.add_product_alert').remove();
					}, 7000);
					setTimeout(function () {
						$('.cart-total').html(json['total']);
					}, 100);

					$('.shopping-cart .cart-content').load('index.php?route=common/cart/info .cart-content > *');
					setTimeout(function () {
						changeAddToCartBtn();
						if(json['popup_design'] == '3'){
							openFixedCart('.shopping-cart > button');
							//$('.shopping-cart > button').trigger('click');
						}
					}, 300);

				}
				$(document).on('hide.bs.modal', '#modal-addcart.modal.fade', function () {
					$('#modal-addcart').remove();
				});
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',

			success: function(json) {

				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('.cart-total').html(json['total']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || location.pathname == '/cart/' || location.pathname == '/checkout/') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('.shopping-cart .cart-content').load('index.php?route=common/cart/info .cart-content > *');
					stickyBtnCart();
				}
			}
		});
	},
	'remove': function(key,product_id) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('.cart-total').html(json['total']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || location.pathname == '/cart/' || location.pathname == '/cart' || location.pathname == '/checkout/') {
					location = 'index.php?route=checkout/cart';
				} else if (getURLVar('route') == 'checkout/onepcheckout') {
					if(json['total'] > 0){
						$('.shopping-cart .cart-content').load('index.php?route=common/cart/info .cart-content > *');
						stickyBtnCart();
						update_checkout();
					} else {
						location = 'index.php?route=checkout/cart';
					}
				} else {
					$('.shopping-cart .cart-content').load('index.php?route=common/cart/info .cart-content > *');
					stickyBtnCart();
				}

				$('.btn-general').each(function(){
					if($(this).attr('onclick')){
						var pid = $(this).attr('onclick').replace(/\D+/g,'');
						if(product_id == pid){
							$(this).removeClass('is-active').find('.text-cart-add').html(chSetting.button_cart);
						}
					} else if($(this).attr('data-pid')){
						var pid = $(this).attr('data-pid');
						if(product_id == pid){
							$(this).removeClass('is-active').find('.text-cart-add').html(chSetting.button_cart);
						}
					}
				});
			}
		});
	}
}


var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('.cart-total').html(json['total']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout' || getURLVar('route') == 'checkout/onepcheckout' || location.pathname == '/cart/' || location.pathname == '/checkout/') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('.shopping-cart .header-cart-scroll').load('index.php?route=common/cart/info .header-cart-scroll > *');
					stickyBtnCart();
				}
			}
		});
	}
}
var wishlist = {
	'add': function(product_id) {
		$('#modal-wishlist').remove();
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {

				html  = '<div id="modal-wishlist" class="modal fade">';
				html += '  <div class="modal-dialog">';
				html += '    <div class="modal-content ch-modal-success">';
				html += '      <div class="modal-body"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"> <div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button></div>';
				html += '    </div>';
				html += '  </div>';
				html += '</div>';

				$('body').append(html);

				$('#modal-wishlist').modal('show');

				if($("#wishlist-total .wishlist-quantity").length==0) {
					$('#wishlist-total').append('<span class="wishlist-quantity">'+ json['total']+ '</span>');
				} else {
					$('#wishlist-total .wishlist-quantity').html(json['total']);
				}
				if($(".btn-mob-wishlist-bottom .wishlist-quantity").length==0) {
					$('.btn-mob-wishlist-bottom').append('<span class="wishlist-quantity">'+ json['total']+ '</span>');
				} else {
					$('.btn-mob-wishlist-bottom .wishlist-quantity').html(json['total']);
				}

				$(document).on('hide.bs.modal', '#modal-wishlist.modal.fade', function () {
					$('#modal-wishlist').remove();
				});

				$('.btn-wishlist').each(function(){
					var pid = $(this).attr('onclick').replace(/\D+/g,'');
					if(product_id == pid){
						$(this).attr('onclick', $(this).attr('onclick').replace('wishlist.add', 'wishlist.remove')).attr('title', chSetting.text_btn_wishlist_active).attr('data-original-title', chSetting.text_btn_wishlist_active).addClass('is-active');
					}
				});

				$.get('index.php?route=upstore/theme/getAllWishlist', function(data) {
					$('.box-wishlist').attr('data-w-ids', data.all_wishlist);
				});
			}

		});
	},
	'remove': function(product_id) {
		$('#modal-wishlist').remove();
		$.ajax({
			url: 'index.php?route=upstore/theme/removeWishlist',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {

				html  = '<div id="modal-wishlist" class="modal fade">';
				html += '  <div class="modal-dialog">';
				html += '    <div class="modal-content ch-modal-success">';
				html += '      <div class="modal-body"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"> <div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button></div>';
				html += '    </div>';
				html += '  </div>';
				html += '</div>';

				$('body').append(html);

				$('#modal-wishlist').modal('show');

				if($("#wishlist-total .wishlist-quantity").length==0) {
					$('#wishlist-total').append('<span class="wishlist-quantity">'+ json['total']+ '</span>');
				} else {
					$('#wishlist-total .wishlist-quantity').html(json['total']);
				}

				if($(".btn-mob-wishlist-bottom .wishlist-quantity").length==0) {
					$('.btn-mob-wishlist-bottom').append('<span class="wishlist-quantity">'+ json['total']+ '</span>');
				} else {
					$('.btn-mob-wishlist-bottom .wishlist-quantity').html(json['total']);
				}

				$(document).on('hide.bs.modal', '#modal-wishlist.modal.fade', function () {
					$('#modal-wishlist').remove();
				});

				$('.btn-wishlist').each(function(){
					var pid = $(this).attr('onclick').replace(/\D+/g,'');
					if(product_id == pid){
						$(this).attr('onclick', $(this).attr('onclick').replace('wishlist.remove', 'wishlist.add')).attr('title', chSetting.text_btn_wishlist).attr('data-original-title', chSetting.text_btn_wishlist).removeClass('is-active');
					}
				});

				$.get('index.php?route=upstore/theme/getAllWishlist', function(data) {
					$('.box-wishlist').attr('data-w-ids', data.all_wishlist);
				});

			}

		});
	}
}

var compare = {
	'add': function(product_id) {
		$('#modal-compare').remove();
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {

				html  = '<div id="modal-compare" class="modal fade">';
				html += '  <div class="modal-dialog">';
				html += '    <div class="modal-content ch-modal-success">';
				html += '      <div class="modal-body"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"> <div class="text-modal-block"> ' + json['success'] + '</div><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button></div>';
				html += '    </div>';
				html += '  </div>';
				html += '</div>';

				$('body').append(html);

				$('#modal-compare').modal('show');

				if($("#compare-total .compare-quantity").length==0) {
					$('#compare-total').append('<span class="compare-quantity">'+ json['total']+ '</span>');
				} else {
					$('#compare-total .compare-quantity').html(json['total']);
				}

				if($(".btn-mob-compare-bottom .compare-quantity").length==0) {
					$('.btn-mob-compare-bottom').append('<span class="compare-quantity">'+ json['total']+ '</span>');
				} else {
					$('.btn-mob-compare-bottom .compare-quantity').html(json['total']);
				}

				$(document).on('hide.bs.modal', '#modal-compare.modal.fade', function () {
					$('#modal-compare').remove();
				});

				$('.btn-compare').each(function(){
					var pid = $(this).attr('onclick').replace(/\D+/g,'');
					if(product_id == pid){
						$(this).attr('onclick', $(this).attr('onclick').replace('compare.add', 'compare.remove')).attr('title', chSetting.text_btn_compare_active).attr('data-original-title', chSetting.text_btn_compare_active).addClass('is-active');
					}
				});

				$.get('index.php?route=upstore/theme/getAllCompare', function(data) {
					$('.box-compare').attr('data-c-ids', data.all_compare);
				});
			}

		});
	},
	'remove': function(product_id) {
		$('#modal-compare').remove();
		$.ajax({
			url: 'index.php?route=upstore/theme/removeCompare',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {

				html  = '<div id="modal-compare" class="modal fade">';
				html += '  <div class="modal-dialog">';
				html += '    <div class="modal-content ch-modal-success">';
				html += '      <div class="modal-body"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"> <div class="text-modal-block"> ' + json['success'] + '</div><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="up-icon-close" aria-hidden="true"></i></button></div>';
				html += '    </div>';
				html += '  </div>';
				html += '</div>';

				$('body').append(html);

				$('#modal-compare').modal('show');

				if($("#compare-total .compare-quantity").length==0) {
					$('#compare-total').append('<span class="compare-quantity">'+ json['total']+ '</span>');
				} else {
					$('#compare-total .compare-quantity').html(json['total']);
				}

				if($(".btn-mob-compare-bottom .compare-quantity").length==0) {
					$('.btn-mob-compare-bottom').append('<span class="compare-quantity">'+ json['total']+ '</span>');
				} else {
					$('.btn-mob-compare-bottom .compare-quantity').html(json['total']);
				}

				$(document).on('hide.bs.modal', '#modal-compare.modal.fade', function () {
					$('#modal-compare').remove();
				});

				$('.btn-compare').each(function(){
					var pid = $(this).attr('onclick').replace(/\D+/g,'');
					if(product_id == pid){
						$(this).attr('onclick', $(this).attr('onclick').replace('compare.remove', 'compare.add')).attr('title', chSetting.text_btn_compare).attr('data-original-title', chSetting.text_btn_compare).removeClass('is-active');
					}
				});

				$.get('index.php?route=upstore/theme/getAllCompare', function(data) {
					$('.box-compare').attr('data-c-ids', data.all_compare);
				});
			}

		});
	}
};

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
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
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);




