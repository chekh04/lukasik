<?php
/**
 * Filter API Controller
 * Returns filter data (price range, sizes) for custom filter modals
 * Directly queries database without relying on OcFilter initialization
 */
class ControllerApiFilter extends Controller {
    
    public function index() {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('X-Robots-Tag: noindex, nofollow');
        
        $json = [];
        
        // Get category_id from request
        $category_id = 0;
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)array_pop($parts);
        } else if (isset($this->request->get['category_id'])) {
            $category_id = (int)$this->request->get['category_id'];
        }
        
        // Get price range for products in this category
        $json['price'] = $this->getPriceRange($category_id);
        
        // Get size filter values
        $json['sizes'] = $this->getSizeValues($category_id);
        
        // URL building parameters for OcFilter
        $json['url_params'] = $this->getOcFilterUrlParams();
        
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get min/max price range for products in category
     */
    private function getPriceRange($category_id) {
        $sql = "SELECT MIN(p.price) as min_price, MAX(p.price) as max_price 
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
                WHERE p.status = '1' AND p.date_available <= NOW()";
        
        if ($category_id) {
            $sql .= " AND p2c.category_id = '" . (int)$category_id . "'";
        }
        
        $query = $this->db->query($sql);
        
        if ($query->row) {
            return [
                'filter_key' => '2.0',  // OcFilter price key (source=0 special, filter=price which is 2)
                'filter_id' => 2,
                'source' => 0,
                'name' => 'Ціна',
                'min' => floor((float)$query->row['min_price']),
                'max' => ceil((float)$query->row['max_price']),
                'prefix' => '',
                'suffix' => ' ₴',
            ];
        }
        
        return null;
    }
    
    /**
     * Get size values from OcFilter
     */
    private function getSizeValues($category_id) {
        // Get size filter (filter_id = 2 for Розмір based on earlier DB query)
        $filter_id = 2;
        
        // Get filter info
        $filter_query = $this->db->query("
            SELECT f.filter_id, fd.name, f.type
            FROM " . DB_PREFIX . "ocfilter_filter f
            LEFT JOIN " . DB_PREFIX . "ocfilter_filter_description fd 
                ON (f.filter_id = fd.filter_id AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "')
            WHERE f.filter_id = '" . (int)$filter_id . "' AND f.status = '1'
        ");
        
        if (!$filter_query->num_rows) {
            return null;
        }
        
        $filter_info = $filter_query->row;
        
        // First, determine the source for this filter's values
        $source_query = $this->db->query("
            SELECT DISTINCT source FROM " . DB_PREFIX . "ocfilter_filter_value 
            WHERE filter_id = '" . (int)$filter_id . "' LIMIT 1
        ");
        
        $source = 1; // Default source
        if ($source_query->num_rows) {
            $source = (int)$source_query->row['source'];
        }
        
        // Get filter values that are used by products in this category
        // Note: oc_ocfilter_filter_value uses value_id (not filter_value_id)
        // Both value and description tables have source column that needs to match
        // Use COALESCE to fallback to default language (1) if current language name is null
        $current_lang = (int)$this->config->get('config_language_id');
        $default_lang = 1; // uk-ua
        
        $sql = "SELECT DISTINCT fv.value_id, fv.source, 
                    COALESCE(fvd_current.name, fvd_default.name) as name, 
                    COUNT(DISTINCT fv2p.product_id) as count
                FROM " . DB_PREFIX . "ocfilter_filter_value fv
                LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_description fvd_current 
                    ON (fv.value_id = fvd_current.value_id AND fv.source = fvd_current.source AND fv.filter_id = fvd_current.filter_id AND fvd_current.language_id = '" . $current_lang . "')
                LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_description fvd_default 
                    ON (fv.value_id = fvd_default.value_id AND fv.source = fvd_default.source AND fv.filter_id = fvd_default.filter_id AND fvd_default.language_id = '" . $default_lang . "')
                LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_to_product fv2p 
                    ON (fv.value_id = fv2p.value_id AND fv.filter_id = fv2p.filter_id AND fv.source = fv2p.source)
                LEFT JOIN " . DB_PREFIX . "product p 
                    ON (fv2p.product_id = p.product_id AND p.status = '1')";
        
        if ($category_id) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }
        
        $sql .= " WHERE fv.filter_id = '" . (int)$filter_id . "'";
        
        if ($category_id) {
            $sql .= " AND p2c.category_id = '" . (int)$category_id . "'";
        }
        
        $sql .= " GROUP BY fv.value_id, fv.source, name, fv.sort_order
                  HAVING count > 0
                  ORDER BY fv.sort_order, name";
        
        $values_query = $this->db->query($sql);
        
        if (!$values_query->num_rows) {
            return null;
        }
        
        $values = [];
        foreach ($values_query->rows as $row) {
            $values[] = [
                'value_id' => (int)$row['value_id'],
                'name' => $row['name'],
                'count' => (int)$row['count'],
            ];
            // Get source from first value (they should all be the same)
            if ($source === 1 && isset($row['source'])) {
                $source = (int)$row['source'];
            }
        }
        
        return [
            'filter_key' => $filter_id . '.' . $source,  // OcFilter format: filter_id.source
            'filter_id' => (int)$filter_info['filter_id'],
            'source' => $source,
            'name' => $filter_info['name'],
            'type' => $filter_info['type'],
            'values' => $values,
        ];
    }
    
    /**
     * Get OcFilter URL parameters from settings
     */
    private function getOcFilterUrlParams() {
        // Default OcFilter URL params
        return [
            'index' => 'ocf',
            'sep_filt' => 'F',   // Filter prefix
            'sep_fsrc' => 'S',   // Source prefix
            'sep_vals' => 'V',   // Values prefix
            'sep_sdot' => 'D',   // Decimal dot
            'sep_sneg' => 'N',   // Negative prefix
            'sep_sran' => 'T',   // Range separator (min T max)
        ];
    }
}

