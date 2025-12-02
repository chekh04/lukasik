function mmpro_aim(){

	if($('.menu-header-box.type-menu-h-1:not(.m-sticky)').hasClass('mm_open_hp')){
		$('#menu-vertical #menu-vertical-list').menuAim({
			activateCallback: activateSubmenu,
			deactivateCallback: deactivateSubmenu,
		});
	} else {
		$('#menu-vertical #menu-vertical-list').menuAim({
			activateCallback: activateSubmenu,
			deactivateCallback: deactivateSubmenu,
			enterCallback:function(row){
				$('#menu-vertical-list > li').removeClass('menu-open');
			},
			exitMenuActiveLastRow: function(lastActiveRow) {
				if($(lastActiveRow).hasClass('dropdown')){
					$(lastActiveRow).addClass('menu-open');
				}
			}
		});
	}

	function activateSubmenu(row) {
		if($(row).hasClass('dropdown')){
			$(row).addClass('menu-open');
			$(row).closest('#menu-vertical').addClass('border-radius-right-off');
		}
	}

	function deactivateSubmenu(row) {
		$(row).removeClass('menu-open');
		$(row).find('.block-opacity').removeClass('active');
		$(row).closest('#menu-vertical').removeClass('border-radius-right-off');
	}

	$('.dropdown-menu-simple .nsmenu-haschild').menuAim({
		activateCallback: activateSubmenu2level,
		deactivateCallback: deactivateSubmenu2level,
	});

	function activateSubmenu2level(row) {
		if($(row).hasClass('nsmenu-issubchild')){
			$(row).addClass('menu-open-2level');
			$(row).parent().addClass('border-radius-right-off');
		}
	}

	function deactivateSubmenu2level(row) {
		$(row).removeClass('menu-open-2level');
		$(row).parent().removeClass('border-radius-right-off');
	}

	$('.dropdown-menu-simple .nsmenu-ischild-simple > ul').menuAim({
		activateCallback: activateSubmenu4level,
		deactivateCallback: deactivateSubmenu4level,
	});

	function activateSubmenu4level(row) {
		$(row).addClass('menu-open-4level');
		$(row).parent().addClass('border-radius-right-off');
	}

	function deactivateSubmenu4level(row) {
		$(row).removeClass('menu-open-4level');
		$(row).parent().removeClass('border-radius-right-off');
	}

	$(".ns-dd").hover(function() {$(this).parent().find('.parent-link').toggleClass('hover');});
	$(".child-box").hover(function() {$(this).parent().find('.with-child').toggleClass('hover');});
	$(".nsmenu-ischild.nsmenu-ischild-simple").hover(function() {$(this).parent().find('> a').toggleClass('hover');});
	$(".child_4level_simple").hover(function() {$(this).parent().find('> a').toggleClass('hover');});

	// Menu
	$('#menu-vertical #menu-vertical-list .dropdown-menu').each(function() {
		var menu = $('#menu-vertical').offset();
		var dropdown = $(this).parent().offset();

		var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu-vertical').outerWidth());

		if (i > 0) {
			$(this).css('margin-left', '-' + (i + 5) + 'px');
		}
	});

	$('.nsmenu-type-manufacturer a[data-toggle="tooltip"]').tooltip({
		animated: 'fade',
		placement: 'top',
		template: '<div class="tooltip tooltip-manufacturer" role="tooltip"><div class="arrow"></div><div class="tooltip-inner tooltip-manufacturer-inner"></div></div>',
		html: true
	});
}

$(document).on('mouseenter', '.menu-header-box.type-menu-h-1:not(.m-sticky) .menu-box', function () {
	if(!$('html').hasClass('vw-100')){
   	$('html').toggleClass('vw-100');
		$('#menu-vertical').toggleClass('open');

		if($('html').hasClass('vw-100')){
			if($('.menu-box').hasClass('menu_mask')){
				$('html').append('<div class="ch-bg z-v3 hidden-xs hidden-sm"></div>');
			} else {
				$('html').append('<div class="ch-bg z-v3 bg-transp hidden-xs hidden-sm"></div>');
			}
			$('.ch-bg.z-v3').toggleClass('active');
		} else {
			$('.ch-bg.z-v3').remove();
		}
   }
}).on("mouseleave", '.menu-header-box.type-menu-h-1:not(.m-sticky):not(.active-m) .menu-box', function () {
    chmCloseMenu();
});


function heightMenuOpenHp() {
	if (viewport().width > 1199 && $('.menu-header-box.type-menu-h-1:not(.m-sticky)').hasClass('mm_open_hp')) {
		var mmh = document.querySelector('.home-page-content-top').clientHeight;
		$('.menu-box.m_type_header_1').css('height', (mmh - 40));
	}
}

function fixTopMenu() {
	let topNavHeight = $('#top').outerHeight() || 0,
		headerHeight = $('header.fix-header').outerHeight() || 0,
		htopBHeight = $('.htop-b-pc').height() || 0;

	let htab = 0;
	if ($('.tabs__header.tabs_top').length) {
		htab = $('.tabs__header .nav-tabs').outerHeight();
	}

	function updateStickyPositions() {
		if (viewport().width > 991) {
			$('.tabs__header.tabs_top').css('top', headerHeight - 1);
			if($('.sticky-left-block').length){
				$('.sticky-left-block').css('top', headerHeight + htab + 20);
			}
			if($('.sticky-product-info').length){
				$('.sticky-product-info').css('top', headerHeight + htab + 20);
			}
		} else {
			$('.tabs__header.tabs_top').removeAttr('style');
			if($('.sticky-product-info').length){
				$('.sticky-left-block').css('top', 0);
			}
			if($('.sticky-product-info').length){
				$('.sticky-product-info').css('top', 0);
			}
		}
	}

	function checkTabSticky() {
		if ($('.tabs__header.tabs_top').length) {
			let contentTop = $('#content').offset().top;
			if (viewport().width < 992) {
				headerHeight = 54;
			}
			if ($(window).scrollTop() > contentTop - headerHeight) {
				$('.tabs__header.tabs_top').addClass('active-tab-sticky');
				if (viewport().width < 992) {
					$('.up-header').addClass('header-no-shadow');
				} else {
					$('.up-header').removeClass('header-no-shadow');
				}
			} else {
				$('.tabs__header.tabs_top').removeClass('active-tab-sticky');
				$('.up-header').removeClass('header-no-shadow');
			}
		}
	}

	function fixDesktopsMenu() {
		let contentTopBox = $('.home-page-content-top');
		let scrollTop = $(window).scrollTop();

		if (scrollTop > topNavHeight + htopBHeight && viewport().width > 991) {
			if ($('.menu-header-box').hasClass('type-menu-h-1') && $('header').hasClass('fix-header')) {
				if ($('.menu-header-box.type-menu-h-1').hasClass('mm_open_hp')) {
					if (contentTopBox.length && scrollTop > contentTopBox.offset().top + contentTopBox.outerHeight() - headerHeight + 2) {
						$('.menu-header-box').addClass('m-sticky').css('top', headerHeight + 'px');
					} else {
						$('.menu-header-box.type-menu-h-1 #menu-vertical-list > li').removeClass('menu-open');
						$('.menu-header-box').removeClass('m-sticky').css('top', '0px');
					}
				} else {
					$('.menu-header-box').addClass('m-sticky').css('top', headerHeight + 'px');
				}
			}
		} else {
			if ($('.menu-header-box').hasClass('type-menu-h-1') && $('header').hasClass('fix-header')) {
				$('.menu-header-box').removeClass('m-sticky').css('top', '0px');
			}
		}
	}

	function handleScroll() {
		updateStickyPositions();
		checkTabSticky();
		if($('header').hasClass('fix-header')){
			fixDesktopsMenu();
		}
	}

	$(window).on('scroll', function () {
		handleScroll();
	});

	handleScroll();
}



function heightMenu() {
	let height_windows = window.innerHeight;
	$('.menu-box.m_type_header_1').removeAttr('style');
	//$('.menu-box.m_type_header_1').css('max-height',height_windows - 100);
	heightMenuOpenHp();
}

function toggleMenu() {
	$('html').toggleClass('vw-100');
	$('.up-header .btn-menu-top').toggleClass('active-btn');
	$('.menu-header-box.type-menu-h-1, .menu-header-box.type-menu-h-2').toggleClass('active-m');
	$('#menu-vertical').toggleClass('open');
	if($('html').hasClass('vw-100')){
		$('.menu-header-box.type-menu-h-1').addClass('m-sticky').css('top', document.querySelector('header').clientHeight + 'px');
		if($('.menu-box').hasClass('menu_mask')){
			$('html').append('<div class="ch-bg z-v3 hidden-xs hidden-sm"></div>');
		} else {
			$('html').append('<div class="ch-bg z-v3 bg-transp hidden-xs hidden-sm"></div>');
		}
		$('.ch-bg.z-v3').toggleClass('active');
	} else {
		$('.menu-header-box.type-menu-h-1').removeClass('m-sticky').css('top', '0px');
		$('.menu-header-box.type-menu-h-1 #menu-vertical-list > li').removeClass('menu-open');
		$('.ch-bg.z-v3').remove();
	}
}

$(document).on('click', '.btn-menu-top.vh1, .btn-menu-top.vh1-bl', function() {
	toggleMenu();
	if(!$('#menu-vertical-list > li').hasClass('menu-open') && (!$('.menu-header-box.type-menu-h-1:not(.m-sticky)').hasClass('mm_open_hp'))){
		$('#menu-vertical-list > li:first-child').addClass('menu-open');
	}
	heightMenu();
});

$(window).resize(function() {
	 chm_delay(function(){
      heightMenu();
    }, 30, "heightMenu");
});

function chmCloseMenu(){
	$('html').removeClass('vw-100');
	$('html').removeClass('modal-open');
	$('.up-header .btn-menu-top').removeClass('active-btn');
	$('.ch-bg').remove();
	if($('.menu-header-box').hasClass('type-menu-h-2')){
		$('.menu-header-box.type-menu-h-2').toggleClass('left-open');
		$('#menu-vertical-list > li').removeClass('menu-open');

		setTimeout(function () {
			$('.menu-header-box.type-menu-h-1, .menu-header-box.type-menu-h-2').removeClass('active-m');
			$('#menu-vertical').removeClass('open');
		}, 200);
	} else {
		$('.menu-header-box.type-menu-h-1, .menu-header-box.type-menu-h-2').removeClass('active-m');
		$('#menu-vertical').removeClass('open');
		$('.menu-header-box.type-menu-h-2').toggleClass('left-open');
		$('.menu-header-box.type-menu-h-1').removeClass('m-sticky').css('top', '0px');
		$('.menu-header-box.type-menu-h-1 #menu-vertical-list > li').removeClass('menu-open');
	}
}

$(document).on('click', '.ch-bg', function() {
	chmCloseMenu();
});

$(document).on('click', '.btn-menu-top.vh2', function() {
	$('html').addClass('modal-open');
	$('html').append('<div class="ch-bg z-v4 hidden-xs hidden-sm"></div>');
	$('.menu-header-box.type-menu-h-2').addClass('active-m');
	$('#menu-vertical').addClass('open');
	$('.ch-bg').addClass('active');

	setTimeout(function () {
		$('.menu-header-box.type-menu-h-2').toggleClass('left-open');
	}, 20);
});

$(document).on('click', '.close-menu-left', function() {
	$(".ch-bg").trigger('click');
});

$(function() {
	fixTopMenu();
	heightMenu();
	mmpro_aim();
});

$(function() {
	if (viewport().width <= 991) {
		$('.mobm-header-block .mob-language').append( $('.box-language #language') );
		$('.mobm-header-block .mob-currency').append( $('.box-currency #currency') );
		$('.links-mob').append($('.header-nav-links'));
	}

	$(window).resize(function() {
		chm_delay(function(){
			var width_dev_ns = viewport().width;
			if (width_dev_ns <= 991) {
				$('.mobm-header-block .mob-language').append( $('.box-language #language') );
				$('.mobm-header-block .mob-currency').append( $('.box-currency #currency') );
				$('.links-mob').append($('.header-nav-links'));
			} else {
				$('.box-nav-links').prepend($('.links-mob .header-nav-links'));
				$('.box-language').append( $('.mobm-header-block .mob-language #language') );
				$('.box-currency').append( $('.mobm-header-block .mob-currency #currency') );
			}

			fixTopMenu();

		}, 300, "change_header");
	});
});


function fm_activeMenu(){
	$('#fm-fixed-mobile').removeClass('d-none');
	$('.mob-menu-info-fixed-left').removeClass('hidden');
	if($('#fm-fixed-mobile-bottom').length){
		$('#fm-fixed-mobile-bottom').addClass('z-index-low');
	}
	setTimeout(function () {
		$('.mob-menu-info-fixed-left').toggleClass('active');
	}, 20);

	$('body').toggleClass('no-scroll');
	$('.mob-menu-info-fixed-left').before('<div class="ch-bg-mob hidden-md hidden-lg"></div>');
	$('.ch-bg-mob').toggleClass('active');

	const $sideMenu = $('#mobm-left-content > .mobm-body');
	const $sideMenuHeader = $('#mobm-left-content .mobm-top');

	addScrollShadow($sideMenu, $sideMenuHeader);

}

function addScrollShadow($sideMenu, $sideMenuHeader){
	$sideMenu.on('scroll', function() {
		if ($sideMenu.scrollTop() > 0) {
			$sideMenuHeader.css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.06)');
		} else {
			$sideMenuHeader.css('box-shadow', 'none');
		}
	});
}

$(document).on('click', '.go-back-catalog', function () {
	$('#mobm-left-content').removeAttr('style');
	$('#mob-catalog-left').removeClass('active').removeAttr('style');
	setTimeout(function () {
		$('#mob-catalog-left').addClass('hidden');
	}, 500);
});

$(document).on('click', '#mm-mobile .go-2level', function (e) {
	e.preventDefault();
	var $this = $(this);
	$this.next().removeClass('d-none');
	setTimeout(function () {
		$('#mobm-left-content').css('transform','translateX(-100%)');
		const $sideMenu = $this.next().find('.m-mm-list').first();
		const $sideMenuHeader = $this.next().find('.mobm-top').first();
		addScrollShadow($sideMenu, $sideMenuHeader);
	}, 20);
});

$(document).on('click', '#mm-mobile .go-3level', function (e) {
	var $this = $(this);
	$this.next().removeClass('d-none');
	setTimeout(function () {
		$('#mobm-left-content').css('transform','translateX(-200%)');
		const $sideMenu = $this.next().find('.m-mm-list').first();
		const $sideMenuHeader = $this.next().find('.mobm-top').first();
		addScrollShadow($sideMenu, $sideMenuHeader);
	}, 20);
});

$(document).on('click', '.back-2level', function () {
	$('#mobm-left-content').removeAttr('style');
	const parent_2lv = $(this).parent();
	setTimeout(function () {
		parent_2lv.addClass('d-none');
	}, 500);
});

$(document).on('click', '.back-3level', function () {
	$('#mobm-left-content').css('transform','translateX(-100%)');
	const parent_3lv = $(this).parent();
	setTimeout(function () {
		parent_3lv.addClass('d-none');
	}, 500);
});

$(document).on('click', '.btn-open-viewed', function () {
	$('#fm-fixed-mobile').removeClass('d-none');
	if(!$('.mobile-sidebar-viewed__content .container-module-viewed').length){
		$('.mobile-sidebar-viewed__content').load('index.php?route=upstore/viewed_product/getViewedProduct');
	}

	$('#fm-fixed-mobile-bottom').addClass('z-index-low');
	$('body').addClass('no-scroll');
	$('.mobile-sidebar-viewed').removeClass('hidden');
	setTimeout(function () {
		$('.mobile-sidebar-viewed').addClass('open-viewed');
	}, 20);
	$('.mobile-sidebar-viewed').before('<div class="ch-bg-mob bg-viewed hidden-md hidden-lg"></div>');
	$('.ch-bg-mob').toggleClass('active');
	setTimeout(function () {
		const $sideMenu = $('.mobile-sidebar-viewed__content .container-module-viewed');
		const $sideMenuHeader = $('.mobile-sidebar-viewed__top');
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
});

$(document).on('click', '.fm-close-viewed,.bg-viewed', function () {
	$('.mobile-sidebar-viewed').removeClass('open-viewed');
	$('#fm-fixed-mobile-bottom').removeClass('z-index-low');
	$('.ch-bg-mob').remove();
	$('body').removeClass('no-scroll');
	setTimeout(function () {
		$('.mobile-sidebar-viewed').addClass('hidden');
		$('#fm-fixed-mobile').addClass('d-none');
	}, 200);
});

$(document).on('click', '.btn-open-search', function () {
	$('#fm-fixed-mobile').removeClass('d-none');
	$('.mobile-sidebar-search__content').append( $('.box-search .header-search') );

	$('#fm-fixed-mobile-bottom').addClass('z-index-low');

	$('body').addClass('no-scroll');
	$('.mobile-sidebar-search').removeClass('hidden');

	setTimeout(function () {
		$('.mobile-sidebar-search').addClass('open-search');
	}, 20);
	$('.mobile-sidebar-search').before('<div class="ch-bg-search hidden-md hidden-lg"></div>');
	$('.ch-bg-search').toggleClass('active');

});

$(document).on('click', '.fm-close-search,.ch-bg-search', function () {

	$('.mobile-sidebar-search').removeClass('open-search');
	$('#fm-fixed-mobile-bottom').removeClass('z-index-low');
	$('.ch-bg-search').remove();
	$('body').removeClass('no-scroll');
	setTimeout(function () {
		$('.mobile-sidebar-search').addClass('hidden');
		$('#fm-fixed-mobile').addClass('d-none');
	}, 200);
});

$(document).on('click', '.btn-open-contact', function () {
	$('#fm-fixed-mobile').removeClass('d-none');
	if ($('.mobile-sidebar-phones__inner').is(':empty')) {
		var clone_top_items = $('.up-header-phones__items').clone();

		clone_top_items.find('*').each(function() {
			var $this = $(this);
			var currentClass = $this.attr('class');
			if (currentClass) {
				$this.attr('class', currentClass.replace(/up-header-phones/g, 'mobile-sidebar-phones'));
			}
		});

		var currentClass = clone_top_items.attr('class');
		if (currentClass) {
			clone_top_items.attr('class', currentClass.replace(/up-header-phones/g, 'mobile-sidebar-phones'));
		}

		var clone_dropdown_items = $('.up-header-phones__dropdown').clone().removeClass('dropdown-menu ch-dropdown').addClass('list-unstyled');

		var currentClass = clone_dropdown_items.attr('class');
		if (currentClass) {
			clone_dropdown_items.attr('class', currentClass.replace(/up-header-phones/g, 'mobile-sidebar-phones'));
		}

		$('.mobile-sidebar-phones__content .mobile-sidebar-phones__inner').append(clone_top_items).append(clone_dropdown_items);
	}

	$('#fm-fixed-mobile-bottom').addClass('z-index-low');

	$('body').addClass('no-scroll');
	$('.mobile-sidebar-phones').removeClass('hidden');

	setTimeout(function () {
		$('.mobile-sidebar-phones').addClass('open-phones');
	}, 20);
	$('.mobile-sidebar-phones').before('<div class="ch-bg-mob ch-bg-phones hidden-md hidden-lg"></div>');
	$('.ch-bg-mob').toggleClass('active');

	setTimeout(function () {
		const $sideMenu = $('.mobile-sidebar-phones__content .mobile-sidebar-phones__inner');
		const $sideMenuHeader = $('.mobile-sidebar-phones__top');
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

});

$(document).on('click', '.fm-close-phones,.ch-bg-phones', function () {

	$('.mobile-sidebar-phones').removeClass('open-phones');
	$('#fm-fixed-mobile-bottom').removeClass('z-index-low');
	$('.ch-bg-mob').remove();
	$('body').removeClass('no-scroll');
	setTimeout(function () {
		$('.mobile-sidebar-search').addClass('hidden');
		$('#fm-fixed-mobile').addClass('d-none');
		const $sideMenuHeader = $('.mobile-sidebar-phones__top');
		$sideMenuHeader.css('box-shadow', 'none');
		$sideMenuHeader.addClass('no-shadow');
	}, 200);
});

$(document).on('click', '.btn-open-chats', function () {
	if($('.mobile-widget-block').hasClass('d-none')){
		$('.mobile-widget-block').removeClass('d-none');
		$('.btn-open-chats').addClass('show-icon-close');
		setTimeout(function () {
			$('.mobile-widget-block').addClass('show-m-block');

		}, 20);
	} else {
		$('.mobile-widget-block').removeClass('show-m-block');
		setTimeout(function () {
			$('.mobile-widget-block').addClass('d-none');
			$('.btn-open-chats').removeClass('show-icon-close');
		}, 20);
	}
});

$(".mob-block-close").click(function(){
	$(".mob-block-fix").fadeOut();
});

function close_mob_menu(){
	$('.mob-menu-info-fixed-left').removeClass('active');
	$('#fm-fixed-mobile-bottom').removeClass('z-index-low');
	$('body').removeClass('no-scroll');
	$('.ch-bg-mob').remove();
	setTimeout(function () {
		$('.mob-menu-info-fixed-left').addClass('hidden');
		$('#fm-fixed-mobile').addClass('d-none');
	}, 200);
}

$(document).on('click', '[data-toggle="close_mob_menu"],.ch-bg-mob', function () {
	close_mob_menu();
});

function open_mob_menu_left() {
	$('.mob-menu-info-fixed-left > div').removeClass('active').removeAttr('style');
	fm_activeMenu();

	if ($("#mob-catalog-left.mob-menu .mobm-body").find('#mm-mobile').length == 0) {
		$('#mob-catalog-left.mob-menu .mobm-body').load('index.php?route=common/menuvh/load_mob_menu');
	}
}


$(document).ready(function() {
	if($('footer.ch-dark-theme').length && (viewport().width <= 991)){
		var $footer = $('footer.ch-dark-theme');
		var $fixedMenu = $('#fm-fixed-mobile-bottom');

		function checkMenuPosition() {
			var footerTop = $footer.offset().top;
			var menuBottom = $fixedMenu.offset().top + $fixedMenu.outerHeight();

			if (menuBottom >= footerTop) {
				$fixedMenu.addClass('over-footer');
			} else {
				$fixedMenu.removeClass('over-footer');
			}
		}

		$(window).on('scroll resize', checkMenuPosition);
		checkMenuPosition();
	}
});
