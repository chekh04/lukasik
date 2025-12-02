<?php
class ModelExtensionModuleUpstoreProductVideo extends Model {

	public function extractYouTubeId($url) {
		$regExp = '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/|.*[?&]v=))([a-zA-Z0-9_-]{11})/';
		preg_match($regExp, $url, $matches);
		return isset($matches[1]) ? $matches[1] : false;
	}

	public function getProductVideos($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_video` WHERE `product_id` = '" . (int)$product_id . "' ORDER BY `sort_order` ASC");

		return $query->rows;
	}

	public function createVideoPreview($video_id) {

		$dir = DIR_IMAGE . 'catalog/video_previews/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$url = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';

		$path = $dir . $video_id . '.jpg';

		$ch = curl_init($url);
		$fp = fopen($path, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		if (file_exists($path)) {
			$img_video = imagecreatefromjpeg($path);
			$img_video_crop = imagecrop($img_video, ['x' => 0, 'y' => 45, 'width' => 480, 'height' => 270]);

			if ($img_video_crop !== FALSE) {
				imagejpeg($img_video_crop, $path, 90);
				imagedestroy($img_video_crop);
			}

			imagedestroy($img_video);
		}
	}

	public function getProductsVideos($product_id, $main_video) {
		$product_video_setting = $this->config->get('upstore_product_video_setting');
		$popup_display_mode = isset($product_video_setting['popup_display_mode']) ? $product_video_setting['popup_display_mode'] : 'only_main';
		$video_links = [];

		if (!empty($main_video)) {
			$video_id = $this->extractYouTubeId($main_video);
			if ($video_id) {
				$video_links[] = $video_id;
			} else {
				$video_links[] = $main_video;
			}
		}

		if($popup_display_mode != 'only_main'){
			$additional_videos = $this->getProductVideos($product_id);

			foreach ($additional_videos as $video) {
				$video_id = $this->extractYouTubeId($video['video_link']);
				if ($video_id) {
					$video_links[] = $video_id;
				} else {
					$video_links[] = $video['video_link'];
				}
			}
		}

		if(!empty($video_links)){
			return json_encode($video_links);
		}


	}

}
