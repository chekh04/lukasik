<?php
class ModelExtensionModuleCouponByManufacturer extends Model {

    public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "coupon_manufacturer` (
			`coupon_id` int(11) NOT NULL,
			`manufacturer_id` int(11) NOT NULL,
			PRIMARY KEY (`coupon_id`,`manufacturer_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
    }
	public function uninstall() {
		$sql = "DROP TABLE IF EXISTS `" . DB_PREFIX . "coupon_manufacturer`";
		$this->db->query($sql);
	}


	public function deleteCouponManufacturers($coupon_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id = '" . (int)$coupon_id . "'");
	}

	public function addCouponManufacturers($coupon_id, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id = '" . (int)$coupon_id . "'");

		if (isset($data['coupon_manufacturer'])) {
			foreach ($data['coupon_manufacturer'] as $manufacturer_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_manufacturer SET coupon_id = '" . (int)$coupon_id . "', manufacturer_id = '" . (int)$manufacturer_id . "'");
			}
		}
	}

	public function getCouponManufacturers($coupon_id) {
		$coupon_manufacturer_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_manufacturer_data[] = $result['manufacturer_id'];
		}

		return $coupon_manufacturer_data;
	}

}