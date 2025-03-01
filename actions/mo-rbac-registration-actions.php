<?php
class Mo_Rbac_Registration_Actions{
	
	function mo_rbac_get_current_customer(){
		global $mo_manager_utility;
		$content = $mo_manager_utility->rbac_get_customer_key();
		$customer_key = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option('mo_rbac_admin_customer_key', $customer_key['id'] );
			update_option('mo_rbac_admin_api_key', $customer_key['apiKey'] );
			update_option('mo_rbac_customer_token', $customer_key['token'] );
			update_option('mo_rbac_admin_password', '' );
			update_option('mo_rbac_message', 'Your account has been retrieved successfully.' );
			delete_option('mo_rbac_verify_customer');
			delete_option('mo_rbac_new_registration');
			$mo_manager_utility->mo_rbac_show_success_message();
		} else {
			update_option('mo_rbac_message', 'You already have an account with miniOrange. Please enter a valid password.');
			update_option('mo_rbac_verify_customer', 'true');
			delete_option('mo_rbac_new_registration');
			$mo_manager_utility->mo_rbac_show_error_message();
		}
	}

	function mo_rbac_rbac_create_customer(){
		delete_option('mo_rbac_sms_otp_count');
		delete_option('mo_rbac_email_otp_count');
		global $mo_manager_utility;
		$customer_key = json_decode( $mo_manager_utility->rbac_create_customer(), true );
		if( strcasecmp( $customer_key['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0 ) {
			$this->mo_rbac_get_current_customer();
		} elseif( strcasecmp( $customer_key['status'], 'SUCCESS' ) == 0 ) {
			update_option('mo_rbac_admin_customer_key', $customer_key['id'] );
			update_option('mo_rbac_admin_api_key', $customer_key['apiKey'] );
			update_option('mo_rbac_customer_token', $customer_key['token'] );
			update_option('mo_rbac_admin_password', '');
			update_option('mo_rbac_message', 'Registration complete!');
			update_option('mo_rbac_registration_status','mo_rbac_REGISTRATION_COMPLETE');
			update_option('mo_rbac_customer_email_transactions_remaining',10);
			update_option('mo_rbac_customer_phone_transactions_remaining',10);
			update_option('mo_rbac_version',1.0);
			delete_option('mo_rbac_verify_customer');
			delete_option('mo_rbac_new_registration');
			$mo_manager_utility->mo_rbac_show_success_message();
			header('Location: admin.php?page=mo_rbac_settings&tab=settings');
		}
		update_option('mo_rbac_admin_password', '');
	}
	
	function mo_rbac_register_customer($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_cust_register_nonce'], 'rbac_customer_register')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$company = '';
		$first_name = '';
		$last_name = '';
		$email = '';
		$phone = '';
		$password = '';
		$confirm_password = '';
		$illegal = "#$%^*()+=[]';,/{}|:<>?~";
		$illegal = $illegal . '"';
		
		if($mo_manager_utility->mo_rbac_check_empty_or_null( $POSTED['email'] ) ||$mo_manager_utility->mo_rbac_check_empty_or_null( $POSTED['password'] ) ||$mo_manager_utility->mo_rbac_check_empty_or_null( $POSTED['confirmPassword'] ) ) {
			update_option( 'mo_rbac_message', 'Email, Password and Confirm Password are required fields. Please enter valid entries.');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} elseif( strlen( $POSTED['password'] ) < 6 || strlen( $POSTED['confirmPassword'] ) < 6){	//check password is of minimum length 6
			update_option( 'mo_rbac_message', 'Choose a password with minimum length 6.qqqqqqqqqqqq');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} elseif(strpbrk($POSTED['email'],$illegal)) {
			update_option( 'mo_rbac_message', 'Please match the format of Email. No special characters are allowed.');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} else {
			$company = sanitize_text_field($_POST['company']);
			$first_name = sanitize_text_field($_POST['fname']);
			$last_name = sanitize_text_field($_POST['lname']);
			$email = sanitize_email( $POSTED['email'] );
			$phone = sanitize_text_field( $POSTED['phone'] );
			$password = sanitize_text_field( $POSTED['password'] );
			$confirm_password = sanitize_text_field( $POSTED['confirmPassword'] );
		}

		update_option( 'mo_rbac_admin_company_name', $company);
		update_option( 'mo_rbac_admin_first_name', $first_name);
		update_option( 'mo_rbac_admin_last_name', $last_name);
		update_option( 'mo_rbac_admin_email', $email );
		update_option( 'mo_rbac_admin_phone', $phone );
		if( strcmp( $password, $confirm_password) == 0 ) {
			update_option( 'mo_rbac_admin_password', $password );
			$content = json_decode($mo_manager_utility->rbac_check_customer(), true);
			if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
				$content = json_decode($mo_manager_utility->rbac_send_otp_token('EMAIL',get_option('mo_rbac_admin_email')), true);
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					if(get_option('mo_rbac_email_otp_count')){
						update_option('mo_rbac_email_otp_count',get_option('mo_rbac_email_otp_count') + 1);
						update_option('mo_rbac_message', 'Another One Time Passcode has been sent <b>( ' . get_option('mo_rbac_email_otp_count') . ' )</b> for verification to ' . get_option('mo_rbac_admin_email'));
					}else{
						update_option('mo_rbac_message', ' A passcode is sent to ' . get_option('mo_rbac_admin_email') . '. Please enter the otp here to verify your email.');
						update_option('mo_rbac_email_otp_count',1 );
					}
					update_option('mo_rbac_transactionId',$content['txId']);
					update_option('mo_rbac_registration_status','MO_OTP_DELIVERED_SUCCESS');
					$mo_manager_utility->mo_rbac_show_success_message();
				}else{
					update_option('mo_rbac_message','There was an error in sending email. Please click on Resend OTP to try again.');
					update_option('mo_rbac_registration_status','MO_OTP_DELIVERED_FAILURE');
					$mo_manager_utility->mo_rbac_show_error_message();
				}
			}else{
					$this->mo_rbac_get_current_customer();
			}
		} else {
			update_option( 'mo_rbac_message', 'Passwords do not match.');
			delete_option('mo_rbac_verify_customer');
			$mo_manager_utility->mo_rbac_show_error_message();
		}

	}

	function mo_rbac_validate_otp($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_otp_verification_nonce'], 'rbac_otp_verification_show')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$otp_token = '';
		global $mo_manager_utility;
		if($mo_manager_utility->mo_rbac_check_empty_or_null( $POSTED['otp_token'] ) ) {
			update_option( 'mo_rbac_message', 'Please enter a value in OTP field.');
			update_option('mo_rbac_registration_status','MO_OTP_VALIDATION_FAILURE');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} elseif(!preg_match('/^[0-9]*$/', $POSTED['otp_token'])) {
			update_option( 'mo_rbac_message', 'Please enter a valid value in OTP field.');
			update_option('mo_rbac_registration_status','MO_OTP_VALIDATION_FAILURE');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} else{
			$otp_token = sanitize_text_field( $POSTED['otp_token'] );
		}

		$content = json_decode($mo_manager_utility->rbac_validate_otp_token(get_option('mo_rbac_transactionId'), $otp_token ),true);

		if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				$this->mo_rbac_rbac_create_customer();
		}else{
			update_option( 'mo_rbac_message','Invalid one time passcode. Please enter a valid passcode.');
			update_option('mo_rbac_registration_status','MO_OTP_VALIDATION_FAILURE');
			$mo_manager_utility->mo_rbac_show_error_message();
		}
	}

	function mo_rbac_verify_customer($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_password_verify_nonce'], 'rbac_password_verify')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$email = '';
		$password = '';
		$illegal = "#$%^*()+=[]';,/{}|:<>?~";
		$illegal = $illegal . '"';
		if($mo_manager_utility->mo_rbac_check_empty_or_null( $POSTED['email'] ) ||$mo_manager_utility->mo_rbac_check_empty_or_null( $POSTED['password'] ) ) {
			update_option( 'mo_rbac_message', 'All the fields are required. Please enter valid entries.');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} elseif(strpbrk($POSTED['email'],$illegal)) {
			update_option( 'mo_rbac_message', 'Please match the format of Email. No special characters are allowed.');
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		} else{
			$email = sanitize_email( $POSTED['email'] );
			$password = sanitize_text_field( $POSTED['password'] );
		}
		update_option( 'mo_rbac_admin_email', $email );
		update_option( 'mo_rbac_admin_password', $password );
		$content = $mo_manager_utility->rbac_get_customer_key();
		$customer_key = json_decode( $content, true );
		if( json_last_error() == JSON_ERROR_NONE ) {
			update_option( 'mo_rbac_admin_customer_key', $customer_key['id'] );
			update_option( 'mo_rbac_admin_api_key', $customer_key['apiKey'] );
			update_option( 'mo_rbac_customer_token', $customer_key['token'] );
			update_option( 'mo_rbac_admin_phone', $customer_key['phone'] );
			update_option( 'mo_rbac_admin_password', '');
			update_option( 'mo_rbac_message', 'Your account has been retrieved successfully.');
			delete_option( 'mo_rbac_verify_customer');
			$mo_manager_utility->mo_rbac_show_success_message();
		} else {
			update_option( 'mo_rbac_message', 'Invalid username or password. Please try again.');
			$mo_manager_utility->mo_rbac_show_error_message();
		}
		update_option('mo_rbac_admin_password', '');
	}
	
	function mo_rbac_rbac_forgot_password($POSTED)	{
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_forgot_password_nonce'], 'rbac_password_forget')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$email = get_option('mo_rbac_admin_email');
			if( $mo_manager_utility->mo_rbac_check_empty_or_null( $email ) ) {
				if( $mo_manager_utility->mo_rbac_check_empty_or_null( $_POST['email'] ) ) {
					update_option( 'mo_rbac_message', 'No email provided. Please enter your email below to reset password.');
					$mo_manager_utility->mo_rbac_show_error_message();
					return;
				} else {
					$email = $_POST['email'];
				} 
			}
			$customer = new Mo_Manager_Utility();
			$content = json_decode($customer->rbac_forgot_password($email),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0){
				update_option( 'mo_rbac_message','You password has been reset successfully. Please enter the new password sent to your registered mail here.');
				$mo_manager_utility->mo_rbac_show_success_message();
			}else{
				update_option( 'mo_rbac_message','An error occurred while processing your request. Please make sure you are registered with miniOrange with the given email address.');
				$mo_manager_utility->mo_rbac_show_error_message();
			}	
	}

	function mo_rbac_go_back($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_back_nonce'], 'rbac_go_back')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		update_option('mo_rbac_registration_status','');	
		delete_option('mo_rbac_new_registration');
		delete_option('mo_rbac_verify_customer' ) ;
		delete_option('mo_rbac_admin_email');
		delete_option('mo_rbac_sms_otp_count');
		delete_option('mo_rbac_email_otp_count');
	}
	
	function mo_rbac_login_page($POSTED)	{
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_go_back_nonce'], 'rbac_back_to_login')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		update_option('mo_rbac_verify_customer','true');
	}
	
	function mo_rbac_resend_otp($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_otp_resend_nonce'], 'rbac_otp_resend')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$content = json_decode($mo_manager_utility->rbac_send_otp_token('EMAIL',get_option('mo_rbac_admin_email')), true);
		if(strcasecmp($content['status'], 'SUCCESS') == 0) {
			if(get_option('mo_rbac_email_otp_count')){
				update_option('mo_rbac_email_otp_count',get_option('mo_rbac_email_otp_count') + 1);
				update_option('mo_rbac_message', 'Another One Time Passcode has been sent <b>( ' . get_option('mo_rbac_email_otp_count') . ' )</b> for verification to ' . get_option('mo_rbac_admin_email'));
			}else{
				
				update_option( 'mo_rbac_message', ' A passcode is sent to ' . get_option('mo_rbac_admin_email') . '. Please enter the otp here to verify your email.');
				update_option('mo_rbac_email_otp_count',1);
			}
			update_option('mo_rbac_transactionId',$content['txId']);
			update_option('mo_rbac_registration_status','MO_OTP_DELIVERED_SUCCESS');

			$mo_manager_utility->mo_rbac_show_success_message();				
		}else{
			update_option('mo_rbac_message','There was an error in sending email. Please click on Resend OTP to try again.');
			update_option('mo_rbac_registration_status','MO_OTP_DELIVERED_FAILURE');
			$mo_manager_utility->mo_rbac_show_error_message();
		}
	}
	
	function mo_rbac_phone_verification($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_phone_verify_nonce'], 'rbac_phone_verify')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$phone = sanitize_text_field($POSTED['phone_number']);
		$phone = str_replace(' ', '', $phone);
		update_option('mo_rbac_admin_phone',$phone);
		$auth_type = 'SMS';
		$send_otp_response = json_decode($mo_manager_utility->rbac_send_otp_token($auth_type,'',$phone),true);
		if(strcasecmp($send_otp_response['status'], 'SUCCESS') == 0){
			update_option('mo_rbac_transactionId',$send_otp_response['txId']);
			update_option( 'mo_rbac_registration_status','MO_OTP_DELIVERED_SUCCESS');
			if(get_option('mo_rbac_sms_otp_count')){
				update_option('mo_rbac_sms_otp_count',get_option('mo_rbac_sms_otp_count') + 1);
				update_option('mo_rbac_message', 'Another One Time Passcode has been sent <b>( ' . get_option('mo_rbac_sms_otp_count') . ' )</b> for verification to ' . $phone);
			}else{
					update_option('mo_rbac_message', 'One Time Passcode has been sent for verification to ' . $phone);
					update_option('mo_rbac_sms_otp_count',1);
			}
			$mo_manager_utility->mo_rbac_show_success_message();
		}else{
			update_option('mo_rbac_message','There was an error in sending sms. Please click on Resend OTP link next to phone number textbox.');
			update_option('mo_rbac_registration_status','MO_OTP_DELIVERED_FAILURE');
			$mo_manager_utility->mo_rbac_show_error_message();
		}
	}

	function mo_rbac_send_contact_us_query($POSTED){
		global $mo_manager_utility;
		if(!wp_verify_nonce($POSTED['_rbac_support_query_nonce'], 'rbac_support_query')){
			update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
			$mo_manager_utility->mo_rbac_show_error_message();
			return;
		}
		$email = sanitize_text_field($POSTED['mo_rbac_contact_us_email']);
		$phone = sanitize_text_field($POSTED['mo_rbac_contact_us_phone']);
		$query = sanitize_text_field($POSTED['mo_rbac_contact_us_query']);
		if ( $mo_manager_utility->mo_rbac_check_empty_or_null( $email ) || $mo_manager_utility->mo_rbac_check_empty_or_null( $query ) ) {
			update_option('mo_rbac_message', 'Please fill up Email and Query fields to submit your query.');
			$mo_manager_utility->mo_rbac_show_error_message();
		} else {
			$submited = $mo_manager_utility->rbac_submit_contact_us( $email, $phone, $query );
			if ( $submited == false ) {
				update_option('mo_rbac_message', 'Your query could not be submitted. Please try again.');
				$mo_manager_utility->mo_rbac_show_error_message();
			} else {
				update_option('mo_rbac_message', 'Thanks for getting in touch! We shall get back to you shortly.');
				$mo_manager_utility->mo_rbac_show_success_message();
			}
		}
	}

}
?>