<?php
class ControllerExtensionModuleUpstoreReviewsStore extends Controller {
  public function getNextPage() {
    if (isset($this->request->post['module_id']) && (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
      $this->load->model('setting/module');
      $module_id = (int)$this->request->post['module_id'];
      $setting = $this->model_setting_module->getModule($module_id);
      $setting['page'] = (int)$this->request->post['page'];
      $setting['module'] = (int)$this->request->post['module'];

      $this->response->setOutput($this->index($setting));
    }
  }

  public function index($setting) {

    $data['module_id'] = $setting['module_id'];

    $this->load->language('product/upstore_reviews_store');
    $data['text_review_guest'] = sprintf($this->language->get('text_review_guest'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

    $reviews_store_setting = $this->config->get('reviews_store_setting');

    if ((isset($reviews_store_setting['review_guest']) && ($reviews_store_setting['review_guest'] == 1)) || $this->customer->isLogged()) {
      $data['review_guest'] = true;
    } else {
      $data['review_guest'] = false;
    }

    if(isset($reviews_store_setting['status']) && ($reviews_store_setting['status'] == 1)){

      static $module = 0;

      $this->load->language('upstore/theme');
      $this->load->model('upstore/theme');

      $data['text_showmore'] = $this->language->get('text_showmore');

      $this->load->model('catalog/upstore_reviews_store');

      $data['reviews_theme_rating'] = $this->model_catalog_upstore_reviews_store->getReviewsThemeRating();

      $this->document->addStyle('catalog/view/theme/upstore/stylesheet/popup-reviews-store/stylers.css');

      $data['heading_title'] = $this->language->get('heading_title');

      $data['btn_write_review'] = $this->language->get('btn_write_review');
      $data['button_continue'] = $this->language->get('button_continue');
      $data['text_empty'] = $this->language->get('text_empty');
      $data['text_sort'] = $this->language->get('text_sort');
      $data['text_limit'] = $this->language->get('text_limit');
      $data['text_empty'] = $this->language->get('text_empty');
      $data['btn_all_review'] = $this->language->get('btn_all_review');
      $data['reviews_store_setting'] = $this->config->get('reviews_store_setting');
      $data['text_reviews'] = $this->config->get('text_reviews');

      $data['rating_store'] = $this->model_catalog_upstore_reviews_store->getSumAvgReviewsStore();
      $data['percent_rating'] = $this->model_catalog_upstore_reviews_store->getPercentReviewsStore();

      if(isset($this->request->post['page'])) {
        $page = $this->request->post['page'];
      } else {
        $page = 1;
      }

      if(deviceType == 'phone') {
        $setting['limit'] = $setting['limit_mob'];
      }

      if(deviceType == 'tablet') {
        $setting['limit'] = $setting['limit_tablet'];
      }

      if(deviceType == 'computer') {
        $setting['limit'] = $setting['limit'];
      }

      $data['status_showmore'] = isset($setting['status_showmore']) ? $setting['status_showmore'] : 0;

      $limit_max = isset($setting['limit_max']) ? $setting['limit_max'] : 12;

      $filter_data = array(
        'start' => ($page - 1) * $setting['limit'],
        'limit' => $setting['limit'],
        'limit_max' => $limit_max
      );

      $reviews_store_total = $this->model_catalog_upstore_reviews_store->getTotalReviewsStore();

      $data['totals_reviews'] = $reviews_store_total;

      if ($reviews_store_total > $limit_max) {
        $reviews_store_total = $limit_max;
      }

      $results = $this->model_catalog_upstore_reviews_store->getAllReviews($filter_data);

      $data['reviews_store'] = array();

      foreach ($results as $result) {
        $data['reviews_store'][] = array(
          'reviews_store_id'    => $result['reviews_store_id'],
          'admin_response'      => $result['admin_response'],
          'author'              => $result['author'],
          'first_letter'        => utf8_substr($result['author'], 0, 1),
          'like'                => $result['like'],
          'dislike'             => $result['dislike'],
          'stars'            => $this->model_catalog_upstore_reviews_store->getAvgRatingCustomer($result['reviews_store_id']),
          'description'         => strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
          'admin_response'      => utf8_substr(strip_tags(html_entity_decode($result['admin_response'], ENT_QUOTES, 'UTF-8')), 0, 230) . '',
          'date_added'          => $this->model_upstore_theme->lang_date('j F, Y', strtotime($result['date_added']))
        );
      }


      $data['all_review_link'] = $this->url->link('product/upstore_reviews_store');

      $data['last_page'] = ceil($reviews_store_total / $setting['limit']);

      $data['nextPage'] = false;

      if ($page == 1) {
        if ($page == $data['last_page']) {
          $data['nextPage'] = false;
        } elseif($data['last_page'] == '0') {
          $data['nextPage'] = false;
        } else {
          $data['nextPage'] = $page + 1;
        }
      } elseif ($page == $data['last_page']) {
        $data['nextPage'] = false;
      }  else {
        $data['nextPage'] = $page + 1;
      }

      if(isset($this->request->post['module'])) {
        $data['module'] = $this->request->post['module'];
      } else {
        $data['module'] = $module++;
      }

      return $this->load->view('extension/module/upstore_reviews_store', $data);
    }
  }
}