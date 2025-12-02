<?php
class ControllerUpstoreArticle extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('product/product');
		$this->load->language('upstore/theme');
		$this->load->model('upstore/theme');
		$this->load->model('extension/module/upstore_pro_sticker');
		$data['text_instock'] = $this->language->get('text_instock');
		$this->load->language('upstore/article');
		$data['lang_id'] = $this->config->get('config_language_id');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('upstore/category');

		if (isset($this->request->get['ch_news_id'])) {

			$ch_news_id = '';

			$parts = explode('_', (string)$this->request->get['ch_news_id']);

			$ch_news_id = (int)array_pop($parts);

			foreach ($parts as $news_id) {
				if (!$ch_news_id) {
					$ch_news_id = $news_id;
				} else {
					$ch_news_id .= '_' . $news_id;
				}

				$category_info = $this->model_upstore_category->getCategory($news_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('upstore/category', 'ch_news_id=' . $ch_news_id)
					);
				}
			}

			$category_info = $this->model_upstore_category->getCategory($ch_news_id);

			if ($category_info) {

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('upstore/category', 'ch_news_id=' . $this->request->get['ch_news_id'])
				);
			}
		}

		if (isset($this->request->get['ch_news_article_id'])) {
			$article_id = (int)$this->request->get['ch_news_article_id'];
		} else {
			$article_id = 0;
		}


		$this->load->model('upstore/article');

		$info_article = $this->model_upstore_article->getArticle($article_id);

		if ($info_article) {

			$data['breadcrumbs'][] = array(
				'text' => $info_article['name'],
				'href' => $this->url->link('upstore/article', '&ch_news_article_id=' . $this->request->get['ch_news_article_id'])
			);

			if ($info_article['meta_title']) {
				$this->document->setTitle($info_article['meta_title']);
			} else {
				$this->document->setTitle($info_article['name']);
			}

			$this->document->setDescription($info_article['meta_description']);
			$this->document->setKeywords($info_article['meta_keyword']);
			$this->document->addLink($this->url->link('upstore/article', 'ch_news_article_id=' . $this->request->get['ch_news_article_id']), 'canonical');

			if ($info_article['meta_h1']) {
				$data['heading_title'] = $info_article['meta_h1'];
			} else {
				$data['heading_title'] = $info_article['name'];
			}

			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_loading'] = $this->language->get('text_loading');
			$data['button_continue'] = $this->language->get('button_continue');

			$data['article_id'] = (int)$this->request->get['ch_news_article_id'];
			$data['description'] = html_entity_decode($info_article['description'], ENT_QUOTES, 'UTF-8');


			$data['total_comments'] = (int)$info_article['comments'];
			$data['viewed'] = (int)$info_article['viewed'];

			$data['entry_name'] = $this->language->get('entry_name');
			$data['text_write_comment'] = $this->language->get('text_write_comment');
			$data['entry_comment'] = $this->language->get('entry_comment');
			$data['text_note'] = $this->language->get('text_note');
			$data['text_no_article_comments'] = $this->language->get('text_no_article_comments');
			$data['button_send'] = $this->language->get('button_send');
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$data['article_comment_status'] = (!empty($this->config->get('article_comment_status')) ? $this->config->get('article_comment_status') : 0);
			$data['date_added'] = $this->model_upstore_theme->lang_date('j F, Y', strtotime($info_article['date_added']));

			$this->load->model('tool/image');

			$ch_article_width = (!empty($this->config->get('ch_article_width')) ? $this->config->get('ch_article_width') : 400);
			$ch_article_height = (!empty($this->config->get('ch_article_height')) ? $this->config->get('ch_article_height') : 400);
			$data['article_show_like_dislike'] = (!empty($this->config->get('article_show_like_dislike')) ? $this->config->get('article_show_like_dislike') : 0);

			$data['ch_article_width'] = $ch_article_width;
			$data['ch_article_height'] = $ch_article_height;

			if ($info_article['image']) {
				$data['thumb'] = $this->model_tool_image->resize($info_article['image'], $ch_article_width, $ch_article_height);
			} else {
				$data['thumb'] = '';
			}

			$data['text_tax'] = $this->language->get('text_tax');
			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');
			$data['text_home_ns'] = $this->language->get('text_home_ns');

			$data['products'] = array();

			$featured_product_title = json_decode($info_article['featured_product_title'], true);
			$data['mod_title'] = (!empty($featured_product_title[$data['lang_id']]) ? $featured_product_title[$data['lang_id']] : '');

			$data['disable_cart_button'] = (!empty($this->config->get('config_disable_cart_button')) ? 1 : 0);
			$data['disable_fastorder_button'] = (!empty($this->config->get('config_disable_fastorder_button')) ? 1 : 0);
			$data['change_text_cart_button_out_of_stock'] = (!empty($this->config->get('config_change_text_cart_button_out_of_stock')) ? 1 : 0);
			$data['show_stock_status'] = (!empty($this->config->get('config_show_stock_status')) ? 1 : 0);
			$config_disable_cart_button_text = $this->config->get('config_disable_cart_button_text');
			if(!empty($config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'])){
				$data['disable_cart_button_text'] = $config_disable_cart_button_text[$this->config->get('config_language_id')]['disable_cart_button_text'];
			} else {
				$data['disable_cart_button_text'] = $this->language->get('disable_cart_button_text');
			}
			$data['setting_module'] = $this->config->get('setting_module');
			$dop_image = true;
			if(deviceType == 'phone') {
				$dop_image = false;
				if(isset($data['setting_module']['hidden_model']) && ($data['setting_module']['hidden_model'] == 1)){
					$data['setting_module']['status_model'] = 0;
				}
				if(isset($data['setting_module']['hidden_desc']) && ($data['setting_module']['hidden_desc'] == 1)){
					$data['setting_module']['status_description'] = 0;
				}
				if(isset($data['setting_module']['hidden_rating']) && ($data['setting_module']['hidden_rating'] == 1)){
					$data['setting_module']['status_rating'] = 0;
				}
				if(isset($data['setting_module']['hidden_actions']) && ($data['setting_module']['hidden_actions'] == 1)){
					$data['setting_module']['status_actions'] = 0;
				}
			}
			$data['config_on_off_featured_quickview'] = $this->config->get('config_on_off_featured_quickview');

			$data['nst_data'] = $this->config->get('nst_data');
			if(isset($data['nst_data']['lazyload_module']) && ($data['nst_data']['lazyload_module'] == 1)){
				$data['lazyload_module'] = true;
				if (isset($data['nst_data']['lazyload_image']) && ($data['nst_data']['lazyload_image'] !='')) {
					$data['lazy_image'] = 'image/' . $data['nst_data']['lazyload_image'];
				} else {
					$data['lazy_image'] = 'image/catalog/lazyload/lazyload.jpg';
				}
			} else {
				$data['lazyload_module'] = false;
			}

			$data['config_on_off_featured_quickview'] = $this->config->get('config_on_off_featured_quickview');
			$data['config_quickview_btn_name'] = $this->config->get('config_quickview_btn_name');
			$data['config_additional_settings_upstore'] = $this->config->get('config_additional_settings_upstore');
			$data['show_special_timer_module'] = (!empty($this->config->get('config_show_special_timer_module')) ? 1 : 0);
			$data['on_off_sticker_special'] = (!empty($this->config->get('on_off_sticker_special')) ? 1 : 0);
			$data['config_change_icon_sticker_special'] = $this->config->get('config_change_icon_sticker_special');
			$data['on_off_sticker_topbestseller'] = (!empty($this->config->get('on_off_sticker_topbestseller')) ? 1 : 0);
			$data['config_limit_order_product_topbestseller'] = $this->config->get('config_limit_order_product_topbestseller');
			$data['config_change_icon_sticker_topbestseller'] = $this->config->get('config_change_icon_sticker_topbestseller');
			$data['on_off_sticker_popular'] = (!empty($this->config->get('on_off_sticker_popular')) ? 1 : 0);
			$data['config_min_quantity_popular'] = $this->config->get('config_min_quantity_popular');
			$data['config_change_icon_sticker_popular'] = $this->config->get('config_change_icon_sticker_popular');
			$data['on_off_sticker_newproduct'] = (!empty($this->config->get('on_off_sticker_newproduct')) ? 1 : 0);
			$data['config_limit_day_newproduct'] = $this->config->get('config_limit_day_newproduct');
			$data['config_change_icon_sticker_newproduct'] = $this->config->get('config_change_icon_sticker_newproduct');
			$data['text_sticker_special'] = $this->config->get('config_change_text_sticker_special');
			$data['text_sticker_newproduct'] = $this->config->get('config_change_text_sticker_newproduct');
			$data['text_sticker_popular'] = $this->config->get('config_change_text_sticker_popular');
			$data['text_sticker_topbestseller'] = $this->config->get('config_change_text_sticker_topbestseller');
			$data['config_text_open_form_send_order'] = $this->config->get('config_text_open_form_send_order');
			$data['on_off_percent_discount'] = $this->config->get('on_off_percent_discount');
			$ch_article_fp_width = (!empty($this->config->get('ch_article_fp_width')) ? $this->config->get('ch_article_fp_width') : 200);
			$ch_article_fp_height = (!empty($this->config->get('ch_article_fp_height')) ? $this->config->get('ch_article_fp_height') : 200);

			$pids_in_cart = array();
			if (isset($this->request->cookie['pids_in_cart'])) {
				$pids = explode(',', $this->request->cookie['pids_in_cart']);
				if(!empty($pids)){
					foreach ($pids as $pid) {
						$pids_in_cart[] = (int)$pid;
					}
				}
			}

			$notify_stock_status = !$this->config->get('config_stock_checkout') && ($this->config->get('upstore_notify_stock_status') == 1);
			$notify_stock_setting = $this->config->get('upstore_notify_stock_setting');
			$data['button_notify_stock'] = isset($notify_stock_setting['button_text'][$this->config->get('config_language_id')]) ? $notify_stock_setting['button_text'][$this->config->get('config_language_id')] : '';

			$pids_in_waitlist = [];
			if ($this->customer->isLogged() && $notify_stock_status) {
				$this->load->model('extension/module/upstore_notify_stock');
				$pids_in_waitlist = $this->model_extension_module_upstore_notify_stock->getProductsRequestsByCustomer();
			}

			$results = $this->model_upstore_article->getProductFeatured($this->request->get['ch_news_article_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $ch_article_fp_width, $ch_article_fp_height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $ch_article_fp_width, $ch_article_fp_height);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$image_h = false;
				$image_hm = array();

				if(isset($data['setting_module']['image_hover']) && ($data['setting_module']['image_hover'] > 0) && $dop_image){
					$results_img = $this->model_catalog_product->getProductImages($result['product_id']);
					if($data['setting_module']['image_hover'] == 1){
						foreach ($results_img as $key => $result_img) {
							if($key == 1){
								break;
							}
							$image_h = $this->model_tool_image->resize($result_img['image'], $ch_article_fp_width, $ch_article_fp_height);
						}
					} else {
						foreach ($results_img as $key => $img) {
							if($key == 6){
								break;
							}
							$image_hm[] = $this->model_tool_image->resize($img['image'], $ch_article_fp_width, $ch_article_fp_height);
						}
					}
				}

				$top_bestsellers = $this->model_catalog_product->getTopSeller($result['product_id']);

				if ((float)$result['special']) {
					$price2 = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
					$special2 = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));
					$skidka = $special2/($price2/100)-100;
				} else {
					$skidka = "";
				}

				$product_quantity = $result['quantity'];
				$stock_status = $result['stock_status'];

				if ((float)$result['special']) {
					$special_date_end = $this->model_catalog_product->getDateEnd($result['product_id']);
				} else {
					$special_date_end = false;
				}

				if((isset($result['date_available'])&&(round((strtotime(date("Y-m-d"))-strtotime($result['date_available']))/86400))<=$this->config->get('config_limit_day_newproduct'))) {
					$sticker_new_prod = true;
				} else {
					$sticker_new_prod = false;
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price_value = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
				} else {
					$price_value = false;
				}

				if ((float)$result['special']) {
					$special_value = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($this->session->data['currency']);
				} else {
					$special_value = false;
				}

				$show_buy_button = true;

				if ($result['quantity'] <= 0 && $notify_stock_status) {
					$show_buy_button = false;
				}

				$data['products'][] = array(
					'show_buy_button' 	=> $show_buy_button,
					'in_waitlist' 			=> in_array($result['product_id'], $pids_in_waitlist) ? true : false,
					'pro_sticker'			=> $this->model_extension_module_upstore_pro_sticker->getProStickers($result['product_id'],'module_page'),
					'rating_stars'			=> $this->model_upstore_theme->productRatingStars($rating),
					'in_cart' 				=> in_array($result['product_id'], $pids_in_cart) ? true : false,
					'width' 					=> $ch_article_fp_width,
					'height' 				=> $ch_article_fp_height,
					'product_id'  			=> $result['product_id'],
					'sticker_new_prod' 	=> $sticker_new_prod,
					'price_value' 			=> $price_value,
					'special_value' 		=> $special_value,
					'date_end'				=> $special_date_end,
					'image_h'				=> $image_h,
					'image_hm'				=> $image_hm,
					'product_quantity'	=> $product_quantity,
					'stock_status'			=> $stock_status,
					'reviews'				=> sprintf((int)$result['reviews']),
					'skidka'					=>  round((float)$skidka),
					'model'					=> $result['model'],
					'date_available'		=> $result['date_available'],
					'viewed'					=> $result['viewed'],
					'top_bestsellers'		=> $top_bestsellers['total'],
					'thumb'					=> $image,
					'name'					=> $result['name'],
					'description' 			=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       			=> $price,
					'special'     			=> $special,
					'tax'         			=> $tax,
					'minimum'     			=> $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      			=> $rating,
					'href'       	 		=> $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}


			$this->model_upstore_article->updateViewedArticle($this->request->get['ch_news_article_id']);

			// Captcha
			if ($this->config->get('article_captcha_status')) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			if ($this->config->get('article_comment_guest') || $this->customer->isLogged()) {
				$data['article_comment_guest'] = true;
			} else {
				$data['article_comment_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}


			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('upstore/article', $data));
		} else {

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('upstore/article', '&ch_news_article_id=' . $article_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	public function article_comment() {
		$this->load->language('upstore/article');
		$this->load->model('upstore/theme');
		$this->load->model('upstore/article');

		$data['text_no_article_comments'] = $this->language->get('text_no_article_comments');
		$data['text_admin_reply'] = $this->language->get('text_admin_reply');
		$data['article_show_like_dislike'] = (!empty($this->config->get('article_show_like_dislike')) ? $this->config->get('article_show_like_dislike') : 0);


		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['article_comments'] = array();

		$comment_total = $this->model_upstore_article->getTotalCommentsByArticleId($this->request->get['ch_news_article_id']);

		$results = $this->model_upstore_article->getCommentsByArticleId($this->request->get['ch_news_article_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['article_comments'][] = array(
				'article_comment_id'	=> $result['article_comment_id'],
				'author'					=> $result['author'],
				'text'					=> nl2br($result['text']),
				'admin_reply'			=> $result['admin_reply'],
				'like'     				=> (int)$result['like'],
				'dislike'     			=> (int)$result['dislike'],
				'date_added' 			=> $this->model_upstore_theme->lang_date('j F, Y', strtotime($result['date_added']))
			);
		}


		$pagination = new Pagination();
		$pagination->total = $comment_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('upstore/article/article_comment', 'ch_news_article_id=' . $this->request->get['ch_news_article_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($comment_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($comment_total - 5)) ? $comment_total : ((($page - 1) * 5) + 5), $comment_total, ceil($comment_total / 5));

		$this->response->setOutput($this->load->view('upstore/article_comment', $data));
	}

	public function write_comment() {
		$this->load->language('upstore/article');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 10) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			// Captcha
			if ($this->config->get('article_captcha_status')) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('upstore/article');

				$this->model_upstore_article->addArticleComment($this->request->get['article_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function likeDislike() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

			$json = array();

			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				$this->load->model('upstore/article');
				$article_comment_id = $this->model_upstore_article->addLikeDislike($this->request->post);
				$json = $this->model_upstore_article->getLikeDislike($article_comment_id);
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
		  $this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}
}
