<?php
class ControllerExtensionModuleUpstoreProductviewed extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/upstore_productviewed');
		$this->load->language('upstore/theme');
		$this->load->language('product/product');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$this->load->model('upstore/theme');
		$this->load->model('extension/module/upstore_pro_sticker');
		$this->load->model('extension/module/upstore_productany');

		$data['bg_image'] = ($this->config->has('bg_product_image') && $this->config->get('bg_product_image') == 1) ? true : false;
		$data['ch_type_btn'] = ($this->config->has('ch_type_btn') && $this->config->get('ch_type_btn') == 1) ? 'rounded' : 'squircle';

		$data['nst_data'] = $this->config->get('nst_data');
		if(isset($data['nst_data']['lazyload_module']) && ($data['nst_data']['lazyload_module'] == 1)){
			$data['lazyload_module'] = true;
			if (isset($data['nst_data']['lazyload_image']) && ($data['nst_data']['lazyload_image'] !='')) {
				$data['lazy_image'] = 'image/' . $data['nst_data']['lazyload_image'];
			} else {
				$data['lazy_image'] = 'image/catalog/lazyload/lazyload.jpg';
			}
		} else {
			$data['lazyload_module'] = false;
		}

		$data['setting_module'] = $this->config->get('setting_module');
		$dop_image = true;
		if(deviceType == 'phone') {
			$dop_image = false;
			if(isset($data['setting_module']['hidden_model']) && ($data['setting_module']['hidden_model'] == 1)){
				$data['setting_module']['status_model'] = 0;
			}
			if(isset($data['setting_module']['hidden_desc']) && ($data['setting_module']['hidden_desc'] == 1)){
				$data['setting_module']['status_description'] = 0;
			}
			if(isset($data['setting_module']['hidden_rating']) && ($data['setting_module']['hidden_rating'] == 1)){
				$data['setting_module']['status_rating'] = 0;
			}
			if(isset($data['setting_module']['hidden_actions']) && ($data['setting_module']['hidden_actions'] == 1)){
				$data['setting_module']['status_actions'] = 0;
			}
		}

		$data['config_additional_settings_upstore'] = $this->config->get('config_additional_settings_upstore');
		$data['show_special_timer_module'] = $this->config->get('config_show_special_timer_module');
		$data['on_off_sticker_special'] = $this->config->get('on_off_sticker_special');
		$data['on_off_percent_discount'] = (!empty($this->config->get('on_off_percent_discount')) ? 1 : 0);
		$data['config_change_icon_sticker_special'] = $this->config->get('config_change_icon_sticker_special');
		$data['on_off_sticker_topbestseller'] = $this->config->get('on_off_sticker_topbestseller');
		$data['config_limit_order_product_topbestseller'] = $this->config->get('config_limit_order_product_topbestseller');
		$data['config_change_icon_sticker_topbestseller'] = $this->config->get('config_change_icon_sticker_topbestseller');
		$data['on_off_sticker_popular'] = $this->config->get('on_off_sticker_popular');
		$data['config_min_quantity_popular'] = $this->config->get('config_min_quantity_popular');
		$data['config_change_icon_sticker_popular'] = $this->config->get('config_change_icon_sticker_popular');
		$data['on_off_sticker_newproduct'] = $this->config->get('on_off_sticker_newproduct');
		$data['config_limit_day_newproduct'] = $this->config->get('config_limit_day_newproduct');
		$data['config_change_icon_sticker_newproduct'] = $this->config->get('config_change_icon_sticker_newproduct');
		$data['config_on_off_featured_quickview'] = $this->config->get('config_on_off_featured_quickview');
		$data['lang_id'] = $this->config->get('config_language_id');
		$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special');
		$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
		$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
		$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');
		$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');
		$data['config_quickview_btn_name'] = $this->config->get('config_quickview_btn_name');
		$data['change_text_cart_button_out_of_stock'] = $this->config->get('config_change_text_cart_button_out_of_stock');
		$data['show_stock_status'] = $this->config->get('config_show_stock_status');
		$config_disable_cart_button_text = $this->config->get('config_disable_cart_button_text');
		if(!empty($config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'])){
			$data['disable_cart_button_text'] = $config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'];
		} else {
			$data['disable_cart_button_text'] = $this->language->get('disable_cart_button_text');
		}
		$data['disable_cart_button'] = $this->config->get('config_disable_cart_button');
		$data['disable_fastorder_button'] = $this->config->get('config_disable_fastorder_button');

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

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (isset($this->request->cookie['productviewed'])) {
			$products = explode(',', $this->request->cookie['productviewed']);
		}

		if (!empty( $products)) {
			$products = array_slice($products, 0, $setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
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

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}

					$image_h = false;
					$image_hm = array();

					if(isset($data['setting_module']['image_hover']) && ($data['setting_module']['image_hover'] > 0) && $dop_image){
						$results_img = $this->model_catalog_product->getProductImages($product_info['product_id']);
						if($data['setting_module']['image_hover'] == 1){
							foreach ($results_img as $key => $result_img) {
								if($key == 1){
									break;
								}
								$image_h = $this->model_tool_image->resize($result_img['image'], $setting['width'], $setting['height']);
							}
						} else {
							foreach ($results_img as $key => $img) {
								if($key == 6){
									break;
								}
								$image_hm[] = $this->model_tool_image->resize($img['image'], $setting['width'], $setting['height']);
							}
						}
					}

					if ((float)$product_info['special']) {
						$price2 = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
						$special2 = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
						$skidka = $special2/($price2/100)-100;
					} else {
						$skidka = "";
					}

					$top_bestsellers = $this->model_catalog_product->getTopSeller($product_info['product_id']);
					$product_quantity = $product_info['quantity'];
					$stock_status = $product_info['stock_status'];

					if ((float)$product_info['special']) {
						$special_date_end = $this->model_catalog_product->getDateEnd($product_info['product_id']);
					} else {
						$special_date_end = false;
					}

					if((isset($product_info['date_available'])&&(round((strtotime(date("Y-m-d"))-strtotime($product_info['date_available']))/86400))<=$this->config->get('config_limit_day_newproduct'))) {
						$sticker_new_prod = true;
					} else {
						$sticker_new_prod = false;
					}

					if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
						$price_value = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
					} else {
						$price_value = false;
					}

					if ((float)$product_info['special']) {
						$special_value = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
					} else {
						$special_value = false;
					}

					$show_buy_button = true;

					if ($product_info['quantity'] <= 0 && $notify_stock_status) {
						$show_buy_button = false;
					}

					$data['products'][] = array(
						'show_buy_button' 	=> $show_buy_button,
						'in_waitlist' 			=> in_array($product_info['product_id'], $pids_in_waitlist) ? true : false,
						'pro_sticker'			=> $this->model_extension_module_upstore_pro_sticker->getProStickers($product_info['product_id'],'module_page'),
						'rating_stars'			=> $this->model_upstore_theme->productRatingStars($rating),
						'in_cart' 				=> in_array($product_info['product_id'], $pids_in_cart) ? true : false,
						'width' 					=> $setting['width'],
						'height' 				=> $setting['height'],
						'sticker_new_prod'	=> $sticker_new_prod,
						'image_h'				=> $image_h,
						'image_hm'				=> $image_hm,
						'product_quantity' 	=> $product_quantity,
						'stock_status' 		=> $stock_status,
						'reviews'    			=> sprintf((int)$product_info['reviews']),
						'skidka'     			=> round((float)$skidka),
						'model'     			=> $product_info['model'],
						'date_available'		=> $product_info['date_available'],
						'date_end'	 			=> $special_date_end,
						'viewed'	 				=> $product_info['viewed'],
						'top_bestsellers'		=> $top_bestsellers['total'],
						'minimum'     			=> ($product_info['minimum'] > 0) ? $product_info['minimum'] : 1,
						'price_value' 			=> $price_value,
						'special_value' 		=> $special_value,
						'product_id'  			=> $product_info['product_id'],
						'thumb'       			=> $image,
						'name'        			=> $product_info['name'],
						'description' 			=> utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       			=> $price,
						'special'     			=> $special,
						'tax'         			=> $tax,
						'rating'      			=> $rating,
						'href'        			=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/module/upstore_productviewed', $data);
		}
	}
}
