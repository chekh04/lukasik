<?php
class ControllerExtensionModuleUpstoreCatalog extends Controller {
	public function index($setting) {
		$data['lang_id'] = $this->config->get('config_language_id');
		$data['heading_title'] = $setting['title_name'];

		if(isset($setting['width']) && !empty($setting['width'])){
			$width = $setting['width'];
		} else {
			$width = 40;
		}
		if(isset($setting['height']) && !empty($setting['height'])){
			$height = $setting['height'];
		} else {
			$height = 40;
		}

		static $module = 0;
		$this->load->model('tool/image');
		$this->load->model('catalog/category');
		$this->load->model('extension/module/upstore_wallcategory');


		if (isset($setting['wall_category'])) {
			$categories = $setting['wall_category'];
		} else {
			$categories = array();
		}

		$data['categories'] = array();
			foreach($categories as $category){
			$category_info = $this->model_catalog_category->getCategory($category['category']);

			if(isset($category['img_from_category']) && ($category['img_from_category'] == 1)){
				if ($category_info['image']) {
					$image_category = $this->model_tool_image->resize($category_info['image'], $width, $height);
				} else {
					$image_category = $this->model_tool_image->resize('placeholder.png', $width, $height);
				}
			} else {
				if ($category['image']) {
					$image_category = $this->model_tool_image->resize($category['image'], $width, $height);
				} else {
					$image_category = $this->model_tool_image->resize('placeholder.png', $width, $height);
				}
			}

			if(!empty($category_info)){
				$path = $this->model_extension_module_upstore_wallcategory->getCategoryPath($category['category']);
				$data['categories'][] = array(
					'width' 	  => $width,
					'height' 	  => $height,
					'category_id' => $category_info['category_id'],
					'name' 		  => $category_info['name'],
					'href'  	  => $this->url->link('product/category', 'path=' . $path),
					'image' 	  => $image_category,
				);
			}
        }

		$data['module'] = $module++;

		return $this->load->view('extension/module/upstore_catalog', $data);
	}


}