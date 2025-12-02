<?php
class ControllerUpstoreViewedProduct extends Controller {
	public function remove(){
		$json = array();
		if(isset($this->request->get['remove']) && (isset($this->request->cookie['productviewed']))){
			$products = array_merge(array_diff(explode(',', $this->request->cookie['productviewed']), array($this->request->get['remove'])));
			setcookie('productviewed', implode(',',$products), time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function quantityViewedProduct(){
		$quantity_viewed = 0;
		if (isset($this->request->cookie['productviewed'])) {
			$quantity_viewed = count(explode(',', $this->request->cookie['productviewed']));
		}
		$this->response->setOutput($quantity_viewed);
	}

	public function loadViewedProduct(){
		$this->response->setOutput($this->getViewedProduct($load_pc = true));
	}
	public function getViewedProduct($load_pc = false) {
		if (isset($this->request->cookie['productviewed'])) {
			$products = explode(',', $this->request->cookie['productviewed']);
		}
		if (!empty( $products)) {
			$this->load->language('product/product');
			$this->load->language('upstore/theme');
			$this->load->model('catalog/product');
			$this->load->model('tool/image');


			$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special');
			$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
			$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
			$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');
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

			$data['show_stock_status'] = $this->config->get('config_show_stock_status');
			$config_disable_cart_button_text = $this->config->get('config_disable_cart_button_text');
			if(!empty($config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'])){
				$data['disable_cart_button_text'] = $config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'];
			} else {
				$data['disable_cart_button_text'] = $this->language->get('disable_cart_button_text');
			}
			$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');
			$data['change_text_cart_button_out_of_stock'] = $this->config->get('config_change_text_cart_button_out_of_stock');
			$data['disable_cart_button'] = $this->config->get('config_disable_cart_button');
			$data['disable_fastorder_button'] = $this->config->get('config_disable_fastorder_button');
			$data['setting_module'] = $this->config->get('setting_module');
			$data['on_off_percent_discount'] = (!empty($this->config->get('on_off_percent_discount')) ? 1 : 0);
			$data['text_viewed'] = $this->language->get('text_viewed');
			$data['text_tax'] = $this->language->get('text_tax');
			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['button_remove'] = $this->language->get('button_remove');
			$data['lang_id'] = $this->config->get('config_language_id');
			$data['text_instock'] = $this->language->get('text_instock');
			$data['text_model'] = $this->language->get('text_model');

			$data['products'] = array();

			$products = array_slice($products, 0, 16);
			if (isset($load_pc) && ($load_pc== 1)) {
				$data['height'] = $height = 200; $data['width'] = $width = 200;
			} else {
				$height = 100; $width = 100;
			}

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

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $height, $width);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $height, $width);
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


					if ((float)$product_info['special']) {
						$special_date_end = $this->model_catalog_product->getDateEnd($product_info['product_id']);
					} else {
						$special_date_end = false;
					}
					$top_bestsellers = $this->model_catalog_product->getTopSeller($product_info['product_id']);

					$show_buy_button = true;

					if ($product_info['quantity'] <= 0 && $notify_stock_status) {
						$show_buy_button = false;
					}

					$data['products'][] = array(
						'show_buy_button' 	=> $show_buy_button,
						'in_waitlist' 			=> in_array($product_info['product_id'], $pids_in_waitlist) ? true : false,
						'in_cart' 				=> in_array($product_info['product_id'], $pids_in_cart) ? true : false,
						'product_quantity' 	=> $product_info['quantity'],
						'stock_status' 		=> $product_info['stock_status'],
						'model'     			=> $product_info['model'],
						'date_available'		=> $product_info['date_available'],
						'skidka'					=> $skidka,
						'date_end'	 			=> $special_date_end,
						'viewed'	 				=> $product_info['viewed'],
						'top_bestsellers'	=> $top_bestsellers['total'],
						'minimum'     		=> ($product_info['minimum'] > 0) ? $product_info['minimum'] : 1,
						'product_id'  		=> $product_info['product_id'],
						'thumb'       		=> $image,
						'name'        		=> $product_info['name'],
						'description' 		=> utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
						'price'       		=> $price,
						'special'     		=> $special,
						'tax'         		=> $tax,
						'remove'     		=> $this->url->link('upstore/viewed_product/remove', 'remove=' . $product_info['product_id']),
						'href'        		=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}

			if (isset($load_pc) && ($load_pc== 1)) {
				return $this->load->view('upstore/upstore_viewed_product_desktop', $data);
			}
			if (!empty($data['products'])) {
				return $this->response->setOutput($this->load->view('upstore/upstore_viewed_product_mobile', $data));
			}
		}

	}

}