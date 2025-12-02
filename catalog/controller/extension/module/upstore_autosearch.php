<?php
class ControllerExtensionModuleUpstoreAutosearch extends Controller {
	private $error = array();

	public function ajaxLiveSearch() {
		$json = array();

		if(!empty($this->request->get['filter_name'])){

			$this->load->model('extension/module/upstore_autosearch');
			$this->load->model('catalog/product');
			$this->load->model('upstore/theme');
			$this->load->model('tool/image');

			$this->load->language('product/product');
			$this->load->language('upstore/theme');

			$ns_autosearch_data = $this->config->get('ns_autosearch_data');

			$filter_manufacturer = ($ns_autosearch_data['search_manufacturer_on_off']=='1') ? true : false;
			$filter_upc = ($ns_autosearch_data['search_upc_on_off']=='1') ? true : false;
			$filter_sku = ($ns_autosearch_data['search_sku_on_off']=='1') ? true : false;
			$filter_model = ($ns_autosearch_data['search_model_on_off']=='1') ? true : false;
			$filter_tag = ($ns_autosearch_data['search_tag_on_off']=='1') ? true : false;

			$filterdata = array(
				'filter_name' 				=> $this->request->get['filter_name'],
				'filter_manufacturer'	=> $filter_manufacturer,
				'filter_upc' 				=> $filter_upc,
				'filter_sku' 				=> $filter_sku,
				'filter_model' 			=> $filter_model,
				'filter_tag' 				=> $filter_tag,
				'start' 						=> 0,
				'limit' 						=> 25,
			);

			$results = $this->model_extension_module_upstore_autosearch->ajaxLiveSearch($filterdata);

			$width = 50;
			$height = 50;

			if($ns_autosearch_data['image_search_width'] !='' && $ns_autosearch_data['image_search_height'] !=''){
				$width = $ns_autosearch_data['image_search_width'];
				$height = $ns_autosearch_data['image_search_height'];
			}

			$lang_id = $this->config->get('config_language_id');

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

			$pids_in_waitlist = [];
			if ($this->customer->isLogged() && $notify_stock_status) {
				$this->load->model('extension/module/upstore_notify_stock');
				$pids_in_waitlist = $this->model_extension_module_upstore_notify_stock->getProductsRequestsByCustomer();
			}

			$setting_module = $this->config->get('setting_module');

			$on_off_sticker_special = $this->config->get('on_off_sticker_special');
			$data_ts_s = $this->config->get('config_change_text_sticker_special');
			$text_sticker_special = (!empty($data_ts_s[$lang_id]['config_change_text_sticker_special'])) ? $data_ts_s[$lang_id]['config_change_text_sticker_special'] : '';

			$on_off_sticker_topbestseller = $this->config->get('on_off_sticker_topbestseller');
			$limit_bestseller = $this->config->get('config_limit_order_product_topbestseller');
			$data_ts_b = $this->config->get('config_change_text_sticker_topbestseller');
			$text_sticker_bestseller = (!empty($data_ts_b[$lang_id]['config_change_text_sticker_topbestseller'])) ? $data_ts_b[$lang_id]['config_change_text_sticker_topbestseller'] : '';

			$on_off_sticker_popular = $this->config->get('on_off_sticker_popular');
			$limit_popular = $this->config->get('config_min_quantity_popular');
			$data_ts_p = $this->config->get('config_change_text_sticker_popular');
			$text_sticker_popular = (!empty($data_ts_p[$lang_id]['config_change_text_sticker_popular'])) ? $data_ts_p[$lang_id]['config_change_text_sticker_popular'] : '';

			$on_off_sticker_newproduct = $this->config->get('on_off_sticker_newproduct');
			$data_ts_n = $this->config->get('config_change_text_sticker_newproduct');
			$text_sticker_newproduct = (!empty($data_ts_n[$lang_id]['config_change_text_sticker_newproduct'])) ? $data_ts_n[$lang_id]['config_change_text_sticker_newproduct'] : '';

			$show_special_timer_module = $this->config->get('config_show_special_timer_module');

			$config_quickview_btn_name = $this->config->get('config_quickview_btn_name');
			$text_quickview = (!empty($config_quickview_btn_name[$lang_id]['config_quickview_btn_name'])) ? $config_quickview_btn_name[$lang_id]['config_quickview_btn_name'] : '';

			$config_text_open_form_send_order = $this->config->get('config_text_open_form_send_order');
			$text_fastorder = (!empty($config_text_open_form_send_order[$lang_id]['config_text_open_form_send_order'])) ? $config_text_open_form_send_order[$lang_id]['config_text_open_form_send_order'] : '';
			$disable_fastorder_button = $this->config->get('config_disable_fastorder_button');

			$settings_upstore = $this->config->get('config_additional_settings_upstore');
			$on_off_percent_discount = (!empty($this->config->get('on_off_percent_discount')) ? 1 : 0);

			$disable_cart_button = $this->config->get('config_disable_cart_button');

			$product_details = (!empty($ns_autosearch_data['display_product_details_on_off']) ? $ns_autosearch_data['display_product_details_on_off'] : false);
			$display_categories = (!empty($ns_autosearch_data['display_categories_on_off']) ? $ns_autosearch_data['display_categories_on_off'] : false);

			$json['c'] = array();
			$json['text_categories'] = $this->language->get('text_categories');

			if(!empty($results['categories'])){
				$json['c'] = $results['categories'];
			}

			$json['p'] = array();

			foreach($results['products'] as $result){

				if(!empty($result['image'])&&file_exists(DIR_IMAGE .$result['image'])){
					$image = $this->model_tool_image->resize($result['image'],$width,$height);
				} else{
					$image = $this->model_tool_image->resize('no_image.png',$width,$height);
				}

				if(!empty($result['image']) && file_exists(DIR_IMAGE . $result['image'])){
					$thumb = $this->model_tool_image->resize($result['image'], 250, 250);
				} else{
					$thumb = $this->model_tool_image->resize('no_image.png', 250, 250);
				}

				if ($result['quantity'] <= 0) {
					$stock_result = $result['stock_status'];
				} else {
					$stock_result = $this->language->get('text_instock');
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$image_h = false;
				$image_hm = array();

				if(isset($setting_module['image_hover']) && ($setting_module['image_hover'] > 0)){
					$images = !empty($result['images']) ? explode(', ', $result['images']) : array();

					if($setting_module['image_hover'] == 1){
						foreach ($images as $key => $img) {
							if($key == 1){
								break;
							}
							$image_h = $this->model_tool_image->resize($img, 250, 250);
						}
					} else {
						foreach ($images as $key => $img) {
							if($key == 6){
								break;
							}
							$image_hm[] = $this->model_tool_image->resize($img, 250, 250);
						}
					}
				}

				$top_bestsellers = $this->model_catalog_product->getTopSeller($result['product_id']);

				if((isset($result['date_available'])&&(round((strtotime(date("Y-m-d"))-strtotime($result['date_available']))/86400))<=$this->config->get('config_limit_day_newproduct'))) {
					$sticker_new_prod = true;
				} else {
					$sticker_new_prod = false;
				}

				if ((float)$result['special']) {
					$special_date_end = $this->model_catalog_product->getDateEnd($result['product_id']);
				} else {
					$special_date_end = false;
				}

				if ((!$disable_fastorder_button && (($result['quantity'] <= 0) || $result['quantity'] > 0)) || ($disable_fastorder_button && $result['quantity'] > 0)){
					$show_fastorder = true;
				} else {
					$show_fastorder = false;
				}

				if (($result['quantity'] <= 0) && $disable_fastorder_button){
					$disabled_fastorder = 'disabled';
				} else {
					$disabled_fastorder = '';
				}

				if ((float)$result['special']) {
					$price2 = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					$special2 = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					$skidka = $special2/($price2/100)-100;
				} else {
					$skidka = "";
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price_value = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
				} else {
					$price_value = false;
				}

				if ((float)$result['special']) {
					$special_value = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
				} else {
					$special_value = false;
				}

				$show_buy_button = true;

				if ($result['quantity'] <= 0 && $notify_stock_status) {
					$show_buy_button = false;
				}

				$json['p'][] = array(
					'product_id' 			=> $result['product_id'],
					'name' 					=> $result['name'],
					'model' 					=> $result['model'],
					'quantity' 				=> $result['quantity'],
					'minimum'     			=> ($result['minimum'] > 0) ? $result['minimum'] : 1,
					'stock_status' 		=> $stock_result,
					'image' 					=> $image,
					'thumb' 					=> $thumb,
					'manufacturer' 		=> $result['manufacturer'],
					'price' 					=> $price,
					'special' 				=> $special,
					'price_value'			=> $price_value,
					'special_value'		=> $special_value,
					'tax' 					=> $tax,
					'rating' 				=> $rating,
					'rating_stars'			=> $this->model_upstore_theme->productRatingStars($rating),
					'reviews'    			=> (int)$result['reviews'],
					'href'        			=> $this->url->link('product/product', 'product_id=' . $result['product_id']),

					'image_h'				=> $image_h,
					'image_hm'				=> $image_hm,

					'lang_id'        		=> $lang_id,
					'in_cart' 				=> in_array($result['product_id'], $pids_in_cart) ? true : false,
					'setting_module' 		=> $setting_module,
					'on_off_sticker_special' => $on_off_sticker_special,
					'text_sticker_special'	=> $text_sticker_special,

					'on_off_sticker_topbestseller' => $on_off_sticker_topbestseller,
					'top_bestsellers' 	=> (int)$top_bestsellers,
					'limit_bestseller' 	=> (int)$limit_bestseller,
					'text_sticker_bestseller' 	=> $text_sticker_bestseller,

					'on_off_sticker_popular' => $on_off_sticker_popular,
					'viewed'					=> (int)$result['viewed'],
					'limit_popular'		=> (int)$limit_popular,
					'text_sticker_popular'=> $text_sticker_popular,

					'on_off_sticker_newproduct'=> $on_off_sticker_newproduct,
					'sticker_new_prod'	=> $sticker_new_prod,
					'text_sticker_newproduct'	=> $text_sticker_newproduct,

					'date_end'	 			=> $special_date_end,
					'show_special_timer_module' => $show_special_timer_module,

					'disabled_fastorder' => ($result['quantity'] <= 0 && $disable_fastorder_button) ? 'disabled' : '',
					'show_fastorder' 		=> $show_fastorder,

					'settings_upstore' => $settings_upstore,
					'on_off_percent_discount' => $on_off_percent_discount,
					'skidka' 				=> round((float)$skidka),
					'disable_cart_button'=> $disable_cart_button,
					'product_details'		=> $product_details,

					'show_buy_button' 	=> $show_buy_button,
					'in_waitlist' 			=> in_array($result['product_id'], $pids_in_waitlist) ? true : false,

					/*Text*/
					'text_model' 			=> $this->language->get('text_model'),
					'text_tax' 				=> $this->language->get('text_tax'),
					'button_cart' 			=> $this->language->get('button_cart'),
					'button_wishlist' 	=> $this->language->get('button_wishlist'),
					'button_compare' 		=> $this->language->get('button_compare'),
					'text_in_cart' 		=> $this->language->get('text_in_cart'),
					'text_instock' 		=> $this->language->get('text_instock'),
					'text_tax' 				=> $this->language->get('text_tax'),
					'text_quickview' 		=> $text_quickview,
					'text_fastorder' 		=> $text_fastorder,
					/*END Text*/

					'show_model'			=> ($ns_autosearch_data['display_model_on_off'] == '1') ? true : false,
					'display_stock_status'	=> ($ns_autosearch_data['display_stock_on_off'] == 1) ? true : false,
					'show_stock_status'	=> ($this->config->get('config_show_stock_status') == 1) ? true : false,
					'show_image'			=> ($ns_autosearch_data['display_image_on_off'] == '1') ? true : false,
					'show_manufacturer'	=> ($ns_autosearch_data['display_manufacturer_on_off'] == '1') ? true : false,
					'show_price'			=> ($ns_autosearch_data['display_price_on_off'] == '1') ? true : false,
					'show_rating'			=> ($ns_autosearch_data['display_rating_on_off'] == '1') ? true : false,

				);

			}
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>