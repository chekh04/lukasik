<?php
class ModelCheckoutOnepcheckout extends Model {

	public function getRelatedProducts($product_ids) {
		$this->load->model('catalog/product');

		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr
			LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id)
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
			WHERE pr.product_id IN (" . $product_ids . ")
			AND p.status = '1'
			AND p.quantity > 0
			AND p.date_available <= NOW()
			AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $this->model_catalog_product->getProduct($result['related_id']);
		}

		return $product_data;
	}

	public function addAbandonedOrder($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "opc_abandoned_order SET store_id = '" . (int)$data['store_id'] . "', customer_id = '" . (int)$data['customer_id'] . "', language_id = '" . (int)$this->config->get('config_language_id') . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', products = '" . $this->db->escape(json_encode($data['products'])) . "', date_added = NOW()");

		return $this->db->getLastId();
	}

	public function editAbandonedOrder($abandoned_id, $data) {
		if ($abandoned_id && $this->abandonedOrderExists($abandoned_id)) {
			$this->db->query("UPDATE " . DB_PREFIX . "opc_abandoned_order SET language_id = '" . (int)$this->config->get('config_language_id') . "', email = '" . $this->db->escape($data['email']) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', products = '" . $this->db->escape(json_encode($data['products'])) . "' WHERE abandoned_id = '" . (int)$abandoned_id . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "opc_abandoned_order SET store_id = '" . (int)$data['store_id'] . "', customer_id = '" . (int)$data['customer_id'] . "', language_id = '" . (int)$this->config->get('config_language_id') . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', products = '" . $this->db->escape(json_encode($data['products'])) . "', date_added = NOW()");
			$abandoned_id = $this->db->getLastId();
		}

		return $abandoned_id;
	}

	public function abandonedOrderExists($abandoned_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "opc_abandoned_order WHERE abandoned_id = '" . (int)$abandoned_id . "'");
		return ($query->row['total'] > 0);
	}

	public function removeAbandonedOrder($abandoned_id) {
		if ($abandoned_id && $this->abandonedOrderExists($abandoned_id)) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "opc_abandoned_order WHERE abandoned_id = '" . (int)$abandoned_id . "'");
		}
	}

	public function getCustomField($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opc_custom_field` cf LEFT JOIN `" . DB_PREFIX . "opc_custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.status = '1' AND cf.custom_field_id = '" . (int)$custom_field_id . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCustomFields($location, $customer_group_id = 0) {
		$custom_field_data = array();

		if (!$customer_group_id) {
			$custom_field_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opc_custom_field` cf LEFT JOIN `" . DB_PREFIX . "opc_custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.status = '1' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cf.status = '1' AND cf.location = '" . $this->db->escape($location) . "' ORDER BY cf.sort_order ASC");
		} else {
			$custom_field_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "opc_custom_field_customer_group` cfcg LEFT JOIN `" . DB_PREFIX . "opc_custom_field` cf ON (cfcg.custom_field_id = cf.custom_field_id) LEFT JOIN `" . DB_PREFIX . "opc_custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.status = '1' AND cf.location = '" . $this->db->escape($location) . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cfcg.customer_group_id = '" . (int)$customer_group_id . "' ORDER BY cf.sort_order ASC");
		}

		foreach ($custom_field_query->rows as $custom_field) {
			$custom_field_value_data = array();

			if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio' || $custom_field['type'] == 'checkbox') {
				$custom_field_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "opc_custom_field_value cfv LEFT JOIN " . DB_PREFIX . "opc_custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfv.custom_field_id = '" . (int)$custom_field['custom_field_id'] . "' AND cfvd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY cfv.sort_order ASC");

				foreach ($custom_field_value_query->rows as $custom_field_value) {
					$custom_field_value_data[] = array(
						'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
						'name'                  => $custom_field_value['name']
					);
				}
			}

			$custom_field_data[$custom_field['custom_field_id']] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $custom_field_value_data,
				'name'               => $custom_field['name'],
				'text_error'         => trim($custom_field['text_error']),
				'type'               => $custom_field['type'],
				'value'              => $custom_field['value'],
				'validation'         => $custom_field['validation'],
				'location'           => $custom_field['location'],
				'required'           => empty($custom_field['required']) || $custom_field['required'] == 0 ? false : true,
				'sort_order'         => $custom_field['sort_order']
			);
		}

		return $custom_field_data;
	}

	public function getCustomFieldValue($custom_field_value_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "opc_custom_field_value cfv LEFT JOIN " . DB_PREFIX . "opc_custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfv.custom_field_value_id = '" . (int)$custom_field_value_id . "' AND cfvd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCustomFieldValues($custom_field_id) {
		$custom_field_value_data = array();

		$custom_field_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "opc_custom_field_value cfv LEFT JOIN " . DB_PREFIX . "opc_custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfv.custom_field_id = '" . (int)$custom_field_id . "' AND cfvd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY cfv.sort_order ASC");

		foreach ($custom_field_value_query->rows as $custom_field_value) {
			$custom_field_value_data[$custom_field_value['custom_field_value_id']] = array(
				'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
				'name'                  => $custom_field_value['name']
			);
		}

		return $custom_field_value_data;
	}
}