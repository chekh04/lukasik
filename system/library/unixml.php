<?php

/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://unixml.pro                                 */
/*                                                          */
/************************copyright***************************/

#[AllowDynamicProperties]
class UniXML {

  public $ver = '7.5';
  public $path = 'extension/feed/unixml'; //2.3
  public $pricedir = 'price';
  public $style = "font:15px/22px Arial;border:2px dashed #39b3d7;padding:15px;margin:100px auto;max-width:500px;text-align:center;background:#F7FDFF;";

  public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->url = $registry->get('url');
		$this->db = $registry->get('db');
		$this->cache = $registry->get('cache');
    $this->response = $registry->get('response');
    $this->request = $registry->get('request');
    $this->session = $registry->get('session');
    $this->load = $registry->get('load');
	}

  public function token() {
    return isset($this->request->get['token'])?'token':'user_token';
  }

  //Функции импорта - админка

    //importGetPriceSettings - функция загрузки настроек импорта
      public function importGetPriceSettings(&$data) {

        $all_setting_key = array(
          'date_edit',
          'unixml_import_id',
          'unixml_import_product_link',
          'unixml_import_xml_root',
          'unixml_import_xml_categories',
          'unixml_import_xml_category',
          'unixml_import_xml_category_id',
          'unixml_import_xml_category_parent_id',
          'unixml_import_xml_category_name',
          'unixml_import_xml_products',
          'unixml_import_xml_product',
          'unixml_import_xml_product_id',
          'unixml_import_xml_product_name',
          'unixml_import_xml_product_model',
          'unixml_import_xml_product_sku',
          'unixml_import_xml_product_manufacturer',
          'unixml_import_xml_product_description',
          'unixml_import_xml_product_price',
          'unixml_import_xml_product_quantity',
          'unixml_import_xml_product_category_id',
          'unixml_import_xml_product_image',
          'unixml_import_xml_product_images',
          'unixml_import_xml_product_attributes',
          'unixml_import_xml_product_options',
          'unixml_import_url_without_language',
          'unixml_import_xml_product_additionals',
          'unixml_import_xml_product_custom',
          'unixml_import_xml_product_attribute_to_group',
          'unixml_import_xml_product_multilang',
        );

        $data = array_merge($data, array_fill_keys($all_setting_key, ''));

        $settings = $this->db->query("SELECT value, date_edit FROM " . DB_PREFIX . "unixml_setting WHERE code = 'import.price.setting' AND name = '" . (int)$data['import_id'] ."'");

        $fields_query = $this->db->query("SELECT name, value FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'import.price.setting.item' AND name LIKE '" . (int)$data['import_id'] . "%'");

        $data['fieldset'] = array();
        if($fields_query->num_rows){
          foreach($fields_query->rows as $row){
            $field_name = str_replace($data['import_id'] . '.', '', $row['name']);
            $field_data = unserialize($row['value']);
            foreach($field_data as $set_key => $set_value){
              if(!in_array($set_key, array('id','item')) && $set_value){
                $data['fieldset'][$field_name][$set_key] = $set_value;
              }
            }
          }
        }

        if($settings->num_rows){
          $data_data = unserialize($settings->row['value']);

          foreach($data_data as $data_data_key => $data_data_value){
            if(is_array($data_data_value)){
              $data[$data_data_key] = $data_data_value;
            }else{
              $data[$data_data_key] = trim($data_data_value);
            }
          }
          $data['date_edit'] = date('d.m.Y [H:i]', strtotime($settings->row['date_edit']));
        }

        $price_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'import.prices' AND setting_id = '" . (int)$data['import_id'] ."'");
        if($price_query->num_rows){
          $data = array_merge($data, unserialize($price_query->row['value']));
        }

        $data['path'] = $this->path;
        $data['token'] = isset($this->session->data[$this->token()])?$this->session->data[$this->token()]:false;
        if(!isset($data['from_import'])){
          $data['xmlex'] = $this->importXmlExample();
        }
        if(!isset($data['from_catalog']) || !$data['from_catalog']){
          $data['cron_link'] = HTTPS_CATALOG . 'index.php?route=' . $this->path . '/import&import_id=' . $data['import_id'] . '&key=' . (base64_encode(($data['import_id']*2) . 'key'));
        }
        if(!isset($data['login'])){ $data['login'] = '';}
        if(!isset($data['pass'])){ $data['pass'] = '';}

        $this->exportGetProductFields($data); //забрали все поля базы для последующих проверок

      }
    //importGetPriceSettings

    //importGetItemSettings - функция выборки конкретной настройки
      public function importGetItemSettings($setting_id, $setting_item) {
        $setting = array();
        $setting_data = array();

        $setting_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'import.price.setting.item' AND name = '" . (int)$setting_id . "." . $this->db->escape($setting_item) ."'");
        if($setting_query->num_rows){
          $setting_data = unserialize($setting_query->row['value']);
          foreach($setting_data as $setting_data_key => $setting_data_value){
            $setting[$setting_data_key] = trim($setting_data_value);
          }
        }

        return $setting;
      }
    //importGetItemSettings

    //importXmlExample - пример XML файла для админки
      private function importXmlExample() {
        $data = $this->text();

        $data['xml_example'] = true;
        if(substr(VERSION, 0, 1) == 3){
          $this->config->set('template_engine', 'template');
        }

        $route = $this->path . '_import';
        if(substr(VERSION, 0, 1) == 2 && substr(VERSION, 2, 3) != 3){ //если версия меньше 2.3
          $route .= '.tpl';
        }
        return $this->load->view($route, $data);
      }
    //importXmlExample

    //importGetImportLink - функция получения полей линковки
      public function importGetImportLink(&$data) {
        $data['link']['xml'] = 'product_id'; //по умолчанию линкуем с product_id
        $data['link']['oc'] = 'p.unixml_link'; //по умолчанию линкуем с полем unixml_link

        if(!isset($data['unixml_import_xml_root'])){ //если в массиве нет настроек (при получении из админки ключа в окне)
          $this->importGetPriceSettings($data);
        }

        if($data['fieldset']){
          foreach($data['fieldset'] as $key => $setting_data){
            if(substr($key, 0, 1) == 'p'){ //если товар
              if(isset($setting_data['link_value']) && $setting_data['link_value']){
                $data['link']['xml'] = $key;
                if(substr($setting_data['link_value'], 0, 1) == 'p'){ //если товар

                  //проверка на доп фид
                  $from_field_data = explode(',', $setting_data['link_value']);
                  if(isset($from_field_data[1]) && trim($from_field_data[1])){ //если прописано примерно так p.sku,feed
                    $data['link_feed'] = true;
                  }
                  $setting_data['link_value'] = str_replace(array(',feed', ', feed'), '', $setting_data['link_value']);
                  //проверка на доп фид

                  if(in_array($setting_data['link_value'], $data['all_access_column'])){ //проверка есть ли поле в базе
                    $data['link']['oc'] = str_replace('product_id', 'unixml_link', $setting_data['link_value']); //защита от проставляния product_id как ключ
                  }
                }
              }
            }
          }
        }
      }
    //importGetImportLink

  //Функции импорта - админка

  //Функции импорта

    //importStart - функция запуска импорта
      public function importStart($catalog = false){
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '3600');

        if(!isset($this->request->get['import_id'])){exit();}

        $data = array('import_id' => (int)$this->request->get['import_id'], 'from_import' => true, 'from_catalog' => $catalog); //здесь параметры ид импорта и тп

        $this->importSetStat($data, 'start');
        $this->importGetSetting($data); //берем конфиг и нужные данные
        $this->importGetXml($data); //забрали данные с XML
        $this->importSetCategory($data); //работа с категориями
        $this->importSetProducts($data); //работа с товарами
        $this->importMultiLanguage($data); //работа с товарами
        $this->importSetStat($data, 'finish');

        if($catalog){ //если запуск с крона
          $html  = "<div style='" . $this->style . "'>";
            $html .= "<h2>UniXML v" . $this->ver . ' ' . $this->text('text_import_success') . "</h2>";
            $html .= '<div>' . $this->text('text_import_max_mem') . $data['stat']['max'] . ' Мб.</div>';
            $html .= '<div>' . $this->text('text_import_all_sql') . $data['stat']['sql'] . '</div>';
            $html .= '<div>' . $this->text('text_import_all_time') . $data['stat']['time']['finish'] . '</div>';
            $html .= '<div>' . $this->text('text_import_new_cat') . $data['stat']['category'] . '</div>';
            $html .= '<div>' . $this->text('text_import_new_attr') . $data['stat']['attributes'] . '</div>';
            $html .= '<div>' . $this->text('text_import_new_pro') . $data['stat']['product_add'] . '</div>';
            $html .= '<div>' . $this->text('text_import_upd_pro') . $data['stat']['product_update'] . '</div>';
            $html .= '<div>' . $this->text('text_import_new_img') . $data['stat']['image'] . '</div>';
            $html .= '<div>' . $this->text('text_import_new_imgs') . $data['stat']['images'] . '</div>';
          $html .= "</div>";

          header('Content-Type: text/html; charset=utf-8');
          echo $html;
          exit();
        }

        exit('final');
      }
    //importStart

    //importSetStat - функция фиксирования статистики
      private function importSetStat(&$data, $key){
        $this->checkDir('unixml/import');

        //проверка на паузу
        if(is_file(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.uxp')){
          exit('pause'); //прерываем процесс импорта
        }
        //проверка на паузу

        if($key == 'start'){
          session_write_close();
          $data['stat']['microtime'] = microtime(true);
          $data['stat']['max'] = 0;
        }else{
          $data['stat']['time'][$key] = round(microtime(true) - $data['stat']['microtime'], 3);
        }
        if(in_array($key, array('start', 'finish'))){
          $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_setting SET code = 'import.price.status', name = '" . (int)$data['import_id'] . "', value = '" . (($key == 'start')?1:2) . "', date_edit = NOW()");
        }

        $data['stat']['step'] = $key;

        $current_memory = round(((memory_get_usage(1))/1024/1024), 3);
        if(!isset($data['stat']['memory'][$key])){
          $data['stat']['memory'][$key] = $current_memory;
        }

        if($current_memory > $data['stat']['memory'][$key]){
          $data['stat']['memory'][$key] = $current_memory;
        }

        $data['stat']['max'] = round(((memory_get_peak_usage(1))/1024/1024), 3);

        if(!isset($data['stat']['step_sql'][$key])){
          $data['stat']['step_sql'][$key] = 0;
        }
        if(isset($data['stat']['sql'])){
          $data['stat']['step_sql'][$key] = $data['stat']['sql'];
        }

        ksort($data['stat']);

        //пишем статистику
        $stat_file = fopen(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.uxs', 'w');
        fwrite($stat_file, serialize($data['stat']));
        fclose($stat_file);

        if($key == 'finish'){ //финал
          if(is_file(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml')){ //удаляем локальный XML
            unlink(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml');
          }
        }

      }
    //importSetStat

    //importGetSetting - функция получения настроек импорта
      private function importGetSetting(&$data){
        $data['stat']['sql'] = 3; //добавляем 2 т.к. в первой функции есть 2 запроса. Их там считать не надо т.к. туда обращаемся с админки где не нужна статистика запросов
        $this->importGetPriceSettings($data); //забираем настройки импорта
        $this->importGetStores($data); //забираем все магазины
        $this->importGetLangs($data); //забираем все языки
        $this->importGetAliasTable($data); //забираем таблицу ЧПУ
        $this->importGetManufacturerDescription($data); //проверка, есть ли таблица описаний производителя
        $this->importGetCustomerGroups($data); //забираем все группы покупателей
        $this->importSetStat($data, 'config');
      }
    //importGetSetting

    //importGetPriceFilter - функция фильтрации по цене
      private function importGetPriceFilter(&$data, &$product){
        if(!isset($product['continue']) && isset($data['fieldset']['product_price']['price_filter_value']) && $data['fieldset']['product_price']['price_filter_value']){
          $price_filter_value = $this->clearData($data['fieldset']['product_price']['price_filter_value'], 1);
          $filter_price = (float)str_replace(array('=','<','>'), '', $price_filter_value);
          $operator = trim(substr(trim($price_filter_value), 0, 1));

          if($operator == '=' && $product['price'] == $filter_price){
            $product['continue'] = true;
          }
          if($operator == '>' && $product['price'] > $filter_price){
            $product['continue'] = true;
          }
          if($operator == '<' && $product['price'] < $filter_price){
            $product['continue'] = true;
          }
        }
      }
    //importGetPriceFilter

    //importReplaceManufacturer - функция замены производителей и фильтрация по ним
      private function importReplaceManufacturer(&$data, &$product){
        if(!isset($data['continue_manufacturer'])){ //забираем бренды товары которых пропускаем
          $data['continue_manufacturer'] = array_column(array_filter($data['replace_manufacturer'], function ($row) { return !$row['oc']; }), 'xml');
        }

        if(!isset($data['replace_manufacturer_list'])){ //формируем замены брендов
          $data['replace_manufacturer_list'] = array();
          $manufacturers = array_flip($data['manufacturers']);
          foreach($data['replace_manufacturer'] as $replace_manufacturer){
            if(isset($manufacturers[$replace_manufacturer['oc']])){
              $data['replace_manufacturer_list'][$replace_manufacturer['xml']] = $manufacturers[$replace_manufacturer['oc']];
            }
          }
        }

        if(in_array($product['manufacturer_id'], $data['continue_manufacturer'])){ //фильтруем товары по этим брендам
          $product['continue'] = true;
        }

        if(isset($data['replace_manufacturer_list'][$product['manufacturer_id']])){ // заменяем бренды
          $product['manufacturer_id'] = $data['replace_manufacturer_list'][$product['manufacturer_id']];
        }
      }
    //importReplaceManufacturer

    //importGetStopList - функция проверки товара на запрет импорта
      private function importGetStopList(&$data, &$product){
        if(!isset($data['stop_list'])){
          $stop_items = array('product_id', 'product_name', 'product_model');
          foreach($stop_items as $stop_item){
            $data['stop_list'][$stop_item] = array();
            if(isset($data['fieldset'][$stop_item]['stop_value'])){
              foreach(explode(PHP_EOL, $data['fieldset'][$stop_item]['stop_value']) as $item){
                if(trim($item)){
                  $data['stop_list'][$stop_item][] = $item;
                }
              }
            }
          }
        }

        if(in_array($product['product_xml_id'], $data['stop_list']['product_id'])){
          $product['continue'] = true;
        }
        if(in_array($product['name'], $data['stop_list']['product_name'])){
          $product['continue'] = true;
        }
        if(in_array($product['model'], $data['stop_list']['product_model'])){
          $product['continue'] = true;
        }
      }
    //importGetStopList

    //importReplaceAttribute - функция замены атрибутов и фильтрация по ним
      private function importReplaceAttribute(&$data, &$product){

        $product['attributes_name'] = array_column($product['attributes'], 'name'); //забираем названия атрибутов товара
        if(!isset($data['continue_attribute'])){ //забираем бренды товары которых пропускаем
          $data['continue_attribute'] = array_column(array_filter($data['replace_attribute'], function ($row) { return !$row['oc']; }), 'xml');
        }
        if(!isset($data['unset_attribute'])){
          $data['unset_attribute'] = array_column(array_filter($data['replace_attribute'], function ($row) { return $row['oc'] == -1; }), 'xml');
        }

        $attrs = array();
        foreach($product['attributes'] as $attribute_key => $attribute_item){
          if($attribute_item){
            if($attribute_item['value'] == ''){
              unset($product['attributes'][$attribute_key]);
            }
            if(in_array($attribute_item['name'], $data['unset_attribute']) || in_array($attribute_item['name'], $attrs)){ //если этот атрибут не загружать или уже есть в массиве атрибутов (уникализация)
              unset($product['attributes'][$attribute_key]);
            }
            $attrs[] = $attribute_item['name'];
          }
        }

        if(!isset($data['attribute_replace_from'])){ //замены атрибутов
          $data['attribute_replace_from'] = array();
          $data['attribute_replace_to'] = array();
          if($data['replace_attribute']){
            $replace_attribute = array_filter($data['replace_attribute'], function ($row) { return (int)$row['oc'] > 0; });
            $attributes = array_flip($data['attributes']); //атрибуты магазина ид - название
            foreach($replace_attribute as $attribute){
              if(isset($attributes[$attribute['oc']]) && $attributes[$attribute['oc']]){
                $data['attribute_replace_from'][] = $attribute['xml'];
                $data['attribute_replace_to'][] = $attributes[$attribute['oc']];
              }
            }
          }
        }

        if(array_uintersect($product['attributes_name'], $data['continue_attribute'], "strcasecmp")){ //фильтруем товары по этим атрибутам
          $product['continue'] = true;
        }
      }
    //importReplaceAttribute

    //importGetCategoryFilter - функция фильтрации по категориям
      private function importGetCategoryFilter(&$data, &$product){
        if(!isset($product['continue'])){
          foreach($product['category_id'] as $category_id){
            if(in_array($category_id, $data['continue_category_id'])){ //если есть ид категории в запрете то пропускаем
              $product['continue'] = true;
            }
          }
        }
      }
    //importGetCategoryFilter

    //importGetOption - группировка товара по опциям
      private function importGetOption(&$data, &$product, $row){
        $name_unic = '***###***';
        $group_id = $this->importGetXmlData($row, $data['unixml_import_xml_product_options']);

        $option_from = array();
        $option_to = array();
        $option_value_from = array();
        $option_value_to = array();

        if(isset($data['fieldset']['product_options'])){
          if(isset($data['fieldset']['product_options']['replace_option_value_from']) && $data['fieldset']['product_options']['replace_option_value_from'] && isset($data['fieldset']['product_options']['replace_option_value_to']) && $data['fieldset']['product_options']['replace_option_value_to']){
            $option_from = explode(PHP_EOL, $data['fieldset']['product_options']['replace_option_value_from']);
            $option_to = explode(PHP_EOL, $data['fieldset']['product_options']['replace_option_value_to']);
            //unic
            foreach($option_from as $option_from_key => $option_from_value){
              $option_from[$option_from_key] = $name_unic . $option_from_value . $name_unic;
            }
            //unic
          }
          if(isset($data['fieldset']['product_options']['replace_option_value_value_from']) && $data['fieldset']['product_options']['replace_option_value_value_from'] && isset($data['fieldset']['product_options']['replace_option_value_value_to']) && $data['fieldset']['product_options']['replace_option_value_value_to']){
            $option_value_from = explode(PHP_EOL, $data['fieldset']['product_options']['replace_option_value_value_from']);
            $option_value_to = explode(PHP_EOL, $data['fieldset']['product_options']['replace_option_value_value_to']);
            foreach($option_value_from as $option_value_from_key => $option_value_from_value){
              $option_value_from[$option_value_from_key] = html_entity_decode($option_value_from_value, ENT_QUOTES, 'UTF-8');
            }
          }
        }

        if($group_id && $option_from && $option_to){ //если есть настройки для группировки
          $data['link_option'][] = $product['link_xml'];

          if(!isset($data['group'][$group_id])){ //если нет группы
            $product['options'] = array();
            foreach($product['attributes'] as $attribute_key => $attribute){
              if($attribute && $attribute['value']){
                $attribute_name_unic = $name_unic . $attribute['name'] . $name_unic;
                if(in_array($attribute_name_unic, $option_from)){ //если есть атрибуты для группировки

                  $attribute['name'] = str_replace($option_from, $option_to, $attribute_name_unic);
                  $attribute['value'] = str_replace($option_value_from, $option_value_to, $attribute['value']);

                  //удаляем этот атрибут из атрибутов
                  unset($product['attributes'][$attribute_key]);

                  //удаляем значение если есть в названии
                    $product['name'] = str_replace($attribute['value'], '', $product['name']);
                    $product['name'] = str_replace('()', '', $product['name']);
                    $product['name'] = trim($product['name']);
                  //удаляем значение если есть в названии

                  $product['options'][$attribute['name']][$attribute['value']] = array(
                    'quantity'      => $product['quantity'],
                    'subtract'      => '1',
                    'price'         => '',
                    'price_prefix'  => '+',
                    'points'        => '',
                    'points_prefix' => '+',
                    'weight'        => '',
                    'weight_prefix' => '+'
                  );

                  //custom_option_data
                    if(isset($data['option_data']) && $data['option_data'] && is_array($data['option_data'])){
                      foreach($data['option_data'] as $option_value_field => $option_value_from_row){
                        $product['options'][$attribute['name']][$attribute['value']][$option_value_field] = $this->importGetXmlData($row, $option_value_from_row);
                      }
                    }
                  //custom_option_data

                }
              }
            }
            //добавляем в группы - group_id => product_xml_id
            $data['group'][$group_id] = array(
              'product_xml_id' => $product['product_xml_id'],
              'status' => $data['status'],
            );
          }else{ //если уже есть эта группа - товар не добавляем а добавляем опции к первому товару
            $xml_id = $data['group'][$group_id]['product_xml_id'];
            $data['status'] = $data['group'][$group_id]['status'];
            $product['continue'] = true;
            $option_iteration = 0;

            if(isset($data['product_' . $data['status']][$xml_id])){
              $price_prefix = '+';
              $price = '';
              $price = (float)$product['price'] - (float)$data['product_' . $data['status']][$xml_id]['price'];
              if($price < 0){
                $price_prefix = '-';
                $price = abs($price);
              }

              foreach($product['attributes'] as $attribute_key => $attribute){
                if($attribute && $attribute['value']){
                  $attribute_name_unic = $name_unic . $attribute['name'] . $name_unic;
                  if(in_array($attribute_name_unic, $option_from)){ //если есть атрибуты для группировки
                    $attribute['name'] = str_replace($option_from, $option_to, $attribute_name_unic);
                    $attribute['value'] = str_replace($option_value_from, $option_value_to, $attribute['value']);

                    if(!isset($data['quantity_original'])){
                      $data['product_' . $data['status']][$xml_id]['quantity'] += $product['quantity'];
                    }

                    $data['product_' . $data['status']][$xml_id]['options'][$attribute['name']][$attribute['value']] = array(
                      'quantity'      => $product['quantity'],
                      'subtract'      => '1',
                      'price'         => $option_iteration?'':$price,
                      'price_prefix'  => $price_prefix,
                      'points'        => '',
                      'points_prefix' => '+',
                      'weight'        => '',
                      'weight_prefix' => '+'
                    );

                    //custom_option_data
                      if(isset($data['option_data']) && $data['option_data'] && is_array($data['option_data'])){
                        foreach($data['option_data'] as $option_value_field => $option_value_from_row){
                          $data['product_' . $data['status']][$xml_id]['options'][$attribute['name']][$attribute['value']][$option_value_field] = $this->importGetXmlData($row, $option_value_from_row);
                        }
                      }
                    //custom_option_data

                    $option_iteration++;
                  }
                }
              } //foreach attributes
            }

          }

        } //если есть настройки для группировки

        //опции как отдельно вложенные элементы в одном offer (пример ниже)

          // <options name="Название опции">
          //   <option>
          //     <name>S</name>
          //     <quantity>555</quantity>
          //     <price>120</price>
          //   </option>
          //   <option>
          //     <name>L</name>
          //     <quantity>777</quantity>
          //     <price>150</price>
          //   </option>
          // </options>

          $tags = explode('&gt;', $data['unixml_import_xml_product_options']);
          $options_raw_name = $this->importGetXmlData($row, $tags[0], 'array');
          if(isset($options_raw_name[0]['name'])){
            $product['options'] = array();

            $option_name = str_replace($option_from, $option_to, $name_unic . $options_raw_name[0]['name'] . $name_unic);

            if(isset($tags[1]) && isset($tags[2])){
              $tag_raw_options = explode(' ', $tags[0]);
              $tag_options = $tag_raw_options[0];
              $tag_option = trim($tags[1]);

              foreach($row->$tag_options->$tag_option as $optin_value_row){ //обход опций
                $optin_value_row = (array)$optin_value_row;

                $option_value = '';
                $option_quantity = 0;
                $option_prefix = '+';
                $option_price = 0;

                foreach(explode(',', $tags[2]) as $option_data_raw_item){ //обход поэлементно массива
                  $option_raw_item = explode('-', $option_data_raw_item);

                  if(trim($option_raw_item[1]) == 'name'){ //если это настройка названия
                    $option_value_key = trim($option_raw_item[0]); //берем поле массива откуда это название берется
                    if(isset($optin_value_row[$option_value_key])){
                      $option_value = $optin_value_row[$option_value_key];
                    }
                  }

                  if(trim($option_raw_item[1]) == 'quantity'){
                    $option_quantity_key = trim($option_raw_item[0]);
                    if(isset($optin_value_row[$option_quantity_key])){
                      $option_quantity = $optin_value_row[$option_quantity_key];
                    }
                  }

                  if(trim($option_raw_item[1]) == 'price'){
                    $option_price_key = trim($option_raw_item[0]);
                    if(isset($optin_value_row[$option_price_key])){
                      $option_price = $optin_value_row[$option_price_key];
                    }
                  }
                }

                if($option_value){
                  $option_value = str_replace($option_value_from, $option_value_to, $option_value);

                  $option_price = $option_price - $product['price'];
                  if($option_price < 0){
                    $option_prefix = '-';
                    $option_price = abs($option_price);
                  }

                  $product['options'][$option_name][$option_value] = array(
                    'quantity'      => $option_quantity,
                    'subtract'      => '1',
                    'price'         => $option_price,
                    'price_prefix'  => $option_prefix,
                    'points'        => '',
                    'points_prefix' => '+',
                    'weight'        => '',
                    'weight_prefix' => '+'
                  );

                  //custom_option_data
                    if(isset($data['option_data']) && $data['option_data'] && is_array($data['option_data'])){
                      foreach($data['option_data'] as $option_value_field => $option_value_from_row){
                        $product['options'][$option_name][$option_value][$option_value_field] = $this->importGetXmlData($row, $option_value_from_row);
                      }
                    }
                  //custom_option_data

                }

              } //обход опций
            }

          }
        //опции как отдельно вложенные элементы в одном offer

      }
    //importGetOption

    //importGetAttributes - функция выборки атрибутов из XML
      private function importGetAttributes(&$data, &$product, $row){
        $nested = explode('&gt;', $data['unixml_import_xml_product_attributes']);
        if(isset($nested[1])){ //если это вложенные атрибуты
          $product['attributes'] = array();
          $properties = trim($nested[0]);
          $property = trim($nested[1]);

          foreach($row->$properties->$property as $attribute){
            $name = '';
            $value = '';
            $attribute = (array)$attribute;

            foreach(explode(',', $nested[2]) as $attribute_data_raw_item){
              $attribute_raw_item = explode('-', $attribute_data_raw_item);

              if(trim($attribute_raw_item[1]) == 'name'){ //если это настройка названия
                $attribute_name_key = trim($attribute_raw_item[0]);
                if(isset($attribute[$attribute_name_key])){
                  $name = trim((string)$attribute[$attribute_name_key]);
                }
              }

              if(trim($attribute_raw_item[1]) == 'value'){ //если это настройка названия
                $attribute_value_key = trim($attribute_raw_item[0]);
                if(isset($attribute[$attribute_value_key])){
                  $value = trim((string)$attribute[$attribute_value_key]);
                }
              }
            }

            if($name && $value){
              $product['attributes'][] = array(
                'name' => $name,
                'value' => $value
              );
            }
          }
        }else{ //если это обычные атрибуты
          $product['attributes'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_attributes'], 'array');
        }
      }
    //importGetAttributes

    //importSetProductId
      private function importSetProductId(&$data, &$product){
        $link_data = explode('.', $data['link']['oc']);
        if(isset($link_data[1])){
          $field_link = $link_data[1];
          if(isset($product[$field_link])){
            $product['link_xml'] = $product[$field_link];
            if(isset($data['products'][$product[$field_link]])){ //если товар с этим линком есть в магазине - c модифицированным шаблоном
              $data['status'] = 'update'; //обновляем
              $product['product_id'] = $data['products'][$product[$field_link]]; //обновляем ид товара
            }
          }
        }
      }
    //importSetProductId

    //importSetProducts - функция добавления товаров
      private function importSetProducts(&$data){
        if(!$data['unixml_import_xml_product'] && !isset($data['xls'])){exit('Не задан тег товара в настройках импорта');} //если нет тега и это не XLS
        $data['product_add'] = array(); //товары на добавление
        $data['product_update'] = array(); //товары на обновление

        if(!isset($data['stat']['product_add'])){ //если мультиязык
          $data['stat']['product_add'] = 0;
          $data['stat']['product_update'] = 0;
          $data['stat']['attributes'] = 0;
          $data['stat']['image'] = 0;
          $data['stat']['images'] = 0;
        }

        $this->importGetOcUrl($data, 'product'); //забираем все ЧПУ товаров (делать если стоит генерить ЧПУ)
        $this->importGetOcProducts($data); //забрали все товары по ключу
        $this->importGetOcManufacturers($data); //забрали всех производителей
        $this->importGetOcAttributes($data); //забрали все атрибуты магазина
        $this->importSetToValues($data); //определяем кастомные поля
        $this->importGetNoUpdate($data); //определяем что не обновляем
        $this->importGetTemplate($data); //забираем шаблоны значений полей
        $this->importGetReplace($data); //забираем замены
        $this->importGetCalc($data); //забираем калькуляцию
        $this->importGetProductFields($data); //забираем поля товара для базы
        $this->importGetReplaceManufacturer($data); //Выборка замен производителей
        $this->importGetReplaceAttribute($data); //Выборка замен атрибутов
        $this->importSetProductDisQuant($data); //проверка выключать/обнулять товары поставщика

        //обходим товары
        $data['group'] = array();
        $data['link_option'] = array();
        $data['row_key'] = 0;
        $links = array();

        if(isset($data['xls'])){ //если XLS
          if($data['unixml_import_xml_products'] && isset($data['xml'][$data['unixml_import_xml_products']-1])){
            unset($data['xml'][$data['unixml_import_xml_products']-1]);
          }
          $rows = $data['xml'];
        }else{ //если XML
          $rows = $data['unixml_import_xml_products']?$data['xml']->{$data['unixml_import_xml_products']}->{$data['unixml_import_xml_product']}:$data['xml']->{$data['unixml_import_xml_product']};
        }

        $this->importCustomBefore($data, $rows); //кастомный код

        if(isset($data['multilang_id'])){ //мультиязык - отдаем только список товаров
          return $rows;
        }

        if($rows){
          foreach($rows as $row){
            $product = array();
            $data['row_key']++;

            $product['link_xml'] = $this->importGetXmlData($row, $data['unixml_import_xml_' . $data['link']['xml']]); //забираем поле для линка

            $data['status'] = 'add'; //по умолчанию товар добавляем
            if(isset($data['products'][$product['link_xml']])){ //если товар с этим линком есть в магазине
              $data['status'] = 'update'; //обновляем
            }

            if(in_array($product['link_xml'], $links) && !in_array($product['link_xml'], $data['link_option'])){ //если есть уже такой линк - это дубль - пропускаем
              continue;
            }
            $links[] = $product['link_xml'];

            $product['product_id'] = ($data['status'] == 'add')?$this->importGetXmlData($row, $data['unixml_import_xml_product_id']):$data['products'][$product['link_xml']];
            $product['product_xml_id'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_id']);
            $product['name'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_name']);
            $product['model'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_model']);
            $product['sku'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_sku']);
            $product['description'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_description']);
            $product['manufacturer_id'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_manufacturer']);
            $product['quantity'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_quantity']);
            $product['price'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_price']);
            $product['special'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_special']);
            $product['category_id'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_category_id'], 'array');
            $product['image'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_image']);
            $product['images'] = $this->importGetXmlData($row, $data['unixml_import_xml_product_images'], 'array');

            //дополнительные теги в какие поля
              if($data['unixml_import_xml_product_additionals']){
                foreach($data['unixml_import_xml_product_additionals'] as $additional_item){
                  $product[$additional_item['tag']] = $this->importGetXmlData($row, $additional_item['tag']);
                }
              }
            //дополнительные теги в какие поля

            if(isset($data['lang_data'])){ //если задан второй язык для загрузки
              foreach($data['lang_data']['fields'] as $lang_field_to => $lang_field_key){
                $product[$lang_field_key] = $this->importGetXmlData($row, $lang_field_key); //забираем нужные данные
              }
            }

            $this->importFixSpecialCharacter($product); //фикс от данных при двойной конвертации спецсимволов
            $this->importGetAttributes($data, $product, $row); //забрали атрибуты
            $this->importCustomCode($data, $product, $row); //кастомный код
            $this->importGetStopList($data, $product); //проверка стоп листов
            $this->importReplaceManufacturer($data, $product); //замены производителя и фильтрация по бренду
            $this->importReplaceAttribute($data, $product); //замены атрибутов и фильтрация по ним
            $this->importGetPriceFilter($data, $product); //фильтрация по цене
            $this->importGetCategoryFilter($data, $product); //фильтрация по категории
            $this->importSetProductQuantity($data, $product); //получение количества
            $this->importGetOption($data, $product, $row); //опции - группировка
            $this->importSetTemplate($data, $product); //генерация по шаблону
            $this->importSetReplace($data, $product); //делаем замены
            $this->importSetProductId($data, $product); //еще раз берем ключ товара с уже модифицированными данными (например приставке к полю и линковки)

            if(isset($data['fieldset']['products']['nadd_value']) && $data['status'] == 'add'){ //если товар новый и его не добавлять
              continue; //пропускаем - товар никуда не добавляется
            }
            if(isset($product['continue']) && $product['continue']){ // если есть фильтрация
              continue;
            }

            $this->importFixProductImages($data, $product); //фикс первого фото в дополнительных + фикс детекта ссылки в фото
            $this->importFixProductPrice($data, $product); //фикс старая цена - цена
            $this->importCalcPriceCurrency($data, $product, $row); //калькуляция цены от валюты
            $this->importSetProductStatus($data, $product); //получение статуса
            $this->importSetProductCategory($data, $product); //получение категории
            $this->importSetAttribute($data, $product); //добавление атрибутов
            $this->importSetManufacturer($data, $product); //добавление производителя
            $this->importSetCalc($data, $product); //делаем калькуляцию
            $this->importGenerateUrl($data, $product, 'product'); //генерация ЧПУ

            $data['product_' . $data['status']][$product['product_xml_id']] = $product;
          }
        }
        //обходим товары

        if(!$data['unixml_import_xml_product_multilang']){
          $this->unsetData($data, 'xml,product_url,manufacturers,products,attributes,xml_oc');
        }

        $this->cache->delete('manufacturer');
        $this->cache->delete('category');
        $this->cache->delete('product');

        //update
          $count_product_update = count($data['product_update']);
          if($count_product_update){
            $product_update = $data['product_update'];
            $blocks = ceil($count_product_update/$data['import_limit']);

            for($i=0;$i<$blocks;$i++){
              $data['product_update'] = array_slice($product_update, 0, $data['import_limit'], true);
              array_splice($product_update, 0, $data['import_limit']);
              $data['stat']['product_update'] = (($i+1) * count($data['product_update'])) . $this->text('text_from') . $count_product_update;

              $this->importUpdateProduct($data);
            }
          }
        //update

        //add
          $count_product_add = count($data['product_add']);
          if($count_product_add){
            $product_add = $data['product_add'];
            $blocks = ceil($count_product_add/$data['import_limit']);

            for($i=0;$i<$blocks;$i++){
              $data['iter_block'] = $i+1;
              $data['product_add'] = array_slice($product_add, 0, $data['import_limit'], true);
              array_splice($product_add, 0, $data['import_limit']);
              $data['stat']['product_add'] = ($data['iter_block'] * count($data['product_add'])) . $this->text('text_from') . $count_product_add . ' в фиде';

              $this->importAddProduct($data);
            }
          }
        //add

        $this->importCustomAfter($data); //кастомный код

      }
    //importSetProducts

    //importFixSpecialCharacter - фикс от двойной конвертации
      private function importFixSpecialCharacter(&$product){
        foreach(array('name','model','sku','description','manufacturer_id') as $field){
          $product[$field] = str_replace(
            array('&amp;amp;','&amp;quot;','&amp;lt;','&amp;gt;','&amp;apos;'),
            array('&amp;','&quot;','&lt;','&gt;','&apos;'),
            $product[$field]
          );
          $product[$field] = trim($this->importRemoveEmoji($product[$field]));
        }
      }
    //importFixSpecialCharacter

    //importMultiLanguage - мультиязык
      private function importMultiLanguage(&$data){
        if($data['unixml_import_xml_product_multilang']){
          foreach($data['unixml_import_xml_product_multilang'] as $lang){
            if($lang['file']){
              $data['price_file'] = $lang['file'];
              $data['multilang_id'] = $lang['lang'];

              $data['multilang_attribute'] = array();
              if(isset($lang['attribute']) && $lang['attribute']){
                foreach(explode(PHP_EOL, $lang['attribute']) as $attr_row){
                  $attr_row_data = explode('==', $attr_row);
                  if(isset($attr_row_data[1]) && isset($data['attributes'][$attr_row_data[1]])){
                    $data['multilang_attribute'][$attr_row_data[0]] = $data['attributes'][$attr_row_data[1]];
                  }
                }
              }

              $this->importGetMultiData($data); //забираем данные с основного фида (для сопоставлений)
              $this->importGetXml($data); //забираем данные с языкового фида
              $this->importMultiCategory($data); //обновляем категори
              $this->importMultiProduct($data); //обновляем товары
              $this->importMultiAttribute($data); //обновляем атрибуты
            }
          }
        }
      }
    //importMultiLanguage

    //importMultiAttribute
      private function importMultiAttribute(&$data){
        $upd_attr = array();

        if($data['main_product'] && $data['multi_attributes']){
          if($data['multilang_attribute']){ //если заданы соответствия
            foreach($data['multi_attributes'] as $product_id => $attributes){
              if(isset($data['main_product'][$product_id])){ //если есть в соответствиях ид товара из главного фида
                foreach($attributes as $attribute_key => $attribute){
                  if($attribute && $attribute['value']){
                    $name = $attribute['name'];
                    $lang_value = $attribute['value'];

                    if(isset($data['multilang_attribute'][$name])){ //если есть в соответствиях атрибут языкового фида = атрибут основного
                      $attribute_id = $data['multilang_attribute'][$name];

                      if(!in_array($attribute_id, $upd_attr)){ //если не обновляли этот атрибут (чтобы каждый раз не обновлять один и тот же атрибут во всех товарах)
                        $upd_attr[] = $attribute_id;
                        $this->db->query("UPDATE " . DB_PREFIX . "attribute_description SET name = '" . $this->db->escape($name) . "' WHERE language_id = '" . (int)$data['multilang_id'] . "' AND attribute_id = '" . (int)$attribute_id . "'");
                        $data['stat']['sql']++;
                      }

                      $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET text = '" . $this->db->escape($lang_value) . "' WHERE language_id = '" . (int)$data['multilang_id'] . "' AND attribute_id = '" . (int)$attribute_id . "' AND product_id = '" . (int)$data['products'][$product_id] . "'");
                      $data['stat']['sql']++;
                    }
                  }
                }
              }
            }
          }else{ //если нет соответствий - загружаем по порядку
            foreach($data['multi_attributes'] as $product_id => $attributes){
              if(isset($data['main_product'][$product_id])){ //если есть в соответствиях ид товара из главного фида
                foreach($attributes as $attribute_key => $attribute){
                  if($attribute && $attribute['value']){
                    if(isset($data['main_product'][$product_id][$attribute_key])){ //если есть в соответствиях ключ атрибута с его ид
                      $attribute_id = $data['main_product'][$product_id][$attribute_key]['attribute_id'];
                      $name = $attribute['name'];
                      $lang_value = $attribute['value'];

                      if(!in_array($attribute_id, $upd_attr)){ //если не обновляли этот атрибут (чтобы каждый раз не обновлять один и тот же атрибут во всех товарах)
                        $upd_attr[] = $attribute_id;
                        $this->db->query("UPDATE " . DB_PREFIX . "attribute_description SET name = '" . $this->db->escape($name) . "' WHERE language_id = '" . (int)$data['multilang_id'] . "' AND attribute_id = '" . (int)$attribute_id . "'");
                        $data['stat']['sql']++;
                      }

                      $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET text = '" . $this->db->escape($lang_value) . "' WHERE language_id = '" . (int)$data['multilang_id'] . "' AND attribute_id = '" . (int)$attribute_id . "' AND product_id = '" . (int)$data['products'][$product_id] . "'");
                      $data['stat']['sql']++;
                    }
                  }
                }
              }
            }
          }
        }
      }
    //importMultiAttribute

    //importGetMultiData - забираем данные с основного фида (для сопоставлений атрибутов)
      private function importGetMultiData(&$data){
        if(!isset($data['main_product'])){
          $data['main_product'] = array();
        }
        if(!isset($data['product_add_lang'])){
          $data['product_add_lang'] = array();
        }

        foreach($data['product_add'] as $product_key => $product){
          $data['product_add_lang'][] = $product['link_xml'];
          $data['main_product'][$product['link_xml']] = $product['attributes'];
        }
        if(!isset($data['fieldset']['product_attributes']['nupd_value'])){
          foreach($data['product_update'] as $product_key => $product){
            $data['main_product'][$product['link_xml']] = $product['attributes'];
          }
        }
      }
    //importGetMultiData

    //importMultiProduct - обновляем данные в товаре
      private function importMultiProduct(&$data){
        $data['multi_attributes'] = array();

        $fields = array();

        //дополнительные теги в какие поля
        if($data['unixml_import_xml_product_additionals']){
          foreach($data['unixml_import_xml_product_additionals'] as $additional_item){
            $field = $additional_item['to'];
            if(substr($field, 0, 2) == 'pd'){ //product_description
              $fields[str_replace('pd.', '', $field)] = $additional_item['tag'];
            }
          }
        }
        //дополнительные теги в какие поля
        foreach($this->importSetProducts($data) as $row){

          $product_xml_id = $this->importGetXmlData($row, $data['unixml_import_xml_' . $data['link']['xml']]); //забираем поле для линка

          if(!in_array('name', $data['no_update']) || in_array($product_xml_id, $data['product_add_lang'])){ //если не стоит запрет на это поле или же это новый добавленный товар
            $fields['name'] = $data['unixml_import_xml_product_name'];
          }
          if(!in_array('description', $data['no_update']) || in_array($product_xml_id, $data['product_add_lang'])){ //если не стоит запрет на это поле или же это новый добавленный товар
            $fields['description'] = $data['unixml_import_xml_product_description'];
          }

          if(isset($data['products'][$product_xml_id])){
            $product_id = $data['products'][$product_xml_id];

            $sql = "UPDATE " . DB_PREFIX . "product_description SET ";

            $sql_fields = '';
            foreach($fields as $fkey => $ftag){
              $sql_fields .= " `" . $fkey . "` = '" . $this->db->escape($this->importGetXmlData($row, $ftag)) . "',";
            }

            $sql .= rtrim($sql_fields, ',') . " WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$data['multilang_id'] . "'";

            if($sql_fields){
              $this->db->query($sql);
              $data['stat']['sql']++;
            }

            if(isset($data['custom_url'])){
              $product[$data['custom_url']] = $this->importGetXmlData($row, $data['custom_url']);
              $this->importAddProductCustomAlias($data, $product, $product_id, (int)$data['multilang_id']);
            }

            $this->importGetAttributes($data, $product, $row); //забрали атрибуты
            $data['multi_attributes'][$product_xml_id] = $product['attributes'];

          }
        }
      }
    //importMultiProduct

    //importMultiCategory - обновляем название категорий
      private function importMultiCategory(&$data){
        if(!isset($data['fieldset']['categories']['nupd_value'])){ //если обновлять
          $this->importGetReplaceCategory($data);
          foreach($data['categories_xml'] as $category){
            if(isset($data['xml_oc'][$category['category_id']])){
              $category_id = $data['xml_oc'][$category['category_id']];
              $name = $category['category_name'];

              $this->db->query("UPDATE " . DB_PREFIX . "category_description SET name = '" . $this->db->escape($name) . "' WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$data['multilang_id'] . "'");
              $data['stat']['sql']++;
            }
          }
        }
      }
    //importMultiCategory

    //importCustomBefore - функция кастомный код до импорта
      private function importCustomBefore(&$data, &$rows){
        if(!isset($data['multilang_id'])){ //если это первый проход основного фида
          $file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '/importCustomBefore';
          if(is_file($file)){
            include($file);
          }
        }
      }
    //importCustomBefore

    //importCustomCode - функция кастомный код в итерации цикла товара - ИМПОРТ
      private function importCustomCode(&$data, &$product, $row){
        if(!isset($data['multilang_id'])){ //если это первый проход основного фида
          $file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '/importCustomCode';
          if(is_file($file)){
            include($file);
          }
        }
      }
    //importCustomCode

    //importCustomAfter - функция кастомный код после импорта
      private function importCustomAfter(&$data){
        if(!isset($data['multilang_id'])){ //если это первый проход основного фида
          $file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '/importCustomAfter';
          if(is_file($file)){
            include($file);
          }
        }
      }
    //importCustomAfter

    //importSetToValues - функция определения кастомный полей
      private function importSetToValues(&$data) {
        $data['to_values'] = array();

        if($data['fieldset']){
          foreach($data['fieldset'] as $field => $field_data){
            if(isset($field_data['to_value'])){
              $values = explode(',', $field_data['to_value']);
              foreach($values as $value){
                $field = trim($field);
                if($field != 'product_id'){
                  $field = str_replace('product_', '', $field);
                }
                if(substr($value, 0, 1) == 'p'){
                  $data['to_values'][$value] = trim($field);
                }
              }
            }
          }
        }
      }
    //importSetToValues

    //importGetNoUpdate - функция определения что не обновляем
      private function importGetNoUpdate(&$data) {
        $data['no_update'] = array();
        $data['no_update'][] = 'unixml_link'; //добавляем тк ключ мы не трогаем

        if($data['fieldset']){
          foreach($data['fieldset'] as $field => $field_data){
            if(isset($field_data['nupd_value'])){
              $data['no_update'][] = str_replace(array('product_','manufacturer'), array('','manufacturer_id'), $field);
            }
          }
        }
      }
    //importGetNoUpdate

    //importGetTemplate - функция определения шаблона полей
      private function importGetTemplate(&$data) {
        $data['template'] = array();

        if($data['fieldset']){
          foreach($data['fieldset'] as $field => $field_data){
            if(isset($field_data['tpl_value'])){
              $data['template'][str_replace('product_', '', $field)] = $field_data['tpl_value'];
            }
          }
        }
      }
    //importGetTemplate

    //importGetReplace - функция определения замен
      private function importGetReplace(&$data) {
        $data['replace'] = array();

        if($data['fieldset']){
          foreach($data['fieldset'] as $field => $field_data){
            if(isset($field_data['replace_value_from']) && isset($field_data['replace_value_to'])){
              $data_from = explode(',', $field_data['replace_value_from']);
              foreach ($data_from as $from) {
                $data['replace'][str_replace('product_', '', $field)]['from'][] = $from;
              }

              $data_to = explode(',', $field_data['replace_value_to']);
              foreach ($data_to as $to) {
                $data['replace'][str_replace('product_', '', $field)]['to'][] = $to;
              }
            }
          }
        }
      }
    //importGetReplace

    //importGetCalc - функция определения калькуляции данных
      private function importGetCalc(&$data) {
        $data['calc'] = array();

        if($data['fieldset']){
          foreach($data['fieldset'] as $field => $field_data){
            if(isset($field_data['calc_value'])){
              $data['calc'][str_replace('product_', '', $field)] = $field_data['calc_value'];
            }
          }
        }
      }
    //importGetCalc

    //importAddProduct - функция добавления товара
      private function importAddProduct(&$data) {
        $data['product_image'] = array();
        $data['product_images'] = array();
        $data['product_options'] = array();
        $data['product_option_values'] = array();
        $data['to_db'] = array();
        $data['to_db']['product_description'][] = array_merge(array_keys($data['fields_pd']), array('product_id' => 'product_id', 'language_id' => 'language_id'));
        $data['to_db']['product_attribute'][] = array('product_id','attribute_id','language_id','text');
        $data['to_db']['product_special'][] = array('product_id','customer_group_id','price');

        if($data['seopro']){
          $data['to_db']['product_to_category'][] = array('product_id','category_id','main_category');
        }else{
          $data['to_db']['product_to_category'][] = array('product_id','category_id');
        }

        $data['to_db']['product_to_store'][] = array('product_id','store_id');

        //обходим товары
          foreach($data['product_add'] as $product_key => $product){
            $sql = "INSERT INTO " . DB_PREFIX . "product SET";
            foreach($data['fields_p'] as $field_sql => $field_array){ //перебор всех полей товара
              if(isset($product[$field_array])){
                $sql .= " " . $field_sql . " = '" . $this->db->escape($product[$field_array]) . "',";
              }
            }
            $sql .= "shipping = 1, subtract = 1, minimum = 1, unixml_feed = '" . (int)$data['import_id'] . "', date_available = NOW(), date_added = NOW(), date_modified = NOW()";

            $this->db->query($sql);
            $data['stat']['sql']++;

            $product_id = $this->db->getLastId();
            $product['product_id'] = $product_id;

            if(!isset($data['products'])){
              $data['products'] = array();
            }
            $data['products'][$product['link_xml']] = $product['product_id'];

            foreach($data['languages'] as $lang){
              $array_data = array();
              foreach($data['fields_pd'] as $field_sql => $field_array){

                //MULTI LANG
                  if(isset($data['lang_data'])){ //второй язык
                    if((int)$lang['language_id'] == $data['lang_data']['lang_id']){ //если идет второй язык
                      if(isset($data['lang_data']['fields'][$field_array])){ //подменяем нужные поля
                        $field_array = $data['lang_data']['fields'][$field_array];
                      }
                    }
                  }
                //MULTI LANG

                $array_data[] = $this->db->escape(isset($product[$field_array])?$product[$field_array]:'');
              }

              $data['to_db']['product_description'][] = array_merge($array_data, array((int)$product_id, (int)$lang['language_id']));
            }

            if($product['attributes']){
              foreach($product['attributes'] as $attribute){
                $data['stat']['attributes']++;
                foreach($data['languages'] as $lang){
                  $data['to_db']['product_attribute'][] = array((int)$product_id, $attribute['attribute_id'], (int)$lang['language_id'], $attribute['value']);
                }
              }
            }

            if($product['special']){
              foreach($data['customer_groups'] as $customer){
                $data['to_db']['product_special'][] = array((int)$product_id, (int)$customer['customer_group_id'], (float)$product['special']);
              }
            }

            if(isset($data['template']['category_id']) && $data['template']['category_id']){
              $product['category_id'] = $data['template']['category_id'];
            }

            if(isset($product['custom_category_id']) && $product['custom_category_id'] != ''){
              $product['category_id'] = $product['custom_category_id'];
            }

            if(isset($product['empty_custom_category_id']) && $product['empty_custom_category_id'] != '' && !$product['category_id']){
              $product['category_id'] = $product['empty_custom_category_id'];
            }

            if(!is_array($product['category_id'])){
              $product['category_id'] = array($product['category_id']);
            }
            $count_category_id = count($product['category_id']);
            foreach($product['category_id'] as $product_category_key => $product_category_id){
              if($data['seopro']){
                $main_category = 1;
                if($count_category_id > 1 && ($product_category_key < $count_category_id - 1)){ //если это мультикатегории и не последняя
                  $main_category = 0;
                }
                $data['to_db']['product_to_category'][] = array((int)$product_id, $product_category_id, $main_category);
              }else{ //если без сеопро
                $data['to_db']['product_to_category'][] = array((int)$product_id, $product_category_id);
              }
            }

            foreach($data['stores'] as $store_id){
              $data['to_db']['product_to_store'][] = array((int)$product_id, (int)$store_id);
            }

            if(isset($product['options']) && $product['options']){
              $data['product_option_values'][$product_id] = $product['options'];
              foreach($product['options'] as $option_name => $option_data){
                foreach($option_data as $option_value_name => $option_value_data){
                  $data['product_options'][$option_name][$option_value_name] = $option_value_name;
                }
              }
            }

            $this->importGetImageName($data, $product); //форматируем фото
            $this->importGetImagesName($data, $product); //форматируем дополнительные фото

            $this->importAddAlias($data, $product['keyword'], $product_id, 'product'); //прописываем ЧПУ

            if(isset($data['custom_url'])){
              $this->importAddProductCustomAlias($data, $product, $product_id);
            }

          }
        //обходим товары

        if(!$data['unixml_import_xml_product_multilang']){
          $this->unsetData($data, 'product_add');
        }
        $this->importInsertToDb($data); //пишем в базу данные товара

        $this->importSetOptions($data); //добавление опций в базу и создания соответствий
        $this->importInsertOptions($data); //добавление опций к товару

        $this->importDownloadProductImage($data); //физически загружаем фото на сервер + здесь идет проверка на расширение файла + сразу пишем в базу по каждому фото
        $this->importDownloadProductImages($data); //физически загружаем дополнительные фото на сервер + здесь идет проверка на расширение файла + запись в базу по каждому фото
        $this->unsetData($data, 'product_image,product_images,product_option_values');
      }
    //importAddProduct

    //importSetOptions - добавление опций в базу - выборка опций
      private function importSetOptions(&$data){
        if($data['unixml_import_xml_product_options']){ //если есть умножение на опции

          //берем то что есть в базе
            if(!isset($data['option_oc'])){
              $data['option_oc'] = array();

              $option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id)");
              $data['stat']['sql']++;

              foreach($option_query->rows as $option_row){
                $option_value_data = array();

            		$option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . (int)$option_row['option_id'] . "' ORDER BY ov.sort_order, ovd.name");
                $data['stat']['sql']++;

            		foreach ($option_value_query->rows as $option_value) {
            			$option_value_data[$option_value['name']] = $option_value['option_value_id'];
            		}

                $data['option_oc'][$option_row['name']] = array(
                  'option_id' => $option_row['option_id'],
                  'values' => $option_value_data,
                );
              }
            }
          //берем то что есть в базе

          //добавляем в базу опции и значения которых нет
            if($data['product_options']){ //если есть опции в товары
              foreach($data['product_options'] as $option_name => $option_values){
                if(!isset($data['option_oc'][$option_name])){ //если нет в базе - добавляем опцию и значения
                  $option_id = $this->importSetOption($data, $option_name);
                  $data['option_oc'][$option_name]['option_id'] = $option_id;

                  $sort_order = 1;
                  foreach($option_values as $value){
                    $option_value_id = $this->importSetOptionValue($data, $option_id, $value, $sort_order);
                    $data['option_oc'][$option_name]['values'][$value] = $option_value_id;
                    $sort_order++;
                  }
                }else{ //если есть опция - смотрим на значения
                  $sort_order = 1;
                  foreach($option_values as $value){
                    if(!isset($data['option_oc'][$option_name]['values'][$value])){ //если нет значения - добавляем
                      $option_id = $data['option_oc'][$option_name]['option_id'];
                      $option_value_id = $this->importSetOptionValue($data, $option_id, $value, $sort_order);
                      $data['option_oc'][$option_name]['values'][$value] = $option_value_id;
                    }
                    $sort_order++;
                  }
                }
              } //foreach product option
            }
          //добавляем в базу опции и значения которых нет

        }
      }
    //importSetOptions

    //importInsertOptions - добавление опций к товару
      private function importInsertOptions(&$data, $update = false){
        foreach($data['product_option_values'] as $product_id => $options){

          if(!isset($data['option_required'])){
            $data['option_required'] = 0;
          }

          if($update){ //обновление опций в товаре
            //забием опции товара магазина - на выходе id опции и id значений опций
              $product_oc_options = array();
              $product_option_query = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
              $data['stat']['sql']++;

              foreach($product_option_query->rows as $product_option){
                $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'");
                $data['stat']['sql']++;

                foreach($product_option_value_query->rows as $product_option_value){
                  $product_oc_options[$product_option['option_id']][$product_option_value['option_value_id']] = $product_option_value;

                }

                ksort($product_oc_options[$product_option['option_id']]);
              }
              ksort($product_oc_options);
            //забием опции товара магазина

            //забираем опции товара XML - на выходе id опции и id значений опций
              $product_xml_options = array();
              foreach($options as $option_name => $option_data) {
                foreach($option_data as $option_data_item_name => $option_data_item_value){ //перебираем значения опций
                  $product_xml_options[$data['option_oc'][$option_name]['option_id']][$data['option_oc'][$option_name]['values'][$option_data_item_name]] = $option_data_item_value;
                }
                ksort($product_xml_options[$data['option_oc'][$option_name]['option_id']]);
              }
              ksort($product_xml_options);
            //забираем опции товара XML

            //удаляем опции и/или значения опций которых нет в XML а есть в магазине (ПРИРАВНИВАЕМ ОПЦИИ МАГАЗИНА К ТОМУ ЧТО ЕСТЬ В XML - на выходе у нас опции xml = опции в магазине)
              foreach($product_oc_options as $oc_option_id => $oc_option_values){

                if(!isset($product_xml_options[$oc_option_id])){ //если опции товара из магазина нет в товаре из xml - удаляем из магазина (соответственно и значения опций в товаре)
                  $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$oc_option_id . "'");
                  $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$oc_option_id . "'");
                  $data['stat']['sql']++;
                  $data['stat']['sql']++;

                  unset($product_oc_options[$oc_option_id]); //удаляем эти опции из массива опций магазина
                  continue; //пропускаем удаление значений опций ниже
                }

                foreach($oc_option_values as $oc_option_value_id => $oc_option_value_data){ //удаляем значения опций которых нет в товаре из xml а есть в товаре магазине
                  if(!isset($product_xml_options[$oc_option_id][$oc_option_value_id])){ //если значение опции товара из магазина нет в товаре из xml - удаляем из магазина значение опции товара
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$oc_option_id . "' AND option_value_id = '" . (int)$oc_option_value_id . "'");
                    $data['stat']['sql']++;

                    unset($product_oc_options[$oc_option_id][$oc_option_value_id]); //удаляем эти опции из массива опций магазина
                  }
                }

              }
            //удаляем опции и/или значения опций которых нет в XML а есть в магазине

            //смотрим на опции из XML и если чего-то нет - добавляем опции и/или значения + обновляем данные
              foreach($product_xml_options as $xml_option_id => $xml_option_values){

                //если опции товара из XML нет в товаре из магазина
                  if(!isset($product_oc_options[$xml_option_id])){
                    //прикрепляем опцию к товару
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$xml_option_id . "', required = '" . (int)$data['option_required'] . "'");
                    $data['stat']['sql']++;

        						$product_option_add_id = $this->db->getLastId();

                    //добавляем все значения этой опции
                    foreach($xml_option_values as $option_value_id => $option_value_data){
                      $this->importAddProductOptionValue($data, $product_id, $xml_option_id, $option_value_id, $product_option_add_id, $option_value_data);
                    }
                    continue; //пропускаем добавление значений опций тк опции не было в товаре из магазина - прикрепили и все значения к товару
                  }
                //если опции товара из XML нет в товаре из магазина

                //прикрепляем отсутствующие значения опций к товару, если опция УЖЕ ЕСТЬ в товаре
                  foreach($xml_option_values as $option_value_id => $option_value_data){

                    //если значения опции нет
                      if(!isset($product_oc_options[$xml_option_id][$option_value_id])){

                        if(!isset($product_option_id)){ //если нет этого значения (например добавляем первое значение опции)
                          $product_option_id_query = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$xml_option_id . "'");
                          $data['stat']['sql']++;

                          $product_option_id = $product_option_id_query->row['product_option_id'];
                        }

                        $this->importAddProductOptionValue($data, $product_id, $xml_option_id, $option_value_id, $product_option_id, $option_value_data); //добавляем значение опции
                        continue; //пропускаем обновление данных в значении опции ниже из-за того что только что добавили это значение и нет смысла его обновлять
                      }
                    //если значения опции нет

                    //обновление данных в значениях опций
                      $this->importUpdateProductOptionValue($data, $product_id, $xml_option_id, $option_value_id, $option_value_data); //обновляем значение опции
                    //обновление данных в значениях опций

                  }
                //прикрепляем отсутствующие значения опций к товару, если опция УЖЕ ЕСТЬ в товаре

              }
            //смотрим на опции из XML и если чего-то нет - добавляем опции и/или значения + обновляем данные

          }else{ //добавление опций к товару

            foreach($options as $option_name => $option_data) {
              $option_id = $data['option_oc'][$option_name]['option_id'];

  						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$option_id . "', required = '" . (int)$data['option_required'] . "'");
              $data['stat']['sql']++;

  						$product_option_id = $this->db->getLastId();

  						foreach($option_data as $option_value_name => $option_value_data){
                $option_value_id = $data['option_oc'][$option_name]['values'][$option_value_name];

                $this->importAddProductOptionValue($data, $product_id, $option_id, $option_value_id, $product_option_id, $option_value_data);

  						}
      			}
          } //добавление опций к товару

        } //foreach product options
      }
    //importInsertOptions

    //importUpdateProductOptionValue - функция обновления значения опции товара
      private function importUpdateProductOptionValue(&$data, $product_id, $option_id, $option_value_id, $option_value_data){
        $sql_option_value_query_plus = array();

        $sql_option_value_query = "UPDATE " . DB_PREFIX . "product_option_value SET ";

        //доп поля из массива опций (стандартные + возможно кастомные)
          foreach($option_value_data as $option_value_field => $option_value_field_data){
            $sql_option_value_query_plus[$option_value_field] = $option_value_field . " = '" . $this->db->escape($option_value_field_data) . "'";
          }

          foreach($data['no_update'] as $noupd_item){
            if(isset($sql_option_value_query_plus[$noupd_item])){
              unset($sql_option_value_query_plus[$noupd_item]);
            }
            if($noupd_item == 'price' && isset($sql_option_value_query_plus['price_prefix'])){
              unset($sql_option_value_query_plus['price_prefix']);
            }
          }

          if(isset($option_value_data['option_image']) && $option_value_data['option_image'] && isset($option_value_data['image']) && $option_value_data['image']){
            $option_value_image_data = explode('/', $option_value_data['image']);
            $option_value_image_name = end($option_value_image_data);
            $image_dir = 'catalog/unixml/' . $data['import_id'] . '/' . $product_id;
            $image_name = $image_dir . '/' . $option_value_image_name;
            $ext = $this->getImageExtension($option_value_image_name);

            $image_result = glob(DIR_IMAGE . str_replace('.' . $ext, '', $image_name) . '.*');

            if(!$image_result){ //если нет фото опции
              $this->importCheckImageDir($image_dir);

              $image_data = array(
                'name' => $image_name,
                'image' => $option_value_data['image'],
              );

              $this->importDownloadImage($data, $image_data, $product_id);
            }else{
              $image_data['name'] = str_replace(DIR_IMAGE, '', $image_result[0]);
              $sql_option_value_query_plus['image'] = $data['option_image'] . " = '" . $this->db->escape($image_data['name']) . "'";
            }
          }
        //доп поля из массива опций (стандартные + возможно кастомные)

        if($sql_option_value_query_plus){
          $sql_option_value_query .= implode(', ', $sql_option_value_query_plus);
          $sql_option_value_query .= " WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$option_id . "' AND option_value_id = '" . (int)$option_value_id . "'";

          $this->db->query($sql_option_value_query);
          $data['stat']['sql']++;

        }
      }
    //importUpdateProductOptionValue

    //importAddProductOptionValue - функция добавления значения опции к товару
      private function importAddProductOptionValue(&$data, $product_id, $option_id, $option_value_id, $product_option_id, $option_value_data){
        $sql_option_value_query_plus = array();

        $sql_option_value_query = "INSERT INTO " . DB_PREFIX . "product_option_value SET
          product_option_id = '" . (int)$product_option_id . "',
          product_id = '" . (int)$product_id . "',
          option_id = '" . (int)$option_id . "',
          option_value_id = '" . (int)$option_value_id . "'";

          //доп поля из массива опций (стандартные + возможно кастомные)
            foreach($option_value_data as $option_value_field => $option_value_field_data){
              $sql_option_value_query_plus[$option_value_field] = $option_value_field . " = '" . $this->db->escape($option_value_field_data) . "'";
            }

            if(isset($option_value_data['option_image']) && $option_value_data['option_image'] && isset($option_value_data['image']) && $option_value_data['image']){
              $option_value_image_data = explode('/', $option_value_data['image']);
              $option_value_image_name = end($option_value_image_data);
              $image_dir = 'catalog/unixml/' . $data['import_id'] . '/' . $product_id;
              $image_name = $image_dir . '/' . $option_value_image_name;
              $ext = $this->getImageExtension($option_value_image_name);

              $image_result = glob(DIR_IMAGE . str_replace('.' . $ext, '', $image_name) . '.*');

              if(!$image_result){ //если нет фото опции
                $this->importCheckImageDir($image_dir);

                $image_data = array(
                  'name' => $image_name,
                  'image' => $option_value_data['image'],
                );

                $this->importDownloadImage($data, $image_data, $product_id);
              }else{
                $image_data['name'] = str_replace(DIR_IMAGE, '', $image_result[0]);
                $sql_option_value_query_plus['image'] = $data['option_image'] . " = '" . $this->db->escape($image_data['name']) . "'";
              }
            }

            if($sql_option_value_query_plus){
              $sql_option_value_query .= ', ' . implode(', ', $sql_option_value_query_plus);
            }
          //доп поля из массива опций (стандартные + возможно кастомные)

				$this->db->query($sql_option_value_query);
        $data['stat']['sql']++;
      }
    //importAddProductOptionValue

    //importSetOption - функция добавление новой опции
      private function importSetOption($data, $name){
        if(!isset($data['option_type'])){
          $data['option_type'] = 'radio';
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($data['option_type']) . "', sort_order = '0'");

        $option_id = $this->db->getLastId();

        foreach($data['languages'] as $lang){
          $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$lang['language_id'] . "', name = '" . $this->db->escape($name) . "'");
        }

        return $option_id;
      }
    //importSetOption

    //importSetOptionValue - функция добавления нового значения опции
      private function importSetOptionValue($data, $option_id, $value, $sort_order){
				$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "', sort_order = '" . (int)$sort_order . "'");

				$option_value_id = $this->db->getLastId();

				foreach($data['languages'] as $lang){
					$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$lang['language_id'] . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($value) . "'");
				}

        return $option_value_id;
      }
    //importSetOptionValue

    //importGetImageName - функция получения названия файла для загрузки главного фото
      private function importGetImageName(&$data, &$product, $update = false){
        $name = str_replace('-', '_', $product['keyword']?$product['keyword']:$this->transform($product['name']));

        $ext = $this->getImageExtension($product['image']);

        if(!$name){
          $name_data = explode('/', str_replace('.' . $ext, '', $product['image']));
          $name = $this->transform(end($name_data));
        }

        $image_dir = 'catalog/unixml/' . $data['import_id'] . '/' . $product['product_id'];
        $image_name = $image_dir . '/' . $name . '.' . $ext;

        $this->importCheckImageDir($image_dir);

        if(!$update){ //если добавление - кидаем фото в массив фото
          if(trim($image_name) && trim($product['image'])){
            $data['product_image'][$product['product_id']] = array(
              'name' => $image_name,
              'image' => $product['image'],
            );
          }
        }else{ //если это из обновления - отдаем в массив уже отформатированные фото
          if(trim($image_name) && trim($product['image'])){
            $product['image'] = array(
              'name' => $image_name,
              'image' => $product['image'],
            );
          }
        }

      }
    //importGetImageName

    //importGetImagesName - функция получения названий файлов для загрузки фото (здесь расширение идет как в файле импорта)
      private function importGetImagesName(&$data, &$product){
        if($product['images']){
          $name = str_replace('-', '_', $product['keyword']?$product['keyword']:$this->transform($product['name']));
          foreach($product['images'] as $image_key => $image){
            if($image){
              $ext = $this->getImageExtension($image);

              if(!$name){
                $name_data = explode('/', str_replace('.' . $ext, '', $image));
                $name = $this->transform(end($name_data));
              }

              $image_dir = 'catalog/unixml/' . $data['import_id'] . '/' . $product['product_id'];
              $image_name = $image_dir . '/' .$name . '_' . ($image_key+1) . '.' . $ext;

              $this->importCheckImageDir($image_dir);

              $data['product_images'][$product['product_id']][$image_key] = array(
                'name' => $image_name,
                'image' => $image
              );
            }
          }
        }
      }
    //importGetImagesName

    //importInsertImageToDB - функция обновления фото в базе
      private function importInsertImageToDB(&$data, $product_id){
        if($data['product_image'] && isset($data['product_image'][$product_id])){
          $image_data = $data['product_image'][$product_id];

          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE main_image = 1 AND product_id = '" . (int)$product_id . "'"); //удаляем старые связки
          $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($image_data['name']) . "' WHERE product_id = '" . (int)$product_id . "'");
          $this->db->query("INSERT INTO " . DB_PREFIX . "unixml_import_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($image_data['image']) . "', oc = '" . $this->db->escape($image_data['name']) . "', main_image = '1'");
          $data['stat']['sql'] += 3;

          $this->importSetStat($data, 'image');
        }
      }
    //importInsertImageToDB

    //importInsertImagesToDB - функция добавления дополнительных фото в базе
      private function importInsertImagesToDB(&$data, $product_id){
        $images = false;
        if($data['product_images'] && isset($data['product_images'][$product_id])){
          $images = $data['product_images'][$product_id];
        }

        if($images){
          $data['to_db']['product_image'][] = array('product_id','image','sort_order');
          $data['to_db']['unixml_import_image'][] = array('product_id','image','oc','main_image');

          foreach($images as $sort_order => $image){
            $data['to_db']['product_image'][] = array((int)$product_id, $image['name'], $sort_order);
            $data['to_db']['unixml_import_image'][] = array((int)$product_id, $this->db->escape($image['image']), $this->db->escape($image['name']), 0);
          }
        }

        $this->importInsertToDb($data);
        $this->importSetStat($data, 'images');
      }
    //importInsertImagesToDB

    //importDownloadProductImage - функция загрузки фото товара
      public function importDownloadProductImage(&$data){
        $count_image = count($data['product_image']);
        if(!isset($data['stat']['image'])){
          $data['stat']['image'] = 0 . $this->text('text_from') . $count_image;
        }
        $image_counter = 0;

        foreach($data['product_image'] as $product_id => $image_data){
          $this->importDownloadImage($data, $image_data, $product_id);
          $this->importSetStat($data, 'image');
          $data['product_image'][$product_id] = $image_data;
          $image_counter++;
          $data['stat']['image'] = $image_counter . $this->text('text_from') . $count_image . ' (' . round(100 * $image_counter/$count_image) . '%)';

          $this->importInsertImageToDB($data, $product_id); //добавляем фото в базу + в таблицу unixml_import_image
        }
      }
    //importDownloadProductImage

    //importDownloadProductImages - функция загрузки дополнительных фото товара
      public function importDownloadProductImages(&$data){
        if($data['product_images']){
          $product_ids = implode(',', array_keys($data['product_images']));

          $this->db->query("DELETE FROM " . DB_PREFIX . "unixml_import_image WHERE main_image = 0 AND product_id IN(" . $product_ids . ")"); //удаляем старые фото
          $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id IN(" . $product_ids . ")"); //удаляем старые фото
          $data['stat']['sql'] += 2;

          $count_images = 0;
          foreach($data['product_images'] as $product_id => $images){
            $count_images += count($images);
          }
          $images_counter = 0;
          foreach($data['product_images'] as $product_id => $images){
            foreach($images as $images_key => $image_data){
              $this->importDownloadImage($data, $image_data, $product_id);
              $this->importSetStat($data, 'images');
              $data['product_images'][$product_id][$images_key] = $image_data;
              $images_counter++;
              $data['stat']['images'] = $images_counter . $this->text('text_from') . $count_images . ' (' . round(100 * $images_counter/$count_images) . '%)';
            }

            $this->importInsertImagesToDB($data, $product_id); //добавляем фото в базу + в таблицу unixml_import_image
          }
        }
      }
    //importDownloadProductImages

    //importDownloadImage - функция загрузки
      private function importDownloadImage(&$data, &$image_data, $product_id){
        if($image_data['name']){
          $ext = $this->getImageExtension($image_data['image']);

          $ch = curl_init(str_replace('&amp;', '&', $image_data['image']));
          curl_setopt($ch, CURLOPT_HEADER, 0);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
          curl_setopt($ch, CURLOPT_TIMEOUT, 30);
          curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
          $image_body = curl_exec($ch);

          $new_ext = '';
          $new_ext = $this->getImageMimeType($image_body, $image_data);

          if($new_ext){
            $image_data['name'] = str_replace('.' . $ext, '.' . $new_ext, $image_data['name']);
          }

          file_put_contents(DIR_IMAGE . $image_data['name'], $image_body);

          //webp convert
            if(!isset($data['convert_webp'])){
              $data['convert_webp'] = true;
            }
            if($new_ext == 'webp' && $data['convert_webp']){
              $im = imagecreatefromwebp(DIR_IMAGE . $image_data['name']);
              unlink(DIR_IMAGE . $image_data['name']);
              $image_data['name'] = str_replace('.webp', '.png', $image_data['name']);
              imagepalettetotruecolor($im);
              imagealphablending($im, true);
              imagesavealpha($im, true);
              imagepng($im, DIR_IMAGE . $image_data['name']);
              imagedestroy($im);
            }
          //webp convert

          $data['stat']['error'] = false;
          if(!is_file(DIR_IMAGE . $image_data['name'])){
            $data['stat']['error'] = $this->text('text_error_download_image_from_offer') . $product_id . ': ' . $image_data['image'];
          }
          if(is_file(DIR_IMAGE . $image_data['name']) && !getimagesize(DIR_IMAGE . $image_data['name'])){
            unlink(DIR_IMAGE . $image_data['name']);
            $data['stat']['error'] = $this->text('text_error_download_image_from_offer') . $product_id . ': ' . $image_data['image'];
          }

          curl_close($ch);
        }
      }
    //importDownloadImage

    //функции проверки типа фото и его расширения
      private function getBytesFromHexString($hexdata){
        for($count = 0; $count < strlen($hexdata); $count+=2){
          $bytes[] = chr(hexdec(substr($hexdata, $count, 2)));
        }

        return implode($bytes);
      }

      private function getImageMimeType($imagedata, $image_info){
        $return = NULL;

        $imagemimetypes = array(
          "jpg" => "FFD8",
          "png"  => "89504E470D0A1A0A",
          "gif"  => "474946",
          "bmp"  => "424D",
          "tiff" => "4949",
          "tiff" => "4D4D"
        );

        foreach($imagemimetypes as $mime => $hexbytes){
          $bytes = $this->getBytesFromHexString($hexbytes);
          if(substr($imagedata, 0, strlen($bytes)) == $bytes){
            $return = $mime;
          }
        }

        //webp
        if($return == NULL){
          $tmpfile = DIR_IMAGE . $image_info['name'] . '.tmp';
          file_put_contents($tmpfile, $imagedata);
          if(is_file($tmpfile)){
            if(exif_imagetype($tmpfile) === IMAGETYPE_WEBP){
              $return = 'webp';
            }
            unlink($tmpfile);
          }

          if(substr($imagedata, 0, 10) == 'RIFFP2WEBP'){
            $return = 'webp';
          }

          if(strpos($imagedata, '4  WEBPVP8') !== FALSE){ //4  WEBPVP8
            $return = 'webp';
          }
        }
        //webp

        return $return;
      }
    //функции проверки типа фото и его расширения

    //importCheckImageDir - функция проверки папки фото
      private function importCheckImageDir($image_dir){
        if (!is_dir(DIR_IMAGE . $image_dir)) {
          mkdir(DIR_IMAGE . $image_dir, 0777, true);
        }
      }
    //importCheckImageDir

    //importCheckImageFile - функция проверки на существование файла
      private function importCheckImageFile(&$image_name){
        if(is_file(DIR_IMAGE . $image_name)){ //если есть файл
          $ext = $this->getImageExtension($image_name);
          $image_name = str_replace('.' . $ext, '_' . mt_rand(100, 999) . '.' . $ext, $image_name);
          $this->importCheckImageFile($image_name);
        }
      }
    //importCheckImageFile

    //importGetProductFields - функция объявления полей товара
      private function importGetProductFields(&$data){
        $data['fields_p'] = array(
          'model'           => 'model',
          'sku'             => 'sku',
          'quantity'        => 'quantity',
          'price'           => 'price',
          'manufacturer_id' => 'manufacturer_id',
          'status'          => 'status'
        );

        $data['fields_pd'] = array(
          'name'        => 'name',
          'description' => 'description',
          'meta_title'  => 'meta_title'
        );

        //кастомные данные в поля
        if($data['to_values']){
          foreach($data['to_values'] as $field => $from_product){
            if(in_array($field, $data['all_access_column'])){
              if(substr($field, 0, 2) == 'p.'){ //product
                $data['fields_p'][str_replace('p.', '', $field)] = $from_product;
              }
              if(substr($field, 0, 2) == 'pd'){ //product_description
                $data['fields_pd'][str_replace('pd.', '', $field)] = $from_product;
              }
            }
          }
        }
        //кастомные данные в поля

        //дополнительные теги в какие поля
        if($data['unixml_import_xml_product_additionals']){
          foreach($data['unixml_import_xml_product_additionals'] as $additional_item){
            $field = $additional_item['to'];
            if(in_array($field, $data['all_access_column'])){
              if(substr($field, 0, 2) == 'p.'){ //product
                $data['fields_p'][str_replace('p.', '', $field)] = $additional_item['tag'];
              }
              if(substr($field, 0, 2) == 'pd'){ //product_description
                $data['fields_pd'][str_replace('pd.', '', $field)] = $additional_item['tag'];
              }
            }
          }
        }
        //дополнительные теги в какие поля

        //ключ для линковки
        if(substr($data['link']['oc'], 0, 2) == 'p.'){ //если линкуем из таблицы product
          $data['fields_p'][str_replace('p.', '', $data['link']['oc'])] = 'link_xml'; //unixml_link
        }
        if(substr($data['link']['oc'], 0, 2) == 'pd'){ //если линкуем из таблицы product
          $data['fields_pd'][str_replace('pd.', '', $data['link']['oc'])] = 'link_xml'; //unixml_link
        }
        //ключ для линковки
      }
    //importGetProductFields

    //importGetReplaceManufacturer - забираем замены производителей
      private function importGetReplaceManufacturer(&$data){
        $data['replace_manufacturer'] = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_import_manufacturer WHERE import_id = '" . $data['import_id'] . "'ORDER BY xml ASC");

        foreach($query->rows as $row){
          $data['replace_manufacturer'][] = array(
            'xml' => $row['xml'],
            'oc' => $row['oc']
          );
        }
        $data['stat']['sql']++;
      }
    //importGetReplaceManufacturer

    //importGetReplaceAttribute - забираем замены атрибутов
      private function importGetReplaceAttribute(&$data){
        $data['replace_attribute'] = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_import_attribute WHERE import_id = '" . $data['import_id'] . "'ORDER BY xml ASC");

        foreach($query->rows as $row){
          $data['replace_attribute'][] = array(
            'xml' => $row['xml'],
            'oc' => $row['oc']
          );
        }
        $data['stat']['sql']++;
      }
    //importGetReplaceAttribute

    //importGetReplaceCategory - забираем замены категорий
      public function importGetReplaceCategory(&$data){

        $data['replace_category'] = array(); //соответствия категории из xml - категории opencart
        $data['continue_category_id'] = array(); //категории товары которых пропускаем
        $data['stop_category_id'] = array(); //категории которые не загружаем из-за соответствий XML -> OC
        $data['category_children'] = array(); //все подкатегории категорий
        $data['categories_xml'] = array(); //xml категории
        $data['categories_xml_id'] = array(); //xml категория - id

        if(isset($data['xml']->{$data['unixml_import_xml_categories']})){
          foreach($data['xml']->{$data['unixml_import_xml_categories']}->{$data['unixml_import_xml_category']} as $category){
            $category_name = $this->importGetXmlData($category, $data['unixml_import_xml_category_name']);
            $category_name = trim($this->importRemoveEmoji($category_name));
            $category_id = $this->importGetXmlData($category, $data['unixml_import_xml_category_id']);
            $parent_id = $this->importGetXmlData($category, $data['unixml_import_xml_category_parent_id']);
            $url = $this->importGetXmlData($category, '@url');
            $data['categories_xml'][] = array(
              'category_name' => $category_name,
              'category_id'   => $category_id,
              'parent_id'     => $parent_id,
              'url'           => $url
            );
            $data['categories_xml_id_without_id'][$category_name] = $category_id;
            $data['categories_xml_id'][$category_id . '-' . $category_name] = $category_id;
            if($parent_id){
              $data['category_children'][$parent_id][] = $category_id;
            }
          }
        }

        //кастомные категории
        if(isset($data['custom_categories'])){
          $data['categories_xml'] = array_merge($data['categories_xml'], $data['custom_categories']);
        }
        //кастомные категории

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "unixml_import_category WHERE import_id = '" . $data['import_id'] . "'ORDER BY xml ASC");
        $data['stat']['sql']++;

        foreach($query->rows as $row){
          $xml_rows = explode('-', $row['xml']); //фикс когда вводят через пробелы
          if(isset($xml_rows[1]) && (int)$xml_rows[0] > 0){ //если есть соответствие с ид категории из XML
            $xml_rows_0 = array_shift($xml_rows);
            $row['xml'] = trim($xml_rows_0) . '-' . trim(implode('-', $xml_rows));
          }else{ //если прописали просто категорию - добавляем туда ид категории из XML
            if(isset($data['categories_xml_id_without_id'][trim($row['xml'])])){
              $row['xml'] = $data['categories_xml_id_without_id'][trim($row['xml'])] . '-' . $row['xml'];
            }
          }

          $data['replace_category'][$row['xml']][] = $row['oc'];

          //если стоит фильтрация по категории
            if(!(int)$row['oc'] && isset($data['categories_xml_id'][$row['xml']])){
              $data['continue_category_id'][] = $data['categories_xml_id'][$row['xml']];
              $this->importGetXmlSubCategory($data, $data['categories_xml_id'][$row['xml']]);
            }
          //если стоит фильтрация по категории

          //если стоят замены по категории (какую категорию из XML грузим в какую категорию в Opencart)
          if((int)$row['oc'] && isset($data['categories_xml_id'][$row['xml']])){
            $data['stop_category_id'][$data['categories_xml_id'][$row['xml']]][] = $row['oc'];
            $this->importGetXmlSubCategory($data, $data['categories_xml_id'][$row['xml']], $row['oc']);
          }
          //если стоят замены по категории
        } //foreach
        unset($data['categories_xml_id_without_id']); // удаляем соответствия, уже не нужны

        $data['stat']['sql']++;
      }
    //importGetReplaceCategory

    //importRemoveEmoji - удаление лишнего с категорий
      private function importRemoveEmoji($string) {
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);

        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
      }
    //importRemoveEmoji

    //importGetXmlSubCategory - функция выборки из XML категорий подкатегорий ($opencart_category_id - флаг того что это заменные категории)
      public function importGetXmlSubCategory(&$data, $category_id, $opencart_category_id = false){
        if(isset($data['category_children'][$category_id])){
          foreach($data['category_children'][$category_id] as $child_id){
            if(!$opencart_category_id){
              $data['continue_category_id'][] = $child_id;
            }else{
              $data['stop_category_id'][$child_id][] = $opencart_category_id;
            }
            $this->importGetXmlSubCategory($data, $child_id, $opencart_category_id);
          }
        }
      }
    //importGetXmlSubCategory

    //importSetProductDisQuant - функция проверки выключения или обнуления остатков товаров поставщика
      private function importSetProductDisQuant(&$data){
        if(!isset($data['multilang_id'])){ //если это первый проход основного фида
          if(isset($data['fieldset']['products']['prodis_value'])){ //если стоит выключать товары поставщика
            $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 WHERE unixml_feed = '" . (int)$data['import_id'] . "'");
            $data['stat']['sql']++;
          }
          if(isset($data['fieldset']['products']['proqua_value'])){ //если стоит обнулять товары поставщика
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = 0 WHERE unixml_feed = '" . (int)$data['import_id'] . "'");
            $data['stat']['sql']++;
          }
        }
      }
    //importSetProductDisQuant

    //importClearImage
      private function importClearImage($image){
        return str_replace(array('https://','http://','www.','.jpg','.jpeg','.png','.bmp','.gif','.tiff','.webp'), '', $image);
      }
    //importClearImage

    //importUpdateCheckImage - функция выборки фото прошлого импорта и текущего
      private function importUpdateCheckImage(&$data, &$product, $field_array){
        if($field_array == 'image'){ //всегда идет обращение в эту функцию и здесь идет проверка что это фото
          if(!isset($data['image_old'])){ //если нет выборки старых фото - забираем с базы
            $data['image_old'] = array();
            $image_old_query = $this->db->query("SELECT uii.product_id, uii.image as oldxml, uii.oc as oldoc, p.image FROM " . DB_PREFIX . "unixml_import_image uii LEFT JOIN " . DB_PREFIX . "product p ON(uii.product_id = p.product_id) WHERE uii.main_image = 1 AND uii.product_id IN(" . implode(',', $data['product_ids']) . ")");
            $data['stat']['sql']++;
            if($image_old_query->num_rows){
              foreach($image_old_query->rows as $row){
                $data['image_old'][$row['product_id']] = array(
                    'image'  => $row['image'], //текущее фото товара в базе
                    'oldxml' => $row['oldxml'], //ссылка с прошлого импорта из XML
                    'oldoc'  => $row['oldoc'] //фото товара с прошлого импорта - то которое записали в базу к товару
                );
              }
            }
          }

          $update_image = false; //изначально фото не обновляем

          if(isset($data['image_old'][$product['product_id']])){ //если есть старая запись с прошлого импорта
            $image_old = $data['image_old'][$product['product_id']]; //здесь в массиве фото текущее и с прошлого импорта

            if($this->importClearImage($image_old['oldxml']) != $this->importClearImage($product['image'])){ //если ссылка на фото с прошлого импорта отличается от фото текущего
              $update_image = true;
            }
            if($this->importClearImage($image_old['oldoc']) != $this->importClearImage($image_old['image'])){ //если текущее фото товара не такое как было при прошлом импорте (то есть фото изменили в самом магазине после импорта)
              $update_image = true;
            }
            if($image_old['image'] == '' || in_array($image_old['image'], array('no_image.jpg','no_image.png','placeholder.jpg','placeholder.png'))){ //если текущее фото товара отсутствует
              $update_image = true;
            }
            if(!$update_image && !is_file(DIR_IMAGE . $image_old['image'])){ //если физически нет фото
              $update_image = true;
            }
          }else{ //если старой записи нет - обновляем
            $update_image = true;
          }

          if($update_image){
            $this->importGetImageName($data, $product, true); //форматируем фото
            if(isset($product['image']['name']) && $product['image']['name']){
              $data['product_image'][$product['product_id']] = $product['image']; //для физ загрузки фото
              $product['image'] = $product['image']['name']; //для обновления в базе
            }else{
              $product['image'] = '';
            }
          }else{
            $product['image'] = $data['image_old'][$product['product_id']]['oldoc']; //cтавим то же фото что и было
          }
        }
      }
    //importUpdateCheckImage

    //importUpdateProduct - функция обновления товара
      private function importUpdateProduct(&$data) {
        $data['fields_p']['image'] = 'image';
        $data['product_image'] = array();
        $data['product_images'] = array();
        $data['product_options'] = array();
        $data['product_option_values'] = array();
        $data['product_ids'] = array_column($data['product_update'], 'product_id');
        //product
          $sql  = "UPDATE " . DB_PREFIX . "product SET";

          foreach($data['fields_p'] as $field_sql => $field_array){ //перебор всех полей товара
            if(!in_array($field_sql, $data['no_update'])){ //если не стоит запрет на это поле
              $sql .= " " . $field_sql . " = (CASE product_id";
              foreach($data['product_update'] as $product){ //обход товаров и кидаем данные в запрос
                $this->importUpdateCheckImage($data, $product, $field_array); //проверка главного фото, обновлять или нет
                $sql .= " WHEN " . $product['product_id'] . " THEN '" . $this->db->escape($product[$field_array]) . "'";
              }
              $sql .= " END), ";
            }
          }

          $sql = str_replace('image = (CASE product_id END)', '',   $sql); //фикс когда нет фото на обновление
          $sql = rtrim($sql, ', ');
          $sql .= " WHERE product_id IN(" . implode(',', $data['product_ids']) . ");";

          $this->db->query($sql);
          $data['stat']['sql']++;
        //product

        //product_description
          $pd_count = 0;
          $sql  = "UPDATE " . DB_PREFIX . "product_description SET";

          foreach($data['fields_pd'] as $field_sql => $field_array){ //перебор всех полей товара

            //фикс, когда нет данных в массиве товара
            foreach($data['product_update'] as $current_item){
              if(!isset($current_item[$field_array])){ //когда нет данного поля в массиве товара
                $data['no_update'][] = $field_sql; //ставим в запрет это поле для обновления
              }
              break;
            }

            if(!in_array($field_sql, $data['no_update'])){ //если не стоит запрет на это поле
              $sql .= " " . $field_sql . " = (CASE product_id";
              foreach($data['product_update'] as $product){ //обход товаров и в запрос данные кидаем
                if(isset($product[$field_array])){
                  $sql .= " WHEN " . $product['product_id'] . " THEN '" . $this->db->escape($product[$field_array]) . "'";
                }
              }
              $sql .= " END), ";
              $pd_count++;
            }
          }

          $sql = rtrim($sql, ', ');
          $sql .= " WHERE product_id IN(" . implode(',', $data['product_ids']) . ");";

          if($pd_count){
            $this->db->query($sql);
          }

          $this->importSetStat($data, 'product');
          $data['stat']['sql']++;

          //MULTI LANG
            if(isset($data['lang_data']) && isset($data['lang_data']['lang_id']) && isset($data['lang_data']['fields'])){ //если задан второй язык

              $pd_count = 0;
              $sql  = "UPDATE " . DB_PREFIX . "product_description SET";

              foreach($data['fields_pd'] as $field_sql => $field_array){ //перебор всех полей товара

                if(isset($data['lang_data']['fields'][$field_array])){ //подменяем нужные поля
                  $field_array = $data['lang_data']['fields'][$field_array];
                }

                //фикс, когда нет данных в массиве товара
                foreach($data['product_update'] as $current_item){
                  if(!isset($current_item[$field_array])){ //когда нет данного поля в массиве товара
                    $data['no_update'][] = $field_sql; //ставим в запрет это поле для обновления
                  }
                  break;
                }

                if(!in_array($field_sql, $data['no_update'])){ //если не стоит запрет на это поле
                  $sql .= " " . $field_sql . " = (CASE product_id";
                  foreach($data['product_update'] as $product){ //обход товаров и в запрос данные кидаем
                    if(isset($product[$field_array])){
                      $sql .= " WHEN " . $product['product_id'] . " THEN '" . $this->db->escape($product[$field_array]) . "'";
                    }
                  }
                  $sql .= " END), ";
                  $pd_count++;
                }
              }

              $sql = rtrim($sql, ', ');
              $sql .= " WHERE product_id IN(" . implode(',', $data['product_ids']) . ") AND language_id = '" . (int)$data['lang_data']['lang_id'] . "';";

              if($pd_count){
                $this->db->query($sql);
              }

              $this->importSetStat($data, 'product');
              $data['stat']['sql']++;

            } //isset lang_data
          //MULTI LANG
        //product_description

        //product_to_category
          if(!in_array('category_id', $data['no_update'])){

            if($data['seopro']){
              $data['to_db']['product_to_category'][] = array('product_id','category_id','main_category');
            }else{
              $data['to_db']['product_to_category'][] = array('product_id','category_id');
            }

            foreach($data['product_update'] as $product){ //обход товаров и в запрос данные кидаем

              if(isset($data['template']['category_id']) && $data['template']['category_id']){
                $product['category_id'] = $data['template']['category_id'];
              }

              if(isset($product['empty_custom_category_id']) && $product['empty_custom_category_id'] != '' && !$product['category_id']){
                $product['category_id'] = $product['empty_custom_category_id'];
              }

              if(!is_array($product['category_id'])){
                $product['category_id'] = array($product['category_id']);
              }

              $count_category_id = count($product['category_id']);

              if(isset($product['category_save']) && $product['category_save']){ //если кастомно не обновлять
                $count_category_id = 0;
              }

              if(!isset($product['category_id'][0]) || !$product['category_id'][0]){
                $count_category_id = 0;
              }

              if($count_category_id){
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product['product_id'] . "'"); //удаляем старые связки
                $data['stat']['sql']++;

                foreach($product['category_id'] as $product_category_key => $product_category_id){
                  if($data['seopro']){
                    $main_category = 1;
                    if($count_category_id > 1 && ($product_category_key < $count_category_id - 1)){ //если это мультикатегории и не последняя
                      $main_category = 0;
                    }
                    $data['to_db']['product_to_category'][$product['product_id'] . '.' . $product_category_id] = array((int)$product['product_id'], $product_category_id, $main_category);
                  }else{
                    $data['to_db']['product_to_category'][$product['product_id'] . '.' . $product_category_id] = array((int)$product['product_id'], $product_category_id);
                  }
                }
              }
            }
          }
        //product_to_category

        //product_special
          if(!in_array('price', $data['no_update'])){ //если обновляем цены - обновим и акции
            if(!isset($data['special_save'])){
              $data['special_save'] = false;
            }
            if(!$data['special_save']){ //если нет $data['special_save'] - то есть не трогать акции
              $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id IN(" . implode(',', $data['product_ids']) . ")"); //удаляем старые связки
              $data['stat']['sql']++;

              $data['to_db']['product_special'][] = array('product_id','customer_group_id','price');
              foreach($data['product_update'] as $product){ //обход товаров и в запрос данные кидаем
                if($product['special']){
                  foreach($data['customer_groups'] as $customer){
                    $data['to_db']['product_special'][] = array((int)$product['product_id'], (int)$customer['customer_group_id'], (float)$product['special']);
                  }
                }
              }
            }
          }
        //product_special

        $this->importInsertToDb($data); //пишем в базу

        //product_attributes
          if(!in_array('attributes', $data['no_update'])){ //если обновляем атрибуты
            $attributes = array();
            $attribute_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE language_id = '" . $this->config->get('config_language_id') . "' AND product_id IN(" . implode(',', $data['product_ids']) . ")");
            $data['stat']['sql']++;

            foreach($attribute_query->rows as $row){
              $attributes[$row['product_id']][$row['attribute_id']] = $row['text']; //атрибуты товаров
            }

            foreach($data['product_update'] as $product){
              if(isset($attributes[$product['product_id']])){ //если есть атрибуты в товаре
                $product_attributes = array();

                foreach($product['attributes'] as $attribute){ //перебираем атрибуты с XML
                  if(!isset($attributes[$product['product_id']][$attribute['attribute_id']])){ //если новый атрибут - добавим
                    foreach($data['languages'] as $lang){
                      $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product['product_id'] . "', attribute_id = '" . (int)$attribute['attribute_id'] . "', language_id = '" . (int)$lang['language_id'] . "', `text` = '" . $this->db->escape($attribute['value']) . "'");
                      $data['stat']['sql']++;
                    }
                    $data['stat']['attributes']++;
                  }
                  $product_attributes[$attribute['attribute_id']] = $attribute['value']; //атрибуты товара id = значение
                }

                $diff = array_diff_assoc($attributes[$product['product_id']], $product_attributes); //если есть разница в атрибутах товара из XML и в базе
                if($diff){
                  foreach($diff as $ak => $av){
                    if(isset($product_attributes[$ak])){
                      $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET `text` = '" . $this->db->escape($product_attributes[$ak]) . "' WHERE product_id = '" . (int)$product['product_id'] . "' AND attribute_id = '" . (int)$ak . "'");
                      $data['stat']['sql']++;
                    }
                  }
                }
              }else{ //если атрибутов нет - добавляем
                if($product['attributes']){
                  foreach($product['attributes'] as $attribute){
                    foreach($data['languages'] as $lang){
                      $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product['product_id'] . "', attribute_id = '" . (int)$attribute['attribute_id'] . "', language_id = '" . (int)$lang['language_id'] . "', `text` = '" . $this->db->escape($attribute['value']) . "'");
                      $data['stat']['sql']++;
                    }
                    $data['stat']['attributes']++;
                  }
                }
              }
            }
          }
        //product_attributes

        //product_options
          if(!in_array('options', $data['no_update'])){ //если обновляем опции

            foreach($data['product_update'] as $product){
              if(isset($product['options']) && $product['options']){
                $data['product_option_values'][$product['product_id']] = $product['options'];
                foreach($product['options'] as $option_name => $option_data){
                  foreach($option_data as $option_value_name => $option_value_data){
                    $data['product_options'][$option_name][$option_value_name] = $option_value_name;
                  }
                }
              }
            }

            $this->importSetOptions($data); //добавление опций в базу и создания соответствий (добавляем опции которых нет в базе)
            $this->importInsertOptions($data, true); //добавляем опции к товарам

          }
        //product_options

        //product_image (проверка на обновление была выше $this->importUpdateCheckImage в обходе товара при записив  таблицу product)
        $this->importDownloadProductImage($data); //физически загружаем фото на сервер + здесь идет проверка на расширение файла + добавляем фото в базу + в таблицу unixml_import_image

        //product_images
          if(!in_array('images', $data['no_update'])){ //если обновляем дополнительные фото
            $images = array();
            $sql = "SELECT product_id, image FROM " . DB_PREFIX . "product_image WHERE product_id IN(" . implode(',', $data['product_ids']) . ") ORDER BY product_id, sort_order ASC";
            $images_query = $this->db->query($sql);
            $data['stat']['sql']++;

            foreach($images_query->rows as $row){
              $images[$row['product_id']][] = $row['image']; //все фото товара на данный момент
            }

            $sql = "SELECT product_id, oc, image FROM " . DB_PREFIX . "unixml_import_image WHERE main_image = 0 AND product_id IN(" . implode(',', $data['product_ids']) . ") ORDER BY product_id, item_id ASC";
            $images_query = $this->db->query($sql);
            $data['stat']['sql']++;

            foreach($images_query->rows as $row){
              $images_prev[$row['product_id']][] = $this->importClearImage($row['oc']); //все фото товара с прошлого раза
              $links_prev[$row['product_id']][] = $this->importClearImage($row['image']); //все ссылки товара с прошлого раза
            }

            foreach($data['product_update'] as $product){
              $update = false;

              //если отличаются ссылки в этот раз и в тот
                $current_compare = $product['images'];
                if($current_compare){
                  foreach($product['images'] as $ik => $iv){
                    $current_compare[$ik] = $this->importClearImage($iv);
                  }
                }
                if(isset($links_prev[$product['product_id']]) && array_diff($links_prev[$product['product_id']], $current_compare)){
                  $update = true;
                }
              //если отличаются ссылки в этот раз и в тот

              //если есть фото и они отличаются от тех что были тот раз
                $images_compare = array();
                if(isset($images[$product['product_id']])){
                  if($images[$product['product_id']]){
                    foreach($images[$product['product_id']] as $ik => $iv){
                      $images_compare[$ik] = $this->importClearImage($iv);
                    }
                  }
                }
                if(!$update && isset($images_prev[$product['product_id']]) && isset($images[$product['product_id']]) && array_diff($images_prev[$product['product_id']], $images_compare)){
                  $update = true;
                }
              //если есть фото и они отличаются от тех что были тот раз

              //если есть фото в XML но их нет в товаре
                if(!$update && $product['images'] && !isset($images[$product['product_id']])){
                  $update = true;
                }
              //если есть фото в XML но их нет в товаре

              //если физически нет фото
                if(!$update && isset($images[$product['product_id']])){
                  foreach($images[$product['product_id']] as $image){
                    if(!is_file(DIR_IMAGE . $image)){
                      $update = true;
                    }
                  }
                }
              //если физически нет фото

              //если есть фото, но их нет в базе unixml или доп фото
                if(!$update && $product['images'] && (!isset($images_prev[$product['product_id']]) || !isset($links_prev[$product['product_id']]))){
                  $update = true;
                }
              //если есть фото, но их нет в базе unixml или доп фото

              if($update){
                $this->importGetImagesName($data, $product);
              }
            }

            $this->importDownloadProductImages($data); //физически загружаем дополнительные фото на сервер + здесь идет проверка на расширение файла
          }
        //product_images

        if(isset($data['custom_url'])){
          foreach($data['product_update'] as $product){
            $this->importAddProductCustomAlias($data, $product, $product['product_id']);
          }
        }

        $this->unsetData($data, 'product_ids,product_image,product_images,image_old');
        $this->importSetStat($data, 'product');
      }
    //importUpdateProduct

    //importInsertToDb - функция записи в базу - пакетно (при добавлении товара)
      private function importInsertToDb(&$data){
        if(isset($data['to_db']) && $data['to_db']){
          foreach($data['to_db'] as $table => $array){
            if($array && count($array) > 1){ //если есть данные кроме заголовков
              foreach($array as $key => $item){
                if(!$key){ //первая запись - ключи
                  $sql = "INSERT INTO " . DB_PREFIX . $table . " (`" . implode("`,`", $item) . "`) VALUES ";
                }else{ //данные
                  $sql .= "('" . implode("','", $item) . "'),";
                }
              }

              $this->db->query(rtrim($sql, ','));
              $this->importSetStat($data, 'product');
              $data['stat']['sql']++;
            }
          }
          $this->db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "product");
          $data['stat']['sql']++;
          $this->unsetData($data, 'to_db');
        }
      }
    //importInsertToDb

    //importSetManufacturer - функция добавления производителя
      private function importSetManufacturer(&$data, &$product){
        if($product['manufacturer_id']){ // $product['manufacturer_id'] - название бренда из XML (потом заменится на его id с магазина)
          if(isset($data['manufacturers'][$product['manufacturer_id']])){ //если есть такой бренд присваиваем его id к товару
            $product['manufacturer_id'] = $data['manufacturers'][$product['manufacturer_id']];
          }else{ //если нет такого бренда добавляем
            $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($product['manufacturer_id']) . "'");
            $data['stat']['sql']++;
            if(!isset($data['stat']['manufacturers'])){
              $data['stat']['manufacturers'] = 0;
            }
            $data['stat']['manufacturers']++;
            $manufacturer_id = $this->db->getLastId();

            if($data['manufacturer_description']){
              foreach($data['languages'] as $lang){
                $this->db->query("REPLACE INTO " . DB_PREFIX . "manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$lang['language_id'] . "', " . $data['manufacturer_filed'] . " = '" . $this->db->escape($product['manufacturer_id']) . "'");
              }
            }

            foreach($data['stores'] as $store_id){
              $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
              $data['stat']['sql']++;
            }

            $this->importAddAlias($data, $this->transform($product['manufacturer_id']), $manufacturer_id, 'manufacturer');

            $data['manufacturers'][$product['manufacturer_id']] = $manufacturer_id; //все бренды магазина название - id
            $product['manufacturer_id'] = $manufacturer_id;
          }
        }
      }
    //importSetManufacturer

    //importSetTemplate - функция генерации значений полей по шаблонам
      private function importSetTemplate(&$data, &$product){
        if($data['template']){ //если есть кастомные шаблоны полей
          foreach($data['template'] as $product_key => $template_value){
            if(isset($product[$product_key])){ //если есть поля для замены в массиве
              $template_replaces = $this->findInText($template_value, "{{", "/{{(.*?)}}/");

              $from = array();
              $to = array();
              foreach($template_replaces as $replace_item){
                $from[] = '{{' . $replace_item . '}}';

                $replace_to = '';
                if(substr($replace_item, 0, 4) == 'RAND'){ //если RANDOM
                  $random_data = explode(',', $replace_item);

                  $range_start = 10000;
                  $range_finish = 90000;
                  if(isset($random_data[1])){
                    $range_data = explode('-', trim($random_data[1]));
                    $range_start = isset($range_data[0])?trim($range_data[0]):10000;
                    $range_finish = isset($range_data[1])?trim($range_data[1]):90000;
                  }
                  $replace_to = rand($range_start, $range_finish);
                }elseif(substr($replace_item, 0, 4) == 'NUMB'){ //если NUMB
                  $replace_to = $data['row_key'];
                }elseif(isset($product[$replace_item])){ //если поле товара
                  $replace_to = $product[$replace_item];
                }

                $to[] = $replace_to;
              }

              $product[$product_key] = trim(str_replace($from, $to, $template_value));
            }
          }
        }
      }
    //importSetTemplate

    //importSetReplace - функция замены значений полей
      private function importSetReplace(&$data, &$product){
        if($data['replace']){ //если есть замены
          foreach($data['replace'] as $product_key => $replace_value){
            if(isset($product[$product_key])){ //если есть поля для замены в массиве
              if(!is_array($product[$product_key])){ //если строка - не массив
                $product[$product_key] = trim(str_replace($replace_value['from'], $replace_value['to'], $product[$product_key]));
              }else{
                foreach($product[$product_key] as $data_key => $data_value){
                  if(!is_array($data_value)){ //если категории, фото и т.п
                    $product[$product_key][$data_key] = trim(str_replace($replace_value['from'], $replace_value['to'], $product[$product_key][$data_key]));
                  }else{
                    foreach($data_value as $item_key => $item_value){
                      $product[$product_key][$data_key][$item_key] = trim(str_replace($replace_value['from'], $replace_value['to'], $product[$product_key][$data_key][$item_key]));
                    }
                  }
                }
              }
            }
          }
        }
      }
    //importSetReplace

    //importSetCalc - функция для калькуляции значений полей
      private function importSetCalc($data, &$product){
        if($data['calc']){ //если есть замены
          foreach($data['calc'] as $product_key => $replace_value){
            if(isset($product[$product_key])){ //если есть поля для калькуляции в массиве
              $data['markup'] = $replace_value;
              $product[$product_key] = $this->markupCalc($product[$product_key], $data);
            }
          }
        }
      }
    //importSetCalc

    //importSetProductCategory - функция замены catagory_id с xml на магазин
      private function importSetProductCategory(&$data, &$product){
        $additional_category_id = array();

        if(isset($product['category_id']['@attributes'])){
          unset($product['category_id']['@attributes']);
        }

        if(!is_array($product['category_id'])){
          $product['category_id'] = array($product['category_id']);
        }

        $product_category_ids = $product['category_id'];
        $product['category_id'] = array();
        foreach($product_category_ids as $product_category_key => $product_category_id){
          if(isset($data['xml_oc'][$product_category_id])){ //если есть соответствия категорий из Opencart
            $product['category_id'][$product_category_key] = $data['xml_oc'][$product_category_id];
          }
          if($data['stop_category_id'] && isset($data['stop_category_id'][$product_category_id])){ //если есть соответствия категорий из настроек
            if(!is_array($data['stop_category_id'][$product_category_id])){ // если это не мультикатегория
              $product['category_id'][$product_category_key] = $data['stop_category_id'][$product_category_id];
            }else{ //в соответствиях 2 и больше привязки
              unset($product['category_id'][$product_category_key]);
              $additional_category_id = array_merge($data['stop_category_id'][$product_category_id], $additional_category_id);
            }
          }
        }

        if($additional_category_id){
          $product['category_id'] = array_merge($additional_category_id, $product['category_id']);
        }

        $product['category_id'] = array_unique($product['category_id']);

        //csv_support
          if(!$product['category_id'] && isset($data['xls'])){
            foreach($product_category_ids as $product_category_id){
              $product_category_id = str_replace('>', '&gt;', $product_category_id);
              if(isset($data['replace_category'][$product_category_id])){
                foreach($data['replace_category'][$product_category_id] as $replace_category_id){
                  $product['category_id'][] = $replace_category_id;
                }
              }
            }
          }
        //csv+xls
      }
    //importSetProductCategory

    //importSetAttribute - функция добавления атрибутов (на выходе id атрибута и его значение)
      private function importSetAttribute(&$data, &$product) {
        $product_attributes = array();

        foreach($product['attributes'] as $sort_order => $attribute){
          if($attribute && $attribute['name']){

            //привязка на группу
            $with_group = false; //по умолчанию атрибут не вяжем на группу
            if(isset($attribute['group']) && $attribute['group']){ //если есть группа в атрибуте ставим флаг что этот атрибут привязывается к группе
              $with_group = true;

              if(!isset($data['attributes_group'])){ //если еще не забрали группы атрибутов - самое время это сделать
                $data['attributes_group'] = array(); //тут будут группа - ид
                $data['attributes_group_attributes'] = array(); //тут будут ид группы - атрибуты группы (название - ид)

                $group_query = $this->db->query("SELECT attribute_group_id, name FROM " . DB_PREFIX . "attribute_group_description");
                $data['stat']['sql']++;

                foreach($group_query->rows as $agrow){
                  $data['attributes_group'][$agrow['name']] = $agrow['attribute_group_id'];
                }
              }

              if(isset($data['attributes_group'][$attribute['group']])){ //если уже есть такая группа атрибутов в магазине
                $attribute_group_id = $data['attributes_group'][$attribute['group']];
              }else{ //если нет такой группы атрибута добавляем
                $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET sort_order = '0'");
                $data['stat']['sql']++;
                $attribute_group_id = $this->db->getLastId();

                foreach($data['languages'] as $lang){
                  $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$lang['language_id'] . "', name = '" . $this->db->escape($attribute['group']) . "'");
                  $data['stat']['sql']++;
                }

                if($attribute['group'] && $attribute_group_id){
                  $data['attributes_group'][$attribute['group']] = $attribute_group_id; //все группы атрибутов магазина название - id
                }
              }
            }
            //привязка на группу

            $attribute['name'] = str_replace($data['attribute_replace_from'], $data['attribute_replace_to'], $attribute['name']); //замены названий

            if($with_group){ //если есть привязка у этого атрибута на группу

              if($attribute_group_id){ //если у этого атрибута есть группа
                if(!isset($data['attributes_group_attributes'][$attribute_group_id])){ //если нет данных о атрибутах в этой группе
                  $data['attributes_group_attributes'][$attribute_group_id] = array();

                  $attributes_query = $this->db->query("SELECT a.attribute_id, ad.name FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_group_id = '" . (int)$attribute_group_id . "'");
                  $data['stat']['sql']++;

                  foreach($attributes_query->rows as $row){
                    if($row['name'] && $row['attribute_id']){
                      $data['attributes_group_attributes'][$attribute_group_id][$row['name']] = $row['attribute_id']; //все атрибуты группы название - id
                    }
                  }
                } //добавили атрибуты в этой группе

                if(isset($data['attributes_group_attributes'][$attribute_group_id][$attribute['name']])){ //если уже есть такой атрибут в такой группе в магазине
                  $attribute_id = $data['attributes_group_attributes'][$attribute_group_id][$attribute['name']];
                }else{ //если нет такого атрибута добавляем

                  $this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$attribute_group_id . "', sort_order = '" . (int)$sort_order . "'");
                  $data['stat']['sql']++;
                  $data['stat']['attributes']++;
                  $attribute_id = $this->db->getLastId();

                  foreach($data['languages'] as $lang){
                    $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$lang['language_id'] . "', name = '" . $this->db->escape($attribute['name']) . "'");
                    $data['stat']['sql']++;
                  }

                  if($attribute['name'] && $attribute_id){
                    $data['attributes_group_attributes'][$attribute_group_id][$attribute['name']] = $attribute_id; //все атрибуты этой группы название - id
                  }

                }
              }

            }else{ //если без привязки - смотрим на первый атрибут и вяжем его, если нет - создаем в группу ту что выбрали в настройках импорта
              if(isset($data['attributes'][$attribute['name']])){ //если уже есть такой атрибут в магазине
                $attribute_id = $data['attributes'][$attribute['name']];
              }else{ //если нет такого атрибута добавляем
                $this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$data['attribute_group'] . "', sort_order = '" . (int)$sort_order . "'");
                $data['stat']['sql']++;
                $data['stat']['attributes']++;
                $attribute_id = $this->db->getLastId();

                foreach($data['languages'] as $lang){
                  $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$lang['language_id'] . "', name = '" . $this->db->escape($attribute['name']) . "'");
                  $data['stat']['sql']++;
                }

                if($attribute['name'] && $attribute_id){
                  $data['attributes'][$attribute['name']] = $attribute_id; //все атрибуты магазина название - id
                }
              }
            }

            $attribute['value'] = $this->db->escape(trim($attribute['value']));

            if($attribute['value']){
              $product_attributes[] = array(
                'attribute_id' => $attribute_id,
                'value' => $attribute['value']
              );
            }
          }
        } //foreach

        $product['attributes'] = $product_attributes;
      }
    //importSetAttribute

    //importFixProductImages - функция фикса первого доп фото
      private function importFixProductImages(&$data, &$product){
        $product['image'] = str_replace(' ', '%20', $product['image']);
        if($data['unixml_import_xml_product_image'] == $data['unixml_import_xml_product_images']){
          array_shift($product['images']); //если теги одинаковые из доп удаляем первое фото
        }
        if(substr($product['image'], 0, 4) != 'http'){ //если не ссылка на фото
          $product['image'] = '';
        }
        foreach($product['images'] as $image_key => $image){
          $product['images'][$image_key] = str_replace(' ', '%20', $product['images'][$image_key]);
          if(substr($image, 0, 4) != 'http'){ //если не ссылка на фото
            unset($product['images'][$image_key]);
          }
        }
      }
    //importFixProductImages

    //importFixProductPrice - функция замены акции на цену
      private function importFixProductPrice(&$data, &$product){
        $product['price'] = str_replace(',', '.', $product['price']);
        if($product['special'] && !isset($data['fieldset']['product_special']['special_value'])){
          $price = $product['price'];
          $product['price'] = $product['special'];
          $product['special'] = $price;
        }
      }
    //importFixProductPrice

    //importCalcPriceCurrency - функция калькуляции цены от валюты
      private function importCalcPriceCurrency(&$data, &$product, $row){
        if(!isset($data['currencies'])){
          $data['currencies'] = array();
          if(isset($data['xml']->currencies->currency) && $data['xml']->currencies->currency){
            foreach($data['xml']->currencies->currency as $currency){
              $data['currencies'][$this->importGetXmlData($currency, '@id')] = $this->importGetXmlData($currency, '@rate');
            }
          }
        }
        if($data['currencies'] && $product['price']){
          $rate = 1;
          $currency_id = $this->importGetXmlData($row, 'currencyId');
          if($currency_id && isset($data['currencies'][$currency_id])){
            $rate = $data['currencies'][$currency_id];
            $product['price'] *= (float)$rate;
            if($product['special']){
              $product['special'] *= $rate;
            }
          }
        }
      }
    //importCalcPriceCurrency

    //importSetProductQuantity - функция определения количества товара
      private function importSetProductQuantity(&$data, &$product){
        if(in_array(strtolower($product['quantity']), array('in stock', 'true','t','yes','y','+','В наличии','Наличие','few','В наявності'))){
          $product['quantity'] = 777;
        }
        $product['quantity'] = ((int)$product['quantity'] > 0)?(int)$product['quantity']:0;
      }
    //importSetProductQuantity

    //importSetProductStatus - функция определения статуса товара
      private function importSetProductStatus($data, &$product){
        $product['status'] = 1;
        if($data['status'] == 'add' && isset($data['fieldset']['product']['status_value'])){ //если товар новый и выкл у новых
          $product['status'] = 0;
        }

        if(!$product['quantity'] && isset($data['fieldset']['product_quantity']['sip_value'])){ //если не в наличии и статус от наличия
          $product['status'] = 0;
        }
      }
    //importSetProductStatus

    //importGetOcProducts - функция получения всех товаров по ключу
      private function importGetOcProducts(&$data){
        if(!isset($data['products'])){
          $data['products'] = array();

          $this->importGetImportLink($data);

          $key_table = array('p'=>'product','pd'=>'product_description');
          $link_data = explode('.', $data['link']['oc']);

          $from_table = isset($key_table[$link_data[0]])?$key_table[$link_data[0]]:'product';
          $from_field = isset($link_data[1])?$link_data[1]:'unixml_link';

          $sql_plus = "";
          //p.sku,feed
            if(isset($data['link_feed']) && $data['link_feed']){ //если прописано примерно так p.sku,feed
              $sql_plus = " WHERE unixml_feed = '" . (int)$data['import_id'] . "'";
            }
          //p.sku,feed

          // if($from_field == 'unixml_link' && $from_table == 'product'){ //если стоит по умолчанию то есть линковка по offer id
          //   $sql_plus = " WHERE unixml_feed = '" . (int)$data['import_id'] . "'";
          // }

          $products = $this->db->query("SELECT " . $from_field . ", product_id FROM " . DB_PREFIX . $from_table . $sql_plus);
          $data['stat']['sql']++;

          foreach($products->rows as $row){
            if($row[$from_field] && $row['product_id']){
              $data['products'][$row[$from_field]] = $row['product_id']; //все товары магазина по связанному ключу
            }
          }
        }
      }
    //importGetOcProducts

    //importGetOcManufacturers - функция получения всех производителей
      private function importGetOcManufacturers(&$data){
        if(!isset($data['manufacturers'])){
          $data['manufacturers'] = array();

          $manufacturers_query = $this->db->query("SELECT manufacturer_id, name FROM " . DB_PREFIX . "manufacturer");
          $data['stat']['sql']++;

          foreach($manufacturers_query->rows as $row){
            if($row['name'] && $row['manufacturer_id']){
              $data['manufacturers'][$row['name']] = $row['manufacturer_id']; //все бренды магазина название - id
            }
          }
        }
      }
    //importGetOcManufacturers

    //importGetOcAttributes - функция получения всех атрибутов
      private function importGetOcAttributes(&$data){
        if(!isset($data['attributes'])){
          $data['attributes'] = array();
          $data['attribute_group'] = ''; //тут будет группа куда складывать атрибуты

          if(isset($data['fieldset']['product_attributes']['attr_value']) && isset($data['attribute_to_group']) && $data['attribute_to_group']){
            $attributes_query = $this->db->query("SELECT ad.attribute_id, ad.name FROM " . DB_PREFIX . "attribute_description ad LEFT JOIN " . DB_PREFIX . "attribute a ON (ad.attribute_id = a.attribute_id) WHERE a.attribute_group_id = '" . (int)$data['fieldset']['product_attributes']['attr_value'] . "'");
          }else{
            $attributes_query = $this->db->query("SELECT attribute_id, name FROM " . DB_PREFIX . "attribute_description");
          }

          $data['stat']['sql']++;

          foreach($attributes_query->rows as $row){
            if($row['name'] && $row['attribute_id']){
              $data['attributes'][$row['name']] = $row['attribute_id']; //все атрибуты магазина название - id
            }
          }
        }

        if(isset($data['fieldset']['product_attributes']['attr_value'])){
          $data['attribute_group'] = $data['fieldset']['product_attributes']['attr_value'];
        }
      }
    //importGetOcAttributes

    //importGetStores - функция получения всех магазинов
      private function importGetStores(&$data) {
        $data['stores'][] = 0;
        $query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . "store ORDER BY url");
        foreach($query->rows as $store_row){
          $data['stores'][] = $store_row['store_id'];
        }
        $data['stat']['sql']++;
      }
    //importGetStores

    //importGetLangs - функция получения всех языков
      private function importGetLangs(&$data) {
        $langs_query = $this->db->query("SELECT code, language_id FROM " . DB_PREFIX . "language ORDER BY sort_order, name");
        $data['languages'] = $langs_query->rows;
        $data['stat']['sql']++;
      }
    //importGetLangs

    //importGetAliasTable - функция получения таблицы ЧПУ
      public function importGetAliasTable(&$data){
        $data['table'] = (substr(VERSION, 0, 1) == 3)?'seo_url':'url_alias';
      }
    //importGetAliasTable

    //importGetXml - функция считывания файла XML
      public function importGetXml(&$data) {

        $this->importGetXmlString($data);
        $this->importSetStat($data, 'loaded');

        if(!isset($data['xls'])){ //если это XML
          $data['xml'] = str_replace(array('<g:', '</g:'), array('<', '</'), $data['xml']);
          $data['xml'] = $data['xml']?simplexml_load_string($data['xml'], 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_PARSEHUGE):"Error: Не удалось прочитать файл";
          $data['xml'] = $data['unixml_import_xml_root']?$data['xml']->{$data['unixml_import_xml_root']}:$data['xml'];
        }else{ //если это XLS
          if($data['unixml_import_xml_root'] !== FALSE){ //csv_support
            //csv - закомментировать - тест
            $data['xml'] = $data['xml'][$data['unixml_import_xml_root']]; //лист с товарами
          }

        }

        $this->importSetStat($data, 'parsed');
      }
    //importGetXml

    //importGetXmlString - функция получения XML в строке
      public function importGetXmlString(&$data) {
        $data['xml'] = false;
        $file = trim(str_replace('&amp;', '&', $data['price_file']));

        if(!isset($data['unixml_import_xml_product_multilang'])){
          $data['unixml_import_xml_product_multilang'] = array();
        }

        if(substr($file, 0, 4) == 'http'){ //если это ссылка - скачиваем с сервера
          if(!$data['unixml_import_xml_product_multilang'] && is_file(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml')){ //если есть XML уже загруженный
            $data['xml'] = file_get_contents(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml');
          }else{ //загружаем XML
            $ch = curl_init($file);
            if(isset($data['login']) && $data['login'] && isset($data['pass']) && $data['pass']){ //если авторизация
              curl_setopt($ch, CURLOPT_USERPWD, $data['login'] . ":" . $data['pass']);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36');
            $output = curl_exec($ch);

            $data['xml'] = $output;

            //сохраняем файл на сервере
            $xml_file = fopen(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml', 'w');
            fwrite($xml_file, $data['xml']);
            fclose($xml_file);
            //сохраняем файл на сервере
          }

        }else{ //если это файл - забираем его
          $file = str_replace("system/", "", DIR_SYSTEM) . $file;
          if(is_file($file)){
            $data['xml'] = file_get_contents($file);
          }
        }

        //xls
          if(is_file(DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml')){
            $xml_file = DIR_SYSTEM . 'unixml/import/' . $data['import_id'] . '.xml';
          }else{
            $xml_file = $file;
          }

          $ext = explode('.', $file);
          $ext = end($ext);
          if(in_array($ext, array('xls','xlsx'))){
            if(is_file(DIR_SYSTEM . 'unixml/phpe/Classes/PHPExcel.php')){
              require_once DIR_SYSTEM . 'unixml/phpe/Classes/PHPExcel.php';
            }else{ //если нет phpexcel - скачиваем в фоне
              if(copy('https://unixml.pro/phpe/phpe.zip', DIR_SYSTEM . '/unixml/phpe.zip')){
                $zip = new ZipArchive;
                if($zip->open(DIR_SYSTEM . '/unixml/phpe.zip') === TRUE){
                  $zip->extractTo(DIR_SYSTEM . 'unixml/');
                  $zip->close();
                  require_once DIR_SYSTEM . 'unixml/phpe/Classes/PHPExcel.php';
                  unlink(DIR_SYSTEM . '/unixml/phpe.zip');
                }else{
                  exit('Error download zip PHPExcel');
                }
              }else{ //если загрузилось
                exit('Error download zip PHPExcel');
              }
            }

            $xls = new PHPExcel();
            $xls = PHPExcel_IOFactory::load($xml_file);

            $data['xls'] = true;
            $data['xml'] = array();

            foreach($xls->getWorksheetIterator() as $worksheet) {
             $data['xml'][] = $worksheet->toArray();
            }
          }
        //xls

        //csv_support
        if(in_array($ext, array('csv','CSV'))){
          $data['xls'] = true;
          $data['xml'] = array();
          $file_content = explode(PHP_EOL, file_get_contents($xml_file));
          foreach($file_content as $row){
            $csv = str_getcsv($row, ';');
            $data['xml'][] = $csv;
          }
        }
        //csv

      }
    //importGetXmlString

    // importGetCategoryReplaceName - функция определения cопоставлений категорий
      private function importGetCategoryReplaceName(&$data, &$category_name) {
        $data['category_replace_name'] = array();
        $data['category_replace_name']['from'] = array();
        $data['category_replace_name']['to'] = array();
        $name_unic = '***###***';

        if($data['fieldset'] && !$data['category_replace_name']['from']){ //формируем списки замен
          foreach($data['fieldset'] as $field => $field_data){
            if(isset($field_data['category_replace_name_value_from']) && isset($field_data['category_replace_name_value_to'])){
              $data_from = explode(PHP_EOL, $field_data['category_replace_name_value_from']);
              foreach ($data_from as $from) {
                $data['category_replace_name']['from'][] = $name_unic . $from . $name_unic;
              }

              $data_to = explode(PHP_EOL, $field_data['category_replace_name_value_to']);
              foreach ($data_to as $to) {
                $data['category_replace_name']['to'][] = $name_unic . $to . $name_unic;
              }
            }
          }
        }

        if($data['category_replace_name']){ //если есть замены
          $category_name = $name_unic . $category_name . $name_unic;
          $category_name = str_replace($data['category_replace_name']['from'], $data['category_replace_name']['to'], $category_name);
          $category_name = str_replace($name_unic, '', $category_name);
        }
      }
    // importGetCategoryReplaceName

    //importSetCategory - функция работы с категориями
      private function importSetCategory(&$data) {
        $this->importGetReplaceCategory($data); //Выборка замен категорий + выборка категорий из фида

        $data['stat']['category'] = 0;
        $categories = array();
        $all_categories = array();

        //if(isset($data['fieldset']['categories']['nupd_value'])){ //если не обновлять
          //$all_categories[0][] = array('name'=>'UniXML - новые товары','category_id'=>'111333777','parent_id'=>0); //заглушка для новых товаров
        //}else{
          foreach($data['categories_xml'] as $category){
            $category_name = $category['category_name'];
            $category_id = $category['category_id'];
            $parent_id = $category['parent_id'];
            $category_url = $category['url'];

            //если есть ид категории в запрете то пропускаем
            //или если есть ид категории в запрете на добавление категорий (из соответствий XML -> OC)
            if(in_array($category_id, $data['continue_category_id']) || isset($data['stop_category_id'][$category_id])){
              continue;
            }

            if($category_name && $category_id){
              $this->importGetCategoryReplaceName($data, $category_name); //замены названий категорий

              $all_categories[$parent_id?$parent_id:0][$category_id] = array( //сортируем по parent_id что бы потом в цикле забрать подкатегории по родителю
                'name' => $category_name,
                'category_id' => $category_id,
                'parent_id' => $parent_id,
                'category_url' => $category_url,
              );

            }
          } //foreach
        //} // if['categories']['nupd_value']

        if(isset($all_categories[0])){
          foreach($all_categories[0] as $category){ //обходим категории без parent_id то есть топ категории
            $categories[] = array(
              'name' => $category['name'],
              'category_id' => $category['category_id'],
              'category_url' => $category['category_url'],
              'parents' => $this->importSetParentsCategory($all_categories, $category['category_id'])
            );
          }
        }

        $this->importSetCategories($data, $categories);
        $this->unsetData($data, 'category_url,categories');
        $this->importSetStat($data, 'category');
      }
    //importSetCategory

    //importSetCategories - функция добавления категорий
      private function importSetCategories(&$data, $categories, $parent_id = '') {
        if($categories){
          $this->importGetOcUrl($data, 'category'); //забираем все ЧПУ категорий (делать если стоит генерить ЧПУ)
          $this->importGetOcCategories($data); //забираем все категории магазина

          $category_id = false;
          foreach($categories as $sort_order => $category){
            $category_name_decode = html_entity_decode($category['name'], ENT_QUOTES);
            if(isset($data['categories'][$category['name']])){ //если категория есть
              $data['xml_oc'][$category['category_id']] = $data['categories'][$category['name']]; //берем id категории и делаем соответствие id xml = id магазина
              $category_id = $data['categories'][$category['name']];
            }elseif(isset($data['categories'][$category_name_decode])){ //если категория есть
              $data['xml_oc'][$category['category_id']] = $data['categories'][$category_name_decode]; //берем id категории и делаем соответствие id xml = id магазина
              $category_id = $data['categories'][$category_name_decode];
            }else{ //если категории нет
              $data['status'] = isset($data['fieldset']['category_name']['url_value'])?'add':'update';
              $this->importGenerateUrl($data, $category, 'category');
              $category['parent_id'] = $parent_id;
              $category['sort_order'] = $sort_order;
              $category['status'] = isset($data['fieldset']['category']['status_value'])?0:1;
              $category['top'] = isset($data['fieldset']['category']['top_value'])?1:0;

              if(!isset($data['fieldset']['categories']['nupd_value'])){ //если нет настройки не обновлять категорию
                $category_id = $this->importAddCategory($data, $category); //добавляем категорию
                $data['xml_oc'][$category['category_id']] = $category_id;
              }

            }
            if($category_id){
              $this->importSetCategories($data, $category['parents'], $category_id); //проход и по подкатегориям
            }
          } //foreach
        }
      }
    //importSetCategories

    //importAddCategory - функция добавления категории
      private function importAddCategory(&$data, $category) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$category['parent_id'] . "', sort_order = '" . (int)$category['sort_order'] . "', status = '" . (int)$category['status'] . "', top = '" . (int)$category['top'] . "', date_modified = NOW(), date_added = NOW()");
        $data['stat']['sql']++;

        $category_id = $this->db->getLastId();

        foreach($data['languages'] as $lang){
          $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$lang['language_id'] . "', name = '" . $this->db->escape($category['name']) . "', meta_title = '" . $this->db->escape($category['name']) . "'");
          $data['stat']['sql']++;
        }

        $level = 0;
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['parent_id'] . "' ORDER BY `level` ASC");
        $data['stat']['sql']++;
        foreach ($query->rows as $result) {
          $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");
          $level++;
          $data['stat']['sql']++;
        }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");
        $data['stat']['sql']++;

        foreach($data['stores'] as $store_id){
          $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
          $data['stat']['sql']++;
        }

        $this->importAddAlias($data, $category['keyword'], $category_id, 'category');
        $data['stat']['category']++;

        return $category_id;
      }
    //importAddCategory

    //importAddAlias - функция добавления ЧПУ
      private function importAddAlias(&$data, $keyword, $id, $type = 'product') {
        if($keyword){ //если есть ЧПУ - добавляем
          if(substr(VERSION, 0, 1) != 3){
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = '" . $type . "_id=" . (int)$id . "', keyword = '" . $this->db->escape($keyword) . "'"); //2.x
            $data['stat']['sql']++;
          }else{ // если opencart 3
            $keyword_original = $keyword;
            foreach($data['stores'] as $store_id){
              foreach($data['languages'] as $lang){
                if($lang['code'] != $this->config->get('config_language')){ //если более 1 языка всем последующим даем приставку языка
                  $lang['code'] = explode('-', $lang['code']);
                  $keyword = $this->transform($lang['code'][0]) . '-' . $keyword_original;
                }else{
                  $keyword = $keyword_original;
                }

                if($data['unixml_import_url_without_language']){
                  $keyword = $keyword_original;
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$lang['language_id'] . "', query = '" . $type . "_id=" . (int)$id . "', keyword = '" . $this->db->escape($keyword) . "'");
                $data['stat']['sql']++;
              }
            }
          }
        }
      }
    //importAddAlias

    //importAddProductCustomAlias - функция добавления Кастомного ЧПУ
      private function importAddProductCustomAlias(&$data, $product, $id, $language_id = false) {
        $keyword = isset($product[$data['custom_url']])?$product[$data['custom_url']]:false;
        if($keyword){ //если есть ЧПУ - добавляем

          if($language_id){ //если указан язык
            if(substr(VERSION, 0, 1) == 3){ //и тройка
              $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$id . "' AND language_id = '" . (int)$language_id . "'");
              $data['stat']['sql']++;

              foreach($data['stores'] as $store_id){
                  $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$id . "', keyword = '" . $this->db->escape($keyword) . "'");
                  $data['stat']['sql']++;
              }
            }
          }else{ //если язык не указан
            $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$id . "'");
            $data['stat']['sql']++;

            if(substr(VERSION, 0, 1) != 3){
              $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$id . "', keyword = '" . $this->db->escape($keyword) . "'"); //2.x
              $data['stat']['sql']++;
            }else{ // если opencart 3
              $keyword_original = $keyword;
              foreach($data['stores'] as $store_id){
                foreach($data['languages'] as $lang){
                  if($lang['code'] != $this->config->get('config_language')){ //если более 1 языка всем последующим даем приставку языка
                    $lang['code'] = explode('-', $lang['code']);
                    $keyword = $this->transform($lang['code'][0]) . '-' . $keyword_original;
                  }else{
                    $keyword = $keyword_original;
                  }

                  if($data['unixml_import_url_without_language']){
                    $keyword = $keyword_original;
                  }

                  $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$lang['language_id'] . "', query = 'product_id=" . (int)$id . "', keyword = '" . $this->db->escape($keyword) . "'");
                  $data['stat']['sql']++;
                }
              }
            }
          }


        }
      }
    //importAddProductCustomAlias

    //importGetOcUrl - функция получения ЧПУ
      private function importGetOcUrl(&$data, $type = 'product', $id = '') {
        if(!isset($data[$type . '_url'])){ //если нет еще массива с ЧПУ
          $data[$type . '_url'] = array();

          $url_query = $this->db->query("SELECT query, keyword FROM " . DB_PREFIX . $data['table'] . " WHERE query " . ($id?'=':'LIKE') . " '" . $type . "_id=" . ($id?$id:'%') . "'");

          foreach($url_query->rows as $url_row){
            if($url_row['keyword']){
              $data[$type . '_url'][$url_row['keyword']] = $url_row['query'];
            }
          }

          $data['stat']['sql']++;
        }
      }
    //importGetOcUrl

    //importGetOcCategories - функция получения всех категорий магазина
      private function importGetOcCategories(&$data){
        if(!isset($data['categories'])){
          $data['categories'] = array();
          $categories_query = $this->db->query("SELECT c.category_id, cd.name FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) ORDER BY c.parent_id ASC");
          foreach($categories_query->rows as $row){
            $data['categories'][$row['name']] = $row['category_id'];
            $data['categories'][html_entity_decode($row['name'], ENT_QUOTES)] = $row['category_id'];
          }

          $data['stat']['sql']++;
        }
      }
    //importGetOcCategories

    //importGenerateUrl - функция генерации ЧПУ
      private function importGenerateUrl(&$data, &$item, $type){
        $item['keyword'] = false;

        if($data['status'] == 'add'){
          if($data['fieldset'] && !isset($data['url_from'])){
            $data['url_from'] = '';
            ksort($data['fieldset']);
            foreach($data['fieldset'] as $field => $value){
              if(isset($value['url_value'])){
                $data['url_from'] = $field;
              }
            }
          }

          if($type == 'category' && isset($data['fieldset']['category_name']['url_value']) && $data['fieldset']['category_name']['url_value']){
            $data['url_from'] = 'category_name';
          }

          if(isset($data['url_from']) && $data['url_from']){
            $transform_from = false;
            if(isset($item[$data['url_from']])){
              $transform_from = $item['name'];
            }elseif(isset($item[str_replace('product_', '', $data['url_from'])])){
              $transform_from = $item[str_replace('product_', '', $data['url_from'])];
            }elseif(isset($item[str_replace('category_', '', $data['url_from'])])){
              $transform_from = $item[str_replace('category_', '', $data['url_from'])];
            }

            if($transform_from){
              $item['keyword'] = $this->transform($transform_from);
              if(isset($data[$type . '_url'][$item['keyword']])){ //если уже есть такой url
                $item['keyword'] = $item['keyword'] . '-' . $item[$type . '_id'];
              }
              $data[$type . '_url'][$item['keyword']] = $type . '_id=' . $item[$type . '_id'];
            }
          }

          //custom_category_url
          if(isset($item['category_url']) && $item['category_url']){
            $item['keyword'] = $item['category_url'];
          }
          //custom_category_url

        }
      }
    //importGenerateUrl

    //importSetParentsCategory - функция структурирования категорий в вид дерева
      private function importSetParentsCategory($all_categories, $category_id){
        $categories = array();

        if(isset($all_categories[$category_id])){ //если есть блок в подкатегориями
          foreach($all_categories[$category_id] as $category){
            $categories[] = array(
              'name' => $category['name'],
              'category_id' => $category['category_id'],
              'category_url' => $category['category_url'],
              'parents' => $this->importSetParentsCategory($all_categories, $category['category_id'])
            );
          }
        }

        return $categories;
      }
    //importSetParentsCategory

    //importGetXmlData - функция получения конечных данных с массива
      private function importGetXmlData($item, $setting, $type = 'string'){
        $data = false;

        if(!$setting){ //информацию берем со значения
          $data = trim($item[0]);
        }else{
          if(substr($setting, 0, 1) == '@'){ //берем с атрибута
            $clear_key = trim(str_replace('@', '', $setting));
            $attributes = $this->importGetXmlDataAttribute($item);
            if($attributes && isset($attributes[$clear_key])){
              $data = $attributes[$clear_key];
            }
          }else{ //берем из значения
            $comby_detect = strpos($setting, '@');
            if($comby_detect === false){ //если обычный тег
              if(isset($item->$setting)){
                $data = $item->$setting;
              }
            }else{ //если тег и атрибут
              $setting_data = explode('@', $setting);
              $setting_tag = isset($setting_data[0])?trim($setting_data[0]):false;
              $setting_attr = isset($setting_data[1])?trim($setting_data[1]):false;
              if($setting_tag && $setting_attr){
                $current_items = $item->$setting_tag;
                foreach($current_items as $current_item){
                  $attributes = $this->importGetXmlDataAttribute($current_item);
                  if($attributes && isset($attributes[$setting_attr])){
                    // $data[$attributes[$setting_attr]] = array( //ключ - для уникальности атрибутов (баг когда одинаковые атрибуты)
                    if(!$data){
                      $data = array();
                    }
                    $data[] = array(
                      $setting_attr => $attributes[$setting_attr],
                      'value' => trim($current_item[0]),
                    );
                  }
                }
              }
              if($data){
                $data = array_values($data);
              }
            }
          }
        }

        //xls
        if(is_array($item)){
          if($setting !== false && isset($item[$setting])){
            $data = $item[$setting];
          }else{
            $data = false;
          }
        }
        //xls

        if($type == 'string'){
          $data = trim(htmlspecialchars((string)$data));
        }elseif($type == 'int') {
          $data = trim((int)$data);
        }elseif($type == 'array') {
          $data = (array)$data;
          foreach($data as $key => $value){
            if(is_array($value)){
              foreach($value as $v_key => $v_data){
                $data[$key][$v_key] = preg_replace('/\s\s+/', ' ', trim($v_data));
              }
            }
          }
        }
        return $data;
      }
    //importGetXmlData

    //importGetXmlDataAttribute - функция получения атрибутов
      private function importGetXmlDataAttribute($item){
        $attributes_array = (array)$item->attributes();
        return isset($attributes_array['@attributes'])?$attributes_array['@attributes']:false;
      }
    //importGetXmlDataAttribute

    //importGetManufacturerDescription - функция проверки наличия таблицы manufacturer_description и поля в таблице
      public function importGetManufacturerDescription(&$data){
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "manufacturer_description'");
        $data['stat']['sql']++;
        $data['manufacturer_description'] = $query->num_rows;

        if($data['manufacturer_description']){
          $data['manufacturer_filed'] = 'meta_title';
          $field_query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "manufacturer_description LIKE 'name'");
          $data['stat']['sql']++;
          if($field_query->num_rows){
            $data['manufacturer_filed'] = 'name';
          }
        }
      }
    //importGetManufacturerDescription

    //importGetCustomerGroups - функция получения групп покупателей
      private function importGetCustomerGroups(&$data) {
        $customer_groups = $this->db->query("SELECT customer_group_id FROM " . DB_PREFIX . "customer_group");
        $data['stat']['sql']++;
        $data['customer_groups'] = $customer_groups->rows;
      }
    //importGetCustomerGroups
  //Функции импорта

  //Функции экспорта - админка

    //insertTables - таблицы и поля для вставки данных при сохранении настроек
      public $insertTables = array(
        'additional_params' => array('param_name','param_text'),
        'attributes' => array('attribute_id','xml_name'),
        'replace_name' => array('name_from','name_to','replace_where'),
        'category_match' => array('category_id','xml_name','markup','custom'),
        'product_markup' => array('name','products','markup')
      );
    //insertTables

    //exportGetFeeds - функция получения списка фидов с базы, если задан $feed - отдает настройки текущего фида
      public function exportGetFeeds($feed = false) {
        $feeds = array();
        $db_feeds_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "unixml_setting` WHERE `code` = 'export.feeds' ORDER BY setting_id ASC");

        if($db_feeds_query->rows){
          foreach ($db_feeds_query->rows as $row) {
            $data_feed = array();

            $data_fd = unserialize($row['value']);

            $data_feed['name'] = $data_fd[0];
            $data_feed['plus'] = isset($data_fd[1])?$data_fd[1]:false;
            $data_feed['minus'] = isset($data_fd[2])?$data_fd[2]:false;
            $data_feed['photo'] = isset($data_fd[3])?$data_fd[3]:false;

            $feeds[$row['name']] = $data_feed;
          }
        }

        return $feed?$feeds[$feed]:$feeds;
      }
    //exportGetFeeds

    //exportGetSetting - функция получения настроек фида по ключу или без ключа - все настройки
      public function exportGetSetting($key, $feed) {

        if(!$key){ //если не указан ключ - забираем все данные с конфига
          $setting = array();

          $setting_query = $this->db->query("SELECT name, value FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'export.setting." . $this->db->escape($feed) . "'");

          foreach($setting_query->rows as $row){ //формируем массив с настройками ключ = значение
            if(substr($row['value'], 0, 4) == 'uss:'){
              $row['value'] = unserialize(str_replace('uss:', '', $row['value']));
            }
            $setting[$row['name']] = $row['value'];
          }

          //обходим все поля настроек ($config_item - ключ)
          foreach($this->getSettingFields() as $setting_block){
            foreach($setting_block as $config_item => $config_text){
              if(!isset($setting[$config_item])){ // если поля в настройках нет - создаем его в массиве
                $setting[$config_item] = $this->config->get($this->varname($key, $feed)); //должно быть false, НО! Забираем данные со стандартной таблицы setting это для перехода c 5 версии на 7 версию
              }
            }
          }

          ksort($setting);

        } else { //если ключ указан - забираем данные по ключу
          $setting = '';
          $setting_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE `code` = 'export.setting." . $this->db->escape($feed) . "' AND name = '" . $this->db->escape($key) . "'");
          if($setting_query->num_rows){
            $setting = $setting_query->row['value'];
            if(substr($setting, 0, 4) == 'uss:'){
              $setting = unserialize(str_replace('uss:', '', $setting));
            }
          }

          if(!$setting){$setting = $this->config->get($this->varname($key, $feed));} //фикс - забираем данные со стандартной таблицы setting это для перехода c 5 версии на 7 версию

        }

        return $setting;
      }
    //exportGetSetting

    //exportConvertName - функция для поиска при неправильном вводе
      private function exportConvertName($text){
        $add_text = '';
        if($text){
          $from = preg_split('//u', "qwertyuiopasdfghjklzxcvbnm", -1, PREG_SPLIT_NO_EMPTY);
          $to = preg_split('//u', "йцукенгшщзфывапролдячсмить", -1, PREG_SPLIT_NO_EMPTY);
          $add_text = str_replace($from, $to, $text);

          $from = preg_split('//u', "qwertyuiopasdfghjklzxcvbnm", -1, PREG_SPLIT_NO_EMPTY);
          $to = preg_split('//u', "квертиуиопасдфгхжклзксвнм", -1, PREG_SPLIT_NO_EMPTY);
          $add_text .= str_replace($from, $to, $text);

          $from = preg_split('//u', "квертиуиопасдфгхжклзксвнм", -1, PREG_SPLIT_NO_EMPTY);
          $to = preg_split('//u', "qwertyuiopasdfghjklzxcvbnm", -1, PREG_SPLIT_NO_EMPTY);
          $add_text .= str_replace($from, $to, $text);
        }

        return $add_text;
      }
    //exportConvertName

    //exportGetfeedList - функция списка фидов в админке
      public function exportGetfeedList($search, $trash = false) {
        $data['exports'] = array();
        $trash_feeds = array();

        $sorts = array();
        $sort_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'feed_sorts'");
        if($sort_query->num_rows){
          $sorts = array_flip(unserialize($sort_query->row['value']));
        }

        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'trash'");
        if($query->num_rows){
          $trash_feeds = explode(',', $query->row['value']);
        }

        $feed_iter = 1;

        foreach($this->exportGetFeeds() as $feed_key => $data_feed){
          $feed_name = $data_feed['name'];

          if($search){
            $feed_search = $this->exportConvertName($feed_name . $feed_key);
            if(strripos($feed_name . $feed_key . $feed_search, $search) === false){
              continue;
            }
          }

          if(in_array($feed_key, $trash_feeds) != $trash){
            continue;
          }

          $name = $feed_key;
          if($feed_name){
            $name = '<b>' . $feed_name . '</b>' . '<small style="display:block;">(' . $feed_key . ')</small>';
          }

          $filename = $this->exportGetSetting('xml_name', $feed_key);
          if($filename){
            $filename = trim($filename, '/');
          }

          if(!$filename){
            $filename = $feed_key;
          }

          $url_plus = '';
          $secret = $this->exportGetSetting('secret', $feed_key);
          if($secret){
            $url_plus = '&key=' . $secret;
          }

          $feed_iter_key = $feed_iter;
          $export_num = $feed_iter;
          if($sorts && isset($sorts[$feed_key])){
            $feed_iter_key = $sorts[$feed_key] . '.' . $feed_iter;
            $export_num = $sorts[$feed_key];
          }

          $data['exports'][$feed_iter_key] = array(
            'export_num' => $export_num,
            'name' => $name,
            'feed' => $feed_key,
            'status' => $this->exportGetSetting('status', $feed_key),
            'link_direct' => HTTPS_CATALOG . 'index.php?route=' . $this->path . '/' . $feed_key . $url_plus,
            'link_cron' => HTTPS_CATALOG . 'index.php?route=' . $this->path . '/' . $feed_key . '&cron=file' . $url_plus,
            'link_file' => HTTPS_CATALOG . $this->pricedir . '/' . $filename . '.xml',
          );

          $feed_iter++;
        }

        ksort($data['exports']);

        return $data;
      }
    //exportGetfeedList

    //exportGetAllCategories - функция выборки категорий и раскрывающийся список
      public function exportGetAllCategories($parent_id = 0) {
        $categories = array();

        $query = $this->db->query("SELECT c.category_id, cd.name, c.sort_order, c.status FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.parent_id = '" . $parent_id . "' GROUP BY c.category_id ORDER BY c.sort_order, cd.name ASC");

        foreach ($query->rows as $category) {
          $categories[$category['category_id']] = array(
            'name' => $category['name'],
            'category_id' => $category['category_id'],
            'child' => $this->exportGetAllCategories($category['category_id']),
          );
        }

        return $categories;
      }
    //exportGetAllCategories

    //exportGetExportFields - функция получения всех полей настроек
      public function exportGetExportFields(){
        $export_fields = array();
        $block_id = 1;

        foreach($this->getSettingFields() as $block => $fields){
          $block_fields = array();
          $field_id = 1;
          foreach ($fields as $field_code => $field_name) {
            if($field_name == ''){continue;}
            $block_fields[] = array(
              'field_id' => $field_id,
              'field_name' => $field_name,
              'field_search' => $this->getSettingFieldSearch($field_code)
            );
            $field_id++;
          }

          $export_fields[] = array(
            'block_name' => $block,
            'block_search' => mb_strtolower($block, 'UTF-8'),
            'block_id' => $block_id,
            'block_fields' => $block_fields
          );
          $block_id++;
        }

        return $export_fields;
      }
    //exportGetExportFields

    //exportGetAllSettingFields -  функция получения полей настроек для админки (function => field)
      public function exportGetAllSettingFields() {
        $fields = array('exportGetOptionData'=>'option_multiplier', 'exportGetAttributeList'=>'attributes', 'exportGetReplaceNameList'=>'replace_name', 'exportGetAdditionalParamList'=>'additional_params', 'exportGetReplaceCategory'=>'category_match' , 'exportGetMarkup'=>'product_markup');

        foreach($this->getSettingFields() as $setting_block){
          foreach($setting_block as $config_item => $config_text){
            $fields[$config_item] = $config_item;
          }
        }
        unset($fields['product_markup']);
        unset($fields['additional_params']);
        unset($fields['category_match']);
        unset($fields['replace_name']);
        unset($fields['products']);

        return $fields;
      }
    //exportGetAllSettingFields

    //exportGetOptionList - функция получения списка опций
      public function exportGetOptionList($language){
        if (!$language){ $language = (int)$this->config->get('config_language_id'); }
        $options = array();

        $query = $this->db->query("SELECT option_id, name FROM " . DB_PREFIX . "option_description WHERE language_id = '" . (int)$language . "'");

        foreach($query->rows as $row){
          $options[] = array(
            'option_id' => $row['option_id'],
            'name'      => $row['name']
          );
        }

        return $options;
      }
    //exportGetOptionList

    //exportGetTrashToggle - функция корзины в админке
      public function exportGetTrashToggle(){
        $trash_toggle = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'export.setting' AND name = 'trash_toggle'");

        return $trash_toggle->num_rows;
      }
    //exportGetTrashToggle

    //exportGetFeedFiles - функция поиска файлов фидов
      public function exportGetFeedFiles(){
        $dir_feeds = array();
        foreach (glob(DIR_CATALOG . 'controller/' . $this->path . '/*.php', GLOB_BRACE) as $filename) {
          $filename_data = explode('feed/unixml/', $filename);
          $filename_data = explode('.', $filename_data[1]);
          $dir_feeds[] = $filename_data[0];
        }

        return $dir_feeds;
      }
    //exportGetFeedFiles

  //Функции экспорта - админка

  //Функции экспорта - экспорт

    //exportGetCategories - функция на выходе которой категории в массиве. На входе id категорий строкой (1,2,3) - запускается из catalog/controller/feed/unixml/startup
      public function exportGetCategories(&$data) {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '3600');

        $data['category_markup'] = array();
        $data['category_tag'] = array();
        $data['categories_for_id'] = '';

        if(!$data['categories']){ //если не заданы категории выгрузки
          if($data['products'] && !$data['products_mode']){ //если заданы товары и стоит режим выгрузки только - забираем те категории
            $sql = "SELECT DISTINCT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id IN(" . $this->db->escape($data['products']) . ")";
          }elseif($data['manufacturers']){
            $sql = "SELECT DISTINCT p2c.category_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product p ON(p2c.product_id = p.product_id) WHERE p.manufacturer_id IN(" . $this->db->escape($data['manufacturers']) . ")";
          }else{
            $sql = "SELECT DISTINCT category_id FROM " . DB_PREFIX . "product_to_category";
          }
          $category_query = $this->db->query($sql);
          $data['stat']['sql']++;
          $data['categories'] = implode(',', array_column($category_query->rows, 'category_id'));
        }
        $all_categories = explode(',', $data['categories']);

        //добавляем категорию в товарах а также
          if($data['products'] && $data['products_mode'] == 2){
            if(!$data['seopro']){ //если не привязываемся - забираем крайнюю категорию
              $sql = "SELECT sub2c.category_id, sub2c.product_id FROM " . DB_PREFIX . "product_to_category sub2c LEFT JOIN " . DB_PREFIX . "category_path cp ON(sub2c.category_id = cp.category_id) WHERE sub2c.product_id IN(" . $data['products'] . ") ORDER BY cp.level DESC LIMIT 1";
            }else{
              $sql = "SELECT category_id, product_id FROM " . DB_PREFIX . "product_to_category WHERE product_id IN(" . $data['products'] . ") AND main_category = '1'";
            }
            $additional_category_query = $this->db->query($sql);

            foreach($additional_category_query->rows as $category_row){
              $all_categories[] = $category_row['category_id'];
            }
          }
        //добавляем категорию в товарах а также
        $data['categories_for_id'] = implode(',', $all_categories);

        $categories = array();

        //список что на что меняем и добавляем наценки в массив категорий
        $category_match_array = array();
        $category_match_query = $this->db->query("SELECT category_id, xml_name, markup, custom FROM " . DB_PREFIX . "unixml_category_match WHERE feed = '" . $this->db->escape($data['feed']) . "'");
        $data['stat']['sql']++;
        foreach($category_match_query->rows as $row){
          $category_match_array[$row['category_id']] = $row['xml_name'];
          if($row['markup']){ //если есть наценка - добавляем в массив
            $data['category_markup'][$row['category_id']] = $row['markup'];
          }
          if($row['custom']){ //если есть кастомные теги - добавляем в массив
            $data['category_tag'][$row['category_id']] = $row['custom'];
          }
        }

        //список с названиями
        $category_names_array = array();
        $category_original_names_array = array();
        $category_names_query = $this->db->query("SELECT category_id, name FROM " . DB_PREFIX . "category_description WHERE language_id = '" . (int)$data['language'] . "'");
        $data['stat']['sql']++;
        foreach($category_names_query->rows as $row){
          if(isset($row['name']) && $row['name']){
            $category_original_names_array[$row['category_id']] = $row['name'];
            if($category_match_array){
              if(isset($category_match_array[$row['category_id']]) && $category_match_array[$row['category_id']]){
                $row['name'] = $category_match_array[$row['category_id']]; //меняем на категория маркета
              }
            }
            $category_names_array[$row['category_id']] = $row['name'];
          }
        }

        //$paths = $this->exportGetCategoriesPath();
        $data['stat']['sql']++;
        foreach($all_categories as $cat_id){
          // if(!isset($paths[$cat_id])){
            $categories_path = explode('_', $this->getPathByCategory($cat_id));
            $data['stat']['sql']++;
          // }else{
          //   $categories_path = explode('_', $paths[$cat_id]);
          // }

          foreach($categories_path as $level => $category_id){
            if($category_id && isset($category_names_array[$category_id])){
              $category_name = $category_names_array[$category_id];
              $market_id = false;
              $category_data = explode('-', $category_name);
              if(isset($category_data[1]) && (int)trim($category_data[0])){
                $market_id = (int)trim($category_data[0]);
                $category_name = trim($category_data[1]);
              }

              if(!$category_name){
                $category_name = $category_original_names_array[$category_id];
              }

              $categories[$category_id] = array(
                'category_id' => $category_id,
                'name'        => $this->clearData($category_name),
                'market_id'   => $market_id,
                'original'    => $this->clearData($category_original_names_array[$category_id]),
                'parent_id'   => $level?$categories_path[$level-1]:''
              );
            }
          }
        }

        $data['categories_xml'] = $categories;
      }
    //exportGetCategories

    //exportGetCategoriesPath - функция получения path всех категорий
      public function exportGetCategoriesPath(){
        $categories = array();

        $path_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE name = 'category_path'");

        if($path_query->num_rows){
          $categories = unserialize($path_query->row['value']);
        }

        return $categories;
      }
    //exportGetCategoriesPath

    //getPathByCategory - функция выборки пути к категории
      public function getPathByCategory($category_id) {
        $path = null;
    		$sql = "SELECT CONCAT_WS('_'";
    		for ($i = 6; $i >= 0; --$i) { $sql .= ",t" . $i . ".category_id"; }
    		$sql .= ") AS path FROM " . DB_PREFIX . "category t0";
    		for ($i = 1; $i < 7; ++$i) { $sql .= " LEFT JOIN " . DB_PREFIX . "category t$i ON (t$i.category_id = t" . ($i-1) . ".parent_id)"; }
    		$sql .= " WHERE t0.category_id = '" . (int)$category_id . "'";
    		$query = $this->db->query($sql);
    		$path[$category_id] = $query->num_rows ? $query->row['path'] : false;
    		return $path[$category_id];
    	}
    //getPathByCategory

    //exportGetCurrencyCode - функция получения кода валюты
      public function exportGetCurrencyCode(&$data) {
        $currency_query = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "currency` WHERE currency_id = '" . (int)$data['currency'] . "'");
        $data['stat']['sql']++;
        $data['currency_xml'] = $currency_query->num_rows ? $currency_query->row['code'] : $this->config->get('config_currency');
      }
    //exportGetCurrencyCode

    //exportGetCurrencyValue - функция получения курса валют
      public function exportGetCurrencyValue(&$data) {
        $currency_query = $this->db->query("SELECT currency_id, `value` FROM `" . DB_PREFIX . "currency`");
        $data['stat']['sql']++;

        $data['currencies'] = array();
        $data['multicurrency'] = array();

        foreach($currency_query->rows as $crow){
          $currency_value[$crow['currency_id']] = $crow['value'];
        }
        $data['currency'] = $currency_value[$data['currency']];

        //multicurrency
          $product_currency_id_query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product LIKE 'currency_id'");

          if($product_currency_id_query->num_rows){ //если есть
            $data['multicurrency'] = $currency_value;
          }
        //multicurrency
      }
    //exportGetCurrencyValue

    //exportGetProductFields - функция для получения списка полей из базы p и pd
      public function exportGetProductFields(&$data){
        $data['all_access_column'] = array();
        foreach($this->getTableFields('product') as $row){
          $data['all_access_column'][] = 'p.' . $row['COLUMN_NAME'];
        }
        foreach($this->getTableFields('product_description') as $row){
          $data['all_access_column'][] = 'pd.' . $row['COLUMN_NAME'];
        }
        if(isset($data['stat']['sql'])){
          $data['stat']['sql'] += 2;
        }
      }
    //exportGetProductFields

    //exportGetAdditionalParams - функция выборки дополнительных статических параметров
      public function exportGetAdditionalParams(&$data){
        $query = $this->db->query("SELECT param_name as name, param_text as text FROM " . DB_PREFIX . "unixml_additional_params WHERE feed = '" . $this->db->escape($data['feed']) . "' ORDER BY item_id ASC");
        $data['stat']['sql']++;
        $data['additional_params'] = $query->rows;
      }
    //exportGetAdditionalParams

    //exportGetSqlFields - функция получения списка полей для выгрузки (берем необходимые + все шаблоны генераций и забираем нужные поля для базы)
      public function exportGetSqlFields(&$data) {
        $data['selected_vars'] = array(
          'p.product_id' => 'product_id',
          'p.image' => 'image',
          'p.stock_status_id' => 'stock_status_id',
          'p.quantity' => 'quantity',
          'p.model' => 'model',
          'p.price' => 'price',
          'pd.name' => 'name',
          'pd.description' => 'description',
          'm.name' => 'manufacturer'
        );

        $data['from'] = array();
        $data['to'] = array();

        if($data['seopro']){
          $data['selected_vars']['p2c.category_id'] = 'category_id';
        }

        $custom_fields = array();

        if($data['fields']){ //если есть дополнительные поля для выгрузки
          $custom_fields = explode(',', $data['fields']);
        }

        if($data['field_price'] && $data['field_price'] != 'p.price'){ //если есть кастомное поле откуда цена и оно не стандартное
          $custom_fields[] = trim($data['field_price']);
          $data['field_price'] = trim(str_replace(array('p.','pd.'), '', $data['field_price']));
        }

        if($data['field_id']){ //если есть настройка откуда брать id товара
          $custom_fields[] = trim($data['field_id']);
          $data['field_id'] = trim(str_replace(array('p.','pd.'), '', $data['field_id']));
        }

        //если есть дополнительные поля для выгрузки в шаблонах генерации
        $db_replaces = $this->findInText($this->apText($data), "((", "/\(\((.*?)\)\)/");

        if($db_replaces){
          foreach($db_replaces as $field){
            $data['from']['((' . $field . '))'] = '((' . $field . '))';
            if(in_array($field, $data['all_access_column'])){ //если есть поле в базе - ставим как ключ замены
              $data['to']['((' . $field . '))'] = str_replace(array('p.','pd.'), '', $field);
            }else{ //если нет поля в базе - ставим замену на пустое значение
              $data['to']['((' . $field . '))'] = '';
            }
          }
        }
        $custom_fields = array_merge($custom_fields, $db_replaces);

        if($custom_fields){
          foreach($custom_fields as $field){
            $field = trim(str_replace(array(' ','(',')','{','}','[',']'), '', $field));
            if(in_array($field, $data['all_access_column'])){
              $data['selected_vars'][$field] = str_replace(array('p.','pd.'), '', $field);
            }
          }
        }

      }
    //exportGetSqlFields

    //exportGetProductMarkup - функция выборки наценки на группы товара
      public function exportGetProductMarkup(&$data){
        $data['product_markup'] = array();
        $product_markup_query = $this->db->query("SELECT products, markup FROM " . DB_PREFIX . "unixml_product_markup WHERE feed = '" . $this->db->escape($data['feed']) . "'");
        $data['stat']['sql']++;
        if($product_markup_query->num_rows){
          foreach($product_markup_query->rows as $markup_row){
            $markup_products_data = explode(',', $markup_row['products']);
            foreach($markup_products_data as $product_id){
              $data['product_markup'][$product_id] = $markup_row['markup'];
            }
          }
        }
      }
    //exportGetProductMarkup

    //exportGetReplaceList - функция получения списка замен
      public function exportGetReplaceList(&$data) {
        $data['replace_list'] = array();

        $query = $this->db->query("SELECT name_from, name_to, replace_where FROM " . DB_PREFIX . "unixml_replace_name WHERE feed = '" . $this->db->escape($data['feed']) . "'");
        $data['stat']['sql']++;
        foreach($query->rows as $row){
          $replace_where = explode(',', $row['replace_where']);
          foreach($replace_where as $replace_item){
            $data['replace_list'][$replace_item][] = array(
              'from'  => $row['name_from'],
              'to'    => $row['name_to']
            );
          }
        }

      }
    //exportGetReplaceList

    //exportGetProducts - функция сборки товара и формирование его в массив для передачи в шаблон XML
      public function exportGetProducts(&$data) { //запускаем один раз за итерацию из startup
        $products = array();

        $rows = $this->exportGetProductsSql($data); //берем товары с базы
        if(!$rows){ return $products; } //завершаем и далее не идем

        $this->ExportCustomXmlAfterSql($rows, $data); //кастомный код после запроса в базу
        $this->exportCheckNoImage($rows, $data); //проверяем товар на фото
        $this->exportSetAttributes($rows, $data); //добавляем атрибуты и формируем замены с атрибутов
        $this->exportSetDiscounts($rows, $data); //добавляем оптовые цены где это нужно
        $this->exportSetImages($rows, $data); //добавляем дополнительные фото
        $this->exportSetMultiplyOption($rows, $data); //умножение на опции (комбинации как отдельные товары)
        $this->exportSetArrayReplaces($data); //добавление списка замен с массива товаров

        foreach ($rows as $product) {
          $this->exportChangeProduct($product, $data); //финальный проход по товарам
          if(isset($product['continue']) && $product['continue']){
            continue;
          }
          $products[$product['product_id']] = $product;
        }

        $this->ExportCustomFinal($products, $data); //кастомный код после всех обработок
        $this->exportSetStat($rows, $data); //статистика

        return $products;
      }
    //exportGetProducts

    //exportChangeProducts - функция изменения товарА, применения дополнительных настроек и данных
      public function exportChangeProduct(&$product, &$data) {
        $this->exportGetAdditionalData($product, $data); //забираем кастомные данные для конкретной выгрузки
        $this->exportAddUtm($product, $data); //приставка к ссылке
        $this->exportAddCategory($product, $data); //добавляем категорию
        $this->exportPriceProduct($product, $data); //расчет цены исходя из курса
        $this->exportMarkupProduct($product, $data); //применения наценок на товар
        $this->exportSetAdditionalParams($product, $data); //добавляем кастомные теги и параметры к товару
        $this->exportCheckAdditionalData($product, $data); //Добавление кастомных данных для фидов
        $this->exportGenerateData($product, $data); //генерации по шаблону, замены данных
        $this->exportReplaceProductData($product, $data); //замены данных из списка замен
        $this->exportChangeOfferId($product, $data); //замена id товара
        $this->ExportCustomXml($product, $data); //кастомный код в итерации цикла товара
        $this->exportCheckDoubleImage($product, $data); //поиск дублей фото
        $this->exportConvertWebp($product, $data); //конвертируем webp - png
        $this->exportCheckStock($product, $data); //проверка наличия - ставим товарам stock
        $this->exportFixSpecialCharacter($product, $data); //фикс всякого мусора с базы данных. Типа прямых амперсандов или скобок
      }
    //exportChangeProducts

    //exportConvertWebp - конвертируем webp - png
      public function exportConvertWebp(&$product, &$data) {
        if(isset($data['convert_webp'])){
          $product['image'] = $this->exportConvertWebpImage($product['image']);
          foreach($product['images'] as $ik => $ii){
            $product['images'][$ik] = $this->exportConvertWebpImage($ii);
          }
        }
      }

      public function exportConvertWebpImage($image) {
        $ext = $this->getImageExtension($image);
        if($ext == 'webp'){
          $file_name = str_replace(HTTP_SERVER . 'image/', '', $image);
          $new_name = str_replace('.webp', '.png', $file_name);
          if(!is_file(DIR_IMAGE . $new_name)){ //если еще нет файлa - создаем и конвертируем в png
            copy(DIR_IMAGE . $file_name, DIR_IMAGE . $new_name); //копируем фото рядом
            $im = imagecreatefromwebp(DIR_IMAGE . $file_name);
            imagepalettetotruecolor($im);
            imagealphablending($im, true);
            imagesavealpha($im, true);
            imagepng($im, DIR_IMAGE . $new_name);
            imagedestroy($im);
          }
          return HTTP_SERVER . 'image/' . $new_name;
        }
      }
    //exportConvertWebp - конвертируем webp - png

    //exportCheckAdditionalData - добавления кастомных данных для фидов
      public function exportCheckAdditionalData(&$product, &$data) {
        if($data['feed'] == 'google' || $data['feed'] == 'facebook' || isset($data['product_type'])){ //google+fb
          if(!isset($data['product_type'])){
            $data['product_type'] = true;
          }

          $product_type = array();

          if((int)$product['category_id']){
            $category_path = unserialize($data['category_path']);

            $product['category_path'] = $product['category_id'];
            if(isset($product['category_id']) && isset($category_path[$product['category_id']])){
              $product['category_path'] = $category_path[$product['category_id']];
            }

            foreach(explode('_', $product['category_path']) as $category_id){
              if(isset($data['categories_xml'][$category_id])){
                $product_type[] = $data['categories_xml'][$category_id]['original'];
              }
            }
          }

          if($data['product_type'] == false){
            $product_type = array();
          }

          if($product_type){
            $product['attributes_full'][] = array(
              'name' => 'g:product_type',
              'end'  => 'g:product_type',
              'text' => implode(' > ', $product_type)
            );
          }
        } //google+fb

        if(isset($data['product_category_path']) && $data['product_category_path'] && !isset($product['category_path'])){
          if((int)$product['category_id']){
            $category_path = unserialize($data['category_path']);
            $product['category_path'] = $product['category_id'];
            if(isset($product['category_id']) && isset($category_path[$product['category_id']])){
              $product['category_path'] = $category_path[$product['category_id']];
            }
          }
        }

      }
    //exportCheckAdditionalData

    //exportFixSpecialCharacter - функция чистки от прямых тегов в базе
      public function exportFixSpecialCharacter(&$product, $data) {
        $from = array('&lt;','&gt;','&amp;', '&quot;', '&apos;');
        $to = array('<','>','&', '"', "'");
        $from1 = array('&', '<','>', '"', "'");
        $to1 = array('&amp;','&lt;','&gt;', '&quot;', '&apos;');

        foreach(array('name','manufacturer','model') as $product_field){
          if($product[$product_field]){
            $product[$product_field] = str_replace($from, $to, $product[$product_field]);
            $product[$product_field] = str_replace($from1, $to1, $product[$product_field]);
          }
        }

        $product['name'] = str_replace(array('&#39;','&#039;','&amp;#39;','&amp;#039;'), "'", $product['name']);

        foreach($product['attributes'] as $ak => $av){
          if($av['text'] == ''){unset($product['attributes'][$ak]);continue;}
          $product['attributes'][$ak]['text'] = str_replace($from, $to, $product['attributes'][$ak]['text']);
          $product['attributes'][$ak]['text'] = str_replace($from1, $to1, $product['attributes'][$ak]['text']);
          $product['attributes'][$ak]['name'] = str_replace($from, $to, $product['attributes'][$ak]['name']);
          $product['attributes'][$ak]['name'] = str_replace($from1, $to1, $product['attributes'][$ak]['name']);
          if(isset($av['decode']) && $av['decode']){
            $product['attributes'][$ak]['text'] = html_entity_decode($av['text'], ENT_QUOTES, 'UTF-8');
          }
          if($data['feed'] == 'rozetka' && isset($av['additional'])){
            $product['attributes'][$ak]['additional'] = str_replace(array('paramcode', 'valuecode'), array('paramid', 'valueid'), $av['additional']);
          }
        }

        foreach($product['attributes_full'] as $afk => $afv){
          if($afv['text'] == ''){unset($product['attributes_full'][$afk]);continue;}
          $product['attributes_full'][$afk]['text'] = str_replace($from, $to, $product['attributes_full'][$afk]['text']);
          $product['attributes_full'][$afk]['text'] = str_replace($from1, $to1, $product['attributes_full'][$afk]['text']);
          if($product['attributes_full'][$afk]['name'] == 'g:product_type'){
            $product['attributes_full'][$afk]['text'] = str_replace('&gt;', '>', $product['attributes_full'][$afk]['text']);
          }
          if(isset($afv['decode']) && $afv['decode']){
            $product['attributes_full'][$afk]['text'] = html_entity_decode($afv['text'], ENT_QUOTES, 'UTF-8');
          }
        }

        if(isset($product['langdata'])){
          foreach($product['langdata'] as $lang_data_key => $lang_data){
            foreach($lang_data as $key_key => $key_value){
              if($key_key != 'attributes'){
                $product['langdata'][$lang_data_key][$key_key] = str_replace($from, $to, $product['langdata'][$lang_data_key][$key_key]);

                //замена названий опций
                if(isset($product['option_data']) && $product['option_data']){
                  foreach($product['option_data'] as $option_data){
                    if(isset($data['replace_option_id_name'][$option_data['option_id']][$option_data['option_value_id']])){
                      $product['langdata'][$lang_data_key][$key_key] = str_replace($product[$product['optionset_key']], $data['replace_option_id_name'][$option_data['option_id']][$option_data['option_value_id']], $product['langdata'][$lang_data_key][$key_key]);
                    }
                  }
                }

                if($key_key != 'description'){
                  $product['langdata'][$lang_data_key][$key_key] = str_replace($from1, $to1, $product['langdata'][$lang_data_key][$key_key]);
                }
                // if(isset($lang_data['attributes']) && $lang_data['attributes']){ //???
                //   $product['langdata'][$lang_data_key][$key_key] = str_replace(array_keys($lang_data['attributes']), array_values($lang_data['attributes']), $product['langdata'][$lang_data_key][$key_key]);
                // }
              }
              if(isset($lang_data['attributes']) && $lang_data['attributes']){
                $product['langdata'][$lang_data_key][$key_key] = str_replace(array_keys($lang_data['attributes']), array_values($lang_data['attributes']), $product['langdata'][$lang_data_key][$key_key]);
              }

              /*
              фикс когда нет данных
              if(!isset($langdata['description']) OR !$langdata['description']){
$langdata['description'] = $product['description'];
}
              */

            }
          }
        }

      }
    //exportFixSpecialCharacter

    //exportAddCategory - функция добавления категории в массив товара
      public function exportAddCategory(&$product, $data) {
        $product['category'] = '';
        if($product['category_id'] && isset($data['categories_xml'][$product['category_id']])){
          $product['category'] = $data['categories_xml'][$product['category_id']]['name'];
          $product['category_original'] = $data['categories_xml'][$product['category_id']]['original'];
        }
      }
    //exportAddCategory

    //exportGetProductsSql - функция получения товаров из базы данных - основной sql запрос
      public function exportGetProductsSql(&$data) {
        session_write_close();
        if((int)$data['step'] < 1){ $data['step'] = 10000; } //если не установили количество за раз ставим по умолчанию 10к
        if(!isset($data['seopro'])){ $data['seopro'] = 0; } //проверка на seopro

        //for opencart 3x
        $data['system_language'] = $this->config->get('config_language_id');
        $this->config->set('config_language_id', $data['language']);
        //for opencart 3x

        $sql_plus = '';
        foreach($data['selected_vars'] as $selected_var => $selected_as){
          $selected_as_name = explode('.', $selected_var);
          $sql_plus .= ", " . $selected_var;
          if(isset($selected_as_name[1]) && $selected_as_name[1] != $selected_as){
            $sql_plus .= " as " . $selected_as;
          }
        }
        $sql = "SELECT" . ltrim($sql_plus, ','); //добавили выборку полей

        if(!$data['seopro']){ //если не привязываемся - забираем крайнюю категорию
          $sql .= ", (SELECT sub2c.category_id FROM " . DB_PREFIX . "product_to_category sub2c LEFT JOIN " . DB_PREFIX . "category_path cp ON(sub2c.category_id = cp.category_id) WHERE sub2c.product_id = p.product_id";
          if($data['categories']){ //если заданы категории
            $sql .= " AND sub2c.category_id IN(" . $this->db->escape($data['categories_for_id']) . ")";
          }
          $sql .= " ORDER BY cp.level DESC LIMIT 1) AS category_id";
        }

        //$sql .= ", (SELECT CONCAT(ps.price, '==', ps.date_start, '==', ps.date_end) FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ps.date_start < NOW() AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) ORDER BY ps.priority ASC LIMIT 1) as special";
        $sql .= ", (SELECT CONCAT(ps.price, '==', ps.date_start, '==', ps.date_end) FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ps.date_start < NOW() AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) LIMIT 1) as special";
        $sql .= " FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
        $sql .= " WHERE ";
        if($data['products'] && !$data['products_mode']){ //если есть товары выбиранные вручную + мод = 0, то есть только эти товары
          $sql .= "p.product_id IN (" . $this->db->escape($data['products']) . ")";
        }else{ //если нет товаров или же режим запрета некоторых товаров (1) или же "а также товары" (2) - выполяем условия по категориям и брендам
          if($data['products'] && $data['products_mode']){ //если заданы товары и стоит режим запрета или а также
            $sql .= "("; //что бы выполнялось условие с товарами а также
          }
          if($data['categories'] && $data['manufacturers']){ //если заданы и категории и бренды
            $and_or = $data['andor']?"OR":"AND";
            $sql .= "(p2c.category_id IN (" . $this->db->escape($data['categories']) . ") " . $and_or . " p.manufacturer_id IN (" . $this->db->escape($data['manufacturers']) . "))";
          }elseif($data['categories']){ //если заданы категории
            $sql .= "p2c.category_id IN (" . $this->db->escape($data['categories']) . ")";
          }elseif($data['manufacturers']){ //если заданы бренды
            $sql .= "p.manufacturer_id IN (" . $this->db->escape($data['manufacturers']) . ")";
          }else{ //если категории и бренды не заданы
            $sql .= "p.product_id > 0"; //пустышка для WHERE
          }
          if($data['products'] && $data['products_mode'] == 1){ //если заданы товары и стоит режим запрета (1)
            $sql .= "  AND p.product_id NOT IN (" . $this->db->escape($data['products']) . ")";
          }
          if($data['products'] && $data['products_mode'] == 2){ //если заданы товары и стоит режим "а также" (2)
            $sql .= "  OR p.product_id IN (" . $this->db->escape($data['products']) . ")"; //)) - последняя для теста
          }
          if($data['products'] && $data['products_mode']){ //если заданы товары и стоит режим запрета или а также
            $sql .= ")"; //что бы выполнялось условие с товарами а также
          }
        }

        if(!isset($data['store'])){$data['store'][] = 0;}
        if(!$data['store']){$data['store'][] = 0;}

        $sql .= " AND p2s.store_id IN(" . implode(',', $data['store']) . ") AND pd.language_id = '" . (int)$data['language'] . "' AND p.status = '1'";

        if($data['image']){
          $sql .= " AND p.image != '' AND p.image != 'no_image.jpg' AND p.image != 'no_image.png' AND p.image != 'placeholder.jpg' AND p.image != 'placeholder.png'";
        }
        if($data['seopro']){ //если есть привязка на seopro
          $sql .= " AND p2c.main_category = '1'";
        }

        if(!$data['quantity']){
          $sql .= " AND p.quantity > '0'";
        }

        if($data['custom_sql']){
          $sql .= " " . $this->clearData($data['custom_sql'], 1);
        }

        $sql .= " GROUP BY p.product_id ORDER BY p.product_id ASC LIMIT " . (int)$data['step'] * $data['iteration'] . ", " . $data['step'];

        $product_query = $this->db->query($sql);

        $data['stat']['sql']++;
        $data['product_ids'] = implode(',', array_column($product_query->rows, 'product_id')); //все id товаров в этом заходе для других функций

        return $product_query->rows;
      }
    //exportGetProductsSql

    //ExportCustomXmlAfterSql - функция кастомный код после запроса в базу
      private function ExportCustomXmlAfterSql(&$rows, &$data){
        $feed = $data['feed'];
        $file = DIR_SYSTEM . 'unixml/' . $feed . '/ExportCustomAfter';
        if(is_file($file)){
          include($file);
        }
      }
    //ExportCustomXmlAfterSql

    //exportCheckNoImage - функция проверки фото и если нет фото - удаляем из массива
      private function exportCheckNoImage(&$rows, &$data){
        $data['product_ids'] = array_flip(explode(',', $data['product_ids']));

        foreach($rows as $key => $row){
          if(!is_file(DIR_IMAGE . $row['image'])){ //если нет фото
            if($data['image']){ //если стоит настройка убирать товары без фото
              unset($rows[$key]); //Убираем с выгрузки товары в которых нет фото
              unset($data['product_ids'][$row['product_id']]); //убираем со списка id товаров удаленный товар что бы в последующих запросах не забирать данные
            }else{ //если нет настройки, просто убираем битое фото с фида
              $rows[$key]['image'] = '';
            }
          }
        }

        $data['product_ids'] = implode(',', array_flip($data['product_ids']));
      }
    //exportCheckNoImage

    //exportSetAttributes - функция выборки всех атрибутов товаров и добавления их в массив
      public function exportSetAttributes(&$rows, &$data) {
        $attributes = array();
        $attribute_replaces = array();
        $attributes_name_id = array();
        $data['attribute_group'] = array();

        if(isset($data['attributes_with_group'])){
          $group_query = $this->db->query("SELECT a.attribute_id, agd.name FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON(agd.attribute_group_id = a.attribute_group_id) WHERE agd.language_id = '" . (int)$data['language'] . "'");
          $data['stat']['sql']++;

          foreach($group_query->rows as $agrow){
            $data['attribute_group'][$agrow['attribute_id']] = $agrow['name'];
          }
        }

        //замены из атрибутов
        $attribute_replaces = $this->findInText($this->apText($data), "{{", "/{{(.*?)}}/");

        if($attribute_replaces){
          foreach($attribute_replaces as $attribute_replace){
            $data['from']['{{' . $attribute_replace . '}}'] = '{{' . $attribute_replace . '}}';
            $data['to']['{{' . $attribute_replace . '}}'] = '';
          }
        }

        $attribute_replaces = array_unique($attribute_replaces);

        if(!$data['attribute_status']){ //если стоит выгружать атрибуты

          $sql = "SELECT pa.attribute_id, pa.product_id, pa.text, ua.xml_name as name
                  FROM " . DB_PREFIX . "product_attribute pa
                  INNER JOIN " . DB_PREFIX . "unixml_attributes ua ON (ua.attribute_id = pa.attribute_id)
                  LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
                  WHERE pa.language_id = '" . (int)$data['language'] . "'
                  AND ua.feed = '" . $this->db->escape($data['feed']) . "'
                  AND pa.product_id IN(" . $data['product_ids'] . ")
                  ORDER BY a.sort_order ASC, a.attribute_id ASC";

          $product_attributes = $this->db->query($sql);
          $data['stat']['sql']++;

          if(!$product_attributes->num_rows){ //если нет соответствий
            $sql = "SELECT pa.attribute_id, pa.product_id, pa.text, ad.name
                    FROM " . DB_PREFIX . "product_attribute pa
                    LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (ad.attribute_id = pa.attribute_id AND ad.language_id = pa.language_id)
                    LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
                    WHERE ad.language_id = '" . (int)$data['language'] . "'
                    AND pa.product_id IN(" . $data['product_ids'] . ")
                    ORDER BY a.sort_order ASC, a.attribute_id ASC";

            $product_attributes = $this->db->query($sql);
            $data['stat']['sql']++;
          }

          foreach($product_attributes->rows as $attribute){
            if($attribute['name'] && $attribute['text']){
              $attributes[$attribute['product_id']][$attribute['attribute_id']] = array(
                'name'  => $this->clearData($attribute['name']),
                'text'  => $this->clearData($attribute['text'])
              );
              if(isset($data['attribute_group'][$attribute['attribute_id']])){
                $attributes[$attribute['product_id']][$attribute['attribute_id']]['group'] = $data['attribute_group'][$attribute['attribute_id']];
                $attributes[$attribute['product_id']][$attribute['attribute_id']]['additional'] = ' group="' . $data['attribute_group'][$attribute['attribute_id']] . '"';
              }
              if($attribute_replaces){
                $attributes_name_id[$this->clearData($attribute['name'])] = $attribute['attribute_id'];
              }
            }
          }

          // если есть соответствия в атрибутах то добавляем для замен то чего еще нет в атрибутах
          if($attribute_replaces){
            $attribute_replaces_in_sql = $attribute_replaces;
            if($attributes_name_id){
              $attribute_replaces_in_sql = array_diff($attribute_replaces, array_flip($attributes_name_id)); //берем только то чего нет в атрибутах
            }

            $attributes_for_replace = array();
            $sql = "SELECT pa.attribute_id, pa.product_id, pa.text, ad.name
                    FROM " . DB_PREFIX . "product_attribute pa
                    INNER JOIN " . DB_PREFIX . "attribute_description ad ON (ad.attribute_id = pa.attribute_id AND ad.language_id = pa.language_id)
                    LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
                    WHERE ad.language_id = '" . (int)$data['language'] . "'
                    AND ad.name IN ('" . implode('\',\'', $attribute_replaces_in_sql) . "')
                    AND pa.product_id IN(" . $data['product_ids'] . ")
                    ORDER BY a.sort_order ASC, a.attribute_id ASC";

            $product_attributes = $this->db->query($sql);
            $data['stat']['sql']++;
            foreach($product_attributes->rows as $attribute){
              if($attribute['name'] && $attribute['text']){
                $attributes_for_replace[$attribute['product_id']][$attribute['attribute_id']] = array(
                  'name'  => $this->clearData($attribute['name']),
                  'text'  => $this->clearData($attribute['text'])
                );
                if(isset($data['attribute_group'][$attribute['attribute_id']])){
                  $attributes_for_replace[$attribute['product_id']][$attribute['attribute_id']]['group'] = $data['attribute_group'][$attribute['attribute_id']];
                  $attributes_for_replace[$attribute['product_id']][$attribute['attribute_id']]['additional'] = ' group="' . $data['attribute_group'][$attribute['attribute_id']] . '"';
                }
                $attributes_name_id[$this->clearData($attribute['name'])] = $attribute['attribute_id'];
              }
            }
            $this->exportInsertData($rows, $attributes_for_replace, 'attributes_for_replace');
          }

        }else{ //если атрибуты не выгружаем, но надо проверить есть ли в шаблонах атрибуты

          if($attribute_replaces){
            $sql = "SELECT pa.attribute_id, pa.product_id, pa.text, ad.name
                    FROM " . DB_PREFIX . "product_attribute pa
                    INNER JOIN " . DB_PREFIX . "attribute_description ad ON (ad.attribute_id = pa.attribute_id AND ad.language_id = pa.language_id)
                    LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id)
                    WHERE ad.language_id = '" . (int)$data['language'] . "'
                    AND ad.name IN ('" . implode('\',\'', $attribute_replaces) . "')
                    AND pa.product_id IN(" . $data['product_ids'] . ")
                    ORDER BY a.sort_order ASC, a.attribute_id ASC";

            $product_attributes = $this->db->query($sql);
            $data['stat']['sql']++;
            foreach($product_attributes->rows as $attribute){
              if($attribute['name'] && $attribute['text']){
                $attributes[$attribute['product_id']][$attribute['attribute_id']] = array(
                  'name'  => $this->clearData($attribute['name']),
                  'text'  => $this->clearData($attribute['text'])
                );
                if(isset($data['attribute_group'][$attribute['attribute_id']])){
                  $attributes[$attribute['product_id']][$attribute['attribute_id']]['group'] = $data['attribute_group'][$attribute['attribute_id']];
                  $attributes[$attribute['product_id']][$attribute['attribute_id']]['additional'] = ' group="' . $data['attribute_group'][$attribute['attribute_id']] . '"';
                }
                $attributes_name_id[$this->clearData($attribute['name'])] = $attribute['attribute_id'];
              }
            }
          }

        }

        if($attribute_replaces){
          foreach($attribute_replaces as $attribute_replace){
            if(isset($attributes_name_id[$attribute_replace])){
              $data['to']['{{' . $attribute_replace . '}}'] = $attributes_name_id[$attribute_replace];
            }
          }
        }

        $this->exportInsertData($rows, $attributes, 'attributes');
      }
    //exportSetAttributes

    //exportSetDiscounts - функция выборки оптовых цен
      public function exportSetDiscounts(&$rows, &$data) {
        $prices = array();

        if(in_array($data['feed'], array('prom','tiu','hubber','bon','obyava','deal'))){ //здесь указаны маркетплейсы которым нужны оптовые цены
          $discounts_query = $query = $this->db->query("SELECT product_id, quantity, price FROM " . DB_PREFIX . "product_discount WHERE customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND product_id IN(" . $data['product_ids'] . ") AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");
          $data['stat']['sql']++;
          if($discounts_query->num_rows){
            foreach($discounts_query->rows as $row){
              $prices[$row['product_id']][] = array(
                'price' => $this->markupCalc($row['price'] * $data['currency'], $data),
                'quantity' => $row['quantity']
              );
            }
          }
        }
        $this->exportInsertData($rows, $prices, 'prices');
      }
    //exportSetDiscounts

    //exportSetImages - функция выборки дополнительных фото к товарам
      public function exportSetImages(&$rows, &$data) {
        $images = array();

        if($data['images']){
          $limit = 999;
          if(isset($this->start[$data['feed']]) && is_array($this->start[$data['feed']])){
            $feed_setting = $this->start[$data['feed']];
            if(isset($feed_setting[3])){
              $limit = (int)$feed_setting[3];
            }
          }

          $image_query = $this->db->query("SELECT product_id, image FROM " . DB_PREFIX ."product_image WHERE product_id IN (" . $data['product_ids'] . ") ORDER BY sort_order ASC");
          $data['stat']['sql']++;
          if($image_query->num_rows){
            foreach($image_query->rows as $row){
              if(!is_file(DIR_IMAGE . $row['image'])){ continue; } //fix for empty image
              if(isset($images[$row['product_id']]) && count($images[$row['product_id']]) >= $limit-1){
                continue;
              }
              $images[$row['product_id']][$row['image']] = (string) HTTPS_SERVER . 'image/' . $this->clearData($row['image'], 'image');
            }
          }
        }
        $this->exportInsertData($rows, array_map('array_values', $images), 'images');
      }
    //exportSetImages

    //exportSetMultiplyOption - функция выборки опций для умножения товара на опции
      public function exportSetMultiplyOption(&$rows, &$data) {

        if(!isset($data['special_calc'])){
          $data['special_calc'] = 1;
        }
        if(!isset($data['special_percent'])){
          $data['special_percent'] = 1;
        }
        if(!isset($data['option_ids_pattern'])){
          $data['option_ids_pattern'] = '{option_id}{option_value_id}';
        }
        if(!isset($data['product_option_id_pattern'])){
          $data['product_option_id_pattern'] = '{product_id}{option_ids}';
        }

        if($data['option_multiplier_status'] && isset($data['option_multiplier'])){
          $data['option_multiplier'] = unserialize($data['option_multiplier']);

          $sql_option_id = implode(',', call_user_func_array('array_merge', $data['option_multiplier'])); //забираем все id опций из наборов

          $option_sql = "SELECT
                         ovd.name as value,
                         od.name,
                         pov.price_prefix,
                         pov.price,
                         pov.quantity,
                         pov.option_value_id,
                         pov.product_option_value_id,
                         pov.option_id,
                         pov.product_id
                         FROM " . DB_PREFIX . "option_description od
                         INNER JOIN " . DB_PREFIX . "option_value_description " . "ovd ON (od.option_id = ovd.option_id)
                         INNER JOIN " . DB_PREFIX . "product_option_value pov ON (pov.option_value_id = ovd.option_value_id)
                         WHERE ovd.language_id = '" . (int)$data['language'] . "'
                               AND pov.product_id IN (" . $data['product_ids']  . ")
                               AND od.language_id = '" . (int)$data['language'] . "'";

                         if(!$data['quantity']){ //только то что в наличи
                           $option_sql .= " AND pov.quantity > 0";
                         }
                         $option_sql .= " AND pov.option_id IN (" . $sql_option_id  . ") ORDER BY pov.option_value_id ASC";

          $option_query = $this->db->query($option_sql);
          $data['stat']['sql']++;

          $options = array();
          $option_values = array();
          foreach ($option_query->rows as $option_data) {
            //Опции для товара
            $options[$option_data['product_id']][$option_data['option_id']][$option_data['option_value_id']] = $option_data;
            //Значения опций id = значение
            $option_values[$option_data['option_value_id']] = $option_data['value'];
          }

          $option_rows = array();
          foreach ($rows as $row_key => $row) { //перебор товара
            $variants = array();

            $row['price_original'] = $row['price'];
            $row['special_original'] = $row['special'];
            $row['quantity_original'] = $row['quantity'];
            $row['ratio'] = 1;
            if((float)$row['price_original'] > 0){
              $row['ratio'] = (float)$row['special_original']/(float)$row['price_original'];
            }

            if(isset($data['special_percent_revers']) && (float)$row['price_original'] && (float)$row['special_original']){
              $row['ratio_revers'] = (float)$row['price_original']/(float)$row['special_original'];
            }

            foreach($data['option_multiplier'] as $multiplier_key => $multiplier){ //проходим по наборам
              foreach($multiplier as $option_id){ //проходим по option_id с набора
                if(isset($options[$row['product_id']][$option_id])){ //если есть опция в наборе конкретного товара
                  foreach($options[$row['product_id']][$option_id] as $option_key => $option_data){ //проходим по всем значениям конкретной опции из текущего набора
                    $variants[$multiplier_key][$option_key] = $option_data['value'];
                  }
                }
              } //проходим по option_id с набора
            } //проходим по наборам

            unset($rows[$row_key]); //Удаляем текущий товар из массива товаров и ниже будем добавдять его в массив но уже размноженным

            $all_variants = $this->exportOptionToVariant($variants);

            foreach($all_variants as $variant_key => $variant){
              $row['option_data'] = array();
              $row['option_id'] = false;
              $row['optionset_key'] = false;
              foreach($variant as $variant_item_key => $variant_item_value){
                $variant_item_key_data = explode('.', $variant_item_key);

                $variant_option_set = $variant_item_key_data[0];
                $variant_option_value_id = $variant_item_key_data[1];

                foreach($data['option_multiplier'] as $multiplier_key => $multiplier){ //проходим по наборам
                  foreach($multiplier as $option_id){ //проходим по option_id с набора
                    $row['option_id'] = $option_id;
                    if(isset($options[$row['product_id']][$option_id][$variant_option_value_id])){
                      $row['option_data'][$variant_option_value_id] = $options[$row['product_id']][$option_id][$variant_option_value_id];
                    }
                  }
                }

                $row['optionset_key'] = 'optionset' . $variant_option_set;
                $row['optionset' . $variant_option_set] = $variant_item_value; //в масиив товаров добавляем optionset1

              }

              //product_option_id = product_id+option_id+option_value_id+option_id2+option_value_id2
              $option_ids = '';
              foreach($row['option_data'] as $optionset_id => $option_block){
                // $option_ids .= str_replace(array('{option_id}','{option_value_id}'), array($option_block['option_id'],$option_block['option_value_id']), $data['option_ids_pattern']);
                $option_ids .= str_replace(array('{option_id}','{option_value_id}','{product_option_value_id}'), array($option_block['option_id'],$option_block['option_value_id'],$option_block['product_option_value_id']), $data['option_ids_pattern']);

                $row['quantity'] = $option_block['quantity']; //логика в том что количество берется из последнего набора

                if($option_block['price_prefix'] == '+'){
                  $row['price'] = (float)$row['price_original'] + (float)$option_block['price'];
                  if($data['special_calc'] && (float)$row['special_original']){ //если наценки опций применять и к акциям
                    $row['special'] = (float)$row['special_original'] + (float)$option_block['price'];
                  }
                }
                if($option_block['price_prefix'] == '-'){
                  $row['price'] = $row['price_original'] - $option_block['price'];
                  if($data['special_calc'] && (float)$row['special_original']){ //если наценки опций применять и к акциям
                    $row['special'] = (float)$row['special_original'] - (float)$option_block['price'];
                  }
                }
                if($option_block['price_prefix'] == '='){
                  $row['price'] = $option_block['price'];
                  if($data['special_calc'] && (float)$row['special_original']){ //если наценки опций применять и к акциям
                    $row['special'] = false;
                  }
                }
                if($option_block['price_prefix'] == '*'){
                  $row['price'] = $row['price_original'] * $option_block['price'];
                  if($data['special_calc'] && (float)$row['special_original']){ //если наценки опций применять и к акциям
                    $row['special'] = (float)$row['special_original'] * (float)$option_block['price'];
                  }
                }
                if($option_block['price_prefix'] == '/'){
                  $row['price'] = $row['price_original'] / $option_block['price'];
                  if($data['special_calc'] && (float)$row['special_original']){ //если наценки опций применять и к акциям
                    $row['special'] = (float)$row['special_original'] / (float)$option_block['price'];
                  }
                }
              } //foreach $row['option_data']

              $row['product_option_id'] = str_replace(array('{product_id}','{option_ids}'), array($row['product_id'],$option_ids), $data['product_option_id_pattern']);

              if($data['special_percent'] && (float)$row['price_original']){ //если считаем special от опции в зависимости от процента скидки, по умолчанию да
                $row['special'] = (float)$row['price'] * $row['ratio'];
              }

              if(isset($data['special_percent_revers']) && (float)$row['price_original'] && (float)$row['special']){ //если считаем special от опции в зависимости от процента скидки, по умолчанию да
                $row['price'] = (float)$row['special'] * $row['ratio_revers'];
              }

              $row['group_id'] = $row['product_id'];

              $option_rows[$variant_key . '.' . $row_key] = $row;

            } //foreach $all_variants

          } //перебор товара

          $rows = $option_rows;

          //замены из набора опций
          $option_replaces = $this->findInText($this->apText($data), "[[", "/\[\[(.*?)\]\]/"); //options
          if($option_replaces){
            foreach($option_replaces as $option_replace){
              $data['from']['[[' . $option_replace . ']]'] = '[[' . $option_replace . ']]';
              $data['to']['[[' . $option_replace . ']]'] = $option_replace;
            }
          }

        } // if status

      }
    //exportSetMultiplyOption

    //exportOptionToVariant - функция получения всех вариантов умножений опций
      private function exportOptionToVariant($arrays) {
        $result = array(array());
        foreach ($arrays as $variant_key => $property_values) {
          $tmp = array();
          foreach ($result as $result_item) {
            foreach ($property_values as $property_key => $property_value) {
              $tmp[] = $result_item + array($variant_key . '.' . $property_key => $property_value);
            }
          }
          $result = $tmp;
        }
        return $result;
      }
    //exportOptionToVariant - функция получения всех вариантов умножений опций

    //exportSetArrayReplaces - функция добавления замен с массива товаров
      public function exportSetArrayReplaces(&$data) {
        $array_replaces = $this->findInText(str_replace('{{', '-tmp-', $this->apText($data)), "{", "/{(.*?)}/"); //product array -tmp- - что бы не брало как массив товара опции
        if($array_replaces){
          foreach($array_replaces as $array_replace){
            $data['from']['{' . $array_replace . '}'] = '{' . $array_replace . '}';
            $data['to']['{' . $array_replace . '}'] = $array_replace;
          }
        }
      }
    //exportSetArrayReplaces

    //exportGetAdditionalData - функция для забора кастомных данных для некоторых фидов
      private function exportGetAdditionalData(&$product, &$data){
        if(isset($data['lang_data']['lang_id']) && isset($data['lang_data']['fields'])){
          $description_query = $this->db->query("SELECT " . $this->db->escape($data['lang_data']['fields']) . " FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $product['product_id'] . "' AND language_id = '" . (int)$data['lang_data']['lang_id'] . "'");
          $data['stat']['sql']++;
          $product['langdata'][$data['lang_data']['lang_id']] = $description_query->row;
          $product['langdata'][$data['lang_data']['lang_id']]['attributes'] = array();

          //fix other data to name&desc keys
          $i = 1;
          foreach($product['langdata'][$data['lang_data']['lang_id']] as $ld_key => $ld_value){
            if($i == 1 && $ld_key != 'name'){
              unset($product['langdata'][$data['lang_data']['lang_id']][$ld_key]);
              $product['langdata'][$data['lang_data']['lang_id']]['name'] = $ld_value;
            }
            if($i == 2 && $ld_key != 'description'){
              unset($product['langdata'][$data['lang_data']['lang_id']][$ld_key]);
              $product['langdata'][$data['lang_data']['lang_id']]['description'] = $ld_value;
            }
            $i++;
          }

          if($product['attributes']){
            $pre_data = array();
            $query_attrs = $this->db->query("SELECT attribute_id, language_id, text FROM " . DB_PREFIX . "product_attribute WHERE attribute_id IN(" . implode(',', array_keys($product['attributes'])) . ") AND product_id = '" . (int)$product['product_id'] . "'");
            foreach($query_attrs->rows as $rowa){
              $pre_data[$rowa['attribute_id']][$rowa['language_id']] = $rowa['text'];
            }
            foreach($pre_data as $rowd){
              if(isset($rowd[$data['lang_data']['lang_id']])){
                $product['langdata'][$data['lang_data']['lang_id']]['attributes'][$rowd[$data['language']]] = $rowd[$data['lang_data']['lang_id']];
              }

            }
          }

          if(isset($product['option_data']) && $product['option_data'] && !isset($data['replace_option_id_name'])){
            $data['replace_option_id_name'] = array();
            $option_replace_query = $this->db->query("SELECT option_id, name, option_value_id FROM " . DB_PREFIX . "option_value_description WHERE language_id = " . (int)$data['lang_data']['lang_id']);
            foreach($option_replace_query->rows as $option_row){
              $data['replace_option_id_name'][$option_row['option_id']] [$option_row['option_value_id']] = $option_row['name'];
            }
          }

        }
      }
    //exportGetAdditionalData

    //exportAddUtm - функция приставки дополнительных данных к ссылке
      public function exportAddUtm(&$product, $data) {
        $product['url'] = $this->clearData($this->url->link('product/product', 'product_id=' . $product['product_id']), 'url', $product, $data);

        if($data['utm']){
          if(stristr($product['url'], '&') === FALSE) { //если в товаре ЧПУ
            if(substr($data['utm'], 0, 5) == '&amp;'){ //если первый символ & (без ЧПУ) меняем на ? под ЧПУ
              $data['utm'] = substr_replace($data['utm'], '?', 0, 5);
            }
          }else{ //если в товаре нет ЧПУ
            if(substr($data['utm'], 0, 1) == '?'){ //если первый символ ? (под ЧПУ) меняем на &
              $data['utm'] = substr_replace($data['utm'], '&amp;', 0, 1);
            }
          }
          $product['url'] .= $data['utm'];
        }
      }
    //exportAddUtm

    //exportPriceProduct - функция расчета цены в зависимости от курса
      public function exportPriceProduct(&$product, &$data) {
        //отделяем от цены - даты
        if($product['special']){
          $product['special_start'] = '';
          $product['special_end'] = '';
          $special_data = explode('==', isset($product['special_original'])?$product['special_original']:$product['special']);

          $special_data_row = explode('==', $product['special']);
          $product['special'] = $special_data_row[0];

          if(isset($special_data[1])){
            $product['special_start'] = $special_data[1];
          }
          if(isset($special_data[2])){
            $product['special_end'] = $special_data[2];
          }
        }

        //если цена с другого поля
        if($data['field_price'] && isset($product[$data['field_price']]) && $product[$data['field_price']]){
          $product['price'] = $product[$data['field_price']];
          if(!isset($data['special_save'])){
            $product['special'] = false;
          }
        }

        if(!isset($data['decimal_place'])){ $data['decimal_place'] = 2; }

        $product['price'] = round((float)$product['price'] * (float)$data['currency'], (int)$data['decimal_place']);
        if($product['special']){
          $product['special'] = round((float)$product['special'] * (float)$data['currency'], (int)$data['decimal_place']);
        }

        if(isset($product['currency_id']) && $product['currency_id'] && isset($data['multicurrency'][$product['currency_id']]) && $data['multicurrency'][$product['currency_id']] > 0){
          $product['price'] = round((float)$product['price']/(float)$data['multicurrency'][$product['currency_id']], $data['decimal_place']);
          if($product['special']){
            $product['special'] = round((float)$product['special']/(float)$data['multicurrency'][$product['currency_id']], $data['decimal_place']);
          }
        }
      }
    //exportPriceProduct

    //exportMarkupProduct - функция применения наценок на товар
      public function exportMarkupProduct(&$product, $data) {
        //наценка на категории
        if($data['category_markup'] && isset($data['category_markup'][$product['category_id']])){
          $data['markup'] = $data['category_markup'][$product['category_id']];
        }
        //наценка на категории

        //наценка на группы товаров
        if($data['product_markup'] && isset($data['product_markup'][$product['product_id']])){
          $data['markup'] = $data['product_markup'][$product['product_id']];
        }
        //наценка на группы товаров

        $product['price'] = $this->markupCalc($product['price'], $data);
        if($product['special']){
          $product['special'] = $this->markupCalc($product['special'], $data);
        }
      }
    //exportMarkupProduct

    //exportSetAdditionalParams - функция добавления дополнительных статических параметров
      private function exportSetAdditionalParams(&$product, $data){

        $product['attributes_full'] = array();

        if(!isset($product['attributes_for_replace'])){
          $product['attributes_for_replace'] = array();
        }

        if($product['attributes_for_replace']){
          $product['attributes_for_replace'] = array_replace($product['attributes_for_replace'], $product['attributes']);
        }else{
          $product['attributes_for_replace'] = $product['attributes'];
        }

        if($data['attribute_status']){ //если стоит не выгружать атрибуты - выбранные атрибуты перемещаем в другой массив - чисто для замены
          $product['attributes'] = array();
        }

        $all_additional = array_flip(array_column($data['additional_params'], 'name'));

        //статические параметры из категории
        if($data['category_tag'] && isset($data['category_tag'][$product['category_id']])){
          foreach (explode(PHP_EOL, $data['category_tag'][$product['category_id']]) as $additional_param) {
            $additional_param_data = explode('==', $additional_param);

            if(isset($additional_param_data[0]) && isset($additional_param_data[1])){
              if(isset($all_additional[$additional_param_data[0]])){ //если в доп стат параметрах есть уже такой параметр - берем его ключ и перезаписываем значение
                $data['additional_params'][$all_additional[$additional_param_data[0]]]['text'] = $additional_param_data[1];
              }else{ //если в доп стат парам нет такого то добавляем туда
                $data['additional_params'][] = array(
                  'name' => $additional_param_data[0],
                  'text' => $additional_param_data[1],
                  'decode' => (isset($additional_param_data[2]))?1:0
                );
              }
            }
          }
        }
        //статические параметры из категории

        //статические параметры
        if($data['additional_params']){
          foreach($data['additional_params'] as $param_key => $param_value){
            $param_value['name'] = $this->clearData($param_value['name'], 'additional_params');

            if(substr($param_value['name'], 0, 1) == "<" && substr($param_value['name'], -1) == ">"){ //если это теги
              $name = str_replace(array('<','>'), '', $param_value['name']);
              $end_data = explode(" ", $name);

              $product['attributes_full'][] = array(
                'name' => $name,
                'text' => $param_value['text'],
                'end'  => $end_data[0],
                'decode' => (isset($param_value['decode']))?1:0
              );
            }else{ //если не теги - пишем в атрибуты
              $product['attributes'][] = array(
                'name' => $param_value['name'],
                'text' => $param_value['text']
              );
            }
          }
        }
        //статические параметры

      }
    //exportSetAdditionalParams

    //exportGenerateData - функция генерации данных по шаблону (название, описание и т.п.)
      private function exportGenerateData(&$product, $data){ //data без & - данные to меняются только внутры этой функции! Вне функции это просто указатель на что меняем
        foreach($data['to'] as $to_key => $to_value){ //на что меняем из массива product
          if($to_value && isset($product[$to_value])){ //если есть ключ в массиве - меняем (это по полям с базы)
            $data['to'][$to_key] = $product[$to_value];
          }elseif($to_value && isset($product['attributes_for_replace'][$to_value]['text'])){ //если есть ключ в атрибутах - меняем (это по атрибутам)
            $data['to'][$to_key] = $product['attributes_for_replace'][$to_value]['text'];
          }else{
            $data['to'][$to_key] = '';
          }
        }

        foreach($product['attributes'] as $ak => $av){ //атрибуты
          $product['attributes'][$ak]['text'] = str_replace($data['from'], $data['to'], $product['attributes'][$ak]['text']);
        }

        foreach($product['attributes_full'] as $afk => $afv){ //теги
          $product['attributes_full'][$afk]['text'] = str_replace($data['from'], $data['to'], $product['attributes_full'][$afk]['text']);
        }

        if($data['genname']){ //название
          $product['name'] = str_replace($data['from'], $data['to'], $data['genname']);
          if(isset($product['langdata']) && $product['langdata'] && !isset($data['not_replace_in_multiname'])){
            foreach($product['langdata'] as $langkey => $langdata){
              if(isset($langdata['name']) && $langdata['name']){
                $data_to = $data['to']; //фикс для замены данных с мультиязыка
                foreach(array('((pd.name))','{name}') as $to_item){
                  if(isset($data_to[$to_item])){
                    $data_to[$to_item] = $langdata['name'];
                  }
                }
                $product['langdata'][$langkey]['name'] = str_replace($data['from'], $data_to, $data['genname']);
              }
            }
          }
        }

        if($data['gendesc_mode'] && strip_tags($this->clearData($product['description'], 'description'))){ //только если нет описания и его нет
          $data['gendesc'] = '';
        }

        if($data['gendesc']){ //описание
          $product['description'] = str_replace($data['from'], $data['to'], $data['gendesc']);
          if(isset($product['langdata']) && $product['langdata'] && !isset($data['not_replace_in_multiname'])){
            foreach($product['langdata'] as $langkey => $langdata){
              if(isset($langdata['description']) && $langdata['description']){
                $data_to = $data['to']; //фикс для замены данных с мультиязыка
                foreach(array('((pd.description))','{description}') as $to_item){
                  if(isset($data_to[$to_item])){
                    $data_to[$to_item] = $langdata['description'];
                  }
                  $product['langdata'][$langkey]['description'] = str_replace($data['from'], $data_to, $data['gendesc']);
                }
              }
            }
          }
        }

        if($product['url']){ //ссылка
          $product['url'] = str_replace($data['from'], $data['to'], $product['url']);
        }

        if($product['quantity'] < 0){ //фикс минусовых остатков
          $product['quantity'] = 0;
        }

        $product['attributes_for_replace'] = array();
      }
    //exportGenerateData

    //exportReplaceProductData - функция замен из списка замен
      public function exportReplaceProductData(&$product, $data){
        //if($data['feed'] == 'epicentr' || $data['feed'] == 'rozetka'){
          $product['manufacturer_code'] = ''; //для эпицентра задаем нужные данные
          foreach($product['attributes'] as $ak => $av){
            if(!isset($product['attributes'][$ak]['additional'])){
              $product['attributes'][$ak]['additional'] = '';
            }
          }
        //}

        $product['description'] = $this->clearData($product['description'], 'description', $product, $data);

        if(isset($product['langdata']) && $product['langdata']){
          foreach($product['langdata'] as $langkey => $langdata){
            if(isset($langdata['description']) && $langdata['description']){
              $product['langdata'][$langkey]['description'] = $this->clearData($langdata['description'], 'description', $product, $data);
            }
          }
        }

        if($product['image']){
          $product['image'] = (string) HTTPS_SERVER . 'image/' . $this->clearData($product['image'], 'image');
        }

        if($data['replace_list']){
          $this->exportReplaceDataItem($product, $data, 1, 'name'); // 1 - В названии товара
          $this->exportReplaceDataItem($product, $data, 2, 'model'); // 2 - В модели товара
          $this->exportReplaceDataItem($product, $data, 3, 'manufacturer'); // 3 - В производителе товара
          $this->exportReplaceDataItem($product, $data, 4, 'description'); // 4 - В описании товара
          $this->exportReplaceDataItem($product, $data, 5, 'url'); // 5 - В ссылке товара
          $this->exportReplaceDataItem($product, $data, 6, 'image'); // 6 - В фото товара

          if($product['images'] && isset($data['replace_list'][6]) && $data['replace_list'][6]){ // 6 - В дополнительных фото товара
            foreach($product['images'] as $ik => $iv){
              $product['images'][$ik] = str_replace(array_column($data['replace_list'][6], 'from'), array_column($data['replace_list'][6], 'to'), $product['images'][$ik]);
            }
          }

          if($product['attributes'] && isset($data['replace_list'][7]) && $data['replace_list'][7]){ // 7 - В названии атрибутов товара
            $aname_from = array_column($data['replace_list'][7], 'from');
            $aname_to = array_column($data['replace_list'][7], 'to');

            foreach($product['attributes'] as $ak => $av){
              $original_name = $product['attributes'][$ak]['name'];

              if(in_array($product['category'] . ' &gt; ' . $original_name, $aname_from)){ //замена с учетом категории
                foreach($aname_from as $aname_key => $aname_value){
                  if($aname_value == $product['category'] . ' &gt; ' . $original_name){
                    $product['attributes'][$ak]['name'] = str_replace($aname_value, $aname_to[$aname_key], $product['category'] . ' &gt; ' . $original_name);
                  }
                }
              }else{
                $product['attributes'][$ak]['name'] = str_replace($aname_from, $aname_to, $product['attributes'][$ak]['name']);
              }

              if($data['feed'] == 'epicentr' || $data['feed'] == 'rozetka'){
                $param_data = explode(' - ', $product['attributes'][$ak]['name']);

                if(isset($param_data[1]) && trim($param_data[0])){
                  $product['attributes'][$ak]['name'] = trim($param_data[1]);
                  $product['attributes'][$ak]['additional'] = '  paramcode="' . trim($param_data[0]) . '"';
                }
              }
            }
          }

          if($product['attributes'] && isset($data['replace_list'][8]) && $data['replace_list'][8]){ // 8 - В значении атрибутов товара

            $value_from = array_column($data['replace_list'][8], 'from');
            $value_to = array_column($data['replace_list'][8], 'to');
            $name_unic = '***###***';

            $replace_with_attr_from = array();
            $replace_with_attr_to = array();
            foreach($value_from as $value_from_key => $value_from_item){
              if(strpos($value_from_item, ' &gt; ') !== false){
                $replace_with_attr_from[] = $value_from_item;
                $replace_with_attr_to[] = $value_to[$value_from_key];
              }
            }

            $attribute_strong = false;
            if(isset($data['attribute_strong'])){ //если стоит настройка для точного вхождения
              $attribute_strong = true;
              foreach($value_from as $value_from_key => $value_from_value){
                $value_from[$value_from_key] = $name_unic . $value_from_value . $name_unic; //уникализируем замену
              }
            }

            foreach($product['attributes'] as $ak => $av){
              $product['attributes'][$ak]['original'] = $product['attributes'][$ak]['name'];
              $product['attributes'][$ak]['original_text'] = $product['attributes'][$ak]['text'];

              if($attribute_strong && in_array($name_unic . $product['attributes'][$ak]['text'] . $name_unic, $value_from)){ //если стоит настройка для точного вхождения и этот атрибут есть в массиве замен
                $product['attributes'][$ak]['text'] = $name_unic . $product['attributes'][$ak]['text'] . $name_unic;
              }

              $product['attributes'][$ak]['text'] = str_replace($value_from, $value_to, $product['attributes'][$ak]['text']);

              //уточненная замена значений с учетом атрибута
                if($replace_with_attr_from){
                  if($attribute_strong){ //если строгая замена
                    if(in_array($name_unic . $product['attributes'][$ak]['original'] . ' &gt; ' . $product['attributes'][$ak]['text'] . $name_unic, $value_from)){ //если стоит настройка для точного вхождения и этот атрибут есть в массиве замен
                      $product['attributes'][$ak]['text'] = str_replace($value_from, $value_to, $name_unic . $product['attributes'][$ak]['original'] . ' &gt; ' . $product['attributes'][$ak]['text'] . $name_unic);
                    }
                  }else{
                    $product['attributes'][$ak]['text'] = str_replace($replace_with_attr_from, $replace_with_attr_to, $product['attributes'][$ak]['original'] . ' &gt; ' . $product['attributes'][$ak]['text']);
                    $product['attributes'][$ak]['text'] = str_replace($product['attributes'][$ak]['original'] . ' &gt; ', '', $product['attributes'][$ak]['text']);
                  }
                }
              //уточненная замена значений с учетом атрибута

              //замена с учетом категории
                if($replace_with_attr_from){
                  if($attribute_strong){ //если строгая замена
                    if(in_array($name_unic . $product['category'] . ' &gt; ' . $product['attributes'][$ak]['text'] . $name_unic, $value_from)){ //если стоит настройка для точного вхождения и этот атрибут есть в массиве замен
                      $product['attributes'][$ak]['text'] = str_replace($value_from, $value_to, $name_unic . $product['category'] . ' &gt; ' . $product['attributes'][$ak]['original_text'] . $name_unic);
                    }
                  }else{
                    if(in_array($product['category'] . ' &gt; ' . $product['attributes'][$ak]['original_text'], $value_from)){
                      $product['attributes'][$ak]['text'] = str_replace($replace_with_attr_from, $replace_with_attr_to, $product['category'] . ' &gt; ' . $product['attributes'][$ak]['original_text']);
                      $product['attributes'][$ak]['text'] = str_replace($product['category'] . ' &gt; ', '', $product['attributes'][$ak]['text']);
                    }
                  }
                }
              //замена с учетом категории

              if($data['feed'] == 'epicentr' || $data['feed'] == 'rozetka'){
                $value_data = explode(' - ', $product['attributes'][$ak]['text']);
                if(isset($value_data[1]) && trim($value_data[0])){
                  $product['attributes'][$ak]['text'] = trim($value_data[1]);
                  $product['attributes'][$ak]['additional'] = $product['attributes'][$ak]['additional'] . '  valuecode="' . trim($value_data[0]) . '"';
                }
              }
            }
          }
        }
      }
    //exportReplaceProductData

    //exportReplaceDataItem - функция замены конкретного элемента товара
      //$this->exportReplaceDataItem($product, $data, 3, 'manufacturer'); // 3 - В производителе товара
      public function exportReplaceDataItem(&$product, $data, $key, $field){
        if(isset($data['replace_list'][$key]) && $data['replace_list'][$key]){
          $product[$field] = str_replace(array_column($data['replace_list'][$key], 'from'), array_column($data['replace_list'][$key], 'to'), $product[$field]);
        }
        if($key == 3){ //если бренд - проставляем id бренда для эпицентра
          $manufacturer_data = explode(' - ', $product[$field]);
          if(isset($manufacturer_data[1]) && trim($manufacturer_data[0])){
            $product['manufacturer_code'] = trim($manufacturer_data[0]);
            $product[$field] = trim($manufacturer_data[1]);
          }
        }
      }
    //exportReplaceDataItem

    //exportChangeOfferId - функция замены id товара
      private function exportChangeOfferId(&$product, $data){
        $product['product_original_id'] = $product['product_id'];

        //если id с другого поля
        if($data['field_id'] && isset($product[$data['field_id']]) && $product[$data['field_id']]){
          $product['product_id'] = $product[$data['field_id']];
        }

        //если с опций есть id - заменяем
        if(isset($product['product_option_id'])){
          $product['product_id'] = $product['product_option_id'];
        }
      }
    //exportChangeOfferId

    //ExportCustomXml - функция кастомный код в итерации цикла товара
      private function ExportCustomXml(&$product, &$data){
        $feed = $data['feed'];
        $file = DIR_SYSTEM . 'unixml/' . $feed . '/ExportCustomXml';
        if(is_file($file)){
          include($file);
        }
      }
    //ExportCustomXml

    //exportCheckDoubleImage - функция проверки на дубли в фото
      private function exportCheckDoubleImage(&$product, $data){
        $from = array('%2F', '+', '%3A');
        $to = array('/', '%20', ':');
        if($product['images']){
          foreach($product['images'] as $ik => $iv){
            $product['images'][$ik] = str_replace($from, $to, urlencode($product['images'][$ik]));
            if($product['image'] == $iv){
              unset($product['images'][$ik]);
            }
          }
        }

        $product['image'] = str_replace($from, $to, urlencode($product['image']));
      }
    //exportCheckDoubleImage

    //exportCheckStock - функция проверки наличия
      private function exportCheckStock(&$product, $data){
        $product['stock'] = true; //по умолчанию товары в наличии
        if($data['quantity'] && $data['stock']){ //если выгружаем даже то что не в наличии
          if($data['stock'] == $product['stock_status_id']){ //если у товара статус то что в настройках в наличии
            $product['stock'] = true;
          }else{ //если у товара другой статус
            $product['stock'] = false;
          }
        }
        if($product['quantity']){
          $product['stock'] = true;
        }
      }
    //exportCheckStock

    //ExportCustomFinal - функция кастомный код после прохождения цикла товаров
      private function ExportCustomFinal(&$products, &$data){
        $feed = $data['feed'];
        $file = DIR_SYSTEM . 'unixml/' . $feed . '/ExportCustomFinal';
        if(is_file($file)){
          include($file);
        }

        //for opencart 3x
        $this->config->set('config_language_id', $data['system_language']);
        //for opencart 3x
      }
    //ExportCustomFinal

    //exportSetStat - функция фиксирования статистики
      public function exportSetStat($rows, &$data){
        $data['stat']['products'] += count($rows);
        $data['stat']['iteration']++;
        $current_memory = round(((memory_get_usage(1))/1024/1024), 3);
        if($current_memory > $data['stat']['memory']){
          $data['stat']['memory'] = $current_memory;
        }
      }
    //exportSetStat

    //exportInsertData - функция вставки данных в массив товаров
      public function exportInsertData(&$rows, $insert, $name) {
        foreach ($rows as $key => $product) {
          $rows[$key][$name] = array();
          if (isset($insert[$product['product_id']])) {
            $rows[$key][$name] = $insert[$product['product_id']];
          }
        }
      }
    //exportInsertData

    //clearData - функция чистки текста и декодирования
      public function clearData($text = '', $datatype = false, $product = array(), $data = array()) {

        if(!isset($data['allow_tags'])){
          $data['allow_tags'] = "<h1><h2><h3><h4><h5><h6><p><br><table><thead><tbody><tr><td><th><ul><ol><li><strong><b><span><i></em><blockquote><a><img><hr><iframe>";
        }

        if($datatype){
          $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

          if($datatype == 'description' && isset($data['clear_desc'])){
            $text = str_replace(PHP_EOL, " ", $text);
            $text = str_replace("><", "> <", $text);
            if(!$data['clear_desc']){ //чистить описание от спецсимволов и html тегов
              $text = strip_tags($text);
            }
            if($data['clear_desc'] == 2){ //чистить описание только от стилей и лишних тегов. Базовые теги оставляем
              $text = str_replace("><", "> <", $text);
              $text = strip_tags($text, $data['allow_tags']);
            }
          }

          if($datatype == 'image'){
            $text = str_replace('&', '&amp;', $text);
          }

          if($datatype == 'url'){
            $text = str_replace(array(' ','&','"',"'",'<','>'), array('%20','&amp;','&quot;','&apos;','&lt;','&gt;'), $text);
          }

        }

        $text = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $text);
        $text = str_replace(array('&#39;','&#039;','&amp;#39;','&amp;#039;'), "'", $text);

        return trim($text);
      }
    //clearData

    //exportToXml - функция вывода готового XML
      public $final_xml = '';
      public function exportToXml($data, $xml, $flag = false, $xml_type = false) {

        if($xml){
          $this->exportToLog($data, $flag);
          $this->finalClearFormat($xml);

          if(!isset($this->request->get['cron'])){ //отдаем в потоке
            if($flag == "start"){
              $this->final_xml = $xml;
            }else{
              $this->final_xml .= $xml; // добавляем
            }

            if($flag == "finish"){
              //if(!$xml_type){
                $xml_type = 'xml';
              //}

              $this->response->addHeader('Last-Modified: ' . gmdate ("D, d M Y H:i:s") . 'GMT');
              $this->response->addHeader('Content-Type: application/' . $xml_type);
              $this->response->setOutput($this->final_xml);
            }else{ //если это старт или в процессе - отдаем обратно готовый XML
              return $this->final_xml;
            }

          }else{ //write to file
            $mode = ($flag == "start")?'w':'a';
            if($flag == "start"){
              echo '<div style="' . $this->style . '">';
              echo '<h3>' . str_replace('{{FEED}}', $data['feed'], $this->text('text_feed_create_success')) . '</h3>';
            }

            $directory_xml = str_replace('system/', 'price', DIR_SYSTEM);

            if (!file_exists($directory_xml)) { //если нет директории создаем
              mkdir($directory_xml, 0777, true);
            }

            if($data['xml_name']){
              $data['xml_name'] = trim($data['xml_name'], '/');
              $xml_name_path = explode('/', $data['xml_name']);
              array_pop($xml_name_path);
              $xml_name_path = implode('/', $xml_name_path);
              $xml_name_path = $directory_xml . '/' . $xml_name_path;
              if (!file_exists($xml_name_path)) { //если нет директории создаем
                mkdir($xml_name_path, 0777, true);
              }
            }

            $filename = $data['xml_name']?$data['xml_name']:$data['feed'];

            $xml_file = fopen($directory_xml . '/' . $filename . '.tmp', $mode);
            fwrite($xml_file, $xml);
            fclose($xml_file);

            if($flag == "finish"){
              if(is_file($directory_xml . '/' . $filename . '.xml')){
                rename($directory_xml . '/' . $filename . '.xml', $directory_xml . '/' . $filename . '.xml_old');
              }
              rename($directory_xml . '/' . $filename . '.tmp', $directory_xml . '/' . $filename . '.xml');

              $filelink = HTTP_SERVER . $this->pricedir . '/' . $filename . '.xml';
              echo $this->text('text_stat_exp_time') . ' <b>' . round(microtime(true) - $data['stat']['time_start'], 3) . '</b><br>';
              echo $this->text('text_stat_exp_mem_before') . ' <b>' . $data['stat']['memory_start'] . ' Mb.</b><br>';
              echo $this->text('text_stat_exp_mem_process') . ' <b>' . $data['stat']['memory'] . ' Mb.</b><br>';
              echo $this->text('text_stat_exp_all_xml') . ' <b>' . $data['stat']['products'] . '</b><br>';
              echo $this->text('text_stat_exp_all_iter') . ' <b>' . $data['stat']['iteration'] . '</b><br>';
              echo $this->text('text_stat_exp_all_sql') . ' <b>' . $data['stat']['sql'] . '</b><br>';
              echo $this->text('text_stat_exp_file') . ' <a style="color:#555;" href="' . $filelink . '" title="' . $this->text('text_open_in_new_win') . '" target="_blank">' . $filelink . '</a><br>';
              echo $this->text('text_stat_exp_file_size') . ' <b>' . $this->filesize(str_replace('system/', $this->pricedir . '/', DIR_SYSTEM) . $filename . '.xml') . '</b><br>';
              echo '<hr>';
              echo $this->text('text_support');
              echo '</div>';
            }
          }
        }
      }
    //exportToXml

    //finalClearFormat - функция финальной очистки и форматирования XML
      private function finalClearFormat(&$xml){ //&nbsp;
        $xml = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u',' ',$xml);
        $xml = str_replace('></', '>==</', $xml);
        $xml = str_replace('><', '>' . PHP_EOL . '<', $xml);
        $xml = str_replace('>==</', '></', $xml);
        $xml = str_replace('&nbsp;', ' ', $xml);
      }
    //finalClearFormat

  //Функции экспорта - экспорт

  //Функции вспомогательные

    //getSeopro - функция проверки наличия seopro в магазине
      public function getSeopro(){
        $seopro_exist = false;
        $seopro_query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "product_to_category LIKE 'main_category'");

        return $seopro_query->num_rows;
      }
    //getSeopro

    //markupCalc - функция калькуляции наценки
      public function markupCalc($price, $data){
        $markup = $data['markup'];

        if(!$price && !$markup){
          return $price;
        }

        if(!isset($data['decimal_place'])){ $data['decimal_place'] = 2; }
        $markup = str_replace(',', '.', trim($markup));
        $number = 0;

        if(substr($markup, -1) == '%'){
          if(substr($markup, 0, 1) == '+'){
            $number += $price * str_replace(array('%',' ','+'), '', $markup) / 100;
          }elseif(substr($markup, 0, 1) == '-'){
            $number -= $price * str_replace(array('%',' ','-'), '', $markup) / 100;
          }else{
            $number += $price * str_replace(array('%',' ','+'), '', $markup) / 100;
          }

          $calc_data = $price + round($number, 2);

        }elseif(substr($markup, 0, 1) == '*'){
          $calc_data = round($price * str_replace('*', '', $markup), 2);

        }elseif(substr($markup, 0, 1) == '/'){
          $calc_data = round($price / str_replace('/', '', $markup), 2);

        }else{
          if(substr($markup, 0, 1) == '+'){
            $number += (float)str_replace('+', '', $markup);
          }else{
            $number += (float)$markup;
          }

          $calc_data = $price + round($number, 2);
        }


        return $calc_data;
      }
    //markupCalc

    //varname - функция получения имени переменной для конкретного фида
      public function varname($var, $feed = false) {
        return 'unixml' . ($feed?('_' . $feed):'') . '_' . $var;
      }
    //varname

    //apText - функция выборки текста из доп статических параметров, параметров/тегов категорий, шаблонов генераций (названия, описания, ссылки)
      private function apText($data){
        $ap_text = $data['genname'] . $data['gendesc'] . $data['utm'];
        $ap_text .= implode(' ', array_column($data['additional_params'], 'text')); //передаем весь текст из дополнительных статических параметров
        $ap_text .= implode(' ', $data['category_tag']); //статические параметры из категории

        return $ap_text;
      }
    //apText

    //checkTables - функция проверка таблиц для модуля
      public function checkTables(){

        //lang
          $lang_dir = $this->config->get('config_language');
    			$query = $this->db->query("SELECT directory FROM " . DB_PREFIX . "language WHERE code = '" . $this->db->escape($this->config->get('config_language')) . "'");
    			if($query->row['directory']){
    				$lang_dir = $query->row['directory'];
    			}

          $lang_filename = DIR_LANGUAGE . $lang_dir . '/' . $this->path . '.php';
    			$lang_filepath = str_replace('/unixml.php', '', $lang_filename);
    			$lang_filedata = file_get_contents(DIR_LANGUAGE . 'en-gb/extension/feed/unixml.php');

    			if(is_file($lang_filename)){
    				unlink($lang_filename);
    			}

    			if(!is_dir($lang_filepath)){
    				mkdir($lang_filepath, 0777, true);
    			}

    			$lang_file = fopen($lang_filename, 'w');
    			fwrite($lang_file, $lang_filedata);
    			fclose($lang_file);
        //lang

        //feed_unixml_status
          $key = 'unixml';
          if(substr(VERSION, 0, 1) == 3){ //oc3x
            $key = 'feed_' . $key;
          }
          $query = $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `key` = '" . $key . "_status'");
          $query = $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = 0, `code` = '" . $key . "', `key` = '" . $key . "_status', `value` = '1'");
        //feed_unixml_status

        //additional_params
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_additional_params` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `feed` varchar(64) NOT NULL,
                `param_name` varchar(255) NOT NULL,
                `param_text` varchar(2000) NOT NULL,
                PRIMARY KEY (`item_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //additional_params

        //attributes
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_attributes` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `feed` varchar(64) NOT NULL,
                `attribute_id` int(11) NOT NULL,
                `xml_name` varchar(2000) NOT NULL,
                PRIMARY KEY (`item_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //attributes

        //category_match
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_category_match` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `feed` varchar(64) NOT NULL,
                `category_id` int(11) NOT NULL,
                `xml_name` varchar(1024) NOT NULL,
                `markup` varchar(64) NOT NULL,
                `custom` varchar(6000) NOT NULL,
                PRIMARY KEY (`item_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //category_match

        //import_image
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_import_image` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `image` varchar(4000) NOT NULL,
                `oc` varchar(4000) NOT NULL,
                `main_image` int(1) NOT NULL,
                PRIMARY KEY (`item_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //import_image

        //product_markup
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_product_markup` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `feed` varchar(64) NOT NULL,
                `name` varchar(255) NOT NULL,
                `products` LONGTEXT NOT NULL,
                `markup` varchar(255) NOT NULL,
                PRIMARY KEY (`item_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //product_markup

        //replace_name
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_replace_name` (
                `item_id` int(11) NOT NULL AUTO_INCREMENT,
                `feed` varchar(64) NOT NULL,
                `name_from` varchar(255) NOT NULL,
                `name_to` varchar(255) NOT NULL,
                `replace_where` varchar(64) NOT NULL,
                PRIMARY KEY (`item_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //replace_name

        //setting
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_setting` (
                `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(64) NOT NULL,
                `name` varchar(255) NOT NULL,
                `value` TEXT NOT NULL,
                `date_edit` datetime NOT NULL,
                PRIMARY KEY (`setting_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //setting

        //import manufacturer
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_import_manufacturer` (
                `import_id` int(11) NOT NULL,
                `xml` varchar(4000) NOT NULL,
                `oc` int(11) NOT NULL,
                `markup` varchar(16) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //import manufacturer

        //import category
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_import_category` (
                `import_id` int(11) NOT NULL,
                `xml` varchar(4000) NOT NULL,
                `oc` int(11) NOT NULL,
                `markup` varchar(16) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //import category

        //import attribute
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "unixml_import_attribute` (
                `import_id` int(11) NOT NULL,
                `xml` varchar(4000) NOT NULL,
                `oc` int(11) NOT NULL,
                `markup` varchar(16) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
        //import attribute

        //Фикс для перехода с 5 версии если нет полей
        $query_replace = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "unixml_replace_name` WHERE `Field` = 'replace_where'");
        if(!$query_replace->num_rows){
          $this->db->query("ALTER TABLE `" . DB_PREFIX . "unixml_replace_name` ADD `replace_where` VARCHAR(64) NOT NULL AFTER `name_to`");
        }
        //Фикс для перехода с 5 версии если нет полей

        //Фикс для перехода с 5 версии если нет полей
        $query_replace = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "unixml_import_image` WHERE `Field` = 'oc'");
        if(!$query_replace->num_rows){
          $this->db->query("ALTER TABLE `" . DB_PREFIX . "unixml_import_image` ADD `oc` VARCHAR(4000) NOT NULL AFTER `image`");
        }
        //Фикс для перехода с 5 версии если нет полей

        //Фикс для добавления в product нужных полей
        $query_replace = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product` WHERE `Field` = 'unixml_link'");
        if(!$query_replace->num_rows){
          $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `unixml_link` TEXT(128) NOT NULL AFTER `date_modified`");
        }
        $query_replace = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product` WHERE `Field` = 'unixml_feed'");
        if(!$query_replace->num_rows){
          $this->db->query("ALTER TABLE `" . DB_PREFIX . "product` ADD `unixml_feed` VARCHAR(32) NOT NULL AFTER `date_modified`");
        }
        //Фикс для добавления в product нужных полей

        //фикс для увеличения поля настроек
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "unixml_setting` CHANGE `value` `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        //фикс для увеличения поля настроек

        //добавления фидов в базу со старта
        $db_feeds_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "unixml_setting` WHERE `code` = 'export.feeds'");
        if(!$db_feeds_query->num_rows){ //если нет фидов - добавим стартовые в базу
          foreach ($this->start as $name => $data_feed) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "unixml_setting` SET `code` = 'export.feeds', `name` = '" . $this->db->escape($name) . "', `value` = '" . $this->db->escape(serialize($data_feed)) . "', `date_edit` = NOW()");
          }
        }
        //добавления фидов в базу

        //фикс для перехода с 7 бета - переносим из таблицы в таблицу product
        // $table_query = $this->db->query("SHOW TABLES FROM '" . DB_DATABASE . "' LIKE '" . DB_PREFIX . "unixml_import_product'");
        // if($table_query->num_rows){ //если есть таблица - переносим данные
        //   $import_product_query = $this->db->query("SELECT import_id, product_id FROM " . DB_PREFIX . "unixml_import_product");
        //   if($import_product_query->num_rows){
        //     foreach($import_product_query->rows as $row){
        //       $this->db->query("UPDATE " . DB_PREFIX . "product SET unixml_feed = '" . $row['import_id'] . "' WHERE product_id = '" . (int)$row['product_id'] . "'");
        //     }
        //   }
        //   $this->db->query("DROP TABLE " . DB_PREFIX . "unixml_import_product"); //удаляем таблицу
        // }
        //фикс для перехода с 7 бета - переносим из таблицы в таблицу product

      }
    //checkTables

    //getSettingFields - функция всех полей настроек
      public function getSettingFields(){
        $fields = array();

        $fields['1. ' . $this->text('text_export_1')] = array(
          'status' => $this->text('text_export_1_1'),
          'name' => $this->text('text_export_1_2'),
          'language' => $this->text('text_export_1_3'),
          'currency' => $this->text('text_export_1_4'),
          'delivery_cost' => $this->text('text_export_1_5'),
          'delivery_time' => $this->text('text_export_1_6'),
          'delivery_jump' => $this->text('text_export_1_7'),
        );

        $fields['2. ' . $this->text('text_export_2')] = array(
          'products' => $this->text('text_export_2_1'),
          'products_mode' => '',
          'categories' => $this->text('text_export_2_2'),
          'manufacturers' => $this->text('text_export_2_3'),
          'andor' => $this->text('text_export_2_4'),
          'seopro' => $this->text('text_export_2_5'),
          'quantity' => $this->text('text_export_2_6'),
          'image' => $this->text('text_export_2_7'),
        );

        $fields['3. ' . $this->text('text_export_3')] = array(
          'markup' => $this->text('text_export_3_1'),
          'option_multiplier_status' => $this->text('text_export_3_2'),
          'genname' => $this->text('text_export_3_3'),
          'gendesc' => $this->text('text_export_3_4'),
          'gendesc_mode' => $this->text('text_export_3_5'),
          'clear_desc' => $this->text('text_export_3_6'),
          'category_match' => $this->text('text_export_3_7'),
          'attribute_status' => $this->text('text_export_3_8'),
          'product_markup' => $this->text('text_export_3_9'),
          'replace_name' => $this->text('text_export_3_10'),
          'images' => $this->text('text_export_3_11'),
          'additional_params' => $this->text('text_export_3_12'),
          'utm' => $this->text('text_export_3_13'),
          'stock' => '',
        );

        $fields['4. ' . $this->text('text_export_4')] = array(
          'custom_sql' => $this->text('text_export_4_1'),
          'custom_xml_after_sql' => $this->text('text_export_4_2'),
          'custom_xml' => $this->text('text_export_4_3'),
          'custom_xml_final' => $this->text('text_export_4_4'),
        );

        $fields['5. ' . $this->text('text_export_5')] = array(
          'fields' => $this->text('text_export_5_1'),
          'field_id' => $this->text('text_export_5_2'),
          'field_price' => $this->text('text_export_5_3'),
          'step' => $this->text('text_export_5_4'),
          'log' => $this->text('text_export_5_5'),
          'secret' => $this->text('text_export_5_6'),
          'xml_name' => $this->text('text_export_5_7'),
          'store' => $this->text('text_export_5_7'),
        );

        $fields['6. ' . $this->text('text_export_6')] = array(
          'xml_link' => $this->text('text_export_6_1'),
          'manuals' => $this->text('text_export_6_2'),
        );

        return $fields;
      }
    //getSettingFields

    //getSettingFieldSearch функция поисковые слова для пунктов
      public function getSettingFieldSearch($field){
        $fields = array(
          'status' => 'статус выгрузки, status cnfnec dsuheprb',
          'name' => 'название компании name yfpdfybt rjvgfybb',
          'language' => 'язык выгрузки language zpsr dsuheprb',
          'currency' => 'валюта выгрузки currency dfk.nf dsuheprb',
          'delivery_cost' => 'стоимость доставки cost cnjbvjcnm ljcnfdrb',
          'delivery_time' => 'сроки доставки в днях chjr ljcnfdrb d lyz[]',
          'delivery_jump' => 'час перескока xfc gthtcrjrf',
          'products' => 'товары products njdfhs',
          'categories' => 'категории categories rfntujhbb',
          'manufacturers' => 'бренды manufacturers ,htyls',
          'andor' => 'логика выгрузки andor kjubrf',
          'seopro' => 'привязка к главной категории seopro maincategory ukfdyfz rfntujhbz',
          'currency' => 'валюта выгрузки currency dfk.nf',
          'quantity' => 'привязка к количеству quantity rjkbxtcndj',
          'image' => 'привязка к фото photo ajnj',
          'markup' => 'наценка на товар markup yfwtyrf',
          'option_multiplier_status' => 'умножать товар на опцию option multiply evyj;fnm njdfh yf jgwb.',
          'genname' => 'генерация названий name generation utythfwbz yfpdfybq',
          'gendesc' => 'генерация описаний description generation utythfwbz jgbcfybq',
          'gendesc_mode' => 'режим генерации deneration mode ht;bv utythfwbb',
          'clear_desc' => 'очистка описаний clear description jxbcnrf jgbcfybq',
          'category_match' => 'категории category rfntujhbb',
          'attribute_status' => 'атрибуты attributes fnhb,ens',
          'product_markup' => 'наценка на группы товара markup yfwtyrf yf uheggs njdfhf',
          'replace_name' => 'что на что меняем replace xnj yf xnj vtyztv pfvtys',
          'images' => 'выгрузка дополнительных фото images ajnj ljgjkybntkmyts',
          'additional_params' => 'дополнительные параметры additional params ljgjkybntkmyst gfhfvtnhs',
          'utm' => 'приставка к ссылке utm ghbcnfdrf r ccskrt',
        );

        return isset($fields[$field])?$fields[$field]:'';
      }
    //getSettingFieldSearch

    //все выгрузки со старта модуля (поля 1-5,1-6,1-7 - скрыты по умолчанию)
    //array('Название', 'плюс поля', 'минус поля', 'лимиты на фото - можно не указывать')
      public $start = array(
        'rozetka' => array('Розетка маркетплейс', '', '3-13', 15),
        'prom' => array('Пром - prom.ua', '', '', ),
        'hotline' => array('Хотлайн - hotline.ua', '', ''),
        'epicentr' => array('Епіцентр - epicentrk.ua', '', ''),
        'google' => array('Google Merchant Center', '1-5', '', ),
        'facebook' => array('Facebook', '', '1-2', ),
        'allo' => array('Алло - allo.ua', '', '1-2,3-13', 12),
        'price' => array('Прайс.юа - price.ua', '', ''),
        'allbiz' => array('Каталог all.biz', '', '1-2'),
        'synthetic' => array('synthetic - synthetic.ua', '', '', 10),
        'nadavi' => array('Nadavi - nadavi.net', '', '3-8'),
        'pinterest' => array('pinterest - pinterest.com', '', '3-8'),
        'hubber' => array('HUBBER - hubber.pro', '', ''),
        'bigl' => array('BIGL - bigl.ua', '', '', ),
        'ekatalog' => array('Ekatalog - ek.ua', '', '3-8'),
        'fotos' => array('Фотос - f.ua', '', '1-2'),
        'bon' => array('bon.ua (ex Безплатка)', '', ''),
        'mobilluck' => array('MOBILLUCK - mobilluck.ua', '', ''),
        'skidochnik' => array('SKIDOCHNIK - skidochnik.com.ua', '', ''),
        'obyava' => array('obyava.ua', '', '1-2'),
        'pn' => array('Всі ціни - pn.com.ua, vse.ua', '', ''),
        'vcene' => array('vcene.com', '', '', 10),
        'chia' => array('chia.ua', '', '', 10),
        'googlerss' => array('Google RSS 2.0', '', '', ),
        'aliexpress' => array('Aliexpress - aliexpress.com', '', '1-2,3-13', 10),
        'distributions' => array('Іграшки опт - distributions.com.ua', '', ''),
        'skidkaua' => array('skidka.ua', '', '', 10),
        'zakupka' => array('zakupka.com', '', '', 10),
        'riacom' => array('RIA - ria.com', '', '', 10),
        'kidstaff' => array('KIDSTAFF - kidstaff.com.ua', '', '1-2'),
        'esputnik' => array('esputnik.com', '', '', 10),
        'uamarket' => array('ua.market', '', '1-2'),
        'kaspi' => array('Каспи - kaspi.kz', '', '2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'halyk' => array('halyk - halykbank.kz/market', '', '2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'froot' => array('froot.kz', '', '1-2,2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'heureka' => array('heureka.kz', '', '', 0),
        'forte' => array('FORTE - market.forte.kz', '', ''),
        'channable' => array('Channable', '', '', 0),
        'salidzini' => array('salidzini.lv', '', '1-2,2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'gudriem' => array('gudriem.lv', '', '1-2,2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'ceno' => array('ceno.lv', '', '1-2,2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'kurpirkt' => array('kurpirkt.lv', '', '1-2', 10),
        'onliner' => array('Онлайнер - onliner.by', '', '1-2,2-7,3-4,3-5,3-6,3-8,3-11,3-13,5-2', 0),
        'tomasby' => array('Томас - tomas.by', '', ''),
        'domby' => array('dom.by', '', '1-2', 10),
        'deal' => array('deal.by', '', ''),
        'yandex'  => array('Yandex YML', '1-5,1-6,1-7', '', 10),
        'retailcrm' => array('retailcrm', '', ''),

      );
    //выгрузки

    //findInText - функция поиска в строке
      public function findInText($text, $marker, $mask){
        $finded = array();
        $posa = strpos($text, $marker);
        if($posa !== false){ //если есть вхождения
          if(preg_match_all($mask, $text, $matches)){
            $finded = $matches[1];
          }
        }
        return $finded;
      }
    //findInText

    //checkPriceDir - проверка директории сохранения фидов
      public function checkPriceDir(){
        $dir = str_replace("system/", $this->pricedir . "/", DIR_SYSTEM);
        if (!is_dir($dir)) {
          mkdir($dir, 0777, true);
        }

        if(!is_file($dir . "index.html")){
          $fp = fopen($dir . "index.html", "w");
          fclose($fp);
        }

        $dir = str_replace("system/", $this->pricedir . "/import/", DIR_SYSTEM);
        if (!is_dir($dir)) {
          mkdir($dir, 0777, true);
          $fp = fopen($dir . "index.html", "w");
          fclose($fp);
        }

        if(!is_file($dir . "index.html")){
          $fp = fopen($dir . "index.html", "w");
          fclose($fp);
        }
      }
    //checkPriceDir

    //getTableFields - функция выборки всех полей из таблицы
      public function getTableFields($table){
        $fileds_query = $this->db->query("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = '" . $this->db->escape(DB_DATABASE) . "' AND table_name = '" . DB_PREFIX . $table . "'");

        return $fileds_query->rows;
      }
    //getTableFields

    //filesize - функция получения размера файла
      public function filesize($file){
        if(!file_exists($file)) return $this->text('text_file_not_found');
        $filesize = filesize($file);
        if($filesize > 1024){
          $filesize = ($filesize/1024);
          if($filesize > 1024){
            $filesize = ($filesize/1024);
            if($filesize > 1024) {
              $filesize = ($filesize/1024);
              $filesize = round($filesize, 1);
              return $filesize." Gb";
            } else {
              $filesize = round($filesize, 1);
              return $filesize." Mb";
            }
          } else {
            $filesize = round($filesize, 1);
            return $filesize." Kb";
          }
        } else {
          $filesize = round($filesize, 1);
          return $filesize." B";
        }
      }
    //filesizе

    //exportToLog - функция логирования статистики генерации фида
      private function exportToLog(&$data, $flag = false) {
        if($flag && $data['log']){
          if($flag == "start"){
            $data['log_text']  = str_replace('{{FEED}}', $data['feed'], $this->text('text_feed_create_success')) . "\r\n";
            $data['log_text'] .= $this->text('text_stat_exp_time_start') . ' ' . date('d.m.Y H:i') . "\r\n";
          }
          if($flag == "finish"){
            $data['log_text'] = $this->text('text_stat_exp_time') . ' ' . round(microtime(true) - $data['stat']['time_start'], 3) . "\r\n";
            $data['log_text'] .= $this->text('text_stat_exp_mem_before') . ' ' . $data['stat']['memory_start'] . ' Mb.' . "\r\n";
            $data['log_text'] .= $this->text('text_stat_exp_mem_process') . ' ' . $data['stat']['memory'] . ' Mb.' . "\r\n";
            $data['log_text'] .= $this->text('text_stat_exp_all_xml') . ' ' . $data['stat']['products'] . "\r\n";
            $data['log_text'] .= $this->text('text_stat_exp_all_iter') . ' ' . $data['stat']['iteration'] . "\r\n";
            $data['log_text'] .= $this->text('text_stat_exp_all_sql') . ' ' . $data['stat']['sql'] . "\r\n";
            $data['log_text'] .= "---u---n---i---x---m---l---\r\n";
          }
          $log_file = fopen(DIR_LOGS . $data['log'], 'a');
          fwrite($log_file, $data['log_text']);
          fclose($log_file);
        }
      }
    //exportToLog

    //transform - функция транслитерации
      public function transform($string){
        if($string){
          $translit=array(
            "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"zh","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"shch","Ъ"=>"","Ы"=>"y","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya",
            "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"zh","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"shch","ъ"=>"","ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            "A"=>"a","B"=>"b","C"=>"c","D"=>"d","E"=>"e","F"=>"f","G"=>"g","H"=>"h","I"=>"i","J"=>"j","K"=>"k","L"=>"l","M"=>"m","N"=>"n","O"=>"o","P"=>"p","Q"=>"q","R"=>"r","S"=>"s","T"=>"t","U"=>"u","V"=>"v","W"=>"w","X"=>"x","Y"=>"y","Z"=>"z","І"=>"i","і"=>"i"
          );
          $string = str_replace("_", "-", $string);
          $string = mb_strtolower($string, 'UTF-8');
          $string = strip_tags($string);
          $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
          $string = strtr($string,$translit);
          $string = preg_replace("/[^a-zA-Z0-9_]/i","-",$string);
          $string = preg_replace("/\-+/i","-",$string);
          $string = preg_replace("/(^\-)|(\-$)/i","",$string);
          $string = preg_replace('/-{2,}/', '-', $string);
          $string = trim($string, "-");

          return substr($string, 0, 180); // обрезаем до 180 символов
        }
      }
    //transform

    //unsetData - функция чистки лишних данных
      public function unsetData(&$data, $keys){
        foreach(explode(',', $keys) as $key){
          if(isset($data[$key])){
            unset($data[$key]);
          }
        }
      }
    //unsetData

    //deleteProduct - функция удаления товара
      public function deleteProduct($product_id){
        $data = array();
        $this->importGetAliasTable($data);
        $product_tables = array('product','product_attribute','product_description','product_discount','product_filter','product_image','product_option','product_option_value','product_related','product_reward','product_special','product_to_category','product_to_download','product_to_layout','product_to_store','product_recurring','review','coupon_product','unixml_import_image');
        foreach ($product_tables as $table) {
          $this->db->query("DELETE FROM " . DB_PREFIX . $table . " WHERE product_id = '" . (int)$product_id . "'");
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . $data['table'] . " WHERE query = 'product_id=" . (int)$product_id . "'");
      }
    //deleteProduct

    //deleteProductImages - функция физического удаления всех фото товара
      public function deleteProductImages($flag, $product_id){
        if($flag){ //удаляем фото с сервера
          $image_query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
          if($image_query->num_rows && $image_query->row['image']){
            if(is_file(DIR_IMAGE . $image_query->row['image'])){
              unlink(DIR_IMAGE . $image_query->row['image']);
            }
          }
          $images_query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
          if($images_query->num_rows){
            foreach($images_query->rows as $image_item){
              if(is_file(DIR_IMAGE . $image_item['image'])){
                unlink(DIR_IMAGE . $image_item['image']);
              }
            }
          }
        }
      }
    //deleteProductImages

    //checkDir - функция проверки директории на существование
      public function checkDir($directory = 'unixml'){
        $dir = DIR_SYSTEM . $directory;
        if (!is_dir($dir)) {
          mkdir($dir, 0777, true);
        }

        if($directory == 'unixml'){ //проверка на .htaccess
          $fp = fopen($dir . '/.htaccess', 'w');
          fwrite($fp, 'Deny from all');
          fclose($fp);
        }
      }
    //checkDir

    //getImageExtension - получание расширение фото из его MimeType
      private function getImageExtension($image){
        $ext = 'jpg';

        $ext_data = explode('.', $image);
        if(count($ext_data) > 1){
          $ext = end($ext_data);
        }

        if(strlen($ext) > 5){
          $ext = 'jpg';
        }

        return strtolower($ext);
      }
    //getImageExtension

  //Функции вспомогательные

  //Перевод
    public function getLanguage(){
      $from = 'ru';

      $from_data = explode('-', $this->config->get('config_language'));

      if(array_intersect(array('uk','ua','ukrainian','ukraine'), $from_data)){
        $from = 'ua';
      }
      if(array_intersect(array('ru','fr','russian','rus'), $from_data)){
        $from = 'ru';
      }

      //если задано принудительно
      $lang_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "unixml_setting WHERE code = 'language'");
      if($lang_query->num_rows){
        $from = $lang_query->row['value'];
      }
      //если задано принудительно

      return $from;
    }

    public function text($word = false){
      $from = $this->getLanguage();
      include('unixml_lang.php');

      if($word){
				return isset($language[$from][$word])?$language[$from][$word]:'';
			}else{
				return $language[$from];
			}
    }
  //Перевод

}
