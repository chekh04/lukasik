<?php
class ModelExtensionTotalKitDiscount extends Model {
	public function getTotal($total) {

		$this->load->model('extension/module/upstore_kits');
		$this->load->language('extension/total/kit_discount');

		$cartProducts = array();
		$options = array();
		foreach($this->cart->getProducts() as $product){
			$option_data = array();
			foreach($product['option'] as $option){
				$option_data[$option['product_option_id']] = (!empty($option['product_option_value_id'])) ? $option['product_option_value_id'] : $option['name'];
			}

			$cartProducts[] = array(
				'product_id'	=> $product['product_id'],
				'quantity'		=> $product['quantity'],
				'price'			=> $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
				'option'			=> $option_data
			);
		}

		$skits = array();
		if (isset($this->session->data['skits']) && is_array($this->session->data['skits'])) {
			$skits = $this->session->data['skits'];
		}

		$total_discount = 0;
		foreach ($skits as $key => $skit) {
			$kit = $this->model_extension_module_upstore_kits->getKitDiscount($skit, $cartProducts);


			if($kit['discount'] > 0){
				$total_discount += $kit['discount'];
			} else {
				unset($this->session->data['skits'][$key]);
			}
			$cartProducts = $kit['products'];
		}


		if($total_discount > 0){
			$total['totals'][] = array(
				'code'       => 'kit_discount',
				'title'      => $this->language->get('text_kit_discount'),
				'value'      => -$total_discount,
				'sort_order' => $this->config->get('total_kit_discount_sort_order')
			);

			$total['total'] -= (float)$total_discount;
		}
	}
}
