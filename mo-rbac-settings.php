<?php
/**
* Plugin Name:WP User Role Editor (Manage User Roles, Capabilities, Membership)
* Plugin URI: http://miniorange.com
* Description:WP User Role Editor plugin manages your users, roles and capabilities. Create custom registration forms fields and approve users after registration. Simple and customizable with active support.
* Version: 1.0.0
* Author: miniOrange
* Author URI: http://miniorange.com
* License: GPL2
*/

require('pages/mo-rbac-main-pages.php');
require('pages/mo-rbac-role-editor-pages.php');
require('resources/mo-rbac-utility.php');
require('resources/mo-rbac-global-variables.php');
require('actions/mo-rbac-role-editor-actions.php');
require('resources/mo-rbac-db-queries.php');
require('actions/mo-rbac-login-actions.php');
require('actions/mo-rbac-registration-actions.php');
require('mo-rbac-table-setup.php');

class Mo_Rbac_Manager_Plugin{

	function __construct() {
		global $pluginDir,$template;
		new Mo_Rbac_Global_Variables();
		add_action( 'admin_menu', array( $this, 'mo_rbac_menu' ) );
		add_filter( 'plugin_action_links', array($this, 'mo_rbac_plugin_actions'), 10, 2 );
		add_action( 'admin_init',  array( $this, 'mo_rbac_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'mo_rbac_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'mo_rbac_settings_script' ) );
		add_action( 'enqueue_scripts', array( $this, 'mo_rbac_settings_style' ) );
		add_action( 'enqueue_scripts', array( $this, 'mo_rbac_settings_script' ) );
		register_activation_hook( __FILE__, array( $this, 'mo_rbac_activate' ));
		register_deactivation_hook(__FILE__, array( $this, 'mo_rbac_deactivate'));
		register_activation_hook(__FILE__, array( $template, 'setup_fields_table'));
		register_deactivation_hook(__FILE__, array( $template, 'setup_fields_table'));
		add_option( 'field_type', 'textbox');
		add_option( 'mo_rbac_send_email_enable', 1);
         
	
		global $sign_up_actions,$role_cap_actions,$mo_manager_utility, $custom_fields_actions;
		add_filter( 'user_row_actions', array( $role_cap_actions, 'mo_rbac_show_edit_action_link'), 10, 2);
		add_filter( 'set-screen-option', array($role_cap_actions, 'mo_rbac_signup_set_option'), 10, 3);
		
		remove_action( 'admin_notices', array( $this, 'mo_rbac_success_message') );
	    remove_action( 'admin_notices', array( $this, 'mo_rbac_error_message') );
		
		$pluginDir = plugin_dir_path(__FILE__);
	}	
	
	function mo_rbac_menu() {
		$page = add_menu_page( 'WP User Role Editor' . __( 'Configure Role Editor Settings', 'mo_rbac_settings' ), 'WP User Role Editor', 'administrator',
		'mo_rbac_settings', array( $this, 'mo_rbac_options' ),plugin_dir_url(__FILE__) . 'includes/images/miniorange.png');

		$page = add_submenu_page('users.php', 'Roles & Capabilities', 'Roles & Capabilities', 'administrator',
		'mo_rbac_role_caps_settings', array( $this, 'mo_rbac_role_cap_settings' ));

	}

	function mo_rbac_plugin_actions( $links, $file ) {
	 	if( $file == 'miniorange-user-manager/mo-rbac-settings.php' && function_exists( "admin_url" ) ) {
			$settings_link = '<a href="' . admin_url( 'tools.php?page=mo_rbac_settings' ) . '">' . __('Settings') . '</a>';
			array_unshift( $links, $settings_link ); // before other links
		}
		return $links;
	}

	function mo_rbac_options() {
		mo_rbac_show_plugin_settings();
	}

	function mo_rbac_role_cap_settings(){
		mo_rbac_show_role_cap_edit_page();
	}

	function mo_rbac_settings_style() {
		wp_enqueue_style( 'mo_rbac_admin_settings_style', plugins_url('includes/css/mo-rbac-style.css?version=1.0', __FILE__));
		wp_enqueue_style( 'mo_rbac_admin_settings_phone_style', plugins_url('includes/css/phone.css', __FILE__));
	}

	function mo_rbac_settings_script() {
		wp_enqueue_script( 'mo_rbac_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ));
		wp_enqueue_script( 'mo_rbac_admin_settings_script', plugins_url('includes/js/settings.js?version=1.0', __FILE__ ), array('jquery'));
	}

	function mo_rbac_activate(){
		update_option( 'mo_rbac_host_name', 'https://login.xecurify.com' );
		add_rewrite_rule( '^user/(.+)','index.php?user=$matches[1]','top' );
		add_rewrite_rule( '^admin/(.+)','index.php?admin=$matches[1]','top' );
		flush_rewrite_rules();
		
		$dbSetup = new Mo_Rbac_Database_Setup();
		if(!get_option('mo_rbac_version')){
			update_option('mo_rbac_version', '1.0.0' );
			$dbSetup->setup_signups_table();
			$dbSetup->setup_fields_table();
			$dbSetup->fields_insert_default();
		}
		
	}
	
	function mo_rbac_deactivate() {
		delete_option('mo_rbac_host_name');
		delete_option('mo_rbac_transactionId');
		delete_option('mo_rbac_admin_password');
		delete_option('mo_rbac_registration_status');
		delete_option('mo_rbac_admin_phone');
		delete_option('mo_rbac_new_registration');
		delete_option('mo_rbac_admin_customer_key');
		delete_option('mo_rbac_admin_api_key');
		delete_option('mo_rbac_customer_token');
		delete_option('mo_rbac_verify_customer');
		delete_option('mo_rbac_message');
		flush_rewrite_rules();
	}

	function mo_rbac_admin_init(){
		global $template;
		
				if ( current_user_can( 'manage_options' )){
			global $role_cap_actions,$sign_up_actions,$mo_manager_utility,$notification_actions;
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_role_edit_settings" )
				$role_cap_actions->mo_rbac_save_role_settings($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_cap_edit_settings" )
				$role_cap_actions->mo_rbac_update_user_capabilties($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_role_cap_edit_settings" )
				$role_cap_actions->mo_rbac_update_role_capabilities($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_add_role" )
				$role_cap_actions->mo_rbac_add_role($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_delete_role" )
				$role_cap_actions->mo_rbac_delete_role($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_change_default_role" )
				$role_cap_actions->mo_rbac_change_default_role($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_add_cap" )
				$role_cap_actions->mo_rbac_add_custom_capabilities($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_delete_cap" )
				$role_cap_actions->mo_rbac_delete_custom_capabilities($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_capability_type_option" )
				$role_cap_actions->mo_rbac_show_group_caps($_POST);
			if( isset( $_POST['option'] ) && $_POST['option'] == "rbac_rename_role" )
				$role_cap_actions->mo_rbac_rename_role($_POST);
		}

		global $registration,$wpdb;
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_register_customer" )
			$registration->mo_rbac_register_customer($_POST);
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_login_page" )
			$registration->mo_rbac_login_page($_POST);
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_validate_otp" )
			$registration->mo_rbac_validate_otp($_POST);	
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_connect_verify_customer" )
			$registration->mo_rbac_verify_customer($_POST);	
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_go_back" )
			$registration->mo_rbac_go_back($_POST);
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_resend_otp" )
			$registration->mo_rbac_resend_otp($_POST);	
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_phone_verification" )
			$registration->mo_rbac_phone_verification($_POST);
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_rbac_forgot_password" )
			$registration->mo_rbac_rbac_forgot_password($_POST);
		if( isset( $_POST['option'] ) && $_POST['option'] == "mo_rbac_contact_us_query_option" )
			$registration->mo_rbac_send_contact_us_query($_POST);	
	}
}

new Mo_Rbac_Manager_Plugin;
?>