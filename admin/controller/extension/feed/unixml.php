<?php

/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://unixml.pro                                 */
/*                                                          */
/************************copyright***************************/

require_once(DIR_SYSTEM . 'library/unixml.php');
class ControllerExtensionFeedUnixml extends Controller {

  public function __construct($registry) {
		parent::__construct($registry);
		$this->unixml = new UniXML($this->registry);
	}

  //install - установка модуля
    public function install() {
      $this->unixml->checkTables(); //проверка таблиц и добавление полей
      $this->initUniXML(); //инициализация UniXML
      $this->unixml->checkPriceDir(); //проверка папки с фидами
    }
  //install

  //initUniXML - инициализация модуля
    private $a = '';
    private $message = '';
    private function initUniXML() {
      $key = $this->getDBKey();
      $lic = $this->checkKey($key);
      $stt = $this->checkStt($lic);
      $init_data = $this->getMdpKey(1);

      $text = str_replace('{{KEY}}', base64_encode($init_data), $this->unixml->text('text_not_key'));

      if(!$stt){
        $a_number = $this->getMdpKey();
        if($a_number){
          $response_type = explode("::", $a_number);
          if($response_type[0] == 'Key' && isset($response_type[1]) && $response_type[1]){
            $this->a = $response_type[1];
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($this->a) . "', serialized = '0'  WHERE `" . (((int)VERSION >= 2)?'code':'group') . "` = '" . $key . "' AND `key` = '" . $key . "_key' AND store_id = '0'");
          }else{
            if(isset($response_type[1])){
              $this->message = $response_type[1];
            }
          }
          if(!$this->message){
            $this->message = $text;
          }
        }else{
          $this->message = $text;
        }
      }
    }
  //initUniXML

  //getDBKey
    private function getDBKey(){
      $key = 'unixml';
      if(substr(VERSION, 0, 1) == 3){ //oc3x
        $key = 'feed_' . $key;
      }

      return $key;
    }
  //getDBKey

  //checkKey
    private function checkKey($key){
      $lic = '';
      $key_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE `key` = '" . $key . "_key'");
      if(!$key_query->num_rows){
        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET `value` = '', serialized = '0', `" . (((int)VERSION >= 2)?'code':'group') . "` = '" . $key . "', `key` = '" . $key . "_key', store_id = '0'");
      }else{
        $lic = $key_query->row['value'];
      }

      return $lic;
    }
  //checkKey

  //checkStt
    private function checkStt($lic){
      $a = 0;
      $dmn = explode("//", HTTP_CATALOG);
      $dmn = str_replace(array('www.','/'),'',$dmn[1]);
			if($lic){
				$key_array = explode("327450", base64_decode(strrev(substr($lic, 0, -7))));
				if($key_array[0] == base64_encode($dmn) && $key_array[1] == base64_encode('3274507573')){
					$a = 1;
				}
			}
      $this->a = $a;
			return str_replace($lic,$dmn,$a);
    }
  //checkStt

  //getMdpKey
    private function getMdpKey($dmn_data = false){
      /*
      $dmn = explode("//", HTTP_CATALOG);
      $dmn = str_replace(array('www.','/'),'',$dmn[1]);
      $init_data = array('email'=>$this->config->get('config_email'),'module'=>'unixml','version'=>$this->unixml->ver,'site'=>$dmn,'sec_token'=>"3274507573",'lang'=>$this->config->get('config_language'),'engine'=>VERSION,'date'=>date("Y-m-d H:i:s"));
      if($dmn_data){
        return $init_data['site'];
      }
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "https://microdata.pro/index.php?route=sale/unixml");
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $init_data);
      $a_number = curl_exec($curl);
      curl_close($curl);
      */
      $a_number = 'Key::==QP9cXTzUleOdXVE50MJpXTwUDN3IzM==QP9cX';          
      return $a_number;
    }
  //getMdpKey

  //addKey
    public function addKey(){
      $key = $this->getDBKey();
      if(isset($this->request->post['txt']) && $this->request->post['txt'] && $this->checkStt($this->request->post['txt'])){
        $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($this->request->post['txt']) . "' WHERE `key` = '" . $key . "_key'");
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(1));
      }
      return false;
    }
  //addKey

  //index - старт админки
    public function index() {
      $data = $this->unixml->text();

      $this->install();
      $this->unixml->checkDir();

      $this->document->setTitle($data['text_heading_title']);

      $data['breadcrumbs'] = array();
      foreach(array($data['text_home'] => 'common/dashboard',$data['text_extension'] => ((substr(VERSION, 0, 1) == 3)?'marketplace/extension':'extension/extension'),$data['text_heading_title'] => $this->unixml->path) as $breadcrumb_text => $breadcrumb_link){
        $data['breadcrumbs'][] = array(
          'text' => $breadcrumb_text,
          'href' => $this->url->link($breadcrumb_link, $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&type=feed', true)
        );
      }

      $data['a'] = $this->a;
      $data['message'] = $this->message;
      $data['image_catalog'] = HTTPS_CATALOG . "image/catalog/";
      $data['token'] = '&' . $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()];
      $data['path'] = $this->unixml->path;
      $data['action'] = $this->url->link($this->unixml->path, $this->unixml->token() . '=' . $data['token'], true);
      $data['cancel'] = $this->url->link('extension/extension', $this->unixml->token() . '=' . $data['token'] . '&type=feed', true);

      $data['fields_product'] = array();
      foreach($this->unixml->getTableFields('product') as $row){
        $data['fields_product'][] = 'p.' . $row['COLUMN_NAME'];
      }

      $data['fields_product_description'] = array();
      foreach($this->unixml->getTableFields('product_description') as $row){
        $data['fields_product_description'][] = 'pd.' . $row['COLUMN_NAME'];
      }

      $data['dir_feeds'] = $this->unixml->exportGetFeedFiles();
      $data['trash_toggle'] = $this->unixml->exportGetTrashToggle();

      $data['header'] = $this->load->controller('common/header');
      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');
      $data['module_init'] = $this->a;

      $data['system_limits'] = array(
        'memory_limit' => '',
        'max_execution_time' => 's',
        'max_input_vars' => '',
        'post_max_size' => '',
        'upload_max_filesize' => ''
      );

      foreach($data['system_limits'] as $limit_item => $limit_unit){
        $data[$limit_item] = ini_get($limit_item) . ($limit_unit?$limit_unit:'');
      }

      $max_allowed_packet = $this->db->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
      $data['max_allowed_packet'] = round(($max_allowed_packet->row['Value']/1024/1024), 3) . 'M';


      $this->response->setOutput($this->loadView($this->unixml->path, $data));

    }
  //index

  //loadView - функция универсального шаблона для всех версий
    private function loadView($route, $data){
      if(substr(VERSION, 0, 1) == 3){
        $this->registry->get('config')->set('template_engine', 'template');
      }
      if(substr(VERSION, 0, 1) == 2 && substr(VERSION, 2, 3) != 3){ //если версия меньше 2.3
        $route .= '.tpl';
      }else{ //если 2.3 или больше
        if(is_file(DIR_APPLICATION . 'controller/feed/unixml.php')){
          unlink(DIR_APPLICATION . 'controller/feed/unixml.php'); //удаляем заглушку для 2.х
        }
      }
      return $this->load->view($route, $data);
    }
  //loadView

  //Функции импорта

    //import - функция запуска импорта
      public function import(){
        return $this->unixml->importStart();
      }
    //import

    //getImportProgress - функция получения прогресса по импорту
      public function getImportProgress(){
        $stat = unserialize(file_get_contents(DIR_SYSTEM . 'unixml/import/' . $this->request->get['import_id'] . '.uxs'));

        //$stat = $stat['stat'];

        $data = array();

        $blocks = array(
          'config' => $this->unixml->text('text_import_process_config'),
          'loaded' => $this->unixml->text('text_import_process_loaded'),
          'parsed' => $this->unixml->text('text_import_process_parsed'),
          'category' => $this->unixml->text('text_import_process_category'),
          'product' => $this->unixml->text('text_import_process_product'),
          'image' => $this->unixml->text('text_import_process_image'),
          'images' => $this->unixml->text('text_import_process_images'),
          'finish' => $this->unixml->text('text_import_process_finish'),
        );

        foreach($blocks as $block => $block_name){
          $data['steps'][$block]['name'] = $block_name;
          $data['steps'][$block]['memory'] = isset($stat['memory'][$block])?$stat['memory'][$block]:0;
          $data['steps'][$block]['time'] = isset($stat['time'][$block])?$stat['time'][$block]:0;
          $data['steps'][$block]['sql'] = isset($stat['step_sql'][$block])?$stat['step_sql'][$block]:0;
        }

        $data['memory'] = isset($stat['max'])?$stat['max']:false;
        $data['sql'] = isset($stat['sql'])?$stat['sql']:0;
        $data['step'] = isset($stat['step'])?$stat['step']:0;
        $data['steps']['category']['category'] = isset($stat['category'])?$stat['category']:0;
        $data['steps']['product']['attributes'] = isset($stat['attributes'])?$stat['attributes']:0;
        $data['steps']['product']['add'] = isset($stat['product_add'])?$stat['product_add']:0;
        $data['steps']['product']['update'] = isset($stat['product_update'])?$stat['product_update']:0;
        $data['steps']['image']['image'] = isset($stat['image'])?$stat['image']:0;
        $data['steps']['images']['images'] = isset($stat['images'])?$stat['images']:0;

        //проверка на паузу
        if(is_file(DIR_SYSTEM . 'unixml/import/' . $this->request->get['import_id'] . '.uxp')){
          $data['pause'] = true;
        }
        //проверка на паузу

        $data['error'] = isset($stat['error'])?$stat['error']:false;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
      }
    //getImportProgress

    //importPauseProcess - функция постановки импорта на паузу
      public function importPauseProcess() {
        $this->unixml->checkDir('unixml/import');

        $import_id = (int)$this->request->get['id'];

        $stat_file = fopen(DIR_SYSTEM . 'unixml/import/' . $import_id . '.uxp', 'w');
        fclose($stat_file);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->unixml->text('text_add_pause'));
      }
    //importPauseProcess

    //importResumeProcess - функция снятия блока паузы
      public function importResumeProcess() {
        $import_id = (int)$this->request->get['id'];

        if(is_file(DIR_SYSTEM . 'unixml/import/' . $import_id . '.uxp')){
          unlink(DIR_SYSTEM . 'unixml/import/' . $import_id . '.uxp');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(true);
      }
    //importResumeProcess

    //importLoadPrices - функция загрузки списка импортов
      public function importLoadPrices() {
        $data = $this->unixml->text();

        $data['prices'] = array();

        $prices_query = $this->db->query("SELECT setting_id, value, date_edit FROM " . DB_PREFIX . "unixml_setting WHERE code = 'import.prices' AND name = 'import_data' ORDER BY date_edit ASC");

        foreach($prices_query->rows as $price_key => $row){
          $price_data = unserialize($row['value']);

          $file_table = $price_data['price_file'];
          if(strlen($price_data['price_file']) > 60){
            $file_table = substr($price_data['price_file'], 0, 30) . '.....' . substr($price_data['price_file'], strlen($price_data['price_file'])-30, strlen($price_data['price_file']));
          }

          $data['prices'][] = array(
            'setting_id' => $row['setting_id'],
            'name' => $price_data['price_name'],
            'comment' => $price_data['price_comment'],
            'file_table' => $file_table,
            'file' => (substr($price_data['price_file'], 0, 5) == 'price')?'/'.$price_data['price_file']:$price_data['price_file'],
            'status' => $this->checkTheImportProcess($row['setting_id']),
            'date' => date('d.m.Y [H:i]', strtotime($row['date_edit']))
          );
        }

        $data['autostart'] = isset($this->request->get['autostart'])?$this->request->get['autostart']:false;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_import', $data));
      }
    //importLoadPrices

    //checkTheImportProcess - функция проверки статуса импортов
      public function checkTheImportProcess($id = false) { //когда id нет это запуск из js, когда есть - запуск из контроллера

        if($id){ //запуск с контроллера
          $status = array();

          //запуск - финиш
          $query = $this->db->query("SELECT value, date_edit FROM " . DB_PREFIX . "unixml_setting WHERE code = 'import.price.status' AND name = '" . (int)$id . "' ORDER BY date_edit DESC LIMIT 2");
          if($query->num_rows){
            foreach($query->rows as $row){
              $status[$row['value']] = (($row['value'] == 1)?$this->unixml->text('text_start'):$this->unixml->text('text_final')) . ': ' . date('d.m.Y [H:i:s]', strtotime($row['date_edit']));
            }
          }
          if(!$status){
            $status[0] = $this->unixml->text('text_not_started');
          }
          //запуск - финиш

          //проверка на паузу
          $pause_file = DIR_SYSTEM . 'unixml/import/' . $id . '.uxp';
          if(is_file($pause_file)){
            $status[9] = $this->unixml->text('text_pause') . ': &nbsp; ' . date('d.m.Y [H:i:s]', filectime($pause_file));
            if(isset($status[2])){ //если есть пауза тогда убираем строку завершен (прошлое завершение)
              unset($status[2]);
            }
          }
          //проверка на паузу

          ksort($status);
          return $status;
        }else{ //запуск из js
          $prices = array();
          $prices_query = $this->db->query("SELECT setting_id FROM " . DB_PREFIX . "unixml_setting WHERE code = 'import.prices' AND name = 'import_data'");
          foreach($prices_query->rows as $row){
            $file_size = 0;
            $stat_file = DIR_SYSTEM . 'unixml/import/' . (int)$row['setting_id'] . '.uxs';
            if(is_file($stat_file)){
              $file_size = filectime($stat_file);
            }
            $prices[$row['setting_id']] = $file_size;
          }

          //проверка еще раз после 3 сек ожидания
            if(isset($this->request->post) && $this->request->post){
              foreach($this->request->post as $before_id => $before_fize){
                $prices[$before_id] = ($before_fize == $prices[$before_id])?false:true;
              }
            }
          //проверка еще раз после 3 сек ожидания

          $this->response->addHeader('Content-Type: application/json');
          $this->response->setOutput(json_encode($prices));
        }
      }
    //checkTheImportProcess

    //importGetCustomFiles - ключ - файл
      private function importGetCustomFiles() {
        return array(
          'custom_before' => 'importCustomBefore',
          'custom' => 'importCustomCode',
          'custom_after' => 'importCustomAfter',
        );
      }
    //importGetCustomFiles

    //importLoadPriceSetting - функция загрузки настроек импорта
      public function importLoadPriceSetting($import_id = false) {
        if($import_id){
          $data['import_id'] = (int)$import_id;
        }else{
          $data = $this->unixml->text();
          $data['import_id'] = (int)$this->request->get['id'];
        }

        $this->unixml->importGetPriceSettings($data);

        if(!isset($data['import_limit']) || !$data['import_limit'] && !$import_id){
          $data['import_limit'] = 10000;
        }

        $custom_codes = array(
          'custom_before' => 'importCustomBefore',
          'custom' => 'importCustomCode',
          'custom_after' => 'importCustomAfter',
        );

        foreach($this->importGetCustomFiles() as $custom_key => $custom_file){
          $data['unixml_import_xml_product_' . $custom_key] = '';
          $file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '/' . $custom_file;
          if(is_file($file)){
            $custom_code = explode(PHP_EOL, file_get_contents($file));
            if(isset($custom_code[4]) && trim($custom_code[4])){
              $custom_code = array_slice($custom_code, 4);
              $data['unixml_import_xml_product_' . $custom_key] = implode(PHP_EOL, $custom_code);
            }
          }
        }

        if($import_id){
          foreach(array('import_id','date_edit','unixml_import_id','path','token','xmlex','cron_link','all_access_column','additionals_row','lang_row','seopro') as $unset_data){
            unset($data[$unset_data]);
          }
          return $data;
        }

        $data['lang_row'] = 1;
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if(!is_array($data['unixml_import_xml_product_multilang'])){
          $data['unixml_import_xml_product_multilang'] = array();
        }
        if(!is_array($data['unixml_import_xml_product_additionals'])){
          $data['unixml_import_xml_product_additionals'] = array();
        }

        $data['additionals_row'] = 1;
        $data['fields_product'] = array();
        foreach($this->unixml->getTableFields('product') as $row){
          if(!in_array($row['COLUMN_NAME'], array('product_id','manufacturer_id','price','quantity','model','sku','status','shipping','subtract','minimum','unixml_feed','unixml_link','date_available','date_added','date_modified'))){
            $data['fields_product'][$row['COLUMN_NAME']] = 'p.' . $row['COLUMN_NAME'];
          }
        }
        ksort($data['fields_product']);

        $data['fields_product_description'] = array();
        foreach($this->unixml->getTableFields('product_description') as $row){
          if(!in_array($row['COLUMN_NAME'], array('product_id','language_id'))){
            $data['fields_product_description'][$row['COLUMN_NAME']] = 'pd.' . $row['COLUMN_NAME'];
          }
        }
        ksort($data['fields_product_description']);

        $data['import_export_setting'] = $this->url->link($this->unixml->path . '/importSettingExport', $this->unixml->token() . '=' . $data['token'] . '&import_id=' . $data['import_id'], true);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_import', $data));
      }
    //importLoadPriceSetting

    //importClearData - функция очистки данных импорта
      public function importClearData() {
        $import_id = (int)$this->request->get['import_id'];

        foreach(array('xml','uxs','uxp') as $ext){
          $file = DIR_SYSTEM . 'unixml/import/' . $import_id . '.' . $ext;
          if(is_file($file)){
            unlink($file);
          }
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE code = 'import.price.status' AND name = '" . (int)$import_id . "'");

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(true);
      }
    //importClearData

    //importGetLinkKey - функция получения связующих полей
      public function importGetLinkKey(){
        $data['stat']['sql'] = 0;
        $data['import_id'] = (int)$this->request->get['id'];
        $this->unixml->importGetImportLink($data);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data['link']));
      }
    //importGetLinkKey

    //importSavePriceSetting - функция сохранения настроек импорта
      public function importSavePriceSetting($setting_data = false) {
        $settings = array();

        if($setting_data){
          $this->request->post = $setting_data;
        }

        foreach(array('price_name','price_comment','price_file') as $item){
          $settings[$item] = $this->request->post[$item];
          if(isset($this->request->post[$item])){
            unset($this->request->post[$item]);
          }
        }

        $post = $this->request->post;
        foreach($this->importGetCustomFiles() as $custom_key => $custom_file){
          $this->importInsertCustomCode($post['unixml_import_xml_product_' . $custom_key], $custom_file, $post['unixml_import_id']);
          unset($this->request->post['unixml_import_xml_product_' . $custom_key]);
        }

        $this->request->post['seopro'] = $this->unixml->getSeopro();

        $this->db->query("UPDATE " . DB_PREFIX . "unixml_setting SET value = '" . serialize($settings) . "' WHERE setting_id = '" . (int)$this->request->post['unixml_import_id'] ."' AND code = 'import.prices' AND name = 'import_data'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'import.price.setting' AND name = '" . (int)$this->request->post['unixml_import_id'] . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET code = 'import.price.setting', name = '" . (int)$this->request->post['unixml_import_id'] ."', value = '" . serialize($this->request->post) . "', date_edit = NOW()");

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));
      }
    //importSavePriceSetting

    //importInsertCustomCode - функция вставки кастомного кода в system
      private function importInsertCustomCode($code, $filename, $import_id){ //custom_code

        $this->unixml->checkDir('unixml/import/' . $import_id);

        //добавляем код
        $new_data  = '<?php' . PHP_EOL;
        $new_data .= '//Этот файл - кастомный код модуля UniXML, Импорт id: ' . $import_id . PHP_EOL;
        $new_data .= '//info: https://unixml.pro' . PHP_EOL . PHP_EOL;
        $new_data .= $code;

        $post_data = html_entity_decode($new_data, ENT_QUOTES, 'UTF-8');
        $fp = fopen(DIR_SYSTEM . 'unixml/import/' . $import_id . '/' . $filename, 'w');
        fwrite($fp, $post_data);
        fclose($fp);
        //добавляем код
      }
    //importInsertCustomCode

    //importDeletePrice - функция удаления импорта
      public function importDeletePrice() {
        $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'import.prices' AND setting_id = '" . (int)$this->request->get['id'] . "'");
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(true);
      }
    //importDeletePrice

    //importUploadFile - функция загрузки файла XML для импорта
      public function importUploadFile() {
        $json = array();

        if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
          $filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));
          if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), array('xml','yml','txt', 'xls', '.xlsx'))) {
            $json['error'] = $this->unixml->text('text_allow_xml');
          }
          if (!in_array($this->request->files['file']['type'], array('text/xml','application/x-yaml','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))) {
            $json['error'] = $this->unixml->text('text_allow_xml');
          }
          if (preg_match('/\<\?php/i', file_get_contents($this->request->files['file']['tmp_name']))) {
            $json['error'] = $this->unixml->text('text_allow_xml');
          }
          if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
            $json['error'] = $this->unixml->text('text_goto_author');
          }
        } else {
          $json['error'] = $this->unixml->text('text_file_upload_error');
        }

        if (!$json) {

          $dir = str_replace("system/", $this->unixml->pricedir . "/import/", DIR_SYSTEM);
          $ext = strtolower(substr(strrchr($filename, '.'), 1));
          $file = $ext . '_' . date('d_m_Y') . '.' . $ext;

          if (!is_dir($dir)) { //если нет директории
            mkdir($dir, 0777, true); //создаем
          }
          if(is_file($dir . $file)){
            $file = rand('100','999') . $file;
          }
          move_uploaded_file($this->request->files['file']['tmp_name'], $dir . $file);

          $json['filename'] = $this->unixml->pricedir . '/import/' . $file;
          $json['success'] = $this->unixml->text('text_file_upload_ok');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //importUploadFile

    //importSavePrice - функция сохранения нового импорта
      public function importSavePrice() {
        $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET `code` = 'import.prices', `name` = 'import_data', `value` = '" . $this->db->escape(serialize($this->request->post)) . "', date_edit = NOW()");
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->db->getLastId());
      }
    //importSavePrice

    //importReadXml - функция отображения XML с файла или ссылки
      public function importReadXml() {
        $data = $this->unixml->text();
        $data['price_file'] = $this->request->post['price_file'];
        $data['login'] = $this->request->post['login'];
        $data['pass'] = $this->request->post['pass'];
        $data['import_id'] = $this->request->get['import_id'];
        $this->unixml->importGetXmlString($data);
        unset($data['import_id']);

        $data['xml_orig'] = htmlentities(str_replace('><', '>' . PHP_EOL . '<', $data['xml']), ENT_QUOTES, "UTF-8");

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($this->loadView($this->unixml->path . '_import', $data)));
      }
    //importReadXml

    //importLoadPriceSettingItem - функция загрузки параметров конкретного элемента
      public function importLoadPriceSettingItem() {
        $fields = array('sip','nupd','to','replace','category_replace_name','link','tpl','stop','price_filter','calc','fix','image','attr','status','url','nadd','prodis','proqua','special','top','replace_manufacturer','replace_category','replace_attribute','replace_option','replace_option_value');
        $setting_item = $this->request->get['item'];
        $import_id = (int)$this->request->get['id'];

        $data = array_fill_keys($fields, '');
        $data = array_merge($data, $this->request->get);
        $data = array_merge($data, $this->unixml->text());
        $data['path'] = $this->unixml->path;
        $data['token'] = $this->session->data[$this->unixml->token()];
        $sets = explode(',', $this->request->get['set']);

        foreach($sets as $item){
          $data[$item] = 1;
          $data[$item . '_value'] = '';
        }

        $field_to_db = array(
          'product_id', 'p.product_id',
          'product_name' => 'pd.name, pd.meta_title',
          'product_model' => 'p.model',
          'product_sku' => 'p.sku',
          'product_manufacturer' => 'm.name',
          'product_description' => 'pd.description',
          'product_price' => 'p.price',
          'product_special' => '',
          'product_quantity' => 'p.quantity'
        );

        $data['setting_item'] = $setting_item;
        $data['setting_item_for_help'] = '';
        if(isset($field_to_db[$data['setting_item']])){
          $data['setting_item_for_help'] = $field_to_db[$data['setting_item']];
          if(isset($data['to_value'])){
            $data['to_value'] = '';
          }
        }

        if(in_array('replace_attribute', $sets)){
          $this->load->model('catalog/attribute_group');
          $data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();
        }

        $setting_data = $this->unixml->importGetItemSettings($import_id, $setting_item);
        foreach($setting_data as $setting_key => $setting_value){
          if($setting_value){
            $data[$setting_key] = $setting_value;
          }
        }

        $filter_data = array('start'=>0,'limit'=>99999999);

        //replace_manufacturer
          if(in_array('replace_manufacturer', $sets)){
            if($data['replace_manufacturer']){
              $data['replace_manufacturer_list'] = array();

              $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_import_manufacturer WHERE import_id = '" . $import_id . "'ORDER BY xml ASC");

              foreach($query->rows as $row){
                $data['replace_manufacturer_list'][] = array(
                  'xml' => $row['xml'],
                  'oc' => $row['oc']
                );
              }

              $this->load->model('catalog/manufacturer');
              $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers($filter_data);
            }
          }
        //replace_manufacturer

        //replace_category
          if(in_array('replace_category', $sets)){

            if($data['replace_category']){
              $data['replace_category_list'] = array();

              $sql = "SELECT * FROM " . DB_PREFIX . "unixml_import_category
                      WHERE import_id = '" . $import_id . "'
                      ORDER BY xml ASC";

              $query = $this->db->query($sql);

              foreach($query->rows as $row){

                $ocname = $this->unixml->text('text_not_load_this_category');
                if($row['oc']){
                  $category_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND category_id = '" . (int)$row['oc'] . "'");
                  if($category_query->num_rows){
                    $ocname = $row['oc'] . ' - ' . $category_query->row['name'];
                  }
                }

                $data['replace_category_list'][] = array(
                  'xml' => $row['xml'],
                  'oc' => $row['oc'],
                  'ocname' => $ocname,
                );
              }
            }
          }
        //replace_category

        //replace_attribute
          if(in_array('replace_attribute', $sets)){
            if($data['replace_attribute']){
              $data['replace_attribute_list'] = array();

              $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_import_attribute WHERE import_id = '" . $import_id . "'ORDER BY xml ASC");

              foreach($query->rows as $row){
                $data['replace_attribute_list'][] = array(
                  'xml' => $row['xml'],
                  'oc' => $row['oc']
                );
              }

              $this->load->model('catalog/attribute');
              $data['attributes'] = array();

              foreach($this->model_catalog_attribute->getAttributes($filter_data) as $attribute_item){
                $data['attributes'][$attribute_item['attribute_group']][$attribute_item['name']] = $attribute_item;
              }

            }
          }
        //replace_attribute

        $data['row_key'] = 0;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_import', $data));
      }
    //importLoadPriceSettingItem

    //importAutocompleteCategory - функция выборки категорий магазина (при сопоставлении)
      public function importAutocompleteCategory() {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
          $sql = "SELECT cp.category_id AS category_id,
            GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name,
            c1.parent_id, c1.sort_order, c1.status
            FROM " . DB_PREFIX . "category_path cp
            LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id)
            LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id)
            LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id)
            LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id)
            WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";
      		if (!empty($this->request->get['filter_name']) && $this->request->get['filter_name'] != '0') {
      			$sql .= " AND (cd2.name LIKE '%" . $this->db->escape($this->request->get['filter_name']) . "%' OR cp.category_id = '" . $this->db->escape($this->request->get['filter_name']) . "')";
      		}
      		$sql .= " GROUP BY cp.category_id ORDER BY name ASC LIMIT 0, 50";

      		$query = $this->db->query($sql);

          $json[] = array(
            'category_id' => 0,
            'name'        => $this->unixml->text('text_not_load_this_category'),
            'sort'        => 0
          );

          foreach ($query->rows as $result) {
            $json[] = array(
              'category_id' => $result['category_id'],
              'name'        => $result['category_id'] . ' - ' . strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
              'sort'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
            );
          }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
          $sort_order[$key] = $value['sort'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //importAutocompleteCategory

    //importGetXMLCategories
      public function importGetXMLCategories(){
        $data['import_id'] = $this->request->get['import_id'];
        $file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.uxc';

        if(!is_file($file)){
          $data['price_file'] = $this->request->get['xml'];
          $data['unixml_import_xml_root'] = $this->request->get['import_root'];
          $data['unixml_import_xml_categories'] = $this->request->get['import_categories'];
          $data['unixml_import_xml_category'] = $this->request->get['import_category'];
          $data['unixml_import_xml_category_id'] = $this->request->get['import_category_id'];
          $data['unixml_import_xml_category_parent_id'] = $this->request->get['import_category_parent_id'];
          $data['unixml_import_xml_category_name'] = $this->request->get['import_category_name'];
          $data['unixml_import_xml_product_multilang'] = false;
          $this->unixml->importGetXml($data);
          $this->unixml->importGetReplaceCategory($data);

          $cats_file = fopen($file, 'w');
          fwrite($cats_file, serialize($data['categories_xml']));
          fclose($cats_file);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));

      }
    //importGetXMLCategories

    //importAutocompleteCategoryXML - функция выборки категорий XML (при сопоставлении)
      public function importAutocompleteCategoryXML() {
        $json = array();

        $data['import_id'] = $this->request->get['import_id'];
        $file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.uxc';

        if(is_file($file)){
          $cats = file_get_contents($file);
          $cats = unserialize($cats);

          foreach($cats as $cat){
            $json[] = $cat['category_id'] . ' - ' . html_entity_decode($cat['category_name'], ENT_QUOTES, 'UTF-8');
          }

        }

        if (isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
          $json_tmp = $json;
          $json = array();
          foreach ($json_tmp as $cat) {
            if(stripos(mb_strtolower($cat,'UTF-8'), mb_strtolower($this->request->get['filter_name'],'UTF-8')) !== FALSE){
              $json[] = $cat;
            }
          }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //importAutocompleteCategoryXML

    //importSavePriceSettingItemSet - функция сохранения параметров конкретного элемента
      public function importSavePriceSettingItemSet($post = false) {

        if($post){
          $this->request->post = $post;
        }

        if($this->request->post['item'] == 'product_manufacturer'){
          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_manufacturer WHERE import_id = '" . (int)$this->request->post['id'] . "'");
          if(isset($this->request->post['replace_manufacturer'])){
            $replace_manufacturer = $this->request->post['replace_manufacturer'];
            unset($this->request->post['replace_manufacturer']);

            foreach($replace_manufacturer as $manufacturer){
              if($manufacturer['xml']){
                $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_import_manufacturer SET import_id = '" . (int)$this->request->post['id'] . "', xml = '" . $this->db->escape($manufacturer['xml']) . "', oc = '" . $this->db->escape($manufacturer['oc']) . "'");
              }
            }
          }
        }

        if($this->request->post['item'] == 'product_category_id'){
          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_category WHERE import_id = '" . (int)$this->request->post['id'] . "'");
          if(isset($this->request->post['replace_category'])){
            $replace_category = $this->request->post['replace_category'];
            unset($this->request->post['replace_category']);

            foreach($replace_category as $category){
              if($category['xml']){
                $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_import_category SET import_id = '" . (int)$this->request->post['id'] . "', xml = '" . $this->db->escape($category['xml']) . "', oc = '" . $this->db->escape($category['oc']) . "'");
              }
            }
          }
        }

        if($this->request->post['item'] == 'product_attributes'){
          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_attribute WHERE import_id = '" . (int)$this->request->post['id'] . "'");
          if(isset($this->request->post['replace_attribute'])){
            $replace_attribute = $this->request->post['replace_attribute'];
            unset($this->request->post['replace_attribute']);

            foreach($replace_attribute as $attribute){
              if($attribute['xml']){
                $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_import_attribute SET import_id = '" . (int)$this->request->post['id'] . "', xml = '" . $this->db->escape($attribute['xml']) . "', oc = '" . $this->db->escape($attribute['oc']) . "'");
              }
            }
          }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'import.price.setting.item' AND name = '" . (int)$this->request->post['id'] . "." . $this->db->escape($this->request->post['item']) ."'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET code = 'import.price.setting.item', name = '" . (int)$this->request->post['id'] . "." . $this->db->escape($this->request->post['item']) ."', value = '" . $this->db->escape(serialize($this->request->post)) . "', date_edit = NOW()");

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));
      }
    //importSavePriceSettingItemSet


  //Функции импорта

  //Функции экспорта

    //exportAddFeed - функция добавления фида
      public function exportAddFeed() {
        $data['error'] = false;
        $code = $this->request->post['code'];
        $from = $this->request->post['from'];
        $name = $this->request->post['name'];

        if(!preg_match('~^[a-z0-9_]*$~i',$code)) {
          $data['error'] = $this->unixml->text('text_delete_from_code');
        }

        $copyset = ($this->request->post['copyset'] == 'false')?0:1;

        if(!$name){$data['error'] = $this->unixml->text('text_entry_feed_name');}
        if(!$code){$data['error'] = $this->unixml->text('text_entry_feed_code');}

        $feeds = $this->unixml->exportGetFeeds(); //берем все выгрузки (смотрим в базе)
        if(isset($feeds[$code])){ //если уже есть такая выгрузка в базе
          $data['error'] = $this->unixml->text('text_code_exists') . $code;
        }

        if(!$data['error']){
          $file = DIR_CATALOG . 'controller/' . $this->unixml->path . '/' . $from . '.php';
          $newfile = DIR_CATALOG . 'controller/' . $this->unixml->path . '/' . $code . '.php';

          $files = $this->unixml->exportGetFeedFiles(); //берем все файлы выгрузки (смотрим в в файлах)
          if(in_array($code, $files)){ //если уже есть файл выгрузки - мы не создаем копии файлов
            $copy = true; //ставим загрушку что якобы скопировали
            $newfile = $file; //новый файл тот что и старый
            $code = $from; //добавляем что на что меняем - по сути что бы ничего не заменять - заглушка
            $exist = true;
          }else{ //если файла нет - копируем из источника
            $copy = copy($file, $newfile);
          }

          if (!$copy) {
            $data['error'] = $this->unixml->text('text_error_file_create') . $this->unixml->path;
          }else{
            //заменяем содержимое
            $filedata = file_get_contents($newfile); //берем содержимое контроллера
            $replace_from = array("FeedUnixml" . ucfirst(str_replace('_', '', $from)), "feed = '" . $from . "'");
            $replace_to = array('FeedUnixml' . ucfirst(str_replace('_', '', $code)), "feed = '" . $code . "'");
            $filedata = str_replace($replace_from, $replace_to, $filedata); // заменяем в контроллере

            $fp = fopen($newfile, "w");
            fwrite($fp, $filedata);
            fclose($fp);

            //обновляем списки
            $this->db->query("INSERT INTO `" . DB_PREFIX . "unixml_setting` SET `code` = 'export.feeds', `name` = '" . $this->db->escape($code) . "', `value` = '" . serialize(array($name, '', '', 100)) . "', `date_edit` = NOW()");

            if($copyset){
              $setting_data = $this->exportSettingExport($from); //забираем настройки
              $this->exportSettingImport($code, $setting_data); //импортируем настройки
            }

            $data['success'] = str_replace('{{CODE}}', $code, $this->unixml->text('text_file_create_ok'));
            if(isset($exist)){ //если есть файл
              $data['success'] = str_replace('{{CODE}}', $code, $this->unixml->text('text_file_db_create_ok'));
            }
          }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
      }
    //exportAddFeed

    //exportFeedToTrash - функция отправки фида в корзину
      public function exportFeedToTrash(){
        $trash = array();
        $trash[] = $this->request->get['feed']; //добавили новый

        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'trash'");
        if($query->num_rows){
          $trash = array_merge($trash, explode(',', $query->row['value'])); //добавили те что были в корзине
          $this->db->query("UPDATE " . DB_PREFIX . "unixml_setting SET value = '" . $this->db->escape(implode(',', $trash)) . "', date_edit = NOW() WHERE code = 'export.setting' AND name = 'trash'");
        }else{
          $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET code = 'export.setting', name = 'trash', value = '" . $this->db->escape(implode(',', $trash)) . "', date_edit = NOW()");
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));
      }
    //exportFeedToTrash

    //exportFeedToList - функция отправки фида в список из корзины
      public function exportFeedToList(){
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'trash'");
        $trash = array_flip(explode(',', $query->row['value']));
        if(isset($trash[$this->request->get['feed']])){
          unset($trash[$this->request->get['feed']]);
        }
        $this->db->query("UPDATE " . DB_PREFIX . "unixml_setting SET value = '" . $this->db->escape(implode(',', array_flip($trash))) . "', date_edit = NOW() WHERE code = 'export.setting' AND name = 'trash'");

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));
      }
    //exportFeedToList

    //exportDeleteFeed - функция удаления фида
      public function exportDeleteFeed() {
        $code = $this->request->post['code'];

        $this->db->query("DELETE FROM `" . DB_PREFIX . "unixml_setting` WHERE `code` = 'export.feeds' AND `name` = '" . $this->db->escape($code) . "'");

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));
      }
    //exportDeleteFeed

    //exportGetXMLFileInfo - функция получения информации о файле
      public function exportGetXMLFileInfo() {
        $file = str_replace('system', 'price', DIR_SYSTEM) . $this->request->get['file'] . '.xml';
        $data = $this->unixml->text('text_file_not_created');
        if(is_file($file)){
          $data = $this->unixml->text('text_file_created') . date("d.m.Y [H:i:s]", filectime($file));
          $data .= $this->unixml->text('text_file_volume') . $this->unixml->filesize($file);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
      }
    //exportGetXMLFileInfo

    //exportLoadfeedList - функция загрузки списка фидов в админке
      public function exportLoadfeedList() {
        $search = false;
        if(isset($this->request->get['search']) && $this->request->get['search']){
          $search = $this->request->get['search'];
        }
        $data = $this->unixml->exportGetfeedList($search);
        $data = array_merge($data, $this->unixml->text());
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_export', $data));
      }
    //exportLoadfeedList

    //exportLoadTrashList - функция загрузки корзины в админке
      public function exportLoadTrashList() {
        $data['trash'] = array();
        $data = $this->unixml->exportGetfeedList(false, true);
        $data = array_merge($data, $this->unixml->text());
        $i = 1;
        foreach(array_reverse($data['exports']) as $export){
          $export['export_num'] = $i;
          $data['trash'][] = $export;
          $i++;
        }

        unset($data['exports']);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_export', $data));
      }
    //exportLoadTrashList

    //exportTrashToggle - функция сохранения статуса корзины
      public function exportTrashToggle() {
        if(!$this->request->get['status']){ //если развернуто
          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'trash_toggle'");
        }else{ //если свернули
          $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET value = '1', code = 'export.setting', name = 'trash_toggle'");
        }
      }
    //exportTrashToggle

    //exportSaveFeedSorts - функция сохранения сортировки фидов
      public function exportSaveFeedSorts() {
        if(isset($this->request->post['sorts'][0])){
          unset($this->request->post['sorts'][0]);
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'feed_sorts'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET value = '" . $this->db->escape(serialize($this->request->post['sorts'])) . "', code = 'export.setting', name = 'feed_sorts'");
      }
    //exportSaveFeedSorts

    //exportLoadFeedSetting - функция загрузки параметров конкретного фида
      public function exportLoadFeedSetting() {
        $data = $this->unixml->text();
        $data['feed'] = $feed = $this->request->get['feed'];
        $data['path'] = $this->unixml->path;
        $feed_setting = $this->unixml->exportGetFeeds($feed);

        $this->load->model('setting/setting');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('localisation/language');
        $this->load->model('localisation/currency');
        $this->load->model('localisation/stock_status');

        $data['token'] = $this->session->data[$this->unixml->token()];
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers(0);
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();
        $data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
        $data['seopro'] = $this->unixml->getSeopro();
        $data['categories'] = $this->unixml->exportGetAllCategories();
        $data['export_fields'] = $this->unixml->exportGetExportFields();
        $data['option_multiplier_key'] = 0;
        $data['category_match_row'] = 0;
        $data['attribute_row'] = 0;
        $data['product_markup_row'] = 0;
        $data['replace_name_row'] = 0;
        $data['param_row'] = 0;

        $field = 'products';
        $data[$this->unixml->varname('products')] = array();
        if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
          $data[$this->unixml->varname($field)] = $this->request->post[$this->unixml->varname($field, $feed)];
        } elseif ($this->unixml->exportGetSetting($field, $feed) != '') {
          $data_unixml_products = explode(',', $this->unixml->exportGetSetting($field, $feed));
          foreach($data_unixml_products as $data_unixml_product_id){
            $product_info = $this->model_catalog_product->getProduct($data_unixml_product_id);
            if($product_info){
              $data[$this->unixml->varname('products')][] = array(
                'product_id' => $product_info['product_id'],
                'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product_info['product_id'], true),
                'view' => HTTPS_CATALOG . 'index.php?route=product/product&product_id=' . $product_info['product_id'],
                'name' => 'id:' . $product_info['product_id'] . ', model:' . $product_info['model'] . ' - ' . $product_info['name'] . ' / ' . $this->currency->format($product_info['price'], $this->config->get('config_currency'))
              );
            }
          }

        } else {
          $data[$this->unixml->varname($field)] = array();
        }

        foreach($this->unixml->exportGetAllSettingFields($feed) as $func => $field){
          if($field == 'categories' or $field == 'manufacturers'){
            if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
              $data[$this->unixml->varname($field)] = $this->request->post[$this->unixml->varname($field, $feed)];
            } elseif ($this->unixml->exportGetSetting($field, $feed) != '') {
              $data[$this->unixml->varname($field)] = explode(',', $this->unixml->exportGetSetting($field, $feed));
            } else {
              $data[$this->unixml->varname($field)] = array();
            }
          }elseif($field == 'field_price'){
            if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
              $data[$this->unixml->varname($field)] = $this->request->post[$this->unixml->varname($field, $feed)];
            } elseif ($this->unixml->exportGetSetting($field, $feed) != '') {
              $data[$this->unixml->varname($field)] = $this->unixml->exportGetSetting($field, $feed);
            } else {
              $data[$this->unixml->varname($field)] = 'p.price';
            }
          }elseif($field == 'field_id'){
            if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
              $data[$this->unixml->varname($field)] = $this->request->post[$this->unixml->varname($field, $feed)];
            } elseif ($this->unixml->exportGetSetting($field, $feed) != '') {
              $data[$this->unixml->varname($field)] = $this->unixml->exportGetSetting($field, $feed);
            } else {
              $data[$this->unixml->varname($field)] = 'p.product_id';
            }
          }elseif(in_array($field, array('attributes','replace_name','additional_params','category_match','product_markup','option_multiplier'))){
            $data[$this->unixml->varname($field)] = array();
            if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
              $data[$this->unixml->varname($field)] = $this->request->post[$this->unixml->varname($field, $feed)];
            }  else {
              $data[$this->unixml->varname($field)] = $this->$func($feed);
            }
          }else{
            if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
              $data[$this->unixml->varname($field)] = $this->request->post[$this->unixml->varname($field, $feed)];
            } else {
              $data[$this->unixml->varname($field)] = $this->unixml->exportGetSetting($field, $feed);
            }
          }
        }

        //минус поля
        $data['minus'] = '';
        $feed_setting['minus'] .= ',1-5,1-6,1-7'; //По умолчанию отключаем все лишнее
        if($feed_setting['minus']){
          foreach(explode(',', $feed_setting['minus']) as $minus_field){
            $data['minus'] .= '#export-block-' . $minus_field . ',';
            $data['minus'] .= 'li[data-id="export-block-' . $minus_field . '"],';
          }
          $data['minus'] = rtrim($data['minus'], ',');
        }
        //минус поля
        //плюс поля
        $data['plus'] = '';
        if($feed_setting['plus']){
          foreach(explode(',', $feed_setting['plus']) as $minus_field){
            $data['plus'] .= '#export-block-' . $minus_field . ',';
            $data['plus'] .= 'li[data-id="export-block-' . $minus_field . '"],';
          }
          $data['plus'] = rtrim($data['plus'], ',');
        }
        //плюс поля

        $this->lang = $data[$this->unixml->varname('language')];

        $data[$this->unixml->varname('options')] = $this->unixml->exportGetOptionList($data[$this->unixml->varname('language')]);
        $data['data_feed'] = HTTPS_CATALOG . 'index.php?route=' . $this->unixml->path . '/' . $feed;

        $data['action'] = $this->url->link($this->unixml->path . '/exportSaveFeedSetting', $this->unixml->token() . '=' . $data['token'], true);
        $data['export_setting'] = $this->url->link($this->unixml->path . '/exportSettingExport', $this->unixml->token() . '=' . $data['token'], true);

        $data[$this->unixml->varname('xml_name')] = trim($data[$this->unixml->varname('xml_name')], '/');
        $data['xml_filename'] = $data[$this->unixml->varname('xml_name')]?$data[$this->unixml->varname('xml_name')]:$feed;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_export', $data));
      }
    //exportLoadFeedSetting

    //importSettingExport - функция экспорта настроек конкретного импорта
      public function importSettingExport(){
        $import_id = $this->request->get['import_id'];
        $settings = 'Backup setting for import #' . $import_id . ' from UniXML Pro (Support: info@unixml.pro) | Time: ' . date('d.m.Y [H:i]') . PHP_EOL . PHP_EOL;
        $setting_array = $this->importLoadPriceSetting($import_id);
        unset($setting_array['unixml_import_product_link']);
        $settings .= base64_encode(serialize($setting_array));

        $this->response->addHeader('Content-disposition: attachment; filename=unixml_' . $import_id . '_' . date('d') . date('m') . date('Y') . '.uxib');
        $this->response->addHeader('Content-type: text/plain');
        $this->response->setOutput($settings);
      }
    //importSettingExport

    //importSettingImport - функция импорта настроек конкретного фида
      public function importSettingImport(){
        $file = false;

        $ext = $this->request->files['file']['name'];
        $ext = explode('.', $ext);
        $ext = end($ext);

        $import_id = $this->request->get['import_id'];
        if(isset($this->request->files['file'])){
          $file = file_get_contents($this->request->files['file']['tmp_name'], 'rb');
        }

        if($file && $ext == 'uxib'){
          $file_data = explode(PHP_EOL, $file);
          if(isset($file_data[0]) && isset($file_data[1]) && isset($file_data[2])){
            if(substr($file_data[0], 0, 6) == "Backup" && empty($file_data[1]) && !empty($file_data[2])){

                //setting
                $settings = unserialize(base64_decode($file_data[2]));

                $settings['unixml_import_id'] = $import_id;

                $fieldset = array();
                if(isset($settings['fieldset'])){
                  $fieldset = $settings['fieldset'];
                  unset($settings['fieldset']);
                }

                $this->importSavePriceSetting($settings);

                if($fieldset){
                  foreach($fieldset as $fieldset_block => $fieldset_data){
                    $post = $fieldset_data;
                    $post['id'] = $import_id;
                    $post['item'] = $fieldset_block;
                    $this->importSavePriceSettingItemSet($post);
                  }
                }

                $json['success'] = true;

              }else{
                $json['error'] = $this->unixml->text('text_import_setting_error');
              }
          }
        }else{
          $json['error'] = $this->unixml->text('text_import_setting_error');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //importSettingImport

    //exportSettingExport - функция экспорта настроек конкретного фида
      public function exportSettingExport($fromcopy = false){

        if(!$fromcopy){
          $feed = $this->request->get['feed'];
        }else{
          $feed = $fromcopy;
        }

        $settings = 'Backup setting for ' . $feed . ' from UniXML Pro (Support: info@unixml.pro) | Time: ' . date('d.m.Y [H:i]') . PHP_EOL . PHP_EOL;
        $setting_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_setting WHERE `code` = '" . $this->db->escape("export.setting." . $feed) . "'");

        if($setting_query->num_rows){
          $setting_array = array();
          foreach ($setting_query->rows as $setting_row) {
            $setting_array[$setting_row['name']] = $setting_row['value'];
          }
          $settings .= base64_encode(serialize($setting_array));
        }

        foreach($this->unixml->insertTables as $table => $fields){
          $table_data_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_" . $table . " WHERE feed = '" . $this->db->escape($feed) . "'");
          $settings .= PHP_EOL . PHP_EOL . base64_encode(serialize($table_data_query->rows));
        }

        if($fromcopy){ //если при копировании
          return $settings;
        }else{
          $this->response->addHeader('Content-disposition: attachment; filename=unixml_' . $feed . '_' . date('d') . date('m') . date('Y') . '.uxb');
          $this->response->addHeader('Content-type: text/plain');
          $this->response->setOutput($settings);
        }
      }
    //exportSettingExport

    //exportSettingImport - функция импорта настроек конкретного фида
      public function exportSettingImport($fromcopy = false, $setting_data = false){
        $file = false;
        $json = array();

        $ext = 'uxb';
        if(isset($this->request->files['file']['name']) && $this->request->files['file']['name']){
          $ext = $this->request->files['file']['name'];
          $ext = explode('.', $ext);
          $ext = end($ext);
        }

        if(!$fromcopy){
          $feed = $this->request->get['feed'];
          if(isset($this->request->files['file'])){
            $file = file_get_contents($this->request->files['file']['tmp_name'], 'rb');
          }
        }else{
          $feed = $fromcopy;
          $file = $setting_data;
        }

        if($file && $ext == 'uxb'){
          $file_data = explode(PHP_EOL, $file);
          if(isset($file_data[0]) && isset($file_data[1]) && isset($file_data[2])){
            if(substr($file_data[0], 0, 6) == "Backup" && empty($file_data[1]) && !empty($file_data[2])){
              $feed_in_file_array = explode(" from", str_replace("Backup setting for ", "", $file_data[0]));
              if(isset($feed_in_file_array[0])){
                $feed_in_file = $feed_in_file_array[0];


                //setting
                $settings = unserialize(base64_decode($file_data[2]));

                if($settings){
                  $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE `code` = '" . $this->db->escape("export.setting." . $feed) . "'");
                  foreach($settings as $setting_name => $setting_value){
                    if($setting_name == 'xml_name'){
                      $setting_value = $feed;
                    }
                    $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET `code` = 'export.setting." . $this->db->escape($feed) . "', `name` = '" . $this->db->escape($setting_name) ."', `value` = '" . $this->db->escape($setting_value) . "', `date_edit` = NOW()");
                  }
                }
                //setting

                //tables
                $start_table_row = 4;
                foreach($this->unixml->insertTables as $table => $fields){
                  if(isset($file_data[$start_table_row])){
                    $table_data = unserialize(base64_decode($file_data[$start_table_row]));

                    if($table_data){
                      $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_" . $table . " WHERE feed = '" . $this->db->escape($feed) . "'");
                      foreach($table_data as $data_row){
                        $query_fields = "";
                        foreach($data_row as $data_key => $data_value){
                          if($data_key != 'item_id'){
                            $query_fields .= "`" . $data_key . "` = '" . $this->db->escape($data_value) . "', ";
                          }
                        }
                        $sql = "INSERT INTO " . DB_PREFIX . "unixml_" . $table . " SET " . rtrim(trim($query_fields), ',');
                        $sql = str_replace($feed_in_file, $feed, $sql);
                        $this->db->query($sql);
                      }
                    }
                  }

                  $start_table_row += 2;
                }
                //tables

                $json['success'] = true;
              }
            }else{
              $json['error'] = $this->unixml->text('text_import_setting_error');
            }
          }else{
            $json['error'] = $this->unixml->text('text_import_setting_error2');
          }
        }else{
          $json['error'] = $this->unixml->text('text_import_setting_error');
        }

        if(!$fromcopy){
          $this->response->addHeader('Content-Type: application/json');
          $this->response->setOutput(json_encode($json));
        }
      }
    //exportSettingImport

    //exportGetCategoryPath - функция выборки сложенности категорий
      private function exportGetCategoryPath($categories = array()){
        $categories_path = array();

        if(!$categories){ //если не выбраны категории - забираем все категории с базы
          $category_query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE status = 1");
          $categories = array_column($category_query->rows, 'category_id');
        }

        //здесь запрос на получения path категорий которые уже есть в базе
        $categories_path = $this->unixml->exportGetCategoriesPath();
        //здесь запрос на получения path категорий

        foreach($categories as $category_id){
          if(!isset($categories_path[$category_id])){
            $categories_path[$category_id] = $this->unixml->getPathByCategory($category_id);
          }
        }

        return serialize($categories_path);
      }
    //exportGetCategoryPath

    //exportSaveFeedSetting - функция сохранения настроек фида
      public function exportSaveFeedSetting() {

        $feed = $this->request->post['feed'];
        unset($this->request->post['feed']);

        $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'export.setting." . $this->db->escape($feed) . "'");

        //переводим в строку массивы
        foreach(array('products','manufacturers') as $field){
          if (isset($this->request->post[$this->unixml->varname($field, $feed)])) {
            $this->request->post[$this->unixml->varname($field, $feed)] = implode(',', $this->request->post[$this->unixml->varname($field, $feed)]);
          }
        }
        //переводим в строку массивы

        //категории - забираем вложенность
        if (isset($this->request->post[$this->unixml->varname('categories', $feed)])) {
          $categories = $this->request->post[$this->unixml->varname('categories', $feed)];
          $this->request->post['category_path'] = $this->exportGetCategoryPath($categories);
          $this->request->post[$this->unixml->varname('categories', $feed)] = implode(',', $categories);
        }else{
          $this->request->post['category_path'] = $this->exportGetCategoryPath();
        }
        //категории - забираем вложенность

        $currency_query = $this->db->query("SELECT decimal_place FROM " . DB_PREFIX . "currency WHERE currency_id = '" . (int)$this->request->post[$this->unixml->varname('currency', $feed)] . "'");
        $this->request->post['decimal_place'] = $currency_query->row['decimal_place'];

        //наборы опций - переводим в serialize
        if (isset($this->request->post[$this->unixml->varname('option_multiplier', $feed)])) {
          $this->request->post[$this->unixml->varname('option_multiplier', $feed)] = serialize($this->request->post[$this->unixml->varname('option_multiplier', $feed)]);
        }
        //наборы опций - переводим в serialize

        //кастомный код - пишем в system
        if (isset($this->request->post[$this->unixml->varname('custom_xml_after_sql', $feed)])) {
          $this->exportInsertCustomCode($this->request->post[$this->unixml->varname('custom_xml_after_sql', $feed)], 'ExportCustomAfter', $feed);
        }
        //кастомный код - пишем в system

        //кастомный код - пишем в system
        if (isset($this->request->post[$this->unixml->varname('custom_xml', $feed)])) {
          $this->exportInsertCustomCode($this->request->post[$this->unixml->varname('custom_xml', $feed)], 'ExportCustomXml', $feed);
        }
        //кастомный код - пишем в system

        //кастомный код - пишем в system
        if (isset($this->request->post[$this->unixml->varname('custom_xml_final', $feed)])) {
          $this->exportInsertCustomCode($this->request->post[$this->unixml->varname('custom_xml_final', $feed)], 'ExportCustomFinal', $feed);
        }
        //кастомный код - пишем в system

        //пишем все в нужные табицы
        foreach($this->unixml->insertTables as $insert_table => $insert_fields){
          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_" . $insert_table . " WHERE feed = '" . $this->db->escape($feed) . "'");
          if (!empty($this->request->post[$this->unixml->varname($insert_table, $feed)]) && is_array($this->request->post[$this->unixml->varname($insert_table, $feed)])) {
            $attributes = $this->request->post[$this->unixml->varname($insert_table, $feed)];
            foreach ($attributes as $attribute) {
              $fields_db = "";
              foreach($insert_fields as $field_item){
                if($field_item == 'replace_where' && !isset($attribute[$field_item])){ //фикс, когда не выбрали где земенять
                  $attribute[$field_item] = '';
                }
                $value = '';
                if(isset($attribute[$field_item])){
                  $value = $attribute[$field_item];
                  if (!empty($value) && is_array($value)) {
                    $value = implode(',', array_unique($value));
                  }
                }

                $fields_db .= " " . $field_item . " = '" . $this->db->escape($value) . "',";
              }

              $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_" . $insert_table . " SET " . $fields_db . " feed = '" . $this->db->escape($feed) . "'");
            }
          }
        }
        //пишем все в нужные табицы

        //пишем в базу
        foreach($this->request->post as $setting_name => $setting_value){
          if(is_array($setting_value)){$setting_value = 'uss:' . serialize($setting_value);}
          $setting_name = str_replace('unixml_' . $feed . '_', '', $setting_name);
          if(!array_key_exists($setting_name, $this->unixml->insertTables)){ //если эти данные не пишем в отдельные таблицы
            $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET code = 'export.setting." . $this->db->escape($feed) . "', name = '" . $this->db->escape($setting_name) ."', value = '" . $this->db->escape($setting_value) . "', date_edit = NOW()");
          }
        }
        //пишем в базу

        //фикс - удаляем данные со стандартной таблицы setting это для перехода c версии на версию (т.к. данные будут записаны в таблицу unixml_setting  чистим стандартный конфиг)
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'unixml' AND `key`LIKE 'unixml_" . $this->db->escape($feed) . "_%'");
        //фикс - удаляем данные со стандартной таблицы setting это для перехода c версии на версию (т.к. данные будут записаны в таблицу unixml_setting  чистим стандартный конфиг)

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(true));
      }
    //exportSaveFeedSetting

    //exportImportMarkupProduct - функция импорта списка товаров в группу наценки
      public function exportImportMarkupProduct() {
        $this->load->model('catalog/product');
        $json = array();
        $json['error'] = 0;
        $json['success'] = 0;

        $field = 'product_id';
        $table = 'product';
        if(isset($this->request->post['import_field']) && $this->request->post['import_field']){
          $field = trim($this->request->post['import_field']);
          $table_data = explode('.', $field);
          $field = str_replace(array('p.','pd.'), '', $field);
          $table = ($table_data[0]=='pd')?'product_description':'product';
        }else{
          $json['error'] = $this->unixml->text('text_import_pro_error');
        }

        if(isset($this->request->post['products']) && $this->request->post['products']){
          $products = $this->request->post['products'];
        }else{
          $json['error'] = $this->unixml->text('text_import_pro_error2');
        }

        if(!$json['error']){
          $separator = PHP_EOL;
          if(isset($this->request->post['import_serapator']) && $this->request->post['import_serapator']){
            $separator = $this->request->post['import_serapator'];
          }

          $products_array = explode($separator, trim($products));
          if($products_array){
            $json['count'] = count($products_array);
            foreach ($products_array as $product_item) {
              $product_id_query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . $table . " WHERE " . $this->db->escape($field) . " = '" . $this->db->escape(trim($product_item)) . "' LIMIT 1");
              if(isset($product_id_query->row['product_id']) && $product_id_query->row['product_id']){
                $product_info = $this->model_catalog_product->getProduct($product_id_query->row['product_id']);
                if($product_info){
                  $json['products'][] = array(
                    'product_id' => $product_info['product_id'],
                    'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product_info['product_id'], true),
                    'view' => HTTPS_CATALOG . 'index.php?route=product/product&product_id=' . $product_info['product_id'],
                    'name' => 'id:' . $product_info['product_id'] . ', model:' . $product_info['model'] . ' - ' . $product_info['name'] . ' / ' . $this->currency->format($product_info['price'], $this->config->get('config_currency'))
                  );
                  $json['success']++;
                }
              }
            }
          }else{
            $json['error'] = $this->unixml->text('text_import_pro_error3');
          }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //exportImportMarkupProduct

    //exportAutocompleteOption - функция выборки списка опций
      public function exportAutocompleteOption() {
        $json = array();
        $filter = $this->request->get['filter_name'];

        $sql = "SELECT option_id, name FROM " . DB_PREFIX . "option_description";
        if($filter){
          $sql .= " WHERE LCASE(name) LIKE '%" . $this->db->escape(utf8_strtolower($filter)) . "%'";
        }
        $sql .= " GROUP BY option_id ORDER BY name ASC LIMIT 0, 20";

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {

          $sql_count = $this->db->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$result['option_id'] . "'");

          $sql_values = $this->db->query("SELECT GROUP_CONCAT(name SEPARATOR ', ') AS vals FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$result['option_id'] . "' GROUP BY language_id ORDER BY language_id, name ASC");

          $json[] = array(
            'option_id' => $result['option_id'],
            'name'      => $result['name'],
            'products'  => $sql_count->row['count'],
            'values'    => $sql_values->row['vals']
          );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //exportAutocompleteOption

    //exportAutocompleteProduct - функция выборки товаров для добавления в списки
      public function exportAutocompleteProduct() {
        $json = array();
        $filter = $this->request->get['filter_name'];
        $feed = $this->request->get['feed'];

        $not_ids = false;

        if(isset($this->request->post['unixml_' . $feed . '_products'])){
          $not_ids = implode(',', $this->request->post['unixml_' . $feed . '_products']);
        }

        if (strlen($filter) > 0) {
          $sql = "SELECT p.product_id, p.model, pd.name, p.price FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                  WHERE (pd.name LIKE '%" . $this->db->escape($filter) . "%'
                  OR p.model LIKE '%" . $this->db->escape($filter) . "%'
                  OR p.sku LIKE '%" . $this->db->escape($filter) . "%'
                  OR p.product_id = '" . $this->db->escape($filter) . "')";

          if($not_ids){
            $sql .= " AND p.product_id NOT IN(" . $not_ids . ")";
          }

          $sql .= " GROUP BY p.product_id ORDER BY pd.name ASC, p.model ASC, p.product_id ASC LIMIT 0, 50";

          $query = $this->db->query($sql);

          foreach ($query->rows as $result) {
            $json[] = array(
              'product_id' => $result['product_id'],
              'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $result['product_id'], true),
              'view' => HTTPS_CATALOG . 'index.php?route=product/product&product_id=' . $result['product_id'],
              'name'       => 'id:' . $result['product_id'] . ', model:' . $result['model'] . ' - ' . $result['name'] . ' / ' . $this->currency->format($result['price'], $this->config->get('config_currency'))
            );
          }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //exportAutocompleteProduct

    //exportAutocompleteCategory - функция выборки категорий магазина (при сопоставлении)
      public function exportAutocompleteCategory() {
        $json = array();

        if (isset($this->request->get['filter_name'])) {

          $sql = "SELECT cp.category_id AS category_id,
            GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name,
            c1.parent_id, c1.sort_order, c1.status
            FROM " . DB_PREFIX . "category_path cp
            LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id)
            LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id)
            LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id)
            LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id)
            WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";
      		if (!empty($this->request->get['filter_name'])) {
      			$sql .= " AND (cd2.name LIKE '%" . $this->db->escape($this->request->get['filter_name']) . "%' OR cp.category_id = '" . $this->db->escape($this->request->get['filter_name']) . "')";
      		}
      		$sql .= " GROUP BY cp.category_id ORDER BY name ASC LIMIT 0, 20";

      		$query = $this->db->query($sql);

          foreach ($query->rows as $result) {
            $json[] = array(
              'category_id' => $result['category_id'],
              'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
            );
          }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
          $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //exportAutocompleteCategory

    //exportAutocompleteAttribute - функция выборки атрибутов для добавления в списки сопоставления
      public function exportAutocompleteAttribute() {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
          $this->load->model('catalog/attribute');

          $results = $this->model_catalog_attribute->getAttributes(array('filter_name' => $this->request->get['filter_name'], 'start' => 0, 'limit' => 20));

          foreach ($results as $result) {
            $json[] = array(
              'attribute_id'    => $result['attribute_id'],
              'name'            => $result['attribute_group'] . ': ' . strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
              'attribute_group' => $result['attribute_group'],
              'language_id'     => $result['language_id']
            );
          }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
          $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //exportAutocompleteAttribute

    //exportInsertCustomCode - функция встатки кастомного кода в system
      private function exportInsertCustomCode($code, $filename, $feed){

        $this->unixml->checkDir('unixml/' . $feed);

        //добавляем код
        $new_data  = '<?php' . PHP_EOL;
        $new_data .= '//Этот файл - кастомный код модуля UniXML, фид ' . $feed . PHP_EOL;
        $new_data .= '//info: https://unixml.pro' . PHP_EOL . PHP_EOL;
        $new_data .= $code;

        $post_data = html_entity_decode($new_data, ENT_QUOTES, 'UTF-8');
        $fp = fopen(DIR_SYSTEM . 'unixml/' . $feed . '/' . $filename, 'w');
        fwrite($fp, $post_data);
        fclose($fp);
        //добавляем код
      }
    //exportInsertCustomCode

    //exportGoogleCategory - функции выборки google категорий
      public function exportGoogleCategory() {
        $categories = array();

        $categories_data = $this->cache->get('google.categories');
        if(!$categories_data){
          $categories_data = @file_get_contents('https://www.google.com/basepages/producttype/taxonomy-with-ids.ru-RU.txt');
          $this->cache->set('google.categories', $categories_data);
        }

        $category_rows = explode(PHP_EOL, $categories_data);
        foreach($category_rows as $category_item){
          $category_data = explode(' - ', $category_item);
          if(isset($category_data[0]) && isset($category_data[1])){
            if (isset($this->request->get['filter_name'])) {
              if(trim($this->request->get['filter_name']) != ''){
                $pos = strpos(mb_strtolower($category_data[0] . $category_data[1], 'UTF-8'), mb_strtolower($this->request->get['filter_name'], 'UTF-8'));
                if ($pos !== false) { //если что-то найдено
                  $categories[$category_data[1]] = array(
                    'category_id' => $category_data[0],
                    'name' => $category_data[0] . ' - ' . $category_data[1]
                  );
                }
              }
            }else{
              $categories[$category_data[1]] = array(
                'category_id' => $category_data[0],
                'name' => $category_data[0] . ' - ' . $category_data[1]
              );
            }
          }
        }

        ksort($categories);

        $categories = array_slice($categories, 0, 20);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($categories));
      }
    //exportGoogleCategory

    //exportKidstaffCategory - функции выборки kidstaff категорий
      public function exportKidstaffCategory() {
        $categories = array();

        $categories_data = $this->cache->get('kidstaff.categories');
        if(!$categories_data){
          $categories_data = @simplexml_load_file('https://www.kidstaff.com.ua/categories.xml');
          $this->cache->set('kidstaff.categories', $categories_data);
        }

        foreach ($categories_data['category'] as $category_item) {

          if(isset($category_item['id']) && isset($category_item['name'])){
            if (isset($this->request->get['filter_name'])) {
              $pos = strpos(mb_strtolower($category_item['id'] . $category_item['name'], 'UTF-8'), mb_strtolower($this->request->get['filter_name'], 'UTF-8'));
              if ($pos !== false) { //если что-то найдено
                $categories[$category_item['name']] = array(
                  'category_id' => $category_item['id'],
                  'name' => $category_item['id'] . ' - ' . $category_item['name']
                );
              }
            }else{
              $categories[$category_item['name']] = array(
                'category_id' => $category_item['id'],
                'name' => $category_item['id'] . ' - ' . $category_item['name']
              );
            }
          }

        }

        ksort($categories);

        $categories = array_slice($categories, 0, 20);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($categories));
      }
    //exportKidstaffCategory

    //exportGetOptionData - функция выборки опций и информации опций
      private function exportGetOptionData($feed){
        $options = array();

        $option_multiplier = $this->unixml->exportGetSetting('option_multiplier', $feed);

        if($option_multiplier){
          foreach (unserialize($option_multiplier) as $option_key => $option_block) {
            foreach ($option_block as $option_id) {
              $option_data = $this->db->query("SELECT name FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
              if($option_data->num_rows){
                $sql_count = $this->db->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");
                $sql_values = $this->db->query("SELECT GROUP_CONCAT(name SEPARATOR ', ') AS vals FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "' GROUP BY language_id ORDER BY language_id, name ASC");

                $options[$option_key][$option_id] = array(
                  'option_id' => $option_id,
                  'name'      => $option_data->row['name'],
                  'products'  => $sql_count->row['count'],
                  'values'    => $sql_values->row['vals']
                );
              }
            }
          }
        }

        return $options;
      }
    //exportGetOptionData

    //exportGetAttributeList - функция выборки атрибутов
      private function exportGetAttributeList($feed){
        $attributes = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_attributes WHERE feed = '" . $this->db->escape($feed) . "' ORDER BY item_id ASC");
        foreach($query->rows as $row){
          $attribute_name_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$row['attribute_id'] . "' AND language_id = '" . (int)$this->lang . "'");
          if(!$attribute_name_query->num_rows){
            $attribute_name_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$row['attribute_id'] . "'");
          }
          if($attribute_name_query->num_rows){
            $attribute_name = $attribute_name_query->row['name'];
          }else{
            $attribute_name = $this->unixml->text('text_attribute_not_found');
          }

          $attributes[] = array(
            'attribute_id'   => $row['attribute_id'],
            'attribute_name' => $attribute_name,
            'xml_name'   => $row['xml_name']
          );
        }

        return $attributes;
      }
    //exportGetAttributeList

    //exportGetReplaceNameList - функция выборки списка замен
      private function exportGetReplaceNameList($feed){
        $replace = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_replace_name WHERE feed = '" . $this->db->escape($feed) . "' ORDER BY item_id ASC");
        foreach($query->rows as $row){
          $replace[] = array(
            'name_from'     => $row['name_from'],
            'name_to'       => $row['name_to'],
            'replace_where' => explode(',', $row['replace_where']),
          );
        }
        return $replace;
      }
    //exportGetReplaceNameList - функция выборки списка замен

    //exportGetReplaceCategory - функция выборки соответствия категорий
      private function exportGetReplaceCategory($feed){
        $categories = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_category_match WHERE feed = '" . $this->db->escape($feed) . "' ORDER BY item_id ASC");
        foreach($query->rows as $row){

          $sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order, c1.status,(select count(product_id) as product_count from " . DB_PREFIX . "product_to_category pc where pc.category_id = c1.category_id) as product_count FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cp.category_id LIKE '" . $this->db->escape($row['category_id']) . "' GROUP BY cp.category_id ORDER BY name ASC LIMIT 0, 1";

          $query = $this->db->query($sql);
          if(isset($query->row['name']) && $query->row['name']){
            $category_name = strip_tags(html_entity_decode($query->row['name'], ENT_QUOTES, 'UTF-8'));
          }else{
            $category_name = 'Категория не найдена!';
          }

          $categories[] = array(
            'category_id'   => $row['category_id'],
            'category_name' => $category_name,
            'xml_name'   => $row['xml_name'],
            'markup'   => $row['markup'],
            'custom'   => $row['custom']
          );
        }
        return $categories;
      }
    //exportGetReplaceCategory

    //exportGetMarkup - функция выборки списка наценок на группы товара
      private function exportGetMarkup($feed){
        $this->load->model('catalog/product');
        $markups = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_product_markup WHERE feed = '" . $this->db->escape($feed) . "' ORDER BY item_id ASC");
        foreach($query->rows as $row){

          $products = array();
          if($row['products']){
            foreach(explode(',', $row['products']) as $product_id){
              $product_info = $this->model_catalog_product->getProduct($product_id);
              if($product_info){
                $products[] = array(
                  'product_id' => $product_info['product_id'],
                  'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product_info['product_id'], true),
                  'view' => HTTPS_CATALOG . 'index.php?route=product/product&product_id=' . $product_info['product_id'],
                  'name' => 'id:' . $product_info['product_id'] . ', model:' . $product_info['model'] . ' - ' . $product_info['name'] . ' / ' . $this->currency->format($product_info['price'], $this->config->get('config_currency'))
                );
              }
            }
          }

          $markups[] = array(
            'name' => $row['name'],
            'markup'   => $row['markup'],
            'products'   => $products
          );
        }

        return $markups;
      }
    //exportGetMarkup

    //exportGetAdditionalParamList - функция выборки списка дополнительных статических параметров
      private function exportGetAdditionalParamList($feed){
        $attributes = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_additional_params WHERE feed = '" . $this->db->escape($feed) . "' ORDER BY item_id ASC");
        foreach($query->rows as $row){
          $attributes[] = array(
            'param_name' => $row['param_name'],
            'param_text'   => $row['param_text']
          );
        }
        return $attributes;
      }
    //exportGetAdditionalParamList

    //exportLoadControllerFile - функция загрузки php файла выгрузки (например catalog/controller/extension/feed/unixml/google.php)
      public function exportLoadControllerFile($feed = false, $flag = false) {
        $data = $this->unixml->text();
        $data['export_system'] = $feed?$feed:$this->request->get['feed'];
        $data['server_path'] = rtrim(str_replace('system/', '', DIR_SYSTEM), '/');
        $data['file_path'] = '/catalog/controller/' . $this->unixml->path . '/' . $data['export_system'] . '.php';
        $server_file = $data['server_path'] . $data['file_path'];

        $data['file_data_error'] = false;
        if(is_file($server_file)){
          $file_data = file_get_contents($server_file);
          $file_data_array = explode("//XML_body", $file_data);
          if(isset($file_data_array[1])){
            $data['file_data'] = $file_data_array[1];
          }else{
            $data['file_data'] = "Что-то пошло не так :(\r\nФайл " . $server_file . " есть на сервере!\r\nФайл неправильной структуры. Не удалось прочитать его XML часть\r\nСкопируйте содержимое выгрузки по ссылке https://unixml.pro/feeds/" . $data['export_system'] . " и вставьте в файл catalog/controller/" . $this->unixml->path . "/" . $data['export_system'] . ".php через ftp подключение.\r\nЕсли не разберетесь обращайтесь в поддержку info@unixml.pro";
          }

        }else{
          $data['file_data'] = "Что-то пошло не так :(\r\nФайл " . $server_file . " отсутствует на сервере!\r\nПопробуйте вручную создать этот файл и повторите попытку.\r\nЕсли не разберетесь обращайтесь в поддержку info@unixml.pro";
          $data['file_data_error'] = true;
        }

        $data['action'] = $this->url->link($this->unixml->path . '/exportSaveControllerFile' . '&feed=' . $data['export_system'], $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()], true);

        if($feed && isset($file_data_array) && isset($file_data_array[$flag])){
          return $file_data_array[$flag];
        }
        if($feed && $flag == 'path'){
          return $server_file;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($this->loadView($this->unixml->path . '_export', $data));
      }
    //exportLoadControllerFile

    //exportSaveControllerFile - функция сохранения php файла выгрузки
      public function exportSaveControllerFile() {
        $feed = $this->request->get['feed'];

        $file_data = $this->exportLoadControllerFile($feed, '0');
        $file_data .= '//XML_body' . PHP_EOL;
        $file_data .= html_entity_decode($this->request->post['filedata'], ENT_QUOTES, 'UTF-8');
        $file_data .= '//XML_body';
        $file_data .= $this->exportLoadControllerFile($feed, '2');

        $server_file = $this->exportLoadControllerFile($feed, 'path');

        $fp = fopen($server_file, "w");
        fwrite($fp, $file_data);
        fclose($fp);

        $this->response->addHeader('Content-Type: application/json');
    		$this->response->setOutput(true);
      }
    //exportSaveControllerFile

  //Функции экспорта

  //Функции вспомогательные

    //getDirContents - функция сканирования папки на фото
      private function getDirContents($dir, &$results = array()){
        if(is_dir($dir)){
          $files = scandir($dir);

          foreach($files as $key => $value){
              $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
              if(!is_dir($path)) {
                  $results[] = $path;
              } else if($value != "." && $value != "..") {
                  $this->getDirContents($path, $results);
                  $results[] = $path;
              }
          }
        }else{
          $results = $this->unixml->text('text_error_directory');
        }

        return $results;
      }
    //getDirContents

    //getProductInfo - функция полечения информации о товаре
      private function getProductInfo($product_id){
        $sql = "SELECT pd.name, p.price, p.model, p.sku, p.image FROM " . DB_PREFIX . "product_description pd
                LEFT JOIN " . DB_PREFIX . "product p ON (pd.product_id = p.product_id)
                WHERE pd.product_id = '" . (int)$product_id . "'
                AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $product_info_query = $this->db->query($sql);

        return $product_info_query->row;
      }
    //getProductInfo

    //serviceDeleteImage - функция удаления лишних фото
      public function serviceDeleteImage(){

        $in_db = array();
        $multi_query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product");
        foreach($multi_query->rows as $row){
          $in_db[$row['image']] = 1;
        }
        $multi_query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product_image");
        foreach($multi_query->rows as $row){
          $in_db[$row['image']] = 1;
        }

        $data_db = '';

        $tables_row = explode(PHP_EOL, $this->request->post['unixml_delete_table']);

        $rows = array();
        foreach($tables_row as $row){
          $rows[$row] = $row;
        }
        $rows['product=image'] = 'product=image';
        $rows['product_image=image'] = 'product_image=image';

        foreach($rows as $row){
          $table_data = explode("=", $row);
          if($table_data[0] && $table_data[1]){
            $table = $this->db->escape(trim($table_data[0]));
            $field = $this->db->escape(trim($table_data[1]));
            $query = $this->db->query("SELECT " . $field . " FROM " . DB_PREFIX . $table . " WHERE " . $field . " LIKE '%catalog/%'"); //берем все данные в которых есть путь к фото
            if($query->num_rows){
              foreach($query->rows as $row){
                $data_db .= $row[$field];
              }
            }
          }
        }

        $count_delete = 0;
        $dir = $this->request->post['unixml_delete_direct'];
        $files = $this->getDirContents(DIR_IMAGE . 'catalog/' . $dir);

        foreach($files as $file){ //перебор файлов
          $delete = true;

          $image = str_replace(DIR_IMAGE, '', $file);
          $image = str_replace("'", "\\'", $image);

          if(isset($in_db[$image])){ //проверка есть ли фото в товаре - не удаляем
            $delete = false;
          }
          if(strpos($data_db, $image) !== false){ //проверка есть ли фото в таблицах базы - не удаляем
            $delete = false;
          }

          if($delete){ //удаляем фото
            if (file_exists($file)){
              if(!is_dir($file)){
                unlink($file);
                $count_delete++;
              }
            }
          }

        }

        if(!$count_delete){
          $count_delete = $this->unixml->text('text_not_file_in_dir');
        }else{
          $count_delete = str_replace('{{COUNT}}', $count_delete, $this->unixml->text('text_deleted_images'));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($count_delete));
      }
    //serviceDeleteImage

    //serviceSearchDouble - функция поиска дублей товаров
      public function serviceSearchDouble(){
        $data['products'] = array();
        $data['delete'] = array();
        $limit = 1000;
        $counter = 0;

        $this->load->model('tool/image');
        if(!isset($this->request->post['unixml_double_field'])){exit();}

        $unixml_double_set_data = explode(".", $this->request->post['unixml_double_field']);
        $table = $unixml_double_set_data[0];
        $field = $unixml_double_set_data[1];
        $prefix = ($table == 'product')?'p.':'pd.';

        $product_query = $this->db->query("SELECT min(product_id) as product_id FROM " . DB_PREFIX . $table . " GROUP BY " . $field);

        foreach($product_query->rows as $row){
          $product_info = $this->getProductInfo($row['product_id']);

          $sql = "SELECT p.product_id
                  FROM " . DB_PREFIX . "product_description pd
                  LEFT JOIN " . DB_PREFIX . "product p ON (pd.product_id = p.product_id)
                  WHERE " . $prefix . $field . " = '" . $this->db->escape($product_info[$field]) . "'
                  AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                  AND p.product_id > " . (int)$row['product_id'] . " ORDER BY p.product_id ASC";
          $double_query = $this->db->query($sql); //запрос дублей

          if($double_query->num_rows && $counter < $limit){
            $double = array();

            foreach($double_query->rows as $double_row){
              $data['delete'][] = $double_row['product_id'];

              $double_info = $this->getProductInfo($double_row['product_id']);

              $double[$double_row['product_id']] = array(
               'name'   => $double_info['name'],
               'image'  => $this->model_tool_image->resize($double_info['image']?$double_info['image']:'placeholder.png', 50, 50),
               'model'  => $double_info['model'],
               'sku'    => $double_info['sku'],
               'price'  => $this->currency->format($double_info['price'], $this->config->get('config_currency')),
               'view'   => '/index.php?route=product/product&product_id=' . $double_row['product_id'],
               'edit'   => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $double_row['product_id'], true)
              );
            }

            $data['products'][$row['product_id']] = array(
             'name'   => $product_info['name'],
             'image'  => $this->model_tool_image->resize($product_info['image']?$product_info['image']:'placeholder.png', 50, 50),
             'model'  => $product_info['model'],
             'sku'    => $product_info['sku'],
             'double' => $double,
             'price'  => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
             'view'   => '/index.php?route=product/product&product_id=' . $row['product_id'],
             'edit'   => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $row['product_id'], true)
            );
            $counter++;
          }
        }

        $data['delete'] = implode(',', $data['delete']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
      }
    //serviceSearchDouble

    //serviceDeleteDouble - функция удаления дублей товаров
      public function serviceDeleteDouble(){
        if(!isset($this->request->post['delete'])){exit();}

        $this->load->model('catalog/product');

        $products = explode(',', $this->request->post['delete']);
        $count_delete = count($products);

        foreach($products as $product_id){ //delete url
          $this->model_catalog_product->deleteProduct($product_id);
        }

        if(!$count_delete){
          $count_delete_text = '<div class="alert alert-success">' . $this->unixml->text('text_service_not_double') . '</div>';
        }else{
          $count_delete_text = str_replace('{{COUNT}}', $count_delete, $this->unixml->text('text_deleted_doubles'));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($count_delete_text));
      }
    //serviceDeleteDouble

    //serviceUrlDouble - функция поиска дублей ЧПУ
      public function serviceUrlDouble(){
        $urls = array();

        $data = array();
        $this->unixml->importGetAliasTable($data);

        $url_query = $this->db->query("SELECT * FROM " . DB_PREFIX . $data['table'] . " WHERE `keyword` != '' AND `keyword` IN (SELECT `keyword` FROM " . DB_PREFIX . $data['table'] . " GROUP BY `keyword` HAVING COUNT(*) > 1) ORDER BY `keyword`");

        foreach($url_query->rows as $row){
          if(substr($row['query'], 0, 11) == 'product_id='){ //товары
            $product_id = str_replace(substr($row['query'], 0, 11), '', $row['query']);
            $product_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

            if($product_query->num_rows){
              $keywords = array();
              $keywords_query = $this->db->query("SELECT seo_url_id, language_id, keyword FROM " . DB_PREFIX . $data['table'] . " WHERE query = 'product_id=" . $product_id . "'");
              foreach($keywords_query->rows as $krow){
                if(!isset($lang_codes)){
                  $lang_codes_query = $this->db->query("SELECT code, language_id FROM " . DB_PREFIX . "language");
                  foreach($lang_codes_query->rows as $lang_row){
                    $lang_codes[$lang_row['language_id']] = $lang_row['code'];
                  }
                }
                $keywords[$krow['language_id'] . $krow['seo_url_id']] = array(
                  'seo_url_id' => $krow['seo_url_id'],
                  'language_id' => $krow['language_id'],
                  'language_code' => $lang_codes[$krow['language_id']],
                  'keyword' => $krow['keyword']
                );
              }
              ksort($keywords);

              $urls[$row['query']] = array(
                'type' => $this->unixml->text('text_product'),
                'name' => $product_query->row['name'],
                'keywords' => $keywords //one in ver 2.x
              );
            }
          }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($urls));
      }
    //serviceUrlDouble

    //serviceSearchNoImage - функция поиска товара без фото
      public function serviceSearchNoImage(){
        $products = array();

        //основные фото
        $all_product_query = $this->db->query("SELECT image, product_id FROM " . DB_PREFIX . "product");
        foreach($all_product_query->rows as $product){

          //поиск по базе
          if(in_array($product['image'], array(NULL, '', 'no_image.jpg','no_image.png','no-image.jpg','no-image.png','placeholder.jpg','placeholder.png'))){
            $product_info = $this->getProductInfo($product['product_id']);
            $products['db.' . $product['product_id']] = array(
              'images' => array(),
              'name' => html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'),
              'image' => $this->unixml->text('text_image_not_in_db'),
              'model' => $product_info['model'],
              'product_id' => $product['product_id'],
              'sku' => $product_info['sku'],
              'price' => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
              'view' => '/index.php?route=product/product&product_id=' . $product['product_id'],
              'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product['product_id'], true)
            );
            continue;
          }
          //поиск по базе

          //поиск по отсутствию файла
          if(!is_file(DIR_IMAGE . $product['image'])){
            $product_info = $this->getProductInfo($product['product_id']);
            $products['if.' . $product['product_id']] = array(
              'images' => array(),
              'name' => html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'),
              'image' => str_replace('{{IMAGE}}', $product['image'], $this->unixml->text('text_image_not_exists')),
              'model' => $product_info['model'],
              'product_id' => $product['product_id'],
              'sku' => $product_info['sku'],
              'price' => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
              'view' => '/index.php?route=product/product&product_id=' . $product['product_id'],
              'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product['product_id'], true)
            );
          }
          //поиск по отсутствию файла

          //поиск по битому файлу
          if(is_file(DIR_IMAGE . $product['image']) && !filesize(DIR_IMAGE . $product['image'])){
            $product_info = $this->getProductInfo($product['product_id']);
            $products['ef.' . $product['product_id']] = array(
              'images' => array(),
              'name' => html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'),
              'image' => str_replace('{{IMAGE}}', $product['image'], $this->unixml->text('text_image_corrupt')),
              'model' => $product_info['model'],
              'product_id' => $product['product_id'],
              'sku' => $product_info['sku'],
              'price' => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
              'view' => '/index.php?route=product/product&product_id=' . $product['product_id'],
              'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product['product_id'], true)
            );
          }
          //поиск по битому файлу

        }
        //основные фото

        //дополнительные фото
        $all_image_query = $this->db->query("SELECT image, product_id FROM " . DB_PREFIX . "product_image");
        foreach($all_image_query->rows as $image){

          //поиск по базе
          if(in_array($image['image'], array(NULL, '', 'no_image.jpg','no_image.png','no-image.jpg','no-image.png','placeholder.jpg','placeholder.png'))){

            if(isset($products['db.' . $image['product_id']])){
              $products['db.' . $image['product_id']]['images'][] = $image['image'] . $this->unixml->text('text_image_not_set');
            }else{
              $product_info = $this->getProductInfo($image['product_id']);
              $products['db.' . $image['product_id']] = array(
                'images' => array($image['image'] . $this->unixml->text('text_image_not_set')),
                'name' => html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'),
                'image' => '',
                'model' => $product_info['model'],
                'product_id' => $image['product_id'],
                'sku' => $product_info['sku'],
                'price' => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
                'view' => '/index.php?route=product/product&product_id=' . $image['product_id'],
                'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product['product_id'], true)
              );
            }
            continue;
          }
          //поиск по базе

          //поиск по отсутствию файла
          if(!is_file(DIR_IMAGE . $image['image'])){
            if(isset($products['db.' . $image['product_id']])){
              $products['if.' . $image['product_id']]['images'][] = $image['image'] . $this->unixml->text('text_image_not_exists_on_server');
            }else{
              $product_info = $this->getProductInfo($image['product_id']);
              $products['if.' . $image['product_id']] = array(
                'images' => array($image['image'] . $this->unixml->text('text_image_not_exists_on_server')),
                'name' => html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'),
                'image' => '',
                'model' => $product_info['model'],
                'product_id' => $image['product_id'],
                'sku' => $product_info['sku'],
                'price' => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
                'view' => '/index.php?route=product/product&product_id=' . $image['product_id'],
                'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product['product_id'], true)
              );
            }
          }
          //поиск по отсутствию файла

          //поиск по битому файлу
          if(is_file(DIR_IMAGE . $image['image']) && !filesize(DIR_IMAGE . $image['image'])){
            if(isset($products['db.' . $image['product_id']])){
              $products['ef.' . $image['product_id']]['images'][] = $image['image'] . $this->unixml->text('text_images_corrupt');
            }else{
              $product_info = $this->getProductInfo($image['product_id']);
              $products['ef.' . $image['product_id']] = array(
                'images' => array($image['image'] . $this->unixml->text('text_images_corrupt')),
                'name' => html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'),
                'image' => '',
                'model' => $product_info['model'],
                'product_id' => $image['product_id'],
                'sku' => $product_info['sku'],
                'price' => $this->currency->format($product_info['price'], $this->config->get('config_currency')),
                'view' => '/index.php?route=product/product&product_id=' . $image['product_id'],
                'edit' => $this->url->link('catalog/product/edit', $this->unixml->token() . '=' . $this->session->data[$this->unixml->token()] . '&product_id=' . $product['product_id'], true)
              );
            }
          }
          //поиск по битому файлу

        }
        //дополнительные фото

        ksort($products);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($products));
      }
    //serviceSearchNoImage

    //serviceDeleteNoImage - функция удаления дефектных данных
      public function serviceDeleteNoImage(){
        $image_counter = 0;

        //основные фото
        $all_product_query = $this->db->query("SELECT image, product_id FROM " . DB_PREFIX . "product");
        foreach($all_product_query->rows as $product){
          //поиск по базе
          if(in_array($product['image'], array(NULL, '', 'no_image.jpg','no_image.png','no-image.jpg','no-image.png','placeholder.jpg','placeholder.png'))){
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '' WHERE product_id = '" . (int)$product['product_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE product_id = '" . (int)$product['product_id'] . "'");
            $image_counter++;
            continue;
          }
          //поиск по базе
          //поиск по отсутствию файла
          if(!is_file(DIR_IMAGE . $product['image'])){
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '' WHERE product_id = '" . (int)$product['product_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE product_id = '" . (int)$product['product_id'] . "'");
            $image_counter++;
          }
          //поиск по отсутствию файла
          //поиск по битому файлу
          if(is_file(DIR_IMAGE . $product['image']) && !filesize(DIR_IMAGE . $product['image'])){
            unlink(DIR_IMAGE . $product['image']);
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '' WHERE product_id = '" . (int)$product['product_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE product_id = '" . (int)$product['product_id'] . "'");
            $image_counter++;
          }
          //поиск по битому файлу
        }
        //основные фото

        //дополнительные фото
        $all_image_query = $this->db->query("SELECT image, product_id FROM " . DB_PREFIX . "product_image");
        foreach($all_image_query->rows as $image){
          //поиск по базе
          if(in_array($image['image'], array(NULL, '', 'no_image.jpg','no_image.png','no-image.jpg','no-image.png','placeholder.jpg','placeholder.png'))){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE image = '" . $this->db->escape($image['image']) . "' AND product_id = '" . (int)$image['product_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE product_id = '" . (int)$image['product_id'] . "'");
            $image_counter++;
          }
          //поиск по базе
          //поиск по отсутствию файла
          if(!is_file(DIR_IMAGE . $image['image'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE image = '" . $this->db->escape($image['image']) . "' AND product_id = '" . (int)$image['product_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE product_id = '" . (int)$image['product_id'] . "'");
            $image_counter++;
          }
          //поиск по отсутствию файла
          //поиск по битому файлу
          if(is_file(DIR_IMAGE . $image['image']) && !filesize(DIR_IMAGE . $image['image'])){
            unlink(DIR_IMAGE . $image['image']);
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE image = '" . $this->db->escape($image['image']) . "' AND product_id = '" . (int)$image['product_id'] . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE product_id = '" . (int)$image['product_id'] . "'");
            $image_counter++;
          }
          //поиск по битому файлу
        }
        //дополнительные фото

        if(!$image_counter){
          $image_counter = $this->unixml->text('text_no_problem');
        }else{
          $image_counter = $this->unixml->text('text_photo_processed') . $image_counter;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($image_counter));
      }
    //serviceDeleteNoImage

    //serviceDeleteData - фугкция удаление всех данных
      public function serviceDeleteData(){
        $json = '';
        $data['stat']['sql'] = 0; //заглушка
        $this->unixml->importGetAliasTable($data);
        $this->unixml->importGetManufacturerDescription($data);

        if(isset($this->request->post['delete_data'])){
          foreach($this->request->post['delete_data'] as $delete_data){
            if($delete_data == '1'){ //Категории
              $this->db->query("TRUNCATE " . DB_PREFIX . "category");
              $this->db->query("TRUNCATE " . DB_PREFIX . "category_description");
              $this->db->query("TRUNCATE " . DB_PREFIX . "category_path");
              $this->db->query("TRUNCATE " . DB_PREFIX . "category_to_layout");
              $this->db->query("TRUNCATE " . DB_PREFIX . "category_to_store");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_to_category");
              $this->db->query("DELETE FROM " . DB_PREFIX . $data['table'] . " WHERE query LIKE 'category_id=%'");
              $json .= $this->unixml->text('text_delete_cats');
            }
            if($delete_data == '2'){ //Товары
              $this->db->query("TRUNCATE " . DB_PREFIX . "product");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_attribute");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_description");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_image");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_special");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_to_category");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_to_store");
              $this->db->query("TRUNCATE " . DB_PREFIX . "unixml_import_image");
              $this->db->query("DELETE FROM " . DB_PREFIX . $data['table'] . " WHERE query LIKE 'product_id=%'");
              $json .= $this->unixml->text('text_delete_pros');
            }
            if($delete_data == '3'){ //Производители
              $this->db->query("TRUNCATE " . DB_PREFIX . "manufacturer");
              if($data['manufacturer_description']){
                $this->db->query("TRUNCATE " . DB_PREFIX . "manufacturer_description");
              }
              $this->db->query("TRUNCATE " . DB_PREFIX . "manufacturer_to_store");
              $this->db->query("UPDATE " . DB_PREFIX . "product SET manufacturer_id = ''");
              $this->db->query("DELETE FROM " . DB_PREFIX . $data['table'] . " WHERE query LIKE 'manufacturer_id=%'");
              $json .= $this->unixml->text('text_delete_mans');
            }
            if($delete_data == '4'){ //Фото
              $this->db->query("UPDATE " . DB_PREFIX . "product SET image = ''");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_image");
              $this->db->query("TRUNCATE " . DB_PREFIX . "unixml_import_image");
              $json .= $this->unixml->text('text_delete_imgs');
            }
            if($delete_data == '5'){ //Атрибуты
              $this->db->query("TRUNCATE " . DB_PREFIX . "attribute");
              $this->db->query("TRUNCATE " . DB_PREFIX . "attribute_description");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_attribute");
              $json .= $this->unixml->text('text_delete_atts');
            }
            if($delete_data == '6'){ //Опции
              $this->db->query("TRUNCATE " . DB_PREFIX . "option");
              $this->db->query("TRUNCATE " . DB_PREFIX . "option_description");
              $this->db->query("TRUNCATE " . DB_PREFIX . "option_value");
              $this->db->query("TRUNCATE " . DB_PREFIX . "option_value_description");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_option");
              $this->db->query("TRUNCATE " . DB_PREFIX . "product_option_value");
              $json .= $this->unixml->text('text_delete_opts');
            }
            if($delete_data == '7'){ //ЧПУ
              $this->db->query("DELETE FROM " . DB_PREFIX . $data['table'] . " WHERE query LIKE 'product_id=%' OR query LIKE 'category_id=%' OR query LIKE 'manufacturer_id=%'");
              $json .= $this->unixml->text('text_delete_urls');
            }
          }
          $json = rtrim($json, ', ') . $this->unixml->text('text_delete_success');
        }else{
          $json = $this->unixml->text('text_select_to_delete');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //serviceDeleteData

    //deleteProducts - функция удаления товаров поставщика
      public function deleteProducts(){
        $json = 0;

        $unixml_feed = $this->request->get['id'];
        $delete_image = $this->request->get['di'];

        if($unixml_feed){
          $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE unixml_feed = '" . (int)$unixml_feed . "'");
          if($query->num_rows){
            $json = $query->num_rows;
            foreach($query->rows as $row){
              $this->unixml->deleteProduct($row['product_id']);
              $this->unixml->deleteProductImages($delete_image, $row['product_id']);
            }
          }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
      }
    //deleteProducts

  //Функции вспомогательные

}
