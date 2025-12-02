<?php
class ControllerExtensionModuleUpstoreMegasliderpro extends Controller {
	public function index($setting) {

		static $module = 0;

		$megamenu_setting = $this->config->get('megamenu_setting');

		$data['mm_open_hp'] = false;
		$host = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER;
		if ((isset($this->request->get['route']) && $this->request->get['route'] == 'common/home') || $this->request->server['REQUEST_URI'] == '/') {
			if (isset($megamenu_setting['mm_open_home_page']) && ($megamenu_setting['mm_open_home_page'] == 1) && ($this->config->get('type_menu_header') == 1)) {
				$data['mm_open_hp'] = true;
			}
		}

		$this->load->model('megasliderpro/megaslider');
		$this->load->model('tool/image');
		$this->document->addStyle('catalog/view/theme/upstore/stylesheet/mslider.css');

		$info_module = $this->model_megasliderpro_megaslider->getSettingSlide($setting['banner']);
		$data['info_module'] = $info_module;

		$data['fp_status'] = !empty($info_module['fp_status']) ? $info_module['fp_status'] : false;
		$data['products'] = array();

		if($data['fp_status']){
			$this->load->model('upstore/theme');
			$this->load->model('extension/module/upstore_pro_sticker');

			$data['bg_image'] = ($this->config->has('bg_product_image') && $this->config->get('bg_product_image') == 1) ? true : false;
			$data['ch_type_btn'] = ($this->config->has('ch_type_btn') && $this->config->get('ch_type_btn') == 1) ? 'rounded' : 'squircle';

			$data['position_product'] = !empty($info_module['position_product']) ? $info_module['position_product'] : 'right';
			$data['fp_width'] = !empty($info_module['fp_width']) ? $info_module['fp_width'] : 200;
			$data['fp_height'] = !empty($info_module['fp_height']) ? $info_module['fp_height'] : 200;

			if(!empty($info_module['product'])){
				$products = json_decode($info_module['product'], true);
			} else {
				$products = false;
			}

			$fp_name = array();
			if(!empty($info_module['fp_name'])){
				$fp_name = json_decode($info_module['fp_name'], true);
			}

			if(is_array($products)){
				$data['nst_data'] = $this->config->get('nst_data');
				$data['setting_module'] = $this->config->get('setting_module');

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
				$this->load->language('upstore/theme');

				$data['heading_title'] = !empty($fp_name[$this->config->get('config_language_id')]) ? $fp_name[$this->config->get('config_language_id')] : '';
				$data['text_model'] = $this->language->get('text_model');
				$data['text_instock'] = $this->language->get('text_instock');
				$data['show_special_timer_module'] = $this->config->get('config_show_special_timer_module');
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
				$data['change_text_cart_button_out_of_stock'] = $this->config->get('config_change_text_cart_button_out_of_stock');
				$data['lang_id'] = $this->config->get('config_language_id');
				$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special');
				$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
				$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
				$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');
				$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');

				$this->load->language('product/product');
				$data['config_quickview_btn_name'] = $this->config->get('config_quickview_btn_name');
				$data['config_on_off_special_quickview'] = $this->config->get('config_on_off_special_quickview');
				$data['text_tax'] = $this->language->get('text_tax');
				$data['button_cart'] = $this->language->get('button_cart');
				$data['button_wishlist'] = $this->language->get('button_wishlist');
				$data['button_compare'] = $this->language->get('button_compare');
				$data['show_stock_status'] = $this->config->get('config_show_stock_status');

				$config_disable_cart_button_text = $this->config->get('config_disable_cart_button_text');
				if(!empty($config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'])){
					$data['disable_cart_button_text'] = $config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'];
				} else {
					$data['disable_cart_button_text'] = $this->language->get('disable_cart_button_text');
				}

				$data['disable_cart_button'] = $this->config->get('config_disable_cart_button');
				$data['disable_fastorder_button'] = $this->config->get('config_disable_fastorder_button');
				$this->load->model('catalog/product');

				$data['products'] = array();

				foreach ($products as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);
					if ($product_info) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $data['fp_width'], $data['fp_height']);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $data['fp_width'], $data['fp_height']);
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
									$image_h = $this->model_tool_image->resize($result_img['image'], $data['fp_width'], $data['fp_height']);
								}
							} else {
								foreach ($results_img as $key => $img) {
									if($key == 6){
										break;
									}
									$image_hm[] = $this->model_tool_image->resize($img['image'], $data['fp_width'], $data['fp_height']);
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

						$top_bestsellers = $this->model_catalog_product->getTopSeller($product_id);
						$product_quantity = $product_info['quantity'];
						$stock_status = $product_info['stock_status'];

						if (isset($product_info)) {
							$result = $product_info;
						} else {
							$result = $result;
						}
						if ((float)$result['special']) {
							$special_date_end = $this->model_catalog_product->getDateEnd($product_info['product_id']);
						} else {
							$special_date_end = false;
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
							'show_buy_button' => $show_buy_button,
							'in_waitlist' 		=> in_array($product_info['product_id'], $pids_in_waitlist) ? true : false,
							'pro_sticker'		=> $this->model_extension_module_upstore_pro_sticker->getProStickers($product_info['product_id'],'module_page'),
							'rating_stars'		=> $this->model_upstore_theme->productRatingStars($rating),
							'in_cart' 			=> in_array($product_info['product_id'], $pids_in_cart) ? true : false,
							'date_end'	 		=> $special_date_end,
							'image_h' 			=> $image_h,
							'image_hm' 			=> $image_hm,
							'product_quantity'=> $product_quantity,
							'stock_status' 	=> $stock_status,
							'reviews'    		=> sprintf((int)$product_info['reviews']),
							'skidka'     		=> round((float)$skidka),
							'model'     		=> $product_info['model'],
							'date_available'	=> $product_info['date_available'],
							'viewed'	 			=> $product_info['viewed'],
							'top_bestsellers'	=> $top_bestsellers['total'],
							'minimum'     		=> ($product_info['minimum'] > 0) ? $product_info['minimum'] : 1,
							'price_value' 		=> $price_value,
							'special_value' 	=> $special_value,
							'product_id'  		=> $product_info['product_id'],
							'thumb'       		=> $image,
							'name'        		=> $product_info['name'],
							'description' 		=> utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
							'price'       		=> $price,
							'special'     		=> $special,
							'tax'         		=> $tax,
							'rating'      		=> $rating,
							'href'        		=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
						);
					}
				}
			}
		}

		$this->load->language('upstore/theme');

		$data['text_price_from'] = $this->language->get('text_price_from');
		$data['lang_id'] = $language_id = $this->config->get('config_language_id');

		$data['megasliders'] = array();


		$small_slider_info = $this->model_megasliderpro_megaslider->getSmallSlider($setting['banner']);

		$data['small_slider_status'] = false;

		if(!empty($small_slider_info)){
			if ($small_slider_info['status'] == 1 && !empty($small_slider_info['slider_items'])) {

				if (!empty($small_slider_info['status'])) {
					$data['small_slider_status'] = $small_slider_info['status'];
				} else {
					$data['small_slider_status'] = false;
				}

				if (!empty($small_slider_info['width'])) {
					$data['small_slider_width'] = $small_slider_info['width'];
				} else {
					$data['small_slider_width'] = 348;
				}

				if (!empty($small_slider_info['height'])) {
					$data['small_slider_height'] = $small_slider_info['height'];
				} else {
					$data['small_slider_height'] = 464;
				}

				if (!empty($small_slider_info['status_autoplay'])) {
					$data['small_slider_status_autoplay'] = $small_slider_info['status_autoplay'];
				} else {
					$data['small_slider_status_autoplay'] = 0;
				}

				if (!empty($small_slider_info['autoplay_delay'])) {
					$data['small_slider_autoplay_delay'] = $small_slider_info['autoplay_delay'];
				} else {
					$data['small_slider_autoplay_delay'] = 5000;
				}

				if (!empty($small_slider_info['pagination'])) {
					$data['small_slider_pagination'] = $small_slider_info['pagination'];
				} else {
					$data['small_slider_pagination'] = 0;
				}

				if (!empty($small_slider_info['navigation'])) {
					$data['small_slider_navigation'] = $small_slider_info['navigation'];
				} else {
					$data['small_slider_navigation'] = 0;
				}

				if (!empty($small_slider_info['position'])) {
					$data['small_slider_position'] = $small_slider_info['position'];
				} else {
					$data['small_slider_position'] = 'right';
				}

				if (!empty($small_slider_info['slider_items'])) {
					$small_slider_items = json_decode($small_slider_info['slider_items'], true);
				} else {
					$small_slider_items = [];
				}

				if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
					$dir_image = $this->config->get('config_ssl') . 'image/';
				} else {
					$dir_image = $this->config->get('config_url') . 'image/';
				}

				$count_promo_slider = 0;

				if(!empty($small_slider_items)){
					foreach($small_slider_items as $small_slider_item){

						if (!empty($small_slider_item['price'][$language_id])) {
							$promo_slider_price = $this->currency->format((float)$small_slider_item['price'][$language_id], $this->session->data['currency']);
						} else {
							$promo_slider_price = false;
						}

						if (is_file(DIR_IMAGE . $small_slider_item['image'][$language_id])) {
							//$image = $dir_image . $small_slider_item['image'][$language_id];
							$image = $this->model_tool_image->resize($small_slider_item['image'][$language_id], round($data['small_slider_width']*1.5), round($data['small_slider_height']*1.5));
						} else {
							continue;
						}

						$count_promo_slider++;

						$data['small_slider_items'][] = [
							'color_title_xs' => isset($small_slider_item['color_title_xs'][$language_id]) ? $small_slider_item['color_title_xs'][$language_id] : '',
							'title_xs' => isset($small_slider_item['title_xs'][$language_id]) ? html_entity_decode($small_slider_item['title_xs'][$language_id], ENT_QUOTES, 'UTF-8') : '',
							'color_title_lg' => isset($small_slider_item['color_title_lg'][$language_id]) ? $small_slider_item['color_title_lg'][$language_id] : '',
							'title_lg' => isset($small_slider_item['title_lg'][$language_id]) ? html_entity_decode($small_slider_item['title_lg'][$language_id], ENT_QUOTES, 'UTF-8') : '',
							'color_price_from' => isset($small_slider_item['color_price_from'][$language_id]) ? $small_slider_item['color_price_from'][$language_id] : '',
							'text_price_from' => isset($small_slider_item['text_price_from'][$language_id]) ? $small_slider_item['text_price_from'][$language_id] : '',
							'color_price' => isset($small_slider_item['color_price'][$language_id]) ? $small_slider_item['color_price'][$language_id] : '',
							'link' => isset($small_slider_item['link'][$language_id]) ? $small_slider_item['link'][$language_id] : '',
							'bg_block' => isset($small_slider_item['bg_block'][$language_id]) ? $small_slider_item['bg_block'][$language_id] : '',
							'sort_order' => isset($small_slider_item['sort_order']) ? $small_slider_item['sort_order'] : 0,
							'image' => $image,
							'image_display_mode' => (!empty($small_slider_item['image_display_mode'][$language_id])) ? $small_slider_item['image_display_mode'][$language_id] : 'cover',
							'price' => $promo_slider_price,
						];
					}
				}

				if($count_promo_slider == 1){
					$data['small_slider_pagination'] = false;
					$data['small_slider_navigation'] = false;
				}

				if (!empty($data['small_slider_items'])){
					foreach ($data['small_slider_items'] as $key => $value) {
						$sort[$key] = $value['sort_order'];
					}
					array_multisort($sort, SORT_ASC, $data['small_slider_items']);
				}
			}
		}

		if(empty($data['small_slider_items'])){
			$data['small_slider_status'] = false;
		}

		$results = $this->model_megasliderpro_megaslider->getChmSlider($setting['banner']);

		$data['width'] = $setting['width'];
		$data['height'] = $setting['height'];

		if($results) {
			$count_ms = 0;
			foreach ($results as $result) {
				$price_ms_banner = isset($result['price']) ? $result['price'] : '';

				if (!empty($price_ms_banner) && ($price_ms_banner != 0)) {
					$price = $this->currency->format($price_ms_banner, $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_file(DIR_IMAGE . $result['image'])) {
					continue;
				}

				$count_ms++;

				$data['megasliders'][] = array(
					'price' 				=> $price,
					'price_color' 		=> $result['price_color'],
					'color_price_from'=> $result['color_price_from'],
					'text_price_from' => $result['text_price_from'],
					'title' 				=> $result['title'],
					'color_title' 		=> ($result['color_title'] ? $result['color_title'] : ''),
					'color_sub_title' => ($result['color_sub_title'] ? $result['color_sub_title'] : ''),
					'sub_title' 		=> (!empty(strip_tags(html_entity_decode($result['sub_title'], ENT_QUOTES, 'UTF-8')))) ? html_entity_decode($result['sub_title'], ENT_QUOTES, 'UTF-8') : '',
					'link'  				=> $result['link'],
					'image' 				=> $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']),
				);
			}

			if($count_ms == 1){
				$data['info_module']['nextback'] = false;
				$data['info_module']['contrl'] = false;
			}

			$data['module'] = $module++;
			return $this->load->view('extension/module/upstore_megasliderpro', $data);
		}
	}

}