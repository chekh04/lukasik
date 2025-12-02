<?php
class ControllerExtensionModuleUpstoreReviewscustomer extends Controller {
	public function getNextPage() {
		if (isset($this->request->post['module_id']) && (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
			$this->load->model('setting/module');
			$module_id = (int)$this->request->post['module_id'];
			$setting = $this->model_setting_module->getModule($module_id);
			$setting['page'] = (int)$this->request->post['page'];
			$setting['module'] = (int)$this->request->post['module'];
			$setting['category_id'] = (int)$this->request->post['category_id'];
			$this->response->setOutput($this->index($setting));
		}
	}

	public function index($setting) {

		$data['module_id'] = $setting['module_id'];

		$data['nst_data'] = $this->config->get('nst_data');

		if(isset($data['nst_data']['lazyload_module']) && ($data['nst_data']['lazyload_module'] == 1)){
			$data['lazyload_module'] = true;
			if (isset($data['nst_data']['lazyload_image']) && ($data['nst_data']['lazyload_image'] !='')) {
				$data['lazy_image'] = 'image/' . $data['nst_data']['lazyload_image'];
			} else {
				$data['lazy_image'] = 'image/catalog/lazyload/lazyload1px.png';
			}
		} else {
			$data['lazyload_module'] = false;
		}

		if(isset($setting['reviewscustomer'])){
			$reviewscustomer = $setting['reviewscustomer'];
			unset($setting['reviewscustomer']);
			$setting = array_merge($setting, $reviewscustomer);
		}

		static $module = 0;

		if(deviceType == 'phone') {
			$setting['limit'] = $setting['limit_mob'];
		}

		if(deviceType == 'tablet') {
			$setting['limit'] = $setting['limit_tablet'];
		}

		if(deviceType == 'computer') {
			$setting['limit'] = $setting['limit'];
		}

		$this->load->language('extension/module/upstore_reviewscustomer');
		$this->load->language('upstore/theme');

		$this->load->model('upstore/theme');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('catalog/upstore_reviewscustomer');

		  //$data['module'] = 'reviews';
		if(!isset($this->request->post['module'])) {
			$data['module_header'] = $setting['module_header'][$this->config->get('config_language_id')];
		}

		$data['reviews'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 3;
		}

		$data['status_showmore'] = isset($setting['status_showmore']) ? $setting['status_showmore'] : 0;
		$limit_max = isset($setting['limit_max']) ? $setting['limit_max'] : 12;

		if (isset($setting['category_sensitive']) && !empty($this->request->get['path'])){
			$categories = explode('_', $this->request->get['path']);
			$category_id = (int)array_pop($categories);
		} else {
			$category_id = 0;
		}

		$data['category_id'] = $category_id;

		if(isset($setting['category_id'])) {
			$category_id = (int)$setting['category_id'];
		}

		if(isset($setting['page'])) {
			$page = (int)$setting['page'];
		} else {
			$page = 1;
		}

		$filter_data = array(
			'category_id' => $category_id,
			'start' => ($page - 1) * $setting['limit'],
			'limit' => $setting['limit'],
			'limit_max' => $limit_max
		);

		$reviews_total = $this->model_catalog_upstore_reviewscustomer->getTotalReviews($filter_data);

		if ($reviews_total > $limit_max) {
			$reviews_total = $limit_max;
		}

		if ($setting['order_type'] == 'last') {
			$results = $this->model_catalog_upstore_reviewscustomer->getLatestCustomerReviews($filter_data);
		} else {
			$results = $this->model_catalog_upstore_reviewscustomer->getRandomCustomerReviews($filter_data);
		}

		foreach ($results as $result) {
			if ($result['image']) {
				$thumb = $this->model_tool_image->resize($result['image'], 80, 80);
			} else {
				$thumb = $this->model_tool_image->resize('placeholder.png', 80, 80);
			}

			$data['reviews'][] = array(
				'width'         => 80,
				'height'        => 80,
				'product_id'    => $result['product_id'],
				'plus'          => $result['plus'],
				'minus'         => $result['minus'],
				'prod_thumb'    => $thumb,
				'prod_name'     => $result['name'],
				'review_id'     => $result['review_id'],
				'rating'        => $result['rating'],
				'description'   => utf8_substr(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8')), 0, 300),
				'date_added'    => $this->model_upstore_theme->lang_date('j F, Y', strtotime($result['date_added'])),
				'author'        => $result['author'],
				'first_letter'  => utf8_substr($result['author'], 0, 1),
				'href'          => $this->url->link('product/product', 'product_id=' . $result['product_id'])
			);
		}

		$data['link_all_reviews'] = $this->url->link('product/upstore_reviewscustomer');
		$data['show_all_button'] = '';

		if(isset($setting['show_all_button'])) {
			$data['show_all_button'] = $setting['show_all_button'];
		}

		$data['last_page'] = ceil($reviews_total / $setting['limit']);

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
			$data['module'] = $module++;
		}

		if($data['reviews']){
			return $this->load->view('extension/module/upstore_reviewscustomer', $data);
		}
	}
}