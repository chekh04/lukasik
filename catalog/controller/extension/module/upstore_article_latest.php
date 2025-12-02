<?php
class ControllerExtensionModuleUpstoreArticleLatest extends Controller {
	public function index($setting) {
		if($this->config->get('upstore_news_status')){
		$this->load->model('upstore/category');
		$this->load->model('upstore/theme');
		static $module = 0;
		$data['nst_data'] = $this->config->get('nst_data');
        if(isset($data['nst_data']['lazyload_module']) && ($data['nst_data']['lazyload_module'] == 1)){
            $data['lazyload_module'] = true;
            if (isset($data['nst_data']['lazyload_image']) && ($data['nst_data']['lazyload_image'] !='')) {
                $data['lazy_image'] = 'image/' . $data['nst_data']['lazyload_image'];
            } else {
                $data['lazy_image'] = 'image/catalog/lazyload/lazyload1px.png';
            }
        } else {
            $data['lazyload_module'] = false;
        }
		$this->load->model('upstore/article');

		$this->load->model('tool/image');

		$data['articles'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}
		if (empty($setting['art_desc_length'])) {
			$setting['art_desc_length'] = 220;
		}
		$data['title'] = $setting['title'][$this->config->get('config_language_id')];
		$data['desctiption_status'] = $setting['desctiption_status'];

		$filter_data = array(
			'sort'               => 'date_added',
			'order'              => 'DESC',
			'limit'              => $setting['limit']
		);

		$data['all_rating_reviews_status'] = (!empty($this->config->get('all_rating_reviews_status')) ? $this->config->get('all_rating_reviews_status') : 0);
		$data['article_review_status'] = (!empty($this->config->get('article_review_status')) ? $this->config->get('article_review_status') : 0);
		$results = $this->model_upstore_category->getArticles($filter_data);

		if (!empty($results)) {

			foreach ($results as $article_info) {

				if ($article_info) {

					if ($article_info['image']) {
						$image = $this->model_tool_image->resize($article_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					$data['articles'][] = array(
						'width' 	  => $setting['width'],
						'height' 	  => $setting['height'],
						'article_id'  => $article_info['article_id'],
						'date_added'  => $this->model_upstore_theme->lang_date('j F, Y', strtotime($article_info['date_added'])),
						'viewed'  	  => $article_info['viewed'],
						'name'        => $article_info['name'],
						'image'       => $image,
						'description' => utf8_substr(strip_tags(html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8')), 0, $setting['art_desc_length']) . '..',
						'href' => $this->url->link('upstore/article', '&ch_news_article_id=' . $article_info['article_id'])
					);
				}
			}
		}

		$data['module'] = $module++;
		if ($data['articles']) {
			return $this->load->view('extension/module/upstore_article_latest', $data);
		}
		}
	}
}