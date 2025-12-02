<?php
class ControllerExtensionModuleUpstoreBannerPro extends Controller {
	public function index($setting) {

		static $module = 0;
		$this->document->addStyle('catalog/view/theme/upstore/stylesheet/banner_pro.css');
		$quantity_column = isset($setting['quantity_column']) ? $setting['quantity_column'] : 6;
		if($quantity_column == 6){
			$data['col'] = 2;
		} elseif($quantity_column == 4){
			$data['col'] = 3;
		} elseif($quantity_column == 3){
			$data['col'] = 4;
		} else {
			$data['col'] = 6;
		}

		$this->load->model('tool/image');

		$data['lang_id'] = $this->config->get('config_language_id');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$results = $setting['banner_pro'];

		$data['banners_pro'] = array();
		$i = 0;
		foreach ($results as $result) {

			$image_width = (!empty($result['image_width'][$data['lang_id']]) ? $result['image_width'][$data['lang_id']] : 200);
			$image_height = (!empty($result['image_height'][$data['lang_id']]) ? $result['image_height'][$data['lang_id']] : 200);

			if (is_file(DIR_IMAGE . $result['image'][$data['lang_id']])) {
				$image = $this->model_tool_image->resize($result['image'][$data['lang_id']], $image_width, $image_height);
			} else {
				$image = '';
			}
			$price_banner = isset($result['price'][$data['lang_id']]) ? $result['price'][$data['lang_id']] : '';
			if (!empty($price_banner)) {
				$price = $this->currency->format((float)$price_banner, $this->session->data['currency']);
			} else {
				$price = false;
			}

			$data['banners_pro'][] = array(
				'image_width' 			=> $image_width,
				'image_height' 		=> $image_height,
				'image'					=> $image,
				'mode' 					=> isset($result['image_display_mode'][$data['lang_id']]) ? $result['image_display_mode'][$data['lang_id']] : '',
				'title' 					=> isset($result['title'][$data['lang_id']]) ? $result['title'][$data['lang_id']] : '',
				'bg_block' 				=> isset($result['bg_block'][$data['lang_id']]) ? $result['bg_block'][$data['lang_id']] : '',
				'description' 			=> isset($result['description'][$data['lang_id']]) ? $result['description'][$data['lang_id']] : '',
				'link' 					=> isset($result['link'][$data['lang_id']]) ? $result['link'][$data['lang_id']] : '',
				'price' 					=> $price,
				'price_from'			=> isset($result['price_from'][$data['lang_id']]) ? $result['price_from'][$data['lang_id']] : '',
				'color_price_from'	=> isset($result['color_price_from'][$data['lang_id']]) ? $result['color_price_from'][$data['lang_id']] : '',
				'price_color'			=> isset($result['price_color'][$data['lang_id']]) ? $result['price_color'][$data['lang_id']] : '',
				'color_title' 			=> isset($result['color_title'][$data['lang_id']]) ? $result['color_title'][$data['lang_id']] : '',
				'color_desc' 			=> isset($result['color_desc'][$data['lang_id']]) ? $result['color_desc'][$data['lang_id']] : '',
			);

		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/upstore_banner_pro', $data);
	}
}