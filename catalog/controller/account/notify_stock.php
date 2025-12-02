<?php
class ControllerAccountNotifyStock extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/notify_stock', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		if (!$this->config->get('upstore_notify_stock_status')) {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}

		$this->load->language('account/account');
		$this->load->language('extension/module/upstore_notify_stock');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/notify_stock', $url, true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_empty'] = $this->language->get('text_empty');

		$data['column_image'] = $this->language->get('column_image');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_status'] = $this->language->get('column_status');


		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_continue'] = $this->language->get('button_continue');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['waitlist_requests'] = array();

		$this->load->model('tool/image');
		$this->load->model('upstore/theme');
		$this->load->model('catalog/product');
		$this->load->model('extension/module/upstore_notify_stock');

		$notify_stock_total = $this->model_extension_module_upstore_notify_stock->getTotalWaitRequests();

		$results = $this->model_extension_module_upstore_notify_stock->getWaitRequests(($page - 1) * 10, 10);

		foreach ($results as $result) {
			if ($result['status_id'] == '0'){
				$status = $this->language->get('text_status_wait');
			}else{
				$status = $this->language->get('text_status_done');
			}

			$product_info = $this->model_catalog_product->getProduct($result['product_id']);

			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], 50, 50);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png',50, 50);
				}

				$data['waitlist_requests'][] = array(
					'product_id'		=> $product_info['product_id'],
					'notify_stock_id' => $result['notify_stock_id'],
					'product_name'		=> $product_info['name'],
					'product_model'	=> $product_info['model'],
					'product_image'	=> $image,
					'status'				=> $status,
					'date_added'  		=> $this->model_upstore_theme->lang_date('j F, Y', strtotime($result['date_added'])),
					'href'        		=> $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
				);
			}
		}

		$pagination = new Pagination();
		$pagination->total = $notify_stock_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/notify_stock', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($notify_stock_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($notify_stock_total - 10)) ? $notify_stock_total : ((($page - 1) * 10) + 10), $notify_stock_total, ceil($notify_stock_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/notify_stock_list', $data));
	}

	public function remove() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->load->model('extension/module/upstore_notify_stock');
			$this->load->language('extension/module/upstore_notify_stock');

			$json = array();

			if (!$this->customer->isLogged()) {
				$json['redirect'] = $this->url->link('account/login', '', true);
			} else {
				if (isset($this->request->post['notify_stock_id'])) {
					$notify_stock_id = (int)$this->request->post['notify_stock_id'];

					$result = $this->model_extension_module_upstore_notify_stock->removeNotifyRequest($notify_stock_id);

					if ($result) {
						$this->session->data['success'] = $this->language->get('text_success_remove_request');
						$json['success'] = $this->language->get('text_success_remove_request');
					} else {
						$json['error'] = $this->language->get('error_remove_request');
					}
				} else {
					$json['error'] = $this->language->get('error_remove_request');
				}
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

}