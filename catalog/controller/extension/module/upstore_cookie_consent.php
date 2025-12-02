<?php
class ControllerExtensionModuleUpstoreCookieConsent extends Controller {
	public function index() {

		$lang_id = $this->config->get('config_language_id');

		$data['cookie_consent_status'] = $this->config->get('module_upstore_cookie_consent_status');
		$data['setting'] = $this->config->get('module_upstore_cookie_consent_setting');

		$data['description'] = html_entity_decode($data['setting']['description'][$lang_id], ENT_QUOTES, 'UTF-8');
		$data['button_agree'] = html_entity_decode($data['setting']['btn_agree_text'][$lang_id], ENT_QUOTES, 'UTF-8');

		$settings = $data['setting'];

		$required_keys = [
			'bg_container',
			'bg_btn_agree',
			'bg_btn_agree_hover',
			'text_color_btn_agree',
			'text_color_btn_agree_hover',
			'border_color_btn_agree',
			'border_color_btn_agree_hover'
		];

		$css_root = ":root {\n";

		foreach ($required_keys as $key) {
			$css_variable = '--' . str_replace('_', '-', $key);

			$css_value = isset($settings[$key]) && !empty($settings[$key]) ? $settings[$key] : '#e8e8e8';

			$css_root .= "    $css_variable: $css_value;\n";
		}

		$css_root .= "}\n";

		$data['css_root'] = $css_root;

		$this->response->setOutput($this->load->view('extension/module/upstore_cookie_consent', $data));

	}

	public function setCookie() {
		$setting = $this->config->get('module_upstore_cookie_consent_setting');
		$cookie_day = isset($setting['cookie_day']) ? (int)$setting['cookie_day'] : 7;

		if (isset($this->request->post['saveCookie'])) {
			setcookie('cookie_consent', 1, time() + (60*60*24*$cookie_day), "/");

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(['status' => 'success']));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(['status' => 'error', 'message' => 'Неверные данные']));
		}
	}
}