<?php
class ModelExtensionModuleUpstoreNotifyPrice extends Model {
	public function saveNotifyPrice($data) {
		if ($this->customer->isLogged()) {
			$customer_id = $this->customer->getId();
		} else {
			$customer_id = 0;
		}

		$this->db->query("INSERT INTO `". DB_PREFIX ."notify_price` SET customer_id = '" . (int)$customer_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', name = '".$this->db->escape($data['name'])."', telephone = '". $this->db->escape($data['telephone']) ."', email = '". $this->db->escape($data['email']) ."', product_id = '". (int)$data['product_id'] ."', price = '". (float)$data['price'] ."', special = '". (float)$data['special'] ."', date_added = NOW(), status_id = '0'");
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT p.product_id, pd.name AS name, p.price, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW()");

		if ($query->num_rows) {
			return [
				'product_id' => $query->row['product_id'],
				'name'       => $query->row['name'],
				'price'      => $query->row['price'],
				'special'    => $query->row['special']
			];
		} else {
			return false;
		}
	}


	public function checkNotifyPrice($product_id, $email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "notify_price WHERE product_id = '" . (int)$product_id . "' AND email = '" . $this->db->escape($email) . "'");

		return $query->row['total'] > 0;
	}

	public function getAvailableProducts() {
		$query = $this->db->query("SELECT DISTINCT `np`.`product_id`, `pd`.`name`, `p`.`image`, `p`.`model` FROM `" . DB_PREFIX . "notify_price` `np` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`np`.`product_id` = `pd`.`product_id` AND `pd`.`language_id` = `np`.`language_id`) LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`np`.`product_id` = `p`.`product_id`) WHERE `np`.`status_id` = 0 AND `p`.`quantity` > 0");

		return $query->rows;
	}

	public function getChangedPriceProducts() {
		$customer_group_id = $this->config->get('config_customer_group_id');

		$query = $this->db->query("SELECT np.product_id, pd.name, p.image, p.tax_class_id, p.model, p.price AS current_price, COALESCE(ps.price, 0) AS current_special, CASE WHEN np.special > 0 THEN CASE WHEN ps.price > 0 AND ps.price < np.special THEN 1 WHEN ps.price = np.special AND p.price < np.price THEN 0 ELSE 0 END ELSE CASE WHEN ps.price > 0 AND ps.price < np.price THEN 1 WHEN p.price < np.price THEN 1 ELSE 0 END END AS notified FROM " . DB_PREFIX . "notify_price np LEFT JOIN " . DB_PREFIX . "product_description pd ON (np.product_id = pd.product_id AND pd.language_id = np.language_id) LEFT JOIN " . DB_PREFIX . "product p ON (np.product_id = p.product_id) LEFT JOIN (SELECT ps.product_id, MIN(ps.price) AS price FROM " . DB_PREFIX . "product_special ps WHERE ps.customer_group_id = {$customer_group_id} AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id) ps ON ps.product_id = p.product_id WHERE np.status_id = 0 HAVING notified = 1");

		return $query->rows;
	}

	public function getCustomersByProductId($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "notify_price` WHERE `product_id` = '" . (int)$product_id . "' AND `status_id` = 0 AND `email` !='' ");

		return $query->rows;
	}

	public function updateStatusNotifyPrice($notify_price_id, $current_price, $current_special) {
		$this->db->query("UPDATE `" . DB_PREFIX . "notify_price` SET status_id = '1', notified_price = '" . (float)$current_price . "', notified_special = '" . (float)$current_special . "', date_send_email = NOW() WHERE notify_price_id = '" . (int)$notify_price_id . "'");
	}

	public function getWaitRequests($data = array()) {
		$sql = "SELECT `np`.*, `pd`.`name` AS `product_name` FROM `" . DB_PREFIX . "notify_price` np LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (np.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE np.customer_id = '" . (int)$this->customer->getId() . "'";

		$sql .= " ORDER BY `np`.`date_added` DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalWaitRequests(){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `". DB_PREFIX ."notify_price` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function removeNotifyRequest($notify_price_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "notify_price` WHERE notify_price_id = '" . (int)$notify_price_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "notify_price` WHERE notify_price_id = '" . (int)$notify_price_id . "'");
			return true;
		} else {
			return false;
		}
	}

	public function getProductsRequestsByCustomer(){
		$query = $this->db->query("SELECT DISTINCT product_id FROM `". DB_PREFIX ."notify_price` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		$product_ids = array_column($query->rows, 'product_id');

		return $product_ids;
	}

}
?>