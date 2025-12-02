<?php
class ControllerProductUpstoreReviewscustomer extends Controller {
    public function index() {


        $this->load->language('extension/module/upstore_reviewscustomer');

        $this->load->model('catalog/upstore_reviewscustomer');
        $this->load->model('catalog/product');
        $this->load->model('upstore/theme');
        $this->load->model('tool/image');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->upstore_minifier->addScript('catalog/view/theme/upstore/js/showmore.js','footer');

        $data['show_review_plus'] = $this->config->get('config_show_review_plus');
        $data['show_review_minus'] = $this->config->get('config_show_review_minus');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('product/upstore_reviewscustomer', $url)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_empty'] = $this->language->get('text_empty');
        $data['text_purchased'] = $this->language->get('text_purchased');
        $data['text_admin_reply'] = $this->language->get('text_admin_reply');
        $data['text_plus'] = $this->language->get('text_plus');
        $data['text_minus'] = $this->language->get('text_minus');

        $data['reviews'] = array();

        $reviews_total = $this->model_catalog_upstore_reviewscustomer->getTotalCustomerReviews();

        $results = $this->model_catalog_upstore_reviewscustomer->getAllCustomerReviews(($page - 1) * $limit, $limit);

        foreach ($results as $result) {
            if ($result['product_id']) {
                $product = $this->model_catalog_product->getProduct($result['product_id']);

                if ($this->config->get('config_review_status')) {
                    $rating = $result['rating'];
                } else {
                    $rating = false;
                }

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                }

                $data['reviews'][] = array(
                    'review_id'     => $result['review_id'],
                    'rating'        => $rating,
                    'description'   => $result['text'],
                    'date_added'    => $this->model_upstore_theme->lang_date('j F, Y', strtotime($result['date_added'])),
                    'author'        => $result['author'],
                    'first_letter'  => utf8_substr($result['author'], 0, 1),
                    'purchased'     => $result['purchased'],
                    'plus'          => $result['plus'],
                    'minus'         => $result['minus'],
                    'admin_reply'   => $result['admin_reply'],
                    'author'        => $result['author'],
                    'product_id'    => $product['product_id'],
                    'prod_thumb'    => $image,
                    'prod_name'     => $product['name'],
                    'prod_model'    => $product['model'],
                    'prod_href'     => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );
            }
        }

        $pagination = new Pagination();
        $pagination->total = $reviews_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('product/upstore_reviewscustomer', '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($reviews_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($reviews_total - $limit)) ? $reviews_total : ((($page - 1) * $limit) + $limit), $reviews_total, ceil($reviews_total / $limit));

        if ($page == 1) {
            $this->document->addLink($this->url->link('product/upstore_reviewscustomer', '', true), 'canonical');
        } elseif ($page == 2) {
            $this->document->addLink($this->url->link('product/upstore_reviewscustomer', '', true), 'prev');
        } else {
            $this->document->addLink($this->url->link('product/upstore_reviewscustomer', '&page='. ($page - 1), true), 'prev');
        }

        if ($limit && ceil($reviews_total / $limit) > $page) {
            $this->document->addLink($this->url->link('product/upstore_reviewscustomer', '&page='. ($page + 1), true), 'next');
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('product/upstore_reviewscustomer', $data));
    }
}