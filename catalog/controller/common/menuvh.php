<?php
class ControllerCommonMenuvh extends Controller {
	public function index($load_mmenu = false) {
		$megamenu_setting = $this->config->get('megamenu_setting');

		$data['lang_id'] = $this->config->get('config_language_id');
		$data['desc_info_mob'] = html_entity_decode($this->config->get('language_description_info_mob')[$data['lang_id']]['text'], ENT_QUOTES, 'UTF-8');
		$data['type_menu_header'] = (!empty($this->config->get('type_menu_header')) ? $this->config->get('type_menu_header') : 1);
		$data['design_icon_menu'] = (!empty($this->config->get('design_icon_menu')) ? $this->config->get('design_icon_menu') : 1);

		if($megamenu_setting['status']=='1'){
			$detect = new Mobile_Detect();
			$data['deviceType'] = ($detect->isMobile() ? ($detect->isTablet() ? 'isMobile' : 'isTablet') : 'computer');
			$this->load->language('upstore/theme');
			$data['text_catalog'] = $this->language->get('text_catalog');
			$data['text_category'] = $this->language->get('text_category');
			$data['text_account'] = $this->language->get('text_account');
			$data['text_info_mob'] = $this->language->get('text_info_mob');
			$data['text_search'] = $this->language->get('text_search');
			$data['text_all_category'] = $this->language->get('text_all_category');
			$this->load->model('extension/module/nsmenu');
			$data['main_menu_mask'] = $megamenu_setting['main_menu_mask'];
			$data['items']=array();
			$menu_items_cache = $this->cache->get('mmheader.' . (int)$this->config->get('config_language_id').'.'. (int)$this->config->get('config_store_id'));

			if (!empty($menu_items_cache)) {
				$data['items'] = $menu_items_cache;
				$data['megamenu_status']=true;
			} else {
				$config_menu_item = $this->model_extension_module_nsmenu->getItemsMenu();

				if(!empty($config_menu_item)) {
					$menu_items = $config_menu_item;
				} else {
					$menu_items = array();
				}

				foreach($menu_items as $datamenu){
					if($datamenu['menu_type']=="link" && $datamenu['status'] !='0')	{
						$data['items'][]=$this->model_extension_module_nsmenu->MegaMenuTypeLink($datamenu);
					}
					if($datamenu['menu_type']=="information" && $datamenu['status'] !='0')	{
						$data['items'][]=$this->model_extension_module_nsmenu->MegaMenuTypeInformation($datamenu);
					}
					if($datamenu['menu_type']=="manufacturer" && $datamenu['status'] !='0')	{
						$data['items'][]=$this->model_extension_module_nsmenu->MegaMenuTypeManufacturer($datamenu);
					}
					if($datamenu['menu_type']=="product" && $datamenu['status'] !='0'){
						$data['items'][]=$this->model_extension_module_nsmenu->MegaMenuTypeProduct($datamenu);
					}
					if($datamenu['menu_type']=="category" && $datamenu['status'] !='0')	{
						$data['items'][] = $this->model_extension_module_nsmenu->MegaMenuTypeCategory($datamenu);
					}
					if($datamenu['menu_type']=="html" && $datamenu['status'] !='0')	{
						$data['items'][]=$this->model_extension_module_nsmenu->MegaMenuTypeHtml($datamenu);
					}
					if($datamenu['menu_type']=="freelink" && $datamenu['status'] !='0')	{
						$data['items'][]=$this->model_extension_module_nsmenu->MegaMenuTypeFreeLink($datamenu);
					}
				}

				$menu_items_cache = $data['items'];
				$this->cache->set('mmheader.' . (int)$this->config->get('config_language_id') . '.'. (int)$this->config->get('config_store_id'), $menu_items_cache);
				$data['megamenu_status']=true;

			}
		} else {
			$data['megamenu_status']=false;
		}
		if(!empty($data['items'])){
			$this->config->set('status_mm', true);
		}
		if (isset($load_mmenu) && ($load_mmenu== 1)) {
			return $this->load->view('common/mob_menu_left', $data);
		}

		return $this->load->view('common/menu_v', $data);
	}
	public function load_mob_menu(){
		$this->response->setOutput($this->index($load_mmenu = true));
	}
}