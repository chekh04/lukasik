<?php
class ModelExtensionModuleUpstoreNotifyStock extends Model {
	public function saveNotifyStock($data) {
		if ($this->customer->isLogged()) {
			$customer_id = $this->customer->getId();
		} else {
			$customer_id = 0;
		}

		$this->db->query("INSERT INTO `". DB_PREFIX ."notify_stock` SET customer_id = '" . (int)$customer_id . "', language_id = '" . (int)$this->config->get('config_language_id') . "', name = '".$this->db->escape($data['name'])."', telephone = '". $this->db->escape($data['telephone']) ."', email = '". $this->db->escape($data['email']) ."', product_id = '". (int)$data['ns_product_id'] ."', date_added = NOW(), status_id = '0'");
	}

	public function checkNotifyStock($product_id, $email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "notify_stock WHERE product_id = '" . (int)$product_id . "' AND email = '" . $this->db->escape($email) . "'");

		return $query->row['total'] > 0;
	}

	public function getAvailableProducts() {
		$query = $this->db->query("SELECT DISTINCT `ns`.`product_id`, `pd`.`name`, `p`.`image`, `p`.`model` FROM `" . DB_PREFIX . "notify_stock` `ns` LEFT JOIN `" . DB_PREFIX . "product_description` `pd` ON (`ns`.`product_id` = `pd`.`product_id` AND `pd`.`language_id` = `ns`.`language_id`) LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`ns`.`product_id` = `p`.`product_id`) WHERE `ns`.`status_id` = 0 AND `p`.`quantity` > 0");

		return $query->rows;
	}

	public function getCustomersByProductId($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "notify_stock` `ns` WHERE `product_id` = '" . (int)$product_id . "' AND `status_id` = 0 AND `email` !='' ");

		return $query->rows;
	}

	public function updateStatusNotifyStock($notify_stock_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "notify_stock` SET status_id = '1', date_send_email = NOW() WHERE notify_stock_id = '" . (int)$notify_stock_id . "'");
	}

	public function getWaitRequests($data = array()) {
		$sql = "SELECT `ns`.*, `pd`.`name` AS `product_name` FROM `" . DB_PREFIX . "notify_stock` ns LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (ns.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE ns.customer_id = '" . (int)$this->customer->getId() . "'";

		$sql .= " ORDER BY `ns`.`date_added` DESC";

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
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `". DB_PREFIX ."notify_stock` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function removeNotifyRequest($notify_stock_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "notify_stock` WHERE notify_stock_id = '" . (int)$notify_stock_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "notify_stock` WHERE notify_stock_id = '" . (int)$notify_stock_id . "'");
			return true;
		} else {
			return false;
		}
	}

	public function getProductsRequestsByCustomer(){
		$query = $this->db->query("SELECT DISTINCT product_id FROM `". DB_PREFIX ."notify_stock` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		$product_ids = array_column($query->rows, 'product_id');

		return $product_ids;
	}

}
?>