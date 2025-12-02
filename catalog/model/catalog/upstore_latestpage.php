<?php
class ModelCatalogUpstoreLatestpage extends Model {

	public function getDateAvailable() {
		$query = $this->db->query("SELECT DATE(date_added) as date_added FROM " . DB_PREFIX . "product WHERE date_added > DATE_SUB(CURDATE(), INTERVAL '" . (int)$this->config->get('config_day_latest_product') . "' DAY) AND status = '1' GROUP BY DATE(date_added) ORDER BY date_added DESC");
		if ($query->num_rows) {
			return $query->rows;
		} else {
			return false;
		}
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";
		$sql .= " FROM " . DB_PREFIX . "product p";
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
		LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
		WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND p.status = '1'
		AND date_added > DATE_SUB(CURDATE(), INTERVAL '" . (int)$this->config->get('config_day_latest_product') . "' DAY)
		AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		if(!empty($data['date_ave'])){
		$sql .= " AND DATE(date_added) = '" . $this->db->escape($data['date_ave']) . "'";
		}
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getLatest($data = array()) {

		$sql = "SELECT p.product_id, p.sort_order, p.model, pd.name, p.quantity, p.price, p.date_added, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		$sql .= " FROM " . DB_PREFIX . "product p";
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_added <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		$sql .= " AND date_added > DATE_SUB(CURDATE(), INTERVAL '" . (int)$this->config->get('config_day_latest_product') . "' DAY)";
		if(!empty($data['date_ave'])){
		$sql .= " AND DATE(date_added) = '" . $this->db->escape($data['date_ave']) . "'";
		}
		$sql .= " GROUP BY p.product_id";


		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'p.image',
			'p.viewed',
			'special',
			'rating',
			'p.sort_order',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY (p.price>0) DESC,(p.image>'') DESC,(p.quantity>0) DESC, LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'special') {
				$order_special = ($data['order'] == 'ASC') ? 'DESC' : 'ASC';
				$sql .= " ORDER BY (p.price>0) DESC, (p.image>'') DESC, (p.quantity>0) DESC, special " . $order_special .", (CASE WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (p.price>0) DESC,(p.image>'') DESC,(p.quantity>0) DESC, (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY (p.price>0) DESC,(p.image>'') DESC,(p.quantity>0) DESC, " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY (p.price>0) DESC,(p.image>'') DESC,(p.quantity>0) DESC, p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}


		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
	}
}
?>