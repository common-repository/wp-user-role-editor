<?php
require('mo-rbac-registration-pages.php');
require('mo-rbac-user-profile-pages.php');
require('mo-rbac-troubleshooting-pages.php');
require('mo-rbac-licensing-pages.php');

function mo_rbac_show_plugin_settings() {
	global $mo_manager_utility, $db_queries,$wpdb;
	if( isset( $_GET[ 'tab' ]) ) {
		$active_tab = $_GET[ 'tab' ];
	 }else {
		$active_tab = 'roles_and_capabilities';
	}

	if( isset( $_GET['option'] ) && $_GET['option'] == "rbac_edit_role" )
		mo_rbac_show_edit_role_page($_GET['user_id']);
	else
		mo_rbac_show_main_settings_page($active_tab);
} 

function mo_rbac_show_main_settings_page($active_tab){
	global $mo_manager_utility;
	$help_url = add_query_arg( array('tab' => 'support'), $_SERVER['REQUEST_URI'] );
	$plans = add_query_arg( array('tab' => 'licensing'), $_SERVER['REQUEST_URI'] );
	$roles_url = add_query_arg( array('tab' => 'roles_and_capabilities'), $_SERVER['REQUEST_URI'] );
	?>
	<div class="wrap"; style="display: inline-flex;">
		<h1>WP User Role Editor<?php if($active_tab=='roles_and_capabilities'){ ?><a class="add-new-h2" href="<?php echo $help_url ?>">Support</a> <?php } ?></h1>
	</div>
	<?php mo_rbac_check_rbac_is_curl_installed() ?>
	<div id="tab" class="mo_rbac_main-tabs" >
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $active_tab == 'roles_and_capabilities' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'roles_and_capabilities'), $_SERVER['REQUEST_URI'] ); ?>">Roles and capabilities</a>
			<a class="nav-tab <?php echo $active_tab == 'troubleshooting' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'troubleshooting'), $_SERVER['REQUEST_URI'] ); ?>">Troubleshooting</a>
			<a class="nav-tab <?php echo $active_tab == 'licensing' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'licensing'), $_SERVER['REQUEST_URI'] ); ?>">Licensing Plans</a>
			<a class="nav-tab <?php echo $active_tab == 'register' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'register'), $_SERVER['REQUEST_URI'] ); ?>">Account</a>
			<a class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'support'), $_SERVER['REQUEST_URI'] ); ?>">Support</a>
			
		</h2>
	</div>

	<div id="mo_rbac_msgs"></div>
	<table style="width:100%;padding-top:12px;">
		<tr>
			<td style="vertical-align:top;width:65%;">

				<?php
					if ( $active_tab == 'register') {
						if (get_option ( 'mo_rbac_verify_customer' ) == 'true') {
							mo_rbac_show_verify_password_page($roles_url);
						}elseif (trim ( get_option ( 'mo_rbac_admin_email' ) ) != '' && trim ( get_option ( 'mo_rbac_admin_api_key' ) ) == '' && get_option ( 'mo_rbac_new_registration' ) != 'true') {
							mo_rbac_show_verify_password_page($roles_url);
						}elseif (get_option('mo_rbac_registration_status') == 'MO_OTP_DELIVERED_SUCCESS' || get_option('mo_rbac_registration_status') == 'MO_OTP_VALIDATION_FAILURE' 
								|| get_option('mo_rbac_registration_status') == 'MO_OTP_DELIVERED_FAILURE' ){
							mo_rbac_show_otp_verification();
						}elseif (!$mo_manager_utility->is_registered()) {
							delete_option ( 'password_mismatch' );
							mo_rbac_show_new_registration_pages();
						}
					}elseif ( $active_tab == 'troubleshooting' ) {
						mo_rbac_show_troubleshooting();
					}elseif ( $active_tab == 'licensing' ) {
						mo_rbac_show_licensing_info();
					}elseif ( $active_tab == 'roles_and_capabilities' ) {
						mo_rbac_show_role_cap_edit_page();
					}elseif ( $active_tab == 'support' ) {
						mo_rbac_show_plugin_support($active_tab);
					}
					if($active_tab=='register' && $mo_manager_utility->is_registered()){
						mo_rbac_show_user_profile();
					}

					
				?>
			</td>
			<td style="vertical-align:top;padding-left:1%;">
				<?php 
					if($active_tab == 'roles_and_capabilities' || $active_tab == 'support'){}
					else{
						echo mo_rbac_show_plugin_support($active_tab);
					}
				 ?>
			</td>
		</tr>
	</table>
	<?php
}


function mo_rbac_check_rbac_is_curl_installed(){
	global $mo_manager_utility;
	if(!$mo_manager_utility->rbac_is_curl_installed()){ 
	?>
		<div id="help_curl_warning_title" class="mo_rbac_title_panel">
			<p><font color="#FF0000">Warning: PHP cURL extension is not installed or disabled. <span style="color:blue">Click here</span> for instructions to enable it.</font></p>
		</div>
		<div hidden="" id="help_curl_warning_desc" class="mo_rbac_help_desc">
			<ul>
				<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Open php.ini file located under php installation folder.</li>
				<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Search for <b>extension=php_curl.dll</b> </li>
				<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Uncomment it by removing the semi-colon(<b>;</b>) in front of it.</li>
				<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Restart the Apache Server.</li>
			</ul>
			For any further queries, please <a href="mailto:info@xecurify.com">contact us</a>.								
		</div>
	<?php
	}
}

function mo_rbac_show_plugin_support($active_tab){
	global $current_user;
	wp_get_current_user();
	?>
	<div class="mo_rbac_support_layout" style="<?php if($active_tab=='support'){ echo 'width: 500px'; } else{ echo 'width: 420px'; } ?>">
		<h3>Support</h3>
			<p>Need any help? Just send us a query so we can help you.</p>
			<form method="post" action="">
				<input type="hidden" name="option" value="mo_rbac_contact_us_query_option" />
				<?php wp_nonce_field( 'rbac_support_query', '_rbac_support_query_nonce' ); ?>
				<table class="mo_rbac_settings_table">
					<tr>
						<td><input type="email" class="mo_rbac_table_contact" required placeholder="Enter your Email" name="mo_rbac_contact_us_email" value="<?php echo get_option("mo_rbac_admin_email"); ?>"></td>
					</tr>
					<tr>
						<td><input type="tel" id="contact_us_phone" pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" placeholder="Enter your phone number with country code (+1)" class="mo_rbac_table_contact" name="mo_rbac_contact_us_phone" value="<?php echo get_option('mo_rbac_admin_phone');?>"></td>
					</tr>
					<tr>
						<td><textarea class="mo_rbac_table_contact" onkeypress="mo_rbac_valid_query(this)" onkeyup="mo_rbac_valid_query(this)" placeholder="Write your query here" onblur="mo_rbac_valid_query(this)" required name="mo_rbac_contact_us_query" rows="4" style="resize: vertical;"></textarea></td>
					</tr>
				</table>
				<br>
			<input type="submit" name="submit" value="Submit Query" style="width:110px;" class="mo_rbac_button mo_rbac_button-primary button-large" />

			</form>
			<p>If you want custom features in the plugin, just drop an email to <a href="mailto:info@xecurify.com">info@xecurify.com</a>.</p>
	</div>
	</div>
	</div>
	</div>
	<script>
		jQuery("#contact_us_phone").intlTelInput();
		function mo_rbac_valid_query(f) {
			!(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
					/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
		}

		function moSharingSizeValidate(e){
			var t=parseInt(e.value.trim());t>60?e.value=60:10>t&&(e.value=10)
		}
		function moSharingSpaceValidate(e){
			var t=parseInt(e.value.trim());t>50?e.value=50:0>t&&(e.value=0)
		}
		function moLoginSizeValidate(e){
			var t=parseInt(e.value.trim());t>60?e.value=60:20>t&&(e.value=20)
		}
		function moLoginSpaceValidate(e){
			var t=parseInt(e.value.trim());t>60?e.value=60:0>t&&(e.value=0)
		}
		function moLoginWidthValidate(e){
			var t=parseInt(e.value.trim());t>1000?e.value=1000:140>t&&(e.value=140)
		}
		function moLoginHeightValidate(e){
			var t=parseInt(e.value.trim());t>50?e.value=50:35>t&&(e.value=35)
		}

	</script>
	<?php
}