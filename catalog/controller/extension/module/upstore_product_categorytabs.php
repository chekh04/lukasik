<?php
class ControllerExtensionModuleUpstoreProductCategorytabs extends Controller {
	public function index($setting) {
		static $module = 0;
		$this->upstore_minifier->addScript('catalog/view/theme/upstore/js/dragscroll.js', 'footer_onload');
		$this->load->model('extension/module/upstore_productcategorytabs');
		$data['ajaxurl'] = 'index.php?route=extension/module/upstore_product_categorytabs/ajaxLoadProduct';
		$data['categories'] = array();
		$filter_sub_category = isset($setting['filter_sub_category']) ? $setting['filter_sub_category'] : 0;
		$categories = isset($setting['category_sel']) ? $setting['category_sel'] : array();

		if(!empty($categories)){
			$category_info = $this->model_extension_module_upstore_productcategorytabs->getCategories($categories);
		}

		if (!empty($category_info)) {
			foreach ($category_info as $category) {
				if($filter_sub_category == 1){
					$cp2c = $this->model_extension_module_upstore_productcategorytabs->checkSubCategories($category['category_id']);
				} else {
					$cp2c = $this->model_extension_module_upstore_productcategorytabs->checkCategory($category['category_id']);
				}

				if($cp2c == 1){
					$data['categories'][] = array(
						'category_id'  => $category['category_id'],
						'name'        => $category['name'],
					);
				}
			}

			$data['module_id'] = $setting['module_id'];
			$data['module'] = $module++;
			return $this->load->view('extension/module/upstore_product_categorytabs', $data);
		}
	}

	public function ajaxLoadProduct() {
		if (isset($this->request->post['module_id']) && (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {

			$this->load->model('setting/module');
			$this->load->model('upstore/theme');
			$this->load->model('extension/module/upstore_pro_sticker');

			$data['bg_image'] = ($this->config->has('bg_product_image') && $this->config->get('bg_product_image') == 1) ? true : false;
			$data['ch_type_btn'] = ($this->config->has('ch_type_btn') && $this->config->get('ch_type_btn') == 1) ? 'rounded' : 'squircle';

			$module_id = (int)$this->request->post['module_id'];
			$setting = $this->model_setting_module->getModule($module_id);
			$data['module_id'] = $module_id;
			$setting['module'] = (int)$this->request->post['module'];
			if(isset($this->request->post['page'])) {
				$setting['page'] = (int)$this->request->post['page'];
			}

			$limit_max = isset($setting['limit_max']) ? (int)$setting['limit_max'] : 10;
			$data['status_showmore'] = isset($setting['status_showmore']) ? (int)$setting['status_showmore'] : 0;

			$setting['filter_sub_category'] = isset($setting['filter_sub_category']) ? (int)$setting['filter_sub_category'] : 0;

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

			$this->load->language('upstore/theme');
			$this->load->language('product/product');

			$this->load->model('extension/module/upstore_productcategorytabs');
			$this->load->model('catalog/product');
			$this->load->model('tool/image');


			$data['lang_id'] = $this->config->get('config_language_id');
			$data['setting_module'] = $this->config->get('setting_module');

			$data['config_on_off_latest_quickview'] = $this->config->get('config_on_off_latest_quickview');
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

			$data['change_text_cart_button_out_of_stock'] = $this->config->get('config_change_text_cart_button_out_of_stock');
			$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special'); //added
			$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
			$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
			$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');

			$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');
			$data['config_quickview_btn_name'] = $this->config->get('config_quickview_btn_name');
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

			if ($setting['filter_sub_category'] == 1) {
				$filter_sub_category = (int)$setting['filter_sub_category'];
			} else {
				$filter_sub_category = null;
			}

			if (isset($this->request->post['category_id'])) {
				$filter_category_id = (int)$this->request->post['category_id'];
				$data['idCategory'] = (int)$this->request->post['category_id'];
			} else {
				$filter_category_id = null;
				$data['idCategory'] = null;
			}

			if(isset($setting['page'])) {
				$page = (int)$setting['page'];
			} else {
				$page = 1;
			}

			$dop_image = true;
			if(deviceType == 'phone') {
				$dop_image = false;
				$setting['limit'] = $setting['limit_mob'];
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

			if(deviceType == 'tablet') {
				$setting['limit'] = $setting['limit_tablet'];
			}
			if(deviceType == 'computer') {
				$setting['limit'] = $setting['limit'];
			}

			$filter_data = array(
				'filter_category_id' => (int)$filter_category_id,
				'filter_sub_category' => (int)$filter_sub_category,
				'filter_hide_out_of_stock' => !empty($setting['hide_out_of_stock_products']) ? $setting['hide_out_of_stock_products'] : false,
				'start' => ((int)$page - 1) * (int)$setting['limit'],
				'limit' => (int)$setting['limit'],
				'sort'  => !empty($setting['sort']) ? $setting['sort'] : 'pd.name',
				'order' => !empty($setting['order']) ? $setting['order'] : 'ASC',
				'limit_max' => (int)$limit_max
			);

			$product_total = $this->model_extension_module_upstore_productcategorytabs->getTotalProducts($filter_data);

			if ($product_total > $limit_max) {
				$product_total = $limit_max;
			}

			$results = $this->model_extension_module_upstore_productcategorytabs->getProductToCategoryTabSelect($filter_data);

			if ($results) {
				foreach ($results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
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

					if ((float)$result['special']) {
						$special_date_end = $this->model_catalog_product->getDateEnd($result['product_id']);
					} else {
						$special_date_end = false;
					}

					$image_h = false;
					$image_hm = array();

					if(isset($data['setting_module']['image_hover']) && ($data['setting_module']['image_hover'] > 0) && $dop_image){
						$results_img = $this->model_catalog_product->getProductImages($result['product_id']);
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

					if ((float)$result['special']) {
						$price2 = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
						$special2 = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
						$skidka = $special2/($price2/100)-100;
					} else {
						$skidka = "";
					}

					$top_bestsellers = $this->model_catalog_product->getTopSeller($result['product_id']);
					$product_quantity = $result['quantity'];
					$stock_status = $result['stock_status'];
					if((isset($result['date_available'])&&(round((strtotime(date("Y-m-d"))-strtotime($result['date_available']))/86400))<=$this->config->get('config_limit_day_newproduct'))) {
						$sticker_new_prod = true;
					} else {
						$sticker_new_prod = false;
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

					$data['products'][] = array(
						'show_buy_button' 	=> $show_buy_button,
						'in_waitlist' 			=> in_array($result['product_id'], $pids_in_waitlist) ? true : false,
						'pro_sticker'			=> $this->model_extension_module_upstore_pro_sticker->getProStickers($result['product_id'],'module_page'),
						'rating_stars'			=> $this->model_upstore_theme->productRatingStars($rating),
						'in_cart' 				=> in_array($result['product_id'], $pids_in_cart) ? true : false,
						'width'					=> $setting['width'],
						'height'					=> $setting['height'],
						'sticker_new_prod'	=> $sticker_new_prod,
						'product_quantity'	=> $product_quantity,
						'stock_status'			=> $stock_status,
						'image_h'				=> $image_h,
						'image_hm'				=> $image_hm,
						'reviews'    			=> sprintf((int)$result['reviews']),
						'skidka'     			=> round((float)$skidka),
						'model'     			=> $result['model'],
						'date_available'		=> $result['date_available'],
						'viewed'					=> $result['viewed'],
						'top_bestsellers'	 	=> $top_bestsellers['total'],
						'date_end'	 			=> $special_date_end,
						'minimum'     			=> ($result['minimum'] > 0) ? $result['minimum'] : 1,
						'price_value' 			=> $price_value,
						'special_value' 		=> $special_value,
						'product_id'			=> $result['product_id'],
						'thumb'					=> $image,
						'name'					=> $result['name'],
						'description' 			=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'					=> $price,
						'special'				=> $special,
						'tax'						=> $tax,
						'rating'					=> $rating,
						'href'					=> $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}

				$data['last_page'] = ceil($product_total / $setting['limit']);
				$data['nextPage'] = false;
				if ($page == 1) {
					if ($page == $data['last_page']) {
						$data['nextPage'] = false;
					} else {
						$data['nextPage'] = $page + 1;
					}
				} elseif ($page == $data['last_page']) {
					$data['nextPage'] = false;
				} else {
					$data['nextPage'] = $page +1;
				}

				if(isset($setting['module'])) {
					$data['module'] = (int)$setting['module'];
				} else {
					$data['module'] = 0;
				}

				$this->response->setOutput($this->load->view('extension/module/upstore_product_list', $data));
			}
		}
	}
}
