<?php
class ControllerExtensionModuleUpstoreProductany extends Controller {
	public function index($setting) {
		$this->load->language('upstore/theme');
		$this->load->language('extension/module/productany');
		$this->load->language('product/product');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('upstore/theme');
		$this->load->model('extension/module/upstore_pro_sticker');
		$this->load->model('extension/module/upstore_productany');

		$data['bg_image'] = ($this->config->has('bg_product_image') && $this->config->get('bg_product_image') == 1) ? true : false;
		$data['ch_type_btn'] = ($this->config->has('ch_type_btn') && $this->config->get('ch_type_btn') == 1) ? 'rounded' : 'squircle';

		$data['promo_slider_status'] = !empty($setting['promo_slider_status']) ? $setting['promo_slider_status'] : false;
		$data['promo_slider_position'] = !empty($setting['promo_slider_position']) ? $setting['promo_slider_position'] : 'left';
		$data['promo_slider_status_autoplay'] = !empty($setting['promo_slider_status_autoplay']) ? $setting['promo_slider_status_autoplay'] : false;
		$data['promo_slider_autoplay_delay'] = !empty($setting['promo_slider_autoplay_delay']) ? $setting['promo_slider_autoplay_delay'] : 5000;
		$data['promo_slider_pagination'] = !empty($setting['promo_slider_controls']['pagination']) ? true : false;
		$data['promo_slider_navigation'] = !empty($setting['promo_slider_controls']['navigation']) ? true : false;
		$data['promo_slider_width'] = !empty($setting['promo_slider_width']) ? $setting['promo_slider_width'] : 264;
		$data['promo_slider_height'] = !empty($setting['promo_slider_height']) ? $setting['promo_slider_height'] : 457;


		$hide_out_of_stock = !empty($setting['hide_out_of_stock_products']) ? $setting['hide_out_of_stock_products'] : false;
		$type_module = !empty($setting['type_module']) ? $setting['type_module'] : 'featured';

		$data['promo_slider_items'] = [];
		$language_id = $this->config->get('config_language_id');

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$dir_image = $this->config->get('config_ssl') . 'image/';
		} else {
			$dir_image = $this->config->get('config_url') . 'image/';
		}

		$count_promo_slider = 0;

		if(!empty($setting['promo_slider_items'])){

			foreach($setting['promo_slider_items'] as $promo_slider_item){

				if (!empty($promo_slider_item['price'][$language_id])) {
					$promo_slider_price = $this->currency->format((float)$promo_slider_item['price'][$language_id], $this->session->data['currency']);
				} else {
					$promo_slider_price = false;
				}

				if (is_file(DIR_IMAGE . $promo_slider_item['image'][$language_id])) {
					//$image = $dir_image . $promo_slider_item['image'][$language_id];
					$image = $this->model_tool_image->resize($promo_slider_item['image'][$language_id], round($data['promo_slider_width']*1.2), round($data['promo_slider_height']*1.2));
				} else {
					continue;
				}

				$count_promo_slider++;

				$data['promo_slider_items'][] = [
					'color_title_xs' => isset($promo_slider_item['color_title_xs'][$language_id]) ? $promo_slider_item['color_title_xs'][$language_id] : '',
					'title_xs' => isset($promo_slider_item['title_xs'][$language_id]) ? html_entity_decode($promo_slider_item['title_xs'][$language_id], ENT_QUOTES, 'UTF-8') : '',
					'color_title_lg' => isset($promo_slider_item['color_title_lg'][$language_id]) ? $promo_slider_item['color_title_lg'][$language_id] : '',
					'title_lg' => isset($promo_slider_item['title_lg'][$language_id]) ? html_entity_decode($promo_slider_item['title_lg'][$language_id], ENT_QUOTES, 'UTF-8') : '',
					'color_price_from' => isset($promo_slider_item['color_price_from'][$language_id]) ? $promo_slider_item['color_price_from'][$language_id] : '',
					'text_price_from' => isset($promo_slider_item['text_price_from'][$language_id]) ? $promo_slider_item['text_price_from'][$language_id] : '',
					'color_price' => isset($promo_slider_item['color_price'][$language_id]) ? $promo_slider_item['color_price'][$language_id] : '',
					'link' => isset($promo_slider_item['link'][$language_id]) ? $promo_slider_item['link'][$language_id] : '',
					'bg_block' => isset($promo_slider_item['bg_block'][$language_id]) ? $promo_slider_item['bg_block'][$language_id] : '',
					'sort_order' => isset($promo_slider_item['sort_order']) ? $promo_slider_item['sort_order'] : 0,
					'image' => $image,
					'image_display_mode' => (!empty($promo_slider_item['image_display_mode'][$language_id])) ? $promo_slider_item['image_display_mode'][$language_id] : 'cover',
					'price' => $promo_slider_price,
				];
			}
		}

		if($count_promo_slider == 1){
			$data['promo_slider_pagination'] = false;
			$data['promo_slider_navigation'] = false;
		}

		if (!empty($data['promo_slider_items'])){
			foreach ($data['promo_slider_items'] as $key => $value) {
				$sort[$key] = $value['sort_order'];
			}
			array_multisort($sort, SORT_ASC, $data['promo_slider_items']);
		}

		if(empty($data['promo_slider_items'])){
			$data['promo_slider_status'] = false;
		}

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
		$data['config_title_color_text_productany'] = $setting['config_title_color_text_productany'];
		$data['status_timer_special'] = $setting['status_timer_special'];
		$data['change_text_cart_button_out_of_stock'] = $this->config->get('config_change_text_cart_button_out_of_stock');
		$data['lang_id'] = $this->config->get('config_language_id');
		$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special');
		$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
		$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
		$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');
		$data['config_productany_title'] = $setting['config_productany_title'];
		$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');
		$data['config_quickview_btn_name'] = $this->config->get('config_quickview_btn_name');
		$data['config_on_off_featured_quickview'] = $this->config->get('config_on_off_featured_quickview');
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

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		$additional_filter = [];

		if($type_module == 'latest'){
			$additional_filter = array(
				'sort' => 'p.date_added',
				'order' => 'DESC',
			);
		}

		if($type_module == 'special'){
			$additional_filter = array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
			);
		}

		if($type_module == 'category'){
			$additional_filter = array(
				'sort'  => !empty($setting['sort']) ? $setting['sort'] : 'pd.name',
				'order' => !empty($setting['order']) ? $setting['order'] : 'ASC',
				'filter_category_id' => $setting['category_id'],
				'filter_sub_category' => $setting['filter_sub_category'],
				'hide_out_of_stock' => $hide_out_of_stock,
			);
		}

		$filter_data = array(
			'start' => 0,
			'limit' => $setting['limit'],
		);

		$filter_data = array_merge($filter_data, $additional_filter);

		$products = [];

		$data['products'] = [];

		if($type_module == 'featured'){
			if (!empty($setting['product'])) {
				$products = array_slice($setting['product'], 0, (int)$setting['limit']);
			}
		} elseif($type_module == 'special'){
			$products = $this->model_catalog_product->getProductSpecials($filter_data);
		} elseif($type_module == 'latest'){
			$products = $this->model_catalog_product->getProducts($filter_data);
		} elseif($type_module == 'bestseller'){
			$products = $this->model_catalog_product->getBestSellerProducts($setting['limit']);
		} elseif($type_module == 'popular'){
			$products = $this->model_extension_module_upstore_productany->getPopularProduct($setting['limit']);
		} elseif($type_module == 'viewed'){
			if (isset($this->request->cookie['productviewed'])) {
				$products = explode(',', $this->request->cookie['productviewed']);
			}
			$products = array_slice($products, 0, $setting['limit']);
		} elseif($type_module == 'category'){
			$products = $this->model_extension_module_upstore_productany->getCategoryProducts($filter_data);
		}

		if(!empty($products)){

			foreach ($products as $product_info) {
				if(!is_array($product_info)){
					$product_info = $this->model_catalog_product->getProduct($product_info);
				}
				if ($product_info) {
					if($hide_out_of_stock && $product_info['quantity'] <= 0){
						continue;
					}
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
						'date_end'	 			=> $special_date_end,
						'image_h'				=> $image_h,
						'image_hm'				=> $image_hm,
						'product_quantity' 	=> $product_quantity,
						'stock_status' 		=> $stock_status,
						'reviews'    			=> sprintf((int)$product_info['reviews']),
						'skidka'     			=> round((float)$skidka),
						'model'     			=> $product_info['model'],
						'date_available'		=> $product_info['date_available'],
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
			return $this->load->view('extension/module/upstore_productany', $data);
		}
	}
}