<?php

class mo_rbac_api
{
	public function rbac_wp_remote_post($url, $args = array()){
		$response = wp_remote_post($url, $args);
        if(!is_wp_error($response)){
            return $response['body'];
        } else {
            $message = 'Please enable curl extension. <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_help">Click here</a> for the steps to enable curl or check Help & Troubleshooting.';

            return json_encode( array( "status" => 'ERROR', "message" => $message ) );
        }
	}

	function rbac_make_curl_call( $url, $fields, $http_header_array =array("Content-Type"=>"application/json","charset"=>"UTF-8","Authorization"=>"Basic")) {

        if ( gettype( $fields ) !== 'string' ) {
            $fields = json_encode( $fields );
        }

        $args = array(
            'method' => 'POST',
            'body' => $fields,
            'timeout' => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => $http_header_array
        );

        $response = mo_rbac_api::rbac_wp_remote_post($url, $args);
        return $response;

    }

    function rbac_header_array_basic(){
    	$currentuser=wp_get_current_user();
    	$username= $currentuser->user_login;
    	$password= get_option('mo_rbac_admin_password');
    	$headers = array(
            "Content-Type" => "application/json",
            "charset" => "UTF - 8",
            "Authorization" => "Basic". base64_encode( $username . ":" . $password )
        );
        return $headers;
    }

    function rbac_header_array($customer_key, $current_time_in_millis, $hash_value){
    	$headers=array(
    		"Content-Type: application/json",
    		"Customer-Key: " => $customer_key,
    		"Timestamp: " => $current_time_in_millis,
    		"Authorization: " => $hash_value
    	);
    	return $headers;
    }
    
}

?>