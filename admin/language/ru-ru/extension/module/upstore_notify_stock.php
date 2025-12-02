<?php
// Heading
$_['heading_title']                       			= '<span style="color:#000"><img style="min-width:30px; margin-right:5px;" src="view/image/upstore_icon.svg"><span class="hidden">Up</span>store - Сообщить о поступлении товара</span>';

// Text
$_['text_module']                         			= 'Модули';
$_['text_success']                        			= 'Настройки успешно сохранены!';
$_['text_edit']                           			= 'Редактировать модуль';
$_['text_yes']                         				= 'Да';
$_['text_no']                         					= 'Нет';
$_['text_field_name']                       			= 'Поле - Имя -';
$_['text_field_telephone'] 								= 'Поле - Телефон -';
$_['text_button_icon_notify_stock'] 					= 'Иконка Кнопки';
$_['text_no_results']                     			= 'Нет результатов';
$_['text_model_product']	  								= 'Модель: ';
$_['text_status_wait']         							= 'Ожидание';
$_['text_status_done']         							= 'Обработан';
$_['text_in_stock']         								= 'Есть в наличии';
$_['text_out_stock']         								= 'Нет в наличии';
$_['text_mail_subject'] 									= '%s - %s снова в продаже!';
$_['text_mail_html'] 										= "<p>Добрый день, %s!</p><p>Мы рады сообщить Вам, что товар <b>%s</b> снова в наличии в нашем магазине <b>%s</b>.</p><p>Вы можете перейти по следующей <a href=\"%s\" target=\"_blank\">ссылке</a> для подробной информации и покупки товара.</p><p>Если у Вас возникнут вопросы, пожалуйста, не стесняйтесь обращаться к нам.</p><p>С уважением,<br/>Команда магазина <b>%s</b></p>";

// Button
$_['button_change_status']                  			= 'Изменить Статус';
$_['button_delete']                   	  				= 'Удалить';

// Tabs
$_['tab_form_field']                    				= 'Поля Формы';
$_['tab_setting']                    					= 'Настройки';
$_['tab_email']                    						= 'Шаблона письма';
$_['tab_list']                            			= 'Список запросов';

// Entry
$_['entry_status']                      				= 'Статус';
$_['entry_status_field']                      		= 'Статус поля';
$_['entry_send_email_status']               			= 'Отправлять письмо на почту администратора?';
$_['entry_notify_after_edit_product']            	= 'Отправлять письмо клиенту после редактирования товара?';
$_['entry_email']                  						= 'Почта';
$_['entry_requared_field'] 								= 'Обязательно для заполнения';
$_['entry_placeholder_field'] 							= 'Заполнитель';
$_['entry_telephone_mask'] 								= 'Маска телефона </br>(Используйте 9 - пример +38(099)999-99-99)';
$_['entry_popup_title'] 									= 'Заголовок всплывающее окно';
$_['entry_popup_after_title'] 							= 'Текст под заголовком';
$_['entry_button_notify_stock'] 							= 'Текст кнопки';
$_['entry_customer_email_subject'] 						= 'Тема письма';
$_['entry_customer_email_html'] 							= 'Шаблон письма';
$_['entry_name'] 												= 'Имя';
$_['entry_email'] 											= 'Почта';
$_['entry_telephone'] 										= 'Телефон';
$_['entry_product'] 											= 'Товар';
$_['entry_cron_security_key'] 							= 'Введите ключ безопасности для выполнения cron-задачи';
$_['entry_agree']												= 'Подтверждение при заполнении формы';
$_['help_agree'] 												= 'Требовать подтверждение согласия с правилами при заполнении формы';


// Column
$_['column_customer']                         		= 'Клиент';
$_['column_product']                         		= 'Товар';
$_['column_status']                   					= 'Cтатус';
$_['column_date_added']                   			= 'Дата добавления';
$_['column_date_send']                   				= 'Дата отправки сообщения';
$_['column_action']                   					= 'Действие';


// Success
$_['success_update_status']                    		= 'Вы успешно изменили статус запроса !';
$_['success_del_selected']                    		= 'Вы успешно удалили выбранный запрос !';

// Error
$_['error_del_selected']                    			= 'Нужно выбрать запрос который нужно удалить !';
$_['error_selected_id']                    			= 'Нужно выбрать запрос в котором Вы хотели бы изменить статус !';
$_['error_permission']                    			= 'У вас нет прав для редактирования модуля!';
$_['error_email_admin']     								= 'Укажите свой Email! Это поле обязательно для заполнения';
$_['text_customer_email_variables'] 					= '
<div>
	<br/><b>~store_name~</b><i style="font-weight:400"> - Название магазина</i>
	<br/><b>~customer_name~</b><i style="font-weight:400"> - Имя покупателя</i>
	<br/><b>~customer_telephone~</b><i style="font-weight:400"> - Телефон</i>
	<br/><b>~customer_email~</b><i style="font-weight:400"> - Email Покупателя</i>
	<br/><b>~product_name~</b><i style="font-weight:400"> - Название товара</i>
	<br/><b>~product_link~</b><i style="font-weight:400"> - Ссылка на товар</i>
	<br/><b>~product_image~</b><i style="font-weight:400"> - Изображение товара</i>
	<br/><b>~product_model~</b><i style="font-weight:400"> - Модель</i>
</div>
';
