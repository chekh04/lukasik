<?php
class ModelCatalogUpstoreProductSizeChart extends Model {
	public function getSizeCharts($product_id) {
		$size_charts = [];

		$query = $this->db->query("SELECT sc.*, scd.name, scd.content
			FROM `" . DB_PREFIX . "product_size_chart` psc
			LEFT JOIN `" . DB_PREFIX . "size_chart` sc ON (psc.size_chart_id = sc.size_chart_id)
			LEFT JOIN `" . DB_PREFIX . "size_chart_description` scd ON (psc.size_chart_id = scd.size_chart_id)
			WHERE psc.product_id = '" . (int)$product_id . "'
			AND scd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			AND sc.status = '1'
			ORDER BY sc.sort_order");

		if ($query->rows) {
			foreach ($query->rows as $result) {
				$content_text = strip_tags(html_entity_decode($result['content'], ENT_QUOTES, 'UTF-8'), '<iframe>');

				if ($content_text !== '') {
					$size_charts[] = array(
						'size_chart_id' => $result['size_chart_id'],
						'sort_order'	 => $result['sort_order'],
						'content_type'	 => $result['content_type'],
						'use_header'	 => $result['use_header'],
						'name' 			 => $result['name'],
						'content' 		 => $result['content'],
					);
				}
			}
		}

		$size_chart_ids = array_column($size_charts, 'size_chart_id');
		$additional_size_charts = $this->getViewAllSizeCharts($size_chart_ids, $product_id);

		return array_merge($size_charts, $additional_size_charts);
	}

	private function getViewAllSizeCharts($exclude_ids, $product_id) {
		$results = [];

		$sql = "SELECT sc.*, scd.name, scd.content
		FROM `" . DB_PREFIX . "size_chart` sc
		LEFT JOIN `" . DB_PREFIX . "size_chart_description` scd ON (sc.size_chart_id = scd.size_chart_id)
		WHERE scd.language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND sc.status = '1'";

		if (!empty($exclude_ids)) {
			$sql .= " AND sc.size_chart_id NOT IN (" . implode(',', array_map('intval', $exclude_ids)) . ")";
		}

		$sql .= " ORDER BY sc.sort_order";

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$ignore_products = $result['products_ignore'] ? json_decode($result['products_ignore'], true) : [];
			if (in_array($product_id, $ignore_products)) {
				continue;
			}

			$show_categories = $result['show_categories'] ? json_decode($result['show_categories'], true) : [];
			$show_manufacturers = $result['show_manufacturers'] ? json_decode($result['show_manufacturers'], true) : [];
			$show_attributes = $result['show_attributes'] ? json_decode($result['show_attributes'], true) : [];

			$status_chart = false;

			if ($show_categories) {
				$query_c = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_category`
					WHERE product_id = '" . (int)$product_id . "'
					AND category_id IN (" . implode(',', array_map('intval', $show_categories)) . ")");
				if ($query_c->num_rows) {
					$status_chart = true;
				}
			}

			if (!$status_chart && $show_manufacturers) {
				$query_m = $this->db->query("SELECT manufacturer_id FROM `" . DB_PREFIX . "product`
					WHERE product_id = '" . (int)$product_id . "'");
				if ($query_m->num_rows && in_array((int)$query_m->row['manufacturer_id'], $show_manufacturers)) {
					$status_chart = true;
				}
			}

			if (!$status_chart && $show_attributes) {
				$query_a = $this->db->query("SELECT attribute_id FROM `" . DB_PREFIX . "product_attribute`
					WHERE product_id = '" . (int)$product_id . "'
					AND attribute_id IN (" . implode(',', array_map('intval', $show_attributes)) . ")
					AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
				if ($query_a->num_rows) {
					$status_chart = true;
				}
			}

			if($result['status_view_all']){
				$status_chart = true;
			}

			if ($status_chart) {
				$content_text = strip_tags(html_entity_decode($result['content'], ENT_QUOTES, 'UTF-8'), '<iframe>');
				if ($content_text !== '') {
					$results[] = array(
						'size_chart_id' => $result['size_chart_id'],
						'sort_order'	 => $result['sort_order'],
						'content_type'	 => $result['content_type'],
						'use_header'	 => $result['use_header'],
						'name' 			 => $result['name'],
						'content' 		 => $result['content'],
					);
				}
			}
		}

		return $results;
	}

	public function hasSizeCharts($product_id) {
		$query = $this->db->query("SELECT 1
			FROM `" . DB_PREFIX . "product_size_chart` psc
			LEFT JOIN `" . DB_PREFIX . "size_chart` sc ON (psc.size_chart_id = sc.size_chart_id)
			LEFT JOIN `" . DB_PREFIX . "size_chart_description` scd ON (psc.size_chart_id = scd.size_chart_id)
			WHERE psc.product_id = '" . (int)$product_id . "'
			AND scd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			AND sc.status = '1'
			LIMIT 1");

		if ($query->num_rows) {
			return true;
		}

		$sql = "SELECT sc.*, scd.name, scd.content
		FROM `" . DB_PREFIX . "size_chart` sc
		LEFT JOIN `" . DB_PREFIX . "size_chart_description` scd ON (sc.size_chart_id = scd.size_chart_id)
		WHERE scd.language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND sc.status = '1'";

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$ignore_products = $result['products_ignore'] ? json_decode($result['products_ignore'], true) : [];
			if (in_array($product_id, $ignore_products)) {
				continue;
			}

			$show_categories = $result['show_categories'] ? json_decode($result['show_categories'], true) : [];
			$show_manufacturers = $result['show_manufacturers'] ? json_decode($result['show_manufacturers'], true) : [];
			$show_attributes = $result['show_attributes'] ? json_decode($result['show_attributes'], true) : [];

			if ($show_categories) {
				$query_c = $this->db->query("SELECT 1 FROM `" . DB_PREFIX . "product_to_category`
					WHERE product_id = '" . (int)$product_id . "'
					AND category_id IN (" . implode(',', array_map('intval', $show_categories)) . ")
					LIMIT 1");
				if ($query_c->num_rows) {
					return true;
				}
			}

			if ($show_manufacturers) {
				$query_m = $this->db->query("SELECT manufacturer_id FROM `" . DB_PREFIX . "product`
					WHERE product_id = '" . (int)$product_id . "'
					LIMIT 1");
				if ($query_m->num_rows && in_array((int)$query_m->row['manufacturer_id'], $show_manufacturers)) {
					return true;
				}
			}

			if ($show_attributes) {
				$query_a = $this->db->query("SELECT 1 FROM `" . DB_PREFIX . "product_attribute`
					WHERE product_id = '" . (int)$product_id . "'
					AND attribute_id IN (" . implode(',', array_map('intval', $show_attributes)) . ")
					AND language_id = '" . (int)$this->config->get('config_language_id') . "'
					LIMIT 1");
				if ($query_a->num_rows) {
					return true;
				}
			}

			if($result['status_view_all']){
				return true;
			}
		}

		return false;
	}

}