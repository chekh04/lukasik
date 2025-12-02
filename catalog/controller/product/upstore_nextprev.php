<?php
class ControllerProductUpstoreNextprev extends Controller {
	public function index() {
		$ns_show_nextprev_prod = $this->config->get('ns_show_nextprev_prod');
		if(isset($ns_show_nextprev_prod) && ($ns_show_nextprev_prod == 1)){
			$this->load->model('upstore/theme');
			if (isset($this->request->get['product_id'])) {
				$product_id = $this->request->get['product_id'];
			} else {
				$product_id = false;
			}
			if (isset($this->request->get['path'])) {
				$path = false;
				foreach (explode('_', $this->request->get['path']) as $path_id) {
					if (!$path) {
						$path = $path_id;
					} else {
						$path = $path_id;
					}

				}
			}
			else {
				$path = $this->model_upstore_theme->getPath($product_id);
			}


			$data['products'] = array();
			$data['products'] = $this->model_upstore_theme->getPrevNextProduct($product_id,$path);


			return $this->load->view('product/upstore_nextprev', $data);
		}
	}
}
