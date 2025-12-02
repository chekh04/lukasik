<?php
class ControllerExtensionModuleUpstoreNewfastorder extends Controller {
	private $error = array();
	public function index() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$data['config_additional_settings_upstore'] = $this->config->get('config_additional_settings_upstore');
			if ($this->config->get('config_quickorder_id')) {
				$this->load->model('catalog/information');
				$this->load->language('upstore/theme');
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_quickorder_id'));

				if ($information_info) {
					$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_quickorder_id'), true), $information_info['title'], $information_info['title']);
				} else {
					$data['text_agree'] = '';
				}
			} else {
				$data['text_agree'] = '';
			}

			$this->load->language('extension/module/upstore_newfastorder');
			$this->load->language('product/product');

			$this->load->model('tool/image');
			$this->load->model('catalog/product');
			$this->load->model('extension/module/upstore_newfastorder');

			if (isset($this->request->get['product_id'])) {
				$product_id = (int)$this->request->get['product_id'];
				$data['product_id'] = (int)$this->request->get['product_id'];
			} else {
				$product_id = 0;
				$data['product_id'] = 0;
			}

			$data['lang_id'] = $this->config->get('config_language_id');

			$data['any_text_at_the_bottom_color'] = $this->config->get('config_any_text_at_the_bottom_color');
			$data['mask_phone_number'] = $this->config->get('config_mask_phone_number');
			$data['config_text_before_button_send'] = $this->config->get('config_text_before_button_send');
			$data['config_any_text_at_the_bottom'] = $this->config->get('config_any_text_at_the_bottom');
			$data['config_title_popup_quickorder'] = $this->config->get('config_title_popup_quickorder');
			$data['config_fields_firstname_requared'] = $this->config->get('config_fields_firstname_requared');
			$data['config_fields_phone_requared'] = $this->config->get('config_fields_phone_requared');
			$data['config_fields_email_requared'] = $this->config->get('config_fields_email_requared');
			$data['config_fields_comment_requared'] = $this->config->get('config_fields_comment_requared');
			$data['config_placeholder_fields_firstname'] = $this->config->get('config_placeholder_fields_firstname');
			$data['config_placeholder_fields_phone'] = $this->config->get('config_placeholder_fields_phone');
			$data['config_placeholder_fields_email'] = $this->config->get('config_placeholder_fields_email');
			$data['config_placeholder_fields_comment'] = $this->config->get('config_placeholder_fields_comment');
			$data['on_off_fields_firstname'] = $this->config->get('config_on_off_fields_firstname');
			$data['on_off_fields_phone'] = $this->config->get('config_on_off_fields_phone');
			$data['on_off_fields_comment'] = $this->config->get('config_on_off_fields_comment');
			$data['on_off_fields_email'] = $this->config->get('config_on_off_fields_email');

			$product_info = $this->model_catalog_product->getProduct($product_id);

			$data['heading_title'] = $product_info['name'];
			$data['model'] = $product_info['model'];
			$data['points'] = $product_info['points'];

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
			} else {
				$data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}
			$data['dicounts_unf'] = $this->model_catalog_product->getProductDiscounts($product_id);

			$data['price_value'] = $product_info['price'];
		 	$data['special_value'] = $product_info['special'];

			$var_currency = array();
			$var_currency['value'] = $this->currency->getValue($this->session->data['currency']);
			$var_currency['symbol_left'] = $this->currency->getSymbolLeft($this->session->data['currency']);
			$var_currency['symbol_right'] = $this->currency->getSymbolRight($this->session->data['currency']);
			$var_currency['currency_code'] = $this->session->data['currency'];
			$var_currency['decimals'] = $this->currency->getDecimalPlace($this->session->data['currency']);
			$var_currency['decimal_point'] = $this->language->get('decimal_point');
			$var_currency['thousand_point'] = $this->language->get('thousand_point');
			$data['currency'] = $var_currency;

			$data['tax_rates'] = $this->tax->getRates(0, $product_info['tax_class_id']);

			$data['options'] = array();

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
							'price_value'             => $option_value['price'],
							'points_value'            => intval($option_value['points_prefix'].$option_value['points']),
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'color'                   => $option_value['color'],
							'image'                   => $option_value['image'] ? $this->model_tool_image->resize($option_value['image'], 50, 50) : '',
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'status_color_type'    => $option['status_color_type'],
					'required'             => $option['required']
				);
			}

			$data['load_datetimepicker'] = false;
			foreach ($data['options'] as $option) {
				if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$data['load_datetimepicker'] = true;
					break;
				}
			}
			if($data['load_datetimepicker']){
				$data['lang_datetimepicker'] = $this->session->data['language'];
			}


			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
				if(isset($this->session->data['csrf_token'])){
					unset($this->session->data['csrf_token']);
				}

				if (function_exists('random_bytes')) {
					$this->session->data['csrf_token'] = bin2hex(random_bytes(32));
				} else {
					$this->session->data['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
				}
			}

			$data['csrf_token'] = $this->session->data['csrf_token'];

			$this->response->setOutput($this->load->view('extension/module/upstore_newfastorder', $data));
		} else {
		  $this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

	public function addFastOrder() {
		$this->load->language('extension/module/upstore_newfastorder');
		$this->load->language('product/product');

		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('extension/module/upstore_newfastorder');
		$this->load->model('account/customer');

		$json = array();

		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && (isset($this->request->post['this_prod_id']) && !empty($this->request->post['this_prod_id']))) {

			if ($this->validate()) {
				$order_data = array();

				$lang_id = $this->config->get('config_language_id');

				if (isset($this->request->post['this_prod_id'])) {
					$data['this_prod_id'] = $this->request->post['this_prod_id'];
				} else {
					$data['this_prod_id'] = '';
				}

				$product_info = $this->model_catalog_product->getProduct($this->request->post['this_prod_id']);

				$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$order_data['store_id'] = $this->config->get('config_store_id');
				$order_data['store_name'] = $this->config->get('config_name');

				if ($order_data['store_id']) {
					$order_data['store_url'] = $this->config->get('config_url');
				} else {
					if ($this->request->server['HTTPS']) {
						$order_data['store_url'] = HTTPS_SERVER;
					} else {
						$order_data['store_url'] = HTTP_SERVER;
					}
				}

				if ($this->customer->isLogged()) {
					$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
					$order_data['customer_id'] = $this->customer->getId();
					$order_data['customer_group_id'] = $customer_info['customer_group_id'];
				} else {
					$order_data['customer_id'] = 0;
					$order_data['customer_group_id'] = $this->customer->getGroupId();
				}

				if (isset($this->request->post['name_fastorder'])) {
					$order_data['name_fastorder'] = $this->request->post['name_fastorder'];
				} else {
					$order_data['name_fastorder'] = '';
				}

				$order_data['firstname'] = $order_data['shipping_firstname'] = $order_data['payment_firstname'] = $order_data['name_fastorder'];
				$order_data['lastname'] = '';

				if (isset($this->request->post['email_buyer'])) {
					$order_data['email_buyer'] = $this->request->post['email_buyer'];
					$order_data['email'] = (isset($this->request->post['email_buyer']) && !empty($this->request->post['email_buyer'])) ? $this->request->post['email_buyer'] : 'empty'.time().'@localhost.net';
				} else {
					$order_data['email_buyer'] = '';
					$order_data['email'] = 'empty'.time().'@localhost.net';
				}

				if (isset($this->request->post['phone'])) {
					$order_data['phone'] = $this->request->post['phone'];
					$order_data['telephone'] = $this->request->post['phone'];
				} else {
					$order_data['phone'] = '';
					$order_data['telephone'] = '';
				}

				if (isset($this->request->post['comment_buyer'])) {
					$order_data['comment_buyer'] = $this->request->post['comment_buyer'];
					$order_data['comment'] = $this->request->post['comment_buyer'];
				} else {
					$order_data['comment_buyer'] = '';
					$order_data['comment'] = '';
				}

				$order_data['custom_field'] = array();
				$order_data['fax'] = '';
				$order_data['payment_lastname'] = '';
				$order_data['payment_company'] = '';
				$order_data['payment_address_1'] = '';
				$order_data['payment_address_2'] = '';
				$order_data['payment_city'] = '';
				$order_data['payment_postcode'] = '';
				$order_data['payment_country'] = '';
				$order_data['payment_country_id'] = '';
				$order_data['payment_zone'] = '';
				$order_data['payment_zone_id'] = '';
				$order_data['payment_address_format'] = '';
				$order_data['payment_custom_field'] = array();
				$order_data['payment_method'] = '';
				$order_data['payment_code'] = '';

				$order_data['shipping_lastname'] = '';
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = '';
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_country'] = '';
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_custom_field'] = array();
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';

				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';

				$order_data['language_id'] = $lang_id;
				$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
				$order_data['currency_code'] = $this->session->data['currency'];
				$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
				$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

				if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
				} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
				} else {
					$order_data['forwarded_ip'] = '';
				}

				if (isset($this->request->server['HTTP_USER_AGENT'])) {
					$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
				} else {
					$order_data['user_agent'] = '';
				}

				if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
					$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
				} else {
					$order_data['accept_language'] = '';
				}

				$order_data['tax_class_id_total'] = $product_info['tax_class_id'];
				$order_data['config_tax'] = $this->config->get('config_tax');

				if ($product_info['image']) {
					$order_data['prod_image'] = $this->model_tool_image->resize($product_info['image'], 228, 228);
					$order_data['small_image'] = $this->model_tool_image->resize($product_info['image'], 74, 74);
				} else {
					$order_data['prod_image'] = $this->model_tool_image->resize('no_image.jpg', 228, 228);
					$order_data['small_image'] = $this->model_tool_image->resize('no_image.jpg', 74, 74);
				}


				$order_data['price_shipping_value']	= '';
				$order_data['shipping_title'] = '';


				if (isset($this->request->post['url_site'])) {
					$order_data['url_site'] = $this->request->post['url_site'];
				} else {
					$order_data['url_site'] = '';
				}

				if (isset($this->request->post['quantity'])) {
					$order_data['quantity'] = $this->request->post['quantity'];
				 } else {
					$order_data['quantity'] = 0;
				}
				if (isset($this->request->post['quantity'])) {
					$order_data['quantity'] = $this->request->post['quantity'];
				} else {
					$order_data['quantity'] = 0;
				}

				if (isset($this->request->post['option-fast'])) {
					$order_data['option-fast'] = $this->request->post['option-fast'];
				} else {
					$order_data['option-fast'] = '';
				}

				if (!empty($this->request->post['option-fast'])) {
					$product_options = $this->getProductsOptionsFastorder($this->request->post['this_prod_id'], $this->request->post['option-fast']);
				} else {
					$product_options = array();
				}

				$product_id = $product_info['product_id'];

				$option_price = 0;

				$product_price = $product_info['price'];

				if ((float)$product_info['special']) {
					$product_price = $product_info['special'];
				}

				$product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$order_data['quantity'] . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

				if ($product_discount_query->num_rows) {
					$product_price = $product_discount_query->row['price'];
				}

				if(!empty($product_options)){
					foreach($product_options as $option){
						if ($option['price_prefix'] == '+') {
							$option_price += $option['price'];
						} elseif ($option['price_prefix'] == '-') {
							$option_price -= $option['price'];
						}
					}
				}

				$qo_price = ($product_price + $option_price);
				$qo_total = ($product_price + $option_price) * $order_data['quantity'];

				$order_data['price'] = $qo_price;
				$order_data['total'] = $qo_total;

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = $qo_total;

				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				$this->load->model('extension/total/total');
				$this->model_extension_total_total->getTotal($total_data);

				$order_data['totals'] = $totals;

				$order_data['products'] = array();

				if($product_info){
					if ($product_info['image']) {
						$small_image = $this->model_tool_image->resize($product_info['image'], 100, 100);
					} else {
						$small_image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
					}
					$order_data['products'][] = array(
						'product_id' => $data['this_prod_id'],
						'name'       => $product_info['name'],
						'model'      => $product_info['model'],
						'option'     => $product_options,
						'quantity'   => $order_data['quantity'],
						'subtract'   => $product_info['subtract'],
						'price'      => $qo_price,
						'total'      => $qo_total,
						'price_format' 	=> $this->currency->format($qo_price, $this->session->data['currency']),
						'total_format' 	=> $this->currency->format($qo_total, $this->session->data['currency']),
						'tax'        		=> $this->tax->getTax(($product_price + $option_price), $product_info['tax_class_id']),
						'reward'     		=> $product_info['reward'],
						'currency_code' 	=> $order_data['currency_code'],
						'currency_value' 	=> $order_data['currency_value'],
						'product_image' 	=> $product_info['image'],
						'small_image' 		=> $small_image,
					);
				}


				$this->load->model('checkout/order');

				$order_id = $this->model_checkout_order->addOrder($order_data);
				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
				$results = $this->model_extension_module_upstore_newfastorder->addOrder($order_id, $order_data);

				$config_complete_quickorder = $this->config->get('config_complete_quickorder');
				$ok = $config_complete_quickorder[$lang_id]['config_complete_quickorder'];

				if($ok !=''){
					$json['success'] = $ok;
				} else {
					$json['success'] = $this->language->get('ok');
				}

				unset($this->session->data['csrf_token']);

				$config_on_off_send_buyer_mail = $this->config->get('config_on_off_send_buyer_mail');
				if($config_on_off_send_buyer_mail =='1'){
					if($order_data['email_buyer'] !='') {
						$this->sendMailBuyer($order_data);
					}
				}

				$config_on_off_send_me_mail = $this->config->get('config_on_off_send_me_mail');
				if($config_on_off_send_me_mail =='1'){
					$this->sendMailMe($order_data);
				}

				$this->cache->delete('product.bestseller');
			} else {
				$json['error'] = $this->error;

			}

			return $this->response->setOutput(json_encode($json));

		} else {
		  $this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

	private function validate() {
		$token_csrf = false;
		if (isset($this->request->post['token_csrf'])) {
			$token_csrf = $this->request->post['token_csrf'];
		}

		if (!isset($this->session->data['csrf_token']) || !$token_csrf || $token_csrf !== $this->session->data['csrf_token']) {
			return false;
		}

		$this->load->language('extension/module/upstore_newfastorder');
		$config_fields_firstname_requared = $this->config->get('config_fields_firstname_requared');
		$config_on_off_fields_firstname = $this->config->get('config_on_off_fields_firstname');
		if(($config_fields_firstname_requared =='1') && $config_on_off_fields_firstname =='1'){
			if ((utf8_strlen(trim($this->request->post['name_fastorder'])) < 1) || (utf8_strlen(trim($this->request->post['name_fastorder'])) > 32)) {
				$this->error['name_fastorder'] = $this->language->get('mister');
			}
		}
		$config_fields_phone_requared = $this->config->get('config_fields_phone_requared');
		$config_on_off_fields_phone = $this->config->get('config_on_off_fields_phone');
		if(($config_fields_phone_requared =='1') && $config_on_off_fields_phone =='1'){
			if ((utf8_strlen(trim($this->request->post['phone'])) < 1) || (utf8_strlen(trim($this->request->post['phone'])) > 32)) {
				$this->error['phone'] = $this->language->get('error_phone');
			}
		}
		$config_fields_comment_requared = $this->config->get('config_fields_comment_requared');
		$config_on_off_fields_comment = $this->config->get('config_on_off_fields_comment');
		if(($config_fields_comment_requared =='1') && $config_on_off_fields_comment == '1'){
			if ((utf8_strlen(trim($this->request->post['comment_buyer'])) < 1) || (utf8_strlen(trim($this->request->post['comment_buyer'])) > 32)) {
				$this->error['comment_buyer'] = $this->language->get('comment_buyer_error');
			}
		}
		$config_fields_email_requared = $this->config->get('config_fields_email_requared');
		$config_on_off_fields_email = $this->config->get('config_on_off_fields_email');
		if(($config_fields_email_requared =='1') && $config_on_off_fields_email == '1'){
			if(!preg_match("/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/", $this->request->post['email_buyer'])){
					$this->error['email_buyer'] =  $this->language->get('email_buyer_error');
			}
		}

		if (isset($this->request->post['option-fast'])) {
			$option = array_filter($this->request->post['option-fast']);
		} else {
			$option = array();
		}

		$this->load->model('catalog/product');
		$product_options = $this->model_catalog_product->getProductOptions($this->request->post['this_prod_id']);
		foreach ($product_options as $product_option) {
			if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
				$this->error['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
			}
		}
			// Agree to terms
		if ($this->config->get('config_quickorder_id')) {
			$this->load->model('catalog/information');
			$this->load->language('upstore/theme');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_quickorder_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['error_agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


	private function getCustomFields($order_info, $varabliesd) {
		$instros = explode('~', $varabliesd);
		$instroz = "";
		foreach ($instros as $instro) {
			if ($instro == 'totals' || isset($order_info[$instro]) ){
				if ($instro == 'totals'){
					 $instro_other = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], true);
				}
				if(isset($order_info[$instro])){
					$instro_other = $order_info[$instro];
				}
			}
			else {
				 $instro_other = nl2br(htmlspecialchars_decode($instro));
			}
				 $instroz .=  $instro_other;
		}
		return $instroz;
	}


	private function sendMailBuyer($data) {
		if($data['config_tax'] =='1'){
			$data['tax_rates_f_p'] = $this->tax->getRates($data['price'], $data['tax_class_id_total']);
		} else {
			$data['tax_rates_f_p'] = '';
		}
		$data['total'] = $this->currency->format($data['price'] * $data['quantity'], $this->session->data['currency']);
		$data['total_all'] = $this->currency->format($data['total'],$this->session->data['currency']);

		$this->load->language('module/newfastorder');

		$data['text_photo'] = $this->language->get('text_photo');
		$data['text_product'] = $this->language->get('text_new_product');
		$data['text_model'] = $this->language->get('text_new_model');
		$data['text_quantity'] = $this->language->get('text_new_quantity');
		$data['text_price'] = $this->language->get('text_new_price');
		$data['text_total'] = $this->language->get('text_new_total');

		$text = '';
		$quickorder_subject = $this->config->get('quickorder_subject');
		$quickorder_description= $this->config->get('quickorder_description');
		$subject_buyer = $this->getCustomFields($data, $quickorder_subject[$data['language_id']]['text']);

		if ((strlen(utf8_decode($subject_buyer)) > 5)){
			$subject = $subject_buyer;
		} else {
			$subject = $this->language->get('subject');
		}

		$html = $this->getCustomFields($data, $quickorder_description[$data['language_id']]['text']). "\n";
		$config_buyer_html_products = $this->config->get('config_buyer_html_products');

		if($config_buyer_html_products =='1'){
		$html .= $this->load->view('mail/quickorderone', $data);
		}

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($data['email_buyer']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($data['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
		$mail->setText($text);
		$mail->send();
	}
	private function sendMailMe($data) {

		if($data['config_tax'] =='1'){
			$data['tax_rates_f_p'] = $this->tax->getRates($data['price'], $data['tax_class_id_total']);
		}else {
			$data['tax_rates_f_p'] = '';
		}

		$data['total'] = $this->currency->format($data['price'] * $data['quantity'], $this->session->data['currency']);
		$data['total_all'] = $this->currency->format($data['total'],$this->session->data['currency']);

		$this->load->language('module/newfastorder');

		$data['text_photo'] = $this->language->get('text_photo');
		$data['text_product'] = $this->language->get('text_new_product');
		$data['text_model'] = $this->language->get('text_new_model');
		$data['text_quantity'] = $this->language->get('text_new_quantity');
		$data['text_price'] = $this->language->get('text_new_price');
		$data['text_total'] = $this->language->get('text_new_total');

		$text = '';
		$quickorder_subject_me = $this->config->get('quickorder_subject_me');
		$quickorder_description_me = $this->config->get('quickorder_description_me');
		$subject_me = $this->getCustomFields($data, $quickorder_subject_me[$data['language_id']]['text']);

		if ((strlen(utf8_decode($subject_me)) > 5)){
			$subject = $subject_me;
		} else {
			$subject = $this->language->get('subject');
		}

		$html = $this->getCustomFields($data, $quickorder_description_me[$data['language_id']]['text']). "\n";
		$config_me_html_products = $this->config->get('config_me_html_products');

		if($config_me_html_products =='1'){
		$html .= $this->load->view('mail/quickorderone', $data);
		}

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($this->config->get('config_you_email_quickorder'));
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($data['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
		$mail->setText($text);
		$mail->send();
	}

	private function getProductsOptionsFastorder($this_prod_id, $option_fast) {
		$product_id = $this_prod_id;
		if (isset($option_fast)) {
			$options = $option_fast;
		} else {
			$options = array();
		}

		$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			WHERE p.product_id = '" . (int)$product_id . "'
			AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			AND p.date_available <= NOW() AND p.status = '1'");

		$option_data = array();
		foreach ($options as $product_option_id => $value) {
			$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po
				LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id)
				LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id)
				WHERE po.product_option_id = '" . (int)$product_option_id . "'
				AND po.product_id = '" . (int)$product_id . "'
				AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

			if ($option_query->num_rows) {
				if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image') {
					$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($option_value_query->num_rows) {

						$option_data[] = array(
							'product_option_id'       => $product_option_id,
							'product_option_value_id' => $value,
							'option_id'               => $option_query->row['option_id'],
							'option_value_id'         => $option_value_query->row['option_value_id'],
							'name'                    => $option_query->row['name'],
							'value'                   => $option_value_query->row['name'],
							'type'                    => $option_query->row['type'],
							'price'                   => $option_value_query->row['price'],
							'price_prefix'            => $option_value_query->row['price_prefix'],
						);
					}
				} elseif ($option_query->row['type'] == 'checkbox' && is_array($value)) {
					foreach ($value as $product_option_value_id) {
						$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($option_value_query->num_rows) {
							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => $product_option_value_id,
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => $option_value_query->row['option_value_id'],
								'name'                    => $option_query->row['name'],
								'value'                   => $option_value_query->row['name'],
								'type'                    => $option_query->row['type'],
								'price'                   => $option_value_query->row['price'],
								'price_prefix'            => $option_value_query->row['price_prefix'],
							);
						}
					}
				} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
					$option_data[] = array(
						'product_option_id'       => $product_option_id,
						'product_option_value_id' => '',
						'option_id'               => $option_query->row['option_id'],
						'option_value_id'         => '',
						'name'                    => $option_query->row['name'],
						'value'                   => $value,
						'type'                    => $option_query->row['type'],
						'price'                   => '',
						'price_prefix'            => '',
					);
				}
			}
		}
		return $option_data;
	}
}
?>
