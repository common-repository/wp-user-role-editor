<?php
/** miniOrange User Management manages your users, roles and capabilities. Create custom registration forms fields and approve users after registration. Simple and customizable with active support.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange User Manager
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
include_once dirname(__FILE__).'/mo-rbac-api.php';
class Mo_Manager_Utility{

    public $email;
    public $phone;
	private $default_customer_key = "16555";
	private $default_api_key = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
    private $plugin_name = "WP User Role Editor";
	
	
	public function mo_rbac_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
    
    public function get_hidden_phone($phone){
        $hidden_phone = 'xxxxxxx' . substr($phone,strlen($phone) - 3);
        return $hidden_phone;
    }
    
    public function rbac_is_curl_installed() {
        if  (in_array  ('curl', get_loaded_extensions()))
            return 1;
        else 
            return 0;
    }

    public function rbac_getCurPageUrl() {
        $page_url = 'http';
        
        if ((isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
            $page_url .= "s";
            
        $page_url .= "://";

        if ($_SERVER["SERVER_PORT"] != "80")
            $page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            
        else
            $page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

        return $page_url;
    }
    
    public function check_number_length($token){
        if(is_numeric($token)){
            if(strlen($token) >= 4 && strlen($token) <= 8){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    public function get_hiden_email($email){
        if(!isset($email) || trim($email)===''){
            return "";
        }
        $emailsize = strlen($email);
        $partialemail = substr($email,0,1);
        $temp = strrpos($email,"@");
        $endemail = substr($email,$temp-1,$emailsize);
        for($i=1;$i<$temp;$i++){
            $partialemail = $partialemail . 'x';
        }
        $hiddenemail = $partialemail . $endemail;
               
        return $hiddenemail;
    }

    public function is_registered(){

        $email          = get_option('mo_rbac_admin_email');
        $customer_key    = get_option('mo_rbac_admin_customer_key');
        if( ! $email || ! $customer_key || ! is_numeric( trim( $customer_key ) ) ) {
            return 0;
        } else {
            return 1;
        }
    }

    function mo_rbac_success_message() {
        $message = get_option('mo_rbac_message'); ?>
        <script> 
            jQuery(document).ready(function() { 
                var message = '<?php echo $message; ?>';
                jQuery('#mo_rbac_msgs').append("<div class='error notice is-dismissible mo_rbac_error_container'> <p class='mo_rbac_msgs'>" + message + "</p></div>");
            });
        </script>
    <?php }
    
    function mo_rbac_error_message() {
            $message = get_option('mo_rbac_message'); ?>
        <script> 
            jQuery(document).ready(function() {
                var message = '<?php echo $message; ?>';
                jQuery('#mo_rbac_msgs').append("<div class='updated notice is-dismissible mo_rbac_success_container'> <p class='mo_rbac_msgs'>" + message + "</p></div>");
            });
        </script>
    <?php }
    
    function mo_rbac_show_success_message() {
        remove_action( 'admin_notices', array( $this, 'mo_rbac_success_message') );
        add_action( 'admin_notices', array( $this, 'mo_rbac_error_message') );
    }

    function mo_rbac_show_error_message() {
        remove_action( 'admin_notices', array( $this, 'mo_rbac_error_message') );
        add_action( 'admin_notices', array( $this, 'mo_rbac_success_message') );
    }

    function get_curl_error(){
        $message="Please enable curl extension";
        return json_encode( array( "status" => 'ERROR', "message" => $message ) );
    }

    

    function rbac_create_customer(){
        $url = get_option('mo_rbac_host_name') . '/moas/rest/customer/add';
        $ch = curl_init( $url );
        if(! $this->rbac_is_curl_installed()){
            return get_curl_error();
        }
        global $current_user;
        wp_get_current_user();
        $this->email        = get_option('mo_rbac_admin_email');
        $this->phone        = get_option('mo_rbac_admin_phone');
        $password           = get_option('mo_rbac_admin_password');
		$first_name			= get_option('mo_rbac_admin_first_name');
		$last_name			= get_option('mo_rbac_admin_last_name');
		$company_name		= get_option('mo_rbac_admin_company_name');

        $fields = array(
            'companyName' => $company_name,
            'areaOfInterest' => $this->plugin_name,
            'firstname' => $first_name,
            'lastname'  => $last_name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'password'  => $password
        );
        $field_string = json_encode($fields);

        $http_header_array = (new mo_rbac_api)->rbac_header_array_basic();

        return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
    }

    function rbac_get_customer_key() {
        $url    = get_option('mo_rbac_host_name') . "/moas/rest/customer/key";
        $ch     = curl_init( $url );
        if(! $this->rbac_is_curl_installed()){
            return get_curl_error();
        }
        $email  = get_option("mo_rbac_admin_email");

        $password = get_option("mo_rbac_admin_password");

        $fields = array(
            'email'     => $email,
            'password'  => $password
        );
        $field_string = json_encode( $fields );

        $http_header_array = (new mo_rbac_api)->rbac_header_array_basic();

        return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
    }

    function rbac_check_customer() {
            $url    = get_option('mo_rbac_host_name') . "/moas/rest/customer/check-if-exists";
            $ch     = curl_init( $url );
            if(! $this->rbac_is_curl_installed()){
                return get_curl_error();
            }
            $email  = get_option("mo_rbac_admin_email");

            $fields = array(
                'email'     => $email,
            );
            $field_string = json_encode( $fields );

            $http_header_array = (new mo_rbac_api)->rbac_header_array_basic();

            return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
    }

    function rbac_send_otp_token($authType,$email='',$phone=''){
            $url = get_option('mo_rbac_host_name') . '/moas/api/auth/challenge';
            $ch = curl_init($url);
			if(! $this->rbac_is_curl_installed()){
                return get_curl_error();
            }
            if($this->mo_rbac_check_empty_or_null(get_option('mo_rbac_admin_customer_key')))
               $customer_key =  $this->default_customer_key;
            else
                $customer_key = get_option('mo_rbac_admin_customer_key');
            if($this->mo_rbac_check_empty_or_null(get_option('mo_rbac_admin_api_key')))
                $api_key =  $this->default_api_key;
            else
                $api_key =  get_option('mo_rbac_admin_api_key');

            $current_time_in_millis = round(microtime(true) * 1000);


            $string_to_hash = $customer_key . number_format($current_time_in_millis, 0, '', '') . $api_key;
            $hash_value = hash("sha512", $string_to_hash);

            $customer_key_header = "Customer-Key: " . $customer_key;
            $timestamp_header = "Timestamp: " . $current_time_in_millis;
            $authorization_header = "Authorization: " . $hash_value;
            if($authType == 'EMAIL') {
                $fields = array(
                'customerKey' => $customer_key,
                'email' => $email,
                'authType' => 'EMAIL',
                'transactionName' => $this->plugin_name
                );
            }elseif($authType == 'SMS'){
                $fields = array(
                'customerKey' => $customer_key,
                'phone' => $phone,
                'authType' => 'SMS',
                'transactionName' => $this->plugin_name
            );
            }
            $field_string = json_encode($fields);
            
            $http_header_array = (new mo_rbac_api)->rbac_header_array($customer_key, $current_time_in_millis, $hash_value);

            return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
        }

        function rbac_validate_otp_token($transactionId,$otpToken){
            $url = get_option('mo_rbac_host_name') . '/moas/api/auth/validate';
            $ch = curl_init($url);
            if(! $this->rbac_is_curl_installed()){
                return get_curl_error();
            }
            if($this->mo_rbac_check_empty_or_null(get_option('mo_rbac_admin_customer_key')))
                $customer_key =  $this->default_customer_key;
            else
                $customer_key = get_option('mo_rbac_admin_customer_key');
            if($this->mo_rbac_check_empty_or_null(get_option('mo_rbac_admin_api_key')))
                $api_key =  $this->default_api_key;
           else
                $api_key =  get_option('mo_rbac_admin_api_key');

            $username = get_option('mo_rbac_admin_email');

            $current_time_in_millis = round(microtime(true) * 1000);

            $string_to_hash = $customer_key . number_format($current_time_in_millis, 0, '', '') . $api_key;
            $hash_value = hash("sha512", $string_to_hash);

            $customer_key_header = "Customer-Key: " . $customer_key;
            $timestamp_header = "Timestamp: " . $current_time_in_millis;
            $authorization_header = "Authorization: " . $hash_value;

            $fields = '';

                $fields = array(
                    'txId' => $transactionId,
                    'token' => $otpToken,
                );

            $field_string = json_encode($fields);

            $http_header_array = (new mo_rbac_api)->rbac_header_array($customer_key, $current_time_in_millis, $hash_value);

            return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
    }

    function rbac_submit_contact_us( $email, $phone, $query ) {
            $url = get_option('mo_rbac_host_name') . '/moas/rest/customer/contact-us';

            $ch = curl_init( $url );
            if(! $this->rbac_is_curl_installed()){
                return get_curl_error();
            }
            global $current_user;
            wp_get_current_user();
            $query = '[WP User Manager] ' . $query;
            $fields = array(
                'firstName'         => $current_user->user_firstname,
                'lastName'          => $current_user->user_lastname,
                'company'           => $_SERVER['SERVER_NAME'],
                'email'             => $email,
                'phone'             => $phone,
                'query'             => $query
            );
            $field_string = json_encode( $fields );

            $http_header_array = (new mo_rbac_api)->rbac_header_array_basic();

            return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
    }
    
    function rbac_forgot_password($email){
        
        $url = get_option('mo_rbac_host_name') . '/moas/rest/customer/password-reset';
        $ch = curl_init($url);
        if(! $this->rbac_is_curl_installed()){
            return get_curl_error();
        }
        $fields = array(
            'email' => $email
        );
        
        $field_string = json_encode($fields);
        
        $http_header_array = (new mo_rbac_api)->rbac_header_array_basic();

        return (new mo_rbac_api)->rbac_make_curl_call( $url, $field_string, $http_header_array );
    }
	
	function redirect($url){
		$redirect = '<script type="text/javascript">';
		$redirect .= 'window.location = "' . $url . '"';
		$redirect .= '</script>';
		echo $redirect;
	}
 
}
?>