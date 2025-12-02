(function($) {
	var OnePageCheckout = function(options) {

		var self = this;
		self.$elem = $('#onepcheckout');
		self.options = options;
		self.$sfields = $('.checkout_form input[type=\'text\'], .checkout-address input[type=\'radio\'], .checkout-address select', self.$elem);
		self.clickSelectors = '.cart-list .btn, [id^="button-"],input[name="register"]';

		self.init = function() {
			self.response();
			self.initSelect2();
			self.authorization();
			self.attachEventHandlers();
			self.saveFields();
			self.initRelatedSlider();

			$(document).ajaxComplete(function( event, xhr, settings ) {
				if ( settings.url === "index.php?route=checkout/cart/remove" || settings.url === "index.php?route=checkout/cart/add" ) {
					self.opcReloadAll();
				}
			});
		};

		self.attachEventHandlers = function(){

			if ($('select[name=\'country_id\']').length > 0) {
				$('select[name=\'country_id\']').trigger('change');
			}

			self.$elem.on('click', self.clickSelectors, function (e) {

				e.preventDefault();
				var $target = $(this);

				if ($target.hasClass('btn') && $target.closest('.cart-list').length > 0) {
					var action = $target.data('action');
					if (action === 'minus' || action === 'plus') {
						self.plusMinusQty($target, action);
					} else if (action === 'remove') {
						self.removeProduct($target.data('key'));
					}
				} else if ($target.attr('id') && $target.attr('id').startsWith('button-')) {
					var buttonId = $target.attr('id');
					if (buttonId === 'button-coupon') {
						self.handleCouponButtonClick();
					} else if (buttonId === 'button-reward') {
						self.handleRewardButtonClick();
					} else if (buttonId === 'button-voucher') {
						self.handleVoucherButtonClick();
					} else if (buttonId === 'button-register') {
						self.opcValidateForm();
					}
				} else if ($target.attr('name') === 'register') {
					self.customerUpdate();
				}
			});

			$(document).on('change', self.sfields, function () {
				setTimeout(function() {
					self.saveFields();
				}, 200);
			});

			/*Update Fileds after change customer group*/
			$(document).on('change', '#input-customer-group', function(e) {
				e.preventDefault();
				self.customerUpdate();
				self.paymentUpdate();
				self.shippingAddressUpdate();
			});

			$(document).on('change', 'select[name=\'country_id\'], select[name=\'zone_id\'], input[name=\'shipping_method\'], input[name=\'city\'], input[name=\'address_1\'], input[name=\'payment_method\']', function(e) {
				e.preventDefault();
				if (this.name == 'contry_id') {
					$("select[name=\'zone_id\']").val("");
					self.getZones(this.value);
				} else if(this.name == 'payment_method'){
					$('.payment').empty().addClass('hidden');
					$('#button-register').show();
					$('#button-confirm').remove();
					$('#opc-confirm').remove();

					self.paymentUpdate();

					setTimeout(function() {
						self.updateTotals();
					}, 500);
				} else if(this.name == 'city' || this.name == 'address_1') {
					setTimeout(function() {
						self.shippingUpdate();
						self.updateTotals();
					}, 700);
				} else {
					self.opcReloadAll();
				}
			});

			var inputTimeout;
			$(document).on('input', '.cart-item-price-quantity .form-control', function () {
				var input = this;
				clearTimeout(inputTimeout);
				inputTimeout = setTimeout(function() {
					self.opcValidateQty(input);
				}, 600);
			});

			self.initMaskPhone();
			self.initDateTimePicker();
		};

		self.opcValidateForm = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'date\'], .checkout_form input[type=\'datetime-local\'], .checkout_form input[type=\'time\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked,.checkout-totals input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select').serialize();
			data += '&_shipping_method='+ $('.checkout_form input[name=\'shipping_method\']:checked').prop('title') + '&_payment_method=' + $('.checkout_form input[name=\'payment_method\']:checked').prop('title');

			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/validate',
				type: 'post',
				data: data,
				dataType: 'json',
				beforeSend: function() {
					$('.ch-alert-danger').remove();
					$('#button-register').button('loading');
					self.loading_mask(true);
				},
				complete: function() {
					$('#button-register').button('reset');
				},
				success: function(json) {
					$('.alert:not(.opc-alert-danger),.opc-text-error').remove();
					$('.form-control').removeClass('error_input_checkout');
					$('.control-label').removeClass('error_input_checkout');

					if (json['error']) {
						self.loading_mask(false);
						for (i in json['error']) {
							if (i.includes('custom_field')) {
								$('#input-' + i.replaceAll('_', '-')).after('<div class="opc-text-error">'+ json['error'][i] +'</div>');
								$('#input-' + i.replaceAll('_', '-')).closest('.form-group').find('.control-label').addClass('error_input_checkout');
							} else {
								$('[name="' + i + '"]').closest('.form-group').find('.control-label').after('<div class="opc-text-error">'+ json['error'][i] +'</div>');
								$('[name="' + i + '"]').closest('.form-group').find('.control-label').addClass('error_input_checkout');
							}
						}

						var arr = [];

						for (i in json['error']) {
							arr.push(json['error'][i]);
						}

						var errorElement = $('.control-label.error_input_checkout').first();

						if (errorElement.length > 0) {
							$('html, body').animate({
								scrollTop: errorElement.offset().top - 120
							}, 'slow');
						}

						var time_a = 5000;
						var index = -1;
						var timer = setInterval(function () {
						if (++index == arr.length) {
							clearInterval(timer);
						} else {
							(function (currentIndex) {
								var block_alert = $('<div class="alert ch-alert-danger alert-' + currentIndex + '"><img class="warning-icon" alt="warning-icon" src="catalog/view/javascript/opc/image/warning-icon.svg"><div class="text-modal-block">' + arr[currentIndex] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
								$('body').append(block_alert);
								setTimeout(() => {
								$(`.ch-alert-danger.alert-${currentIndex}`).remove();
								}, time_a);
							})(index);
						}
						time_a = time_a + 1000;
						}, 10);
					}

					if (json['success']) {
						$('#button-register').hide();

						const paymentBlockSelector = '#opc-payment.payment';
						$(paymentBlockSelector).empty();
						$(paymentBlockSelector).html(json['success']['payment']);

						const elementsToCheck = `${paymentBlockSelector} h2, ${paymentBlockSelector} p, ${paymentBlockSelector} form, ${paymentBlockSelector} .proposition, ${paymentBlockSelector} .btn-primary, ${paymentBlockSelector} input[type='button'], ${paymentBlockSelector} input[type='submit'], ${paymentBlockSelector} a`;
						const elementsToClick = `${paymentBlockSelector} #button-confirm, ${paymentBlockSelector} .btn-primary, ${paymentBlockSelector} button, ${paymentBlockSelector} input[type='button'], ${paymentBlockSelector} input[type='submit'], ${paymentBlockSelector} a`;

						if ($(elementsToCheck).length) {

							$(elementsToClick).click(function() {
								$(document).ajaxComplete(function() {
									setTimeout(function() {
										self.loading_mask(false);
									}, 300);
								});
    						});

							setTimeout(function() {
								const confirmButton = $('#button-confirm');
								if (confirmButton.attr('href')) {
									window.location.href = confirmButton.attr('href');
								} else {
									$(elementsToClick).click();
								}
							}, 300);
						} else {
							setTimeout(function() {
								$(paymentBlockSelector).removeClass('hidden');
								$('html, body').animate({ scrollTop: $(paymentBlockSelector).offset().top - document.querySelector('header').clientHeight - 50}, 250);

								self.loading_mask(false);
							}, 300);
						}
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		};

		self.opcReloadAll = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked,.checkout-totals input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select').serialize();
			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/reloadAll',
				type: 'post',
				data: data,
				dataType: 'json',
				cache: false,
				beforeSend: function() {
					$('.ch-alert-danger').remove();
					self.loading_mask(true);
				},
				complete: function() {
					self.loading_mask(false);
				},
				success: function(json) {
					if(json['redirect']){
						location = json['redirect'];
					} else {
						for (var key in json) {
							switch (key) {
								case 'country_region':
									if($(json.country_region).find('.checkout-address-info .row').length > 0){
										$('.opc_block_country_region').html(json.country_region);
									}
									if (typeof self.initSelect2 == 'function') {
										self.initSelect2();
									}
									break;
								case 'shipping_method':
									if(json.shipping_method){
										$('.opc_block_shipping_method').html(json.shipping_method);
									} else {
										location = 'index.php?route=checkout/cart';
									}
									break;
								case 'shipping_address':
									if($(json.shipping_address).find('.checkout-address-info .row').length > 0){
										$('.opc_block_shipping_address').html(json.shipping_address);
										$('.opc_block_shipping_address').removeClass('hidden');
									} else {
										$('.checkout-address-info').empty();
										$('.opc_block_shipping_address').addClass('hidden');
									}
									if (typeof self.initSelect2 == 'function') {
										self.initSelect2();
									}
									break;
								case 'payment_method':
									if(json.payment_method !== ''){
										$('.opc_block_payment_method').html(json.payment_method);
									}
									break;
								case 'customer':
									$('.opc_block_customer').html(json.customer);
									self.initMaskPhone();
									break;
								case 'cart':
									$(".cart-list").html($(json.cart).find(".cart-list").html());
									new Function(self.options.load_script)();
									break;
								case 'totals':
									if($(".opc-cart-weight").length){
										$(".opc-cart-weight").html($(json.totals).find(".opc-cart-weight").html());
									}

									var free_ship_left_html = $(json.totals).find(".free-shipping-left").html();
									var fsPercentageMatch = false;

									if(free_ship_left_html){
										fsPercentageMatch = $(json.totals).find(".free-shipping-inner").attr('data-fsl-width');
									} else {
										$('.free-shipping-left').remove();
									}

									if (fsPercentageMatch) {
										if($(".free-shipping-left").length){
											var targetWidth = parseFloat(fsPercentageMatch);

											var currentWidth = parseFloat($(".free-ship-bar-fill").css("width"));

											$(".free-ship-bar-fill").css({ width: targetWidth + "%" });

											$('.free-ship-info').html($(free_ship_left_html).find('.free-ship-info').html());

											if(targetWidth == 100){
												$('.free-ship-progress-bar').addClass('hidden');
												$('.free-ship-info').addClass('active-free-ship');
											} else {
												$('.free-ship-progress-bar').removeClass('hidden');
												$('.free-ship-info').removeClass('active-free-ship');
											}

										} else if(free_ship_left_html) {
											$('.checkout-totals').prepend('<div class="free-shipping-left">'+ free_ship_left_html +'</div>')
										}
									}

									$(".table_total").html($(json.totals).find(".table_total").html());
									break;
								case 'related_products':
									$('[id^="tooltip"]').remove();
									if($('.opc_block_related_products').length){
										$('.opc_block_related_products').html(json.related_products);
									}
									self.initRelatedSlider();
									break;
								case 'opc_errors':
									if (Object.keys(json.opc_errors).length > 0) {
										$('.opc-alert-danger').remove();

										$.each(json.opc_errors, function(errorKey, opcError) {
											html = '<div class="alert opc-alert-danger ' + errorKey + '">';
											html += '	<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">';
											html += '		<path fill-rule="evenodd" clip-rule="evenodd" d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17ZM9 18C13.9706 18 18 13.9706 18 9C18 4.02944 13.9706 0 9 0C4.02944 0 0 4.02944 0 9C0 13.9706 4.02944 18 9 18Z" fill="#d8300e"></path>';
											html += '		<path fill-rule="evenodd" clip-rule="evenodd" d="M9 4.50952C9.27614 4.50952 9.5 4.73338 9.5 5.00952V10.2151C9.5 10.4913 9.27614 10.7151 9 10.7151C8.72386 10.7151 8.5 10.4913 8.5 10.2151V5.00952C8.5 4.73338 8.72386 4.50952 9 4.50952Z" fill="#d8300e"></path>';
											html += '		<path fill-rule="evenodd" clip-rule="evenodd" d="M9 12.2985C9.27614 12.2985 9.5 12.5223 9.5 12.7985V13.6879C9.5 13.964 9.27614 14.1879 9 14.1879C8.72386 14.1879 8.5 13.964 8.5 13.6879V12.7985C8.5 12.5223 8.72386 12.2985 9 12.2985Z" fill="#d8300e"></path>';
											html += '	</svg>' + opcError + '<button type="button" class="close" data-dismiss="alert">×</button>';
											html += '</div>';

											$('#onepcheckout').before(html);
										});
									} else {
										if($('.opc-alert-danger').length){
											$('.opc-alert-danger').remove();
										}
									}

									break;
							}
						}

						self.initDateTimePicker();
					}
				}
			});
		};

		self.shippingUpdate = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked,.checkout-totals input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select').serialize();

			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/shipping_method',
				type: 'post',
				data: data,
				dataType: 'html',
				cache: false,
				beforeSend: function() {
					self.loading_mask(true);
				},
				complete: function() {
					self.loading_mask(false);
				},
				success: function(html) {
					if(html.length){
						$('.opc_block_shipping_method').html(html);
					} else {
						location = 'index.php?route=checkout/cart';
					}
				}
			});
		};

		self.paymentUpdate = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked,.checkout-totals input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select').serialize();

			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/payment_method',
				type: 'post',
				data: data,
				dataType: 'html',
				cache: false,
				success: function(html) {
					$('.opc_block_payment_method').html(html);
				}
			});
		};

		self.updateCart = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select');

			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/cart',
				type: 'post',
				data: data,
				dataType: 'html',
				cache: false,
				success: function(html) {
					$(".table_total").html($(html).find(".table_total").html());
					$(".cart-list").html($(html).find(".cart-list").html());
					$(".panel-group").html($(html).find(".panel-group").html());
					new Function(self.options.load_script)();
				}
			});
		};

		self.updateTotals = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select');

			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/totals',
				type: 'post',
				data: data,
				dataType: 'html',
				cache: false,
				success: function(html) {
					if($(".opc-cart-weight").length){
						$(".opc-cart-weight").html($(html).find(".opc-cart-weight").html());
					}
					$(".table_total").html($(html).find(".table_total").html());
				}
			});
		};

		self.shippingAddressUpdate = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked,.checkout-totals input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select').serialize();

			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/shipping_address',
				type: 'post',
				data: data,
				dataType: 'html',
				cache: false,
				complete: function() {
					self.loading_mask(false);
				},
				success: function(html) {
					if($(html).find('.checkout-address-info .row').length){
						$('.opc_block_shipping_address').html(html);
						$('.opc_block_shipping_address').removeClass('hidden');
					} else {
						$('.opc_block_shipping_address').addClass('hidden');
					}
					self.initDateTimePicker();
				}
			}).done(function() {
				if (typeof self.initSelect2 == 'function') {
					self.initSelect2();
				}
			});
		};

		self.customerUpdate = function(){
			var data = $('.checkout_form input[type=\'text\'], .checkout_form input[type=\'password\'], .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked,.checkout-totals input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select').serialize();
			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/customer',
				type: 'post',
				data: data,
				dataType: 'html',
				cache: false,
				beforeSend: function() {
					self.loading_mask(true);
				},
				complete: function() {
					self.loading_mask(false);
				},
				success: function(data) {
					$('.opc_block_customer').html(data);
					self.initMaskPhone();
					self.initDateTimePicker();
				}
			});
		};

		self.getZones = function(value){
			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/country&country_id=' + value,
				dataType: 'json',
				success: function(json) {

					html = '<option value="">'+ self.options.text_select +'</option>';

					if (json['zone'] && json['zone'] != '') {
						for (i = 0; i < json['zone'].length; i++) {
							html += '<option value="' + json['zone'][i]['zone_id'] + '"';

							if (json['zone'][i]['zone_id'] == json['active_zone_id']) {
								html += ' selected="selected"';
							}

							html += '>' + json['zone'][i]['name'] + '</option>';
						}
					}
					$('select[name=\'zone_id\']').html(html);
					self.shippingUpdate();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		};

		self.initMaskPhone = function(){
			if(self.options.tel_mask.length && $("#input-opc-telephone").length){
				$("#input-opc-telephone").mask(self.options.tel_mask);
			}

			if($('#input-opc-telephone-main').length){
				self.initIntlTelInput();
			}
		};

		self.initDateTimePicker = function(){
			if($('#onepcheckout .date').length){
				$('.date').each(function() {
					$(this).datetimepicker({
						pickTime: false,
						minDate: new Date()
					});
				});
			}

			if($('#onepcheckout .time').length){
				$('.time').each(function() {
					$(this).datetimepicker({
						pickDate: false
					});
				});
			}

			if($('#onepcheckout .datetime').length){
				$('.datetime').each(function() {
					$(this).datetimepicker({
						pickDate: true,
						pickTime: true
					});
				});
			}
		};

		self.handleCouponButtonClick = function(){
			$.ajax({
				url: 'index.php?route=extension/total/coupon/coupon',
				type: 'post',
				data: 'coupon=' + encodeURIComponent($('input[name=\'coupon\']').val()),
				dataType: 'json',
				beforeSend: function() {
					$('input[name=\'coupon\']').attr('disabled', 'disabled');
				},
				complete: function() {
					$('input[name=\'coupon\']').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert').remove();
					self.opcReloadAll();
					if (json['error']) {
						$('body').append('<div class="alert ch-alert-danger"><img class="warning-icon" alt="warning-icon" src="catalog/view/javascript/opc/image/warning-icon.svg"><div class="text-modal-block">' + json['error'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}
					if (json['success']) {
						$('body').append('<div class="alert ch-alert-success"><img class="success-icon" alt="success-icon" src="catalog/view/javascript/opc/image/success-icon.svg"><div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}
				}
			});
		};

		self.handleRewardButtonClick = function(){
			$.ajax({
				url: 'index.php?route=extension/total/reward/reward',
				type: 'post',
				data: 'reward=' + encodeURIComponent($('input[name=\'reward\']').val()),
				dataType: 'json',
				beforeSend: function() {
					$('input[name=\'reward\']').attr('disabled', 'disabled');
				},
				complete: function() {
					$('input[name=\'reward\']').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert').remove();

					if (json['error']) {
						$('body').append('<div class="alert ch-alert-danger"><img class="warning-icon" alt="warning-icon" src="catalog/view/javascript/opc/image/warning-icon.svg"><div class="text-modal-block">' + json['error'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}
					if (json['success']) {
						$('body').append('<div class="alert ch-alert-success"><img class="success-icon" alt="success-icon" src="catalog/view/javascript/opc/image/success-icon.svg"><div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}
					self.opcReloadAll();
				}
			});
		};

		self.handleVoucherButtonClick = function(){
			$.ajax({
				url: 'index.php?route=extension/total/voucher/voucher',
				type: 'post',
				data: 'voucher=' + encodeURIComponent($('input[name=\'voucher\']').val()),
				dataType: 'json',
				beforeSend: function() {
					$('input[name=\'voucher\']').attr('disabled', 'disabled');
				},
				complete: function() {
					$('input[name=\'voucher\']').removeAttr('disabled');
				},
				success: function(json) {
					$('.alert').remove();
					self.opcReloadAll();
					if (json['error']) {
						$('body').append('<div class="alert ch-alert-danger"><img class="warning-icon" alt="warning-icon" src="catalog/view/javascript/opc/image/warning-icon.svg"><div class="text-modal-block">' + json['error'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}
					if (json['success']) {
						$('body').append('<div class="alert ch-alert-success"><img class="success-icon" alt="success-icon" src="catalog/view/javascript/opc/image/success-icon.svg"><div class="text-modal-block">' + json['success'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
					}
				}
			});
		};

		self.plusMinusQty = function(elem, action){
			var $parent = elem.closest('.ch-cart-quantity');

			var key = $parent.find('input').data('key');
			var minimum = parseFloat($parent.find('input').data('minimum'));
			minimum = minimum < 1 ? 1 : minimum;
			var quantity = parseFloat($parent.find('input').val().replace(/[^\d]/g, ''));

			if (quantity === '' || quantity === 0) {
				quantity = minimum;
			} else if (action === 'plus') {
				quantity += minimum;
			} else if (action === 'minus') {
				if (quantity <= minimum) {
					quantity = minimum;
				} else {
					quantity -= minimum;
				}
			}

			$parent.find('input').val(quantity).change();
			self.updateQty(key, quantity, minimum);
		};

		self.updateQty = function(key, quantity, minimum = 1){

			if(quantity >= minimum){
				$.ajax({
					url: 'index.php?route=checkout/onepcheckout/cart_edit',
					type: 'post',
					data: 'quantity[' + key + ']='+ quantity,
					dataType: 'json',
					beforeSend: function() {
						self.loading_mask(true);
					},
					complete: function() {
						self.loading_mask(false);
					},
					success: function(json) {
						self.opcReloadAll();
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		}

		self.opcValidateQty = function(elem) {
			var input = $(elem);

			var minimum = input.data('minimum');
			var value = input.val().trim();
			var key = input.data('key');

			if (/^0/.test(value)) {
				input.val(minimum);
			} else {
				var count = value.replace(/[^\d]/g, '');
				if (count === '') count = minimum;
				if (count === '0') count = minimum;
				if (count < minimum) count = minimum;
				input.val(count);
			}

			input.change();
			self.updateQty(key, count, minimum);
		};

		self.removeProduct = function(key){
			$.ajax({
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: 'key=' + key,
				dataType: 'json',
				beforeSend: function() {
					self.loading_mask(true);
				},
				complete: function() {
					self.loading_mask(false);
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		};

		self.saveFields = function(){
			$.ajax({
				url: 'index.php?route=checkout/onepcheckout/save_fields',
				type: 'post',
				data: $('.checkout_form input[type=\'text\']:not([id*=\'input_pr_quantity_\']), .checkout_form input[type=\'hidden\'], .checkout_form input[type=\'checkbox\']:checked, .checkout_form input[type=\'radio\']:checked, .checkout_form textarea, .checkout_form select'),
				cache: false,
			});
		};

		self.initSelect2 = function () {
			$('.opc_block_shipping_address').find("select[data-type=select2]").each(function() {
				$(this).select2();
			});
		};

		self.authorization = function () {
			$(document).on('click', '.opc_login', function (e) {
				e.preventDefault();
				$.ajax({
					type:'get',
					url:'index.php?route=checkout/onepcheckout/authorization',
					beforeSend: function() {
						self.loading_mask(true);
					},
					complete: function() {
						self.loading_mask(false);
					},
					success:function (data) {
						$('html body').append('<div id="login-form-popup" class="modal fade" role="dialog">'+ data +'</div>');
						$('#login-form-popup').modal('show');
						self.validateAuthorization();
						$(document).on('hide.bs.modal', '#login-form-popup.modal.fade', function () {
							$('#login-form-popup').remove();
						});
					}
				});
			});
		};

		self.validateAuthorization = function () {
			$(document).on('click', '#button-login-popup', function (e) {
				e.preventDefault();
				$.ajax({
					url: 'index.php?route=checkout/onepcheckout/validate_authorization',
					type: 'post',
					data: $('#opc_authorization input'),
					dataType: 'json',
					beforeSend: function() {
						self.loading_mask(true);
					},
					complete: function() {
						self.loading_mask(false);
					},
					success: function(json) {
						$('.alert.ch-alert-danger').remove();

						if(json['islogged']){
							window.location.href="index.php?route=account/account";
						}
						if (json['error']) {
							$('body').append('<div class="alert ch-alert-danger"><img class="success-icon" alt="success-icon" src="catalog/view/javascript/opc/image/warning-icon.svg"><div class="text-modal-block">' + json['error'] + '</div><button type="button" class="close" data-dismiss="alert"><i class="up-icon-close" aria-hidden="true"></i></button></div>');
						}

						setTimeout(function () {
							$('.ch-alert-danger').remove();
						}, 3000);

						if(json['success']){
							location.reload();
							$('#login-form-popup').modal('hide');
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		};

		self.addTopCartRight = function () {
			if(self.viewport().width > 991){
				if($('header').hasClass('fix-header')){
					$('.checkout-col-fix-right').css('top', document.querySelector('header').clientHeight + 30);
				} else {
					$('.checkout-col-fix-right').css('top', 30);
				}
			} else {
				$('.checkout-col-fix-right').css('top', 0);
			}
		};

		self.response = function () {
			var base = this,
			smallDelay,
			lastWindowWidth;

			lastWindowWidth = $(window).width();
			self.addTopCartRight();
			base.resizer = function () {
				if ($(window).width() !== lastWindowWidth) {

					window.clearTimeout(smallDelay);
					smallDelay = window.setTimeout(function () {
						lastWindowWidth = $(window).width();
						self.addTopCartRight();
					}, 200);
				}
			};
			$(window).resize(base.resizer);
		};

		self.loading_mask = function(action){
			if (action) {
				if(!$('.loading_mask').length){
					$('body').append('<div class="loading_mask"></div>');
				}
				$('.loading_mask').html('<div class="center-body"><div class="opc-loader-circle"></div></div>');
				$('.loading_mask').show();
			} else {
				$('.loading_mask').html('');
				$('.loading_mask').hide();
			}
		};

		self.initRelatedSlider = function(){
			var swiperCarousel = new Swiper('.carousel_related_prodcuts', {
				slidesPerView: 2,
				watchSlidesVisibility: true,
				watchSlidesProgress: true,
				watchOverflow: true,
				observer: true,
				observeParents: true,
				spaceBetween: 20,
				nested: false,
				speed: 400,
				breakpointsBase: 'container',
				grabCursor: true,
				navigation: {
					enabled: false,
				},
				scrollbar: {
				  el: '.carousel-related-scrollbar',
				  draggable: true,
				},
				on: {
					afterInit: function () {
						setTimeout(function () {
							$('.carousel_related_prodcuts').addClass('swiper-visible');
						}, 500);

					},
				},
				breakpoints: {
				  400 : {slidesPerView: 2},
				  600 : {slidesPerView: 3},
				  740: {slidesPerView: 4},
				  992: {slidesPerView: 5},
				  1200: {slidesPerView: 6}
				}
			});
		};

		self.initIntlTelInput = function () {
			var initialCountry = self.options.initial_country;
			var onlyCountries = formatCountryCodes(self.options.only_countries);
			var excludeCountries = formatCountryCodes(self.options.exclude_countries);
			var geoipStatus = self.options.geoip_status;

			var $input = $("#input-opc-telephone-main");
			var $phoneFullInput = $("#telephone_full");
			var $countryCodeInput = $("#country_code");

			if (!window.intlTelInput || !jQuery.fn.mask) {
				console.error("intlTelInput или jQuery.mask не загружены.");
				return;
			}

			if(geoipStatus){
				onlyCountries = [];
			}

			var itiOptions = {
				initialCountry: initialCountry,
				excludeCountries: excludeCountries,
				onlyCountries: onlyCountries,
				customPlaceholder: function(selectedCountryPlaceholder, selectedCountryData) {
					var mask = generateMask(selectedCountryPlaceholder, selectedCountryData);
					$input.mask(mask);
					return selectedCountryPlaceholder.replace(/\d/g, "_").replace(/\s/g, "-");
				},
				containerClass: "opc-tel-mask",
				placeholderNumberType: "MOBILE",
				separateDialCode: true,
				useFullscreenPopup: false,
				// i18n: {
				// 	searchPlaceholder: 'Поиск'
				// },
			};

			if(geoipStatus){
				itiOptions.geoIpLookup = function(callback) {
					fetch("https://ipapi.co/json")
					.then(function(res) { return res.json(); })
					.then(function(data) { callback(data.country_code); })
					.catch(function() { callback(); });
				};
			}

			var iti = window.intlTelInput($input[0], itiOptions);

			function formatCountryCodes(countryString) {
				if (!countryString.trim()) {
					return [];
				}

				return countryString.split(',').map(function(country) {
					return country.trim();
				});
			}

			function generateMask(placeholder, selectedCountryData) {
				return placeholder.replace(/\d/g, "9").replace(/\s/g, "-");
			}

			function sanitizeNumber(number) {
				return number.replace(/[^\d+]/g, '');
			}

			function handlePhoneInput() {
				let number = iti.getNumber();
				if (iti.isValidNumber()) {
					$phoneFullInput.val(sanitizeNumber(number));
				} else {
					$phoneFullInput.val('');
				}
			}

			var countryData = iti.getSelectedCountryData();
			$countryCodeInput.val(countryData.dialCode);

			$input.on("countrychange", function () {
				const countryData = iti.getSelectedCountryData();
				handlePhoneInput();
				$countryCodeInput.val(countryData.dialCode);
			});

			$input.on("blur", function () {
				handlePhoneInput();
				$countryCodeInput.val(iti.getSelectedCountryData().dialCode);
			});

			$input.on("input", function () {
				handlePhoneInput();
				$countryCodeInput.val(iti.getSelectedCountryData().dialCode);
			});
		};

		self.viewport = function(){
			let e = window, a = 'inner';
			if (!('innerWidth' in window )) {
				a = 'client';
				e = document.documentElement || document.body;
			}
			return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
		}
	}

	window.OnePageCheckout = OnePageCheckout;
})(jQuery);
