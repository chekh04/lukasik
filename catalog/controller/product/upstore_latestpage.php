<?php
class ControllerProductUpstoreLatestpage extends Controller {

	public function index() {

		$data['bg_image'] = ($this->config->has('bg_product_image') && $this->config->get('bg_product_image') == 1) ? true : false;
		$data['ch_type_btn'] = ($this->config->has('ch_type_btn') && $this->config->get('ch_type_btn') == 1) ? 'rounded' : 'squircle';
		$data['us_setting_pages'] = $this->config->get('us_setting_pages') ? $this->config->get('us_setting_pages') : [];

		$custom_sort_data = $this->config->get('custom_sort_data');

		$this->load->model('upstore/theme');
		$this->load->model('extension/module/upstore_pro_sticker');

		$data['display_view'] = isset($this->session->data['display']) ? $this->session->data['display'] : 'grid';
		$data['setting_lp'] = $this->config->get('setting_lp');

		$this->load->language('upstore/theme');
		$this->load->language('product/product');
		$this->load->language('product/upstore_latestpage');

		$this->load->model('catalog/product');
		$this->load->model('catalog/upstore_latestpage');
		$this->load->model('tool/image');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/theme/upstore/js/showmore.js');

		if(isset($data['setting_lp']['status_latest_page']) && ($data['setting_lp']['status_latest_page'] == 1)){
			$status_active_last_date = (!empty($data['setting_lp']['status_active_last_date']) ? 1 : 0);

			$data['heading_title'] = $this->language->get('heading_title');

			$dop_image = true;
			if(deviceType == 'phone') {$dop_image = false;}
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_refine'] = $this->language->get('text_refine');
			$data['text_quantity'] = $this->language->get('text_quantity');
			$data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$data['text_model'] = $this->language->get('text_model');
			$data['text_price'] = $this->language->get('text_price');
			$data['text_tax'] = $this->language->get('text_tax');
			$data['text_points'] = $this->language->get('text_points');
			$data['text_sort'] = $this->language->get('text_sort');
			$data['text_limit'] = $this->language->get('text_limit');
			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_list'] = $this->language->get('button_list');
			$data['button_grid'] = $this->language->get('button_grid');

			$data['lang_id'] = $this->config->get('config_language_id');
			$data['text_instock'] = $this->language->get('text_instock');
			$data['config_on_off_category_page_quickview'] = $this->config->get('config_on_off_category_page_quickview');

			$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');
			$data['text_reviews_title'] = $this->language->get('text_reviews_title');
			$data['button_price'] = $this->language->get('button_price');
			$data['config_quickview_btn_name'] = $this->config->get('config_quickview_btn_name');
			$data['show_stock_status'] = (!empty($this->config->get('config_show_stock_status')) ? 1 : 0);

			$config_disable_cart_button_text = $this->config->get('config_disable_cart_button_text');
			if(!empty($config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'])){
				$data['disable_cart_button_text'] = $config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'];
			} else {
				$data['disable_cart_button_text'] = $this->language->get('disable_cart_button_text');
			}

			$data['nst_data'] = $this->config->get('nst_data');
			if(isset($data['nst_data']['lazyload_page']) && ($data['nst_data']['lazyload_page'] == 1)){
				$data['lazyload_page'] = true;
				if (isset($data['nst_data']['lazyload_image']) && ($data['nst_data']['lazyload_image'] !='')) {
					$data['lazy_image'] = 'image/' . $data['nst_data']['lazyload_image'];
				} else {
					$data['lazy_image'] = 'image/catalog/lazyload/lazyload.jpg';
				}
			} else {
				$data['lazyload_page'] = false;
			}
			$data['text_home_ns'] = $this->language->get('text_home_ns');
			$data['text_select'] = $this->language->get('text_select');
			$data['config_additional_settings_upstore'] = $this->config->get('config_additional_settings_upstore');
			$data['required_text_option'] = $this->config->get('required_text_option');
			$data['change_text_cart_button_out_of_stock'] = (!empty($this->config->get('config_change_text_cart_button_out_of_stock')) ? 1 : 0);
			$data['show_special_timer_page'] = (!empty($this->config->get('config_show_special_timer_page')) ? 1 : 0);
			$data['disable_cart_button'] = (!empty($this->config->get('config_disable_cart_button')) ? 1 : 0);
			$data['disable_fastorder_button'] = (!empty($this->config->get('config_disable_fastorder_button')) ? 1 : 0);
			$data['on_off_sticker_special'] = (!empty($this->config->get('on_off_sticker_special')) ? 1 : 0);
			$data['on_off_percent_discount'] = $this->config->get('on_off_percent_discount');
			$data['config_change_icon_sticker_special'] = $this->config->get('config_change_icon_sticker_special');
			$data['on_off_sticker_topbestseller'] = (!empty($this->config->get('on_off_sticker_topbestseller')) ? 1 : 0);
			$data['config_limit_order_product_topbestseller'] = $this->config->get('config_limit_order_product_topbestseller');
			$data['config_change_icon_sticker_topbestseller'] = $this->config->get('config_change_icon_sticker_topbestseller');
			$data['on_off_sticker_popular'] = (!empty($this->config->get('on_off_sticker_popular')) ? 1 : 0);
			$data['config_min_quantity_popular'] = $this->config->get('config_min_quantity_popular');
			$data['config_change_icon_sticker_popular'] = $this->config->get('config_change_icon_sticker_popular');
			$data['on_off_sticker_newproduct'] = (!empty($this->config->get('on_off_sticker_newproduct')) ? 1 : 0);
			$data['config_limit_day_newproduct'] = $this->config->get('config_limit_day_newproduct');
			$data['config_change_icon_sticker_newproduct'] = $this->config->get('config_change_icon_sticker_newproduct');
			$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special');
			$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
			$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
			$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');

			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'p.date_added';
			}

			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'DESC';
			}

			if (isset($this->request->get['date_ave'])) {
				$date_ave = $this->request->get['date_ave'];
				$data['date_ave'] = $this->request->get['date_ave'];
			} else {
				$date_ave = null;
				$data['date_ave'] = false;
			}

			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$active_last_date = '';

			$data['date_date_added'] = array();

			if(isset($data['setting_lp']['status_receipt_date']) && ($data['setting_lp']['status_receipt_date'] == 1)){
				$date_availeble = $this->model_catalog_upstore_latestpage->getDateAvailable();
				if($date_availeble){
					foreach($date_availeble as $result_date){
						$data['date_availeble'][] = array(
							'text'  => $result_date['date_added'],
							'href'  => $this->url->link('product/upstore_latestpage', 'date_ave=' . $result_date['date_added'] . $url)
						);
					}
					if(isset($status_active_last_date) && ($status_active_last_date == 1)){
						$active_last_date_data = array_values($data['date_availeble'])[0];
						$active_last_date = $active_last_date_data['text'];
					}
				}
			}

			if (isset($this->request->get['date_ave'])) {
				$url .= '&date_ave=' . $this->request->get['date_ave'];
			} elseif(isset($status_active_last_date) && ($status_active_last_date == 1)){
				$url .= '&date_ave=' . $active_last_date;
				$data['date_ave'] = $active_last_date;
				$date_ave = $active_last_date;
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('product/upstore_latestpage', $url)
			);

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

			$filter_data = array(
				'sort'  		=> $sort,
				'date_ave'  => $date_ave,
				'order' 		=> $order,
				'start' 		=> ((int)$page - 1) * (int)$limit,
				'limit' 		=> (int)$limit,

			);

			$product_total = $this->model_catalog_upstore_latestpage->getTotalProducts($filter_data);

			$results = $this->model_catalog_upstore_latestpage->getLatest($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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

				$image_h = false;
				$image_hm = array();

				if(isset($data['setting_lp']['image_hover']) && ($data['setting_lp']['image_hover'] > 0) && $dop_image){
					$results_img = $this->model_catalog_product->getProductImages($result['product_id']);
					if($data['setting_lp']['image_hover'] == 1){
						foreach ($results_img as $key => $result_img) {
							if($key == 1){
								break;
							}
							$image_h = $this->model_tool_image->resize($result_img['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
						}
					} else {
						foreach ($results_img as $key => $img) {
							if($key == 6){
								break;
							}
							$image_hm[] = $this->model_tool_image->resize($img['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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

				if ((float)$result['special']) {
					$special_date_end = $this->model_catalog_product->getDateEnd($result['product_id']);
				} else {
					$special_date_end = false;
				}

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
					'width' 					=> $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
					'height' 				=> $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'),
					'image_h'				=> $image_h,
					'image_hm'				=> $image_hm,
					'sticker_new_prod'	=> $sticker_new_prod,
					'date_end'				=> $special_date_end,
					'reviews'      		=> (int)$result['reviews'],
					'price_value' 			=> $price_value,
					'special_value' 		=> $special_value,
					'product_quantity' 	=> $product_quantity,
					'stock_status' 		=> $stock_status,
					'skidka'     			=> round((float)$skidka),
					'model'     			=> $result['model'],
					'date_available'		=> $result['date_available'],
					'viewed'	 				=> $result['viewed'],
					'top_bestsellers'		=> $top_bestsellers['total'],
					'product_id'			=> $result['product_id'],
					'thumb'					=> $image,
					'name'					=> $result['name'],
					'description' 			=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'      			=> $price,
					'special'     			=> $special,
					'tax'         			=> $tax,
					'minimum'     			=> $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      			=> $result['rating'],
					'href'        			=> $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			if (isset($this->request->get['date_ave'])) {
				$url .= '&date_ave=' . $this->request->get['date_ave'];
			} elseif(isset($status_active_last_date) && ($status_active_last_date == 1)){
				$url .= '&date_ave=' . $active_last_date;
			}

			$data['sorts'] = array();

			if (!empty($custom_sort_data['sort'])) {
				$lang_id = $this->config->get('config_language_id');

				foreach ($custom_sort_data['sort'] as $item_sort) {
					if (!empty($item_sort['value']) && !empty($item_sort['show'])) {
						$value_sort = $item_sort['value'];
						$sort_order = explode('-', $value_sort);
						$sort_name = strtolower(preg_replace('/^.*?\./', '', str_replace('-', '_', $value_sort)));

						if (!empty($item_sort['custom_text'][$lang_id])) {
							$sorts_text = $item_sort['custom_text'][$lang_id];
						} else {
							$sorts_text = $this->language->get('text_' . $sort_name);
						}

						$data['sorts'][] = array(
							'text'  => $sorts_text,
							'value' => $value_sort,
							'href'  => $this->url->link('product/upstore_latestpage', '&sort=' . $sort_order[0] . '&order=' . $sort_order[1] . $url)
						);
					}
				}
			}

			$url = '';

			if (isset($this->request->get['date_ave'])) {
				$url .= '&date_ave=' . $this->request->get['date_ave'];
			} elseif(isset($status_active_last_date) && ($status_active_last_date == 1)){
				$url .= '&date_ave=' . $active_last_date;
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/upstore_latestpage', $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['date_ave'])) {
				$url .= '&date_ave=' . $this->request->get['date_ave'];
			} elseif(isset($status_active_last_date) && ($status_active_last_date == 1)){
				$url .= '&date_ave=' . $active_last_date;
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/upstore_latestpage', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			if ($page == 1) {
				$this->document->addLink($this->url->link('product/upstore_latestpage', '', true), 'canonical');
			} elseif ($page == 2) {
				$this->document->addLink($this->url->link('product/upstore_latestpage', '', true), 'prev');
			} else {
				$this->document->addLink($this->url->link('product/upstore_latestpage', 'page='. ($page - 1), true), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
				$this->document->addLink($this->url->link('product/upstore_latestpage', 'page='. ($page + 1), true), 'next');
			}

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/upstore_latestpage', $data));
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
