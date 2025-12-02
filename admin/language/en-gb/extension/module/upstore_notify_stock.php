<?php
// Heading
$_['heading_title']                       			= '<span style="color:#000"><img style="min-width:30px; margin-right:5px;" src="view/image/upstore_icon.svg"><span class="hidden">Up</span>store - Notify When Available</span>';

// Text
$_['text_module']                         			= 'Modules';
$_['text_success']                        			= 'Settings saved successfully!';
$_['text_edit']                           			= 'Edit Module';
$_['text_yes']                         				= 'Yes';
$_['text_no']                         					= 'No';
$_['text_field_name']                       			= 'Field - Name -';
$_['text_field_telephone'] 								= 'Field - Phone -';
$_['text_button_icon_notify_stock'] 					= 'Button Icon';
$_['text_no_results']                     			= 'No results';
$_['text_model_product']	  								= 'Model: ';
$_['text_status_wait']         							= 'Pending';
$_['text_status_done']         							= 'Processed';
$_['text_in_stock']         								= 'In Stock';
$_['text_out_stock']         								= 'Out of Stock';
$_['text_mail_subject'] 									= '%s - %s is back in stock!';
$_['text_mail_html'] 										= "<p>Hello, %s!</p><p>We are glad to inform you that the product <b>%s</b> is back in stock at our store <b>%s</b>.</p><p>You can follow this <a href=\"%s\" target=\"_blank\">link</a> for more details and to purchase the product.</p><p>If you have any questions, feel free to contact us.</p><p>Best regards,<br/>The <b>%s</b> store team</p>";

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
$_['entry_send_email_status']               			= 'Send email to the administrator?';
$_['entry_notify_after_edit_product']            	= 'Send email to the customer after editing the product?';
$_['entry_email']                  						= 'Email';
$_['entry_requared_field'] 								= 'Required Field';
$_['entry_placeholder_field'] 							= 'Placeholder';
$_['entry_telephone_mask'] 								= 'Phone Mask </br>(Use 9 - example +38(099)999-99-99)';
$_['entry_popup_title'] 									= 'Popup Title';
$_['entry_popup_after_title'] 							= 'Text under Title';
$_['entry_button_notify_stock'] 							= 'Button Text';
$_['entry_customer_email_subject'] 						= 'Email Subject';
$_['entry_customer_email_html'] 							= 'Email Template';
$_['entry_name'] 												= 'Name';
$_['entry_email'] 											= 'Email';
$_['entry_telephone'] 										= 'Phone';
$_['entry_product'] 											= 'Product';
$_['entry_cron_security_key'] 							= 'Enter security key for cron job execution';
$_['entry_agree']												= 'Confirmation for form filling';
$_['help_agree'] 												= 'Require agreement confirmation when filling out the form';


// Column
$_['column_customer']                         		= 'Customer';
$_['column_product']                         		= 'Product';
$_['column_status']                   					= 'Status';
$_['column_date_added']                   			= 'Date Added';
$_['column_date_send']                   				= 'Date Sent';
$_['column_action']                   					= 'Action';


// Success
$_['success_update_status']                    		= 'You successfully changed the request status!';
$_['success_del_selected']                    		= 'You successfully deleted the selected request!';

// Error
$_['error_del_selected']                    			= 'You need to select a request to delete!';
$_['error_selected_id']                    			= 'You need to select a request to change the status!';
$_['error_permission']                    			= 'You do not have permission to edit the module!';
$_['error_email_admin']     								= 'Provide your Email! This field is required.';
$_['text_customer_email_variables'] 					= '
<div>
	<br/><b>~store_name~</b><i style="font-weight:400"> - Store Name</i>
	<br/><b>~customer_name~</b><i style="font-weight:400"> - Customer Name</i>
	<br/><b>~customer_telephone~</b><i style="font-weight:400"> - Phone</i>
	<br/><b>~customer_email~</b><i style="font-weight:400"> - Customer Email</i>
	<br/><b>~product_name~</b><i style="font-weight:400"> - Product Name</i>
	<br/><b>~product_link~</b><i style="font-weight:400"> - Product Link</i>
	<br/><b>~product_image~</b><i style="font-weight:400"> - Product Image</i>
	<br/><b>~product_model~</b><i style="font-weight:400"> - Model</i>
</div>
';
