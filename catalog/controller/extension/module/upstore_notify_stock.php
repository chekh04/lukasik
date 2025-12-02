<?php
class ControllerExtensionModuleUpstoreNotifyStock extends Controller {
	private $error = array();

	public function index() {

		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->load->model('catalog/product');
			$this->load->language('extension/module/upstore_notify_stock');

			$data['setting'] = $this->config->get('upstore_notify_stock_setting');

			$data['lang_id'] = $this->config->get('config_language_id');
			$data['button_send'] = $this->language->get('button_send');
			$data['text_placeholder_email'] = $this->language->get('text_placeholder_email');
			$data['name'] = ($this->customer->isLogged()) ? $this->customer->getFirstName() : '';
			$data['telephone'] = ($this->customer->isLogged()) ? $this->customer->getTelephone() : '';
			$data['email'] = ($this->customer->isLogged()) ? $this->customer->getEmail() : '';

			$agree_notify_stock_id = isset($data['setting']['agree_notify_stock_id']) ? $data['setting']['agree_notify_stock_id'] : 0;

			if ($agree_notify_stock_id) {
				$this->load->model('catalog/information');
				$this->load->language('upstore/theme');
				$information_info = $this->model_catalog_information->getInformation($agree_notify_stock_id);

				if ($information_info) {
					$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $agree_notify_stock_id, true), $information_info['title'], $information_info['title']);
				} else {
					$data['text_agree'] = '';
				}
			} else {
				$data['text_agree'] = '';
			}

			if (isset($this->request->get['product_id'])) {
				$data['product_id'] = $this->request->get['product_id'];
			} else {
				$data['product_id'] = '0';
			}

			$pids_in_waitlist = [];

			$data['in_waitlist'] = false;

			if ($this->customer->isLogged()) {
				$this->load->model('extension/module/upstore_notify_stock');
				$pids_in_waitlist = $this->model_extension_module_upstore_notify_stock->getProductsRequestsByCustomer();

				$in_waitlist = in_array($data['product_id'], $pids_in_waitlist) ? true : false;

				if($in_waitlist){
					$data['in_waitlist'] = true;
					$data['text_notify_stock_exists'] = $this->language->get('text_notify_stock_exists');
				}
			}

			if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
				if(isset($this->session->data['csrf_token'])){
					unset($this->session->data['csrf_token']);
				}

				if (function_exists('random_bytes')) {
					$this->session->data['csrf_token'] = bin2hex(random_bytes(32));
				} else {
					$this->session->data['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
				}
			}

			$data['csrf_token'] = $this->session->data['csrf_token'];

			$this->response->setOutput($this->load->view('extension/module/upstore_notify_stock', $data));
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

	public function confirm() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$json = [];

			$this->load->language('extension/module/upstore_notify_stock');
			$this->load->model('extension/module/upstore_notify_stock');
			$this->load->model('catalog/product');

			if (!$this->validateCsrfToken()) {
				$json['error']['csrf'] = $this->language->get('error_csrf');
			}

			if (!$json && !$this->validate()) {
				$json['error'] = $this->error;
			}

			$product_id = (int)($this->request->post['ns_product_id'] ?? 0);
			$product_info = $this->model_catalog_product->getProduct($product_id);
			$notify_stock_setting = $this->config->get('upstore_notify_stock_setting');

			if (!$product_info) {
				$json['error']['product'] = $this->language->get('error_product_not_found');
			}

			if (!$json && $this->model_extension_module_upstore_notify_stock->checkNotifyStock($product_id, $this->request->post['email'])) {
				$json['error']['exists'] = $this->language->get('error_notify_stock_exists');
			}

			if (!$json) {
				$data = [
					'name' 				=> $this->request->post['name'] ?? '',
					'telephone' 		=> $this->request->post['telephone'] ?? '',
					'email' 				=> $this->request->post['email'] ?? '',
					'ns_product_id'	=> $product_id,
					'product_name'		=> $product_info['name'],
					'product_link'		=> $this->url->link('product/product', 'product_id=' . $product_id)
				];

				$this->model_extension_module_upstore_notify_stock->saveNotifyStock($data);

				if ($notify_stock_setting['send_email_status']) {
					$this->sendMail($data);
				}

				unset($this->session->data['csrf_token']);
				$json['success'] = $this->language->get('text_success_send');
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

	private function validate() {
		$notify_stock_setting = $this->config->get('upstore_notify_stock_setting');

		if (isset($notify_stock_setting['name_field'], $notify_stock_setting['name_field_required']) &&
			$notify_stock_setting['name_field'] == 1 && $notify_stock_setting['name_field_required'] == 1) {
			if (utf8_strlen(trim($this->request->post['name'])) < 1 || utf8_strlen(trim($this->request->post['name'])) > 32) {
				$this->error['name'] = $this->language->get('error_name_field');
			}
		}

		if (isset($notify_stock_setting['telephone_field'], $notify_stock_setting['telephone_field_required']) &&
			$notify_stock_setting['telephone_field'] == 1 && $notify_stock_setting['telephone_field_required'] == 1) {
			if (utf8_strlen($this->request->post['telephone']) < 3 || utf8_strlen($this->request->post['telephone']) > 20) {
				$this->error['telephone'] = $this->language->get('error_telephone_field');
			}
		}

		if (utf8_strlen($this->request->post['email']) > 96 || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email_field');
		}

		$agree_notify_stock_id = isset($notify_stock_setting['agree_notify_stock_id']) ? $notify_stock_setting['agree_notify_stock_id'] : 0;

		if ($agree_notify_stock_id) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($agree_notify_stock_id);

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return !$this->error;
	}

	private function validateCsrfToken() {
		$token_csrf = $this->request->post['token_csrf'] ?? false;

		return isset($this->session->data['csrf_token']) && $token_csrf && $token_csrf === $this->session->data['csrf_token'];
	}

	private function sendMail($data){

		$setting = $this->config->get('upstore_notify_stock_setting');

		$data['customer_info'] = $data;

		$data['date_added'] 				= date('m/d/Y h:i:s a', time());
		$data['text_date_added'] 		= $this->language->get('text_date_added');
		$data['logo']						= $this->config->get('config_url').'image/'.$this->config->get('config_logo');
		$data['store_name']   			= $this->config->get('config_name');
		$data['store_url']    			= $this->config->get('config_url');
		$data['text_customer']   		= $this->language->get('text_customer');
		$data['text_name'] 				= $this->language->get('text_name');
		$data['text_telephone'] 		= $this->language->get('text_telephone');
		$data['text_email'] 				= $this->language->get('text_email');
		$data['text_link_product'] 	= $this->language->get('text_link_product');

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($setting['email_admin']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject($this->language->get('subject'));
		$mail->setHtml($this->load->view('mail/notify_stock_mail', $data));
		$mail->send();
	}

	public function cron(){

		$setting = $this->config->get('upstore_notify_stock_setting');

		if ($this->config->get('upstore_notify_stock_status') && isset($setting['cron_security_key'])) {

			$getSecurityKey = isset($this->request->get['cron_security_key']) ? $this->request->get['cron_security_key'] : '';

			if ($getSecurityKey === $setting['cron_security_key']) {

				$this->load->model('tool/image');
				$this->load->model('extension/module/upstore_notify_stock');
				$this->load->language('extension/module/upstore_notify_stock');

				$products = $this->model_extension_module_upstore_notify_stock->getAvailableProducts();

				if(!empty($products)){
					foreach($products as $product_info){
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], 100, 100);
						} else {
							$image = false;
						}

						$customers = $this->model_extension_module_upstore_notify_stock->getCustomersByProductId($product_info['product_id']);

						if(!empty($customers)){
							foreach($customers as $customer_info){
								$data['store_name']				= $this->config->get('config_name');
								$data['product_name'] 			= $product_info['name'];
								$data['product_link'] 			= $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
								$data['product_image'] 			= $image;
								$data['product_model'] 			= $product_info['model'];
								$data['customer_name'] 			= $customer_info['name'];
								$data['customer_email'] 		= $customer_info['email'];
								$data['customer_telephone'] 	= $customer_info['telephone'];

								if (isset($setting['customer_email_status']) && $setting['customer_email_status'] == 1) {
									$data['subject']  = $this->convertHtmlCustomFields($data, $setting['customer_email_subject'][$customer_info['language_id']]);
									$data['html']  = $this->convertHtmlCustomFields($data, $setting['customer_email_html'][$customer_info['language_id']]);
								} else {
									$data['subject']  = sprintf($this->language->get('text_mail_subject'), $data['store_name'], $data['product_name']);
									$data['html']  =  sprintf($this->language->get('text_mail_html'), $data['customer_name'], $data['product_name'], $data['store_name'], $data['product_link'], $data['store_name']);
								}

								$this->sendMailCustomer($data);
								$this->model_extension_module_upstore_notify_stock->updateStatusNotifyStock($customer_info['notify_stock_id']);
							}
						}
					}
				}
			} else {
				$this->response->redirect($this->url->link('error/not_found', '', true));
			}
		}
	}

	private function sendMailCustomer($data) {
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($data['customer_email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($data['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($data['subject'], ENT_QUOTES, 'UTF-8'));
		$mail->setHtml(html_entity_decode($data['html'], ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}

	private function convertHtmlCustomFields($data, $html) {

		$replace_map = [
			'~store_name~'       	=> isset($data['store_name']) ? $data['store_name'] : '',
			'~customer_name~'    	=> isset($data['customer_name']) ? $data['customer_name'] : '',
			'~customer_telephone~' 	=> isset($data['customer_telephone']) ? $data['customer_telephone'] : '',
			'~customer_email~'   	=> isset($data['customer_email']) ? $data['customer_email'] : '',
			'~product_name~'     	=> isset($data['product_name']) ? $data['product_name'] : '',
			'~product_link~'     	=> isset($data['product_link']) ? $data['product_link'] : '',
			'~product_image~'    	=> isset($data['product_image']) ? '<img src="'. $data['product_image'] .'" alt="'. $data['product_name'] .'">' : '',
			'~product_model~'    	=> isset($data['product_model']) ? $data['product_model'] : '',
		];

		foreach ($replace_map as $variable => $value) {
			$html = str_replace($variable, $value, $html);
		}

		return nl2br(htmlspecialchars_decode($html));
	}
}
