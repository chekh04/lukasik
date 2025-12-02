<?php
class ModelExtensionModuleUpstoreProSticker extends Model {
	private $lang_id;
	private $settings;
	private $dir_image;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->lang_id = $this->config->get('config_language_id');
		$this->settings = $this->getSettings();
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->dir_image = $this->config->get('config_ssl') . 'image/';
		} else {
			$this->dir_image = $this->config->get('config_url') . 'image/';
		}
	}

	public function getProStickers($product_id, $type = 'module_page') {
		$this->load->model('tool/image');

		$lang_id = $this->lang_id;
		$dir_image = $this->dir_image;
		$settings = $this->settings;

		$sticker_info = [];
		$sticker_info['product_page']['items'] = [];
		$sticker_info['module_page']['items'] = [];
		$sticker_info['status'] = false;

		if(!$settings['pro_sticker_status']){
			return $sticker_info;
		}

		$results = $this->getStickerInfo($product_id);

		if($results){

			$positions_status = ['topleft' => 1, 'topcenter' => 1, 'topright' => 1, 'centerleft' => 1, 'centercenter' => 1, 'centerright' => 1, 'bottomleft' => 1, 'bottomcenter' => 1, 'bottomright' => 1];

			/*Special*/
			if ($settings['special_status'] && ($settings['special_stiker_design'] == '0') && $results['special'] && is_file(DIR_IMAGE . $settings['special_image'])) {

				$special_sticker_data = array(
					'type'		=> 'image',
					'width'		=> $settings['special_img_width'],
					'height'		=> $settings['special_img_height'],
					'pos' 		=> $settings['special_position'],
					'image' 		=> $dir_image . $settings['special_image']
				);

				$sticker_info['module_page']['items'][$settings['special_position']][] = $special_sticker_data;

				if (isset($settings['special_view']) && ($settings['special_view'] == 1)) {
					$special_sticker_data_topright = $special_sticker_data;
					$special_sticker_data_topright['pos'] = 'topright';

					$sticker_info['product_page']['items']['topright'][] = $special_sticker_data_topright;
				} else {
					$sticker_info['product_page']['block'][] = $special_sticker_data;
				}
			}

			if ($settings['special_status'] && ($settings['special_stiker_design'] == '1') && $results['special']) {
				$text_label_ss = $settings['special_text_sticker'][$lang_id];
				$text_color_ss = $settings['special_text_color'][$lang_id];
				$text_bg_color_ss = $settings['special_bg_color'][$lang_id];

				if(isset($text_label_ss) && (!empty($text_label_ss))) {

					$special_text_label_data = array(
						'type'	=> 'label',
						'text'	=> $text_label_ss,
						'color'	=> $text_color_ss,
						'bg'		=> $text_bg_color_ss,
						'pos' 	=> $settings['special_position'],
					);

					$sticker_info['module_page']['items'][$settings['special_position']][] = $special_text_label_data;

					if (isset($settings['special_view']) && ($settings['special_view'] == 1)) {
						$special_text_label_data_topright = $special_text_label_data;
						$special_text_label_data_topright['pos'] = 'topright';

						$sticker_info['product_page']['items']['topright'][] = $special_text_label_data_topright;
					}
				}



				$skidka = false;
				if ($settings['special_status_dp'] && (float)$results['special']) {
					$price2 = $this->tax->calculate($results['price'], $results['tax_class_id'], $this->config->get('config_tax'));
					$special2 = $this->tax->calculate($results['special'], $results['tax_class_id'], $this->config->get('config_tax'));
					if($price2 != $special2){
						$skidka = round($special2/($price2/100)-100).'%';
					}
				} else {
					$skidka = false;
				}

				if($skidka){
					$special_dp_color = $settings['special_dp_color'][$lang_id];
					$special_dp_bg_color = $settings['special_dp_bg_color'][$lang_id];

					$special_skidka_data = array(
						'type'	=> 'label',
						'text'	=> $skidka,
						'color' 	=> $special_dp_color,
						'bg'		=> $special_dp_bg_color,
						'pos' 	=> $settings['special_position'],
					);

					$sticker_info['module_page']['items'][$settings['special_position']][] = $special_skidka_data;

					if (isset($settings['special_view']) && ($settings['special_view'] == 1)) {
						$special_skidka_data_topright = $special_skidka_data;
						$special_skidka_data_topright['pos'] = 'topright';

						$sticker_info['product_page']['items']['topright'][] = $special_skidka_data_topright;
					}
				}
			}

			/*New*/
			if ($settings['new_status'] && ($settings['new_stiker_design'] == '0') && is_file(DIR_IMAGE . $settings['new_image'])) {
				$time = explode('-', $results['date_available']);

				if (time() < (mktime (0, 0, 0, $time[1], $time[2], $time[0]) + ($settings['days_new'] * 86400))) {

					$new_sticker_data = array(
						'type'		=> 'image',
						'width'		=> $settings['new_img_width'],
						'height'		=> $settings['new_img_height'],
						'pos' 		=> $settings['new_position'],
						'image' 		=> $dir_image . $settings['new_image']
					);

					$sticker_info['module_page']['items'][$settings['new_position']][] = $new_sticker_data;

					if (isset($settings['new_view']) && ($settings['new_view'] == 1)) {
						$new_sticker_data_topright = $new_sticker_data;
						$new_sticker_data_topright['pos'] = 'topright';

						$sticker_info['product_page']['items']['topright'][] = $new_sticker_data_topright;
					} else {
						$sticker_info['product_page']['block'][] = $sticker_info['module_page']['items'][$settings['new_position']];
					}
				}
			}

			if ($settings['new_status'] && ($settings['new_stiker_design'] == '1')) {
				$text_label_ns = $settings['new_text_sticker'][$lang_id];
				$text_color_ns = $settings['new_text_color'][$lang_id];
				$text_bg_color_ns = $settings['new_bg_color'][$lang_id];

				$time = explode('-', $results['date_available']);
				if (time() < (mktime (0, 0, 0, $time[1], $time[2], $time[0]) + ($settings['days_new'] * 86400))) {
					if (isset($text_label_ns) && (!empty($text_label_ns))) {

						$new_text_label_data = array(
							'type'	=> 'label',
							'text'	=> $text_label_ns,
							'color'	=> $text_color_ns,
							'bg'		=> $text_bg_color_ns,
							'pos' 	=> $settings['new_position'],
						);

						$sticker_info['module_page']['items'][$settings['new_position']][] = $new_text_label_data;

						if (isset($settings['new_view']) && ($settings['new_view'] == 1)) {
							$new_text_label_data_topright = $new_text_label_data;
							$new_text_label_data_topright['pos'] = 'topright';

							$sticker_info['product_page']['items']['topright'][] = $new_text_label_data_topright;
						}

					}
				}
			}

			/*Bestseller*/
			if ($settings['bestseller_status'] && ($settings['bestseller_stiker_design'] == '0') && is_file(DIR_IMAGE . $settings['bestseller_image'])) {
				$bestsellers_total = $this->getTopSeller($product_id);
				if($bestsellers_total >= $settings['limit_order']) {

					$bestseller_sticker_data = array(
						'type'		=> 'image',
						'width'		=> $settings['bestseller_img_width'],
						'height'		=> $settings['bestseller_img_height'],
						'pos' 		=> $settings['bestseller_position'],
						'image' 		=> $dir_image . $settings['bestseller_image']
					);

					$sticker_info['module_page']['items'][$settings['bestseller_position']][] = $bestseller_sticker_data;

					if (isset($settings['bestseller_view']) && ($settings['bestseller_view'] == 1)) {
						$bestseller_sticker_data_topright = $bestseller_sticker_data;
						$bestseller_sticker_data_topright['pos'] = 'topright';

						$sticker_info['product_page']['items']['topright'][] = $bestseller_sticker_data_topright;
					} else {
						$sticker_info['product_page']['block'][] = $bestseller_sticker_data;
					}
				}
			}

			if ($settings['bestseller_status'] && ($settings['bestseller_stiker_design'] == '1')) {
				$text_label_bs = $settings['bestseller_text_sticker'][$lang_id];
				$text_color_bs = $settings['bestseller_text_color'][$lang_id];
				$text_bg_color_bs = $settings['bestseller_bg_color'][$lang_id];

				$bestsellers_total = $this->getTopSeller($product_id);
				if(($bestsellers_total >= $settings['limit_order'])) {
					if (isset($text_label_bs) && (!empty($text_label_bs))) {

						$bestseller_text_label_data = array(
							'type'	=> 'label',
							'text' 	=> $text_label_bs,
							'color'	=> $text_color_bs,
							'bg'	 	=> $text_bg_color_bs,
							'pos'		=> $settings['bestseller_position'],
						);

						$sticker_info['module_page']['items'][$settings['bestseller_position']][] = $bestseller_text_label_data;

						if (isset($settings['new_view']) && ($settings['new_view'] == 1)) {
							$bestseller_text_label_data_topright = $bestseller_text_label_data;
							$bestseller_text_label_data_topright['pos'] = 'topright';

							$sticker_info['product_page']['items']['topright'][] = $bestseller_text_label_data_topright;
						}

					}
				}
			}

			/*Viewed*/
			if ($settings['popular_status']  && ($settings['popular_stiker_design'] == '0') && is_file(DIR_IMAGE . $settings['popular_image'])) {
				$limit_viewed = $results['viewed'];
				if(($limit_viewed >= $settings['limit_viewed'])) {

					$popular_sticker_data = array(
						'type'		=> 'image',
						'width'		=> $settings['popular_img_width'],
						'height'		=> $settings['popular_img_height'],
						'pos' 		=> $settings['popular_position'],
						'image' 		=> $dir_image . $settings['popular_image']
					);

					$sticker_info['module_page']['items'][$settings['popular_position']][] = $popular_sticker_data;

					if (isset($settings['popular_view']) && ($settings['popular_view'] == 1)) {
						$popular_sticker_data_topright = $popular_sticker_data;
						$popular_sticker_data_topright['pos'] = 'topright';

						$sticker_info['product_page']['items']['topright'][] = $popular_sticker_data_topright;
					} else {
						$sticker_info['product_page']['block'][] = $popular_sticker_data;
					}
				}
			}

			if ($settings['popular_status'] && ($settings['popular_stiker_design'] == '1')) {
				$limit_viewed = $results['viewed'];
				if(($limit_viewed >= $settings['limit_viewed'])) {

					$text_label_ps = $settings['popular_text_sticker'][$lang_id];
					$text_color_ps = $settings['popular_text_color'][$lang_id];
					$text_bg_color_ps = $settings['popular_bg_color'][$lang_id];

					if (isset($text_label_ps) && (!empty($text_label_ps))) {

						$popular_text_label_data = array(
							'type'	=> 'label',
							'text' 	=> $text_label_ps,
							'color'	=> $text_color_ps,
							'bg'	 	=> $text_bg_color_ps,
							'pos'		=> $settings['popular_position'],
						);

						$sticker_info['module_page']['items'][$settings['popular_position']][] = $popular_text_label_data;

						if (isset($settings['popular_view']) && ($settings['popular_view'] == 1)) {
							$popular_text_label_data_topright = $popular_text_label_data;
							$popular_text_label_data_topright['pos'] = 'topright';

							$sticker_info['product_page']['items']['topright'][] = $popular_text_label_data_topright;
						}
					}
				}
			}

			/*Quantity*/
			if ($settings['quantity_status'] && $settings['quantity'] && ($settings['quantity_stiker_design'] == '0')) {
				foreach ($settings['quantity'] as $quantity) {
					if (($results['quantity'] >= $quantity['min']) && ($results['quantity'] <= $quantity['max']) && is_file(DIR_IMAGE . $quantity['image'])) {
						$sticker_info['module_page']['items'][$settings['quantity_position']][] = array(
							'type'		=> 'image',
							'view' 		=> '',
							'pos' 		=> $quantity['quantity_position'],
							'image' 		=> $dir_image . $quantity['image']
						);
						break;
					}
				}
			}

			if ($settings['quantity_status'] && $settings['quantity'] && ($settings['quantity_stiker_design'] == '1')) {
				foreach ($settings['quantity'] as $quantity) {

					if (($results['quantity'] >= $quantity['min']) && ($results['quantity'] <= $quantity['max'])) {
						$sticker_info['module_page']['items'][$settings['quantity_position']][] = array(
							'type'	=> 'label',
							'text' 	=> $quantity['quantity_text_sticker'][$lang_id],
							'color'	=> $quantity['quantity_text_color'][$lang_id],
							'bg'	 	=> $quantity['quantity_bg_color'][$lang_id],
							'pos'		=> $settings['quantity_position'],
						);
						break;
					}
				}
			}

			/*Brand*/
			if ($settings['manufacturer_status'] && is_file(DIR_IMAGE . $results['manufacturer'])) {

				$manufacturer_sticker_data = array(
					'type'		=> 'image',
					'width'		=> $settings['width'],
					'height'		=> $settings['height'],
					'view' 		=> '',
					'pos' 		=> $settings['manufacturer_position'],
					'image' 		=> $this->model_tool_image->resize($results['manufacturer'], $settings['width'], $settings['height'])
				);

				$sticker_info['module_page']['items'][$settings['manufacturer_position']][] = $manufacturer_sticker_data;

				$manufacturer_sticker_data_topright = $manufacturer_sticker_data;
				$manufacturer_sticker_data_topright['pos'] = 'topright';

				$sticker_info['product_page']['items']['topright'][] = $manufacturer_sticker_data_topright;
			}

			/*Gift*/
			if($settings['gift_status'] && $results['gift_status']){
				if (($settings['gift_stiker_design'] == '0') && is_file(DIR_IMAGE . $settings['gift_image'])) {

					$gift_sticker_data = array(
						'type'		=> 'image',
						'width'		=> $settings['gift_img_width'],
						'height'		=> $settings['gift_img_height'],
						'pos' 		=> $settings['gift_position'],
						'image' 		=> $dir_image . $settings['gift_image']
					);

					$sticker_info['module_page']['items'][$settings['gift_position']][] = $gift_sticker_data;

					if (isset($settings['gift_view']) && ($settings['gift_view'] == 1)) {
						$gift_sticker_data_topright = $gift_sticker_data;
						$gift_sticker_data_topright['pos'] = 'topright';

						$sticker_info['product_page']['items']['topright'][] = $gift_sticker_data_topright;
					} else {
						$sticker_info['product_page']['block'][] = $gift_sticker_data;
					}
				}

				if ($settings['gift_stiker_design'] == '1') {
					$text_label_gs = $settings['gift_text_sticker'][$lang_id];
					$text_color_gs = $settings['gift_text_color'][$lang_id];
					$text_bg_color_gs = $settings['gift_bg_color'][$lang_id];

					if (isset($text_label_gs) && (!empty($text_label_gs))) {

						$gift_text_label_data = array(
							'type'	=> 'label',
							'text' 	=> $text_label_gs,
							'color'	=> $text_color_gs,
							'bg'	 	=> $text_bg_color_gs,
							'pos'		=> $settings['gift_position'],
						);

						$sticker_info['module_page']['items'][$settings['gift_position']][] = $gift_text_label_data;

						if (isset($settings['gift_view']) && ($settings['gift_view'] == 1)) {
							$gift_text_label_data_topright = $gift_text_label_data;
							$gift_text_label_data_topright['pos'] = 'topright';

							$sticker_info['product_page']['items']['topright'][] = $gift_text_label_data_topright;
						}
					}
				}
			}


			/*Custom Stiker*/

			if ((date('Y-m-d') >= $results['date_start_sticker']) && (date('Y-m-d') <= $results['date_end_sticker']) || (($results['date_start_sticker'] == '0000-00-00') && ($results['date_end_sticker'] == '0000-00-00'))) {

				if(!empty($results['custom_sticker'])){

					foreach($results['custom_sticker'] as $custom_sticker){

						$custom_images = array();
						if (!empty($custom_sticker['images'])) {
							$decoded_images = json_decode($custom_sticker['images'], true);
							$custom_images = isset($decoded_images[$lang_id]) ? $decoded_images[$lang_id] : array();
						}

						$images_size = array();
						if (!empty($custom_sticker['images_size'])) {
							$decoded_images_size = json_decode($custom_sticker['images_size'], true);
							$images_size = isset($decoded_images_size[$lang_id]) ? $decoded_images_size[$lang_id] : array();
						}

						$popover_text = array();
						if (!empty($custom_sticker['popover_text'])) {
							$decoded_popover_text = json_decode($custom_sticker['popover_text'], true);
							$popover_text = isset($decoded_popover_text[$lang_id]) ? $decoded_popover_text[$lang_id] : array();
						}

						$text_label = array();
						if (!empty($custom_sticker['text_label'])) {
							$decoded_text_label = json_decode($custom_sticker['text_label'], true);
							$text_label = isset($decoded_text_label[$lang_id]) ? $decoded_text_label[$lang_id] : array();
						}

						$product_page_status = !empty($custom_sticker['product_page_status']) ? $custom_sticker['product_page_status'] : false;
						$product_page_view = !empty($custom_sticker['product_page_view']) ? $custom_sticker['product_page_view'] : 'image';
						$module_page_status = !empty($custom_sticker['module_page_status']) ? $custom_sticker['module_page_status'] : false;
						$module_page_view = !empty($custom_sticker['module_page_view']) ? $custom_sticker['module_page_view'] : 'image';

						$product_page_condition_image = $product_page_status && $product_page_view == 'image';
						$module_page_condition_image = $module_page_status && $module_page_view == 'image';

						$product_page_condition_block = $product_page_status && $product_page_view == 'block';
						$module_page_condition_block = $module_page_status && $module_page_view == 'block';


						$positions = array('topleft','topcenter','topright','centerleft','centercenter','centerright','bottomleft','bottomcenter','bottomright');

						if (!empty($custom_images)) {
							if ($product_page_status) {
								if ($product_page_view == 'image') {
									foreach ($positions as $position) {
										if (!empty($custom_images[$position]) && is_file(DIR_IMAGE . $custom_images[$position])) {
											$sticker_info['product_page']['items'][$position][] = array(
												'type'      => 'image',
												'width'     => !empty($images_size[$position]['width']) ? $images_size[$position]['width'] : 30,
												'height'    => !empty($images_size[$position]['height']) ? $images_size[$position]['height'] : 30,
												'pos'       => $position,
												'popover'   => !empty($popover_text[$position]) ? $popover_text[$position] : '',
												'image'     => $dir_image . $custom_images[$position],
												'condition' => $product_page_condition_image,
											);
										}
									}
								} elseif ($product_page_view == 'block') {
									foreach ($positions as $position) {
										if (!empty($custom_images[$position]) && is_file(DIR_IMAGE . $custom_images[$position])) {
											$sticker_info['product_page']['block'][] = array(
												'type'      => 'image',
												'width'     => !empty($images_size[$position]['width']) ? $images_size[$position]['width'] : 30,
												'height'    => !empty($images_size[$position]['height']) ? $images_size[$position]['height'] : 30,
												'popover'   => !empty($popover_text[$position]) ? $popover_text[$position] : '',
												'image'     => $dir_image . $custom_images[$position],
												'condition' => $product_page_condition_block,
											);
										}
									}
								}
							}

							if ($module_page_status) {
								if ($module_page_view == 'image') {
									foreach ($positions as $position) {
										if (!empty($custom_images[$position]) && is_file(DIR_IMAGE . $custom_images[$position])) {
											$sticker_info['module_page']['items'][$position][] = array(
												'type'      => 'image',
												'width'     => !empty($images_size[$position]['width']) ? $images_size[$position]['width'] : 30,
												'height'    => !empty($images_size[$position]['height']) ? $images_size[$position]['height'] : 30,
												'pos'       => $position,
												'popover'   => !empty($popover_text[$position]) ? $popover_text[$position] : '',
												'image'     => $dir_image . $custom_images[$position],
												'condition' => $module_page_condition_image,
											);
										}
									}
								} elseif ($module_page_view == 'block') {
									foreach ($positions as $position) {
										if (!empty($custom_images[$position]) && is_file(DIR_IMAGE . $custom_images[$position])) {
											$sticker_info['module_page']['block'][] = array(
												'type'      => 'image',
												'width'     => !empty($images_size[$position]['width']) ? $images_size[$position]['width'] : 30,
												'height'    => !empty($images_size[$position]['height']) ? $images_size[$position]['height'] : 30,
												'popover'   => !empty($popover_text[$position]) ? $popover_text[$position] : '',
												'image'     => $dir_image . $custom_images[$position],
												'condition' => $module_page_condition_block,
											);
										}
									}
								}
							}
						}

						if (!empty($text_label)) {
							if ($product_page_status) {
								if ($product_page_view == 'image') {
									foreach ($text_label as $label) {
										$sticker_info['product_page']['items'][$label['position']][] = array(
											'type'  => 'label',
											'text'  => $label['text'],
											'color' => $label['text_color'],
											'bg'    => $label['bg_color'],
											'pos'   => $label['position'],
											'condition' => $product_page_condition_image,
										);
									}
								} elseif ($product_page_view == 'block') {
									foreach ($text_label as $label) {
										$sticker_info['product_page']['block'][] = array(
											'type'  => 'label',
											'text'  => $label['text'],
											'color' => $label['text_color'],
											'bg'    => $label['bg_color'],
											'condition' => $product_page_condition_block,
										);
									}
								}
							}

							if ($module_page_status) {
								if ($module_page_view == 'image') {
									foreach ($text_label as $label) {
										$sticker_info['module_page']['items'][$label['position']][] = array(
											'type'  => 'label',
											'text'  => $label['text'],
											'color' => $label['text_color'],
											'bg'    => $label['bg_color'],
											'pos'   => $label['position'],
											'condition' => $module_page_condition_image,
										);
									}
								} elseif ($module_page_view == 'block') {
									foreach ($text_label as $label) {
										$sticker_info['module_page']['block'][] = array(
											'type'  => 'label',
											'text'  => $label['text'],
											'color' => $label['text_color'],
											'bg'    => $label['bg_color'],
											'condition' => $module_page_condition_block,
										);
									}
								}
							}
						}
					}
				}
			}
		}

		return [
			'status' => $settings['pro_sticker_status'],
			'hide_hover' => $settings['hide_hover'],
			'items' => isset($sticker_info[$type]['items']) ? $sticker_info[$type]['items'] : [],
			'block' => isset($sticker_info[$type]['block']) ? $sticker_info[$type]['block'] : [],
		];

	}

	private function getStickerInfo($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$query = $this->db->query('
			SELECT
				p.product_id AS product_id,
				p.sticker_id,
				p.price AS price,
				p.tax_class_id AS tax_class_id,
				p.viewed AS viewed,
				p.date_available AS date_available,
				p.quantity AS quantity,
				p.date_start_sticker AS date_start_sticker,
				p.date_end_sticker AS date_end_sticker,
				m.image AS manufacturer,
				ps.price AS special,
				IF(pg.status = 1 AND TRIM(pg.products) !="", 1, 0) AS gift_status
			FROM
				' . DB_PREFIX . 'product p
			LEFT JOIN
				' . DB_PREFIX . 'manufacturer m ON (m.manufacturer_id = p.manufacturer_id)
			LEFT JOIN
				' . DB_PREFIX . 'product_special ps ON (
				ps.product_id = p.product_id
				AND ps.customer_group_id = "' . (int)$customer_group_id . '"
				AND (
					(ps.date_start = "0000-00-00" OR ps.date_start < NOW()) AND (ps.date_end = "0000-00-00" OR ps.date_end > NOW()))
				)
			LEFT JOIN
				' . DB_PREFIX . 'product_gifts pg ON FIND_IN_SET(pg.product_id, p.product_id)
			WHERE
				p.product_id = "'. (int)$product_id .'"
				AND p.date_available <= NOW()
				AND p.status = "1"
			ORDER BY
				ps.priority DESC,
				ps.price DESC
		');



		if ($query->num_rows) {

			$sticker_images_info = [];

			if(!empty($query->row['sticker_id'])){
				$sticker_images_info = $this->getStickers($query->row['sticker_id']);
			}

			return array(
				'product_id'			=> $query->row['product_id'],
				'custom_sticker'		=> $sticker_images_info,
				'quantity'				=> $query->row['quantity'],
				'manufacturer'			=> $query->row['manufacturer'],
				'special'				=> $query->row['special'],
				'price'					=> $query->row['price'],
				'tax_class_id'			=> $query->row['tax_class_id'],
				'date_available'		=> $query->row['date_available'],
				'viewed'					=> $query->row['viewed'],
				'date_start_sticker'	=> $query->row['date_start_sticker'],
				'date_end_sticker'	=> $query->row['date_end_sticker'],
				'gift_status'			=> $query->row['gift_status'],
				'viewed'					=> $query->row['viewed']
			);

		} else {
			return false;
		}
	}

	private function getStickers($stickers_id) {
		if(!empty($stickers_id)){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "stickers_list WHERE sticker_id IN (" . $this->db->escape($stickers_id) . ") ORDER BY sort_order ASC");

			return $query->rows;
		}
		return false;
	}

   private function getTopSeller($product_id) {
		$query = $this->db->query("SELECT SUM(quantity) AS total FROM " . DB_PREFIX . "order_product op WHERE op.product_id = '". (int)$product_id ."'");
		return ($query->row['total'] ? $query->row['total'] : 0);
	}

	private function getSettings() {
		$settings = $this->config->get('module_upstore_pro_sticker_settings');
		$default = array (
			'pro_sticker_status' => $this->config->get('module_upstore_pro_sticker_status') !== null ? $this->config->get('module_upstore_pro_sticker_status') : 0,
			'class' => '.image',
			'class_main_image' => '#image-box .slider-main-img',
			'min_width' => 40,
			'min_height' => 40,
			'hide_hover' => 1,
			'show_effect' => 0,
			'z_index' => 0,
			'width' => 40,
			'height' => 40,
			'days_new' => 30,
			'gift_status' => 0,
			'gift_position' => 'bottomleft',
			'gift_image' => '',
			'gift_text_sticker' => '',
			'gift_text_color' => '',
			'gift_bg_color' => '',
			'gift_stiker_design' => 0,
			'gift_img_width' => 32,
			'gift_img_height' => 32,
			'popular_status' => 0,
			'popular_position' => 'bottomleft',
			'popular_image' => '',
			'popular_text_sticker' => '',
			'popular_text_color' => '',
			'popular_bg_color' => '',
			'popular_stiker_design' => 0,
			'popular_view' => 0,
			'popular_img_width' => 32,
			'popular_img_height' => 32,
			'limit_viewed' => 15,
			'bestseller_status' => 0,
			'bestseller_position' => 'topright',
			'bestseller_image' => '',
			'bestseller_text_sticker' => '',
			'bestseller_text_color' => '',
			'bestseller_bg_color' => '',
			'bestseller_stiker_design' => 0,
			'bestseller_view' => 0,
			'bestseller_img_width' => 32,
			'bestseller_img_height' => 32,
			'limit_order' => 10,
			'special_status' => 0,
			'special_position' => 'topleft',
			'special_image' => 'catalog/stickers_icon/special_top_left.png',
			'special_text_sticker' => '',
			'special_text_color' => '',
			'special_bg_color' => '',
			'special_stiker_design' => 0,
			'special_status_dp' => 0,
			'special_dp_color' => 0,
			'special_dp_bg_color' => 0,
			'special_view' => 0,
			'special_img_width' => 32,
			'special_img_height' => 32,
			'new_status' => 0,
			'new_position' => 'bottomright',
			'new_image' => 'catalog/stickers_icon/new_bottom_right.png',
			'new_text_sticker' => '',
			'new_text_color' => '',
			'new_bg_color' => '',
			'new_stiker_design' => 0,
			'new_view' => 0,
			'new_img_width' => 32,
			'new_img_height' => 32,
			'quantity_status' => 0,
			'quantity_stiker_design' => 0,
			'quantity_position' => 'topright',
			'manufacturer_status' => 0,
			'manufacturer_position' => 'bottomleft',
			'quantity' => array(array('min' => -10, 'max' => 0, 'image' => ''), array ('min' => 1, 'max' => 50, 'image' => ''), array ('min' => 51, 'max' => 100, 'image' => ''), array ('min' => 101, 'max' => 150, 'image' => ''), array ('min' => 151, 'max' => 200, 'image' => ''), array ('min' => 201, 'max' => 1000, 'image' => '')));

		foreach ($default as $setting=>$value) {
			if (!isset($settings[$setting])) {
				$settings[$setting] = $value;
			}
		}

		return $settings;
	}
}