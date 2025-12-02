<?php
class ControllerExtensionModuleUpstoreEasybanner extends Controller {
	public function index($setting) {

		static $module = 0;
		$this->document->addStyle('catalog/view/theme/upstore/stylesheet/easybanner.css');

		$data['banner_column'] = isset($setting['banner_column']) ? $setting['banner_column'] : 4;

		$this->load->model('tool/image');

		$data['lang_id'] = $this->config->get('config_language_id');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['type_easy_banner'] = isset($setting['easy_banner_type']) ? $setting['easy_banner_type'] : 1;

		$results = $setting['easy_banner'];

		$data['easy_banners'] = array();

		foreach ($results as $result) {

			$image_width = (!empty($result['image_width'][$data['lang_id']]) ? $result['image_width'][$data['lang_id']] : 200);
			$image_height = (!empty($result['image_height'][$data['lang_id']]) ? $result['image_height'][$data['lang_id']] : 200);

			$logo_image_width = (!empty($result['logo_image_width'][$data['lang_id']]) ? $result['logo_image_width'][$data['lang_id']] : 100);
			$logo_image_height = (!empty($result['logo_image_height'][$data['lang_id']]) ? $result['logo_image_height'][$data['lang_id']] : 40);


			if (is_file(DIR_IMAGE . $result['image'][$data['lang_id']])) {
				$image = $this->model_tool_image->resize($result['image'][$data['lang_id']], $image_width, $image_height);
			} else {
				$image = '';
			}
			if (is_file(DIR_IMAGE . $result['logo_image'][$data['lang_id']])) {
				$logo_image = $this->model_tool_image->resize($result['logo_image'][$data['lang_id']], $logo_image_width, $logo_image_height);
			} else {
				$logo_image = false;
			}
			$price_banner = isset($result['price'][$data['lang_id']]) ? $result['price'][$data['lang_id']] : '';

			if (!empty($price_banner)) {
				$price = $this->currency->format((float)$price_banner, $this->session->data['currency']);
			} else {
				$price = false;
			}

			$data['easy_banners'][] = array(
				'image_width' 	=> $image_width,
				'image_height' 	=> $image_height,
				'logo_image_width' => $logo_image_width,
				'logo_image_height' => $logo_image_height,
				'image'			=> $image,
				'logo_image'	=> $logo_image,
				'title' 		=> isset($result['title'][$data['lang_id']]) ? $result['title'][$data['lang_id']] : '',
				'bg_block' 		=> isset($result['bg_block'][$data['lang_id']]) ? $result['bg_block'][$data['lang_id']] : '',
				'description' 	=> isset($result['description'][$data['lang_id']]) ? $result['description'][$data['lang_id']] : '',
				'link' 			=> isset($result['link'][$data['lang_id']]) ? $result['link'][$data['lang_id']] : '',
				'price' 		=> $price,
				'price_from'	=> isset($result['price_from'][$data['lang_id']]) ? $result['price_from'][$data['lang_id']] : '',
				'price_color'	=> isset($result['price_color'][$data['lang_id']]) ? $result['price_color'][$data['lang_id']] : '',
				'price_bg' 		=> isset($result['price_bg'][$data['lang_id']]) ? $result['price_bg'][$data['lang_id']] : '',
				'color_title' 	=> isset($result['color_title'][$data['lang_id']]) ? $result['color_title'][$data['lang_id']] : '',
				'color_desc' 	=> isset($result['color_desc'][$data['lang_id']]) ? $result['color_desc'][$data['lang_id']] : '',
				'text_align' 	=> isset($result['text_align'][$data['lang_id']]) ? $result['text_align'][$data['lang_id']] : 'left',
				'image_align' 	=> isset($result['image_align'][$data['lang_id']]) ? $result['image_align'][$data['lang_id']] : 'left',
			);
			$data['html_css'] = '';
			foreach($data['easy_banners'] as $key => $result){
				if(!empty($result['bg_block'])){
					$data['html_css'] .='.easy-banner-' . $module . $key .' .easy-b-price-inner:after {background: '. $result['bg_block'] .';}';
				}
				if(!empty($result['price_bg'])){
					$data['html_css'] .='.easy-banner-' . $module . $key .' .easy-b-price-inner,.easy-banner-' . $module . $key .' .easy-b-price-inner:before {background: '. $result['price_bg'] .';}';
				}
				if(!empty($result['price_color'])){
					$data['html_css'] .='.easy-banner-' . $module . $key .' .easy-b-price,.easy-banner-' . $module . $key .' .easy-b-price .cs-currency,.easy-banner-' . $module . $key .' .easy-b-price-from {color: '. $result['price_color'] .';}';
				}


			}

		}
		$data['module'] = $module++;

		return $this->load->view('extension/module/upstore_easybanner', $data);
	}
}