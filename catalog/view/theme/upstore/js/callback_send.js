function sendCallback() {
	$('#callback_url').val(window.location.href);
	$.ajax({
		url: 'index.php?route=extension/module/upstore_callback',
		type: 'post',
		data: $('#callback_data').serialize() + '&action=send',
		dataType: 'json',
		beforeSend: function() {
			creatOverlayLoadPage(true);
			$('#up-btn-callback').data('original-content', $('#up-btn-callback').html());
			$('#up-btn-callback').html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
		},
		complete: function() {
			setTimeout(function () {
				creatOverlayLoadPage(false);
				$('#up-btn-callback').html($('#up-btn-callback').data('original-content')).prop('disabled', false);
			}, 300);
		},
		success: function(json) {
			$('#popup-callback .form-control').removeClass('error_input');
			$('#popup-callback .form-control').removeClass('success_input');
			$('#popup-callback').find('.us-error-agree').removeClass('us-error-agree');
			$('.alert.ch-alert-danger,.us-success-icon,.us-error-icon,.us-text-error').remove();

			if (json['warning']) {
				handleFieldNotifications('#modal-callback', json['warning']);
			}

			if (json['success']){

				$('#modal-callback').modal('hide');

				showModalWithMessage(json['success']);

				$(document).on('hide.bs.modal', '#modal-success-callback.modal.fade', function () {
					$('#modal-success-callback').remove();
				});
			}
		}
	});
}