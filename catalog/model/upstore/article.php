<?php
class ModelUpstoreArticle extends Model {
	public function updateViewedArticle($article_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "ch_news_articles SET viewed = (viewed + 1) WHERE article_id = '" . (int)$article_id . "'");
	}
	public function getProductFeatured($article_id) {
		$this->load->model('catalog/product');
		$product_data = array();

		$query = $this->db->query("SELECT `featured_product` FROM " . DB_PREFIX . "ch_news_articles WHERE article_id = '" . (int)$article_id . "'");

		if(!empty($query->row['featured_product'])){
			$products = json_decode($query->row['featured_product'], true);


			foreach ($products as $product_id) {
				$product_data[] = $this->model_catalog_product->getProduct($product_id);
			}
		}
		return $product_data;
	}
	public function getArticle($article_id) {

		$query = $this->db->query("SELECT *,
		(SELECT COUNT(*) FROM " . DB_PREFIX . "ch_article_comment ac2 WHERE ac2.article_id = na.article_id AND ac2.status = '1' GROUP BY ac2.article_id) AS comments
		FROM " . DB_PREFIX . "ch_news_articles na
		LEFT JOIN " . DB_PREFIX . "ch_news_articles_description nad ON (na.article_id = nad.article_id)
		LEFT JOIN " . DB_PREFIX . "ch_news_articles_to_store na2s ON (na.article_id = na2s.article_id)

		WHERE nad.language_id = '" . (int)$this->config->get('config_language_id') . "'
		AND na.status = '1'
		AND na.article_id = '" . (int)$article_id . "'
		AND na2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}
	public function addArticleComment($article_id, $data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "ch_article_comment SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', article_id = '" . (int)$article_id . "', text = '" . $this->db->escape($data['text']) . "', date_added = NOW()");

	}

	public function getCommentsByArticleId($article_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT ac.article_comment_id, ac.author, ac.dislike, ac.like, ac.admin_reply, ac.text, na.article_id, nad.name, ac.date_added FROM " . DB_PREFIX . "ch_article_comment ac
			LEFT JOIN " . DB_PREFIX . "ch_news_articles na ON (ac.article_id = na.article_id)
			LEFT JOIN " . DB_PREFIX . "ch_news_articles_description nad ON (na.article_id = nad.article_id)
			WHERE na.article_id = '" . (int)$article_id . "'
			AND na.date_added <= NOW()
			AND na.status = '1'
			AND ac.status = '1'
			AND nad.language_id = '" . (int)$this->config->get('config_language_id') . "'
			ORDER BY ac.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalCommentsByArticleId($article_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ch_article_comment ac
			LEFT JOIN " . DB_PREFIX . "ch_news_articles na ON (ac.article_id = na.article_id)
			LEFT JOIN " . DB_PREFIX . "ch_news_articles_description nad ON (na.article_id = nad.article_id)
			WHERE na.article_id = '" . (int)$article_id . "'
			AND na.date_added <= NOW()
			AND na.status = '1'
			AND ac.status = '1'
			AND nad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
	public function getLikeDislike($article_comment_id) {
		$sql = "SELECT ac.like,ac.dislike FROM " . DB_PREFIX . "ch_article_comment ac WHERE `status` = '1' AND ac.article_comment_id='" . (int)$article_comment_id . "'";

		$query = $this->db->query($sql);

		return $query->row;
	}
	public function addLikeDislike($data) {
		$getld = $this->getLikeDislike($data['article_comment_id']);

		$like = $getld['like'] + $data['like'];

		$dislike = $getld['dislike'] + $data['dislike'];

		$this->db->query("UPDATE " . DB_PREFIX . "ch_article_comment SET
			`like` = '" . (int)$like . "',
			`dislike` = '" . (int)$dislike . "'
			 WHERE article_comment_id = '" . (int)$data['article_comment_id'] . "'");


		return $data['article_comment_id'];
	}
}