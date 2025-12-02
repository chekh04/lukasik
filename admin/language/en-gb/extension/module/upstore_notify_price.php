<?php
// Heading
$_['heading_title']                       			= '<span style="color:#000"><img style="min-width:30px; margin-right:5px;" src="view/image/upstore_icon.svg"><span class="hidden">Up</span>store - Notify about price drop, new promotions</span>';

// Text
$_['text_module']                         			= 'Modules';
$_['text_success']                        			= 'Settings have been successfully saved!';
$_['text_edit']                           			= 'Edit module';
$_['text_yes']                         				= 'Yes';
$_['text_no']                         					= 'No';
$_['text_field_name']                       			= 'Field - Name -';
$_['text_field_telephone'] 								= 'Field - Telephone -';
$_['text_no_results']                     			= 'No results';
$_['text_model_product']	  								= 'Model: ';
$_['text_status_wait']         							= 'Pending';
$_['text_status_done']         							= 'Processed';
$_['text_in_stock']         								= 'In stock';
$_['text_out_stock']         								= 'Out of stock';
$_['text_mail_subject'] 									= '%s - New price or promotion for %s!';
$_['text_mail_html'] 										= "<p>Hello, %s!</p><p>We are pleased to inform you that the price of the product <b>%s</b> has changed in our store <b>%s</b>.</p><p>You can now purchase this product at the new price! Follow this <a href=\"%s\" target=\"_blank\">link</a> for more details and to make a purchase.</p><p>If you have any questions, please don't hesitate to contact us.</p><p>Best regards,<br/>The team of the store <b>%s</b></p>";

// Button
$_['button_change_status']                  			= 'Change Status';
$_['button_delete']                   	  				= 'Delete';

// Tabs
$_['tab_form_field']                    				= 'Form Fields';
$_['tab_setting']                    					= 'Settings';
$_['tab_email']                    						= 'Email Template';
$_['tab_list']                            			= 'Request List';

// Entry
$_['entry_status']                      				= 'Status';
$_['entry_status_field']                      		= 'Field Status';
$_['entry_send_email_status']               			= 'Send email to administrator?';
$_['entry_notify_after_edit_product']            	= 'Send email to the customer after editing the product?';
$_['entry_email']                  						= 'Email';
$_['entry_requared_field'] 								= 'Required';
$_['entry_placeholder_field'] 							= 'Placeholder';
$_['entry_telephone_mask'] 								= 'Telephone mask </br>(Use 9 - example +38(099)999-99-99)';
$_['entry_popup_title'] 									= 'Popup Title';
$_['entry_popup_after_title'] 							= 'Text under title';
$_['entry_button_notify_price'] 							= 'Button text';
$_['entry_customer_email_subject'] 						= 'Email subject';
$_['entry_customer_email_html'] 							= 'Email template';
$_['entry_name'] 												= 'Name';
$_['entry_email'] 											= 'Email';
$_['entry_telephone'] 										= 'Telephone';
$_['entry_product'] 											= 'Product';
$_['entry_cron_security_key'] 							= 'Enter the security key for cron tasks';
$_['entry_agree']												= 'Consent required to fill out the form';
$_['help_agree'] 												= 'Require consent agreement with terms when filling out the form';

// Column
$_['column_customer']                         		= 'Customer';
$_['column_product']                         		= 'Product';
$_['column_current_price'] 								= 'Current price';
$_['column_request_price'] 								= 'Price at the time of request';
$_['column_send_request_price'] 							= 'Price at the time of sending';
$_['column_status']                   					= 'Status';
$_['column_date_added']                   			= 'Date added';
$_['column_date_send']                   				= 'Date of sending message';
$_['column_action']                   					= 'Action';

// Success
$_['success_update_status']                    		= 'You have successfully changed the request status!';
$_['success_del_selected']                    		= 'You have successfully deleted the selected request!';

// Error
$_['error_del_selected']                    			= 'You need to select a request to delete!';
$_['error_selected_id']                    			= 'You need to select a request to change its status!';
$_['error_permission']                    			= 'You do not have permission to edit the module!';
$_['error_email_admin']     								= 'Enter your email! This field is required.';
$_['text_customer_email_variables'] 					= '
<div>
	<br/><b>~store_name~</b><i style="font-weight:400"> - Store name</i>
	<br/><b>~customer_name~</b><i style="font-weight:400"> - Customer name</i>
	<br/><b>~customer_telephone~</b><i style="font-weight:400"> - Telephone</i>
	<br/><b>~customer_email~</b><i style="font-weight:400"> - Customer email</i>
	<br/><b>~product_name~</b><i style="font-weight:400"> - Product name</i>
	<br/><b>~price~</b><i style="font-weight:400"> - Price</i>
	<br/><b>~special~</b><i style="font-weight:400"> - Special</i>
	<br/><b>~old_price~</b><i style="font-weight:400"> - Old price</i>
	<br/><b>~old_special~</b><i style="font-weight:400"> - Old special</i>
	<br/><b>~product_link~</b><i style="font-weight:400"> - Product link</i>
	<br/><b>~product_image~</b><i style="font-weight:400"> - Product image</i>
	<br/><b>~product_model~</b><i style="font-weight:400"> - Model</i>
</div>
';
