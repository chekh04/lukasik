<?php
// Heading
$_['heading_title']    = '<span style="color:#000"><img style="min-width:30px; margin-right:5px;" src="view/image/upstore_icon.svg"><span class="hidden">Up</span>store - Модуль Настройки заказов</span>';

// Text
$_['text_module']      = 'модуль';
$_['text_success']     = 'Настройки успешно изменены!';
$_['text_edit']        = 'Настройки модуля';

$_['entry_on_off_model_product']        			= 'Вкл ВЫКЛ модель товара';
$_['entry_on_off_sku_product']        				= 'Вкл ВЫКЛ SKU товара';
$_['entry_on_off_upc_product']        				= 'Вкл ВЫКЛ UPC товара';
$_['entry_on_off_payment_method']        			= 'Вкл ВЫКЛ Payment Method';
$_['entry_on_off_shipping_method']        			= 'Вкл ВЫКЛ Shipping Method';

$_['entry_on_off_column_product']        			= 'Вкл ВЫКЛ Колонку Товара';
$_['entry_on_off_column_comment_manager']        	= 'Вкл ВЫКЛ Колонку Комментарий менеджера';
$_['entry_on_off_column_send_ttn_email']        	= 'Вкл ВЫКЛ Колонку Отправка ТТН на почту клиенту';
$_['entry_on_off_column_price_purchase']        	= 'Вкл ВЫКЛ Колонку Цена Закупки';
$_['entry_on_off_column_total_profit']        		= 'Вкл ВЫКЛ Колонку Прибыль';
$_['entry_on_off_column_delivery_price']    		= 'Вкл ВЫКЛ Колонку Стоимость Доставки';
$_['entry_on_off_column_build']    					= 'Вкл ВЫКЛ Колонку Сборку';
$_['entry_on_off_column_rise_product']    			= 'Вкл ВЫКЛ Колонку Подъем';
$_['entry_on_off_column_manager_process_orders']    = 'Вкл ВЫКЛ Колонку Менеджер';


$_['ttn_subject_text'] 								= 'Тема письма';
$_['ttn_subject_text_variables'] 					= 'Поддерживает переменные';
$_['entry_description_send_email_ttn'] 				= ' Шаблон письма TTN';
$_['it_supports_variable_or_html_tags'] 			= ' Поддерживает переменные и html теги';
$_['list_of_variables_text'] 						= 'Список Переменных';
$_['list_of_variables_entry'] 						= '<table><tr><td>
<br/><b>~text_ttn~</b> - номер ТТН
<br/><b>~order_id~</b> - номер заказа
<br/><b>~totals~</b> - сумма заказа в текущей валюте
<br/><b>~invoice_no~</b> - номер счета
<br/><b>~invoice_prefix~</b> - префикс счета
<br/><b>~store_id~</b> - id магазина
<br/><b>~store_name~</b> - название магазина
<br/><b>~store_url~</b> - url магазина
<br/><b>~customer_id~</b> - id покупателя
<br/><b>~firstname~</b> - имя
<br/><b>~lastname~</b> - фамилия
<br/><b>~telephone~</b> - телефон
<br/><b>~fax~</b> - факс
<br/><b>~email~</b> - почта
<br/><b>~payment_firstname~</b> - имя плательщика
<br/><b>~payment_lastname~</b> - фамилия плательщика
<br/><b>~payment_company~</b> - компания плательщика
<br/><b>~payment_address_1~</b> - адрес плательщика
<br/><b>~payment_address_2~</b> - адрес2 плательщика
<br/><b>~payment_postcode~</b> - индекс плательщика
<br/><b>~payment_city~</b> - город плательщика
<br/><b>~payment_zone_id~</b> - id гео зоны плательщика
<br/><b>~payment_zone~</b> - гео зона плательщика
<br/><b>~payment_zone_code~</b> - код зоны плательщика
<br/><b>~payment_country_id~</b> - id страны плательщика
<br/><b>~payment_country~</b> - страна плательщика
<br/><b>~payment_iso_code_2~</b> - iso код плательщика
<br/><b>~payment_iso_code_3~</b>  - iso код плательщика
<br/><b>~payment_address_format~</b> - формат адреса плательщика
<br/><b>~payment_method~</b> - метод оплаты
<br/><b>~payment_code~</b> - код оплаты
</td><td>
<br/><b>~shipping_firstname~</b> - имя для доставки
<br/><b>~shipping_lastname~</b> - фамилия для доставки
<br/><b>~shipping_company~</b> - компания для доставки
<br/><b>~shipping_address_1~</b> - адрес доставки
<br/><b>~shipping_address_2~</b> - адрес2 доставки
<br/><b>~shipping_postcode~</b> - индекс для доставки
<br/><b>~shipping_city~</b> - город доставки
<br/><b>~shipping_zone_id~</b> - id гео зоны доставки
<br/><b>~shipping_zone~</b> - гео зона доставки
<br/><b>~shipping_zone_code~</b>  - код зоны доставки
<br/><b>~shipping_country_id~</b> - id страны доставки
<br/><b>~shipping_country~</b> - страна доставки
<br/><b>~shipping_iso_code_2~</b> - iso код доставки
<br/><b>~shipping_iso_code_3~</b> - iso код доставки
<br/><b>~shipping_address_format~</b> - формат адреса доставки
<br/><b>~shipping_method~</b> - метод доставки
<br/><b>~shipping_code~</b> - код метода доставки
<br/><b>~comment~</b> - комментарий
<br/><b>~total~</b> - итого (в валюте по умолчанию)
<br/><b>~order_status_id~</b> - id статуса заказа
<br/><b>~order_status~</b> - статус заказа
<br/><b>~language_id~</b> - id языка
<br/><b>~language_code~</b> - код языка
<br/><b>~language_filename~</b> - файл языка
<br/><b>~language_directory~</b> - дирректория языка
<br/><b>~currency_id~</b> - id валюты
<br/><b>~currency_code~</b> - код валюты
<br/><b>~currency_value~</b> - курс валюты
<br/><b>~ip~</b> - ip
<br/><b>~forwarded_ip~</b> - переадресованный ip
<br/><b>~user_agent~</b> - юзер агент
<br/><b>~accept_language~</b> - язык
<br/><b>~date_modified~</b> - дата изменения заказа
<br/><b>~date_added~</b> - дата создания заказа
</td></tr></table><br/>
Некоторые переменные могут не работать, зависит от версии магазина.
';
// Error
$_['error_permission'] = 'У Вас нет прав для управления даннным модулем!';
$_['error_name'] = 'Название модуля должно содержать от 3 до 64 символов!';
