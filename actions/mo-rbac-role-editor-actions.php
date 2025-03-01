<?php
	class Mo_Rbac_Role_Cap_Actions{
		
		private $predefined_roles = array("administrator","editor","author","contributor","subscriber");

		private $predefined_capabilities = array();

		private function rbac_get_predefined_roles(){
    		return $this->predefined_roles;
    	}

    	private function rbac_get_predefined_capabilities(){
    		return $this->predefined_capabilities;
    	}

    	private function mo_rbac_check_role_associated_to_user($role){
    		$role_in_use = get_users(array('role' => $role, 'number' => 1));
        	return empty($role_in_use) ? false : true;
    	}

    	private function mo_rbac_check_if_default_role($role){
    		return get_option('default_role') == $role ? true : false;
    	}

    	public function mo_rbac_get_user_defined_capabilities(){
    		return unserialize(get_option('rbac_custom_caps')) ? unserialize(get_option('rbac_custom_caps')) : array();
    	}

		public function mo_rbac_save_role_settings($POSTED){
			global $mo_manager_utility;

			if (!current_user_can( 'edit_users', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_roles_nonce'], 'rbac_set_roles' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}

			$user = get_user_by('id', $POSTED['user_id']);
			$user->remove_all_caps();
			$roles = get_editable_roles();
			$new_roles = array_key_exists('rbac_role', $POSTED) ? (array) $POSTED['rbac_role'] : array();
			$new_roles = array_intersect( $new_roles, array_keys( $roles ) );
			$user_roles = array_intersect( array_values( $user->roles ), array_keys( $roles ) );

			if($new_roles){
				$add_roles = array_diff( $new_roles, $user_roles );
				foreach ($add_roles as $role) {
					$user->add_role($role);
				}
			}

			$user->update_user_level_from_caps();

			update_option('mo_rbac_message', 'User Role changed Succesfully.' );
			$mo_manager_utility->mo_rbac_show_success_message();
		}

		public function mo_rbac_update_user_capabilties($POSTED){
			global $mo_manager_utility;

			if (!current_user_can( 'edit_users', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_caps_nonce'], 'rbac_set_user_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}

			$user = get_user_by('id', $POSTED['user_id']);
			$capabilities = array_key_exists('rbac_capability',$POSTED) ? (array) $POSTED['rbac_capability'] : array();
			$user_roles = $user->roles;
			$user->remove_all_caps();
			foreach ($user_roles as $role) {
				$user->add_role($role);
			}
			$user->update_user_level_from_caps();
			if (!empty($capabilities)) {
	            foreach ($capabilities as $capability) {
	                $user->add_cap($capability);
	            }
	        }
	        update_option('mo_rbac_message', 'User Capabilities Saved Succesfully.');
			$mo_manager_utility->mo_rbac_show_success_message();
		}

		public function mo_rbac_update_role_capabilities($POSTED){
			global $mo_manager_utility;
			$flag=0;
			if (current_user_can( 'rbac_edit_roles', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_roles_caps_nonce'], 'rbac_set_roles_caps' ) ) {
				$flag=1;
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			$role_id = array_key_exists('role_id',$POSTED) ? $POSTED['role_id'] : '';
			$capabilities = array_key_exists('rbac_capability',$POSTED) ? (array) $POSTED['rbac_capability'] : array();

			if($role_id==""){
				$flag=1;
				update_option('mo_rbac_message', 'An Error Occurred: Invalid Role Name' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			if(empty($capabilities)){
				$flag=1;
				update_option('mo_rbac_message', 'An Error Occurred: Capabilities cannot be empty' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}

			$role = get_role($role_id);
			foreach ($capabilities as $capability){
				$role->add_cap($capability);
			}

			$all_capabilities = $this->mo_rbac_get_capabilities_from_roles((array)$role_id);
			
			$unselect_capabilities = array_diff($all_capabilities,$capabilities);
			if(!empty($unselect_capabilities)){
                foreach($unselect_capabilities as $unselect_capability) {
                	$role->remove_cap($unselect_capability);
                }

            }
            if ($flag==0) {
            	update_option('mo_rbac_message', 'Changes Saved Succesfully' );
				$mo_manager_utility->mo_rbac_show_success_message();
				return;
            }
		}

		public function mo_rbac_add_role($POSTED){
			global $mo_manager_utility;
			if (current_user_can( 'rbac_add_role', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_add_role_nonce'], 'rbac_add_roles_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}

			$role_id = sanitize_text_field($POSTED['rbac_role_id']);
			$role_id = strtolower($role_id);
        	$role_name =sanitize_text_field($POSTED['rbac_role_name']);
        	$all_roles = get_editable_roles();

        	if (preg_match("/^[a-zA-Z0-9-_]+$/", $role_id) != 1) {
	            update_option('mo_rbac_message', 'Invalid Role ID Format. Role ID can contain character, numbers and underscore only. It should start with an alphabet. Please fill the role ID accordingly.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
	        }elseif($mo_manager_utility->mo_rbac_check_empty_or_null($role_id) || $mo_manager_utility->mo_rbac_check_empty_or_null($role_name)){
	        	update_option('mo_rbac_message', 'Role ID or Name cannot be Null.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
	        }elseif(array_key_exists($role_id,$all_roles)){
	        	update_option('mo_rbac_message', 'Role already exists with that ID.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
	        }elseif(!ctype_alpha($role_id[0]) || !ctype_alpha($role_name[0])){
	        	update_option('mo_rbac_message', 'Role ID and Role Name should start with alphabet' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
	        }else{
				$wp_roles = new WP_Roles();
				$all_role_names = $wp_roles->get_names();
				foreach($all_role_names as $role_id_name => $display_name){
					if($display_name == $role_name){
						update_option('mo_rbac_message', 'Role name(Display name) already exists. Please choose different role name.' );
						$mo_manager_utility->mo_rbac_show_error_message();
						return;
					}
				}
				
	        	$copy_role_caps = trim($POSTED['rbac_role']);
	        	$caps = ($copy_role_caps != '' && isset($all_roles[$copy_role_caps])) ? $all_roles[$copy_role_caps]['capabilities'] : array();
	        	$new_role = add_role($role_id, $role_name, $caps);
	        	if(!$new_role){
	        		update_option('mo_rbac_message', 'An Error Occurred while Saving new Role.' );
				$mo_manager_utility->mo_rbac_show_error_message();
					return;
	        	}
				$mo_manager_utility = new Mo_Manager_Utility();
	        	$curr_url = $mo_manager_utility->rbac_getCurPageUrl();
	        	$str = '&role_id=';
	        	if(strpos($curr_url,$str)){
	        		$curr_url = substr($curr_url,0,strpos($curr_url,$str));
	        	}
	        	$curr_url = $curr_url . $str . $new_role->name;
	        	update_option('mo_rbac_message', 'New Role created Succesfully' );
				$mo_manager_utility->mo_rbac_show_success_message();
	        }
		}

		public function mo_rbac_change_default_role($POSTED){
			global $mo_manager_utility;

			if (current_user_can( 'rbac_change_default_role', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_default_role_nonce'], 'rbac_default_roles_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			$all_roles = get_editable_roles();
			if(in_array($POSTED['rbac_role'],array_keys($all_roles))){
				update_option('default_role',$POSTED['rbac_role']);
			} 
			update_option('mo_rbac_message', 'Default Role Changed Succesfully' );
			$mo_manager_utility->mo_rbac_show_success_message();
		}

		public function mo_rbac_delete_role($POSTED){
			global $mo_manager_utility;

			if (current_user_can( 'rbac_delete_role', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_delete_role_nonce'], 'rbac_delete_roles_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			$role = $POSTED['rbac_role'];
			if (!empty($role)) {
                if(in_array($role,array_keys($this->mo_rbac_get_unused_roles()))){
                    remove_role($role);
                }
	            update_option('mo_rbac_message', 'Role deleted Succesfully.' );
				$mo_manager_utility->mo_rbac_show_success_message();
	        } else {
	            update_option('mo_rbac_message', 'Please select an unused role to delete.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
	        }
		}

		public function mo_rbac_add_custom_capabilities($POSTED){
			global $mo_manager_utility;

			if (current_user_can( 'rbac_add_custom_cap', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_add_cap_nonce'], 'rbac_add_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			$cap = sanitize_text_field($POSTED['rbac_cap_name']);
			$val=ord($cap);
			
			if(preg_match("/^[a-zA-Z0-9-_]+$/", $cap) == 1){
				$custom_caps = $this->mo_rbac_get_user_defined_capabilities();
				$admin_role = get_role('administrator');

				if(array_key_exists($cap,$admin_role->capabilities)){
					update_option('mo_rbac_message', 'Capability already exists' );
					$mo_manager_utility->mo_rbac_show_error_message();
					return;
				}

				$admin_role->add_cap($cap);
				if (!in_array($cap, $custom_caps)) {
	                array_push($custom_caps,$cap);
	            }

	            update_option('rbac_custom_caps', serialize($custom_caps));
	            update_option('mo_rbac_message', 'Capability Created Successfully' );
				$mo_manager_utility->mo_rbac_show_success_message();
				return;
			}
			else{
				update_option('mo_rbac_message', 'Capability name can contain alphabet,number and underscore only. Please fill the capability name accordingly.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
		}

		public function mo_rbac_delete_custom_capabilities($POSTED){
			global $mo_manager_utility;

			if (current_user_can( 'rbac_delete_custom_cap', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_delete_cap_nonce'], 'rbac_delete_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			$cap = isset($POSTED['rbac_cap']) ? $POSTED['rbac_cap'] : null;
			if($mo_manager_utility->mo_rbac_check_empty_or_null($cap)){
				update_option('mo_rbac_message', 'Please select the custom capability to delete.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
			$custom_caps = $this->mo_rbac_get_user_defined_capabilities();
			$all_roles = get_editable_roles();
			if (in_array($cap, $custom_caps)) {
				foreach ($all_roles as $key => $value) {
			        $role = get_role($key);
			        $role->remove_cap($cap);
			        unset($role);
			    }
			}
			unset($custom_caps[array_search($cap, $custom_caps)]);
			update_option('rbac_custom_caps', serialize($custom_caps));
            update_option('mo_rbac_message', 'Capability Deleted Successfully' );
			$mo_manager_utility->mo_rbac_show_success_message();
			return;
		}

		public function mo_rbac_rename_role($POSTED){
			global $mo_manager_utility;
			if (current_user_can( 'rbac_rename_role', $POSTED['user_id'] ) || !wp_verify_nonce( $POSTED['_rbac_rename_role_nonce'], 'rbac_rename_role_caps' ) ) {
				update_option('mo_rbac_message', 'You are not allowed to perform this action.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}

			$role_id = $POSTED['role_id'];
			$new_role_name = sanitize_text_field($POSTED['rbac_role_name']);
			if(preg_match("/^[a-zA-Z0-9-_]+$/", $new_role_name) == 1){
				$wp_roles = new WP_Roles();
				$old_role_name = $wp_roles->roles[$role_id]['name'];
	  			$all_roles = wp_roles()->roles;
	  			wp_roles()->roles[$role_id]['name']=$new_role_name;
	     		update_option( $wp_roles->role_key, wp_roles()->roles);
				update_option('mo_rbac_message', 'Successfully renamed role name from '.$old_role_name.' to '.$new_role_name.'.');
				$mo_manager_utility->mo_rbac_show_success_message();
				return;
			}
			else{
				update_option('mo_rbac_message', 'Role name should contain alphabet,number or underscore.' );
				$mo_manager_utility->mo_rbac_show_error_message();
				return;
			}
		}

		public function mo_rbac_get_capabilities_list() {
	        $built_in_capabilities = array();
	        $capability_list = $this->mo_rbac_show_group_caps();
	        foreach ($capability_list as $capability => $group) {
	        		if(!in_array($capability,$built_in_capabilities))
	        			array_push($built_in_capabilities, $capability);
	        	}
	        
	        return $built_in_capabilities;
	    }

		public function mo_rbac_check_capability($capability,$user){
			$status = '';
			$cap_from_roles = $this->mo_rbac_get_capabilities_from_roles($user->roles);
	        if (in_array($capability, array_keys($user->allcaps)))
	            $status = 'checked';
	        if (in_array($capability, $cap_from_roles))
	        	$status .= ' disabled';
	        return $status;
		}
		
		public function mo_rbac_check_capability_roles($capability,$role){
			$status = '';
			$cap_from_roles = $this->mo_rbac_get_capabilities_from_roles($role);
	        if (in_array($capability, $cap_from_roles)){
	        	$status .= 'checked';
	        }
	        return $status;
		}

		public function mo_rbac_get_capabilities_from_roles($roles) {
	        $all_roles = get_editable_roles();
	        $user_caps_from_roles = array();
	        if (!empty($roles)) {
	            foreach ($roles as $role) {
	                if (isset($all_roles[$role])) {
	                    $user_caps_from_roles = array_merge($user_caps_from_roles, array_keys($all_roles[$role]['capabilities']));
	                }
	            }
	        }
	      	return array_unique($user_caps_from_roles);
    	}

    	public function mo_rbac_get_unused_roles(){
    		$all_roles = get_editable_roles();
    		$unused_roles = array();
    		foreach ($all_roles as $key => $value) {
				if (in_array($key, $this->predefined_roles) || $this->mo_rbac_check_role_associated_to_user($key) === true || $this->mo_rbac_check_if_default_role($key) === true ){
				 	continue;
				}

				$unused_roles[$key] = $value['name'];
    		}
    		return $unused_roles;
    	}

    	public function mo_rbac_show_edit_action_link($actions, $user_object){
			$actions['roles'] = "<a href='" . admin_url( "users.php?page=mo_rbac_settings&option=rbac_edit_role&user_id=$user_object->ID") . "'>" . __( 'Edit Role', 'wpum' ) . "</a>";
			if($user_object->roles[0]=='administrator'){
				$actions['roles']='';
			}
			return $actions;
		}

		function mo_rbac_signup_set_option($status, $option, $value) {
		    if ( 'wpum_signups_per_page' == $option ) return $value;
		    return $status;
		}

		public function mo_rbac_show_group_caps(){
			
			$caps = array();
	        $caps['switch_themes'] = array('core', 'themes','all');
	        $caps['edit_themes'] = array('core', 'themes','all');
	        $caps['activate_plugins'] = array('core', 'plugins','all');
	        $caps['edit_plugins'] = array('core', 'plugins','all');
	        $caps['edit_users'] = array('core', 'users','all');
	        $caps['edit_files'] = array('core', 'deprecated','all');
	        $caps['manage_options'] = array('core', 'general','all');
	        $caps['moderate_comments'] = array('core', 'posts', 'general','all');
	        $caps['manage_categories'] = array('core', 'posts', 'general','all');
	        $caps['manage_links'] = array('core', 'general','all');
	        $caps['upload_files'] = array('core', 'general','all'); 
	        $caps['import'] = array('core', 'general','all');
	        $caps['unfiltered_html'] = array('core','general','all');
	        $caps['edit_posts'] = array('core', 'posts','all');
	        $caps['edit_others_posts'] = array('core', 'posts','all');
	        $caps['edit_published_posts'] = array('core', 'posts','all');
	        $caps['publish_posts'] = array('core', 'posts','all');
	        $caps['edit_pages'] = array('core', 'pages','all');
	        $caps['read'] = array('core', 'general','all');
	        $caps['level_10'] = array('core', 'deprecated','all');
	        $caps['level_9'] = array('core', 'deprecated','all');
	        $caps['level_8'] = array('core', 'deprecated','all');
	        $caps['level_7'] = array('core', 'deprecated','all');
	        $caps['level_6'] = array('core', 'deprecated','all');
	        $caps['level_5'] = array('core', 'deprecated','all');
	        $caps['level_4'] = array('core', 'deprecated','all');
	        $caps['level_3'] = array('core', 'deprecated','all');
	        $caps['level_2'] = array('core', 'deprecated','all');
	        $caps['level_1'] = array('core', 'deprecated','all');
	        $caps['level_0'] = array('core', 'deprecated','all');
	        $caps['edit_others_pages'] = array('core', 'pages','all');
	        $caps['edit_published_pages'] = array('core', 'pages','all');
	        $caps['publish_pages'] = array('core', 'pages','all');
	        $caps['delete_pages'] = array('core', 'pages','all');
	        $caps['delete_others_pages'] = array('core', 'pages','all');
	        $caps['delete_published_pages'] = array('core', 'pages','all');
	        $caps['delete_posts'] = array('core', 'posts','all');
	        $caps['delete_others_posts'] = array('core', 'posts','all');
	        $caps['delete_published_posts'] = array('core', 'posts','all');
	        $caps['delete_private_posts'] = array('core', 'posts','all');
	        $caps['edit_private_posts'] = array('core', 'posts','all');
	        $caps['read_private_posts'] = array('core', 'posts','all');
	        $caps['delete_private_pages'] = array('core', 'pages','all');
	        $caps['edit_private_pages'] = array('core', 'pages','all');
	        $caps['read_private_pages'] = array('core', 'pages','all');
	        $caps['unfiltered_upload'] = array('core', 'general','all');
	        $caps['edit_dashboard'] = array('core', 'general','all');
	        $caps['update_plugins'] = array('core', 'plugins','all');
	        $caps['delete_plugins'] = array('core', 'plugins','all');
	        $caps['install_plugins'] = array('core', 'plugins','all');
	        $caps['update_themes'] = array('core', 'themes','all');
	        $caps['install_themes'] = array('core', 'themes','all');
	        $caps['update_core'] = array('core', 'general','all');
	        $caps['list_users'] = array('core', 'users','all');
	        $caps['remove_users'] = array('core', 'users','all');
	        $caps['promote_users'] = array('core', 'users','all');
	        $caps['edit_theme_options'] = array('core', 'themes','all');
	        $caps['delete_themes'] = array('core', 'themes','all');
	        $caps['export'] = array('core', 'general','all');
	        $caps['delete_users'] = array('core', 'users','all');
	        $caps['create_users'] = array('core', 'users','all');
	        
	        return $caps;
	        
		}
	}
?>