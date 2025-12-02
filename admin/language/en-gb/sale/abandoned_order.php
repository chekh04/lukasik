<?php
// Heading
$_['heading_title'] 							= 'Abandoned Orders';
$_['heading_title_setting'] 				= 'Settings';

// Text
$_['text_list'] 								= 'List of Abandoned Orders';
$_['text_modal_title'] 						= 'Abandoned Order Information';
$_['text_model'] 								= 'Product Code: ';
$_['text_customer_info'] 					= 'Customer Information';
$_['text_products'] 							= 'Products';
$_['text_orders'] 							= 'Orders with this email or phone number';
$_['text_qty'] 								= 'pcs.';
$_['text_send_message'] 					= 'You have sent a message ';
$_['text_loading'] 							= 'Sending...';
$_['text_smsclub_balance'] 				= 'Account Balance: ';

// Entry
$_['entry_customer'] 						= 'Customer';
$_['entry_status'] 							= 'Status';
$_['entry_date_added'] 						= 'Date Added';
$_['entry_email_subject'] 					= 'Email Subject';
$_['entry_email_template'] 				= 'Email Template';
$_['entry_smsclub_token'] 					= 'Token';
$_['entry_src_addr'] 						= 'Alpha Name (Sender Name)';
$_['entry_smsclub_message'] 				= 'SMS Message Text';

$_['text_sms_status_enroute'] 			= 'Message sent';
$_['text_sms_status_delivrd'] 			= 'Message delivered';
$_['text_sms_status_expired'] 			= 'Message expired, not delivered';
$_['text_sms_status_undeliv'] 			= 'Message undeliverable';
$_['text_sms_status_rejectd'] 			= 'Message rejected by system (blacklist or other filters)';
$_['text_sms_status_default'] 			= 'Unknown status';
$_['text_abandoned_order'] 				= 'Abandoned Orders';
$_['text_column_left_abandoned_order'] = 'Abandoned Orders <span style="position:absolute; right:14px; top:auto; margin-top:2px;" class="label label-danger">%s</span>';

// Tabs
$_['tab_general'] 							= 'General';
$_['tab_email'] 								= 'Email';
$_['tab_smsclub'] 							= 'SMS Club';

// Column
$_['column_abandoned_id'] 					= 'ID';
$_['column_customer'] 						= 'Customer';
$_['column_email'] 							= 'Email';
$_['column_telephone'] 						= 'Phone';
$_['column_date_added'] 					= 'Date Added';
$_['column_action'] 							= 'Action';
$_['column_product_name']					= 'Product';
$_['column_product_quantity'] 			= 'Quantity';
$_['column_product_price'] 				= 'Price';
$_['column_total'] 							= 'Total';

// Button
$_['button_setting'] 						= 'Settings';
$_['button_send_email'] 					= 'Send Email Message';
$_['button_send_sms'] 						= 'Send SMS Message';

// Help
$_['help_status_informer'] 				= 'Enable or disable the informer in the site header';
$_['help_status_email'] 					= 'Enable or disable the ability to send messages to customers via email';
$_['help_status_telegram'] 				= 'Enable or disable the ability to send messages to customers via Telegram';
$_['help_email_subject'] 					= 'Available variables: </br> [firstname], [lastname], [date_added]';
$_['help_email_template'] 					= 'Available variables: </br> [firstname], [lastname], [products], [email], [telephone], [date_added]';
$_['help_token'] 								= 'You can get your token in the Profile tab of your personal account on the website <a target="_blank" href="https://my.smsclub.mobi/auth?id_referral=56722#tab-reg">my.smsclub.mobi/uk/profile</a>';
$_['help_originators'] 						= 'You can create Alpha Names in your personal account on the website in the Alpha Names section <a target="_blank" href="https://my.smsclub.mobi/uk/alphanames/">my.smsclub.mobi/uk/alphanames/</a><br>Alpha Names are mandatory,without them, the message won\'t be sent';
$_['help_sms_message'] 						= 'Available variables: </br> [firstname], [lastname], [email], [telephone], [products], [date_added]';

// Success
$_['text_success'] 							= 'You have successfully deleted the abandoned order!';
$_['text_success_save_setting'] 			= 'You have successfully saved the settings';
$_['text_success_send_email_message'] 	= 'You have successfully sent an email message!';
$_['text_success_send_sms_message'] 	= 'You have successfully sent an SMS message!';

// Error
$_['error_permission'] 						= 'Warning: You do not have permission to modify abandoned orders!';
$_['text_error_email'] 						= 'Invalid email address';
$_['text_error_email_subject'] 			= 'You did not fill in the email subject!';
$_['text_error_email_template'] 			= 'You did not fill in the email template!';
$_['text_error_empty_token'] 				= 'Token cannot be empty. Please provide your token.';
$_['text_error_invalid_token'] 			= 'The token is invalid. Please obtain the correct Token.';
$_['text_error_src_addr'] 					= 'Alpha Name is mandatory!';
$_['text_error_send_sms_message'] 		= 'An error occurred while sending the message! :(';

