<?php
class ModelExtensionModuleUpstorePriceHistory extends Model {

	public function getPriceHistory($product_info) {

		$currency_code = $this->session->data['currency'];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_price_history WHERE product_id = '" . (int)$product_info['product_id'] . "' ORDER BY date_added ASC");

		$price_history_data = $query->rows;

		if(!empty($price_history_data) && $query->num_rows > 1){
			$today = date('Y-m-d');

			$current_date = null;

			foreach ($price_history_data as $result) {
				$date_added = date('Y-m-d', strtotime($result['date_added']));
				if ($date_added === $today) {
					$current_date = $result;
					break;
				}
			}

			if (!$current_date) {
				$current_price = $product_info['price'];
				$price_history_data[] = [
					'date_added' => $today,
					'price' => $current_price
				];
			}

			if(empty($price_history_data)){
				return;
			}

			$labels = [];
			$prices = [];
			$formatted_prices = [];

			foreach ($price_history_data as $item) {
				if (isset($item[$currency_code]) && (float)$item[$currency_code] > 0) {
					$currency_value = $item[$currency_code];
				} else {
					$currency_value = '';
				}

				$date = new DateTime($item['date_added']);
				$labels[] = $date->format('Y-m-d');
				$prices[] = $this->currency->format(
					$this->tax->calculate(
						$item['price'],
						$product_info['tax_class_id'],
						$this->config->get('config_tax')
					),
					$currency_code,
					$currency_value,
					false
				);

				$formatted_prices[] = $this->currency->format(
					$this->tax->calculate(
						$item['price'],
						$product_info['tax_class_id'],
						$this->config->get('config_tax')
					),
					$currency_code,
					$currency_value
				);
			}

			return json_encode([
				'labels' => $labels,
				'prices' => $prices,
				'formatted_prices' => $formatted_prices,


			]);

		} else {
			return false;
		}
	}
}
?>