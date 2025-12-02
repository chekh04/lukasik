<?php
class ModelExtensionModuleUpstoreAutosearch extends Model {
	private $lang_id;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->lang_id = $this->config->get('config_language_id');
   }

	public function ajaxLiveSearch($data = array()) {
		$language_id = $this->lang_id;

		$ns_autosearch_data = $this->config->get('ns_autosearch_data');

		$product_details = (!empty($ns_autosearch_data['display_product_details_on_off']) ? $ns_autosearch_data['display_product_details_on_off'] : false);
		$display_categories = (!empty($ns_autosearch_data['display_categories_on_off']) ? $ns_autosearch_data['display_categories_on_off'] : false);

		$customer_group_id = $this->customer->isLogged() ? $this->customer->getGroupId() : $this->config->get('config_customer_group_id');

		$sql = "SELECT
			p.product_id,
			p.model,
			p.status,
			p.price,
			p.quantity,
			p.minimum,
			p.viewed,
			p.date_available,
			pd.name,
			p.tax_class_id,
			p.image,
		";

		if ($product_details || (isset($ns_autosearch_data['display_rating_on_off']) && $ns_autosearch_data['display_rating_on_off'] == 1)) {
		$sql .= " (SELECT AVG(rating) AS total FROM `" . DB_PREFIX . "review` `r1` WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating,";
		$sql .= " (SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "review` `r2` WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews,";
		}
		if(isset($ns_autosearch_data['display_manufacturer_on_off']) && $ns_autosearch_data['display_manufacturer_on_off'] == 1){
		$sql .= " (SELECT m.name FROM " . DB_PREFIX . "manufacturer m WHERE m.manufacturer_id = p.manufacturer_id) AS manufacturer,";
		}
		if ($product_details || (isset($ns_autosearch_data['display_price_on_off']) && $ns_autosearch_data['display_price_on_off'] == 1)) {
		$sql .= " (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount,";
		$sql .= " (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, ";
		}
		if($product_details){
		$sql .= " (SELECT GROUP_CONCAT(image ORDER BY sort_order ASC SEPARATOR ', ') FROM " . DB_PREFIX . "product_image pi WHERE pi.product_id = p.product_id LIMIT 7) AS images,";
		}
		if($display_categories){
		$sql .= " (SELECT GROUP_CONCAT(DISTINCT CONCAT(pc.category_id, ':', cd.name) ORDER BY pc.category_id ASC SEPARATOR '|, ') AS categories_info FROM " . DB_PREFIX . "product_to_category pc LEFT JOIN " . DB_PREFIX . "category_description cd ON (pc.category_id = cd.category_id AND cd.language_id = '" . (int)$language_id . "') WHERE pc.product_id = p.product_id) AS categories_info,";
		}
		$sql .= " (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$language_id . "') AS stock_status";

		$sql .= " FROM " . DB_PREFIX . "product p";
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$language_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";



		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
			}

			if (!empty($data['filter_model'])) {
				$sql .= " OR LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
			}

			if (!empty($data['filter_sku'])) {
				$sql .= " OR LCASE(p.sku) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
			}

			if (!empty($data['filter_upc'])) {
				$sql .= " OR LCASE(p.upc) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
			}

			if (!empty($data['filter_manufacturer'])) {
				$sql .= " OR p.manufacturer_id IN (SELECT manufacturer_id from ".DB_PREFIX."manufacturer WHERE `name` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%')";
			}

			$sql .= ")";
		}

		$sql .= " GROUP BY p.product_id";
		$sql .= " ORDER BY p.sort_order";
		$sql .= " ASC, LCASE(pd.name) ASC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		$categoriesMap = array();
		$products = array();

		foreach ($query->rows as $row) {

			$categoryInfo = !empty($row['categories_info']) ? explode('|, ', $row['categories_info']) : array();

			foreach ($categoryInfo as $category) {
				list($category_id, $category_name) = explode(':', $category);

        // Добавляем категорию в массив categoriesMap только если ее нет
				if (!isset($categoriesMap[$category_id])) {
					$category_path = $this->getCategoryPath($category_id);

					$categoriesMap[$category_id] = array(
						'category_id' => $category_id,
						'name' => $category_name,
						'href' => $this->url->link('product/category', 'path=' . $category_path)
					);
				}
			}

			$products[] = array(
				'product_id' 	=> $row['product_id'],
				'model' 			=> $row['model'],
				'status' 		=> $row['status'],
				'price' 			=> (isset($row['discount']) ? $row['discount'] : (isset($row['price']) ? $row['price'] : false)),
				'quantity' 		=> $row['quantity'],
				'minimum' 		=> $row['minimum'],
				'viewed' 		=> $row['viewed'],
				'date_available' => $row['date_available'],
				'name' 			=> $row['name'],
				'tax_class_id' => $row['tax_class_id'],
				'image' 			=> $row['image'],
				'rating' 		=> isset($row['rating']) ? $row['rating'] : '',
				'reviews' 		=> isset($row['reviews']) ? $row['reviews'] : '',
				'manufacturer' => isset($row['manufacturer']) ? $row['manufacturer'] : '',
				'special' 		=> isset($row['special']) ? $row['special'] : false,
				'images' 		=> isset($row['images']) ? $row['images'] : array(),
				'stock_status' => $row['stock_status'],
        );

		}

		$uniqueCategories = array_values($categoriesMap);

		return array(
			'categories' => $uniqueCategories,
			'products'	 => $products,
		);
	}

	private function getCategoryPath($category_id){
		$path = '';
		$category = $this->db->query("SELECT `c`.`category_id`, `c`.`parent_id` FROM `" . DB_PREFIX . "category` `c` WHERE `c`.`category_id` = " .(int)$category_id."");
		if($category->row['parent_id'] != 0){
			$path .= $this->getCategoryPath($category->row['parent_id']) . '_';
		}
		$path .= $category->row['category_id'];

		return $path;
	}
}