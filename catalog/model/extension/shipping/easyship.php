<?php
class ModelExtensionShippingEasyship extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/easyship');

		$totals 	= $this->getTotals();
		$sub_total  = $totals['total'];
		$settings = $this->config->get('shipping_easyship');
		$method_data = array();

		foreach ((array)$settings as $sg_code => $group) {

			if ($group['status'] == 'off'){
				continue;
			}
			$quote_data = array();
			foreach ($group['shipping_methods'] as $code => $method) {

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$method['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($method['geo_zone_id'] && !$query->num_rows) {
					$status = false;
				} elseif ($sub_total < $method['minimum_order_amount']) {
					$status = false;
				} elseif ($method['maximum_order_amount'] && $sub_total > $method['maximum_order_amount']) {
					$status = false;
				} else {
					$status = true;
				}

				if($method['status'] == 'off'){
					$status = false;
				}

				if ($status) {
					$cost = 0;
					if (!empty((int)$method['cost'])) {
						$cost = (int)$method['cost'];
					}
					if (!empty($method['name'][$this->config->get('config_language_id')])) {
						$description = $method['name'][$this->config->get('config_language_id')];
					} else {
						$description = '';
					}

					if ($cost && (empty($method['free_shipping']) || $sub_total < $method['free_shipping'])) {
						if($cost){
							$text = $this->currency->format($this->tax->calculate($cost, 0, $this->config->get('config_tax')), $this->session->data['currency']);
						} elseif(!empty($method['shipping_text'][$this->config->get('config_language_id')])){
							$text = $method['shipping_text'][$this->config->get('config_language_id')];
						} else {
							$text = '';
						}
					} elseif ($method['free_shipping'] && $sub_total >= $method['free_shipping']) {
						$text = $method['free_shipping_text'][$this->config->get('config_language_id')];
						$cost = 0;
					} else {
						if(!empty($method['shipping_text'][$this->config->get('config_language_id')])){
							$text = $method['shipping_text'][$this->config->get('config_language_id')];
						} else {
							$text = '';
						}
					}

					$quote_data[$code] = array(
						'code'			=> $sg_code . '.' . $code,
						'title'			=> $description,
						'cost'			=> $cost,
						'tax_class_id'	=> 0,
						'text'			=> $text,
					);
				}
			}

			if (!empty($group['group_name'][$this->config->get('config_language_id')])) {
				$title = $group['group_name'][$this->config->get('config_language_id')];
			} else {
				$title = '';
			}

			if ($quote_data) {
				$method_data[$sg_code] = array(
					'code'       => $sg_code,
					'title'      => $title,
					'quote'      => $quote_data,
					'sort_order' => $this->config->get('shipping_easyship_sort_order'),
					'error'      => false
				);
			}
		}

		return $method_data;
	}

	private function getTotals() {
		$extensions = array();
		$total      = 0;
		$totals     = array();
		$taxes      = $this->cart->getTaxes();

		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		if (version_compare(VERSION, '2', '<') || version_compare(VERSION, '3', '>=')) {
			$this->load->model('setting/extension');

			$result = $this->model_setting_extension->getExtensions('total');
		} else {
			$this->load->model('extension/extension');

			$result = $this->model_extension_extension->getExtensions('total');
		}

		foreach ($result as $k => $v) {
			if (version_compare(VERSION, '3', '>=')) {
				if ($this->config->get('total_' . $v['code'] . '_status')) {
					$extensions[$this->config->get('total_' . $v['code'] . '_sort_order')] = $v;
				}
			} else {
				if ($this->config->get($v['code'] . '_status')) {
					$extensions[$this->config->get($v['code'] . '_sort_order')] = $v;
				}
			}
		}

		ksort($extensions);

		foreach ($extensions as $v) {
			if ($v['code'] == 'shipping') {
				continue;
			}

			if (version_compare(VERSION, '2.3', '>=')) {
				$this->load->model('extension/total/' . $v['code']);

				$this->{'model_extension_total_' . $v['code']}->getTotal($total_data);
			} elseif (version_compare(VERSION, '2.2', '>=')) {
				$this->load->model('total/' . $v['code']);

				$this->{'model_total_' . $v['code']}->getTotal($total_data);
			} else {
				$this->load->model('total/' . $v['code']);

				$this->{'model_total_' . $v['code']}->getTotal($totals, $total, $taxes);
			}
		}

		return $total_data;
	}
}