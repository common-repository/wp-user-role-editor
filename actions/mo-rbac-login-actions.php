<?php

class Mo_Rbac_Login_Actions{
	
	function __construct(){
		$this->mo_rbac_start_session();
		add_action('login_message',array($this,'mo_rbac_login_message'),1,1);

		//Setting default options
		add_option( 'mo_rbac_admin_dashboard_enable', 0 );
		add_option( 'mo_rbac_notification_option_enable', 'php' );
		add_option( 'mo_rbac_register_form_firstname', 0 );
		add_option( 'mo_rbac_register_form_lastname', 0 );
		add_option( 'mo_rbac_register_form_nickname', 0 );
		add_option( 'mo_rbac_register_form_description', 0 );
		add_option( 'mo_rbac_register_form_website', 0 );
		if(get_option('mo_rbac_admin_dashboard_enable')==0){
			if( !is_admin() )		
				add_filter( 'login_redirect', array($this, 'admin_login'));	
			add_action('after_setup_theme', array($this,'mo_rbac_remove_admin_bar'));
		}
	}

	function admin_login(){
		return home_url();
	}
	
	function mo_rbac_start_session(){
		if (!session_id())
			session_start();
	}
	
	function mo_rbac_remove_admin_bar() {
		if (!current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
		}
	}
	
	function mo_rbac_login_message($message){
		if(array_key_exists('login_message',$_SESSION)){
			$message = $_SESSION['login_message'];
			unset($_SESSION['login_message']);
			return $message;
		}
	}
}
?>