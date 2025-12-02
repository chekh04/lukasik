                  <label class="col-sm-2 control-label" for="input-manufacturer"><span data-toggle="tooltip" title="<?php echo$entry_help_manufacturer; ?>"><?php echo $entry_manufacturer; ?></span></label>
                  <div class="col-sm-10">
                    <input type="text" name="manufacturer" value="" placeholder="<?php echo $entry_manufacturer; ?>" id="input-manufacturer" class="form-control" autocomplete="off">
                    <div id="coupon-manufacturer" class="well well-sm" style="height: 150px; overflow: auto;">
                      <?php foreach ($coupon_manufacturers as $coupon_manufacturer) { ?>
                      <div id="coupon-manufacturer<?php echo $coupon_manufacturer['manufacturer_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $coupon_manufacturer['name']; ?>
                      <input type="hidden" name="coupon_manufacturer[]" value="<?php echo $coupon_manufacturer['manufacturer_id']; ?>" />
                      </div>
                    <?php } ?>
                    </div>
                  </div>
              </div>
 <script>
// Manufacturer
$('input[name=\'manufacturer\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/manufacturer/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['manufacturer_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'manufacturer\']').val('');
		
		$('#coupon-manufacturer' + item['value']).remove();
		
		$('#coupon-manufacturer').append('<div id="coupon-manufacturer' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="coupon_manufacturer[]" value="' + item['value'] + '" /></div>');
	}	
});

$('#coupon-manufacturer').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
</script>			  
              <div class="form-group">
