<?php
class ControllerExtensionModuleUpstoreProductSizeChart extends Controller {

	public function index() {

		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

			$status_size_charts = $this->config->get('upstore_product_size_chart_status');

			if($status_size_charts){

				$lang_id = $this->config->get('config_language_id');
				$this->load->model('catalog/upstore_product_size_chart');
				$this->load->language('extension/module/upstore_product_size_chart');

				if (isset($this->request->get['product_id'])) {
					$product_id = (int)$this->request->get['product_id'];
				} else {
					$product_id = 0;
				}

				$data['heading_title'] = $this->language->get('heading_title');

				$results = $this->model_catalog_upstore_product_size_chart->getSizeCharts($product_id);

				$data['contents'] = [];

				if (!empty($results)) {
					foreach ($results as $result) {
						if ($result['content_type'] == 'html') {
							$data['contents'][] = [
								'type'		=> 'html',
								'title'		=> $result['name'],
								'sort'		=> $result['sort_order'],
								'content'	=> html_entity_decode($result['content'], ENT_QUOTES, 'UTF-8')
							];
						} elseif ($result['content_type'] == 'table') {
							$content = $result['content'];
							$decoded = json_decode($content, true);

							if (is_array($decoded) && isset($decoded['header'], $decoded['cell'])) {
								$headers = $decoded['header'];
								$cells = $decoded['cell'];
								$row_count = (count($headers) > 0) ? ceil(count($cells) / count($headers)) : 0;

								$data['contents'][] = [
									'type'	=> 'table',
									'title'	=> $result['name'],
									'sort'	=> $result['sort_order'],
									'header'	=> $headers,
									'cell'	=> $cells,
									'rows'	=> $row_count
								];
							}
						}
					}
				}

				if (!empty($data['contents'])) {
					$sort = array_column($data['contents'], 'sort');
					array_multisort($sort, SORT_ASC, $data['contents']);
				}

				$this->response->setOutput($this->load->view('extension/module/upstore_product_size_chart', $data));
			}
		} else {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
	}
}
?>
