function quickorder_confirm() {

	$('#quickorder_url').val(window.location.href);
	$.ajax({
		url: 'index.php?route=extension/module/upstore_newfastorder/addFastOrder',
		type: 'post',
		data: $('#fastorder_data').serialize() + '&action=send',
		dataType: 'json',
		beforeSend: function() {
			creatOverlayLoadPage(true);
			$('#up-btn-fastorder').data('original-content', $('#up-btn-fastorder').html());
			$('#up-btn-fastorder').html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
		},
		complete: function() {
			setTimeout(function () {
				creatOverlayLoadPage(false);
				$('#up-btn-fastorder').html($('#up-btn-fastorder').data('original-content')).prop('disabled', false);
			}, 300);
		},
		success: function(json) {
			$('#modal-quickorder .form-group').removeClass('has-error');
			$('#modal-quickorder .form-control').removeClass('error_input');
			$('#modal-quickorder .form-control').removeClass('success_input');
			$('#modal-quickorder').find('.us-error-agree').removeClass('us-error-agree');
			$('#modal-quickorder .text-danger,.us-success-icon,.us-error-icon,.us-text-error').remove();
			$('#modal-quickorder .form-group').removeClass('option-error');
			if (json['error']) {

				if (json['error']) {
					handleFieldNotifications('#modal-quickorder', json['error']);
				}

				if (json['error']['option']) {
					for (i in json['error']['option']) {
						var element = $('#modal-quickorder #input-option' + i.replace('_', '-'));

						if (element.parent().hasClass('input-group')) {
							element.parent().parent().addClass('option-error');
							element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
						} else {
							element.parent().addClass('option-error');
							element.after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
						}
					}
				}
			}

			if (json['success']){
				$('#modal-quickorder').modal('hide');

				showModalWithMessage(json['success']);

				$(document).on('hide.bs.modal', '#modal-addquickorder.modal.fade', function () {
					$('#modal-addquickorder').remove();
				});
			}
		}

	});
}

function quickorder_confirm_checkout() {
	$('#quickorder_url').val(window.location.href);

	$.ajax({
		url: 'index.php?route=extension/module/upstore_newfastordercart/addFastOrder',
		type: 'post',
		data: $('#fastorder_data').serialize() + '&action=send',
		dataType: 'json',
		beforeSend: function() {
			creatOverlayLoadPage(true);
		},
		complete: function() {
			setTimeout(function () {
				creatOverlayLoadPage(false);
			}, 500);
		},
		success: function(json) {
			$('#modal-quickorder .form-group').removeClass('has-error');
			$('#modal-quickorder .form-control').removeClass('error_input');
			$('#modal-quickorder .form-control').removeClass('success_input');
			$('#modal-quickorder').find('.us-error-agree').removeClass('us-error-agree');
			$('#modal-quickorder .text-danger,.us-success-icon,.us-error-icon,.us-text-error').remove();
			$('#modal-quickorder .form-group').removeClass('option-error');

			if (json['error']) {
				if (json['error']) {
					handleFieldNotifications('#modal-quickorder', json['error']);
				}
			}

			if (json['success']){
				$('.shopping-cart #cart').load('index.php?route=common/cart/info .shopping-cart #cart');

				$('#modal-quickorder').modal('hide');

				showModalWithMessage(json['success']);

				$(document).on('hide.bs.modal', '#modal-addquickorder.modal.fade', function () {
					$('#modal-addquickorder').remove();
				});
			}
		}

	});
}