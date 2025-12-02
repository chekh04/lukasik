<?php
class ControllerExtensionModuleUpstoreLookbook extends Controller {
	public function index($setting) {

		static $module = 0;

		$this->document->addStyle('catalog/view/theme/upstore/stylesheet/lookbook.css');

		$quantity_column = isset($setting['quantity_column']) ? $setting['quantity_column'] : 4;

		if($quantity_column == 6){
			$data['col'] = 2;
		} elseif($quantity_column == 4){
			$data['col'] = 3;
		} else {
			$data['col'] = 4;
		}

		$lang_id = $this->config->get('config_language_id');
		$data['title'] = !empty($setting['title'][$lang_id]) ? $setting['title'][$lang_id] : '';

		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$data['items'] = [];

		$results = $setting['items'];

		foreach ($results as $result) {

			$image_width = (!empty($result['image_width']) ? $result['image_width'] : 470);
			$image_height = (!empty($result['image_height']) ? $result['image_height'] : 369);

			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], $image_width, $image_height);
			} else {
				$image = '';
			}

			if(!empty($image) && (!empty($result['point']))){

				$point = [];

				if(!empty($result['point'])){
					foreach($result['point'] as $point_info){
						$product_info = $this->model_catalog_product->getProduct($point_info['product_id']);
						if ($product_info) {
							$point[] = [
								'product_id' 	=> $product_info['product_id'],
								'left' 			=> $point_info['left'],
								'top' 			=> $point_info['top'],
							];
						}
					}
				}

				$data['items'][] = [
					'image_width' 		=> $image_width,
					'image_height' 	=> $image_height,
					'image'				=> $image,
					'point'				=> $point,
					'bg_block' 			=> isset($result['bg_block']) ? $result['bg_block'] : '',
					'bg_point'			=> isset($result['bg_point']) ? $result['bg_point'] : '',
				];
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/upstore_lookbook', $data);
	}

	public function getProductInfo() {
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->language('product/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
			$product_info = $this->model_catalog_product->getProduct($product_id);

			$notify_stock_status = !$this->config->get('config_stock_checkout') && ($this->config->get('upstore_notify_stock_status') == 1);
			$notify_stock_setting = $this->config->get('upstore_notify_stock_setting');
			$data['button_notify_stock'] = isset($notify_stock_setting['button_text'][$this->config->get('config_language_id')]) ? $notify_stock_setting['button_text'][$this->config->get('config_language_id')] : '';

			$pids_in_waitlist = [];
			if ($this->customer->isLogged() && $notify_stock_status) {
				$this->load->model('extension/module/upstore_notify_stock');
				$pids_in_waitlist = $this->model_extension_module_upstore_notify_stock->getProductsRequestsByCustomer();
			}

			if ($product_info) {
				$data['product_name'] = $product_info['name'];
				$data['href'] = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);

				$data['show_buy_button'] = true;

				if ($product_info['quantity'] <= 0 && $notify_stock_status) {
					$data['show_buy_button'] = false;
				}

				$data['in_waitlist']	= in_array($product_info['product_id'], $pids_in_waitlist) ? true : false;

				if ($product_info['image']) {
					$data['image'] = $this->model_tool_image->resize($product_info['image'], 120, 120);
				} else {
					$data['image'] = $this->model_tool_image->resize('placeholder.png', 120, 120);
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

				$data['button_cart'] = $this->language->get('button_cart');
				$data['product_id'] = $product_id;

				$this->response->setOutput($this->load->view('extension/module/upstore_lookbook_product', $data));
			}
		}
	}
}