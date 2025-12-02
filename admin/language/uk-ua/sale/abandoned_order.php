<?php
// Heading
$_['heading_title'] 								= 'Відхилені замовлення';
$_['heading_title_setting'] 					= 'Налаштування';

// Text
$_['text_list'] 									= 'Список відхилені замовлення';
$_['text_modal_title'] 							= 'Інформація про відхилене замовлення';
$_['text_model'] 									= 'Код товару: ';
$_['text_customer_info'] 						= 'Інформація про покупця';
$_['text_products'] 								= 'Товари';
$_['text_orders'] 								= 'Замовлення за електронною поштою або номером телефону';
$_['text_qty'] 									= 'шт.';
$_['text_send_message'] 						= 'Ви надіслали повідомлення ';
$_['text_loading'] 								= 'Відправка...';
$_['text_smsclub_balance'] 					= 'На рахунку: ';

//Entry
$_['entry_customer'] 							= 'Покупець';
$_['entry_status'] 								= 'Статус';
$_['entry_date_added'] 							= 'Дата додавання';
$_['entry_email_subject'] 						= 'Тема листа';
$_['entry_email_template'] 					= 'Шаблон листа';
$_['entry_smsclub_token'] 						= 'Токен';
$_['entry_src_addr'] 							= 'Альфа-ім\'я (Ім\'я відправника)';
$_['entry_smsclub_message'] 					= 'Текст SMS-повідомлення';

$_['text_sms_status_enroute'] 				= 'Повідомлення відправлено';
$_['text_sms_status_delivrd'] 				= 'Повідомлення доставлено';
$_['text_sms_status_expired'] 				= 'Термін життя сплив, повідомлення не доставлено';
$_['text_sms_status_undeliv'] 				= 'Неможливо доставити повідомлення';
$_['text_sms_status_rejectd'] 				= 'Повідомлення відхилено системою (чорний список або інші фільтри)';
$_['text_sms_status_default'] 				= 'Невідомий статус';
$_['text_abandoned_order'] 					= 'Відхилені замовлення';
$_['text_column_left_abandoned_order'] 	= 'Відхилені замовлення <span style="position:absolute; right:14px; top:auto; margin-top:2px;" class="label label-danger">%s</span>';

//Tabs
$_['tab_general'] 								= 'Загальне';
$_['tab_email'] 									= 'Email';
$_['tab_smsclub'] 								= 'SMS Club';

//Column
$_['column_abandoned_id'] 						= '№';
$_['column_customer'] 							= 'Покупець';
$_['column_email'] 								= 'Email';
$_['column_telephone'] 							= 'Телефон';
$_['column_date_added'] 						= 'Дата додавання';
$_['column_action'] 								= 'Дія';
$_['column_product_name'] 						= 'Товар';
$_['column_product_quantity'] 				= 'Кількість';
$_['column_product_price'] 					= 'Ціна';
$_['column_total'] 								= 'Всього';

//Button
$_['button_setting'] 							= 'Налаштування';
$_['button_send_email'] 						= 'Надіслати листа';
$_['button_send_sms'] 							= 'Надіслати SMS-повідомлення';

//Help
$_['help_status_informer'] 					= 'Увімкнути або вимкнути інформер в шапці сайту';
$_['help_status_email'] 						= 'Увімкнути або вимкнути можливість надсилання повідомлень покупцям на електронну пошту';
$_['help_status_telegram'] 					= 'Увімкнути або вимкнути можливість надсилання повідомлень покупцям в Telegram';
$_['help_email_subject'] 						= 'Доступні змінні: </br> [firstname], [lastname], [date_added]';
$_['help_email_template'] 						= 'Доступні змінні: </br> [firstname], [lastname], [products], [email], [telephone], [date_added]';
$_['help_token'] 									= 'Отримати свій токен можна в розділі Профіль в особистому кабінеті на сайті <a target="_blank" href="https://my.smsclub.mobi/auth?id_referral=56722#tab-reg">my.smsclub.mobi/uk/profile</a>';
$_['help_originators'] 							= 'Створити Альфа-імена можна в особистому кабінеті на сайті в розділі Альфа-імена <a target="_blank" href="https://my.smsclub.mobi/uk/alphanames/">my.smsclub.mobi/uk/alphanames/</a><br>Альфа-імена обов\'язкові для заповнення, без них відправка повідомлення не буде здійснюватися';
$_['help_sms_message'] 							= 'Доступні змінні: </br> [firstname], [lastname], [email], [telephone], [products], [date_added]';

// Success
$_['text_success'] 								= 'Ви успішно видалили відхилене замовлення!';
$_['text_success_save_setting'] 				= 'Ви успішно зберегли налаштування';
$_['text_success_send_email_message'] 		= 'Ви успішно надіслали листа!';
$_['text_success_send_sms_message'] 		= 'Ви успішно надіслали SMS-повідомлення!';


// Error
$_['error_permission'] 							= 'Увага: У вас немає прав для зміни відхилені замовлення!';
$_['text_error_email'] 							= 'Некоректна адреса електронної пошти';
$_['text_error_email_subject'] 				= 'Ви не заповнили тему листа!';
$_['text_error_email_template'] 				= 'Ви не заповнили шаблон листа!';
$_['text_error_empty_token'] 					= 'Токен не може бути порожнім. Вкажіть свій токен.';
$_['text_error_invalid_token'] 				= 'Токен вказано невірно! Отримайте правильний токен.';
$_['text_error_src_addr'] 						= 'Альфа-ім\'я обов\'язкове для заповнення!';
$_['text_error_send_sms_message'] 			= 'Під час надсилання повідомлення сталася помилка! :(';

