<?php
class ModelUpstoreTheme extends Model {

	public function getManufacturerHtmlBloks($product_info) {
		$manufacturer_id = (int)$product_info['manufacturer_id'];

		$blocks = ['top' => '', 'bottom' => ''];
		$used_block_ids = [];

		$query = $this->db->query("SELECT b.block_id, b.position, bd.description FROM " . DB_PREFIX . "manufacturer_html_block b LEFT JOIN " . DB_PREFIX . "manufacturer_html_block_description bd ON b.block_id = bd.block_id WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND FIND_IN_SET('" . $manufacturer_id . "', b.manufacturer_ids) AND b.status = 1");

		foreach ($query->rows as $row) {
			$position = in_array($row['position'], ['top', 'bottom']) ? $row['position'] : 'bottom';
			if (empty($blocks[$position])) {
				$blocks[$position] = $this->replaceManufacturerHtmlFields($product_info, $row['description']);
				$used_block_ids[] = (int)$row['block_id'];
			}
		}

		if (empty($blocks['top']) || empty($blocks['bottom'])) {
			$query = $this->db->query("SELECT b.block_id, b.position, bd.description FROM " . DB_PREFIX . "manufacturer_html_block b LEFT JOIN " . DB_PREFIX . "manufacturer_html_block_description bd ON b.block_id = bd.block_id WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND b.show_for_all = 1 AND b.status = 1");

			foreach ($query->rows as $row) {
				$position = in_array($row['position'], ['top', 'bottom']) ? $row['position'] : 'bottom';
				if (empty($blocks[$position]) && !in_array((int)$row['block_id'], $used_block_ids)) {
					$blocks[$position] = $this->replaceManufacturerHtmlFields($product_info, $row['description']);
				}
			}
		}

		return $blocks;
	}


	public function replaceManufacturerHtmlFields($data, $html) {
		$this->load->model('tool/image');

		$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

		$manufacturer_name = !empty($data['manufacturer']) ? $data['manufacturer'] : '';
		$manufacturer_link = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . (int)$data['manufacturer_id']);

		$replacements = [
			'[manufacturer_name]' => $manufacturer_name,
			'[manufacturer_link]' => $manufacturer_link,
		];

		if (strpos($html, '[manufacturer_image]') !== false) {
			$manufacturer = $this->getManufacturerImage($data['manufacturer_id']);

			if (!empty($manufacturer['image'])) {
				$image = $this->model_tool_image->resize($manufacturer['image'], 100, 100);
				$replacements['[manufacturer_image]'] = $image;
			} else {
				$html = preg_replace_callback('/<img[^>]*>/i', function ($matches) {
					if (strpos($matches[0], '[manufacturer_image]') !== false) {
						return '';
					}
					return $matches[0];
				}, $html);
			}
		}

		foreach ($replacements as $key => $value) {
			$html = str_replace($key, $value, $html);
		}

		return $html;
	}


	public function getManufacturerImage($manufacturer_id) {
		$query = $this->db->query("SELECT m.image FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m.manufacturer_id = '" . (int)$manufacturer_id . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}

	public function replaceOrderHtmlFields($order_id, $html) {
		$order_info = $this->getOrder($order_id);

		if (!$order_info) return $html;

		$replacements = [
			'[order_id]'            => $order_info['order_id'],
			'[date_added]'          => $order_info['date_added'],
			'[firstname]'           => $order_info['firstname'],
			'[lastname]'            => $order_info['lastname'],
			'[telephone]'           => $order_info['telephone'],
			'[email]'               => $order_info['email'],
			'[payment_method]'      => $order_info['payment_method'],
			'[shipping_method]'     => $order_info['shipping_method'],
			'[shipping_zone]'       => $order_info['shipping_zone'],
			'[shipping_city]'       => $order_info['shipping_city'],
			'[shipping_address_1]'  => $order_info['shipping_address_1'],
			'[shipping_address_2]'  => $order_info['shipping_address_2'],
			'[total]'               => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']),

			'[contact_page]'        => $this->url->link('information/contact'),
			'[account_page]'        => $this->url->link('account/account', '', true),
			'[orders_page]'         => $this->url->link('account/order', '', true),
		];

		foreach ($replacements as $variable => $value) {
			$html = str_replace($variable, $value, $html);
		}

		return $html;
	}


	private function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");

		if ($order_query->num_rows) {

			return array(
				'order_id'                => $order_query->row['order_id'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'email'                   => $order_query->row['email'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_added'              => $order_query->row['date_added'],
			);
		} else {
			return false;
		}
	}

	public function getProductRelatedAuto($data = array()) {
		$this->load->model('catalog/product');

		if (empty($data['product_info']) || empty($data['product_info']['product_id'])) {
			return array();
		}

		$product_info = $data['product_info'];
		$exclude_ids = !empty($data['exclude_ids']) ? array_map('intval', $data['exclude_ids']) : [];
		$product_id = $product_info['product_id'];

		$language_id = $this->config->get('config_language_id');
		$store_id = $this->config->get('config_store_id');

		$sql = "SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

		if (!empty($data['filter_category_id'])) {
			$category_id = (int)$data['filter_category_id'];
			if (!empty($data['filter_sub_category'])) {
				$sql .= "
				LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
				LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= "
				LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
			}
		}

		$sql .= " WHERE pd.language_id = '" . (int)$language_id . "'";
		$sql .= " AND p.status = '1'";
		$sql .= " AND p.quantity > 0";
		$sql .= " AND p.date_available <= NOW()";
		$sql .= " AND p2s.store_id = '" . (int)$store_id . "'";
		$sql .= " AND p.product_id NOT IN (" . implode(',', $exclude_ids) . ")";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}

		if (!empty($product_info['manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$product_info['manufacturer_id'] . "'";
		}

		$words = explode(' ', trim($product_info['name']));
		$words = array_filter($words, function($word) {
			return mb_strlen($word) > 2;
		});

		$words = array_values($words);

		$word_conditions = [];

		if ($words) {
			if (count($words) <= 2) {
				$conditions = array_map(function ($word) {
					return "LCASE(pd.name) LIKE '%" . $this->db->escape($word) . "%'";
				}, $words);
				$sql .= " AND (" . implode(" OR ", $conditions) . ")";
			} else {

				$conditions = [];
				for ($i = 0; $i < 2 && $i < count($words); $i++) {
					$conditions[] = "LCASE(pd.name) LIKE '%" . $this->db->escape($words[$i]) . "%'";
				}

				$sql .= " AND (" . implode(" AND ", $conditions) . ")";
			}
		}

		$sql .= " GROUP BY p.product_id";

		$sort = 'p.sort_order';
		$order = 'ASC';

		if ($sort === 'pd.name' || $sort === 'p.product_id') {
			$sql .= " ORDER BY LCASE(" . $sort . ") " . $order;
		} else {
			$sql .= " ORDER BY " . $sort . " " . $order;
		}


		$start = 0;
		$limit = (!empty($data['limit']) && (int)$data['limit'] > 0) ? (int)$data['limit'] : 10;
		$sql .= " LIMIT " . $start . ", " . $limit;

		$product_data = array();
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getAllWishlist() {
		$this->load->model('account/wishlist');
		if ($this->model_account_wishlist->getWishlist()) {
			$wishlist = array();
			foreach ($this->model_account_wishlist->getWishlist() as $key => $value) {
				$wishlist[] = $value['product_id'];
			}
			return implode(",", $wishlist);
		} elseif (isset($this->session->data['wishlist'])) {
			return implode(",", $this->session->data['wishlist']);
		}
	}

	private function collectСategories(array $allItems, array &$children, $parentId = 0) {
		foreach ($allItems as $item) {
			if ($item['parent_id'] == $parentId) {
				$item['children'] = [];
				$this->collectСategories($allItems, $item['children'], $item['category_id']);
				$children[] = $item;
			}
		}
	}

	public function getWishlistCategories($data = array()) {

		if(!empty($data)){

			foreach ($data as $result) {
				if(isset($result['product_id'])){
					$products[] = (int)$result['product_id'];
				} else {
					$products[] = (int)$result;
				}
			}

			$sql = " SELECT cd.name, c.parent_id, c.category_id,cp.level, COUNT(DISTINCT `p`.`product_id`) AS total FROM `" . DB_PREFIX . "category_path` `cp`";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "category_description` `cd` ON (`cp`.`path_id` = `cd`.`category_id`)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "category` `c` ON (`cp`.`path_id` = `c`.`category_id`)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` `p2c` ON (`cp`.`category_id` = `p2c`.`category_id`)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`p2c`.`product_id` = `p`.`product_id`)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_store` `p2s` ON (`p`.`product_id` = `p2s`.`product_id`)";
			$sql .= " WHERE `cd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";
			$sql .= " AND `p`.`status` = '1' AND `c`.`status` = '1' AND `p`.`date_available` <= NOW() AND `p2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'";
			$sql .= " AND `p`.`product_id` IN (" . implode(',', $products) . ")";
			$sql .= " GROUP BY cp.path_id";
			$sql .= " ORDER BY `cp`.`level` ASC";

			$query = $this->db->query($sql);
			$query_rows = $query && $query->rows ? $query->rows : array();

			$categories = [];
			$this->collectСategories($query_rows, $categories);
			return $categories;
		}
	}

	public function getCheckedCategories($data = array()) {

		if(!empty($data['filter_categories'])){
			foreach ($data['filter_categories'] as $category_id) {
				$categories[] = (int)$category_id;
			}
			$sql = " SELECT cd.name, cd.category_id FROM `" . DB_PREFIX . "category_description` `cd`";
			$sql .= " WHERE `cd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";
			$sql .= " AND `cd`.`category_id` IN (" . implode(',', $categories) . ")";
			$sql .= " GROUP BY cd.category_id";
			$sql .= " ORDER BY `cd`.`category_id` ASC";
			$query = $this->db->query($sql);
		}

		return !empty($query->rows) ? $query->rows : array();
	}

	public function getWishlistProducts($data = array()) {

		if(!empty($data['filter_products'])){
			$products = array();
			$categories = array();

			foreach ($data['filter_products'] as $result) {
				if(isset($result['product_id'])){
					$products[] = (int)$result['product_id'];
				} else {
					$products[] = (int)$result;
				}
			}
			if(!empty($data['filter_categories'])){
				foreach ($data['filter_categories'] as $category_id) {
					$categories[] = (int)$category_id;
				}
			}


			$sql = " SELECT DISTINCT `p`.`product_id` FROM `" . DB_PREFIX . "category_path` `cp`";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` `p2c` ON (`cp`.`category_id` = `p2c`.`category_id`)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product` `p` ON (`p2c`.`product_id` = `p`.`product_id`)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_store` `p2s` ON (`p`.`product_id` = `p2s`.`product_id`)";
			$sql .= " WHERE `p`.`status` = '1' AND `p`.`date_available` <= NOW() AND `p2s`.`store_id` = '" . (int)$this->config->get('config_store_id') . "'";
			$sql .= " AND `p`.`product_id` IN (" . implode(',', $products) . ")";

			if(!empty($data['filter_categories'])){
				$sql .= " AND `cp`.`path_id` IN (" . implode(',', $categories) . ")";
			}

			if(!empty($data['filter_stock_status'])){
				if($data['filter_stock_status'] == 1){
					$sql .= " AND `p`.`quantity` > 0";
				} elseif($data['filter_stock_status'] == 2){
					$sql .= " AND `p`.`quantity` <= 0";
				}
			}

			$query = $this->db->query($sql);

			return !empty($query->rows) ? $query->rows : array();
		}

	}

	public function getLayoutModulesHomePage($layout_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "layout_module WHERE layout_id = '" . (int)$layout_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function num2word($num, $words){
		$num = $num % 100;
		if ($num > 19) {
			$num = $num % 10;
		}
		switch ($num) {
			case 1: {
				return($words[0]);
			}
			case 2: case 3: case 4: {
				return($words[1]);
			}
			default: {
				return($words[2]);
			}
		}
	}

	public function lang_date($date_format, $date_added) {
		$this->load->language('upstore/theme');
		$translate = array(
			'January' => $this->language->get('text_January'),
			'February' => $this->language->get('text_February'),
			'March' => $this->language->get('text_March'),
			'April' => $this->language->get('text_April'),
			'May' => $this->language->get('text_May'),
			'June' => $this->language->get('text_June'),
			'July' => $this->language->get('text_July'),
			'August' => $this->language->get('text_August'),
			'September' => $this->language->get('text_September'),
			'October' => $this->language->get('text_October'),
			'November' => $this->language->get('text_November'),
			'December' => $this->language->get('text_December'),
		);

		return strtr(date($date_format, $date_added), $translate);
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'image'            => $query->row['image'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'tax_class_id'     => $query->row['tax_class_id'],
			);
		} else {
			return false;
		}
	}

	public function getTotalPercentProductReviewsById($product_id) {
		$query = $this->db->query("SELECT
			TRUNCATE(SUM(IF(r.rating=1,1,0)) * 100 / COUNT(r.rating),0) AS star1,
			SUM(IF(r.rating=1,1,0)) AS total_s1,
			TRUNCATE(SUM(IF(r.rating=2,1,0)) * 100 / COUNT(r.rating),0) AS star2,
			SUM(IF(r.rating=2,1,0)) AS total_s2,
			TRUNCATE(SUM(IF(r.rating=3,1,0)) * 100 / COUNT(r.rating),0) AS star3,
			SUM(IF(r.rating=3,1,0)) AS total_s3,
			TRUNCATE(SUM(IF(r.rating=4,1,0)) * 100 / COUNT(r.rating),0) AS star4,
			SUM(IF(r.rating=4,1,0)) AS total_s4,
			TRUNCATE(SUM(IF(r.rating=5,1,0)) * 100 / COUNT(r.rating),0) AS star5,
			SUM(IF(r.rating=5,1,0)) AS total_s5
			FROM " . DB_PREFIX . "review r WHERE r.status = '1' AND product_id='". (int)$product_id ."'");

		$rating_info = array();

		foreach($query->rows as $result){
			$rating_info = array(
				'1' => array('pct' => (!empty($result['star1']) ? $result['star1'] : 0),'total' => (!empty($result['total_s1']) ? $result['total_s1'] : 0)),
				'2' => array('pct' => (!empty($result['star2']) ? $result['star2'] : 0),'total' => (!empty($result['total_s2']) ? $result['total_s2'] : 0)),
				'3' => array('pct' => (!empty($result['star3']) ? $result['star3'] : 0),'total' => (!empty($result['total_s3']) ? $result['total_s3'] : 0)),
				'4' => array('pct' => (!empty($result['star4']) ? $result['star4'] : 0),'total' => (!empty($result['total_s4']) ? $result['total_s4'] : 0)),
				'5' => array('pct' => (!empty($result['star5']) ? $result['star5'] : 0),'total' => (!empty($result['total_s5']) ? $result['total_s5'] : 0)),

			);
		}

		return $rating_info;
	}

	public function multi_implode($glue, $array) {
		$_array=array();
		foreach($array as $val){
			if(isset($val['product_id'])){
				$_array[] = is_array($val['product_id']) ? $this->multi_implode($glue, $val['product_id']) : $val['product_id'];
			} else {
				$_array[] = is_array($val) ? $this->multi_implode($glue, $val) : $val;
			}
		}
		return implode($glue, $_array);
	}
	public function getPath($productid) {
			$sql = "SELECT p2c.category_id as category_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id = ".(int)$productid.")";
			$sql .= " WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			$sql .= " AND p2c.product_id = '" . (int)$productid . "'";
			$sql .= " GROUP BY p2c.category_id";
			$sql .= " LIMIT 1";
			$query = $this->db->query($sql);
			if(isset($query->row['category_id'])){
				return $query->row['category_id'];
			} else {
				return 0;
			}

	}
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
	public function getPrevNextProduct($productid, $category_id) {
			if ($this->customer->isLogged()) {
			  $customer_group_id = $this->customer->getGroupId();
			} else {
			  $customer_group_id = $this->config->get('config_customer_group_id');
			}
			if (VERSION >= 2.2) {
				$currency = $this->session->data['currency'];
			} else {
				$currency = '';
			}
			$product_data = array();
			$path = $this->getCategoryPath($category_id);
			$sql_next = "SELECT p2c.product_id,p2c.category_id, pd.name,p.image,p.tax_class_id,p.price, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM ". DB_PREFIX . "product_to_category p2c
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id = p2c.product_id)";
			$sql_next .= " LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = p2c.product_id) ";
			$sql_next .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) ";
			$sql_next .= " WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			$sql_next .= " AND p2c.category_id = '" . (int)$category_id . "'";
			$sql_next .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql_next .= " AND p2c.product_id > '" . (int)$productid . "' ORDER BY p2c.product_id LIMIT 1";


			$query_next = $this->db->query($sql_next);

			if($query_next->row) {
				$product_data['next']['name'] = $query_next->row['name'];
				$product_data['next']['image'] = isset($query_next->row['image']) ? $this->model_tool_image->resize($query_next->row['image'], 100, 100) : $this->model_tool_image->resize('no_image.png', 100, 100);
				$product_data['next']['product_id'] = $query_next->row['product_id'];
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$product_data['next']['price'] = $this->currency->format($this->tax->calculate($query_next->row['price'], $query_next->row['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$product_data['next']['price'] = false;
				}

				if ((float)$query_next->row['special']) {
					$product_data['next']['special'] = $this->currency->format($this->tax->calculate($query_next->row['special'], $query_next->row['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$product_data['next']['special'] = false;
				}
				$product_data['next']['href']= $this->url->link('product/product','path=' . $path . '&product_id=' . $query_next->row['product_id']);
			}

			$sql_prev = "SELECT p2c.product_id,p2c.category_id, pd.name,p.image,p.tax_class_id,p.price, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM ". DB_PREFIX . "product_to_category p2c
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p2s.product_id = p2c.product_id)";
			$sql_prev .= " LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = p2c.product_id) ";
			$sql_prev .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) ";
			$sql_prev .= " WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			$sql_prev .= " AND p2c.category_id = '" . (int)$category_id . "'";
			$sql_prev .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql_prev .= " AND p2c.product_id < '" . (int)$productid . "'  ORDER BY p2c.product_id DESC LIMIT 1";

			$query_prev = $this->db->query($sql_prev);

			if($query_prev->row) {
				$product_data['prev']['name'] = $query_prev->row['name'];
				$product_data['prev']['image'] = isset($query_prev->row['image']) ? $this->model_tool_image->resize($query_prev->row['image'], 100, 100) : $this->model_tool_image->resize('no_image.png', 100, 100);
				$product_data['prev']['product_id'] = $query_prev->row['product_id'];
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$product_data['prev']['price'] = $this->currency->format($this->tax->calculate($query_prev->row['price'], $query_prev->row['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$product_data['prev']['price'] = false;
				}

				if ((float)$query_prev->row['special']) {
					$product_data['prev']['special'] = $this->currency->format($this->tax->calculate($query_prev->row['special'], $query_prev->row['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$product_data['prev']['special'] = false;
				}
				$product_data['prev']['href']= $this->url->link('product/product','path=' . $path . '&product_id=' . $query_prev->row['product_id']);
			}
		return $product_data;
	}

	public function getLatest($data = array()) {
		$cache_key = 'product.ns_latest_grid.' . (int)$data['filter_category_id'] . '.' . (int)$data['filter_sub_category'] . '.'. (int)$data['limit_max'] . '.' . (int)$data['start'] . '.'. (int)$data['limit'] . '.'. (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id');

		$product_data = $this->cache->get($cache_key);

		if (is_array($product_data)) {
			return $product_data;
		}

		if (!$product_data) {
			$sql = "SELECT p.product_id, p.image, p.name, p.quantity, p.price, p.date_added FROM (SELECT p.product_id, p.image, pd.name, p.quantity, p.price, p.date_added, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

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

			$sql .= " ORDER BY p.date_added DESC";
			$sql .= " LIMIT  0, " . (int)$data['limit_max'];
			$sql .= ") p ORDER BY p.date_added DESC";

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

	public function productRatingStars($rating) {
		$full_stars = floor($rating);
		$remainder = $rating - $full_stars;

		$stars = [];

		for ($i = 0; $i < 5; $i++) {
			if ($i < $full_stars) {
				$stars[$i] = 100;
			} elseif ($i == $full_stars) {
				$stars[$i] = $remainder * 100;
			} else {
				$stars[$i] = 0;
			}
		}
		return $stars;
	}
}
?>
