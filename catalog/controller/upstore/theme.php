<?php
class ControllerUpstoreTheme extends Controller {

	public function updateLogo() {
		$this->load->model('tool/image');

		$theme = isset($this->request->post['theme']) && $this->request->post['theme'] == 'dark-theme' ? 'dark' : 'light';

		$server = $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;

		$theme_logo = $theme === 'dark' ? $this->config->get('config_dark_theme_logo') : $this->config->get('config_logo');
		$mobile_logo = $theme === 'dark' ? $this->config->get('config_fm_dark_theme_logo') : $this->config->get('fm_logo');

		$logo_width = (!empty($this->config->get('theme_' . $this->config->get('config_theme') . '_image_logo_width')) ? $this->config->get('theme_' . $this->config->get('config_theme') . '_image_logo_width') : '');
		$logo_height = (!empty($this->config->get('theme_' . $this->config->get('config_theme') . '_image_logo_height')) ? $this->config->get('theme_' . $this->config->get('config_theme') . '_image_logo_height') : '');

		$mobile_logo_width = !empty($this->config->get('fm_logo_width')) ? $this->config->get('fm_logo_width') : '';
		$mobile_logo_height = !empty($this->config->get('fm_logo_height')) ? $this->config->get('fm_logo_height') : '';

		$data = [
			'logo' => '',
			'fm_logo' => '',
		];

		if (!empty($theme_logo) && is_file(DIR_IMAGE . $theme_logo)) {
			if (!empty($logo_width) && !empty($logo_height)) {
				$data['logo'] = $this->model_tool_image->resize($theme_logo, $logo_width, $logo_height);
				$data['logo_iwh_status'] = true;
			} else {
				$data['logo'] = $server . 'image/' . $theme_logo;
			}
		}

		if (!empty($mobile_logo) && is_file(DIR_IMAGE . $mobile_logo)) {
			if (!empty($mobile_logo_width) && !empty($mobile_logo_height)) {
				$data['fm_logo'] = $this->model_tool_image->resize($mobile_logo, $mobile_logo_width, $mobile_logo_height);
			} else {
				$data['fm_logo'] = $server . 'image/' . $mobile_logo;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function removeWishlist() {

		$this->load->language('upstore/theme');
		$this->load->language('account/wishlist');
		$this->load->model('account/wishlist');
		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if($this->customer->isLogged()){
				$this->model_account_wishlist->deleteWishlist($product_id);
				$json['total'] = $this->model_account_wishlist->getTotalWishlist();
			} else {
				unset($this->session->data['wishlist'][array_search($product_id,$this->session->data['wishlist'])]);
				$json['total'] = (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);
			}

			$json['success'] = sprintf($this->language->get('text_wremove'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getAllWishlist() {

		$json = array();

		$this->load->model('upstore/theme');
		$json['all_wishlist'] = $this->model_upstore_theme->getAllWishlist();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeCompare() {

		$this->load->language('upstore/theme');
		$this->load->language('product/compare');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			$key = array_search($product_id, $this->session->data['compare']);

			if ($key !== false) {
				unset($this->session->data['compare'][$key]);

				$json['success'] = sprintf($this->language->get('text_cremove'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('product/compare'));
			}

		}
		$json['total'] = (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getAllCompare() {

		$json = array();

		$this->load->model('upstore/theme');
		$json['all_compare'] = isset($this->session->data['compare']) ? implode(",", $this->session->data['compare']) : array();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function checkOptions() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->load->language('upstore/theme');

			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$data['text_select_options'] = $this->language->get('text_select_options');
			$data['button_cart'] = $this->language->get('button_cart');
			$data['text_select'] = $this->language->get('text_select');

			$data['config_additional_settings_upstore'] = $this->config->get('config_additional_settings_upstore');
			$show_options_required = (!empty($this->config->get('config_show_options_required')) ? $this->config->get('config_show_options_required') : false);

			$var_autocalc = array();
			$currency_code = $this->session->data['currency'];
			$var_autocalc['value'] = $this->currency->getValue($currency_code);
			$var_autocalc['symbol_left'] = $this->currency->getSymbolLeft($currency_code);
			$var_autocalc['symbol_right'] = $this->currency->getSymbolRight($currency_code);
			$var_autocalc['decimals'] = $this->currency->getDecimalPlace($currency_code);
			$var_autocalc['decimal_point'] = $this->language->get('decimal_point');
			$var_autocalc['thousand_point'] = $this->language->get('thousand_point');
			$data['currency_autocalc'] = $var_autocalc;

			$json = array();

			$product_id = 0;
			$data['product_id'] = 0;
			if (isset($this->request->post['product_id'])) {
				 $product_id = $this->request->post['product_id'];
				 $data['product_id'] = $product_id;
			}
			$data['quantity'] = 1;
			if (isset($this->request->post['quantity'])) {
				 $data['quantity'] = $this->request->post['quantity'];
			}

			$product_info = $this->model_catalog_product->getProduct($product_id);
			if ($product_info) {
				if (!isset($this->request->post['check_option'])) {
					foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
						$product_option_value_data = array();

						foreach ($option['product_option_value'] as $option_value) {
							if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
								if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
									$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
								} else {
									$price = false;
								}

								$product_option_value_data[] = array(
									'color'    				  => $option_value['color'],
									'image_thumb' 			  => $this->model_tool_image->resize($option_value['image'], 60, 60),
									'price_value'			  => $option_value['price']*$this->currency->getValue($this->session->data['currency']),
									'product_option_value_id' => $option_value['product_option_value_id'],
									'option_value_id'         => $option_value['option_value_id'],
									'name'                    => $option_value['name'],
									'image'                   => $option_value['image'] ? $this->model_tool_image->resize($option_value['image'], 50, 50) : '',
									'price'                   => $price,
									'price_prefix'            => $option_value['price_prefix']
								);
							}
						}

						if($show_options_required && !$option['required']){
							continue;
						}

						$data['options'][] = array(
							'status_color_type'    => $option['status_color_type'],
							'product_option_id'    => $option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $option['option_id'],
							'name'                 => $option['name'],
							'type'                 => $option['type'],
							'value'                => $option['value'],
							'required'             => $option['required']
						);
					}
				}
			}

			if(!empty($data['options'])){
				$data['load_file'] = false;
				$data['load_datetimepicker'] = false;

				foreach ($data['options'] as $option) {
					if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
						$data['load_datetimepicker'] = true;
						break;
					} elseif($option['type'] == 'file'){
						$data['load_file'] = true;
					}
				}
				if($data['load_datetimepicker']){
					$data['lang_datetimepicker'] = $this->session->data['language'];
				}


				$data['minimum'] = $product_info['minimum'];

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$data['price_value'] = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
				} else {
					$data['price'] = false;
					$data['price_value'] = false;
				}

				if ((float)$product_info['special']) {
					$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$data['special_value'] = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
				} else {
					$data['special'] = false;
					$data['special_value'] = false;
				}

				$data_options = $this->load->view('upstore/upstore_product_options', $data);
				$json['options'] = $data_options;
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}

		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

}