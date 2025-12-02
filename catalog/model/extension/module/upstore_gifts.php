<?php
class ModelExtensionModuleUpstoreGifts extends Model {
	public function getGiftMaxCnt($product_id) {
		$query = $this->db->query("SELECT `max_cnt` FROM `" . DB_PREFIX . "product_gifts` WHERE `product_id` = '" . (int)$product_id . "'");

		if($query->num_rows){
			return $query->row['max_cnt'];
		}
	}

	public function getGift($product_id) {

		$this->load->model('tool/image');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_gifts` WHERE `product_id` = '" . (int)$product_id . "'");

		$data_gift = array();
		if($query->num_rows){
			$product_gift_data = array();

			$products = (!empty($query->row['products'])) ? json_decode($query->row['products'], true) : array();
			if(!empty($products)){

				foreach ($products as $product_id) {
					$implode[] = (int)$product_id;
				}

				$query_products = $this->db->query("SELECT `p`.`product_id`, `pd`.`name`, `p`.`image` FROM `" . DB_PREFIX . "product` `p` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON(`p`.`product_id` = `pd`.`product_id`) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE `pd`.`product_id` IN (" . implode(',', $implode) . ") AND `pd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.quantity > 0");

				if($query_products->num_rows){
					foreach($query_products->rows as $product){

						if ($product['image']) {
							$image = $this->model_tool_image->resize($product['image'], 56, 56);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', 56, 56);
						}

						$product_gift_data[] = array(
							'name'			=> $product['name'],
							'image'			=> $image,
							'product_id'	=> $product['product_id'],
						);
					}
				}
				if(!empty($query->row['title'])){
					$title =  json_decode($query->row['title'], true);
				}
				$data_gift = array(
					'product_id'	=> $query->row['product_id'],
					'status'			=> $query->row['status'],
					'title'			=> $title[$this->config->get('config_language_id')],
					'max_cnt'		=> $query->row['max_cnt'],
					'products'		=> $product_gift_data,
				);
			}
		}

		$this->cache->set('product_gift.' . (int)$product_id . '.'. (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $data_gift);
		return $data_gift;
	}

}