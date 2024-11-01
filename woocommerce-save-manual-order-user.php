<?php
/*
Plugin Name: WooCommerce Save Manual Order User
Plugin URI: https://www.timoxendale.co.uk/plugins/woo-save-manual-order-user/
Description: Ads an option to the WooCommerce order actions to save the billing details as a user. Option only shows when the billing user does not already exist and has a email address, first name and last name.
Version: 1.2
Author: Tim's Solutions
Author URI: https://www.timoxendale.co.uk/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wsmou
Domain Path: /languages
WC requires at least: 3.0.0
WC tested up to: 8.0.1

WooCommerce Save Manual Order User is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WooCommerce Save Manual Order User is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with WooCommerce Save Manual Order User. If not, see https://www.gnu.org/licenses/gpl-2.0.html.

*/
	
	defined('ABSPATH') || exit;

	if(!function_exists('wsmou_add_save_billing_details_order_meta_box_action')){
		add_action('woocommerce_order_actions', 'wsmou_add_save_billing_details_order_meta_box_action');
		function wsmou_add_save_billing_details_order_meta_box_action($actions) {
			global $theorder;

			if(!($theorder->get_billing_email()) || !($theorder->get_billing_first_name()) || !($theorder->get_billing_last_name())) {
			    return $actions;
			}

			if(email_exists($theorder->get_billing_email())) {
			    return $actions;
			}
			    
			$actions['wc_save_billing_details_order_action'] = __('Save billing details as WordPress user', 'wsmou');
			return $actions;
		}
	}
		
	if(!function_exists('wsmou_save_billing_details_order_action')){
		add_action('woocommerce_order_action_wc_save_billing_details_order_action', 'wsmou_save_billing_details_order_action');
		function wsmou_save_billing_details_order_action($order) {
			if($order->get_billing_email()){
				$user_id = username_exists($order->get_billing_email());
				if(!$user_id and email_exists($order->get_billing_email()) == false){
					$random_password = wp_generate_password(12, true);
					$new_user_id = wp_create_user($order->get_billing_email(), $random_password, $order->get_billing_email());

					if($new_user_id){
						add_user_meta($new_user_id,'billing_first_name',$order->get_billing_first_name());
						add_user_meta($new_user_id,'billing_last_name',$order->get_billing_last_name());
						add_user_meta($new_user_id,'billing_company',$order->get_billing_company());
						add_user_meta($new_user_id,'billing_address_1',$order->get_billing_address_1());
						add_user_meta($new_user_id,'billing_address_2',$order->get_billing_address_2());
						add_user_meta($new_user_id,'billing_city',$order->get_billing_city());
						add_user_meta($new_user_id,'billing_postcode',$order->get_billing_postcode());
						add_user_meta($new_user_id,'billing_country',$order->get_billing_country());
						add_user_meta($new_user_id,'billing_state',$order->get_billing_state());
						add_user_meta($new_user_id,'billing_phone',$order->get_billing_phone());
						add_user_meta($new_user_id,'billing_email',$order->get_billing_email());

						wp_update_user(
							array(
								'ID' => $new_user_id, 
								'first_name' => $order->get_billing_first_name(), 
								'last_name'=> $order->get_billing_last_name(),
								'display_name'=>$order->get_billing_first_name().' '. $order->get_billing_last_name()
							)
						);
						wc_update_new_customer_past_orders($new_user_id);
					}
				}else{
					add_action('admin_notices', 'wsmou_error_user_exists');
				}
			}
		}
	}


	if(!function_exists('wsmou_error_user_exists')){
		function wsmou_error_user_exists() { ?>
			<div class="error notice-error is-dismissible">
				<p><?php _e('User was not added. A user with the email address or same username already exists!', 'wsmou'); ?></p>
			</div>
		<?php }
	}