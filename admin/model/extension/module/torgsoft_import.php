<?php
class ModelExtensionModuleTorgsoftImport extends Model {

    private $log_file;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->log_file = DIR_STORAGE . 'logs/torgsoft.log';
    }

    private function log($level, $msg) {
        if (!file_exists($this->log_file)) {
            file_put_contents($this->log_file, "=== TorgSoft Import Log ===\n");
        }

        file_put_contents(
            $this->log_file,
            '[' . date('Y-m-d H:i:s') . "][$level] $msg\n",
            FILE_APPEND
        );
    }

    /**
     * Забезпечує наявність атрибуту, повертає attribute_id
     */
    private function ensureAttribute($code, $name, $attribute_group_id, $lang_id, &$cache) {
        if (isset($cache[$code])) {
            return $cache[$code];
        }

        $q = $this->db->query("
            SELECT a.attribute_id
            FROM " . DB_PREFIX . "attribute a
            LEFT JOIN " . DB_PREFIX . "attribute_description ad
                ON (a.attribute_id = ad.attribute_id)
            WHERE ad.name='" . $this->db->escape($name) . "'
              AND ad.language_id=" . (int)$lang_id . "
            LIMIT 1
        ");

        if ($q->num_rows) {
            $id = (int)$q->row['attribute_id'];
        } else {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "attribute SET
                    attribute_group_id=" . (int)$attribute_group_id . ",
                    sort_order=0
            ");
            $id = (int)$this->db->getLastId();

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "attribute_description SET
                    attribute_id=" . (int)$id . ",
                    language_id=" . (int)$lang_id . ",
                    name='" . $this->db->escape($name) . "'
            ");

            $this->log('INFO', "Створено атрибут: $name (id=$id)");
        }

        $cache[$code] = $id;
        return $id;
    }

    /**
     * Забезпечує наявність опції, повертає option_id
     */
    private function ensureOption($code, $name, $type, $lang_id, &$cache) {
        if (isset($cache[$code])) {
            return $cache[$code];
        }

        $q = $this->db->query("
            SELECT o.option_id
            FROM " . DB_PREFIX . "option o
            LEFT JOIN " . DB_PREFIX . "option_description od
                ON (o.option_id = od.option_id)
            WHERE od.name='" . $this->db->escape($name) . "'
              AND od.language_id=" . (int)$lang_id . "
            LIMIT 1
        ");

        if ($q->num_rows) {
            $id = (int)$q->row['option_id'];
        } else {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "option SET
                    type='" . $this->db->escape($type) . "',
                    sort_order=0
            ");
            $id = (int)$this->db->getLastId();

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "option_description SET
                    option_id=" . (int)$id . ",
                    language_id=" . (int)$lang_id . ",
                    name='" . $this->db->escape($name) . "'
            ");

            $this->log('INFO', "Створено опцію: $name (id=$id)");
        }

        $cache[$code] = $id;
        return $id;
    }

    /**
     * Забезпечує наявність значення опції, повертає option_value_id
     */
    private function ensureOptionValue($option_id, $value, $lang_id, &$cache) {
        $key = $option_id . '::' . $value;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $q = $this->db->query("
            SELECT ov.option_value_id
            FROM " . DB_PREFIX . "option_value ov
            LEFT JOIN " . DB_PREFIX . "option_value_description ovd
                ON (ov.option_value_id = ovd.option_value_id)
            WHERE ov.option_id=" . (int)$option_id . "
              AND ovd.name='" . $this->db->escape($value) . "'
              AND ovd.language_id=" . (int)$lang_id . "
            LIMIT 1
        ");

        if ($q->num_rows) {
            $id = (int)$q->row['option_value_id'];
        } else {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "option_value SET
                    option_id=" . (int)$option_id . ",
                    image='',
                    sort_order=0
            ");
            $id = (int)$this->db->getLastId();

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "option_value_description SET
                    option_value_id=" . (int)$id . ",
                    language_id=" . (int)$lang_id . ",
                    option_id=" . (int)$option_id . ",
                    name='" . $this->db->escape($value) . "'
            ");

            $this->log('INFO', "Створено значення опції: $value (id=$id, option_id=$option_id)");
        }

        $cache[$key] = $id;
        return $id;
    }

    public function runImport() {

        // пути к XML и папке с фото
        $xml_path   = $this->config->get('module_torgsoft_import_xml_path');
        $img_source = rtrim($this->config->get('module_torgsoft_import_images_path'), "/\\");

        $this->log('INFO', "XML path: $xml_path");
        $this->log('INFO', "IMG source: $img_source");

        if (!$xml_path || !is_file($xml_path)) {
            $this->log('ERROR', "Не знайдено XML: $xml_path");
            return;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($xml_path);
        if (!$xml) {
            $this->log('ERROR', "XML не читається");
            foreach (libxml_get_errors() as $e) {
                $this->log('ERROR', 'XML error: ' . trim($e->message));
            }
            libxml_clear_errors();
            return;
        }

        $this->log('INFO', "XML успішно прочитано.");

        $lang_id = (int)$this->config->get('config_language_id');

        /* ======================= КАТЕГОРІЇ ======================= */

        $categories = [];

        if (isset($xml->shop->categories->category)) {
            foreach ($xml->shop->categories->category as $c) {
                $id   = (string)$c['id'];
                $pid  = (string)$c['parentId'];
                $name = trim((string)$c);

                if ($id === '') {
                    continue;
                }

                $categories[$id] = [
                    'id'     => $id,
                    'parent' => $pid ?: 0,
                    'name'   => $name
                ];
            }
        }

        foreach ($categories as $id => $cat) {
            $row = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE category_id=" . (int)$id);
            if (!$row->num_rows) {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "category SET
                        category_id=" . (int)$id . ",
                        parent_id=" . (int)$cat['parent'] . ",
                        top=0,
                        `column`=1,
                        sort_order=0,
                        status=1,
                        date_added=NOW(),
                        date_modified=NOW()
                ");

                $nm = $this->db->escape($cat['name']);

                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "category_description SET
                        category_id=" . (int)$id . ",
                        language_id=" . (int)$lang_id . ",
                        name='$nm',
                        meta_title='$nm',
                        description=''
                ");

                $this->log('INFO', "Категорія створена: " . $cat['name'] . " (id=$id)");
            }

            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "category_to_store
                SET category_id=" . (int)$id . ",
                    store_id=0
            ");
        }

        foreach ($categories as $id => $cat) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "category_path WHERE category_id=" . (int)$id);

            $path    = [];
            $current = $id;

            while ($current && isset($categories[$current])) {
                $path[]  = $current;
                $current = $categories[$current]['parent'];
            }

            $path  = array_reverse($path);
            $level = 0;

            foreach ($path as $pid) {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "category_path SET
                        category_id=" . (int)$id . ",
                        path_id=" . (int)$pid . ",
                        level=" . (int)$level . "
                ");
                $level++;
            }
        }

        $this->log('INFO', "Ієрархія категорій оновлена. Всього категорій: " . count($categories));

        /* ============= ГРУПА АТРИБУТІВ / КЕШІ ============== */

        $attr_group_name = 'Характеристики товару';

        $ag = $this->db->query("
            SELECT ag.attribute_group_id
            FROM " . DB_PREFIX . "attribute_group ag
            LEFT JOIN " . DB_PREFIX . "attribute_group_description agd
                ON (ag.attribute_group_id = agd.attribute_group_id)
            WHERE agd.name='" . $this->db->escape($attr_group_name) . "'
              AND agd.language_id=" . (int)$lang_id . "
            LIMIT 1
        ");

        if ($ag->num_rows) {
            $attribute_group_id = (int)$ag->row['attribute_group_id'];
        } else {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "attribute_group SET
                    sort_order=0
            ");
            $attribute_group_id = (int)$this->db->getLastId();

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "attribute_group_description SET
                    attribute_group_id=" . (int)$attribute_group_id . ",
                    language_id=" . (int)$lang_id . ",
                    name='" . $this->db->escape($attr_group_name) . "'
            ");

            $this->log('INFO', "Створено групу атрибутів: $attr_group_name (id=$attribute_group_id)");
        }

        $attribute_map = [
            'Color'    => 'Колір',
            'Material' => 'Матеріал',
            'Season'   => 'Сезон',
            'Country'  => 'Країна',
            'Age'      => 'Вік',
            'Sex'      => 'Стать'
        ];

        $attribute_cache    = [];
        $option_cache       = [];
        $option_value_cache = [];

        /* ======================= ТОВАРИ ======================= */

        $added   = 0;
        $updated = 0;

        if (!isset($xml->shop->offers->offer)) {
            $this->log('WARN', "У XML немає offers/offer");
            return;
        }

        $customer_group_id = (int)$this->config->get('config_customer_group_id');
        if (!$customer_group_id) {
            $customer_group_id = 1;
        }

        // вспомогательные карты по товару (product_id) в рамках одного импорта
        $seen_products  = []; // флаг, что товар уже очищен и quantity сброшен
        $image_index    = []; // текущий sort_order для фото по product_id
        $images_added   = []; // уже добавленные картинки по product_id => [image_rel => true]

        foreach ($xml->shop->offers->offer as $offer) {

            /* GoodID */
            $GoodID = '';
            foreach ($offer->param as $p) {
                if ((string)$p['name'] === 'GoodID') {
                    $GoodID = trim((string)$p);
                    break;
                }
            }
            if (!$GoodID) {
                $this->log('WARN', "Пропущено offer без GoodID");
                continue;
            }

            /* Артикул (общий для всех размеров) */
            $Articul = '';
            foreach ($offer->param as $p) {
                if ((string)$p['name'] === 'Articul') {
                    $Articul = trim((string)$p);
                    break;
                }
            }

            // запасной вариант: стандартный тег vendorCode
            if (!$Articul && isset($offer->vendorCode)) {
                $Articul = trim((string)$offer->vendorCode);
            }

            // если совсем нет артикула – используем GoodID как модель
            if (!$Articul) {
                $Articul = $GoodID;
            }

            // ключ товара в магазине = артикул
            $product_key = $Articul;

            /* Назва */
            $GoodName = '';
            foreach ($offer->param as $p) {
                if ((string)$p['name'] === 'GoodName') {
                    $GoodName = trim(trim((string)$p), '"');
                    break;
                }
            }

            /* Опис */
            $Description = '';
            foreach ($offer->param as $p) {
                if ((string)$p['name'] === 'Description') {
                    $Description = trim(trim((string)$p), '"');
                    break;
                }
            }

            /* Категорія */
            $catId = isset($offer->categoryId) ? (int)$offer->categoryId : 0;

            /* Кількість (для конкретного розміру) */
            $qty = 0;
            foreach ($offer->param as $p) {
                if ((string)$p['name'] === 'WarehouseQuantity') {
                    $qty = (int)$p;
                    break;
                }
            }

            /* ================== ЦІНИ / АКЦІЇ ================== */

            $price                     = 0.0; // базовая розничная
            $price_old_tag             = 0.0; // <oldprice>
            $price_tag                 = 0.0; // <price> (со скидкой)
            $price_retail_param        = 0.0; // param RetailPrice
            $price_with_discount_param = 0.0; // param RetailPriceWithDiscount
            $discount_percent          = 0.0; // param PriceDiscountPercent
            $special_price             = 0.0;

            // YML теги
            if (isset($offer->oldprice)) {
                $price_old_tag = (float)$offer->oldprice;
            }
            if (isset($offer->price)) {
                $price_tag = (float)$offer->price;
            }

            // параметры
            foreach ($offer->param as $p) {
                $n = (string)$p['name'];
                if ($n === 'RetailPrice') {
                    $price_retail_param = (float)$p;
                } elseif ($n === 'RetailPriceWithDiscount') {
                    $price_with_discount_param = (float)$p;
                } elseif ($n === 'PriceDiscountPercent') {
                    $discount_percent = (float)$p;
                }
            }

            // определяем базовую цену
            if ($price_old_tag > 0) {
                $price = $price_old_tag;
            } elseif ($price_retail_param > 0) {
                $price = $price_retail_param;
            } elseif ($price_tag > 0) {
                $price = $price_tag;
            }

            // определяем акционную цену
            if ($price_with_discount_param > 0) {
                $special_price = $price_with_discount_param;
            } elseif ($price_tag > 0 && $price_old_tag > 0 && $price_tag < $price_old_tag) {
                // формат YML: price (со скидкой) + oldprice (старая)
                $special_price = $price_tag;
            } elseif ($discount_percent > 0 && $price > 0) {
                $special_price = round($price * (100 - $discount_percent) / 100, 2);
            }

            if ($price <= 0 && $special_price > 0) {
                // страховка: если базовой цены нет, а скидочная есть
                $price         = $special_price;
                $special_price = 0.0;
            }

            /* Бренд */
            $brand = '';
            foreach ($offer->param as $p) {
                if ((string)$p['name'] === 'PCName') {
                    $brand = trim(trim((string)$p), '"');
                    break;
                }
            }

            if (!$brand) {
                $brand = (string)$this->config->get('module_torgsoft_import_brand_fallback');
                if (!$brand) {
                    $brand = 'TorgSoft';
                }
            }

            // Характеристики + опції
            $attr_values = [
                'Color'    => '',
                'Material' => '',
                'Season'   => '',
                'Country'  => '',
                'Age'      => '',
                'Sex'      => ''
            ];
            $size_value       = '';
            $color_option_val = '';

            foreach ($offer->param as $p) {
                $name = (string)$p['name'];
                $val  = trim(trim((string)$p), '"');

                switch ($name) {
                    case 'Color':
                        $attr_values['Color'] = $val;
                        $color_option_val     = $val;
                        break;
                    case 'Material':
                        $attr_values['Material'] = $val;
                        break;
                    case 'Season':
                        $attr_values['Season'] = $val;
                        break;
                    case 'Country':
                        $attr_values['Country'] = $val;
                        break;
                    case 'Age':
                        $attr_values['Age'] = $val;
                        break;
                    case 'Sex':
                        $attr_values['Sex'] = $val;
                        break;
                    case 'TheSize':
                        $size_value = $val;
                        break;
                }
            }

            /* ----------- БРЕНД ----------- */

            $m = $this->db->query("
                SELECT manufacturer_id
                FROM " . DB_PREFIX . "manufacturer
                WHERE name='" . $this->db->escape($brand) . "'
                LIMIT 1
            ");
            if ($m->num_rows) {
                $manufacturer_id = (int)$m->row['manufacturer_id'];
            } else {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "manufacturer SET
                        name='" . $this->db->escape($brand) . "'
                ");
                $manufacturer_id = (int)$this->db->getLastId();

                $this->db->query("
                    INSERT IGNORE INTO " . DB_PREFIX . "manufacturer_description SET
                        manufacturer_id=" . (int)$manufacturer_id . ",
                        language_id=" . (int)$lang_id . "
                ");

                $this->log('INFO', "Створено бренд: $brand (id=$manufacturer_id)");
            }

            $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "manufacturer_to_store SET
                    manufacturer_id=" . (int)$manufacturer_id . ",
                    store_id=0
            ");

            /* ----------- ТОВАР ПО АРТИКУЛУ (model = Articul) ----------- */

            $q = $this->db->query("
                SELECT product_id
                FROM " . DB_PREFIX . "product
                WHERE model='" . $this->db->escape($product_key) . "'
                LIMIT 1
            ");
            $is_new = !$q->num_rows;

            if ($is_new) {

                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product SET
                        model='" . $this->db->escape($product_key) . "',
                        quantity=0,
                        price=" . (float)$price . ",
                        manufacturer_id=" . (int)$manufacturer_id . ",
                        status=1,
                        date_available=NOW(),
                        date_added=NOW(),
                        date_modified=NOW()
                ");
                $pid = (int)$this->db->getLastId();

                $nm = $this->db->escape($GoodName);
                $ds = $this->db->escape($Description);

                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product_description SET
                        product_id=" . (int)$pid . ",
                        language_id=" . (int)$lang_id . ",
                        name='$nm',
                        description='$ds',
                        meta_title='$nm'
                ");

                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product_to_store SET
                        product_id=" . (int)$pid . ",
                        store_id=0
                ");

                $added++;
                $this->log('INFO', "Створено товар: $GoodName (#$pid, Articul=$product_key, GoodID=$GoodID)");

            } else {

                $pid = (int)$q->row['product_id'];

                // обновляем цену/бренд, но количество пересчитаем ниже по всем размерам
                $this->db->query("
                    UPDATE " . DB_PREFIX . "product SET
                        price=" . (float)$price . ",
                        manufacturer_id=" . (int)$manufacturer_id . ",
                        status=1,
                        date_modified=NOW()
                    WHERE product_id=" . (int)$pid . "
                ");

                $this->db->query("
                    UPDATE " . DB_PREFIX . "product_description SET
                        name='" . $this->db->escape($GoodName) . "',
                        description='" . $this->db->escape($Description) . "',
                        meta_title='" . $this->db->escape($GoodName) . "'
                    WHERE product_id=" . (int)$pid . "
                      AND language_id=" . (int)$lang_id . "
                ");

                $this->db->query("
                    INSERT IGNORE INTO " . DB_PREFIX . "product_to_store SET
                        product_id=" . (int)$pid . ",
                        store_id=0
                ");

                $updated++;
                $this->log('INFO', "Оновлено товар: $GoodName (#$pid, Articul=$product_key, GoodID=$GoodID)");
            }

            /* ----------- ПЕРВИЧНАЯ ОЧИСТКА ПО product_id (однажды за импорт) ----------- */

            if (!isset($seen_products[$pid])) {

                // обнуляем количество (будем суммировать по всем размерам)
                $this->db->query("
                    UPDATE " . DB_PREFIX . "product SET
                        quantity = 0,
                        image    = ''
                    WHERE product_id=" . (int)$pid . "
                ");

                // чистим категории, атрибуты, опции, фото и акции — перед полным пересозданием
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id=" . (int)$pid);
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id=" . (int)$pid);
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id=" . (int)$pid);
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id=" . (int)$pid);
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id=" . (int)$pid);
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id=" . (int)$pid);

                $seen_products[$pid] = true;
                $image_index[$pid]   = 0;
                $images_added[$pid]  = [];
            }

            /* ----------- СУММАРНОЕ КОЛИЧЕСТВО ПО ВСЕМ РАЗМЕРАМ ----------- */

            if ($qty > 0) {
                $this->db->query("
                    UPDATE " . DB_PREFIX . "product SET
                        quantity = quantity + " . (int)$qty . "
                    WHERE product_id=" . (int)$pid . "
                ");
            }

            /* ----------- АКЦИИ (product_special) ------------- */

            if ($special_price > 0 && $special_price < $price) {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product_special SET
                        product_id=" . (int)$pid . ",
                        customer_group_id=" . (int)$customer_group_id . ",
                        priority=1,
                        price=" . (float)$special_price . ",
                        date_start='0000-00-00',
                        date_end='0000-00-00'
                ");

                $this->log('INFO', "Акція для Articul=$product_key: price={$price}, special={$special_price}");
            }

            /* ----------- Категорія ------------ */

            if ($catId > 0) {
                // возможны несколько офферов с тем же товаром и категорией → используем IGNORE
                $this->db->query("
                    INSERT IGNORE INTO " . DB_PREFIX . "product_to_category SET
                        product_id=" . (int)$pid . ",
                        category_id=" . (int)$catId . "
                ");
            }

            /* ----------- ХАРАКТЕРИСТИКИ ----------- */

            foreach ($attr_values as $code => $value) {
                if ($value === '') continue;
                if (!isset($attribute_map[$code])) continue;

                $attr_name    = $attribute_map[$code];
                $attribute_id = $this->ensureAttribute($code, $attr_name, $attribute_group_id, $lang_id, $attribute_cache);

                // один и тот же атрибут может прилететь от нескольких офферов → ON DUPLICATE KEY
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product_attribute
                        (product_id, attribute_id, language_id, text)
                    VALUES
                        (" . (int)$pid . ",
                         " . (int)$attribute_id . ",
                         " . (int)$lang_id . ",
                         '" . $this->db->escape($value) . "'
                        )
                    ON DUPLICATE KEY UPDATE
                        text = VALUES(text)
                ");
            }

            /* ----------- ОПЦІЇ (Розмір + Колір) ----------- */

            // Розмір
            if ($size_value !== '') {
                $size_option_id = $this->ensureOption('TheSize', 'Розмір', 'select', $lang_id, $option_cache);
                $size_ov_id     = $this->ensureOptionValue($size_option_id, $size_value, $lang_id, $option_value_cache);

                // product_option (одна запись на товар и опцію)
                $q_po = $this->db->query("
                    SELECT product_option_id
                    FROM " . DB_PREFIX . "product_option
                    WHERE product_id=" . (int)$pid . "
                      AND option_id=" . (int)$size_option_id . "
                    LIMIT 1
                ");

                if ($q_po->num_rows) {
                    $product_option_id = (int)$q_po->row['product_option_id'];
                } else {
                    $this->db->query("
                        INSERT INTO " . DB_PREFIX . "product_option SET
                            product_id=" . (int)$pid . ",
                            option_id=" . (int)$size_option_id . ",
                            required=1
                    ");
                    $product_option_id = (int)$this->db->getLastId();
                }

                // product_option_value (по комбинации product_id + option_value_id)
                $q_pov = $this->db->query("
                    SELECT product_option_value_id
                    FROM " . DB_PREFIX . "product_option_value
                    WHERE product_id=" . (int)$pid . "
                      AND product_option_id=" . (int)$product_option_id . "
                      AND option_id=" . (int)$size_option_id . "
                      AND option_value_id=" . (int)$size_ov_id . "
                    LIMIT 1
                ");

                if ($q_pov->num_rows) {
                    // обновляем количество
                    $this->db->query("
                        UPDATE " . DB_PREFIX . "product_option_value SET
                            quantity=" . (int)$qty . "
                        WHERE product_option_value_id=" . (int)$q_pov->row['product_option_value_id'] . "
                    ");
                } else {
                    $this->db->query("
                        INSERT INTO " . DB_PREFIX . "product_option_value SET
                            product_option_id=" . (int)$product_option_id . ",
                            product_id=" . (int)$pid . ",
                            option_id=" . (int)$size_option_id . ",
                            option_value_id=" . (int)$size_ov_id . ",
                            quantity=" . (int)$qty . ",
                            subtract=1,
                            price=0,
                            price_prefix='+',
                            points=0,
                            points_prefix='+',
                            weight=0,
                            weight_prefix='+'
                    ");
                }
            }

            // Колір
            if ($color_option_val !== '') {
                $color_option_id = $this->ensureOption('ColorOption', 'Колір', 'select', $lang_id, $option_cache);
                $color_ov_id     = $this->ensureOptionValue($color_option_id, $color_option_val, $lang_id, $option_value_cache);

                $q_po2 = $this->db->query("
                    SELECT product_option_id
                    FROM " . DB_PREFIX . "product_option
                    WHERE product_id=" . (int)$pid . "
                      AND option_id=" . (int)$color_option_id . "
                    LIMIT 1
                ");

                if ($q_po2->num_rows) {
                    $product_option_id2 = (int)$q_po2->row['product_option_id'];
                } else {
                    $this->db->query("
                        INSERT INTO " . DB_PREFIX . "product_option SET
                            product_id=" . (int)$pid . ",
                            option_id=" . (int)$color_option_id . ",
                            required=0
                    ");
                    $product_option_id2 = (int)$this->db->getLastId();
                }

                $q_pov2 = $this->db->query("
                    SELECT product_option_value_id
                    FROM " . DB_PREFIX . "product_option_value
                    WHERE product_id=" . (int)$pid . "
                      AND product_option_id=" . (int)$product_option_id2 . "
                      AND option_id=" . (int)$color_option_id . "
                      AND option_value_id=" . (int)$color_ov_id . "
                    LIMIT 1
                ");

                if ($q_pov2->num_rows) {
                    $this->db->query("
                        UPDATE " . DB_PREFIX . "product_option_value SET
                            quantity=" . (int)$qty . "
                        WHERE product_option_value_id=" . (int)$q_pov2->row['product_option_value_id'] . "
                    ");
                } else {
                    $this->db->query("
                        INSERT INTO " . DB_PREFIX . "product_option_value SET
                            product_option_id=" . (int)$product_option_id2 . ",
                            product_id=" . (int)$pid . ",
                            option_id=" . (int)$color_option_id . ",
                            option_value_id=" . (int)$color_ov_id . ",
                            quantity=" . (int)$qty . ",
                            subtract=0,
                            price=0,
                            price_prefix='+',
                            points=0,
                            points_prefix='+',
                            weight=0,
                            weight_prefix='+'
                    ");
                }
            }

            /* ----------- ФОТО ----------- */

            $found_any = false;

            foreach ($offer->picture as $p) {
                $raw = trim((string)$p);
                if (!$raw) continue;

                $raw_norm = str_replace('\\', '/', $raw);
                $base     = basename($raw_norm);

                $clean_base = preg_replace('/^[^\d]*/', '', $base);

                $seed   = [];
                $seed[] = $raw_norm;
                $seed[] = $base;
                if ($clean_base && $clean_base !== $base) {
                    $seed[] = $clean_base;
                }

                $cand_list = [];
                foreach ($seed as $cand) {
                    if ($cand === '') continue;

                    $cand_list[] = $cand;
                    $cand_list[] = strtolower($cand);
                    $cand_list[] = strtoupper($cand);
                }

                $cand_list = array_values(array_unique($cand_list));

                $src      = '';
                $file_rel = '';

                foreach ($cand_list as $cand) {
                    $cand_clean = ltrim($cand, '/');
                    if ($cand_clean === '') continue;

                    $try = $img_source . '/' . $cand_clean;

                    if (is_file($try)) {
                        $src      = $try;
                        $file_rel = basename($try);
                        break;
                    }
                }

                if (!$src || !$file_rel) {
                    $this->log(
                        'WARN',
                        "Фото не знайдено для GoodID=$GoodID, raw='$raw', base='$base', clean='$clean_base'"
                    );
                    continue;
                }

                $dst_rel = 'catalog/torgsoft/' . $file_rel;

                // проверяем, не добавляли ли уже это фото для данного товара в рамках текущего імпорту
                if (isset($images_added[$pid][$dst_rel])) {
                    continue;
                }

                $found_any = true;

                $dst = DIR_IMAGE . $dst_rel;

                if (!is_dir(dirname($dst))) {
                    mkdir(dirname($dst), 0777, true);
                }

                if (@copy($src, $dst)) {
                    $this->log('INFO', "Фото скопійовано для GoodID=$GoodID: $src -> $dst_rel");
                } else {
                    $this->log('ERROR', "Не вдалося скопіювати фото для GoodID=$GoodID: $src -> $dst_rel");
                    continue;
                }

                // индекс фото для sort_order
                if (!isset($image_index[$pid])) {
                    $image_index[$pid] = 0;
                }
                $i = $image_index[$pid];

                if ($i == 0) {
                    // главное фото
                    $this->db->query("
                        UPDATE " . DB_PREFIX . "product SET
                            image='" . $this->db->escape($dst_rel) . "'
                        WHERE product_id=" . (int)$pid . "
                    ");
                } else {
                    $this->db->query("
                        INSERT INTO " . DB_PREFIX . "product_image SET
                            product_id=" . (int)$pid . ",
                            image='" . $this->db->escape($dst_rel) . "',
                            sort_order=" . (int)$i . "
                    ");
                }

                $images_added[$pid][$dst_rel] = true;
                $image_index[$pid]            = $i + 1;
            }

            if (!$found_any) {
                $this->log('WARN', "Для товара Articul=$product_key (GoodID=$GoodID) не знайдено жодного фото");
            }
        }

        $this->log('INFO', "Готово! Додано=$added, оновлено=$updated");
    }
}
