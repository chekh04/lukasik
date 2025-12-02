<?php
class ControllerExtensionModuleUpstoreKits extends Controller {
	public function index() {

		static $module = 0;

		$product_id = 0;

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			return;
		}

		$this->document->addStyle('catalog/view/theme/upstore/stylesheet/kits.css');
		$this->document->addScript('catalog/view/theme/upstore/js/kits.js');

		$this->load->language('extension/module/upstore_kits');

		$this->load->model('extension/module/upstore_kits');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_tax'] = $this->language->get('text_tax');
		$data['button_kit_cart'] = $this->language->get('button_kit_cart');
		$data['text_kit_discount'] = $this->language->get('text_kit_discount');
		$data['button_change_kit'] = $this->language->get('button_change_kit');

		$data['on_off_percent_discount'] = (!empty($this->config->get('on_off_percent_discount')) ? 1 : 0);

		$variant_kits = $this->model_extension_module_upstore_kits->getVariantKit($product_id);

		$data['kits'] = array();

		if(!empty($variant_kits)){

			foreach($variant_kits as $key => $info_kit){
				$products = array();
				$total = 0;

				foreach($info_kit['products'] as $result){
					$product_info = $this->model_catalog_product->getProduct($result['product_id']);

					if($product_info){
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], 150, 150);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', 150, 150);
						}

						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'] + $result['option_price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}

						if ((float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'] + $result['option_price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}

						if($special){
							$total += $this->tax->calculate($product_info['special'] + $result['option_price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
						}else{
							$total += $this->tax->calculate($product_info['price'] + $result['option_price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
						}

						if ((float)$product_info['special']) {
							$price2 = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
							$special2 = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
							$skidka = $special2/($price2/100)-100;
						} else {
							$skidka = "";
						}

						$products[] = array(
							'vk_id'			=> $info_kit['vk_id'],
							'kit_id'		=> $info_kit['kit_id'],
							'total_prod'	=> (!empty($result['total_prod'])) ? $result['total_prod'] : 0,
							'width'			=> 150,
							'height'		=> 150,
							'skidka'     	=> round((float)$skidka),
							'product_id'	=> $product_info['product_id'],
							'thumb'			=> $image,
							'name'			=> $product_info['name'],
							'price'			=> $price ,
							'special'		=> $special,
							'tax'			=> $tax,
							'href'			=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
						);
					}

				} // end foreach Products


				if ($info_kit['type_discount']) {
					$discount = (int)$info_kit['discount'];
				}else{
					$discount = $total / 100 * (int)$info_kit['discount'];
				}

				$total_kit = $this->currency->format($total - $discount , $this->session->data['currency']);
				$discount_kit = $this->currency->format($discount, $this->session->data['currency']);


				$data['kits'][] = array(
					'vk_id'				=> $info_kit['vk_id'],
					'kit_id'			=> $info_kit['kit_id'],
					'main_product'		=> $info_kit['main_product'],
					'product_id'		=> $info_kit['product_id'],
					'products'			=> $products,
					'total_kit'			=> $total_kit,
					'discount_kit'		=> $discount_kit,
				);

			} // end foreach Sets


			$data['module'] = $module++;

			if(!empty($data['kits'])){
				return $this->load->view('extension/module/upstore_kits', $data);
			}

		}
	}

	public function getVariantProducts() {
		$this->load->language('extension/module/upstore_kits');

		$this->load->model('extension/module/upstore_kits');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['text_tax'] = $this->language->get('text_tax');
		$data['text_kits_products'] = $this->language->get('text_kits_products');
		$data['on_off_percent_discount'] = (!empty($this->config->get('on_off_percent_discount')) ? 1 : 0);

		if (isset($this->request->get['kit_id'])) {
			$kit_id = $this->request->get['kit_id'];
		} else {
			return;
		}

		if (isset($this->request->get['vk_id'])) {
			$vk_id = $this->request->get['vk_id'];
		} else {
			return;
		}

		$product_id = 0;
		$data['prod_id'] = 0;
		if (isset($this->request->get['prod_id'])) {
			 $product_id = $this->request->get['prod_id'];
			 $data['prod_id'] = $this->request->get['prod_id'];
		}

		$products = $this->model_extension_module_upstore_kits->getVariantProductsKits($kit_id, $vk_id);

		$data['products'] = array();

		if(!empty($products)){
			foreach($products as $result){
				$product_info = $this->model_catalog_product->getProduct($result['product_id']);
				if($products){
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], 100, 100);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', 100, 100);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}
					if ((float)$product_info['special']) {
						$price2 = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
						$special2 = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
						$skidka = $special2/($price2/100)-100;
					} else {
						$skidka = "";
					}

					$active_prod = false;
					if($product_info['product_id'] == $product_id){
						$active_prod = true;
					}

					$data['products'][] = array(
						'vk_id'			=> $result['vk_id'],
						'kit_id'			=> $result['kit_id'],
						'main_prod_id'	=> $result['main_product'],
						'width'			=> 100,
						'height'			=> 100,
						'skidka'     	=> round((float)$skidka),
						'product_id'	=> $product_info['product_id'],
						'active_prod'	=> $active_prod,
						'thumb'			=> $image,
						'name'			=> $product_info['name'],
						'price'			=> $price,
						'special'		=> $special,
						'tax'				=> $tax,
					);
				}
			}
		}

		$this->response->setOutput($this->load->view('extension/module/upstore_kits_products', $data));
	}

	public function setNewProduct() {
		$this->load->language('extension/module/upstore_kits');

		$this->load->model('extension/module/upstore_kits');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$json = array();

		$data['text_tax'] = $this->language->get('text_tax');
		$data['text_kit_discount'] = $this->language->get('text_kit_discount');
		$data['text_kits_products'] = $this->language->get('text_kits_products');
		$data['on_off_percent_discount'] = (!empty($this->config->get('on_off_percent_discount')) ? 1 : 0);
		$data['button_change_kit'] = $this->language->get('button_change_kit');

		$product_id = 0;
		if (isset($this->request->post['prod_id'])) {
			 $product_id = $this->request->post['prod_id'];
		}

		if (isset($this->request->post['kit_id'])) {
			$kit_id = $this->request->post['kit_id'];
		} else {
			return;
		}

		if (isset($this->request->post['vk_id'])) {
			$vk_id = $this->request->post['vk_id'];
		} else {
			return;
		}

		$info_kit = $this->model_extension_module_upstore_kits->getInfoKit($kit_id, $vk_id, $product_id);

		$total = 0;

		$main_product = $this->model_catalog_product->getProduct($info_kit['main_product']);

		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$main_price = $main_product['price'];
		} else {
			$main_price = false;
		}

		if ((float)$main_product['special']) {
			$main_special = $main_product['special'];
		} else {
			$main_special = false;
		}

		if($main_special){
			$total += $this->tax->calculate($main_special, $main_product['tax_class_id'], $this->config->get('config_tax'));
		}else{
			$total += $this->tax->calculate($main_price, $main_product['tax_class_id'], $this->config->get('config_tax'));
		}


		$data['product'] = array();

		if($info_kit){
			$product_info = $this->model_catalog_product->getProduct($product_id);
			if ($product_info['image']) {
				$image = $this->model_tool_image->resize($product_info['image'], 150, 150);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', 150, 150);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if ((float)$product_info['special']) {
				$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}
			if ((float)$product_info['special']) {
				$price2 = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$special2 = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$skidka = $special2/($price2/100)-100;
			} else {
				$skidka = "";
			}
			if($special){
				$total += $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
			}else{
				$total += $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
			}
			$data['product'] = array(
				'vk_id'			=> $info_kit['vk_id'],
				'kit_id'			=> $info_kit['kit_id'],
				'total_prod'	=> (!empty($info_kit['total_prod'])) ? $info_kit['total_prod'] : 0,
				'width'			=> 150,
				'height'			=> 150,
				'skidka'     	=> round((float)$skidka),
				'product_id'	=> $product_info['product_id'],
				'thumb'			=> $image,
				'name'			=> $product_info['name'],
				'price'			=> $price,
				'special'		=> $special,
				'tax'				=> $tax,
				'href'			=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
			);
		}

		if ($info_kit['type_discount']) {
			$discount = (int)$info_kit['discount'];
		}else{
			$discount = $total / 100 * (int)$info_kit['discount'];
		}

		$total_kit = $this->currency->format($total - $discount , $this->session->data['currency']);
		$discount_kit = $this->currency->format($discount, $this->session->data['currency']);

		$json['total_kit'] = $total_kit;
		$json['discount_kit'] = $discount_kit;

		$data_product = $this->load->view('extension/module/upstore_kits_product', $data);
		$json['product'] = $data_product;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function kitAddToCart() {
		$this->load->language('extension/module/upstore_kits');
		$this->load->language('checkout/cart');

		$this->load->model('extension/module/upstore_kits');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['text_select_options'] = $this->language->get('text_select_options');
		$data['button_kit_cart'] = $this->language->get('button_kit_cart');
		$data['text_kit_discount'] = $this->language->get('text_kit_discount');
		$data['text_select'] = $this->language->get('text_select');

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
		if (isset($this->request->post['prod_id'])) {
			 $product_id = $this->request->post['prod_id'];
		}

		if (isset($this->request->post['kit_id'])) {
			$kit_id = $this->request->post['kit_id'];
		} else {
			return;
		}

		if (isset($this->request->post['vk_id'])) {
			$vk_id = $this->request->post['vk_id'];
		} else {
			return;
		}

		$info_kit = $this->model_extension_module_upstore_kits->checkKit($kit_id, $vk_id, $product_id);

		$data['options'] = array();

		if(!empty($info_kit)){
			$info_kit['options'] = (!empty($info_kit['options'])) ? json_decode($info_kit['options'], true) : array();

			if (!isset($this->request->post['check_option'])) {
				foreach ($this->model_catalog_product->getProductOptions($info_kit['main_product']) as $option) {
					if ($option['type'] == 'select' || $option['type'] == 'radio' ||  $option['type'] == 'checkbox') {
						$product_option_value_data = array();

						foreach ($option['product_option_value'] as $option_value) {
							if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
								if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
									$price = $this->currency->format($this->tax->calculate($option_value['price'], $info_kit['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
								} else {
									$price = false;
								}

								$product_option_value_data[] = array(
									'color'    				  => $option_value['color'],
									'image_thumb' 			  => $this->model_tool_image->resize($option_value['image'], 60, 60),
									'price_value'			  => $option_value['price'],
									'product_option_value_id' => $option_value['product_option_value_id'],
									'option_value_id'         => $option_value['option_value_id'],
									'name'                    => $option_value['name'],
									'image'                   => $option_value['image'] ? $this->model_tool_image->resize($option_value['image'], 50, 50) : '',
									'price'                   => $price,
									'price_prefix'            => $option_value['price_prefix']
								);
							}
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

				if(!empty($data['options'])){

					$data['info_kit'] = $info_kit;
					$total = 0;

					$main_product = $this->model_catalog_product->getProduct($info_kit['main_product']);

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$main_price = $main_product['price'];
					} else {
						$main_price = false;
					}

					if ((float)$main_product['special']) {
						$main_special = $main_product['special'];
					} else {
						$main_special = false;
					}

					if($main_special){
						$total += $main_special;
					}else{
						$total += $main_price;
					}


					$dop_product = $this->model_catalog_product->getProduct($info_kit['product_id']);

					$option_price = 0;
					if(!empty($info_kit['options'])){
						$option_price = $this->model_extension_module_upstore_kits->getTotalOptionsPrice($info_kit['options'], $info_kit['product_id']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$dop_price = $dop_product['price'] + $option_price;
					} else {
						$dop_price = false;
					}

					if ((float)$dop_product['special']) {
						$dop_special = $dop_product['special'] + $option_price;
					} else {
						$dop_special = false;
					}

					if($dop_special){
						$total += $this->tax->calculate($dop_special, $dop_product['tax_class_id'], $this->config->get('config_tax'));
					}else{
						$total += $this->tax->calculate($dop_price, $dop_product['tax_class_id'], $this->config->get('config_tax'));
					}

					if ($info_kit['type_discount']) {
						$discount = (int)$info_kit['discount'];
					}else{
						$discount = $total / 100 * (int)$info_kit['discount'];
					}

					$total_kit = $this->currency->format($total - $discount , $this->session->data['currency']);
					$discount_kit = $this->currency->format($discount, $this->session->data['currency']);

					$data['total'] = $total;
					$data['total_kit'] = $total_kit;
					$data['discount_kit'] = $discount_kit;

					$data_options = $this->load->view('extension/module/upstore_kits_options', $data);
					$json['options'] = $data_options;
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));
				}
			}

			if (isset($this->request->post['check_option'])) {
				if (isset($this->request->post['option'])) {
					$option = array_filter($this->request->post['option']);
				} else {
					$option = array();
				}

				$product_options = $this->model_catalog_product->getProductOptions($info_kit['main_product']);

				foreach ($product_options as $product_option) {
					if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' ||  $product_option['type'] == 'checkbox') {
						if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
							$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
						}
					}
				}
			} else {
				$option = array();
			}

			if (!$json) {
				$quantity = 1;
				$this->cart->add($info_kit['main_product'], $quantity, $option);
				$this->cart->add($info_kit['product_id'], $quantity, $info_kit['options']);

				$option_data_main = array();
				foreach ($option as $key => $value) {
					if (is_array($value)) {
						foreach ($value as $po_vid) {
							$option_data_main[$key] = $po_vid;
						}
					} else {
						$option_data_main[$key] = $value;
					}
				}

				$option_data = array();
				foreach ($info_kit['options'] as $key => $value) {
					if (is_array($value)) {
						foreach ($value as $po_vid) {
							$option_data[$key] = $po_vid;
						}
					} else {
						$option_data[$key] = $value;
					}
				}

				$skits = array(
					'kit_id' 		=> (int)$info_kit['kit_id'],
					'vk_id' 		=> (int)$info_kit['vk_id'],
					'main_product' 	=> (int)$info_kit['main_product'],
					'product_id' 	=> (int)$info_kit['product_id'],
					'option_main'	=> $option_data_main,
					'option'		=> $option_data,
				);


				$this->session->data['skits'][] = $skits;

				// Unset all shipping and payment methods
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				// Totals
				$this->load->model('setting/extension');

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;

				// Because __call can not keep var references so we put them into an array.
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);

							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($totals as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $totals);
				}

				$this->load->language('upstore/theme');
				$json['total'] = sprintf($this->language->get('text_items_my'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

				$json['success'] = $this->language->get('success_add_kit');

			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}

	}
}