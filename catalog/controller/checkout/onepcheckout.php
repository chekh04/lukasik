<?php
class ControllerCheckoutOnepcheckout extends Controller {

	public function index() {

		if(isset($this->session->data['shipping_address_id'])){
			unset($this->session->data['shipping_address_id']);
		}

		$this->document->addScript('catalog/view/javascript/opc/select2.min.js');
		$this->document->addStyle('catalog/view/javascript/opc/select2.min.css');

		$this->document->addStyle('catalog/view/javascript/opc/swiper/css/swiper.min.css');
		$this->document->addScript('catalog/view/javascript/opc/swiper/js/swiper.min.js');

		$this->document->addScript('catalog/view/javascript/opc/opc.js');
		$this->document->addStyle('catalog/view/javascript/opc/style.css');

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$data['opc_mask'] = $this->config->get('opc_mask');

		$opc_type_tel_mask = $this->config->get('opc_type_tel_mask');

		if($opc_type_tel_mask == 2){
			$this->document->addStyle('catalog/view/javascript/opc/intl-tel-input/css/intlTelInput.min.css');
			$this->document->addScript('catalog/view/javascript/opc/intl-tel-input/js/intlTelInputWithUtils.min.js');
		}

		$this->document->addScript('catalog/view/javascript/opc/maskedinput.js');

		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('checkout/onepcheckout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home'),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart'),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/onepcheckout', '', 'SSL'),
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_you_order'] = $this->language->get('text_you_order');
		$data['text_coupon'] = $this->language->get('text_coupon');
		$data['text_voucher'] = $this->language->get('text_voucher');
		$data['text_checkout_confirm'] = $this->language->get('text_checkout_confirm');

		$data['text_modify'] = $this->language->get('text_modify');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_continue'] = $this->language->get('button_continue');

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$data['opc_errors']['stock'] = $this->language->get('error_stock');
		}

		$opc_min_price_order = $this->config->get('opc_min_price_order');
		$customer_group_id = $this->config->get('config_customer_group_id');

		if ((!empty($opc_min_price_order[$customer_group_id]) && ($this->cart->getTotal() < $opc_min_price_order[$customer_group_id]))) {
			$data['opc_errors']['error_min_totals'] = sprintf($this->language->get('text_min_totals_order'), $this->currency->format($opc_min_price_order[$customer_group_id], $this->session->data['currency']));
		}

		if (!isset($this->session->data['guest']['customer_group_id'])) $this->session->data['guest']['customer_group_id'] = (int)$this->config->get('config_customer_group_id');

		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		if ($this->customer->isLogged()){
			$data['customer_id'] = $this->session->data['customer_id'];
			if (isset($this->session->data['checkout_customer_id']) && $this->session->data['checkout_customer_id'] === true){

				unset($this->session->data['checkout_customer_id']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['shipping_address']);
				unset($this->session->data['shipping_address_id']);
				unset($this->session->data['payment_address']);
				unset($this->session->data['payment_address_id']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				unset($this->session->data['guest']);
				unset($this->session->data['account']);
				unset($this->session->data['customer']);
				unset($this->session->data['shipping_country_id']);
				unset($this->session->data['shipping_zone_id']);
				unset($this->session->data['payment_country_id']);
				unset($this->session->data['payment_zone_id']);
			}
		}

		$opc_sorts_block = $this->config->get('opc_sorts_block');

		if(empty($opc_sorts_block)){
			$opc_sorts_block = array('cart' => 'top_left','free_text' => 'bottom_full','related_products' => 'top_full', 'country_region' => 'top_left','customer' => 'top_left','shipping_method' => 'center_left','payment_method' => 'center_right','shipping_address' => 'bottom_left','comment' => 'bottom_left','totals' => 'fix_right');
		}

		$opc_free_text_status = $this->config->get('opc_free_text_status');
		$opc_related_products_status = $this->config->get('opc_related_products_status');
		$opc_country_region_status = $this->config->get('opc_country_region_status');

		$data['opc_blocks'] = array();

		foreach($opc_sorts_block as $block_name => $block_position){
			if($block_name == 'free_text' && $opc_free_text_status == 0){
				continue;
			}
			if($block_name == 'country_region' && $opc_country_region_status == 0){
				continue;
			}
			if($block_name == 'related_products' && $opc_related_products_status == 0){
				continue;
			}

			switch ($block_position) {
				case 'top_full':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
				case 'top_left':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
				case 'center_left':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
				case 'center_right':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
				case 'bottom_left':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
				case 'bottom_full':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
				case 'fix_right':
					$data['opc_blocks'][$block_position][] = $block_name;
					break;
			}
		}

		$data['opc_block']['shipping_method'] = $this->shipping_method(false, $data);
		$data['opc_block']['country_region'] = $this->country_region(false, $data);
		$data['opc_block']['shipping_address'] = $this->shipping_address(false, $data);
		$data['opc_block']['payment_method'] = $this->payment_method(false, $data);
		$data['opc_block']['customer'] = $this->customer(false, $data);
		$data['opc_block']['cart'] = $this->cart(false, $data);
		$data['opc_block']['totals'] = $this->totals(false, $data);
		$data['opc_block']['comment'] = $this->comment();
		$data['opc_block']['free_text'] = $this->freeText();
		$data['opc_block']['related_products'] = $this->relatedProducts();

		if (isset($this->session->data['shipping_address']['country_id']) && ($this->session->data['shipping_address']['country_id'] !='')) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['opc_geoip_auto_detect_status'])) {
			$data['opc_geoip_auto_detect_status'] = $this->request->post['opc_geoip_auto_detect_status'];
		} elseif(!empty($this->config->get('opc_geoip_auto_detect_status'))) {
			$data['opc_geoip_auto_detect_status'] = $this->config->get('opc_geoip_auto_detect_status');
		} else {
			$data['opc_geoip_auto_detect_status'] = 0;
		}

		if(!empty($this->config->get('opc_geoip_auto_detect_status')) && ($this->config->get('opc_geoip_auto_detect_status') == 1)) {
			$initial_country = 'auto';
		} else {
			$initial_country = (!empty($this->config->get('opc_initial_country')) ? $this->config->get('opc_initial_country') : '');
		}

		$data['opc_setting'] = array(
			'text_select' 		=> $this->language->get('text_select'),
			'tel_mask'			=> (!empty($this->config->get('opc_mask')) ? $this->config->get('opc_mask') : ''),
			'load_script' 		=> (!empty($this->config->get('opc_javascript')) ? html_entity_decode($this->config->get('opc_javascript'), ENT_QUOTES, 'UTF-8') : ''),
			'only_countries' 		=> (!empty($this->config->get('opc_only_countries')) ? $this->config->get('opc_only_countries') : ''),
			'exclude_countries' 	=> (!empty($this->config->get('opc_exclude_countries')) ? $this->config->get('opc_exclude_countries') : ''),
			'initial_country' 	=> $initial_country,
			'geoip_status' 		=> (!empty($this->config->get('opc_geoip_auto_detect_status')) ? $this->config->get('opc_geoip_auto_detect_status') : false)
		);

		$opc_cr_width = $this->config->get('opc_cr_width');
		$opc_cl_width = $this->config->get('opc_cl_width');

		$data['opc_cr_width'] = '30';
		$data['opc_cl_width'] = '70';

		if(isset($opc_cr_width) && ($opc_cr_width > 0)){
			$data['opc_cr_width'] = $opc_cr_width;
		}

		if(isset($opc_cl_width) && ($opc_cl_width > 0)){
			$data['opc_cl_width'] = $opc_cl_width;
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/onepcheckout', $data));
	}

	public function relatedProducts() {

		$opc_related_products_status = $this->config->get('opc_related_products_status');
		$opc_related_products_setting = $this->config->get('opc_related_products_setting');
		$lang_id = $this->config->get('config_language_id');


		if($opc_related_products_status){

			$this->load->language('product/product');
			$this->load->model('checkout/onepcheckout');

			$product_ids = array();

			$data['title_related_products'] = (!empty($opc_related_products_setting['title'][$lang_id]) ? $opc_related_products_setting['title'][$lang_id] : false);
			$data['text_tax'] = $this->language->get('text_tax');
			$data['button_cart'] = $this->language->get('button_cart');

			foreach ( $this->cart->getProducts() as $product ) {
				if ( isset($product['product_id'] )) {
					$product_ids[] = (int)$product['product_id'];
				}
			}

			$data['image_width'] = (!empty($opc_related_products_setting['image_width']) ? $opc_related_products_setting['image_width'] : 120);
			$data['image_height'] = (!empty($opc_related_products_setting['image_height']) ? $opc_related_products_setting['image_height'] : 120);
			$opc_related_products_limit = (!empty($opc_related_products_setting['limit']) ? $opc_related_products_setting['limit'] : 15);
			$type_product_display = (!empty($opc_related_products_setting['type_product_display']) ? $opc_related_products_setting['type_product_display'] : 'related');
			$featured_products = (!empty($opc_related_products_setting['featured_products']) ? $opc_related_products_setting['featured_products'] : array());
			$shuffle_products = (!empty($opc_related_products_setting['shuffle_products']) ? $opc_related_products_setting['shuffle_products'] : false);

			$product_ids_implode = implode(',', $product_ids);

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

			$data['products'] = array();

			if ($results) {

				$results = array_slice($results, 0, (int)$opc_related_products_limit);

				foreach ($results as $result) {

					if($type_product_display == 'featured'){
						$result = $this->model_catalog_product->getProduct($result);
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

					$data['products'][] = array(
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
			}

			if(!empty($data['products'])){
				return $this->load->view('checkout/onepcheckout_related_products', $data);
			}
		}
	}

	public function authorization() {

		$this->load->language('checkout/onepcheckout');
		$data['text_login'] = $this->language->get('text_login');
		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['text_register'] = $this->language->get('text_register');
		$data['text_forgotten'] = $this->language->get('text_forgotten');
		$data['button_login'] = $this->language->get('button_login');

		$data['register'] = $this->url->link('account/register', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);

		$this->response->setOutput($this->load->view('checkout/onepcheckout_login', $data));
	}

	public function reloadAll() {
		$json = array();

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['shipping_method'] = $this->shipping_method(false);
			$json['country_region'] = $this->country_region(false);
			$json['shipping_address'] = $this->shipping_address(false);
			$json['payment_method'] = $this->payment_method(false);
			$json['customer'] = $this->customer(false);
			$json['cart'] = $this->cart(false);
			$json['totals'] = $this->totals(false);
			$json['opc_errors'] = $this->opc_errors();
			$json['related_products'] = $this->relatedProducts();

			$this->abandonedOrders();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function opc_errors() {
		$data = array();

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$data['stock'] = $this->language->get('error_stock');
		}

		$opc_min_price_order = $this->config->get('opc_min_price_order');
		$customer_group_id = $this->config->get('config_customer_group_id');

		if ((!empty($opc_min_price_order[$customer_group_id]) && ($this->cart->getTotal() < $opc_min_price_order[$customer_group_id]))) {
			$data['error_min_totals'] = sprintf($this->language->get('text_min_totals_order'), $this->currency->format($opc_min_price_order[$customer_group_id], $this->session->data['currency']));
		}

		return $data;
	}

	public function validate_authorization() {
		$this->load->language('checkout/checkout');

		$json = array();
		$this->load->model('account/customer');

		if ($this->customer->isLogged()) {
			$json['islogged'] = true;
		}else if(isset($this->request->post)) {

			// Check how many login attempts have been made.
			$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['emailpopup']);

			if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
				$json['error'] = $this->language->get('error_attempts');
			}

			// Check if customer has been approved.
			$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['emailpopup']);

			if ($customer_info && !$customer_info['status']) {
				$json['error'] = $this->language->get('error_approved');
			}

		} else {
			$json['error'] = $this->language->get('error_warning');
		}

		if (!isset($json['error'])) {
			if (!$this->customer->login($this->request->post['emailpopup'], $this->request->post['passwordpopup'])) {
				$json['error'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($this->request->post['emailpopup']);
			} else {
					// Unset guest
				$json['success'] = true;

				unset($this->session->data['guest']);
				unset($this->session->data['customer']);

					// Default Shipping Address
				$this->load->model('account/address');

				if ($this->config->get('config_tax_customer') == 'payment') {
					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
				}

				if ($this->config->get('config_tax_customer') == 'shipping') {
					$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
				}

					// Wishlist
				if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
					$this->load->model('account/wishlist');

					foreach ($this->session->data['wishlist'] as $key => $product_id) {
						$this->model_account_wishlist->addWishlist($product_id);

						unset($this->session->data['wishlist'][$key]);
					}
				}

				$this->model_account_customer->deleteLoginAttempts($this->request->post['emailpopup']);
			}
		}

		$this->session->data['checkout_customer_id'] = true;
		$this->session->data['customer_id'] = $this->customer->getId();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function freeText() {

		$opc_free_text_status = $this->config->get('opc_free_text_status');
		$free_text = $this->config->get('free_text');
		$lang_id = $this->config->get('config_language_id');

		$free_text_status = false;

		if(isset($opc_free_text_status) && $opc_free_text_status == 1){
			$free_text_status = true;
		}

		$data['free_text'] = '';

		if(!empty( strip_tags(html_entity_decode($free_text[$lang_id]['text'], ENT_QUOTES, 'UTF-8')) )){
			$data['free_text'] = html_entity_decode($free_text[$lang_id]['text'], ENT_QUOTES, 'UTF-8');
		} else {
			$free_text_status = false;
		}

		if($free_text_status){
			return $this->load->view('checkout/onepcheckout_text', $data);
		}
	}

	public function comment() {

		$opc_comment_setting = $this->config->get('opc_comment_setting');

		$data['lang_id'] = $this->config->get('config_language_id');

		$data['opc_comment_setting'] = array(
			'status' 						 => !empty($opc_comment_setting['status']) ? $opc_comment_setting['status'] : false,
			'text_comments' 				 => !empty($opc_comment_setting['name_field'][$this->config->get('config_language_id')]) ? $opc_comment_setting['name_field'][$this->config->get('config_language_id')] : $this->language->get('text_comments'),
			'text_placeholder_comments' => !empty($opc_comment_setting['placeholder_field'][$this->config->get('config_language_id')]) ? $opc_comment_setting['placeholder_field'][$this->config->get('config_language_id')] : $this->language->get('text_comments'),
		);

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}

		if(!empty($opc_comment_setting) && $opc_comment_setting['status']){
			return $this->load->view('checkout/onepcheckout_comment', $data);
		}
	}

	public function customer($render = true, &$data  = array()) {

		$this->load->language('checkout/checkout');
		$this->load->language('checkout/onepcheckout');

		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_op_customer'] = $this->language->get('text_op_customer');
		$data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$data['text_i_am_registered'] = $this->language->get('text_i_am_registered');
		$data['text_register'] = $this->language->get('text_register');

		$data['entry_firstname'] = $data['entry_ph_firstname'] = $this->language->get('entry_firstname');
		$data['entry_lastname'] = $data['entry_ph_lastname'] = $this->language->get('entry_lastname');
		$data['entry_email'] = $data['entry_ph_email'] = $this->language->get('entry_email');
		$data['entry_telephone'] = $data['entry_ph_telephone'] = $this->language->get('entry_telephone');
		$data['entry_fax'] = $data['entry_ph_fax'] = $this->language->get('entry_fax');

		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_confirm'] = $this->language->get('entry_confirm');
		$data['status_email'] = false;

		if (isset($this->session->data['customer_id'])){
			$data['customer_id'] = $this->session->data['customer_id'];
		}

		$data['opc_type_tel_mask'] = $this->config->get('opc_type_tel_mask');

		$customer_fields = array();

		$customer_methods_data = $this->config->get('opc_customer_setting');

		if(isset($this->session->data['shipping_method']) && (!empty($this->session->data['shipping_method']['code'] !=''))){
			$shipping_code = str_replace(".","_",$this->session->data['shipping_method']['code']);

			if(!empty($customer_methods_data[$shipping_code])){
				$customer_fields = $customer_methods_data[$shipping_code];
			} else {
				$customer_fields = $customer_methods_data['default'];
			}
		} else {
			$customer_fields = $customer_methods_data['default'];
		}

		$customer_logged = false;

		if ($this->customer->isLogged()) {
			$customer_logged = true;
		}

		$this->load->model('checkout/onepcheckout');

		$this->load->model('account/customer_group');

		$data['customer_groups'] = array();

		if (is_array($this->config->get('config_customer_group_display'))) {
			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$data['customer_groups'][] = $customer_group;
				}
			}
		}

		if ($this->customer->isLogged()) {
			$customer_group_id = $data['customer_group_id'] = $this->config->get('config_customer_group_id');
		} else {
			if(isset($this->request->post['customer_group_id'])) {
				$customer_group_id = $data['customer_group_id'] = $this->session->data['guest']['customer_group_id'] = $this->request->post['customer_group_id'];
			} elseif (isset($this->session->data['guest']['customer_group_id'])) {
				$customer_group_id = $data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
			} else {
				$customer_group_id = $data['customer_group_id'] = $this->config->get('config_customer_group_id');
			}
		}

		$customer_custom_fields = $this->model_checkout_onepcheckout->getCustomFields('opc_customer', $customer_group_id);

		$data['customer_fields'] = array();

		foreach($customer_fields as $field_key => $customer_field){
			if(is_array($customer_field)){
				if(isset($customer_field['status']) && ($customer_field['status'] != '0')){

					if($customer_logged && ($customer_field['show_when'] == 'only_quest')){
						continue;
					}

					if(!$customer_logged && ($customer_field['show_when'] == 'only_authorized')){
						continue;
					}

					if(!empty($customer_field['setting']['name_field'][$this->config->get('config_language_id')])){
						$data['entry_' . $field_key] = $customer_field['setting']['name_field'][$this->config->get('config_language_id')];
					}
					if(!empty($customer_field['setting']['placeholder_field'][$this->config->get('config_language_id')])){
						$data['entry_ph_' . $field_key] = $customer_field['setting']['placeholder_field'][$this->config->get('config_language_id')];
					}

					$data['customer_fields'][$field_key] = array(
						'status'	=> $customer_field['status'],
					);
				}

				if(strpos($field_key, 'custom_field_') === 0){
					if(!empty($customer_custom_fields[$customer_field['id']])){
						if($customer_logged && ($customer_field['show_when'] == 'only_quest')){
							continue;
						}

						if(!$customer_logged && ($customer_field['show_when'] == 'only_authorized')){
							continue;
						}
						$data['customer_fields'][$field_key] = $customer_custom_fields[$customer_field['id']];
					}
				}
			}
		}

		$register_status = (!empty($this->config->get('opc_register_status')) ? $this->config->get('opc_register_status') : 0);

		$data['register_status'] = true;

		if ($this->customer->isLogged()){
			$data['register_status'] = false;
		}

		$data['register_required'] = false;
		if($register_status == 1){
			$data['register_required'] = true;
		}

		if($register_status == 2){
			$data['register_status'] = false;
		}

		$data['register_checked'] = false;

		if (!$this->customer->isLogged() && isset($this->request->post['register']) || (!$this->customer->isLogged() && ($register_status == 1))){
			$data['register_checked'] = true;
			$data['customer_fields']['email']['status'] = 'required';
		}

		if(isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->session->data['customer']['firstname'] = $this->request->post['firstname'];
		} elseif (isset($this->session->data['customer']['firstname'])) {
			$data['firstname'] = $this->session->data['customer']['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if(isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->session->data['customer']['lastname'] = $this->request->post['lastname'];
		} elseif (isset($this->session->data['customer']['lastname'])) {
			$data['lastname'] = $this->session->data['customer']['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if(isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->session->data['customer']['telephone'] = $this->request->post['telephone'];
		} elseif (isset($this->session->data['customer']['telephone'])) {
			$data['telephone'] = $this->session->data['customer']['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if(isset($this->request->post['fax'])) {
			$data['fax'] = $this->session->data['customer']['fax'] = $this->request->post['fax'];
		} elseif (isset($this->session->data['customer']['fax'])) {
			$data['fax'] = $this->session->data['customer']['fax'];
		} else {
			$data['fax'] = '';
		}

		if(isset($this->request->post['email'])) {
			$data['email'] = $this->session->data['customer']['email'] = $this->request->post['email'];
		} elseif (isset($this->session->data['customer']['email'])) {
			$data['email'] = $this->session->data['customer']['email'];
		} else {
			$data['email'] = '';
		}

		if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			$data['email'] = $data['email'];
		} else {
			$data['email'] = '';
		}

		if ($this->customer->isLogged()){
			$this->load->model('account/address');
			$data['firstname'] =  (!empty($this->session->data['customer']['firstname'])) ? $this->session->data['customer']['firstname'] : $this->customer->getFirstName();
			$data['lastname'] = (!empty($this->session->data['customer']['lastname'])) ? $this->session->data['customer']['lastname'] : $this->customer->getLastName();
			$data['email'] =  $this->customer->getEmail();
			$data['telephone'] = (!empty($this->session->data['customer']['telephone'])) ? $this->session->data['customer']['telephone'] : $this->customer->getTelephone();
			$data['payment_address_id'] = $this->customer->getAddressId();
			$data['address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
		}

		if(isset($this->request->post['custom_field']['opc_customer'])) {
			$data['customer_custom_field'] = $this->session->data['guest']['opc_customer_custom_field'] = $this->request->post['custom_field']['opc_customer'];
		} elseif (isset($this->session->data['guest']['opc_customer_custom_field'])) {
			$data['customer_custom_field'] = $this->session->data['guest']['opc_customer_custom_field'];
		} else {
			$data['customer_custom_field'] = [];
		}

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_customer', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_customer', $data);
		}
	}

	public function country($data = array()) {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if(!empty($this->session->data['shipping_address']['zone_id'])){
			$active_zone_id = $this->session->data['shipping_address']['zone_id'];
		} else {
			$active_zone_id = 0;
		}

		if(!empty($this->session->data['shipping_address']['zone_id'])){
			$active_szone_id = $this->session->data['shipping_address']['zone_id'];
		} else {
			$active_szone_id = 0;
		}

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'active_zone_id'    => $active_zone_id,
				'active_szone_id'   => $active_szone_id,
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function validate($data  = array()) {
		$json = array();

		$this->load->language('checkout/onepcheckout');
		$this->load->language('checkout/checkout');
		$this->load->language('checkout/cart');

		$this->load->model('account/customer');

		// Validate cart has products and has stock.

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['error']['warning'] = $this->language->get('error_stock');
		}

		$opc_min_price_order = $this->config->get('opc_min_price_order');
		$customer_group_id = $this->config->get('config_customer_group_id');

		if ((!empty($opc_min_price_order[$customer_group_id]) && ($this->cart->getTotal() < $opc_min_price_order[$customer_group_id]))) {
			$json['error']['warning'] = sprintf($this->language->get('text_min_totals_order'), $this->currency->format($opc_min_price_order[$customer_group_id], $this->session->data['currency']));
		}

		// Validate minimum quantity requirments.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['error']['warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

				break;
			}
		}

		if (!$this->customer->isLogged() && !isset($this->request->post['register'])) {
			if (!$this->config->get('config_checkout_guest') || $this->config->get('config_customer_price')) {
				$json['error']['warning'] = $this->language->get('error_register');
			}
		}

		// Customer Group
		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$this->load->model('checkout/onepcheckout');

		$customer_custom_fields = $this->model_checkout_onepcheckout->getCustomFields('opc_customer', $this->config->get('config_customer_group_id'));
		$address_custom_fields = $this->model_checkout_onepcheckout->getCustomFields('opc_address', $this->config->get('config_customer_group_id'));
		$payment_custom_fields = $this->model_checkout_onepcheckout->getCustomFields('opc_payment', $this->config->get('config_customer_group_id'));

		$customer_fields = array();
		$shipping_methods_fields = array();

		$customer_methods_data = $this->config->get('opc_customer_setting');
		$shipping_methods_data = $this->config->get('opc_payment_address');

		if(isset($this->session->data['shipping_method']) && (!empty($this->session->data['shipping_method']['code'] !=''))){
			$shipping_code = str_replace(".","_",$this->session->data['shipping_method']['code']);

			if(!empty($customer_methods_data[$shipping_code])){
				$customer_fields = $customer_methods_data[$shipping_code];
			} else {
				$customer_fields = $customer_methods_data['default'];
			}

			if(!empty($shipping_methods_data[$shipping_code])){
				$shipping_methods_fields = $shipping_methods_data[$shipping_code];
			} else {
				$shipping_methods_fields = $shipping_methods_data['default'];
			}

		} else {
			$customer_fields = $customer_methods_data['default'];
			$shipping_methods_fields = $shipping_methods_data['default'];
		}

		//$this->save_fields();

		// Customer & shipping_address validate
		if (!$json) {

			if ($this->config->get('config_checkout_id')) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

				if ($information_info && !isset($this->request->post['agree'])) {
					$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}

			$customer_logged = false;

			if ($this->customer->isLogged()) {
				$customer_logged = true;
			}

			$opc_payment_option = $this->config->get('opc_payment_option');

			if(isset($this->request->post['payment_method'])){
				$code_pm = $this->request->post['payment_method'];

				if (!empty($opc_payment_option[$code_pm]['custom_fields'])) {
					foreach ($opc_payment_option[$code_pm]['custom_fields'] as $pcf_id => $pcf_value) {
						$custom_field = $payment_custom_fields[$pcf_id];

						if((!$customer_logged && ($pcf_value['show_when'] == 'only_quest')) || ($customer_logged && ($pcf_value['show_when'] == 'only_authorized')) || ($pcf_value['show_when'] == 'all_client')){
							if (($custom_field['location'] == 'opc_payment') && $custom_field['required'] && empty($this->request->post['custom_field']['opc_payment'][$custom_field['custom_field_id']])) {
								if (!empty($custom_field['text_error'])) {
									$json['error']['custom_field' . $custom_field['custom_field_id']] = $custom_field['text_error'];
								} else {
									$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
								}
							} elseif (($custom_field['location'] == 'opc_payment') && ($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field']['opc_payment'][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
								if (!empty($custom_field['text_error'])) {
									$json['error']['custom_field' . $custom_field['custom_field_id']] = $custom_field['text_error'];
								} else {
									$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
								}
							}
						}
					}
				}
			}

			if (!$this->customer->isLogged() && isset($this->request->post['register'])){
				$customer_fields['email']['status'] = 'required';
			}

			foreach($customer_fields as $field_key => $customer_field){
				if(is_array($customer_field)){

					if ($field_key == 'firstname' && isset($customer_field['status']) && $customer_field['status'] == 'required') {
						$opc_firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
						if((!$customer_logged && ($customer_field['show_when'] == 'only_quest')) || ($customer_logged && ($customer_field['show_when'] == 'only_authorized')) || ($customer_field['show_when'] == 'all_client')){
							if ((utf8_strlen( $opc_firstname ) < 1) || (utf8_strlen( $opc_firstname ) > 32)) {
								$json['error']['firstname'] = !empty($customer_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customer_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_firstname');
							}
						}
					}

					if ($field_key == 'lastname' && isset($customer_field['status']) && $customer_field['status'] == 'required') {
						$opc_lastname = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
						if((!$customer_logged && ($customer_field['show_when'] == 'only_quest')) || ($customer_logged && ($customer_field['show_when'] == 'only_authorized')) || ($customer_field['show_when'] == 'all_client')){
							if (((utf8_strlen($opc_lastname) < 1) || (utf8_strlen($opc_lastname) > 32))) {
								$json['error']['lastname'] = !empty($customer_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customer_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_lastname');
							}
						}
					}

					if ($field_key == 'telephone' && isset($customer_field['status']) && $customer_field['status'] == 'required') {
						$opc_telephone = isset($this->request->post['telephone']) ? $this->request->post['telephone'] : '';
						if((!$customer_logged && ($customer_field['show_when'] == 'only_quest')) || ($customer_logged && ($customer_field['show_when'] == 'only_authorized')) || ($customer_field['show_when'] == 'all_client')){
							if (((utf8_strlen($opc_telephone) < 3) || (utf8_strlen($opc_telephone) > 32))) {
								$json['error']['telephone'] = !empty($customer_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customer_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_telephone');
							}

							$opc_type_tel_mask = $this->config->get('opc_type_tel_mask');

							if($opc_type_tel_mask == 1){
								$reg_tel = $this->config->get('opc_tel_validation');
								if(!empty($reg_tel) && (!isset($json['error']['telephone']))){
									if (!preg_match($reg_tel, $opc_telephone)) {
										$json['error']['telephone'] = $this->language->get('error_format_telephone');
									}
								}
							} else {
								$country_code = isset($this->request->post['country_code']) ? $this->request->post['country_code'] : '';
								$multi_tel_validations = $this->config->get('opc_multi_tel_validations');

								if (!empty($multi_tel_validations) && (!isset($json['error']['telephone']))) {
									foreach ($multi_tel_validations as $validation) {
										if (isset($validation['country_code']) && $validation['country_code'] == $country_code) {
											if (!preg_match($validation['regex'], $opc_telephone)) {
												$json['error']['telephone'] = $this->language->get('error_format_telephone');
											}
											break;
										}
									}
								}
							}
						}
					}

					if ($field_key == 'fax' && isset($customer_field['status']) && $customer_field['status'] == 'required') {
						$opc_fax = isset($this->request->post['fax']) ? trim($this->request->post['fax']) : '';
						if((!$customer_logged && ($customer_field['show_when'] == 'only_quest')) || ($customer_logged && ($customer_field['show_when'] == 'only_authorized')) || ($customer_field['show_when'] == 'all_client')){
							if (((utf8_strlen($opc_fax) < 1) || (utf8_strlen($opc_fax) > 264))) {
								$json['error']['fax'] = !empty($customer_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customer_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_fax');
							}
						}
					}

					if ($field_key == 'email' && isset($customer_field['status']) && $customer_field['status'] == 'required' || (!empty($this->request->post['email']))) {
						$opc_email = isset($this->request->post['email']) ? $this->request->post['email'] : '';
						if((!$customer_logged && ($customer_field['show_when'] == 'only_quest')) || ($customer_logged && ($customer_field['show_when'] == 'only_authorized')) || ($customer_field['show_when'] == 'all_client')){
							if ((utf8_strlen($opc_email) > 96) || !filter_var($opc_email, FILTER_VALIDATE_EMAIL)) {
								$json['error']['email'] = !empty($customer_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $customer_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_email');
							}
						}

						if (!$this->customer->isLogged()){
							if (isset($this->request->post['register']) && !empty($this->request->post['register'])){
								if ($this->model_account_customer->getTotalCustomersByEmail($opc_email)) {
									$json['error']['warning'] = $this->language->get('error_exists');
								}
							}
						}
					}

					if (strpos($field_key, 'custom_field_') === 0) {
						if (!empty($customer_custom_fields[$customer_field['id']])) {
							$custom_field = $customer_custom_fields[$customer_field['id']];
							if((!$customer_logged && ($customer_field['show_when'] == 'only_quest')) || ($customer_logged && ($customer_field['show_when'] == 'only_authorized')) || ($customer_field['show_when'] == 'all_client')){
								if (($custom_field['location'] == 'opc_customer') && $custom_field['required'] && empty($this->request->post['custom_field']['opc_customer'][$custom_field['custom_field_id']])) {
									if (!empty($custom_field['text_error'])) {
										$json['error']['customer_custom_field' . $custom_field['custom_field_id']] = $custom_field['text_error'];
									} else {
										$json['error']['customer_custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
									}
								} elseif (($custom_field['location'] == 'opc_customer') && ($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field']['opc_customer'][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
									if (!empty($custom_field['text_error'])) {
										$json['error']['customer_custom_field' . $custom_field['custom_field_id']] = $custom_field['text_error'];
									} else {
										$json['error']['customer_custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
									}
								}
							}
						}
					}
				}
			}

			$opc_country_region_status = $this->config->get('opc_country_region_status');

			foreach($shipping_methods_fields as $field_key => $shipping_field){
				if(is_array($shipping_field)){

					if ($field_key == 'country' && (isset($shipping_field['status']) && $shipping_field['status'] == 'required') || ($opc_country_region_status == 1)) {
						$opc_country_id = isset($this->request->post['country_id']) ? $this->request->post['country_id'] : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if ($opc_country_id == '') {
								$json['error']['country_id'] = $this->language->get('error_country');
							}
						}
					}

					if ($field_key == 'zone_id' && (isset($shipping_field['status']) && $shipping_field['status'] == 'required') || ($opc_country_region_status == 1)) {
						$opc_zone_id = isset($this->request->post['zone_id']) ? $this->request->post['zone_id'] : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if ($opc_zone_id == '') {
								$json['error']['zone_id'] = $this->language->get('error_zone');
							}
						}
					}

					if ($field_key == 'city' && isset($shipping_field['status']) && $shipping_field['status'] == 'required') {
						$opc_city = isset($this->request->post['city']) ? trim($this->request->post['city']) : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if (!empty($shipping_field['setting']['custom_fields']) && ($shipping_field['setting']['type'] != 'input')) {
								if (((utf8_strlen($opc_city) < 1) || (utf8_strlen($opc_city) > 3))) {
									$json['error']['city'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_city');
								}
							} elseif ((utf8_strlen($opc_city) < 3) || (utf8_strlen($opc_city) > 128)) {
								$json['error']['city'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_city');
							}
						}
					}

					if ($field_key == 'address_1' && isset($shipping_field['status']) && $shipping_field['status'] == 'required') {
						$opc_address_1 = isset($this->request->post['address_1']) ? trim($this->request->post['address_1']) : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if (!empty($shipping_field['setting']['custom_fields']) && ($shipping_field['setting']['type'] != 'input')) {
								if ((utf8_strlen($opc_address_1) < 1) || (utf8_strlen($opc_address_1) > 3)) {
									$json['error']['address_1'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_1');
								}
							} elseif ((utf8_strlen($opc_address_1) < 1) || (utf8_strlen($opc_address_1) > 128)) {
								$json['error']['address_1'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_1');
							}
						}
					}

					if ($field_key == 'address_2' && isset($shipping_field['status']) && $shipping_field['status'] == 'required') {
						$opc_address_2 = isset($this->request->post['address_2']) ? trim($this->request->post['address_2']) : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if (!empty($shipping_field['setting']['custom_fields']) && ($shipping_field['setting']['type'] != 'input')) {
								if ((utf8_strlen($opc_address_2) < 1) || (utf8_strlen($opc_address_2) > 3)) {
									$json['error']['address_2'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_2');
								}
							} elseif ((utf8_strlen($opc_address_2) < 1) || (utf8_strlen($opc_address_2) > 128)) {
								$json['error']['address_2'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_address_2');
							}
						}
					}

					if ($field_key == 'company' && isset($shipping_field['status']) && $shipping_field['status'] == 'required') {
						$opc_company = isset($this->request->post['company']) ? trim($this->request->post['company']) : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if (!empty($shipping_field['setting']['custom_fields']) && ($shipping_field['setting']['type'] != 'input')) {
								if ((utf8_strlen($opc_company) < 1) || (utf8_strlen($opc_company) > 3)) {
									$json['error']['company'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_company');
								}
							} elseif ((utf8_strlen($opc_company) < 1) || (utf8_strlen($opc_company) > 128)) {
								$json['error']['company'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_company');
							}
						}
					}

					if ($field_key == 'postcode' && isset($shipping_field['status']) && $shipping_field['status'] == 'required') {
						$opc_postcode = isset($this->request->post['postcode']) ? trim($this->request->post['postcode']) : '';
						if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
							if (!empty($shipping_field['setting']['custom_fields']) && ($shipping_field['setting']['type'] != 'input')) {
								if ((utf8_strlen($opc_postcode) < 1) || (utf8_strlen($opc_postcode) > 3)) {
									$json['error']['postcode'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_postcode');
								}
							} elseif ((utf8_strlen($opc_postcode) < 1) || (utf8_strlen($opc_postcode) > 128)) {
								$json['error']['postcode'] = !empty($shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')]) ? $shipping_field['setting']['text_error_field'][$this->config->get('config_language_id')] : $this->language->get('error_postcode');
							}
						}
					}

					if (strpos($field_key, 'custom_field_') === 0) {
						if (!empty($address_custom_fields[$shipping_field['id']])) {
							$custom_field = $address_custom_fields[$shipping_field['id']];
							if((!$customer_logged && ($shipping_field['show_when'] == 'only_quest')) || ($customer_logged && ($shipping_field['show_when'] == 'only_authorized')) || ($shipping_field['show_when'] == 'all_client')){
								if (($custom_field['location'] == 'opc_address') && $custom_field['required'] && empty($this->request->post['custom_field']['opc_address'][$custom_field['custom_field_id']])) {
									if (!empty($custom_field['text_error'])) {
										$json['error']['address_custom_field' . $custom_field['custom_field_id']] = $custom_field['text_error'];
									} else {
										$json['error']['address_custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
									}
								} elseif (($custom_field['location'] == 'opc_address') && ($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field']['opc_address'][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
									if (!empty($custom_field['text_error'])) {
										$json['error']['address_custom_field' . $custom_field['custom_field_id']] = $custom_field['text_error'];
									} else {
										$json['error']['address_custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
									}
								}
							}
						}
					}
				}
			}

			if (isset($this->request->post['register']) && !empty($this->request->post['register'])){
				if (isset($this->request->post['register']) && ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20))) {
					$json['error']['password'] = $this->language->get('error_password');
				}

				if (isset($this->request->post['confirm']) && ($this->request->post['confirm'] != $this->request->post['password'])) {
					$json['error']['confirm'] = $this->language->get('error_confirm');
				}
			}
		}

		if (!$json) {
			if (!isset($this->request->post['shipping_method'])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			} else {
				$shipping = explode('.', $this->request->post['shipping_method']);
				if (!isset($shipping[0]) || !isset($shipping[1])) {
					$json['error']['warning'] = $this->language->get('error_shipping');
				}
			}

			if (!$json) {
				$shipping = explode('.', $this->request->post['shipping_method']);

				if (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])){
					$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
				}
			}
		}

		if (!$json) {
			if (!isset($this->request->post['payment_method'])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			}

			if (!$json) {
				$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
			}
		}


		if(!isset($json['error'])){

			if (isset($this->request->post['register']) && !empty($this->request->post['register'])){
				$this->session->data['account'] = 'register';
				if (!$this->customer->isLogged()){
					$opc_customer_data = array(
						'fax'				=> (isset($this->request->post['fax'])) ? $this->request->post['fax'] : '',
						'email'			=> (isset($this->request->post['email'])) ? $this->request->post['email'] : '',
						'telephone'		=> (isset($this->request->post['telephone'])) ? $this->request->post['telephone'] : '',
						'firstname'		=> (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '',
						'lastname'		=> (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '',
						'company'		=> (isset($this->request->post['company'])) ? $this->request->post['company'] : '',
						'address_1'		=> (isset($this->request->post['address_1'])) ? $this->request->post['address_1'] : '',
						'address_2'		=> (isset($this->request->post['address_2'])) ? $this->request->post['address_2'] : '',
						'city'			=> (isset($this->request->post['city'])) ? $this->request->post['city'] : '',
						'postcode'		=> (isset($this->request->post['postcode'])) ? $this->request->post['postcode'] : '',
						'country_id'	=> (isset($this->request->post['country_id'])) ? $this->request->post['country_id'] : '',
						'zone_id'		=> (isset($this->request->post['zone_id'])) ? $this->request->post['zone_id'] : '',
						'password'		=> (isset($this->request->post['password'])) ? $this->request->post['password'] : '',
					);
					$this->session->data['customer_id'] = $customer_id = $this->model_account_customer->addCustomer($opc_customer_data);
					$this->session->data['checkout_customer_id'] = true;
				}

				$this->load->model('account/customer_group');

				$customer_group = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

				$this->customer->login($this->request->post['email'], $this->request->post['password']);

				unset($this->session->data['guest']);

					// Add to activity log
				$this->load->model('account/activity');

				$activity_data = array(
					'customer_id' => $customer_id,
					'name'        => $this->request->post['firstname'] . ' ' . $this->request->post['lastname']
				);

				$this->model_account_activity->addActivity('register', $activity_data);
				$this->registry->set('cart', new Cart\Cart($this->registry));
			} elseif(!isset($this->session->data['customer_id'])) {

				$this->session->data['account'] = 'guest';
				$this->session->data['guest']['customer_group_id'] = $customer_group_id;
				$this->session->data['guest']['firstname'] = (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
				$this->session->data['guest']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
				$this->session->data['guest']['email'] = (isset($this->request->post['email'])) ? $this->request->post['email'] : '';
				$this->session->data['guest']['telephone'] = (isset($this->request->post['telephone'])) ? $this->request->post['telephone'] : '';
				$this->session->data['guest']['fax'] = (isset($this->request->post['fax'])) ? $this->request->post['fax'] : '';
			} elseif($this->customer->isLogged()) {
				$this->session->data['customer']['firstname'] =  (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
				$this->session->data['customer']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
				$this->session->data['customer']['telephone'] = (isset($this->request->post['telephone'])) ? $this->request->post['telephone'] : '';
				$this->session->data['customer']['fax'] = (isset($this->request->post['fax'])) ? $this->request->post['fax'] : '';

			}

			if (isset($this->request->post['custom_field']['opc_customer'])) {
				$this->session->data['guest']['customer_custom_field'] = $this->request->post['custom_field']['opc_customer'];
			} else {
				$this->session->data['guest']['customer_custom_field'] = array();
			}

			if (isset($this->request->post['custom_field']['opc_address'])) {
				$this->session->data['guest']['address_custom_field'] = $this->request->post['custom_field']['opc_address'];
			} else {
				$this->session->data['guest']['address_custom_field'] = array();
			}

			if (isset($this->request->post['custom_field']['opc_payment'])) {
				$this->session->data['guest']['payment_custom_field'] = $this->request->post['custom_field']['opc_payment'];
			} else {
				$this->session->data['guest']['payment_custom_field'] = array();
			}

			if (isset($this->request->post['opc_not_call_me'])) {
				$this->session->data['guest']['opc_not_call_me'] = $this->request->post['opc_not_call_me'];
			} else {
				$this->session->data['guest']['opc_not_call_me'] = '';
			}

			if (isset($this->request->post['opc_contact_telegram_viber'])) {
				$this->session->data['guest']['opc_contact_telegram_viber'] = $this->request->post['opc_contact_telegram_viber'];
			} else {
				$this->session->data['guest']['opc_contact_telegram_viber'] = '';
			}

			$this->load->model('localisation/country');

			$this->session->data['payment_address']['country_id'] = (isset($this->request->post['country_id'])) ? $this->request->post['country_id'] : '';
			$this->session->data['payment_address']['zone_id'] = (isset($this->request->post['zone_id'])) ? $this->request->post['zone_id'] : '';
			$this->session->data['payment_address']['firstname'] = (isset($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
			$this->session->data['payment_address']['lastname'] = (isset($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';

			$this->session->data['payment_address']['company'] = (isset($this->request->post['company'])) ? $this->request->post['company'] : '';
			$this->session->data['payment_address']['address_1'] = (isset($this->request->post['address_1'])) ? $this->request->post['address_1'] : '';
			$this->session->data['payment_address']['address_2'] = (isset($this->request->post['address_2'])) ? $this->request->post['address_2'] : '';
			$this->session->data['payment_address']['postcode'] = (isset($this->request->post['postcode'])) ? $this->request->post['postcode'] : '';
			$this->session->data['payment_address']['city'] = (isset($this->request->post['city'])) ? $this->request->post['city'] : '';

			foreach($shipping_methods_fields as $field_key => $shipping_field){
				if (is_array($shipping_field) && isset($shipping_field['status']) && $shipping_field['status'] != '0') {
					if(!empty($shipping_field['setting']['custom_fields'])){
						if($field_key == 'city'){
							if(!empty($shipping_field['setting']['custom_fields'][$this->request->post['city']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['city']))){
								$this->session->data['payment_address']['city'] = (isset($shipping_field['setting']['custom_fields'][$this->request->post['city']][$this->config->get('config_language_id')]['name'])) ? $shipping_field['setting']['custom_fields'][$this->request->post['city']][$this->config->get('config_language_id')]['name'] : '';
							} else {
								$this->session->data['payment_address']['city'] = '';
							}
						}
						if($field_key == 'postcode'){
							if(!empty($shipping_field['setting']['custom_fields'][$this->request->post['postcode']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['postcode']))){
								$this->session->data['payment_address']['postcode'] = (isset($shipping_field['setting']['custom_fields'][$this->request->post['postcode']][$this->config->get('config_language_id')]['name'])) ? $shipping_field['setting']['custom_fields'][$this->request->post['postcode']][$this->config->get('config_language_id')]['name'] : '';
							} else {
								$this->session->data['payment_address']['postcode'] = '';
							}
						}
						if($field_key == 'address_1'){
							if(!empty($shipping_field['setting']['custom_fields'][$this->request->post['address_1']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['address_1']))){
								$this->session->data['payment_address']['address_1'] = (isset($shipping_field['setting']['custom_fields'][$this->request->post['address_1']][$this->config->get('config_language_id')]['name'])) ? $shipping_field['setting']['custom_fields'][$this->request->post['address_1']][$this->config->get('config_language_id')]['name'] : '';
							} else {
								$this->session->data['payment_address']['address_1'] = '';
							}
						}
						if($field_key == 'address_2'){
							if(!empty($shipping_field['setting']['custom_fields'][$this->request->post['address_2']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['address_2']))){
								$this->session->data['payment_address']['address_2'] = (isset($shipping_field['setting']['custom_fields'][$this->request->post['address_2']][$this->config->get('config_language_id')]['name'])) ? $shipping_field['setting']['custom_fields'][$this->request->post['address_2']][$this->config->get('config_language_id')]['name'] : '';
							} else {
								$this->session->data['payment_address']['address_2'] = '';
							}
						}
						if($field_key == 'company'){
							if(!empty($shipping_field['setting']['custom_fields'][$this->request->post['company']][$this->config->get('config_language_id')]['name']) && (isset($this->request->post['company']))){
								$this->session->data['payment_address']['company'] = (isset($shipping_field['setting']['custom_fields'][$this->request->post['company']][$this->config->get('config_language_id')]['name'])) ? $shipping_field['setting']['custom_fields'][$this->request->post['company']][$this->config->get('config_language_id')]['name'] : '';
							} else {
								$this->session->data['payment_address']['company'] = '';
							}
						}
					}
				}
			}

			if(!empty($this->request->post['country_id'])){
				$this->load->model('localisation/country');

				$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

				if ($country_info) {
					$this->session->data['payment_address']['country'] = $country_info['name'];
					$this->session->data['payment_address']['iso_code_2'] = $country_info['iso_code_2'];
					$this->session->data['payment_address']['iso_code_3'] = $country_info['iso_code_3'];
					$this->session->data['payment_address']['address_format'] = $country_info['address_format'];
				} else {
					$this->session->data['payment_address']['country'] = '';
					$this->session->data['payment_address']['iso_code_2'] = '';
					$this->session->data['payment_address']['iso_code_3'] = '';
					$this->session->data['payment_address']['address_format'] = '';
				}
			}

			if(!empty($this->request->post['zone_id'])){
				$this->load->model('localisation/zone');

				$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

				if ($zone_info) {
					$this->session->data['payment_address']['zone'] = $zone_info['name'];
					$this->session->data['payment_address']['zone_code'] = $zone_info['code'];
				} else {
					$this->session->data['payment_address']['zone'] = '';
					$this->session->data['payment_address']['zone_code'] = '';
				}
			} else {
				$this->session->data['payment_address']['zone'] = '';
				$this->session->data['payment_address']['zone_code'] = '';
			}
			$this->session->data['shipping_address'] = $this->session->data['payment_address'];

			$this->session->data['comment'] = (isset($this->request->post['comment'])) ? strip_tags($this->request->post['comment']) : '';

			$json = $this->confirm();

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function save_fields() {

		if(isset($this->request->post['firstname'])){
			$this->session->data['customer']['firstname'] = $this->request->post['firstname'];
		}
		if(isset($this->request->post['lastname'])){
			$this->session->data['customer']['lastname'] = $this->request->post['lastname'];
		}
		if(isset($this->request->post['telephone'])){
			$this->session->data['customer']['telephone'] = $this->request->post['telephone'];
		}
		if(isset($this->request->post['email'])){
			$this->session->data['customer']['email'] = $this->request->post['email'];
		}
		if(isset($this->request->post['fax'])){
			$this->session->data['customer']['fax'] = $this->request->post['fax'];
		}

		if(isset($this->request->post['city'])){
			if(isset($this->request->post['shipping_method']) && (isset($this->session->data['shipping_address']['city']))){
				if($this->request->post['shipping_method'] == 'novaposhta.department' || $this->request->post['shipping_method'] == 'novaposhta.poshtomat'){
					$this->session->data['sm_address']['novaposhta.department']['city'] = $this->request->post['city'];
					$this->session->data['sm_address']['novaposhta.poshtomat']['city'] = $this->request->post['city'];
				} else {
					$this->session->data['sm_address'][$this->request->post['shipping_method']]['city'] = $this->request->post['city'];
				}
			}
			$this->session->data['payment_address']['city'] = $this->session->data['shipping_address']['city'] = $this->request->post['city'];
		}
		if(isset($this->request->post['address_1'])){
			if(isset($this->request->post['shipping_method']) && (isset($this->session->data['shipping_address']['address_1']))){
				$this->session->data['sm_address'][$this->request->post['shipping_method']]['address_1'] = $this->request->post['address_1'];
			}
			$this->session->data['payment_address']['address_1'] = $this->session->data['shipping_address']['address_1'] = $this->request->post['address_1'];
		}
		if(isset($this->request->post['address_2'])){
			if(isset($this->request->post['shipping_method']) && (isset($this->session->data['shipping_address']['address_2']))){
				$this->session->data['sm_address'][$this->request->post['shipping_method']]['address_2'] = $this->request->post['address_2'];
			}
			$this->session->data['payment_address']['address_2'] = $this->session->data['shipping_address']['address_2'] = $this->request->post['address_2'];
		}
		if(isset($this->request->post['postcode'])){
			if(isset($this->request->post['shipping_method']) && (isset($this->session->data['shipping_address']['postcode']))){
				$this->session->data['sm_address'][$this->request->post['shipping_method']]['postcode'] = $this->request->post['postcode'];
			}
			$this->session->data['payment_address']['postcode'] = $this->session->data['shipping_address']['postcode'] = $this->request->post['postcode'];
		}
		if(isset($this->request->post['company'])){
			if(isset($this->request->post['shipping_method']) && (isset($this->session->data['shipping_address']['company']))){
				$this->session->data['sm_address'][$this->request->post['shipping_method']]['company'] = $this->request->post['company'];
			}
			$this->session->data['payment_address']['company'] = $this->session->data['shipping_address']['company'] = $this->request->post['company'];
		}

		if(isset($this->request->post['custom_field']['opc_customer'])) {
			$this->session->data['guest']['opc_customer_custom_field'] = $this->request->post['custom_field']['opc_customer'];
		} else {
			$this->session->data['guest']['opc_customer_custom_field'] = [];
		}

		if(isset($this->request->post['custom_field']['opc_address'])) {
			$this->session->data['guest']['opc_address_custom_field'] = $this->request->post['custom_field']['opc_address'];
		} else {
			$this->session->data['guest']['opc_address_custom_field'] = [];
		}

		if(isset($this->request->post['custom_field']['opc_payment'])) {
			$this->session->data['guest']['opc_payment_custom_field'] = $this->request->post['custom_field']['opc_payment'];
		} else {
			$this->session->data['guest']['opc_payment_custom_field'] = [];
		}

		$this->abandonedOrders();
	}

	public function abandonedOrders() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('tool/upload');

			$products = array();

			foreach ($this->cart->getProducts() as $product) {

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


				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$products[] = array(
					'name'		=> $product['name'],
					'model'		=> $product['model'],
					'option'		=> $option_data,
					'quantity'	=> $product['quantity'],
					'price'		=> $price,
					'total'		=> $total,
					'href'		=> $this->url->link('product/product', 'product_id=' . $product['product_id']),
				);
			}

			$abandoned_data = array(
				'store_id' => $this->config->get('store_id'),
            'customer_id' => $this->customer->isLogged() ? $this->customer->getId() : '',
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'telephone' => '',
            'products' => $products
			);

			if(isset($this->request->post['firstname'])){
				$abandoned_data['firstname'] = $this->request->post['firstname'];
			}
			if(isset($this->request->post['lastname'])){
				$abandoned_data['lastname'] = $this->request->post['lastname'];
			}

			$opc_telephone = isset($this->request->post['telephone']) ? $this->request->post['telephone'] : '';
			if (((utf8_strlen($opc_telephone) > 3) || (utf8_strlen($opc_telephone) < 32))) {
				$abandoned_data['telephone'] = $opc_telephone;
			}

			$opc_email = isset($this->request->post['email']) ? $this->request->post['email'] : '';
			if (utf8_strlen($opc_email) <= 96 && filter_var($opc_email, FILTER_VALIDATE_EMAIL)) {
				$abandoned_data['email'] = $opc_email;
			}

			if (!empty($abandoned_data['products']) && (!empty($abandoned_data['email']) || !empty($abandoned_data['telephone']))) {
            $this->load->model('checkout/onepcheckout');

				if(!isset($this->session->data['abandoned_id'])){
					$this->session->data['abandoned_id'] = $this->model_checkout_onepcheckout->addAbandonedOrder($abandoned_data);
				} else {
					$abandoned_id = $this->session->data['abandoned_id'];
					$this->session->data['abandoned_id'] = $this->model_checkout_onepcheckout->editAbandonedOrder($abandoned_id, $abandoned_data);
				}
        }
		}
	}

	public function country_region($render = true, &$data  = array()) {

		$this->load->language('checkout/checkout');
		$this->load->language('checkout/onepcheckout');

		$data['title_country_region'] = $this->language->get('title_country_region');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');
		$data['entry_country'] = $data['entry_ph_country'] = $this->language->get('entry_country');
		$data['entry_zone'] = $data['entry_ph_zone'] = $this->language->get('entry_zone');

		$this->load->model('account/address');
		$this->load->model('checkout/onepcheckout');

		$data['country_type'] = 'select';
		$data['zones_type'] = 'select';

		if (isset($this->session->data['shipping_address']['country_id']) && ($this->session->data['shipping_address']['country_id'] !='')) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['shipping_address']['zone_id']) && ($this->session->data['shipping_address']['zone_id'] !='')) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$country_info = $this->model_localisation_country->getCountry($data['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');
			$data['zones'] = $this->model_localisation_zone->getZonesByCountryId($data['country_id']);
		}

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_country_region', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_country_region', $data);
		}
	}

	public function shipping_address($render = true, &$data  = array()) {

		$this->load->language('checkout/checkout');
		$this->load->language('checkout/onepcheckout');

		$data['title_shipping_address'] = $this->language->get('title_shipping_address');
		$data['text_address_existing'] = $this->language->get('text_address_existing');
		$data['text_address_new'] = $this->language->get('text_address_new');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');

		$data['entry_firstname'] = $this->language->get('entry_firstname');
		$data['entry_lastname'] = $this->language->get('entry_lastname');

		$data['entry_company'] = $data['entry_ph_company'] = $this->language->get('entry_company');
		$data['entry_address_1'] = $data['entry_ph_address_1'] = $this->language->get('entry_address_1');
		$data['entry_address_2'] = $data['entry_ph_address_2'] = $this->language->get('entry_address_2');
		$data['entry_postcode'] = $data['entry_ph_postcode'] = $this->language->get('entry_postcode');
		$data['entry_city'] = $data['entry_ph_city'] = $this->language->get('entry_city');
		$data['entry_country'] = $data['entry_ph_country'] = $this->language->get('entry_country');
		$data['entry_zone'] = $data['entry_ph_zone'] = $this->language->get('entry_zone');

		$this->load->model('account/address');
		$this->load->model('checkout/onepcheckout');

		$opc_country_region_status = $this->config->get('opc_country_region_status');

		$address_custom_fields = $this->model_checkout_onepcheckout->getCustomFields('opc_address', $this->config->get('config_customer_group_id'));
		$shipping_methods_fields = array();

		$shipping_methods_data = $this->config->get('opc_payment_address');

		if(isset($this->session->data['shipping_method']) && (!empty($this->session->data['shipping_method']['code'] !=''))){
			$shipping_code = str_replace(".","_",$this->session->data['shipping_method']['code']);

			if(!empty($shipping_methods_data[$shipping_code])){
				$shipping_methods_fields = $shipping_methods_data[$shipping_code];
			} else {
				$shipping_methods_fields = $shipping_methods_data['default'];
			}
		} else {
			$shipping_methods_fields = $shipping_methods_data['default'];
		}

		$customer_logged = false;

		if ($this->customer->isLogged()) {
			$customer_logged = true;
		}

		foreach($shipping_methods_fields as $field_key => $shipping_field){
			if(is_array($shipping_field)){
				if(isset($shipping_field['status']) && ($shipping_field['status'] != '0')){

					if(($field_key == 'country' || $field_key == 'zone_id') && $opc_country_region_status == 1){
						continue;
					}

					if($customer_logged && ($shipping_field['show_when'] == 'only_quest')){
						continue;
					}

					if(!$customer_logged && ($shipping_field['show_when'] == 'only_authorized')){
						continue;
					}

					if(!empty($shipping_field['setting']['name_field'][$this->config->get('config_language_id')])){
						$data['entry_' . $field_key] = $shipping_field['setting']['name_field'][$this->config->get('config_language_id')];
					}
					if(!empty($shipping_field['setting']['placeholder_field'][$this->config->get('config_language_id')])){
						$data['entry_ph_' . $field_key] = $shipping_field['setting']['placeholder_field'][$this->config->get('config_language_id')];
					}

					$opc_custom_fields = array();

					if(!empty($shipping_field['setting']['custom_fields'])){
						foreach($shipping_field['setting']['custom_fields'] as $cf_value => $custom_field){
							if(!empty($custom_field[$this->config->get('config_language_id')]['name'])){
								$opc_custom_fields[] = array(
									'value' 	=> $cf_value,
									'name' 	=> $custom_field[$this->config->get('config_language_id')]['name'],
								);
							}
						}
					} else {
						if(isset($shipping_field['setting']['type']) &&  ($shipping_field['setting']['type'] == 'select2')){
							$shipping_field['setting']['type'] = 'select2';
						} else {
							$shipping_field['setting']['type'] = 'input';
						}
					}

					if(!isset($shipping_field['setting']['type'])){
						$sf_type = 'input';
					} else {
						$sf_type = $shipping_field['setting']['type'];
					}

					$ds_cf = '';
					if(isset($shipping_field['setting']['cf_default_select']) && ($shipping_field['setting']['cf_default_select'] != '')){
						$ds_cf = $shipping_field['setting']['cf_default_select'];

						if(!isset($this->session->data['shipping_address']['city']) && $field_key == 'city' && $ds_cf !=''){
							$this->session->data['shipping_address']['city'] = $ds_cf;
						}
						if(empty($this->session->data['shipping_address']['address_1']) && $field_key == 'address_1' && $ds_cf !=''){
							$this->session->data['shipping_address']['address_1'] = $ds_cf;
						}
						if(empty($this->session->data['shipping_address']['address_2']) && $field_key == 'address_2' && $ds_cf !=''){
							$this->session->data['shipping_address']['address_2'] = $ds_cf;
						}
						if(empty($this->session->data['shipping_address']['postcode']) && $field_key == 'postcode' && $ds_cf !=''){
							$this->session->data['shipping_address']['postcode'] = $ds_cf;
						}
						if(empty($this->session->data['shipping_address']['company']) && $field_key == 'company' && $ds_cf !=''){
							$this->session->data['shipping_address']['company'] = $ds_cf;
						}
					}

					$data['shipping_methods_fields'][$field_key] = array(
						'status'				=> $shipping_field['status'],
						'type' 				=> $sf_type,
						'ds_cf' 				=> $ds_cf,
						'custom_fields' 	=> $opc_custom_fields
					);

				}

				if(strpos($field_key, 'custom_field_') === 0){
					if(!empty($address_custom_fields[$shipping_field['id']])){
						if($customer_logged && ($shipping_field['show_when'] == 'only_quest')){
							continue;
						}

						if(!$customer_logged && ($shipping_field['show_when'] == 'only_authorized')){
							continue;
						}
						$data['shipping_methods_fields'][$field_key] = $address_custom_fields[$shipping_field['id']];
					}
				}
			}
		}

		if(isset($this->session->data['shipping_method']['code']) && (isset($this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['city']))){
			$this->session->data['shipping_address']['city'] = $this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['city'];
		} else {
			$this->session->data['shipping_address']['city'] = '';
		}

		if(isset($this->session->data['shipping_method']['code']) && (isset($this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['address_1']))){
			$this->session->data['shipping_address']['address_1'] = $this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['address_1'];
		} else {
			$this->session->data['shipping_address']['address_1'] = '';
		}

		if(isset($this->session->data['shipping_method']['code']) && (isset($this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['address_2']))){
			$this->session->data['shipping_address']['address_2'] = $this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['address_2'];
		} else {
			$this->session->data['shipping_address']['address_2'] = '';
		}

		if(isset($this->session->data['shipping_method']['code']) && (isset($this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['postcode']))){
			$this->session->data['shipping_address']['postcode'] = $this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['postcode'];
		} else {
			$this->session->data['shipping_address']['postcode'] = '';
		}

		if(isset($this->session->data['shipping_method']['code']) && (isset($this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['company']))){
			$this->session->data['shipping_address']['company'] = $this->session->data['sm_address'][$this->session->data['shipping_method']['code']]['company'];
		} else {
			$this->session->data['shipping_address']['company'] = '';
		}
		if (isset($this->session->data['shipping_address']['city'])) {
			$data['city'] = $this->session->data['shipping_address']['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->session->data['shipping_address']['company'])) {
			$data['company'] = $this->session->data['shipping_address']['company'];
		} else {
			$data['company'] = '';
		}

		if (isset($this->session->data['shipping_address']['address_1'])) {
			$data['address_1'] = $this->session->data['shipping_address']['address_1'];
		} else {
			$data['address_1'] = '';
		}

		if (isset($this->session->data['shipping_address']['address_2'])) {
			$data['address_2'] = $this->session->data['shipping_address']['address_2'];
		} else {
			$data['address_2'] = '';
		}

		if (isset($this->session->data['shipping_address']['country_id']) && ($this->session->data['shipping_address']['country_id'] !='')) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['shipping_address']['zone_id']) && ($this->session->data['shipping_address']['zone_id'] !='')) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$country_info = $this->model_localisation_country->getCountry($data['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');
			$data['zones'] = $this->model_localisation_zone->getZonesByCountryId($data['country_id']);
		}

		if(isset($this->request->post['custom_field']['opc_address'])) {
			$data['address_custom_field'] = $this->session->data['guest']['opc_address_custom_field'] = $this->request->post['custom_field']['opc_address'];
		} elseif (isset($this->session->data['guest']['opc_address_custom_field'])) {
			$data['address_custom_field'] = $this->session->data['guest']['opc_address_custom_field'];
		} else {
			$data['address_custom_field'] = [];
		}

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_shipping_address', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_shipping_address', $data);
		}
	}

	private function check_products(){
		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}
	}

	public function shipping_method($render = true, &$data = array()){
		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			return;
		}

		$this->load->language('checkout/checkout');
		$this->load->model('account/address');
		$this->load->model('tool/image');

		if(isset($this->session->data['shipping_address'])) {
			$shipping_address = $this->session->data['shipping_address'];
		} else {
			$shipping_address = array('country_id' => $this->config->get('config_country_id'), 'zone_id' => $this->config->get('config_zone_id'), 'firstname' => '', 'lastname' => '', 'company' => '', 'address_1' => '');
		}

		if(isset($this->request->post['firstname'])) {
			$shipping_address['firstname'] = $this->request->post['firstname'];
		} elseif (isset($this->session->data['shipping_address']['firstname'])) {
			$shipping_address['firstname'] = $this->session->data['shipping_address']['firstname'];
		} else {
			$shipping_address['firstname'] = '';
		}

		if(isset($this->request->post['lastname'])) {
			$shipping_address['lastname'] = $this->request->post['lastname'];
		} elseif (isset($this->session->data['shipping_address']['lastname'])) {
			$shipping_address['lastname'] = $this->session->data['shipping_address']['lastname'];
		} else {
			$shipping_address['lastname'] = '';
		}

		if(isset($this->request->post['address_1'])) {
			$shipping_address['address_1'] = $this->request->post['address_1'];
		} elseif (isset($this->session->data['shipping_address']['address_1'])) {
			$shipping_address['address_1'] = $this->session->data['shipping_address']['address_1'];
		} else {
			$shipping_address['address_1'] = '';
		}

		if(isset($this->request->post['address_2'])) {
			$shipping_address['address_2'] = $this->request->post['address_2'];
		} elseif (isset($this->session->data['shipping_address']['address_2'])) {
			$shipping_address['address_2'] = $this->session->data['shipping_address']['address_2'];
		} else {
			$shipping_address['address_2'] = '';
		}

		if(isset($this->request->post['company'])) {
			$shipping_address['company'] = $this->request->post['company'];
		} elseif (isset($this->session->data['shipping_address']['company'])) {
			$shipping_address['company'] = $this->session->data['shipping_address']['company'];
		} else {
			$shipping_address['company'] = '';
		}

		if(isset($this->request->post['postcode'])) {
			$shipping_address['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['shipping_address']['postcode'])) {
			$shipping_address['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$shipping_address['postcode'] = '';
		}

		if(isset($this->request->post['city'])) {
			$shipping_address['city'] = $this->request->post['city'];
		} elseif (isset($this->session->data['shipping_address']['city'])) {
			$shipping_address['city'] = $this->session->data['shipping_address']['city'];
		} else {
			$shipping_address['city'] = '';
		}

		if(isset($this->request->post['city'])) {
			$shipping_address['city'] = $shipping_address['shipping_city'] = $this->request->post['city'];
		} elseif (isset($this->session->data['shipping_address']['city'])) {
			$shipping_address['city'] = $shipping_address['shipping_city'] = $this->session->data['shipping_address']['city'];
		} else {
			$shipping_address['city'] = $shipping_address['shipping_city'] = '';
		}

		if(isset($this->request->post['country_id'])) {
			$country_id = $shipping_address['country_id'] = $shipping_address['country_id'] = $this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$country_id = $shipping_address['country_id'] = $shipping_address['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$country_id = $shipping_address['country_id'] = $shipping_address['shipping_country_id'] = $this->config->get('config_country_id');
		}

		if(isset($this->request->post['zone_id'])) {
			$zone_id = $shipping_address['zone_id'] = $shipping_address['zone_country_id'] = $shipping_address['shipping_zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$zone_id = $shipping_address['zone_id'] = $shipping_address['zone_country_id'] = $shipping_address['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$zone_id = $shipping_address['zone_id'] = $shipping_address['zone_country_id'] = $shipping_address['shipping_zone_id'] = $this->config->get('config_zone_id');
		}

		if(!empty($zone_id)){
			$this->load->model('localisation/zone');
			$zone_info = $this->model_localisation_zone->getZone($zone_id);

			$shipping_address['zone'] = $this->session->data['shipping_address']['zone'] = $zone_info ? $zone_info['name'] : '';
			$shipping_address['zone_code'] = $this->session->data['shipping_address']['zone_code'] = $zone_info ? $zone_info['code'] : '';
		}

		if(!empty($country_id)){
			$this->load->model('localisation/country');
			$data['countries'] = $this->model_localisation_country->getCountries();
			$country_info = $this->model_localisation_country->getCountry($country_id);

			$shipping_address['country'] = $this->session->data['shipping_address']['country'] = $country_info ? $country_info['name'] : '';
			$shipping_address['iso_code_2'] = $this->session->data['shipping_address']['iso_code_2'] = $country_info ? $country_info['iso_code_2'] : '';
			$shipping_address['iso_code_3'] = $this->session->data['shipping_address']['iso_code_3'] = $country_info ? $country_info['iso_code_3'] : '';
			$shipping_address['address_format'] = $this->session->data['shipping_address']['address_format'] = $country_info ? $country_info['address_format'] : '';
		}

		$this->session->data['shipping_address'] = $shipping_address;

		$opc_setting_shipping_methods = $this->config->get('opc_setting_shipping_methods');
		$available_shipping_methods = array();

		if (isset($shipping_address)) {

			$this->tax->setShippingAddress($shipping_address['country_id'], $shipping_address['zone_id']);

			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status')) {
					$this->load->model('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote){
						if(isset($quote['quote'][$result['code']]['cost']) && ($quote['quote'][$result['code']]['cost'] == 0)){
							$quote['quote'][$result['code']]['text'] = '';
						}
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			uasort($method_data, function($a, $b) {
				$original_order_a = isset($a['original_order']) ? $a['original_order'] : 0;
				$original_order_b = isset($b['original_order']) ? $b['original_order'] : 0;

				if ($a['sort_order'] == $b['sort_order']) {
					return ($original_order_a < $original_order_b) ? -1 : 1;
				}
				return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
			});

			unset($this->session->data['shipping_methods']);

			$lang_id = $this->config->get('config_language_id');

			foreach ($method_data as $code => $method) {
				if (is_array($method['quote'])) {
					$available_methods = array();

					foreach ($method['quote'] as $quote_code => $quote_data) {
						$method_code = $quote_data['code'];
						$opc_code = str_replace(".", "_", $method_code);

						if (!isset($opc_setting_shipping_methods[$opc_code])) {
							$available_methods[$quote_code] = $quote_data;
						} else {
							$settings = $opc_setting_shipping_methods[$opc_code];

							$method['quote']['image'] = false;

							if (isset($settings['image']) && ($settings['image'] !='') ) {
								$quote_data['image_width'] = isset($settings['image_width']) ? $settings['image_width'] : 36;
								$quote_data['image_height'] = isset($settings['image_height']) ? $settings['image_height'] : 36;
								$quote_data['image'] = $this->model_tool_image->resize($settings['image'], $quote_data['image_width'], $quote_data['image_height']);
							}

							$opc_sm_status = true;

							if (isset($settings['sm_show_all_countries']) && $settings['sm_show_all_countries'] == 0) {
								$opc_sm_status = false;
							}

							if (isset($settings['sm_show_only_countries']) && is_array($settings['sm_show_only_countries'])) {
								if (!in_array($country_id, $settings['sm_show_only_countries'])) {
									$opc_sm_status = false;
								}
							}

							if (isset($settings['sm_disabled_countries']) && is_array($settings['sm_disabled_countries'])) {
								if (in_array($country_id, $settings['sm_disabled_countries'])) {
									$opc_sm_status = false;
								}
							}

							if (isset($settings['status_sm_new_title']) && ($settings['status_sm_new_title'] == 1) ) {
								if (isset($settings['sm_new_title'][$lang_id]) && !empty(strip_tags($settings['sm_new_title'][$lang_id]))) {
									$quote_data['title'] = html_entity_decode($settings['sm_new_title'][$lang_id], ENT_QUOTES, 'UTF-8');
								}
							}

							if ($opc_sm_status) {
								$available_methods[$quote_code] = $quote_data;
							}
						}
					}

					if (!empty($available_methods)) {
						$method['quote'] = $available_methods;
						$available_shipping_methods[$code] = $method;
					}
				}
			}

			$this->session->data['shipping_methods'] = $available_shipping_methods;
		}

		if (!empty($this->session->data['shipping_methods']) && isset($this->request->post['shipping_method'])) {
			$shipping_save = explode('.', $this->request->post['shipping_method']);
			if(isset($this->session->data['shipping_methods'][$shipping_save[0]])){
				$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping_save[0]]['quote'][$shipping_save[1]];
			}
		}

		$this->load->language('checkout/onepcheckout');

		$data['title_shipping_method'] = $this->language->get('title_shipping_method');
		$data['text_shipping_method'] = $this->language->get('text_shipping_method');

		$data['text_loading'] = $this->language->get('text_loading');

		$data['button_continue'] = $this->language->get('button_continue');

		if (empty($this->session->data['shipping_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['shipping_methods'])) {
			$data['shipping_methods'] =  $available_shipping_methods;
		} else {
			$data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method'])) {
		    $shipping_method_code = $this->session->data['shipping_method']['code'];
		    list($method_code, $quote_code) = explode('.', $shipping_method_code);

		    if (!isset($available_shipping_methods[$method_code]['quote'][$quote_code])) {
		        unset($this->session->data['shipping_method']);
		    }
		}

		if(!isset($this->session->data['shipping_method']) && $available_shipping_methods) {
			$select_first_method = array();
			foreach($available_shipping_methods as $method) {
				if(is_array($method['quote'])){
					$keys = array_keys($method['quote']);
					$select_first_method = $method['quote'][$keys[0]];
					break;
				}
			}

			$this->session->data['shipping_method'] = $select_first_method;
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$data['shipping_code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['shipping_code'] = '';
		}

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_shipping_method', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_shipping_method', $data);
		}
	}

	public function payment_method($render = true, &$data = array()) {

		$this->load->language('checkout/checkout');

		$this->load->model('checkout/onepcheckout');
		$this->load->model('account/address');
		$this->load->model('tool/image');

  		if(isset($this->request->post['firstname'])) {
			$pm_address['firstname'] = $this->request->post['firstname'];
		} elseif (isset($this->session->data['payment_address']['firstname'])) {
			$pm_address['firstname'] = $this->session->data['payment_address']['firstname'];
		} else {
			$pm_address['firstname'] = '';
		}

		if(isset($this->request->post['lastname'])) {
			$pm_address['lastname'] = $this->request->post['lastname'];
		} elseif (isset($this->session->data['payment_address']['lastname'])) {
			$pm_address['lastname'] = $this->session->data['payment_address']['lastname'];
		} else {
			$pm_address['lastname'] = '';
		}

		if(isset($this->request->post['address_1'])) {
			$pm_address['address_1'] = $this->request->post['address_1'];
		} elseif (isset($this->session->data['payment_address']['address_1'])) {
			$pm_address['address_1'] = $this->session->data['payment_address']['address_1'];
		} else {
			$pm_address['address_1'] = '';
		}

		if(isset($this->request->post['postcode'])) {
			$pm_address['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['payment_address']['postcode'])) {
			$pm_address['postcode'] = $this->session->data['payment_address']['postcode'];
		} else {
			$pm_address['postcode'] = '';
		}

		if(isset($this->request->post['city'])) {
			$pm_address['city'] = $pm_address['shipping_city'] = $this->request->post['city'];
		} elseif (isset($this->session->data['payment_address']['city'])) {
			$pm_address['city'] = $pm_address['shipping_city'] = $this->session->data['payment_address']['city'];
		} else {
			$pm_address['city'] = $pm_address['shipping_city'] = '';
		}

		if(isset($this->request->post['country_id'])) {
			$country_id = $pm_address['country_id'] = $pm_address['country_id'] = $this->request->post['country_id'];
		} elseif (isset($this->session->data['payment_address']['country_id'])) {
			$country_id = $pm_address['country_id'] = $pm_address['shipping_country_id'] = $this->session->data['payment_address']['country_id'];
		} else {
			$country_id = $pm_address['country_id'] = $pm_address['shipping_country_id'] = $this->config->get('config_country_id');
		}

		if(isset($this->request->post['zone_id'])) {
			$zone_id = $pm_address['zone_id'] = $pm_address['zone_country_id'] = $pm_address['payment_zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($this->session->data['payment_address']['zone_id'])) {
			$zone_id = $pm_address['zone_id'] = $pm_address['zone_country_id'] = $pm_address['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
		} else {
			$zone_id = $pm_address['zone_id'] = $pm_address['zone_country_id'] = $pm_address['payment_zone_id'] = $this->config->get('config_zone_id');
		}

		if(!empty($zone_id)){
			$this->load->model('localisation/zone');
			$zone_info = $this->model_localisation_zone->getZone($zone_id);

			$pm_address['zone'] = $this->session->data['payment_address']['zone'] = $zone_info ? $zone_info['name'] : '';
			$pm_address['zone_code'] = $this->session->data['payment_address']['zone_code'] = $zone_info ? $zone_info['code'] : '';
		}

		if(!empty($country_id)){
			$this->load->model('localisation/country');
			$data['countries'] = $this->model_localisation_country->getCountries();
			$country_info = $this->model_localisation_country->getCountry($country_id);

			$pm_address['country'] = $this->session->data['payment_address']['country'] = $country_info ? $country_info['name'] : '';
			$pm_address['iso_code_2'] = $this->session->data['payment_address']['iso_code_2'] = $country_info ? $country_info['iso_code_2'] : '';
			$pm_address['iso_code_3'] = $this->session->data['payment_address']['iso_code_3'] = $country_info ? $country_info['iso_code_3'] : '';
			$pm_address['address_format'] = $this->session->data['payment_address']['address_format'] = $country_info ? $country_info['address_format'] : '';
		}

		$this->session->data['payment_address'] = $pm_address;

		$this->tax->setPaymentAddress($pm_address['country_id'], $pm_address['zone_id']);

			// Totals
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

			// Payment Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('payment');

		$recurring = $this->cart->hasRecurringProducts();

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);

				if ($method) {
					if ($recurring) {
						if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
							$method_data[$result['code']] = $method;
						}
					} else {
						$method_data[$result['code']] = $method;
					}
				}
			}
		}

		$sort_order = array();

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);

		$opc_payment_option = $this->config->get('opc_payment_option');

		$lang_id = $this->config->get('config_language_id');

		$customer_logged = false;

		if ($this->customer->isLogged()) {
			$customer_logged = true;
		}

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->config->get('config_customer_group_id');
		} else {
			if(isset($this->request->post['customer_group_id'])) {
				$customer_group_id = $this->session->data['guest']['customer_group_id'] = $this->request->post['customer_group_id'];
			} elseif (isset($this->session->data['guest']['customer_group_id'])) {
				$customer_group_id = $this->session->data['guest']['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
		}

		$payment_custom_fields = $this->model_checkout_onepcheckout->getCustomFields('opc_payment', $customer_group_id);

		foreach ($method_data as &$method) {

			$method['image'] = false;

			if (isset($opc_payment_option[$method['code']]['image']) && ($opc_payment_option[$method['code']]['image'] !='') ) {
				$method['image_width'] = isset($opc_payment_option[$method['code']]['image_width']) ? $opc_payment_option[$method['code']]['image_width'] : 32;
				$method['image_height'] = isset($opc_payment_option[$method['code']]['image_height']) ? $opc_payment_option[$method['code']]['image_height'] : 32;
				$method['image'] = $this->model_tool_image->resize($opc_payment_option[$method['code']]['image'], $method['image_width'], $method['image_height']);
			}

			if (isset($opc_payment_option[$method['code']]['status_pm_title']) && ($opc_payment_option[$method['code']]['status_pm_title'] == 1) ) {
				if (isset($opc_payment_option[$method['code']]['title'][$lang_id]) && !empty(strip_tags($opc_payment_option[$method['code']]['title'][$lang_id]))) {
					$method['title'] = html_entity_decode($opc_payment_option[$method['code']]['title'][$lang_id], ENT_QUOTES, 'UTF-8');
				}
			}

			$method['pm_description'] = false;

			if (isset($opc_payment_option[$method['code']]['status_pm_description']) && ($opc_payment_option[$method['code']]['status_pm_description'] == 1) ) {
				if (isset($opc_payment_option[$method['code']]['description'][$lang_id]) && !empty(strip_tags($opc_payment_option[$method['code']]['description'][$lang_id]))) {
					$method['pm_description'] = html_entity_decode($opc_payment_option[$method['code']]['description'][$lang_id], ENT_QUOTES, 'UTF-8');
				}
			}

			$method['custom_fields'] = [];

			if (!empty($opc_payment_option[$method['code']]['custom_fields']) && (!empty($payment_custom_fields))) {
				foreach ($opc_payment_option[$method['code']]['custom_fields'] as $pcf_id => $pcf_value) {
					if ($customer_logged && ($pcf_value['show_when'] == 'only_quest')) {
						continue;
					}

					if (!$customer_logged && ($pcf_value['show_when'] == 'only_authorized')) {
						continue;
					}

					$method['custom_fields'][$pcf_id] = $payment_custom_fields[$pcf_id];
				}
			}
		}

		unset($method);

		if(isset($this->request->post['shipping_method'])){
			$selectedShippingMethod = $this->request->post['shipping_method'];
		} else {
			$selectedShippingMethod = !empty($this->session->data['shipping_method']) ? $this->session->data['shipping_method']['code'] : '';
		}

		$filteredMethodData = array();

		foreach ($method_data as $payment_code => $method) {
			if (isset($opc_payment_option[$payment_code])) {

				$availableShippingMethods = isset($opc_payment_option[$payment_code]['shipping']) ? $opc_payment_option[$payment_code]['shipping'] : array();

				if (empty($availableShippingMethods)) {
					if (!$this->customer->isLogged() && $opc_payment_option[$payment_code]['quest'] == 1) {
						$filteredMethodData[$payment_code] = $method;
					} elseif ($this->customer->isLogged() && $opc_payment_option[$payment_code]['authorized'] == 1) {
						$filteredMethodData[$payment_code] = $method;
					}
				} else {
					$foundMatchingShippingMethod = false;

					foreach ($availableShippingMethods as $shipping_code => $shipping_method) {
						if ($shipping_method['code'] === $selectedShippingMethod) {
							$foundMatchingShippingMethod = true;
							break;
						}
					}

					if ($foundMatchingShippingMethod) {
						if (!$this->customer->isLogged() && $opc_payment_option[$payment_code]['quest'] == 1) {
							$filteredMethodData[$payment_code] = $method;
						} elseif ($this->customer->isLogged() && $opc_payment_option[$payment_code]['authorized'] == 1) {
							$filteredMethodData[$payment_code] = $method;
						}
					}
				}
			} else {
				$filteredMethodData[$payment_code] = $method;
			}
		}

		$this->session->data['payment_methods'] = $filteredMethodData;

		if (!empty($filteredMethodData)) {
			if (!empty($this->session->data['payment_method']) && !isset($filteredMethodData[$this->session->data['payment_method']['code']])) {
				$method_keys = array_keys($filteredMethodData);
				$this->session->data['payment_method'] = $filteredMethodData[$method_keys[0]];
			} elseif (!isset($this->session->data['payment_method']) && $filteredMethodData) {
				$method_keys = array_keys($filteredMethodData);
				$this->session->data['payment_method'] = $filteredMethodData[$method_keys[0]];
			}
		}

		$this->load->language('checkout/onepcheckout');
		$data['title_payment_method'] = $this->language->get('title_payment_method');
		$data['text_payment_method'] = $this->language->get('text_payment_method');
		$data['text_comments'] = $this->language->get('text_comments');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');

		$data['button_continue'] = $this->language->get('button_continue');

		if (empty($this->session->data['payment_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['payment_methods'])) {
			$data['payment_methods'] = $this->session->data['payment_methods'];
		} else {
			$data['payment_methods'] = array();
		}

		if(isset($this->request->post['payment_method']) && isset($this->session->data['payment_methods'][$this->request->post['payment_method']])){
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['payment_code'] = $this->session->data['payment_method']['code'];
		} else {
			$data['payment_code'] = '';
		}

		if(isset($this->request->post['custom_field']['opc_payment'])) {
			$data['payment_custom_field'] = $this->session->data['guest']['opc_payment_custom_field'] = $this->request->post['custom_field']['opc_payment'];
		} elseif (isset($this->session->data['guest']['opc_payment_custom_field'])) {
			$data['payment_custom_field'] = $this->session->data['guest']['opc_payment_custom_field'];
		} else {
			$data['payment_custom_field'] = [];
		}

		if (isset($this->request->post['comment'])){
			$this->session->data['comment'] = $this->request->post['comment'];
		}

		if (isset($this->request->post['agree'])){
			$this->session->data['agree'] = $this->request->post['agree'];
		}

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_payment_method', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_payment_method', $data);
		}
	}

	public function cart($render = true, &$data = array()){

		$this->load->language('checkout/cart');
		$this->load->language('extension/total/coupon');
		$this->load->language('extension/total/reward');
		$this->load->language('extension/total/voucher');
		$this->load->language('checkout/onepcheckout');

		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = array();
		}

		$points = $this->customer->getRewardPoints();

		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$data['text_model'] = $this->language->get('text_model');
		$data['column_price'] = $this->language->get('column_price');
		$data['column_total'] = $this->language->get('column_total');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['text_recurring_item'] = $this->language->get('text_recurring_item');

		$data['cart_width'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width');
		$data['cart_height']	= $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height');

		$data['products'] = array();

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
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
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

			$data['products'][] = array(
				'minimum'      => !empty($product['minimum'] && $product['minimum'] > 0) ? $product['minimum'] : 1,
				'key'				=> $product['cart_id'],
				'product_id' 	=> $product['product_id'],
				'thumb'			=> $image,
				'name'			=> $product['name'],
				'model'			=> $product['model'],
				'option'			=> $option_data,
				'quantity'		=> $product['quantity'],
				'stock'			=> $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				'reward'			=> ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
				'price'			=> $price,
				'total'			=> $total,
				'href'			=> $this->url->link('product/product', 'product_id=' . $product['product_id']),
				'remove'			=> $this->url->link('checkout/cart', 'remove=' . $product['cart_id']),
				'recurring'		=> isset($product['recurring'])?$product['recurring']:'',
			);
		}

		$data['products_recurring'] = array();

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

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_cart', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_cart', $data);
		}
	}

	public function totals($render = true, &$data = array()){

		$this->load->language('checkout/cart');
		$this->load->language('extension/total/coupon');
		$this->load->language('extension/total/reward');
		$this->load->language('extension/total/voucher');
		$this->load->language('checkout/onepcheckout');

		$points = $this->customer->getRewardPoints();

		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}

		$data['text_you_order'] = $this->language->get('text_you_order');
		$data['text_coupon'] = $this->language->get('text_coupon');
		$data['text_voucher'] = $this->language->get('text_voucher');
		$data['text_checkout_confirm'] = $this->language->get('text_checkout_confirm');

		$data['text_next'] = $this->language->get('text_next');
		$data['text_next_choice'] = $this->language->get('text_next_choice');
		$data['entry_coupon'] = $this->language->get('entry_coupon');
		$data['entry_voucher'] = $this->language->get('entry_voucher');
		$data['entry_reward'] = sprintf($this->language->get('entry_reward'), $points_total);
		$data['text_reward'] = sprintf($this->language->get('text_reward'), $points);
		$data['text_loading'] = $this->language->get('text_loading');

		$data['button_checkout'] = $this->language->get('button_checkout');

		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$data['text_agree'] = '';
			}
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->session->data['agree'])) {
			$data['agree'] = $this->session->data['agree'];
		} else {
			$data['agree'] = '';
		}

		if($this->config->get('opc_agree_default')){
			$data['agree'] = $this->config->get('opc_agree_default');
		}

		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = array();
		}

		$data['coupon_status'] = $this->config->get('total_coupon_status');

		if (isset($this->request->post['coupon'])) {
			$data['coupon'] = $this->request->post['coupon'];
		} elseif (isset($this->session->data['coupon'])) {
			$data['coupon'] = $this->session->data['coupon'];
		} else {
			$data['coupon'] = '';
		}

		$data['voucher_status'] = $this->config->get('total_voucher_status');

		if (isset($this->request->post['voucher'])) {
			$data['voucher'] = $this->request->post['voucher'];
		} elseif (isset($this->session->data['voucher'])) {
			$data['voucher'] = $this->session->data['voucher'];
		} else {
			$data['voucher'] = '';
		}

		$data['reward_status'] = ($points && $points_total && $this->config->get('total_reward_status'));

		if (isset($this->request->post['reward'])) {
			$data['reward'] = $this->request->post['reward'];
		} elseif (isset($this->session->data['reward'])) {
			$data['reward'] = $this->session->data['reward'];
		} else {
			$data['reward'] = '';
		}

		/*Free Shipping Progress*/
		$opc_setting_shipping_methods = $this->config->get('opc_setting_shipping_methods');

		$free_shipping_data = array();

		if(isset($this->session->data['shipping_method']) && (!empty($this->session->data['shipping_method']['code']))){
			$shipping_code = str_replace(".","_",$this->session->data['shipping_method']['code']);

			if(!empty($opc_setting_shipping_methods[$shipping_code])){
				$free_shipping_data = $opc_setting_shipping_methods[$shipping_code];
			} else {
				$free_shipping_data = $opc_setting_shipping_methods['default'];
			}
		} else {
			$free_shipping_data = $opc_setting_shipping_methods['default'];
		}

		$order_sum = $this->cart->getSubTotal();

		$free_shipping_from = isset($free_shipping_data['free_shipping_price']) ? $free_shipping_data['free_shipping_price'] : 0;

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
		/*End Free Shipping Progress*/

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
					if($data['free_shipping_status'] && $fs_percentage == 100 && ($result['code'] =='shipping')){
						if(isset($this->session->data['shipping_method']['cost'])){
							$this->session->data['shipping_method']['cost'] = 0;
						}
						continue;
					}
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

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'value' => $total['value'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}

		$data['text_not_call_me'] = $this->language->get('text_not_call_me');
		$data['opc_show_not_call_me'] = $this->config->get('opc_show_not_call_me');

		if (isset($this->request->post['opc_not_call_me'])) {
			$data['opc_not_call_me'] = $this->session->data['guest']['opc_not_call_me'] = $this->request->post['opc_not_call_me'];
		} elseif (isset($this->session->data['guest']['opc_not_call_me'])) {
			$data['opc_not_call_me'] = $this->session->data['guest']['opc_not_call_me'];
		} else {
			$data['opc_not_call_me'] = '';
		}

		$data['text_contact_telegram_viber'] = $this->language->get('text_contact_telegram_viber');
		$data['opc_show_contact_telegram_viber'] = $this->config->get('opc_show_contact_telegram_viber');

		if (isset($this->request->post['opc_contact_telegram_viber'])) {
			$data['opc_contact_telegram_viber'] = $this->session->data['guest']['opc_contact_telegram_viber'] = $this->request->post['opc_contact_telegram_viber'];
		} elseif (isset($this->session->data['guest']['opc_contact_telegram_viber'])) {
			$data['opc_contact_telegram_viber'] = $this->session->data['guest']['opc_contact_telegram_viber'];
		} else {
			$data['opc_contact_telegram_viber'] = '';
		}

		$data['opc_show_weight'] = $this->config->get('opc_show_weight');

		if ($this->config->get('config_cart_weight')) {
			$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
		} else {
			$data['weight'] = '';
		}

		$data['payment'] = false;

		if ($render !== false){
			$this->response->setOutput($this->load->view('checkout/onepcheckout_totals', $data));
		} else {
			return $this->load->view('checkout/onepcheckout_totals', $data);
		}
	}

	private function confirm() {

		$redirect = '';
		$data['payment'] = false;

		$this->load->language('checkout/checkout');

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}
		}

		/*Free Shipping Progress*/
		$opc_setting_shipping_methods = $this->config->get('opc_setting_shipping_methods');

		$free_shipping_data = array();

		if(isset($this->session->data['shipping_method']) && (!empty($this->session->data['shipping_method']['code'] !=''))){
			$shipping_code = str_replace(".","_",$this->session->data['shipping_method']['code']);

			if(!empty($opc_setting_shipping_methods[$shipping_code])){
				$free_shipping_data = $opc_setting_shipping_methods[$shipping_code];
			} else {
				$free_shipping_data = $opc_setting_shipping_methods['default'];
			}
		} else {
			$free_shipping_data = $opc_setting_shipping_methods['default'];
		}

		$order_sum = $this->cart->getSubTotal();

		$free_shipping_from = isset($free_shipping_data['free_shipping_price']) ? $free_shipping_data['free_shipping_price'] : 0;

		$free_shipping_status = isset($free_shipping_data['free_shipping_status']) ? $free_shipping_data['free_shipping_status'] : false;

		if($free_shipping_from == 0){
			$free_shipping_status = false;
		}

		if ($order_sum >= $free_shipping_from) {
			$fs_percentage = 100;
		} else {
			$fs_percentage = round(($order_sum / $free_shipping_from) * 100, 2);
		}
		/*End Free Shipping Progress*/

		$order_data = array();

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
				if($free_shipping_status && $fs_percentage == 100 && ($result['code'] =='shipping')){
					if(isset($this->session->data['shipping_method']['cost'])){
						$this->session->data['shipping_method']['cost'] = 0;
						$this->session->data['shipping_method']['title'] = $this->language->get('text_free_shipping');
					}
				}
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

		$opc_email_default = $this->config->get('opc_email_default_on_empty') ?: 'empty' . time() . '@localhost.net';

		$this->load->model('account/customer');

		if ($this->customer->isLogged()) {
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

			$order_data['customer_id'] = $this->customer->getId();
			$order_data['customer_group_id'] = $customer_info['customer_group_id'];
			$order_data['firstname'] = (!empty($this->session->data['customer']['firstname'])) ? $this->session->data['customer']['firstname'] : '';
			$order_data['lastname'] = (!empty($this->session->data['customer']['lastname'])) ? $this->session->data['customer']['lastname'] : '';
			$order_data['email'] = $customer_info['email'];
			$order_data['telephone'] = (!empty($this->session->data['customer']['telephone'])) ? $this->session->data['customer']['telephone'] : '';
			$order_data['fax'] = (!empty($this->session->data['customer']['fax'])) ? $this->session->data['customer']['fax'] : '';
			$order_data['custom_field'] = json_decode($customer_info['custom_field']);
		} elseif (isset($this->session->data['guest'])) {
			$order_data['customer_id'] = 0;
			$order_data['customer_group_id'] = isset($this->session->data['guest']['customer_group_id'])?$this->session->data['guest']['customer_group_id']:$this->config->get('config_customer_group_id');
			$order_data['firstname'] = isset($this->session->data['guest']['firstname'])?$this->session->data['guest']['firstname'] : '';
			$order_data['lastname'] = isset($this->session->data['guest']['lastname'])?$this->session->data['guest']['lastname'] : '';
			$order_data['email'] = isset($this->session->data['guest']['email'])?$this->session->data['guest']['email'] : $opc_email_default;
			$order_data['telephone'] = isset($this->session->data['guest']['telephone'])?$this->session->data['guest']['telephone'] : '';
			$order_data['fax'] = isset($this->session->data['guest']['fax'])?$this->session->data['guest']['fax'] : '';
			$order_data['custom_field'] = isset($this->session->data['guest']['custom_field']) ? $this->session->data['guest']['custom_field'] : '';
		}

		if(empty($order_data['email'])) {
			$order_data['email'] = $opc_email_default;
		}

		$payment_address = array();

		if (isset($this->session->data['payment_address'])){
			$payment_address = $this->session->data['payment_address'];
		}

		$order_data['payment_firstname'] = $payment_address['firstname'];
		$order_data['payment_lastname'] = isset($payment_address['lastname']) ? $payment_address['lastname'] : '';
		$order_data['payment_company'] = isset($payment_address['company']) ? $payment_address['company'] : '';
		$order_data['payment_company_id'] = isset($payment_address['company_id']) ? $payment_address['company_id'] : '';
		$order_data['payment_tax_id'] = isset($payment_address['tax_id']) ? $payment_address['tax_id']:'';
		$order_data['payment_address_1'] = isset($payment_address['address_1']) ? $payment_address['address_1'] : '';
		$order_data['payment_address_2'] = isset($payment_address['address_2']) ? $payment_address['address_2'] : '';
		$order_data['payment_city'] = isset($payment_address['city']) ? $payment_address['city'] : '';
		$order_data['payment_postcode'] = isset($payment_address['postcode']) ? $payment_address['postcode'] : '';
		$order_data['payment_zone'] = isset($payment_address['zone']) ? $payment_address['zone'] : '';
		$order_data['payment_zone_id'] = isset($payment_address['zone_id']) ? $payment_address['zone_id'] : '';
		$order_data['payment_country'] = isset($payment_address['country']) ? $payment_address['country'] : '';
		$order_data['payment_country_id'] = isset($payment_address['country_id']) ? $payment_address['country_id'] : '';
		$order_data['payment_address_format'] = isset($payment_address['address_format']) ? $payment_address['address_format'] : '';
		$order_data['payment_custom_field'] = isset($payment_address['custom_field']) ? $payment_address['custom_field'] : array();

		if (isset($this->session->data['payment_method']['title'])) {
			$order_data['payment_method'] = $this->session->data['payment_method']['title'];
		} else {
			$order_data['payment_method'] = '';
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$order_data['payment_code'] = $this->session->data['payment_method']['code'];
		} else {
			$order_data['payment_code'] = '';
		}

		if ($this->cart->hasShipping()) {
			$new_s = isset($this->request->post['shipping_address']) && $this->request->post['shipping_address'] == 'new' ? 1 : 0;

			if (isset($this->session->data['shipping_address']) && $new_s){
				$shipping_address = $this->session->data['shipping_address'];
			} else {
				$shipping_address = $this->session->data['payment_address'];
			}

			$order_data['shipping_firstname'] = isset($shipping_address['firstname']) ? $shipping_address['firstname'] : '';
			$order_data['shipping_lastname'] = isset($shipping_address['lastname']) ? $shipping_address['lastname'] : '';
			$order_data['shipping_company'] = isset($shipping_address['company']) ? $shipping_address['company'] : '';
			$order_data['shipping_address_1'] = isset($shipping_address['address_1']) ? $shipping_address['address_1'] : '';
			$order_data['shipping_address_2'] = isset($shipping_address['address_2']) ? $shipping_address['address_2'] : '';
			$order_data['shipping_city'] = isset($shipping_address['city']) ? $shipping_address['city'] : '';
			$order_data['shipping_postcode'] = isset($shipping_address['postcode']) ? $shipping_address['postcode'] : '';
			$order_data['shipping_zone'] = isset($shipping_address['zone']) ? $shipping_address['zone'] : '';
			$order_data['shipping_zone_id'] = isset($shipping_address['zone_id']) ? $shipping_address['zone_id'] : '';
			$order_data['shipping_country'] = isset($shipping_address['country']) ? $shipping_address['country'] : '';
			$order_data['shipping_country_id'] = isset($shipping_address['country_id']) ? $shipping_address['country_id'] : '';
			$order_data['shipping_address_format'] =  isset($shipping_address['address_format']) ? $shipping_address['address_format'] : '';
			$order_data['shipping_custom_field'] = isset($shipping_address['custom_field']) ? $shipping_address['custom_field'] : array();

			if (isset($this->session->data['shipping_method']['title'])) {
				$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
			} else {
				$order_data['shipping_method'] = '';
			}

			if (isset($this->session->data['shipping_method']['code'])) {
				$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
			} else {
				$order_data['shipping_code'] = '';
			}
		} else {
			$order_data['shipping_firstname'] = '';
			$order_data['shipping_lastname'] = '';
			$order_data['shipping_company'] = '';
			$order_data['shipping_address_1'] = '';
			$order_data['shipping_address_2'] = '';
			$order_data['shipping_city'] = '';
			$order_data['shipping_postcode'] = '';
			$order_data['shipping_zone'] = '';
			$order_data['shipping_zone_id'] = '';
			$order_data['shipping_country'] = '';
			$order_data['shipping_country_id'] = '';
			$order_data['shipping_address_format'] = '';
			$order_data['shipping_custom_field'] = array();
			$order_data['shipping_method'] = '';
			$order_data['shipping_code'] = '';
		}

		$order_data['comment'] = $this->session->data['comment'];

		$customer_fields = array();

		$customer_methods_data = $this->config->get('opc_customer_setting');

		if(isset($order_data['shipping_code']) && (!empty($order_data['shipping_code']))){
			$shipping_code = str_replace(".","_",$order_data['shipping_code']);

			if(!empty($customer_methods_data[$shipping_code])){
				$customer_fields = $customer_methods_data[$shipping_code];
			} else {
				$customer_fields = $customer_methods_data['default'];
			}
		} else {
			$customer_fields = $customer_methods_data['default'];
		}

		foreach($customer_fields as $cfield => $customer_field){
			if(is_array($customer_field) && isset($customer_field['status']) && $customer_field['status'] != '0'){
				if(!empty($customer_field['type_action']) && ($customer_field['type_action'] == 'write_to')){
					if(!empty($order_data[$cfield])){
						$order_data[$customer_field['action_field']] = $order_data[$customer_field['action_field']] . ' ' . $order_data[$cfield];
					}
				}
			}
		}

		$shipping_methods_fields = array();

		$shipping_methods_data = $this->config->get('opc_payment_address');

		if(isset($order_data['shipping_code']) && (!empty($order_data['shipping_code']))){
			$shipping_code = str_replace(".","_",$order_data['shipping_code']);

			if(!empty($shipping_methods_data[$shipping_code])){
				$shipping_methods_fields = $shipping_methods_data[$shipping_code];
			} else {
				$shipping_methods_fields = $shipping_methods_data['default'];
			}
		} else {
			$shipping_methods_fields = $shipping_methods_data['default'];
		}

		foreach($shipping_methods_fields as $sfield => $shipping_field){
			if(is_array($shipping_field) && isset($shipping_field['status']) && $shipping_field['status'] != '0'){
				if(!empty($shipping_field['type_action']) && ($shipping_field['type_action'] == 'write_to')){
					if(!empty($order_data['shipping_' . $sfield])){
						$order_data[$shipping_field['action_field']] = $order_data[$shipping_field['action_field']] . ' ' . $order_data['shipping_' . $sfield];
					}
				}
			}
		}

		// add to Comment Customer Custom field
		if (isset($this->session->data['guest']['customer_custom_field']) && !empty($this->session->data['guest']['customer_custom_field'])) {
		   $this->load->model('checkout/onepcheckout');

			foreach($this->session->data['guest']['customer_custom_field'] as $custom_field_id => $custom_field_value){

				$customer_custom_fields = $this->model_checkout_onepcheckout->getCustomField($custom_field_id);

				if ($customer_custom_fields['type'] == 'select' || $customer_custom_fields['type'] == 'radio') {
					$custom_field_value_data = $this->model_checkout_onepcheckout->getCustomFieldValue($custom_field_value);
					if (!empty($custom_field_value_data['name'])) {
						$order_data['comment'] .= "\n" . $customer_custom_fields['name'] . ': ' . $custom_field_value_data['name'];
					}
				} elseif($customer_custom_fields['type'] == 'checkbox' && is_array($custom_field_value)){
					$custom_field_value_data = $this->model_checkout_onepcheckout->getCustomFieldValues($custom_field_id);

					$checkbox_values = array();

					foreach ($custom_field_value as $custom_field_value_id) {
						if (isset($custom_field_value_data[$custom_field_value_id])) {
							$checkbox_values[] = $custom_field_value_data[$custom_field_value_id]['name'];
						}
					}

					if (!empty($checkbox_values)) {
						$checkbox_values_text = implode(', ', $checkbox_values);
						$order_data['comment'] .= "\n" . $customer_custom_fields['name'] . ': ' . $checkbox_values_text;
					}
				} else {
					if($customer_custom_fields['action_field']){
						$order_data['comment'] .= "\n" . $customer_custom_fields['name'] . ': ' . $custom_field_value;
					}
				}
			}
		}

		// add to Comment address Custom field
		if (isset($this->session->data['guest']['address_custom_field']) && !empty($this->session->data['guest']['address_custom_field'])) {
		   $this->load->model('checkout/onepcheckout');

			foreach($this->session->data['guest']['address_custom_field'] as $custom_field_id => $custom_field_value){

				$address_custom_fields = $this->model_checkout_onepcheckout->getCustomField($custom_field_id);
				if ($address_custom_fields['type'] == 'select' || $address_custom_fields['type'] == 'radio') {
					$custom_field_value_data = $this->model_checkout_onepcheckout->getCustomFieldValue($custom_field_value);
					if (!empty($custom_field_value_data['name'])) {
						$order_data['comment'] .= "\n" . $address_custom_fields['name'] . ': ' . $custom_field_value_data['name'];
					}
				} elseif($address_custom_fields['type'] == 'checkbox' && is_array($custom_field_value)){
					$custom_field_value_data = $this->model_checkout_onepcheckout->getCustomFieldValues($custom_field_id);

					$checkbox_values = array();

					foreach ($custom_field_value as $custom_field_value_id) {
						if (isset($custom_field_value_data[$custom_field_value_id])) {
							$checkbox_values[] = $custom_field_value_data[$custom_field_value_id]['name'];
						}
					}

					if (!empty($checkbox_values)) {
						$checkbox_values_text = implode(', ', $checkbox_values);
						$order_data['comment'] .= "\n" . $address_custom_fields['name'] . ': ' . $checkbox_values_text;
					}
				} else {
					if($address_custom_fields['action_field']){
						$order_data['comment'] .= "\n" . $address_custom_fields['name'] . ': ' . $custom_field_value;
					}
				}
			}
		}

		// add to Comment Payment Custom field
		if (isset($this->session->data['guest']['payment_custom_field']) && !empty($this->session->data['guest']['payment_custom_field'])) {
		   $this->load->model('checkout/onepcheckout');

			foreach($this->session->data['guest']['payment_custom_field'] as $custom_field_id => $custom_field_value){

				$payment_custom_fields = $this->model_checkout_onepcheckout->getCustomField($custom_field_id);

				if ($payment_custom_fields['type'] == 'select' || $payment_custom_fields['type'] == 'radio') {
					$custom_field_value_data = $this->model_checkout_onepcheckout->getCustomFieldValue($custom_field_value);
					if (!empty($custom_field_value_data['name'])) {
						$order_data['comment'] .= "\n" . $payment_custom_fields['name'] . ': ' . $custom_field_value_data['name'];
					}
				} elseif($payment_custom_fields['type'] == 'checkbox' && is_array($custom_field_value)){
					$custom_field_value_data = $this->model_checkout_onepcheckout->getCustomFieldValues($custom_field_id);

					$checkbox_values = array();

					foreach ($custom_field_value as $custom_field_value_id) {
						if (isset($custom_field_value_data[$custom_field_value_id])) {
							$checkbox_values[] = $custom_field_value_data[$custom_field_value_id]['name'];
						}
					}

					if (!empty($checkbox_values)) {
						$checkbox_values_text = implode(', ', $checkbox_values);
						$order_data['comment'] .= "\n" . $payment_custom_fields['name'] . ': ' . $checkbox_values_text;
					}
				} else {
					if($payment_custom_fields['action_field']){
						$order_data['comment'] .= "\n" . $payment_custom_fields['name'] . ': ' . $custom_field_value;
					}
				}
			}
		}

		if(isset($this->session->data['guest']['opc_not_call_me']) && $this->session->data['guest']['opc_not_call_me'] == 1){
			$order_data['comment'] .= "\n" . '<b>'. $this->language->get('text_not_call_me') . '</b>';
		}

		if(isset($this->session->data['guest']['opc_contact_telegram_viber']) && $this->session->data['guest']['opc_contact_telegram_viber'] == 1){
			$order_data['comment'] .= "\n" . '<b>'. $this->language->get('text_contact_telegram_viber') . '</b>';
		}

		$order_data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
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
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			);
		}

		// Gift Voucher
		$order_data['vouchers'] = array();

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$order_data['vouchers'][] = array(
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']
				);
			}
		}

		$order_data['total'] = $total;

		if (isset($this->request->cookie['tracking'])) {
			$order_data['tracking'] = $this->request->cookie['tracking'];

			$subtotal = $this->cart->getSubTotal();

			// Affiliate
			$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

			if ($affiliate_info) {
				$order_data['affiliate_id'] = $affiliate_info['customer_id'];
				$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
			}

			// Marketing
			$this->load->model('checkout/marketing');

			$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

			if ($marketing_info) {
				$order_data['marketing_id'] = $marketing_info['marketing_id'];
			} else {
				$order_data['marketing_id'] = 0;
			}
		} else {
			$order_data['affiliate_id'] = 0;
			$order_data['commission'] = 0;
			$order_data['marketing_id'] = 0;
			$order_data['tracking'] = '';
		}

		$order_data['language_id'] = $this->config->get('config_language_id');
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

		$this->load->model('checkout/order');

		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

		$json['success']['payment'] = $this->load->controller('extension/payment/' . $this->session->data['payment_method']['code']);

		if($json['success']){
			$this->load->model('checkout/onepcheckout');
			if (isset($this->session->data['abandoned_id']) && $this->session->data['abandoned_id'] != '') {
				$abandoned_id = $this->session->data['abandoned_id'];
				$this->model_checkout_onepcheckout->removeAbandonedOrder($abandoned_id);
			}
			return $json;
		}
	}

	public function cart_edit() {
		$this->load->language('checkout/cart');

		$json = array();

		if (!empty($this->request->post['quantity'])) {
			foreach ($this->request->post['quantity'] as $key => $value) {
				$this->cart->update($key, $value);
			}

			unset($this->session->data['reward']);
			$json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
