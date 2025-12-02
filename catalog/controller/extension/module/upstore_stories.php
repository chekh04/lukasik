<?php
class ControllerExtensionModuleUpstoreStories extends Controller {

	public function index($setting) {

		static $module = 0;
		$this->document->addStyle('catalog/view/theme/upstore/stylesheet/stories.css');

		$this->load->model('tool/image');

		$data['module_id'] = $setting['module_id'];
		$lang_id = $this->config->get('config_language_id');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['width'] = isset($setting['width']) ? $setting['width'] : 480;
		$data['height'] = isset($setting['height']) ? $setting['height'] : 660;

		$data['chm_stories'] = array();

		$results = $setting['chm_stories'];

		foreach ($results as $key_stories => $stories) {
			if(!empty($stories[$lang_id])){
				foreach ($stories[$lang_id] as $key_item => $result) {
					if ($key_item > 0) {
						break;
					}
					if (is_file(DIR_IMAGE . $result['image'])) {
						$data['chm_stories'][$key_stories] = array(
							'image' => $this->model_tool_image->resize($result['image'], 480, 660),
						);
					}
				}
			}
		}
		$data['module'] = $module++;

		return $this->load->view('extension/module/upstore_stories', $data);
	}

	public function getAllStories() {
		if (isset($this->request->get['module_id']) && (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {

			if (isset($this->request->get['story_id'])) {
				$data['story_id'] = (int)$this->request->get['story_id'];
			}
			if (isset($this->request->get['module_id'])) {
				$module_id = (int)$this->request->get['module_id'];
			}
			if(!isset($story_id) && (!isset($module_id))){
				return;
			}

			$this->load->model('tool/image');
			$this->load->model('setting/module');

			$setting = $this->model_setting_module->getModule($module_id);
			$data['module_id'] = $setting['module_id'];
			$data['width'] = isset($setting['width']) ? $setting['width'] : 480;
			$data['height'] = isset($setting['height']) ? $setting['height'] : 660;

			$lang_id = $this->config->get('config_language_id');

			$data['chm_stories'] = array();

			$results = $setting['chm_stories'];

			foreach ($results as $key_stories => $stories) {
				foreach ($stories[$lang_id] as $key_item => $result) {
					if (is_file(DIR_IMAGE . $result['image'])) {
						$data['chm_stories'][$key_stories][] = array(
							'btn_text'	=> !empty($result['btn_text']) ? $result['btn_text'] : false,
							'location'	=> !empty($result['location']) ? 'bottom' : 'top',
							'type_btn'	=> !empty($result['type_btn']) ? 'light' : 'dark',
							'link'		=> !empty($result['link']) ? $result['link'] : false,
							'image' 		=> $this->model_tool_image->resize($result['image'], 480, 660),
						);
					}
				}
			}

			$this->response->setOutput($this->load->view('extension/module/upstore_modal_stories', $data));
		}
	}
}