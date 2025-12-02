<?php
// Heading
$_['heading_title']								= 'Брошенные Заказы';
$_['heading_title_setting']					= 'Настройки';

// Text
$_['text_list']									= 'Список брошенных заказов';
$_['text_modal_title']							= 'Информация о брошенном заказе';
$_['text_model']									= 'Код товар: ';
$_['text_customer_info']						= 'Данные покупателя';
$_['text_products']								= 'Товары';
$_['text_orders']									= 'Заказы с такой почтой или номером телефона';
$_['text_qty']										= 'шт.';
$_['text_send_message']							= 'Вы отправили сообщение ';
$_['text_loading']								= 'Отправляем...';
$_['text_smsclub_balance']						= 'На счету: ';

//Entry
$_['entry_customer']								= 'Покупатель';
$_['entry_status']								= 'Статус';
$_['entry_date_added']							= 'Дата добавления';
$_['entry_email_subject']						= 'Тема письма';
$_['entry_email_template']						= 'Шаблон письма';
$_['entry_smsclub_token']						= 'Token';
$_['entry_src_addr']								= 'Альфа-имя(Имя отправителя)';
$_['entry_smsclub_message']					= 'Текст смс сообщения';

$_['text_sms_status_enroute']					= 'Cообщение отправлено';
$_['text_sms_status_delivrd']					= 'Cообщение доставлено';
$_['text_sms_status_expired']					= 'Истек срок жизни, сообщение не доставлено';
$_['text_sms_status_undeliv']					= 'Невозможно доставить сообщение';
$_['text_sms_status_rejectd']					= 'Сообщение отклонено системой (черный список или же другие фильтры)';
$_['text_sms_status_default']					= 'Неизвестный статус';
$_['text_abandoned_order']						= 'Брошенные Заказы';
$_['text_column_left_abandoned_order']		= 'Брошенные Заказы <span style="position:absolute; right:14px; top:auto; margin-top:2px;" class="label label-danger">%s</span>';

//Tabs
$_['tab_general']									= 'Общие';
$_['tab_email']									= 'Email';
$_['tab_smsclub']									= 'SMS Club';

//Column
$_['column_abandoned_id']						= '№';
$_['column_customer']							= 'Покупатель';
$_['column_email']								= 'Email';
$_['column_telephone']							= 'Телефон';
$_['column_date_added']							= 'Дата добавления';
$_['column_action']								= 'Действие';
$_['column_product_name']						= 'Товар';
$_['column_product_quantity']					= 'Количество';
$_['column_product_price']						= 'Цена';
$_['column_total']								= 'Итого';

//Button
$_['button_setting']								= 'Настройки';
$_['button_send_email']							= 'Отправить сообщение на почту';
$_['button_send_sms']							= 'Отправить sms сообщение';

//Help
$_['help_status_informer']						= 'Включает или выключает информер в шапке сайта';
$_['help_status_email']							= 'Включает или выключает Возможность отправлять сообщения покупателям на почту';
$_['help_status_telegram']						= 'Включает или выключает Возможность отправлять сообщения покупателям в Telegram';
$_['help_email_subject']						= 'Доступные переменные: </br> [firstname], [lastname], [date_added]';
$_['help_email_template']						= 'Доступные переменные: </br> [firstname], [lastname], [products], [email], [telephone], [date_added]';
$_['help_token']									= 'Получить свой token можно во вкладке Профиль в личном кабинете на сайте <a target="_blank" href="https://my.smsclub.mobi/auth?id_referral=56722#tab-reg">my.smsclub.mobi/uk/profile</a>';
$_['help_originators']							= 'Создать Альфа-имена Вы можете в личном кабинете на сайте в разделе Альфа-имена <a target="_blank" href="https://my.smsclub.mobi/uk/alphanames/">my.smsclub.mobi/uk/alphanames/</a><br>Альфа-имена Обязательные для заполнения, без них отправки сообщения не будет';
$_['help_sms_message']							= 'Доступные переменные: </br> [firstname], [lastname], [email], [telephone], [products], [date_added]';

// Success
$_['text_success']								= 'Вы успешно удалили брошенный заказ!';
$_['text_success_save_setting']				= 'Вы успешно сохранили настройки';
$_['text_success_send_email_message']		= 'Вы успешно отправили сообщение на почту!';
$_['text_success_send_sms_message']			= 'Вы успешно отправили смс сообщение!';


// Error
$_['error_permission']							= 'Внимание: У вас нет прав для изменения брошенных заказов!';
$_['text_error_email']							= 'Некорректный адрес электронной почты';
$_['text_error_email_subject']				= 'Вы не заполнили тему письма!';
$_['text_error_email_template']				= 'Вы не заполнили шаблон письма!';
$_['text_error_empty_token']					= 'Токен не может быть пустым, Укажите свой токен.';
$_['text_error_invalid_token']				= 'Токен указан не верно! получите правильный Token.';
$_['text_error_src_addr']						= 'Альфа-имя обязательно для заполнрения!';
$_['text_error_send_sms_message']			= 'При отправке сообщения возникла ошибка! :(';
