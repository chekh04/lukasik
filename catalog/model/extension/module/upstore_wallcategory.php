<?php
class ModelExtensionModuleUpstoreWallcategory extends Model {
	public function getCategoryPath($category_id){
		$path = '';
		$category = $this->db->query("SELECT c.`category_id`,c.`parent_id` FROM " . DB_PREFIX . "category c WHERE c.`category_id` = " .(int)$category_id."");
		if(isset($category->row['parent_id']) && ($category->row['parent_id'] != 0)){
			$path .= $this->getCategoryPath($category->row['parent_id']) . '_';
		}
		if(isset($category->row['category_id'])){
			$path .= $category->row['category_id'];
		}

		return $path;
	}
}