<?php
class ControllerExtensionModuleUpstoreCallback extends Controller {
	private $error = array();

	public function index() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			if ($this->config->get('config_callback_id')) {
				$this->load->model('catalog/information');
				$this->load->language('upstore/theme');
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_callback_id'));

				if ($information_info) {
					$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_callback_id'), true), $information_info['title'], $information_info['title']);
				} else {
					$data['text_agree'] = '';
				}
			} else {
				$data['text_agree'] = '';
			}
			$data['lang_datetimepicker'] = $this->session->data['language'];
			$data['dt_locale_file'] = false;
			if(file_exists('catalog/view/javascript/jquery/datetimepicker/locale/'.$this->session->data['language'].'.js')) {
				$data['dt_locale_file'] = true;
			}
			$this->load->model('extension/module/upstore_callback');
			$this->load->language('extension/module/upstore_callback');

			$data['callbackpro'] = $this->config->get('callbackpro');

			$json = array();
			if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['action'])) {
				if ($this->validate()) {
					$data = array();
					if (isset($this->request->post['name'])) {
						$data['name'] = $this->request->post['name'];
					} else {
						$data['name'] = '';
					}
					if (isset($this->request->post['phone'])) {
						$data['phone'] = $this->request->post['phone'];
					} else {
						$data['phone'] = '';
					}
					if (isset($this->request->post['comment_buyer'])) {
						$data['comment_buyer'] = $this->request->post['comment_buyer'];
					} else {
						$data['comment_buyer'] = '';
					}
					if (isset($this->request->post['email_buyer'])) {
						$data['email_buyer'] = $this->request->post['email_buyer'];
					} else {
						$data['email_buyer'] = '';
					}
					if (isset($this->request->post['date_callback'])) {
						$data['date_callback'] = $this->request->post['date_callback'];
					} else {
						$data['date_callback'] = '';
					}
					if (isset($this->request->post['time_callback_on'])) {
						$data['time_callback_on'] = $this->request->post['time_callback_on'];
					} else {
						$data['time_callback_on'] = '';
					}
					if (isset($this->request->post['time_callback_off'])) {
						$data['time_callback_off'] = $this->request->post['time_callback_off'];
					} else {
						$data['time_callback_off'] = '';
					}
					if (isset($this->request->post['url_site'])) {
						$data['url_site'] = $this->request->post['url_site'];
					} else {
						$data['url_site'] = '';
					}
					if (isset($this->request->post['topic_callback_send'])) {
						$data['topic_callback_send'] = $this->request->post['topic_callback_send'];
					} else {
						$data['topic_callback_send'] = '';
					}
					$data['store_name'] = $this->config->get('config_name');
					$data['language_id'] = $this->config->get('config_language_id');
					$results = $this->model_extension_module_upstore_callback->addCallback($data);
					unset($this->session->data['csrf_token']);
					$callbackpro = $this->config->get('callbackpro');
					$config_on_off_send_me_mail_callback = (isset($callbackpro['config_on_off_send_me_mail_callback']) ? $callbackpro['config_on_off_send_me_mail_callback'] : '');
					$config_you_email_callback = $callbackpro['config_you_email_callback'];
					if($config_on_off_send_me_mail_callback =='1'){
						if($config_you_email_callback != ''){
							$this->sendMail($data);
						}
					}
					$json['success'] = $this->language->get('ok');
				}else{
					if(!empty($this->error)){
						$json['warning'] = $this->error;
					}
				}

				return $this->response->setOutput(json_encode($json));
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

			$data['lang_id'] = $this->config->get('config_language_id');

			$this->load->model('tool/image');

			if(!empty($data['callbackpro']['call_topic'])) {
				$data['call_topic_data'] = $data['callbackpro']['call_topic'];
			} else {
				$data['call_topic_data'] = array();
			}
			if (is_file(DIR_IMAGE . $data['callbackpro']['main_image_callback'])) {
				$data['main_image_callback'] = $this->model_tool_image->resize($data['callbackpro']['main_image_callback'], 120, 120);
			} else {
				$data['main_image_callback'] = '';
			}

			$this->response->setOutput($this->load->view('extension/module/upstore_callback', $data));
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}

	private function validate() {

		$token_csrf = false;
		if (isset($this->request->post['token_csrf'])) {
			$token_csrf = $this->request->post['token_csrf'];
		}

		if (!isset($this->session->data['csrf_token']) || !$token_csrf || $token_csrf !== $this->session->data['csrf_token']) {
			return false;
		}

		$this->load->language('extension/module/callback');
		$callbackpro = $this->config->get('callbackpro');
		$config_fields_firstname_requared_cb = (isset($callbackpro['config_fields_firstname_requared_cb']) ? 1 : 0);
		$config_on_off_fields_firstname_cb = (isset($callbackpro['config_on_off_fields_firstname_cb']) ? 1 : 0);
		if(($config_fields_firstname_requared_cb =='1') && $config_on_off_fields_firstname_cb =='1'){
			if ((utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
				$this->error['name'] = $this->language->get('mister');
			}
		}
		$config_fields_phone_requared_cb = (isset($callbackpro['config_fields_phone_requared_cb']) ? 1 : 0);
		$config_on_off_fields_phone_cb = (isset($callbackpro['config_on_off_fields_phone_cb']) ? 1 : 0);
		if(($config_fields_phone_requared_cb =='1') && $config_on_off_fields_phone_cb =='1'){
			if ((utf8_strlen(trim($this->request->post['phone'])) < 1) || (utf8_strlen(trim($this->request->post['phone'])) > 32)) {
				$this->error['phone'] = $this->language->get('wrongnumber');
			}
		}
		$config_fields_comment_requared_cb = (isset($callbackpro['config_fields_comment_requared_cb']) ? 1 : 0);
		$config_on_off_fields_comment_cb = (isset($callbackpro['config_on_off_fields_comment_cb']) ? 1 : 0);
		if(($config_fields_comment_requared_cb =='1') && $config_on_off_fields_comment_cb == '1'){
			if ((utf8_strlen(trim($this->request->post['comment_buyer'])) < 1) || (utf8_strlen(trim($this->request->post['comment_buyer'])) > 400)) {
				$this->error['comment_buyer'] = $this->language->get('comment_buyer_error');
			}
		}
		$config_fields_email_requared_cb = (isset($callbackpro['config_fields_email_requared_cb']) ? 1 : 0);
		$config_on_off_fields_email_cb = (isset($callbackpro['config_on_off_fields_email_cb']) ? 1 : 0);
		if(($config_fields_email_requared_cb =='1') && $config_on_off_fields_email_cb == '1'){
			if(!preg_match("/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/", $this->request->post['email_buyer'])){
				$this->error['email_error'] =  $this->language->get('email_buyer_error');
			}
		}
			// Agree to terms
		if ($this->config->get('config_callback_id')) {
			$this->load->model('catalog/information');
			$this->load->language('upstore/theme');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_callback_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['error_agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	private function getCustomFields($order_info, $varabliesd) {
		$instros = explode('~', $varabliesd);
		$instroz = "";
		foreach ($instros as $instro) {
			if ($instro == 'totals' || isset($order_info[$instro]) ){
				if ($instro == 'totals'){
					$instro_other = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], true);
				}
				if(isset($order_info[$instro])){
					$instro_other = $order_info[$instro];
				}
			}
			else {
				$instro_other = nl2br(htmlspecialchars_decode($instro));
			}
			$instroz .=  $instro_other;
		}
		return $instroz;
	}
	private function sendMail($data) {
		$this->load->language('extension/module/upstore_callback');
		$text = '';
		$callbackpro = $this->config->get('callbackpro');
		$subject_get = $this->getCustomFields($data, $callbackpro['quickorder_subject_me_callback'][$data['language_id']]);
		if ((strlen(utf8_decode($subject_get)) > 5)){
			$subject = $subject_get;
		} else {
			$subject = $this->language->get('subject');
		}

		$html = $this->getCustomFields($data, $callbackpro['quickorder_description_me_callback'][$data['language_id']]). "\n";

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($callbackpro['config_you_email_callback']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($data['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
		$mail->setText($text);
		$mail->send();
	}
}
?>
