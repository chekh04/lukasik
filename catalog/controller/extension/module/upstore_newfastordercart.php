<?php
class ControllerExtensionModuleUpstoreNewfastordercart extends Controller {
	private $error = array();

	public function index() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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

			$config_show_onepagecheckout = $this->config->get('config_show_onepagecheckout');
			if ($config_show_onepagecheckout == '1') {
				$data['checkout'] = $this->url->link('checkout/onepcheckout', '', true);
			} else {
				$data['checkout'] = $this->url->link('checkout/checkout', '', true);
			}

			$this->load->language('upstore/theme');
			$this->load->language('extension/module/upstore_newfastorder');

			$data['text_model'] = $this->language->get('text_model');
			$data['text_one_price'] = $this->language->get('text_one_price');
			$data['text_totals'] = $this->language->get('text_totals');
			$data['button_buy'] = $this->language->get('button_buy');
			$data['button_remove'] = $this->language->get('button_remove');
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_continue'] = $this->language->get('text_continue');
			$data['title_shopping_cart'] = $this->language->get('title_shopping_cart');
			$data['lang_id'] = $this->config->get('config_language_id');

			$data['text_checkout'] = $this->language->get('button_send');

			$data['mask_phone_number'] = $this->config->get('config_mask_phone_number');

			$placeholder_phone = $this->config->get('config_placeholder_fields_phone');

			$data['placeholder_phone'] = (!empty($placeholder_phone[$this->config->get('config_language_id')]['config_placeholder_fields_phone'])) ? $placeholder_phone[$this->config->get('config_language_id')]['config_placeholder_fields_phone'] : '';

			$data['show_shopping_cart_field_telephone'] = $this->config->get('config_show_shopping_cart_btn');

			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$data['products'] = array();

			//Gifts
			$this->load->language('extension/module/upstore_gifts');
			$data['text_gift'] = $this->language->get('text_gift');
			//END Gifts

			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], 100, 100);
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year')
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				//Gifts
				if($product['ch_gift_id'] > 0){
					$price = $this->currency->format($product['price'], $this->session->data['currency']);
					$total = $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']);
				}
				//END Gifts

				$data['products'][] = array(
					//Gifts
					'ch_gift_id'		=> $product['ch_gift_id'],
					//END Gifts
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)
					);
				}
			}

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

			$data['total_order'] = $this->currency->format($total, $this->session->data['currency']);

			$data['totals'] = array();

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}

			$order_sum = $this->cart->getSubTotal();

			$opc_setting_shipping_methods = $this->config->get('opc_setting_shipping_methods');
			$free_shipping_data = $opc_setting_shipping_methods['default'];

			$free_shipping_from = isset($free_shipping_data['free_shipping_price']) && is_numeric($free_shipping_data['free_shipping_price']) ? (float)$free_shipping_data['free_shipping_price'] : 0;

			$data['free_shipping_status'] = isset($free_shipping_data['free_shipping_status']) ? $free_shipping_data['free_shipping_status'] : false;

			if($free_shipping_from == 0){
				$data['free_shipping_status'] = false;
			}

			if ($order_sum >= $free_shipping_from) {
				$fs_percentage = 100;
			} else {
				$fs_percentage = round(($order_sum / $free_shipping_from) * 100, 2);
			}

			$data['fs_percentage'] = $fs_percentage;

			$data['text_free_shipping'] = $this->language->get('text_free_shipping');
			$data['text_free_shipping_left'] = sprintf($this->language->get('text_free_shipping_left'), $this->currency->format($free_shipping_from - $order_sum, $this->session->data['currency']));

			if(!empty($data['products'])){
				$data['related'] = $this->relatedProducts();
			}

			$this->response->setOutput($this->load->view('extension/module/upstore_newfastordercart', $data));
		} else {
		  $this->response->redirect($this->url->link('error/not_found', '', true));
		}
  	}

  	public function relatedProducts() {

		$fo_related_products_status = $this->config->get('cart_related_products_status');
		$fo_related_products_setting = $this->config->get('cart_related_products_setting');
		$lang_id = $this->config->get('config_language_id');


		if($fo_related_products_status){

			$this->load->language('product/product');
			$this->load->model('checkout/onepcheckout');

			$product_ids = array();

			$data['title'] = (!empty($fo_related_products_setting['title'][$lang_id]) ? $fo_related_products_setting['title'][$lang_id] : false);
			$data['text_tax'] = $this->language->get('text_tax');
			$data['button_cart'] = $this->language->get('button_cart');

			$product_ids = [];

			foreach ( $this->cart->getProducts() as $product ) {
				if ( isset($product['product_id'] )) {
					$product_ids[] = (int)$product['product_id'];
				}
			}



			$data['image_width'] = 100;
			$data['image_height'] = 100;
			$fo_related_products_limit = (!empty($fo_related_products_setting['limit']) ? $fo_related_products_setting['limit'] : 15);
			$type_product_display = (!empty($fo_related_products_setting['type_product_display']) ? $fo_related_products_setting['type_product_display'] : 'related');
			$featured_products = (!empty($fo_related_products_setting['featured_products']) ? $fo_related_products_setting['featured_products'] : array());
			$shuffle_products = (!empty($fo_related_products_setting['shuffle_products']) ? $fo_related_products_setting['shuffle_products'] : false);

			$product_ids_implode = implode(',', $product_ids);
			setcookie('pids_in_cart', $product_ids_implode, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);

			$results = array();

			if(!empty($product_ids_implode) && ($type_product_display == 'related')){
				$results = $this->model_checkout_onepcheckout->getRelatedProducts($product_ids_implode);
			} elseif(!empty($featured_products) && ($type_product_display == 'featured')){
				$this->load->model('catalog/product');
				$results = $featured_products;
			}

			if($shuffle_products){
				shuffle($results);
			}

			$data['disable_cart_button'] = $this->config->get('config_disable_cart_button');
			$data['ch_type_btn'] = ($this->config->has('ch_type_btn') && $this->config->get('ch_type_btn') == 1) ? 'rounded' : 'squircle';

			$data['products'] = array();

			$pids_in_cart = array();
			if (isset($this->request->cookie['pids_in_cart'])) {
				$pids = explode(',', $this->request->cookie['pids_in_cart']);
				if(!empty($pids)){
					foreach ($pids as $pid) {
						$pids_in_cart[] = (int)$pid;
					}
				}
			}

			$notify_stock_status = !$this->config->get('config_stock_checkout') && ($this->config->get('upstore_notify_stock_status') == 1);
			$notify_stock_setting = $this->config->get('upstore_notify_stock_setting');
			$data['button_notify_stock'] = isset($notify_stock_setting['button_text'][$this->config->get('config_language_id')]) ? $notify_stock_setting['button_text'][$this->config->get('config_language_id')] : '';

			$pids_in_waitlist = [];
			if ($this->customer->isLogged() && $notify_stock_status) {
				$this->load->model('extension/module/upstore_notify_stock');
				$pids_in_waitlist = $this->model_extension_module_upstore_notify_stock->getProductsRequestsByCustomer();
			}


			if ($results) {
				$results = array_slice($results, 0, (int)$fo_related_products_limit);

				if (!empty($results) && is_array($results[0])) {
					$results = array_filter($results, function($result) use ($product_ids) {
						return !in_array((int)$result['product_id'], $product_ids);
					});
				} else {
					$results = array_filter($results, function($result) use ($product_ids) {
						return !in_array((int)$result, $product_ids);
					});
				}

				foreach ($results as $result) {

					if($type_product_display == 'featured'){
						$result = $this->model_catalog_product->getProduct($result);
						if(!isset($result['product_id'])){
							continue;
						}
					}

					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $data['image_width'], $data['image_height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $data['image_width'], $data['image_height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}

					$show_buy_button = true;

					if ($result['quantity'] <= 0 && $notify_stock_status) {
						$show_buy_button = false;
					}

					$data['products'][] = array(
						'show_buy_button' => $show_buy_button,
						'in_waitlist' 	=> in_array($result['product_id'], $pids_in_waitlist) ? true : false,
						'in_cart' 		=> in_array($result['product_id'], $pids_in_cart) ? true : false,
						'product_id'	=> $result['product_id'],
						'quantity'		=> $result['quantity'],
						'thumb'			=> $image,
						'name'			=> $result['name'],
						'price'			=> $price,
						'special'		=> $special,
						'tax'				=> $tax,
						'rating'			=> $rating,
						'href'			=> $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
			}

			if(!empty($data['products'])){
				return $data;
			}
		}
	}

  	public function addFastOrder() {

		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model('account/customer');

		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && (isset($this->request->post['action'])) && $this->request->server['REQUEST_METHOD'] == 'POST') {

			$json = array();
			if ($this->validate()) {
				$order_data = array();
				$lang_id = $this->config->get('config_language_id');

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

				$order_data['name_fastorder'] = '';

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

				if (isset($this->request->post['url_site'])) {
					$order_data['url_site'] = $this->request->post['url_site'];
				} else {
					$order_data['url_site'] = '';
				}

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;

				// Because __call can not keep var references so we put them into an array.
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				$this->load->model('setting/extension');

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


				$order_data['totals'] = $totals;

				$order_data['total'] = $total_data['total'];

				$this->load->model('tool/image');

				$order_data['products'] = array();

				foreach ($this->cart->getProducts() as $product) {
					if ($product['image']) {
						$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
					} else {
						$image = '';
					}
					$option_data = array();

					foreach ($product['option'] as $option) {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['value'],
							'type'                    => $option['type']
						);
					}

					$order_data['products'][] = array(
						'product_id' => $product['product_id'],
						'product_image' => $product['image'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'download'   => $product['download'],
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $product['price'],
						'total'      => $product['total'],
						'price_fast' => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')),
						'total_fast' => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'],
						'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward'     => $product['reward'],
						'currency_code' => $order_data['currency_code'],
						'currency_value' => $order_data['currency_value'],
					);
				}


				$order_data['total_fast'] = $total_data['total'];


				$this->load->model('extension/module/upstore_newfastorder');
				$this->load->model('checkout/order');

				$order_id = $this->model_checkout_order->addOrder($order_data);

				$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));

				$results = $this->model_extension_module_upstore_newfastorder->addOrder($order_id, $order_data);

				$json['success'] = sprintf($this->language->get('success_add_order'), $order_id);

				$this->cache->delete('product.bestseller');
				$this->cart->clear();

			} else {
				$json['error'] = $this->error;
			}
			return $this->response->setOutput(json_encode($json));
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}

	}
  	private function validate() {
  		$this->load->language('extension/module/upstore_newfastorder');

  		if ((utf8_strlen(trim($this->request->post['phone'])) < 1) || (utf8_strlen(trim($this->request->post['phone'])) > 32)) {
  			$this->error['phone'] = $this->language->get('error_phone');
  		}
  		if (!$this->error) {
  			return true;
  		} else {
  			return false;
  		}
	}

	public function editCartQuick() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->load->language('checkout/cart');
			$this->load->model('tool/image');

			$json = array();

		// Update
		if (isset($this->request->post['quantity']) && isset($this->request->post['key'])) {

			$this->cart->update($this->request->post['key'], $this->request->post['quantity']);

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
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
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
		}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
		  $this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}
}
?>
