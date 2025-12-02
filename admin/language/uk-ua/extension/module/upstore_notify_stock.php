<?php
// Heading
$_['heading_title']                       			= '<span style="color:#000"><img style="min-width:30px; margin-right:5px;" src="view/image/upstore_icon.svg"><span class="hidden">Up</span>store - Повідомити про наявність товару</span>';

// Text
$_['text_module']                         			= 'Модулі';
$_['text_success']                        			= 'Налаштування успішно збережено!';
$_['text_edit']                           			= 'Редагувати модуль';
$_['text_yes']                         				= 'Так';
$_['text_no']                         					= 'Ні';
$_['text_field_name']                       			= 'Поле - Ім’я -';
$_['text_field_telephone'] 								= 'Поле - Телефон -';
$_['text_button_icon_notify_stock'] 					= 'Іконка кнопки';
$_['text_no_results']                     			= 'Немає результатів';
$_['text_model_product']	  								= 'Модель: ';
$_['text_status_wait']         							= 'Очікування';
$_['text_status_done']         							= 'Оброблено';
$_['text_in_stock']         								= 'Є в наявності';
$_['text_out_stock']         								= 'Немає в наявності';
$_['text_mail_subject'] 									= '%s - %s знову в продажу!';
$_['text_mail_html'] 										= "<p>Добрий день, %s!</p><p>Ми раді повідомити, що товар <b>%s</b> знову доступний у нашому магазині <b>%s</b>.</p><p>Ви можете перейти за наступним <a href=\"%s\" target=\"_blank\">посиланням</a> для детальної інформації та покупки товару.</p><p>Якщо у вас виникнуть запитання, будь ласка, звертайтеся до нас.</p><p>З повагою,<br/>Команда магазину <b>%s</b></p>";

// Button
$_['button_change_status']                  			= 'Змінити статус';
$_['button_delete']                   	  				= 'Видалити';

// Tabs
$_['tab_form_field']                    				= 'Поля форми';
$_['tab_setting']                    					= 'Налаштування';
$_['tab_email']                    						= 'Шаблон листа';
$_['tab_list']                            			= 'Список запитів';

// Entry
$_['entry_status']                      				= 'Статус';
$_['entry_status_field']                      		= 'Статус поля';
$_['entry_send_email_status']               			= 'Надсилати листа на пошту адміністратора?';
$_['entry_notify_after_edit_product']            	= 'Надсилати лист клієнту після редагування товару?';
$_['entry_email']                  						= 'Електронна пошта';
$_['entry_requared_field'] 								= 'Обов’язкове для заповнення';
$_['entry_placeholder_field'] 							= 'Заповнювач';
$_['entry_telephone_mask'] 								= 'Маска телефону </br>(Використовуйте 9 - приклад +38(099)999-99-99)';
$_['entry_popup_title'] 									= 'Заголовок спливаючого вікна';
$_['entry_popup_after_title'] 							= 'Текст під заголовком';
$_['entry_button_notify_stock'] 							= 'Текст кнопки';
$_['entry_customer_email_subject'] 						= 'Тема листа';
$_['entry_customer_email_html'] 							= 'Шаблон листа';
$_['entry_name'] 												= 'Ім’я';
$_['entry_email'] 											= 'Електронна пошта';
$_['entry_telephone'] 										= 'Телефон';
$_['entry_product'] 											= 'Товар';
$_['entry_cron_security_key'] 							= 'Введіть ключ безпеки для виконання cron-завдання';
$_['entry_agree']												= 'Підтвердження при заповненні форми';
$_['help_agree'] 												= 'Вимагати підтвердження згоди з правилами при заповненні форми';

// Column
$_['column_customer']                         		= 'Клієнт';
$_['column_product']                         		= 'Товар';
$_['column_status']                   					= 'Статус';
$_['column_date_added']                   			= 'Дата додавання';
$_['column_date_send']                   				= 'Дата відправлення повідомлення';
$_['column_action']                   					= 'Дія';

// Success
$_['success_update_status']                    		= 'Ви успішно змінили статус запиту!';
$_['success_del_selected']                    		= 'Ви успішно видалили вибраний запит!';

// Error
$_['error_del_selected']                    			= 'Необхідно вибрати запит для видалення!';
$_['error_selected_id']                    			= 'Необхідно вибрати запит для зміни статусу!';
$_['error_permission']                    			= 'У вас немає прав для редагування модуля!';
$_['error_email_admin']     								= 'Вкажіть свій Email! Це поле обов’язкове для заповнення';
$_['text_customer_email_variables'] 					= '
<div>
	<br/><b>~store_name~</b><i style="font-weight:400"> - Назва магазину</i>
	<br/><b>~customer_name~</b><i style="font-weight:400"> - Ім’я клієнта</i>
	<br/><b>~customer_telephone~</b><i style="font-weight:400"> - Телефон</i>
	<br/><b>~customer_email~</b><i style="font-weight:400"> - Email клієнта</i>
	<br/><b>~product_name~</b><i style="font-weight:400"> - Назва товару</i>
	<br/><b>~product_link~</b><i style="font-weight:400"> - Посилання на товар</i>
	<br/><b>~product_image~</b><i style="font-weight:400"> - Зображення товару</i>
	<br/><b>~product_model~</b><i style="font-weight:400"> - Модель</i>
</div>
';

