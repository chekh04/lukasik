<?php
class ControllerExtensionModuleUpstoreNotifyPrice extends Controller {
	private $error = array();

	public function index() {

		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->load->model('catalog/product');
			$this->load->language('extension/module/upstore_notify_price');

			$data['setting'] = $this->config->get('upstore_notify_price_setting');

			$data['lang_id'] = $this->config->get('config_language_id');
			$data['button_send'] = $this->language->get('button_send');
			$data['text_placeholder_email'] = $this->language->get('text_placeholder_email');
			$data['name'] = ($this->customer->isLogged()) ? $this->customer->getFirstName() : '';
			$data['telephone'] = ($this->customer->isLogged()) ? $this->customer->getTelephone() : '';
			$data['email'] = ($this->customer->isLogged()) ? $this->customer->getEmail() : '';

			$agree_notify_price_id = isset($data['setting']['agree_notify_price_id']) ? $data['setting']['agree_notify_price_id'] : 0;

			if ($agree_notify_price_id) {
				$this->load->model('catalog/information');
				$this->load->language('upstore/theme');
				$information_info = $this->model_catalog_information->getInformation($agree_notify_price_id);

				if ($information_info) {
					$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $agree_notify_price_id, true), $information_info['title'], $information_info['title']);
				} else {
					$data['text_agree'] = '';
				}
			} else {
				$data['text_agree'] = '';
			}

			if (isset($this->request->get['product_id'])) {
				$data['product_id'] = $this->request->get['product_id'];
			} else {
				$data['product_id'] = 0;
			}

			$pids_in_waitlist = [];

			$data['in_waitlist'] = false;

			if ($this->customer->isLogged()) {
				$this->load->model('extension/module/upstore_notify_price');
				$pids_in_waitlist = $this->model_extension_module_upstore_notify_price->getProductsRequestsByCustomer();

				$in_waitlist = in_array($data['product_id'], $pids_in_waitlist) ? true : false;

				if($in_waitlist){
					$data['in_waitlist'] = true;
					$data['text_notify_price_exists'] = $this->language->get('text_notify_price_exists');
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

			$this->response->setOutput($this->load->view('extension/module/upstore_notify_price', $data));
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

	public function confirm() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$json = array();

			$this->load->language('extension/module/upstore_notify_price');
			$this->load->model('extension/module/upstore_notify_price');
			$this->load->model('catalog/product');

			if (!$this->validateCsrfToken()) {
				$json['error']['csrf'] = $this->language->get('error_csrf');
			}

			if (!$json && !$this->validate()) {
				$json['error'] = $this->error;
			}

			if(isset($this->request->post['np_product_id'])){
				$product_id = $this->request->post['np_product_id'];
			} else {
				$product_id = 0;
			}



			$product_info = $this->model_extension_module_upstore_notify_price->getProduct($product_id);

			$notify_stock_setting = $this->config->get('upstore_notify_price_setting');

			if (!$product_info) {
				$json['error']['product'] = $this->language->get('error_product_not_found');
			}

			// if (!$json && $this->model_extension_module_upstore_notify_price->checkNotifyPrice($product_id, $this->request->post['email'])) {
			// 	$json['error']['exists'] = $this->language->get('error_notify_price_exists');
			// }

			$notify_price_status = $this->config->get('upstore_notify_price_status');

			if (!$json && $notify_price_status) {

				$data = [
					'name' 				=> $this->request->post['name'] ?? '',
					'telephone' 		=> $this->request->post['telephone'] ?? '',
					'email' 				=> $this->request->post['email'] ?? '',
					'product_id'		=> $product_id,
					'product_name'		=> $product_info['name'],
					'price'				=> $product_info['price'],
					'special'			=> $product_info['special'],
					'product_link'		=> $this->url->link('product/product', 'product_id=' . $product_id)
				];

				$this->model_extension_module_upstore_notify_price->saveNotifyPrice($data);

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
		$notify_price_setting = $this->config->get('upstore_notify_price_setting');

		if (isset($notify_price_setting['name_field'], $notify_price_setting['name_field_required']) &&
			$notify_price_setting['name_field'] == 1 && $notify_price_setting['name_field_required'] == 1) {
			if (utf8_strlen(trim($this->request->post['name'])) < 1 || utf8_strlen(trim($this->request->post['name'])) > 32) {
				$this->error['name'] = $this->language->get('error_name_field');
			}
		}

		if (isset($notify_price_setting['telephone_field'], $notify_price_setting['telephone_field_required']) &&
			$notify_price_setting['telephone_field'] == 1 && $notify_price_setting['telephone_field_required'] == 1) {
			if (utf8_strlen($this->request->post['telephone']) < 3 || utf8_strlen($this->request->post['telephone']) > 20) {
				$this->error['telephone'] = $this->language->get('error_telephone_field');
			}
		}

		if (utf8_strlen($this->request->post['email']) > 96 || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email_field');
		}

		$agree_notify_price_id = isset($notify_price_setting['agree_notify_price_id']) ? $notify_price_setting['agree_notify_price_id'] : 0;

		if ($agree_notify_price_id) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($agree_notify_price_id);

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

		$setting = $this->config->get('upstore_notify_price_setting');

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
		$mail->setHtml($this->load->view('mail/notify_price_mail', $data));
		$mail->send();
	}

	public function cron(){

		$setting = $this->config->get('upstore_notify_price_setting');

		if ($this->config->get('upstore_notify_price_status') && isset($setting['cron_security_key'])) {

			$getSecurityKey = isset($this->request->get['cron_security_key']) ? $this->request->get['cron_security_key'] : '';

			if ($getSecurityKey === $setting['cron_security_key']) {

				$this->load->model('tool/image');
				$this->load->model('extension/module/upstore_notify_price');
				$this->load->language('extension/module/upstore_notify_price');

				$products = $this->model_extension_module_upstore_notify_price->getChangedPriceProducts();

				if(!empty($products)){
					foreach($products as $product_info){
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], 100, 100);
						} else {
							$image = false;
						}


						$customers = $this->model_extension_module_upstore_notify_price->getCustomersByProductId($product_info['product_id']);

						if(!empty($customers)){

							foreach($customers as $customer_info){

								$current_price = (float)$product_info['current_price'];
								$current_special = isset($product_info['current_special']) ? (float)$product_info['current_special'] : null;

								$notified = false;

								$old_price = (float)$customer_info['price'];
								$old_special = (float)$customer_info['special'];

								if ($old_special > 0) {
									if ($current_special > 0 && $current_special < $old_special) {
										$notified = true;
									} elseif ($current_special == $old_special && $current_price < $old_price) {
										$notified = false;
									}
								} else {
									if ($current_special > 0 && $current_special < $old_price) {
										$notified = true;
									}
								}

								if ($current_special == 0 && $current_price < $old_price) {
									$notified = true;
								}


								$format_price = $this->currency->format($this->tax->calculate($product_info['current_price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));

								if ((float)$product_info['current_special']) {
									$format_special = $this->currency->format($this->tax->calculate($product_info['current_special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
								} else {
									$format_special = false;
								}

								$format_old_price = $this->currency->format($this->tax->calculate($customer_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));

								if ((float)$customer_info['special']) {
									$format_old_special = $this->currency->format($this->tax->calculate($customer_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
								} else {
									$format_old_special = false;
								}

								if ($notified) {
									$data['store_name']				= $this->config->get('config_name');
									$data['product_name'] 			= $product_info['name'];
									$data['product_link'] 			= $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
									$data['product_image'] 			= $image;
									$data['product_model'] 			= $product_info['model'];
									$data['customer_name'] 			= $customer_info['name'];
									$data['customer_email'] 		= $customer_info['email'];
									$data['customer_telephone'] 	= $customer_info['telephone'];
									$data['price']						= $format_price;
									$data['special']					= $format_special;
									$data['old_price']				= $format_old_price;
									$data['old_special']				= $format_old_special;

									if (isset($setting['customer_email_status']) && $setting['customer_email_status'] == 1) {
										$subject = isset($setting['customer_email_subject'][$customer_info['language_id']]) ? strip_tags($setting['customer_email_subject'][$customer_info['language_id']]) : '';
										$html = isset($setting['customer_email_html'][$customer_info['language_id']]) ? strip_tags($setting['customer_email_html'][$customer_info['language_id']]) : '';

										if (empty($subject)) {
											$subject = sprintf($this->language->get('text_mail_subject'), $data['store_name'], $data['product_name']);
										}

										if (empty($html)) {
											$html = sprintf($this->language->get('text_mail_html'), $data['customer_name'], $data['product_name'], $data['store_name'], $data['product_link'], $data['store_name']);
										}

										$data['subject'] = $this->convertHtmlCustomFields($data, $subject);
										$data['html'] = $this->convertHtmlCustomFields($data, $html);
									} else {
										$data['subject'] = sprintf($this->language->get('text_mail_subject'), $data['store_name'], $data['product_name']);
										$data['html'] = sprintf($this->language->get('text_mail_html'), $data['customer_name'], $data['product_name'], $data['store_name'], $data['product_link'], $data['store_name']);
									}

									$this->sendMailCustomer($data);
									$this->model_extension_module_upstore_notify_price->updateStatusNotifyPrice($customer_info['notify_price_id'], $current_price, $current_special);
								}
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
			'~store_name~'       => isset($data['store_name']) ? $data['store_name'] : '',
			'~customer_name~'    => isset($data['customer_name']) ? $data['customer_name'] : '',
			'~customer_telephone~' => isset($data['customer_telephone']) ? $data['customer_telephone'] : '',
			'~customer_email~'   => isset($data['customer_email']) ? $data['customer_email'] : '',
			'~product_name~'     => isset($data['product_name']) ? $data['product_name'] : '',
			'~product_link~'     => isset($data['product_link']) ? $data['product_link'] : '',
			'~product_image~'    => isset($data['product_image']) ? '<img src="'. $data['product_image'] .'" alt="'. $data['product_name'] .'">' : '',
			'~product_model~'    => isset($data['product_model']) ? $data['product_model'] : '',
			'~price~'            => isset($data['price']) ? $data['price'] : '',
			'~special~'          => isset($data['special']) ? $data['special'] : '',
			'~old_price~'        => isset($data['old_price']) ? $data['old_price'] : '',
			'~old_special~'      => isset($data['old_special']) ? $data['old_special'] : '',
		];

		foreach ($replace_map as $variable => $value) {
			$html = str_replace($variable, $value, $html);
		}

		$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

		if (isset($data['special']) && !empty($data['special'])) {
			$html = preg_replace('/\{ if special \}(.*?)\{ else \}.*?\{ endif \}/s', '$1', $html);
		} else {
			$html = preg_replace('/\{ if special \}.*?\{ else \}(.*?)\{ endif \}/s', '$1', $html);
		}

		if (isset($data['old_special']) && !empty($data['old_special'])) {
			$html = preg_replace('/\{ if old_special \}(.*?)\{ else \}.*?\{ endif \}/s', '$1', $html);
		} else {
			$html = preg_replace('/\{ if old_special \}.*?\{ else \}(.*?)\{ endif \}/s', '$1', $html);
		}

		$html = str_replace(['{ if special }', '{ else }', '{ endif }', '{ if old_special }', '{ else }', '{ endif }'], '', $html);

		return nl2br($html);
	}
}
