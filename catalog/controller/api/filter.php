<?php
/**
 * Filter API Controller
 * Returns filter data (price range, sizes) for custom filter modals
 */
class ControllerApiFilter extends Controller {
    
    public function index() {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('X-Robots-Tag: noindex, nofollow');
        
        $json = [];
        
        // Check if OcFilter is available
        if (!$this->registry->has('ocfilter') || !$this->ocfilter->startup()) {
            $json['error'] = 'OcFilter module is not available';
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        $this->load->language('extension/module/ocfilter');
        $this->load->model('extension/module/ocfilter');
        $this->load->model('tool/image');
        
        // Get category_id from request
        $category_id = 0;
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)array_pop($parts);
        } else if (isset($this->request->get['category_id'])) {
            $category_id = (int)$this->request->get['category_id'];
        }
        
        // Setup OcFilter for this category
        $this->ocfilter->filter->setValuesCounter();
        
        $filter_model_data = $this->ocfilter->filter->getFilterModelData();
        
        // Get filters
        $results = $this->model_extension_module_ocfilter->getFilters($filter_model_data);
        
        $json['filters'] = [];
        $json['price'] = null;
        $json['sizes'] = [];
        
        foreach ($results as $result) {
            $filter = $this->ocfilter->filter->formatFilter($result);
            
            // Handle price slider
            if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') {
                if (!$this->ocfilter->filter->setFilterSlider($filter)) {
                    continue;
                }
                
                // Price filter
                if ($this->ocfilter->params->key($filter['filter_key'])->is('price')) {
                    $json['price'] = [
                        'filter_key' => $filter['filter_key'],
                        'name' => $filter['name'],
                        'min' => (float)$filter['min'],
                        'max' => (float)$filter['max'],
                        'min_request' => (float)$filter['min_request'],
                        'max_request' => (float)$filter['max_request'],
                        'prefix' => $filter['prefix'],
                        'suffix' => $filter['suffix'],
                    ];
                }
            } else {
                // Handle checkbox/radio filters (like size)
                if (!$this->ocfilter->filter->setFilterValues($filter)) {
                    continue;
                }
                
                // Check if this is the size filter (you may need to adjust this check)
                $filter_key = $filter['filter_key'];
                $filter_name_lower = strtolower($filter['name']);
                
                // Detect size filter by name or filter_key
                // Common size filter keys: "2.4" or names containing "size", "розмір", "размер"
                $is_size = (
                    strpos($filter_name_lower, 'size') !== false ||
                    strpos($filter_name_lower, 'розмір') !== false ||
                    strpos($filter_name_lower, 'размер') !== false ||
                    $filter_key === '2.4'  // Specific filter key for sizes
                );
                
                if ($is_size) {
                    $json['sizes'] = [
                        'filter_key' => $filter['filter_key'],
                        'name' => $filter['name'],
                        'values' => []
                    ];
                    
                    foreach ($filter['values'] as $value) {
                        $json['sizes']['values'][] = [
                            'value_id' => $value['value_id'],
                            'name' => $value['name'],
                            'count' => $value['count'],
                            'selected' => $value['selected'],
                        ];
                    }
                }
                
                // Add all filters to general list
                $json['filters'][] = [
                    'filter_key' => $filter['filter_key'],
                    'name' => $filter['name'],
                    'type' => $filter['type'],
                    'values' => array_map(function($v) {
                        return [
                            'value_id' => $v['value_id'],
                            'name' => $v['name'],
                            'count' => $v['count'],
                            'selected' => $v['selected'],
                        ];
                    }, $filter['values'])
                ];
            }
        }
        
        // URL building parameters
        $json['url_params'] = [
            'index' => $this->ocfilter->params->getIndex(),
            'sep_filt' => $this->ocfilter->params->getSepFilter(),
            'sep_fsrc' => $this->ocfilter->params->getSepSource(),
            'sep_vals' => $this->ocfilter->params->getSepValues(),
            'sep_sdot' => $this->ocfilter->params->getSepSliderDot(),
            'sep_sneg' => $this->ocfilter->params->getSepSliderNegative(),
            'sep_sran' => $this->ocfilter->params->getSepSliderRange(),
        ];
        
        // Current filter params from URL
        $json['current_params'] = $this->ocfilter->seo->getParams();
        $json['base_url'] = str_replace('&amp;', '&', $this->ocfilter->seo->link());
        
        $this->response->setOutput(json_encode($json));
    }
}

