<?php
class ModelExtensionModuleUpstoreKits extends Model {

	public function getVariantKit($product_id) {
		$variant_kit = array();
		$query = $this->db->query("SELECT DISTINCT count(`kp`.`vk_id`) AS total_prod, `kp`.`vk_id`, `k`.`kit_id`, k.product_id AS main_product, `kp`.`product_id`, `kp`.`type_discount`, `kp`.`discount`, `kp`.`sort_order`, `kp`.`options` FROM `" . DB_PREFIX . "kits` `k` LEFT JOIN (SELECT * FROM `" . DB_PREFIX . "kits_products` `kp` ORDER BY `kp`.`sort_order` ASC) `kp` ON (`k`.`kit_id` = `kp`.`kit_id`) WHERE `k`.`product_id` = '" . (int)$product_id . "' AND `k`.`status` = '1' GROUP BY `kp`.`vk_id` ORDER BY `kp`.`vk_id`, `kp`.`id` ASC");

		if(!empty($query->rows)){
			foreach ($query->rows as $result) {

				$variant_kit[$result['vk_id']] = array(
					'vk_id'				=> $result['vk_id'],
					'products'			=> array(array('product_id' => $result['main_product'],'option_price' => 0)),
					'kit_id'				=> $result['kit_id'],
					'type_discount'	=> $result['type_discount'],
					'discount'			=> $result['discount'],
					'main_product'		=> $result['main_product'],
					'product_id'		=> $result['product_id'],
				);


				$products = array(
					'product_id' 	=> $result['product_id'],
					'options' 		=> (!empty($result['options'])) ? json_decode($result['options'], true) : array(),
					'option_price' => $this->getTotalOptionsPrice((!empty($result['options'])) ? json_decode($result['options'], true) : array(), $result['product_id']),
					'total_prod' 	=> $result['total_prod']
				);

				$variant_kit[$result['vk_id']]['products'][] = $products;
			}
		}
		return $variant_kit;
	}

	public function getTotalOptionsPrice($options,$product_id){
		$option_price = 0;

		foreach ($options as $product_option_id => $value) {
			$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "'  AND `o`.`type` IN('select', 'radio', 'checkbox') AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");
			if ($option_query->num_rows) {
				if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
					$option_value_query = $this->db->query("SELECT pov.option_value_id, pov.quantity, pov.price, pov.price_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
					if ($option_value_query->num_rows) {
						if ($option_value_query->row['price_prefix'] == '+') {
							$option_price += $option_value_query->row['price'];
						} elseif ($option_value_query->row['price_prefix'] == '-') {
							$option_price -= $option_value_query->row['price'];
						}
					}
				} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
					foreach ($value as $product_option_value_id) {
						$option_value_query = $this->db->query("SELECT pov.option_value_id, pov.quantity, pov.price, pov.price_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
						if ($option_value_query->num_rows) {
							if ($option_value_query->row['price_prefix'] == '+') {
								$option_price += $option_value_query->row['price'];
							} elseif ($option_value_query->row['price_prefix'] == '-') {
								$option_price -= $option_value_query->row['price'];
							}
						}
					}
				}
			}
		}
		return $option_price;
	}

	public function getVariantProductsKits($kit_id, $vk_id){
		$query = $this->db->query("SELECT DISTINCT `kp`.`vk_id`, `k`.`kit_id`, k.product_id AS main_product, `kp`.`product_id`, `kp`.`type_discount`, `kp`.`discount`, `kp`.`options` FROM `" . DB_PREFIX . "kits` `k` LEFT JOIN (SELECT * FROM `" . DB_PREFIX . "kits_products` `kp` ORDER BY `kp`.`sort_order` ASC) `kp` ON (`k`.`kit_id` = `kp`.`kit_id`) WHERE `k`.`kit_id` = '" . (int)$kit_id . "' AND `k`.`status` = '1' AND `kp`.`vk_id` = '" . (int)$vk_id . "' ORDER BY `kp`.`vk_id`, `kp`.`id` ASC");

		return !empty($query->rows) ? $query->rows : array();
	}

	public function getInfoKit($kit_id, $vk_id, $product_id){
		$query = $this->db->query("SELECT `k`.`product_id` AS `main_product`, `kp`.`vk_id`, `kp`.`kit_id`, `kp`.`product_id`, `kp`.`type_discount`, `kp`.`discount`, `kp`.`options` ,(SELECT  COUNT(`kp2`.`vk_id`) FROM `" . DB_PREFIX . "kits_products` `kp2` WHERE `kp2`.`kit_id` = '" . (int)$kit_id . "' AND `kp2`.`vk_id` = '" . (int)$vk_id . "' LIMIT 1) AS total_prod FROM `" . DB_PREFIX . "kits_products` `kp` LEFT JOIN `" . DB_PREFIX . "kits` `k` ON (`kp`.`kit_id` = `k`.`kit_id`) WHERE `kp`.`kit_id` = '" . (int)$kit_id . "' AND `kp`.`vk_id` = '" . (int)$vk_id . "' AND `kp`.`product_id` = '" . (int)$product_id . "' AND `k`.`status` = '1' LIMIT 1");

		return !empty($query->row) ? $query->row : array();
	}

	public function checkKit($kit_id, $vk_id, $product_id){
		$query = $this->db->query("SELECT `p`.`tax_class_id`, `k`.`product_id` AS `main_product`, `kp`.`vk_id`, `kp`.`kit_id`, `kp`.`product_id`, `kp`.`type_discount`, `kp`.`discount`, `kp`.`options` ,(SELECT  COUNT(`kp2`.`vk_id`) FROM `" . DB_PREFIX . "kits_products` `kp2` WHERE `kp2`.`kit_id` = '" . (int)$kit_id . "' AND `kp2`.`vk_id` = '" . (int)$vk_id . "' LIMIT 1) AS total_prod FROM `" . DB_PREFIX . "kits_products` `kp` LEFT JOIN `" . DB_PREFIX . "kits` `k` ON (`kp`.`kit_id` = `k`.`kit_id`) INNER JOIN `" . DB_PREFIX . "product` `p` ON (`kp`.`product_id` = `p`.`product_id`) WHERE `kp`.`kit_id` = '" . (int)$kit_id . "' AND `kp`.`vk_id` = '" . (int)$vk_id . "' AND `kp`.`product_id` = '" . (int)$product_id . "' AND `k`.`status` = '1' LIMIT 1");

		return !empty($query->row) ? $query->row : array();
	}

	public function getKitDiscount($skit, $cartProducts){

		$query = $this->db->query("SELECT DISTINCT `kp`.`vk_id`, `k`.`kit_id`, k.product_id AS main_product, `kp`.`product_id`, `kp`.`type_discount`, `kp`.`discount` FROM `" . DB_PREFIX . "kits` `k` LEFT JOIN `" . DB_PREFIX . "kits_products` `kp` ON (`k`.`kit_id` = `kp`.`kit_id`) WHERE `k`.`kit_id` = '" . (int)$skit['kit_id'] . "' AND `kp`.`vk_id` = '" . (int)$skit['vk_id'] . "' AND `kp`.`product_id` = '" . (int)$skit['product_id'] . "' AND `k`.`product_id` = '" . (int)$skit['main_product'] . "' AND `k`.`status` = '1' LIMIT 1");

		$total = 0;
		$status_kit = false;
		$status_kit_main = false;

		if ($query->num_rows) {

			foreach($cartProducts as $key => $product){
				if(!$status_kit){
					if($product['product_id'] == $query->row['product_id'] && $product['quantity'] > 0){
						if(!empty($product['option'])){
							$check_option = array_diff_assoc($product['option'], $skit['option']);
							if(empty($check_option)){
								$total += $product['price'];
								$cartProducts[$key]['quantity'] = $cartProducts[$key]['quantity'] - 1;
								$status_kit = true;
							} else {
								$status_kit = false;
							}
						} else {
							$total += $product['price'];
							$cartProducts[$key]['quantity'] = $cartProducts[$key]['quantity'] - 1;
							$status_kit = true;
						}
					}
				}

				if(!$status_kit_main){
					if($product['product_id'] == $query->row['main_product'] && $product['quantity'] > 0){
						if(!empty($product['option'])){
							$check_option = array_diff_assoc($product['option'], $skit['option_main']);
							if(empty($check_option)){
								$total += $product['price'];
								$cartProducts[$key]['quantity'] = $cartProducts[$key]['quantity'] - 1;
								$status_kit_main = true;
							} else {
								$status_kit_main = false;
							}
						} else {
							$total += $product['price'];
							$cartProducts[$key]['quantity'] = $cartProducts[$key]['quantity'] - 1;
							$status_kit_main = true;
						}
					}
				}
			}

		}

		if($status_kit && $status_kit_main){
			if ($query->row['type_discount']) {
				$data['discount'] = (int)$query->row['discount'];
			}else{
				$data['discount'] = $total / 100 * (int)$query->row['discount'];
			}
		} else {
			$data['discount'] = false;
		}

		$data['products'] = $cartProducts;

		return $data;
	}
}