<?php
class ControllerExtensionModuleUpstoreWallcategory extends Controller {
	public function index($setting) {

		$data['type_view_mob'] = (!empty($setting['type_view_mob']) ? $setting['type_view_mob'] : 0);

		$this->upstore_minifier->addStyle('catalog/view/theme/upstore/stylesheet/jquery.mCustomScrollbar.min.css', 'footer');
		$this->upstore_minifier->addScript('catalog/view/theme/upstore/js/jquery.mousewheel-3.1.3.js', 'footer_onload');
		$this->upstore_minifier->addScript('catalog/view/theme/upstore/js/jquery.mCustomScrollbar.min.js', 'footer_onload');

		if(isset($setting['width']) && !empty($setting['width'])){
			$width = $setting['width'];
		} else {
			$width = 150;
		}
		if(isset($setting['height']) && !empty($setting['height'])){
			$height = $setting['height'];
		} else {
			$height = 150;
		}

		static $module = 0;
		$this->load->model('tool/image');

		$this->load->model('catalog/category');
		$this->load->model('extension/module/upstore_wallcategory');
		$limit_sub_category = $setting['limit'];

		if (isset($setting['wall_category'])) {
			$categories = $setting['wall_category'];
		} else {
			$categories = array();
		}
		if (!empty($categories)){
			foreach ($categories as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
			array_multisort($sort_order, SORT_ASC, $categories);
		}

		$data['categories'] = array();
		foreach($categories as $category){
            $category_info = $this->model_catalog_category->getCategory($category['category']);

		if(!empty($category_info)){
			$data['subcategories'] = array();
			$subcategories = $this->model_catalog_category->getCategories($category['category']);
			if($subcategories){
				$subcategories = array_slice($subcategories, 0, $limit_sub_category);

				foreach($subcategories as $subcategory){
					$path = $this->model_extension_module_upstore_wallcategory->getCategoryPath($subcategory['category_id']);
					$data['subcategories'][] = array(
						'category_id' 	=> $subcategory['category_id'],
						'name'        	=> $subcategory['name'],
						'href'  	    => $this->url->link('product/category', 'path=' . $path),
					);
				}
			}
			if ($category['image']) {
				$image_category = $this->model_tool_image->resize($category['image'], $width, $height);
			} else {
				$image_category = $this->model_tool_image->resize('placeholder.png', $width, $height);
			}
			$path = $this->model_extension_module_upstore_wallcategory->getCategoryPath($category['category']);
			$data['categories'][] = array(
				'width' 		=> $width,
				'height' 		=> $height,
				'subcategories' => $data['subcategories'],
				'category_id' => $category_info['category_id'],
				'name' 		  => $category_info['name'],
				'href'  	  => $this->url->link('product/category', 'path=' . $path),
				'image' 	  => $image_category,
			);
		}
        }


		$this->load->model('catalog/manufacturer');
		if (isset($setting['wall_manufactures'])) {
			$wall_manufactures = $setting['wall_manufactures'];
		} else {
			$wall_manufactures = '';
		}
		if (!empty($wall_manufactures)){
			foreach ($wall_manufactures as $key => $value) {
				$sort_order_manufactures[$key] = $value['sort_order'];
			}
			array_multisort($sort_order_manufactures, SORT_ASC, $wall_manufactures);
		}
		$data['manufacturers'] = array();

		if($wall_manufactures) {
			foreach ($wall_manufactures as $manufacturer) {
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer['manufacturer_id']);

				if($manufacturer_info) {
					$data['manufacturers'][] = array(
						'width' 		=> $width,
						'height' 		=> $height,
						'manufacturer_id' => $manufacturer_info['manufacturer_id'],
						'name'            => $manufacturer_info['name'],
						'href'            => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id']),
						'thumb'           => $this->model_tool_image->resize(($manufacturer['image']=='' ? 'no_image.jpg' : $manufacturer['image']), $width, $height)
						);
				}
			}
		}
		$data['module'] = $module++;
		$data['lang_id'] = $this->config->get('config_language_id');
		$data['heading_title'] = $setting['title_name'];
		return $this->load->view('extension/module/upstore_wallcategory', $data);
	}


}