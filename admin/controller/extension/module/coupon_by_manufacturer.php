<?php
class ControllerExtensionModuleCouponByManufacturer extends Controller {
	private $error = array(); 
	private $data = array();

	private $path_module = 'extension/module/coupon_by_manufacturer';

	private $path_extension ='marketplace/extension&type=module';
	private $module_name ='coupon_by_manufacturer';
	private $my_model ='model_extension_module_coupon_by_manufacturer';
	
	private $user_token = 'user_token';
	public function index() {   
		$data = $this->load->language($this->path_module );

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('setting/setting');

		$data['heading_title']       = $this->language->get('heading_title');
		$data['text_module']         = $this->language->get('text_module');
		$data['text_success']        = $this->language->get('text_success');

		$data['button_cancel']       = $this->language->get('button_cancel');

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->makeUrl('common/home'),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->makeUrl($this->path_extension),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->makeUrl($this->path_module),
   		);

		$data['cancel'] = $this->makeUrl($this->path_extension);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path_module, $data));

	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->path_module)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
				
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function install() {
		$this->load->model($this->path_module);
		$this->{$this->my_model}->install();
		$events = $this->getEvents();
		$this->load->model('setting/event');
		foreach ($events as $code=>$value) {
			$this->model_setting_event->deleteEvent($code);
		}
		foreach ($events as $code=>$values) {
			foreach ($values as $value) {
			$this->model_setting_event->addEvent($code, $value['trigger'], $value['action'], 1);
			}
		}
		
	}
	

	public function uninstall() {
		$this->load->model($this->path_module);
		$this->{$this->my_model}->uninstall();
		$events = $this->getEvents();
		$this->load->model('setting/event');
		foreach ($events as $code=>$value) {
			$this->model_setting_event->deleteEvent($code);
		}
	}
	

	private function makeUrl($route, $arg=''){
		if ($arg) {
			$arg = '&' . ltrim($arg,'&');
		}
		return $this->url->link($route, $this->user_token . '=' . $this->session->data[$this->user_token] . $arg, true);
	}

	private function makeUrlScript($route, $arg=''){
		return str_replace('&amp;','&',$this->makeUrl($route, $arg));
	}

	public function deleteCoupon(&$route, &$args) {
		$this->load->model($this->path_module);
		$coupon_id = $args[0];
		$this->{$this->my_model}->deleteCouponManufacturers($coupon_id);
	}

	public function editCoupon(&$route, &$args, &$output) {
		$this->load->model($this->path_module);
		$coupon_id = 0;
		if ($route == 'marketing/coupon/addCoupon') {
			$coupon_id = $output;
			$data = $args[0];
		} elseif (isset($args[0])) {
			$coupon_id = $args[0];
			$data = $args[1];
		}

		$this->{$this->my_model}->addCouponManufacturers($coupon_id,$data);
	}

	public function getFormCoupon(&$route, &$data, &$output) {
		$my_data = $this->load->language($this->path_module . '_form');
		$this->load->model($this->path_module);
		if (isset($this->request->post['coupon_manufacturer'])) {
			$manufacturers = $this->request->post['coupon_manufacturer'];
		} elseif (isset($this->request->get['coupon_id'])) {
			$manufacturers = $this->{$this->my_model}->getCouponManufacturers($this->request->get['coupon_id']);
		} else {
			$manufacturers = array();
		}

		$this->load->model('catalog/manufacturer');

		$my_data['coupon_manufacturers'] = array();
		$my_data[$this->user_token] = $data[$this->user_token];

		foreach ($manufacturers as $manufacturer_id) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

			if ($manufacturer_info) {
				$my_data['coupon_manufacturers'][] = array(
					'manufacturer_id' => $manufacturer_info['manufacturer_id'],
					'name'        => $manufacturer_info['name']
				);
			}
		}
		$manufacturer_form = $this->load->view($this->path_module . '/coupon_by_manufacturer_form', $my_data);
		$output = str_replace(
			'<label class="col-sm-2 control-label" for="input-date-start">',
			$manufacturer_form . '<label class="col-sm-2 control-label" for="input-date-start">',
			$output);

	}
	
	private function getEvents() {
		$events  = array();
		$events['coupon_by_manufacturer'] = array(
			array(
				'trigger' => 'admin/model/marketing/coupon/addCoupon/after',
				'action'  => $this->path_module . '/editCoupon',
			),
			array(
				'trigger' => 'admin/model/marketing/coupon/editCoupon/after',
				'action'  => $this->path_module . '/editCoupon',
			),
			array(
				'trigger' => 'admin/model/marketing/coupon/deleteCoupon/before',
				'action'  => $this->path_module . '/deleteCoupon',
			),
			array(
				'trigger' => 'admin/view/marketing/coupon_form/after',
				'action'  => $this->path_module . '/getFormCoupon',
			),
		);
		return $events;
	}	
	
}