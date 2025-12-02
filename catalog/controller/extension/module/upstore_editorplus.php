<?php
class ControllerExtensionModuleUpstoreEditorplus extends Controller {
	public function index() {
		if (isset($this->request->get['product_id'])) {
			$data['product_id'] = (int)$this->request->get['product_id'];
		} else {
			$data['product_id'] = '';
		}
		if (isset($this->request->get['route'])) {
			$data['route'] = (string)$this->request->get['route'];
		} else {
			$data['route'] = '';
		}
		if (isset($this->request->get['path'])) {
			$seo_parts = explode('_', (string)$this->request->get['path']);
			$data['seo_category_id'] = (int)array_pop($seo_parts);
		} else {
			$data['seo_category_id'] = 0;
		}
		$data['preg1'] = '/\/[a-zA-Zа-яА-ЯёЁ0-9\-\_\%]*[\.]{0,1}[a-zA-Z]{0,}\?|$/';
		$data['preg2'] = '/\/([a-zA-Zа-яА-ЯёЁ0-9\-\_\%]*)[^]{0,1}[a-zA-Z]{0,}(?:\?|$)/';
		if(isset($this->session->data['user_token'])){
			return $this->load->view('extension/module/upstore_editorplus', $data);
		} else {
			return false;
		}
	}

	public function getSettingEditorplus() {
		if(isset($this->session->data['user_token'])){
			$data['user_token'] = $this->session->data['user_token'];
		} else {
			$data['user_token'] = false;
		}

		$this->registry->set('user', new Cart\User($this->registry));
		if ($this->user->isLogged()){
			$editorusergroupid = $this->user->getGroupId();
			$data['user_logged'] = true;
		} else {
			$data['user_logged'] = false;
		}

	if($this->request->post['route_mod'] == 'product/category'){
		if(isset($this->session->data['user_token'])){
			if($data['user_logged']) {
				$data['seo_category_id'] = $this->request->post['seo_category_id'];
				$data['user_token'] = $this->session->data['user_token'];
				$this->load->model('catalog/product_quick');
				$this->load->language('module/groupeditor/btngroupeditor');
				if(!empty($editorusergroupid)){
					$user_groups_view = $this->model_catalog_product_quick->getUserGroupsEditView($editorusergroupid);
					$data['view_open_description_edit'] 	= (!empty($user_groups_view['description_edit']) ? $user_groups_view['description_edit'] : false);
					$data['view_open_category_edit'] 		= (!empty($user_groups_view['category_edit']) ? $user_groups_view['category_edit'] : false);
					$data['view_open_image_edit'] 			= (!empty($user_groups_view['image_edit']) ? $user_groups_view['image_edit'] : false);
					$data['view_open_image_url_edit'] 		= (!empty($user_groups_view['image_url_edit']) ? $user_groups_view['image_url_edit'] : false);
					$data['view_open_image_google_edit'] 	= (!empty($user_groups_view['image_google_edit']) ? $user_groups_view['image_google_edit'] : false);
					$data['view_open_price_edit'] 			= (!empty($user_groups_view['price_edit']) ? $user_groups_view['price_edit'] : false);
					$data['view_open_special_edit'] 		= (!empty($user_groups_view['special_edit']) ? $user_groups_view['special_edit'] : false);
					$data['view_open_related_edit'] 		= (!empty($user_groups_view['related_edit']) ? $user_groups_view['related_edit'] : false);
					$data['view_open_code_edit'] 			= (!empty($user_groups_view['code_edit']) ? $user_groups_view['code_edit'] : false);
					$data['view_open_attribute_edit'] 		= (!empty($user_groups_view['attribute_edit']) ? $user_groups_view['attribute_edit'] : false);
					$data['view_open_option_edit'] 			= (!empty($user_groups_view['option_edit']) ? $user_groups_view['option_edit'] : false);
					$data['link_module_edit_admin'] 		= (!empty($user_groups_view['link_module_edit_admin']) ? $user_groups_view['link_module_edit_admin'] : false);
					$data['link_product_admin'] 			= (!empty($user_groups_view['link_product_admin']) ? $user_groups_view['link_product_admin'] : false);
					$data['group_editor'] 					= (!empty($user_groups_view['group_editor']) ? $user_groups_view['group_editor'] : false);
				}
			}
		}

		$json = array();

		$products_id = $products_url_alias = $json['btn_product'] = array ();

		if (isset($this->request->post['prod_id_edit']) && is_array($this->request->post['prod_id_edit'])) {
			foreach ($this->request->post['prod_id_edit'] as $key => $value) {
				$products_id[$key] = (int)$value;
			}
		}

		if (isset($this->request->post['url_product_edit']) && is_array($this->request->post['url_product_edit'])) {
			foreach ($this->request->post['url_product_edit'] as $key => $value) {
				$products_url_alias[$key] = $this->db->escape(utf8_strtolower(urldecode($value)));
			}
		}


		if ($products_url_alias) {
			$query = $this->db->query('SELECT query, LCASE(keyword) AS keyword FROM ' . DB_PREFIX . 'seo_url WHERE keyword IN ("' . implode ('","', $products_url_alias) . '") AND query LIKE "product_id=%" AND language_id = ' . (int)$this->config->get('config_language_id') . '');
			foreach ($query->rows as $result_db) {
				foreach ($products_url_alias as $index=>$keyword) {
					if ($keyword == $result_db['keyword']) {
						$products_id[$index] = (int)str_replace('product_id=', '', $result_db['query']);
						unset ($products_url_alias[$index]);
					}
				}
			}
		}


		if(!empty($products_id)){
			$json['group_btn'] = $this->load->view('extension/module/upstore_editorplus_group', $data);
		} else {
			$json['group_btn'] = array();
		}

		foreach ($products_id as $index=>$product_id) {
			$astickers = '';
			$data['product_id'] = $product_id;
			$astickers .= $this->load->view('extension/module/upstore_editorplus_category', $data);

			$json['btn_product'][$index] = $astickers;
		}


		header ('Content-type: text/html; charset=utf-8');

		echo json_encode($json);
	}

		/*Product load*/
		if($this->request->post['route_mod'] == 'product/product'){
			$json = array();
			if(isset($this->session->data['user_token'])){
				if (isset($this->request->post['product_id'])) {
					$data['product_id'] = (int)$this->request->post['product_id'];
				} else {
					$data['product_id'] = '';
				}
					if($data['user_logged']) {
						$this->load->model('catalog/product_quick');
						$this->load->language('module/groupeditor/btngroupeditor');
						$data['user_token'] = $this->session->data['user_token'];
						if(!empty($editorusergroupid)){
							$user_groups_view = $this->model_catalog_product_quick->getUserGroupsEditView($editorusergroupid);
							$data['view_open_description_edit'] 	= (!empty($user_groups_view['description_edit']) ? $user_groups_view['description_edit'] : false);
							$data['view_open_category_edit'] 		= (!empty($user_groups_view['category_edit']) ? $user_groups_view['category_edit'] : false);
							$data['view_open_image_edit'] 			= (!empty($user_groups_view['image_edit']) ? $user_groups_view['image_edit'] : false);
							$data['view_open_image_url_edit'] 		= (!empty($user_groups_view['image_url_edit']) ? $user_groups_view['image_url_edit'] : false);
							$data['view_open_image_google_edit'] 	= (!empty($user_groups_view['image_google_edit']) ? $user_groups_view['image_google_edit'] : false);
							$data['view_open_price_edit'] 			= (!empty($user_groups_view['price_edit']) ? $user_groups_view['price_edit'] : false);
							$data['view_open_special_edit'] 		= (!empty($user_groups_view['special_edit']) ? $user_groups_view['special_edit'] : false);
							$data['view_open_related_edit'] 		= (!empty($user_groups_view['related_edit']) ? $user_groups_view['related_edit'] : false);
							$data['view_open_code_edit'] 			= (!empty($user_groups_view['code_edit']) ? $user_groups_view['code_edit'] : false);
							$data['view_open_attribute_edit'] 		= (!empty($user_groups_view['attribute_edit']) ? $user_groups_view['attribute_edit'] : false);
							$data['view_open_option_edit'] 			= (!empty($user_groups_view['option_edit']) ? $user_groups_view['option_edit'] : false);
							$data['link_module_edit_admin'] 		= (!empty($user_groups_view['link_module_edit_admin']) ? $user_groups_view['link_module_edit_admin'] : false);
							$data['link_product_admin'] 			= (!empty($user_groups_view['link_product_admin']) ? $user_groups_view['link_product_admin'] : false);
						}
					}
					$json['edit_prod'] = $this->load->view('extension/module/upstore_editorplus_product', $data);
					header ('Content-type: text/html; charset=utf-8');
					echo json_encode($json);
			}
		}
	}
}

