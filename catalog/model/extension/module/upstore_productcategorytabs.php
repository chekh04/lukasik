<?php
class ModelExtensionModuleUpstoreProductcategorytabs extends Model {
	public function getCategories($categories) {
		if(!empty($categories)){
			$implode = array();

			foreach ($categories as $category_id) {
				$implode[] = (int)$category_id;
			}

			$query = $this->db->query("SELECT DISTINCT c.category_id,cd.name FROM " . DB_PREFIX . "category c
				LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
				LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)
				WHERE c.category_id IN (". implode(',', $implode). ")
				AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
				AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
				AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

			return $query->rows;
		}
	}

	public function checkCategory($category_id){
		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p
			LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
			WHERE p2c.category_id = '". (int)$category_id ."'
			AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			AND p.status = '1'
			AND p.date_available <= NOW()
			AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");

		return $query->num_rows;
	}

	public function checkSubCategories($category_id){

		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_path WHERE path_id='". (int)$category_id ."'");
		if($query->rows){
			$result=array();
			foreach($query->rows as $row){
				$result[]= (int)$row['category_id'];
			}
			$query_p2c = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p
			LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
			WHERE p2c.category_id IN (". implode(',',$result). ")
			AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			AND p.status = '1'
			AND p.date_available <= NOW()
			AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' LIMIT 1");

			return $query_p2c->num_rows;
		} else {
			return false;
		}

	}

	public function getProductToCategoryTabSelect($data = array()) {


		$cache_key = 'product.pct.' . (int)$data['filter_category_id'] . '.' . (int)$data['filter_sub_category'] . '.'. (int)$data['limit_max'] . '.' . (int)$data['start'] . '.'. (int)$data['limit'] . '.'. (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . substr(md5(serialize($data['sort'] . $data['order'])), 0, 8);

		$product_data = $this->cache->get($cache_key);

		if (is_array($product_data)) {
			return $product_data;
		}

		$sql = "SELECT * FROM (SELECT p.product_id, p.sort_order, p.image, p.model, p.viewed, p.date_added, pd.name, p.quantity, p.price,(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";

		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}

		if ($data['filter_hide_out_of_stock']) {
			$sql .= " AND p.quantity > 0";
		}

		$sql .= " GROUP BY p.product_id";

		$sql .= $this->buildSortOrderSql($data, 'p.sort_order');

		$sql .= " LIMIT 0," . (int)$data['limit_max'];
		$sql .= ") p";

		$sql .= $this->buildSortOrderSql($data, 'p.sort_order',true);

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 5;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		$this->cache->set($cache_key, $product_data);

		return $product_data;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";

		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}

		if ($data['filter_hide_out_of_stock']) {
			$sql .= " AND p.quantity > 0";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	private function buildSortOrderSql($data, $default_sort, $is_outer_query = false) {
		$sort_data = array(
			'pd.name',
			'p.price',
			'p.product_id',
			'p.model',
			'p.quantity',
			'rating',
			'p.viewed',
			'p.sort_order',
			'p.date_added'
		);

		$sql = "";

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort_field = $is_outer_query && $data['sort'] == 'pd.name' ? 'p.name' : $data['sort'];

			if ($sort_field == 'pd.name' || $sort_field == 'p.name' || $sort_field == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $sort_field . ")";
			} elseif ($sort_field == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $sort_field;
			}
		} else {
			$sql .= " ORDER BY " . $default_sort;
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		return $sql;
	}

}