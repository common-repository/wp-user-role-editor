<?php

function mo_rbac_show_new_registration_pages(){
	update_option ( 'mo_rbac_new_registration', 'true' );
	global $current_user;
	$mo_manager_utility = new Mo_Manager_Utility();
	$current_user = wp_get_current_user();
	?>
	<form name="f" method="post" action="" id="register-form">
		<input type="hidden" name="option" value="mo_rbac_register_customer" />
		<?php wp_nonce_field( 'rbac_customer_register', '_rbac_cust_register_nonce' ); ?>
		<div class="mo_rbac_table_layout"; style="width: 96.8%">
			

			<h3>Register with miniOrange</h3>

			<p>
				<div id="mo_rbac_register_title" class="mo_rbac_help_title">[ Why should I register? ]</a></div>
				<div hidden id="mo_rbac_register_desc" class="mo_rbac_help_desc">
					All configurations made by you are stored on your WordPress instance and all transactions made are between your site and the Service Provider(s) that you have configured. We do not track any of your transactions or store any of your data. Registration is appreciated so that we can get back to you as and when you need support.
				</div>
			</p>
			<table class="mo_rbac_settings_table">
				<tr>
					<td><b><font color="#FF0000">*</font>Email:</b></td>
					<td><input class="mo_rbac_table_textbox" type="email" name="email"
						required placeholder="person@example.com"
						value="<?php echo $current_user->user_email;?>" /></td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Website/Company Name:</b></td>
					<td><input class="mo_rbac_table_textbox" type="text" name="company"
						required placeholder="Enter website or company name" 
						value="<?php echo $_SERVER['SERVER_NAME']; ?>"/></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;First Name:</b></td>
					<td><input class="mo_rbac_table_textbox" type="text" name="fname"
						placeholder="Enter first name"
						value="<?php echo $current_user->user_firstname;?>" /></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;Last Name:</b></td>
					<td><input class="mo_rbac_table_textbox" type="text" name="lname"
						placeholder="Enter last name"
						value="<?php echo $current_user->user_lastname;?>" /></td>
				</tr>
				<tr>
					<td><b>&nbsp;&nbsp;Phone number:</b></td>
					<td><input class="mo_rbac_table_textbox" type="tel" id="phone"
						pattern="[\+]\d{11,14}|[\+]\d{1,4}[\s]\d{9,10}" name="phone"
						title="Phone with country code eg. +1xxxxxxxxxx"
						placeholder="Phone with country code eg. +1xxxxxxxxxx"
						value="<?php echo get_option('mo_rbac_admin_phone');?>" /><br/>We will call only if you need support.</td>
					<td></td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Password:</b></td>
					<td><input class="mo_rbac_table_textbox" required type="password"
						name="password" placeholder="Choose your password (Min. length 6)" /></td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
					<td><input class="mo_rbac_table_textbox" required type="password"
						name="confirmPassword" placeholder="Confirm your password" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><br /><input type="submit" name="submit" value="Next" style="width:100px;"
						class="mo_rbac_button mo_rbac_button-primary button-large" />
					<input type="button" name="login_page" value="Login Page" id="mo_rbac_login_page"style="width:100px;" class="mo_rbac_button mo_rbac_button-primary button-large" /></td>
				</tr>
			</table>
								
		</div>
	</form>
	<form name="f" method="post" action="" id="rbacgoback">
		<input type="hidden" name="option" value="mo_rbac_login_page"/>
		<?php wp_nonce_field( 'rbac_back_to_login', '_rbac_go_back_nonce' ); ?>
	</form>
	<script>
			jQuery('#mo_rbac_login_page').click(function() {
				jQuery('#rbacgoback').submit();
			});
			var text = "&nbsp;&nbsp;We will call only if you need support."
				jQuery("#phone").intlTelInput();
			

	</script>
<?php	
}

function mo_rbac_show_otp_verification(){	
?>
	<form name="f" method="post" id="otp_form" action="">
		<input type="hidden" name="option" value="mo_rbac_validate_otp" />
		<?php wp_nonce_field( 'rbac_otp_verification_show', '_rbac_otp_verification_nonce' ); ?>
			<div class="mo_rbac_table_layout">
				<table class="mo_rbac_settings_table">
					<h3>Verify Your Email</h3>
					<tr>
						<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
						<td colspan="3"><input class="mo_rbac_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:40%;"  title="Only 6 digit numbers are allowed"/>
						 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP ?</a></td>
					</tr>
					<tr><td colspan="3"></td></tr>
					<tr>
						<td>&nbsp;</td>
						<td style="width:17%">
							<input type="submit" name="submit" value="Validate OTP" class="mo_rbac_button mo_rbac_button-primary button-large" />
						</td>
	</form>
	<form name="f" method="post">
						<td style="width:18%">
						<input type="hidden" name="option" value="mo_rbac_go_back"/>
						<?php wp_nonce_field( 'rbac_go_back', '_rbac_back_nonce' ); ?>
						<input type="submit" name="submit"  value="Back" class="mo_rbac_button mo_rbac_button-primary button-large" /></td>
	</form>
	<form name="f" id="resend_otp_form" method="post" action="">
						<td>

							<input type="hidden" name="option" value="mo_rbac_resend_otp"/>
							<?php wp_nonce_field( 'rbac_otp_resend', '_rbac_otp_resend_nonce' ); ?>
						</td>
					</tr>
	</form>
				</table>
			<br>
			<hr>
			<h3>I did not receive any email with OTP . What should I do ?</h3>
	<form id="phone_verification" method="post" action="">
		<input type="hidden" name="option" value="mo_rbac_phone_verification" />
		<?php wp_nonce_field( 'rbac_phone_verify', '_rbac_phone_verify_nonce' ); ?>
		 If you cannot see an email from miniOrange in your mails, please check your <b>SPAM Folder</b>. If you don't see an email even in SPAM folder, verify your identity with our alternate method.
		 <br><br>
			<b>Enter your valid phone number here and verify your identity using one time passcode sent to your phone.</b>
			<br><br>
			<table class="mo_rbac_settings_table">
			<tr>
			<td colspan="3">
			<input class="mo_rbac_table_textbox" required  pattern="[0-9\+]{12,18}" autofocus="true" style="width:100%;" type="tel" name="phone_number" id="phone" placeholder="Enter Phone Number" value="<?php echo get_option('mo_rbac_admin_phone'); ?>" title="Enter phone number(at least 10 digits) without any space or dashes."/>
			</td>
			<td>&nbsp;&nbsp;
		<a style="cursor:pointer;" onclick="document.getElementById('phone_verification').submit();">Resend OTP ?</a>
			</td>
			</tr>
			</table>
			<br><input type="submit" value="Send OTP" class="mo_rbac_button mo_rbac_button-primary button-large" />
	</form>
	<br>
	<h3>What is an OTP ?</h3>
	<p>OTP is a one time passcode ( a series of numbers) that is sent to your email or phone number to verify that you have access to your email account or phone. </p>
	</div>
	<script>
		jQuery("#phone").intlTelInput();
	</script>
<?php	
}

function mo_rbac_show_verify_password_page($roles_url){
	global $mo_manager_utility;
	?>
	<form name="f" method="post" action="">
		<input type="hidden" name="option" value="mo_rbac_connect_verify_customer" />
		<?php wp_nonce_field( 'rbac_password_verify', '_rbac_password_verify_nonce' ); ?>
		<div class="mo_rbac_table_layout">
			
		
			<h3>Login with miniOrange</h3>
			<p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password. <a href="#rbac_forgot_password">Click here if you forgot your password?</a></b></p>
			<table class="mo_rbac_settings_table">
				<tr>
					<td><b><font color="#FF0000">*</font>Email:</b></td>
					<td><input class="mo_rbac_table_textbox" type="email" name="email"
						required placeholder="person@example.com"
						value="<?php echo get_option('mo_rbac_admin_email');?>" /></td>
				</tr>
				<td><b><font color="#FF0000">*</font>Password:</b></td>
				<td><input class="mo_rbac_table_textbox" required type="password"
					name="password" placeholder="Choose your password" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="submit"
						class="mo_rbac_button mo_rbac_button-primary button-large" value="Submit" onclick="window.location.href='admin.php?page=mo_rbac_settings&tab=roles_and_capabilities'"> 
						<input type="button" id="goBackButton" value="Registration Page"
						class="mo_rbac_button mo_rbac_button-primary button-large" />
					</td>
				</tr>
			</table>
		</div>
	</form>
	<form name="goBack" method="post" action="" id="goBacktoRegistrationPage">
		<input type="hidden" name="option" value="mo_rbac_go_back"/>
	</form>
	<form name="forgotpassword" method="post" action="" id="forgotpasswordform">
		<input type="hidden" name="option" value="mo_rbac_rbac_forgot_password"/>
		<input type="hidden" id="forgot_pass" name="email" value=""/>
		<?php wp_nonce_field( 'rbac_password_forget', '_rbac_forgot_password_nonce' ); ?>
	</form>
	<script>
		jQuery('a[href="#rbac_forgot_password"]').click(function(){
			jQuery('#forgot_pass').val(jQuery('#email').val());
			jQuery('#forgotpasswordform').submit();
		});
		jQuery('#goBackButton').click(function(){
			jQuery('#goBacktoRegistrationPage').submit();
		});
	</script>
	<?php
	
}

?>