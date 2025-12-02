
function getVariantProducts(kit_id, vk_id,prod_id){
	$.ajax({
		type:'get',
		url:'index.php?route=extension/module/upstore_kits/getVariantProducts&kit_id=' + kit_id +'&vk_id=' + vk_id +'&prod_id=' + prod_id,
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			creatOverlayLoadPage(false);
		},
		success:function (data) {
			html  = '<div id="modal-kits-products" class="modal fade" role="dialog">';
			html += '	<div class="modal-dialog modal-kits sm-modal-4 chm-modal modal-dialog-centered">';
			html += '		<div class="modal-content">'+ data +'</div>';
			html += '	</div>';
			html += '</div>';

			$('html body').append(html);
			$('#modal-kits-products').modal('show');

			$(document).on('hide.bs.modal', '#modal-kits-products.modal.fade', function () {
				$('#modal-kits-products').remove();
			});
		}
	});
}

$(document).on('click','.add-kit-product', function(e){
	e.preventDefault();
	var old_pid = $(this).attr('data-old-pid'),
		pid = $(this).attr('data-pid'),
		kid = $(this).attr('data-kid'),
		vid = $(this).attr('data-vid'),
		elem_kit = $('.product-kit[data-kit="'+ kid +'_'+ vid +'_'+ old_pid +'"]'),
		elem_total = $('.totals_kit_'+ kid +'_' + vid);

	$.ajax({
		url: 'index.php?route=extension/module/upstore_kits/setNewProduct',
		type: 'post',
		data: 'kit_id=' + kid +'&vk_id=' + vid +'&prod_id=' + pid,
		dataType: 'json',
		success: function(json) {

			if(json['product']){
				elem_kit.html(json['product']);
				elem_kit.attr("data-kit",kid + '_' + vid +'_' + pid);

			}
			if(json['total_kit']){
				elem_total.find('.kit-totals').html(json['total_kit']);
			}
			if(json['discount_kit']){
				elem_total.find('.kit-discount-total').html(json['discount_kit']);
				elem_total.find('.kit-cart .btn').attr('onclick','kitAddToCart('+ kid +','+ vid +','+ pid +')');
			}
			setTimeout(function() {
			 	$('#modal-kits-products .close-modal').trigger('click');
			}, 50);
		}
	});

});

function updateKitTotal(){
	var kit_total = Number($('.modal-kits-options .kit-totals').attr('data-total')),
	kit_option_price = 0,
	kit_type_d = Number($('.modal-kits-options .kit-totals').attr('data-kit-td')),
	kit_discount = Number($('.modal-kits-options .kit-totals').attr('data-kit-d'));

	$('input:checked,option:selected').each(function() {
		if ($(this).data('prefix') == '+') {
			kit_option_price += Number($(this).data('price'));
		}
		if ($(this).data('prefix') == '-') {
			kit_option_price -= Number($(this).data('price'));
		}
	});

	kit_total += kit_option_price;

	if (kit_type_d) {
		var discount = Number(kit_discount);
	}else{
		var discount = kit_total / 100 * (kit_discount);
	}

	$('.modal-kits-options .kit-totals').html( kit_price_format(kit_total - discount) );
	$('.modal-kits-options .kit-discount-total').html( kit_price_format(discount) );

}

$(document).on('change', '.modal-kits-options input[type="checkbox"]', function(e) {
	updateKitTotal();
});
$(document).on('change', '.modal-kits-options input[type="radio"]', function(e) {
	updateKitTotal();
});
$(document).on('change', '.modal-kits-options select', function(e) {
	updateKitTotal();
});
let chmKitTimeout_id = 0;
$(document).on('click','.kitAddToCart', function(e){
	$.ajax({
		url: 'index.php?route=extension/module/upstore_kits/kitAddToCart',
		type: 'post',
		data: $('.modal-kits-options input[type=\'text\'], .modal-kits-options input[type=\'hidden\'], .modal-kits-options input[type=\'radio\']:checked, .modal-kits-options input[type=\'checkbox\']:checked, .modal-kits-options select, .modal-kits-options textarea'),
		dataType: 'json',
		beforeSend: function() {
			clearTimeout(chmKitTimeout_id);
		},
		success: function(json) {
			$('.option-danger, .alert, .text-danger,.add_product_alert').remove();
			$('.form-group.option-error').removeClass('option-error');
			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {
						var element = $('#input-kit-option' + i.replace('_', '-'));

						if (element.parent().hasClass('input-group')) {
							element.parent().parent().addClass('option-error');
						} else {
							element.parent().addClass('option-error');
						}

						$('#top').before('<div class="alert option-danger"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/warning-icon.svg"><div class="text-modal-block">' + json['error']['option'][i] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}

					chmKitTimeout_id = setTimeout(function () {
		  				$('.option-danger, .alert, .text-danger').remove();
					}, 7000);
				}
			}

			if (json['success']) {
				$('#top').before('<div class="alert add_product_alert"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"><div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');

				setTimeout(function () {
					$('.alert, .text-danger,.add_product_alert').remove();
				}, 3000);

				setTimeout(function () {
					$('.cart-total').html(json['total']);
				}, 100);

				$('#cart .header-cart-scroll').load('index.php?route=common/cart/info .header-cart-scroll > *')

				$('#modal-kits-options .close-modal').trigger('click');
			}

		}
	});
});

function kitAddToCart(kid, vid, pid){
	$.ajax({
		url: 'index.php?route=extension/module/upstore_kits/kitAddToCart',
		type: 'post',
		data: 'kit_id=' + kid +'&vk_id=' + vid +'&prod_id=' + pid,
		dataType: 'json',
		success: function(json) {
			if(json['options']){
				html  = '<div id="modal-kits-options" class="modal fade" role="dialog">';
				html += '	<div class="modal-dialog modal-kits-options chm-modal modal-dialog-centered">';
				html += '		<div class="modal-content">'+ json['options'] +'</div>';
				html += '	</div>';
				html += '</div>';

				$('html body').append(html);
				$('#modal-kits-options').modal('show');

				$(document).on('hide.bs.modal', '#modal-kits-options.modal.fade', function () {
					$('#modal-kits-options').remove();
				});
			}

			if (json['success']) {
				$('#top').before('<div class="alert add_product_alert"><img class="success-icon" alt="success-icon" src="catalog/view/theme/upstore/image/success-icon.svg"><div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');

				setTimeout(function () {
					$('.option-danger, .alert, .text-danger,.add_product_alert').remove();
				}, 3000);

				setTimeout(function () {
					$('.cart-total').html(json['total']);
				}, 100);

				$('#cart .header-cart-scroll').load('index.php?route=common/cart/info .header-cart-scroll > *')

				$('#modal-kits-options .close-modal').trigger('click');
			}
		}
	});
}