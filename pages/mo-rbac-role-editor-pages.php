<?php
	function mo_rbac_show_edit_role_page($user_id){
		global $role_cap_actions,$mo_manager_utility;

		$editable_roles = get_editable_roles();
		$capabilities_list = $role_cap_actions->mo_rbac_get_capabilities_list();
		$user_data = get_userdata( $user_id );
		if(!empty($user_data)){
			$user_roles = $user_data->roles;
			$primary_role = !empty($user_roles) ? $user_roles[0] : NULL;
		}
		$help_url = admin_url('admin.php?page=mo_rbac_settings&tab=troubleshooting');
		$add_new_url = admin_url('users.php?page=mo_rbac_role_caps_settings');
		$userlist_url = admin_url('users.php');
		$disabled = !$mo_manager_utility->is_registered() ? 'disabled' : '';

		?>
		<div class="wrap">
			<h1>
				WP User Role Editor 
				<?php if ( current_user_can( 'rbac_add_role' ) ) : ?>
					<a class="add-new-h2" href="<?php echo $add_new_url ?>">Add New Role</a>
				<?php endif; ?> 
				<a class="add-new-h2" href="<?php echo $help_url ?>">Troubleshooting</a>
			</h1>
			<div id="mo_rbac_msgs" class="mo_rbac_msgs"></div>
			<div class="mo_rbac_table_layout1 mo_rbac_layout">
				<div class="mo_rbac_table_layout_container mo_rbac_baner_text">
					USER : <a href="user-edit.php?user_id=<?php echo $user_data->ID; ?>"><?php echo $user_data->user_login; ?></a> ( <?php echo $user_data->user_email ?> ) 
					<a class="mo_rbac_right" href="<?php echo $userlist_url ?>" >&#8592; Go Back</a>
				</div>
			</div>
			<div  class="mo_rbac_options_note">
				<b>NOTE: Changes in capabilities would only change for the current user. It will not change the capabilities for any other user with same role or the capabilities for the ROLE in general.</b>
			</div><br>
			<div class="mo_rbac_table_layout1 mo_rbac_left mo_rbac_layout mo_rbac_left_div">
				<div class="mo_rbac_table_layout_header mo_rbac_role_header mo_rbac_header_background">Roles</div>
				<div class="mo_rbac_table_layout_container">

					<form name="f" method="post" action="">
						<input type="hidden" name="option" value="rbac_role_edit_settings" />
						<input type="hidden" name="user_id" value="<?php echo  $user_data->ID ?>">
						<?php wp_nonce_field( 'rbac_set_roles', '_rbac_roles_nonce' ); ?>
						<span class="subheading" style="">Primary Role:</span>
						<select name='rbac_role[]' >
		  					<option disabled value="">-- Select Role --</option>
		  					<?php  wp_dropdown_roles($primary_role); ?>
		  				</select>
		  				<span style="display:block;" class="subheading">Secondary Roles:</span>
		  					<div style="padding-left:3%;">
				  				<?php foreach ($editable_roles as $role_id => $role_info) : ?>
									<?php if(strcasecmp($primary_role,$role_id)!=0){ ?>
				  							<input type='checkbox'	name ='rbac_role[]' value='<?php echo $role_id; ?>' <?php echo in_array($role_id, $user_roles) ? 'checked' : ''; ?>/>
				  								<?php echo $role_info['name']." ( ".$role_id." )"; ?><br/>
				  					<?php } ?>
								<?php endforeach; ?>
							</div>
						<br/>
						<div class="mo_rbac_center">
							<input type="submit" name="submit" value="Update Roles" class="mo_rbac_button mo_rbac_button-primary button-large" />
						</div>
					</form>
				</div>
			</div>
			<div class="mo_rbac_table_layout1 mo_rbac_left mo_rbac_right_div">
				<div class="mo_rbac_table_layout_header mo_rbac_header_background">
					<div style="margin:10px;" class="mo_rbac_left" >Capabilities</div>
					<div style="text-align:right"><input type="submit" name="submit" onclick='document.getElementById("cap_settings").submit()'
						 value="Save Capabilties" style="margin:5px;" class="mo_rbac_button mo_rbac_button-primary" /></div>
				</div>
				<div class="mo_rbac_table_layout_container">

					<form name="f" method="post" id="cap_settings" action="">
						<input type="hidden" name="option" value="rbac_cap_edit_settings" />
						<input type="hidden" name="user_id" value="<?php echo  $user_data->ID ?>">
						<?php wp_nonce_field( 'rbac_set_user_caps', '_rbac_caps_nonce' ); ?>
						<?php foreach ($capabilities_list as $key => $capability) : ?>
							<div class="capability">
								<input type='checkbox'	name ='rbac_capability[]' <?php echo $role_cap_actions->mo_rbac_check_capability($capability,$user_data)?> 
								value='<?php echo $capability; ?>'/><?php echo $capability; ?>
				  			</div>
						<?php endforeach; ?>
					</form>
				</div>
			</div>
		</div>
	<?php
	}

	function show_dropdown( $selected = '') {
		$r = '';
		$roles = wp_roles()->roles;
		$editable_roles = $roles;

		foreach ( $editable_roles as $role => $details ) {
			if($role!='administrator'){
				$name = translate_user_role( $details['name'] );
				if ( $selected == $role ) {
					$r .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
				} else {
					$r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
				}
			}
		}

		echo $r;
	}

	function count_caps_in_group($caps, $granted, $curr_role_id){
		$obj = new Mo_Rbac_Role_Cap_Actions();
		foreach ($caps as $key => $value) {
			$status= $obj->mo_rbac_check_capability_roles($key,(array)$curr_role_id);
			if($status=='checked'){
				foreach ($value as $val) {
					$granted[$val]++;
				}
			}
		}
		return $granted;
	}

	function mo_rbac_show_role_cap_edit_page(){
		global $role_cap_actions,$mo_manager_utility;
		$help_url = admin_url('admin.php?page=mo_rbac_settings&tab=troubleshooting');
		$capabilities_list = $role_cap_actions->mo_rbac_get_capabilities_list();
		$roles = get_editable_roles();
		$user_id = get_current_user_id();
		$default_role = get_option('default_role');
		$curr_role_id= array_key_exists('role_id',$_GET) && array_key_exists($_GET['role_id'],$roles) ? $_GET['role_id'] : 'author';
		$curr_role = $roles[$curr_role_id];
		$disabled = !$mo_manager_utility->is_registered() ? 'disabled' : '';
		$unused_roles = $role_cap_actions->mo_rbac_get_unused_roles();
		$custom_caps = $role_cap_actions->mo_rbac_get_user_defined_capabilities();
		$hidden = in_array($curr_role_id,array_keys($unused_roles)) ? '' : 'style="display: none;" ';
		
		$obj = new Mo_Rbac_Role_Cap_Actions();
		$caps = $obj->mo_rbac_show_group_caps();
		$granted=array();
		$classes=array('all','core','general','themes','posts','pages','plugins','users','deprecated','custom');
		foreach ($classes as $key) {
			$granted[$key]=0;
		}

		foreach ($custom_caps as $val) {
			$caps[$val]=array('custom','all');
		}
		
		
		$granted=count_caps_in_group($caps, $granted, $curr_role_id);
		$roles = get_editable_roles();

	?>
		<div class="wrap"; style="width: 1200px">
			
			<div id="mo_rbac_msgs" class="mo_rbac_msgs"></div>
				<div class="mo_rbac_table_layout1 mo_rbac_layout">
					<div class="mo_rbac_baner_text">
						<div style="margin:5px 0 0 10px;"> </div>
							<div style="text-align:left;">
								SELECT ROLE:&nbsp;
								<select name='rbac_role' id="rbac_role_edit_dropdown" required >
									<option selected disabled value="">-- Select Role --</option> 
									<?php show_dropdown($curr_role_id); ?>
								</select>	
							</div>
					</div>
				</div>
				<div style="display: inline-flex;">

					<div class="mo_rbac_table_layout1 mo_rbac_left myUL"; style="width: 30%">
						<div class="mo_rbac_table_layout_header mo_rbac_role_header mo_rbac_header_background">Group</div>
						<ul id="myUL">
	  						<li>&nbsp;&nbsp;&nbsp;&nbsp;<span id="all" style="cursor: pointer;" onclick="mo_rbac_show_group_caps('all')"> <b>All Capabilities</b> <?php echo "(".$granted['all']."/".sizeof($caps).")"; ?></span>
		    					<ul >
		    						<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="core" onclick="mo_rbac_show_group_caps('core')"> Core <?php echo "(".$granted['core']."/61)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="general" onclick="mo_rbac_show_group_caps('general')"> General <?php echo "(".$granted['general']."/12)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="themes" onclick="mo_rbac_show_group_caps('themes')">Themes <?php echo "(".$granted['themes']."/6)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="posts" onclick="mo_rbac_show_group_caps('posts')">Posts <?php echo "(".$granted['posts']."/12)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="pages" onclick="mo_rbac_show_group_caps('pages')">Pages <?php echo "(".$granted['pages']."/10)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="plugins" onclick="mo_rbac_show_group_caps('plugins')">Plugins <?php echo "(".$granted['plugins']."/5)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="users" onclick="mo_rbac_show_group_caps('users')">Users <?php echo "(".$granted['users']."/6)";  ?></li>
			      					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="capabilities" value="deprecated" onclick="mo_rbac_show_group_caps('deprecated')">Deprecated <?php echo "(".$granted['deprecated']."/12)";  ?></li>
			      				</ul>
			      			</li>
			      			<li>&nbsp;&nbsp;&nbsp;&nbsp;<span id="custom" style="cursor: pointer;" onclick="mo_rbac_show_group_caps('custom')"><b>Custom Capabilities</b> <?php echo "(".$granted['custom']."/".sizeof($custom_caps).")"; ?></span>
			      				
			      			</li>
			      		</ul>
					</div>
					<script>
						var toggler = document.getElementsByClassName("caret");
						var i;

						for (i = 0; i < toggler.length; i++) {
						  toggler[i].addEventListener("click", function() {
						    this.parentElement.querySelector(".nested").classList.toggle("active");
						    this.classList.toggle("caret-down");
						  });
						}
						document.getElementById("all").style.color = "blue";
						function mo_rbac_show_group_caps(value){
							
   							jQuery('.all').hide();
							jQuery('.'+value).show();
							
							if(value=='custom'){
								document.getElementById(value).style.color = "blue";
								document.getElementById("all").style.color = "black";
							}
							else{
								document.getElementById("all").style.color = "blue";
								document.getElementById("custom").style.color = "black";
							}

							
						}
							
						
					</script>
					<div class="mo_rbac_table_layout1 mo_rbac_left mo_rbac_layout mo_rbac_right_div"; style="width: 942px">
				<div class="mo_rbac_table_layout_header mo_rbac_header_background">
					<div style="margin:10px;" class="mo_rbac_left" >Role Capabilities</div>
					<div style="text-align:right"><input type="submit" name="submit" onclick='document.getElementById("role_cap_settings").submit()'
						 value="Save Capabilties" style="margin:5px;" class="mo_rbac_button mo_rbac_button-primary" /></div>
				</div>
				<div class="mo_rbac_table_layout_container_caps">

					<form name="f" method="post" id="role_cap_settings" action="">
						<input type="hidden" name="option" value="rbac_role_cap_edit_settings" />
						<input type="hidden" name="role_id" value="<?php echo  $curr_role_id ?>">
						<input type="hidden" name="user_id" value="<?php echo  $user_id ?>">
						<?php wp_nonce_field( 'rbac_set_roles_caps', '_rbac_roles_caps_nonce' ); ?>
						<?php 
						foreach ($caps as $key => $groups) : 
							$class_name='';
							foreach ($groups as $keys) {
								$class_name.=$keys.' ';
							}
							?>
							<div class='<?php echo $class_name; ?>'>
								<input type='checkbox' name ='rbac_capability[]' <?php echo $role_cap_actions->mo_rbac_check_capability_roles($key,(array)$curr_role_id)?> 
								value='<?php echo $key; ?>'/><?php echo $key; ?>
				  			</div>
						<?php endforeach; ?>
					</form>
				</div>
			</div>

			<div class="mo_rbac_table_layout1 mo_rbac_left mo_rbac_left_div">
				<div class="mo_rbac_table_layout_header mo_rbac_role_header mo_rbac_header_background">Actions</div>
				<div class="mo_rbac_table_layout_container mo_rbac_center">

					<input type="button" value="Add Role" data-action="add_role" class="overlay mo_rbac_button mo_rbac_button-primary rbac_action_btn" />
					<input type="button" value="Delete Role" data-action="delete_role" class="overlay mo_rbac_button mo_rbac_button-primary rbac_action_btn" />
					<input type="button" value="Add Capability" data-action="add_cap" class="overlay mo_rbac_button mo_rbac_button-primary rbac_action_btn"/>
					<input type="button" value="Delete Capability" data-action="delete_cap" class="overlay mo_rbac_button mo_rbac_button-primary rbac_action_btn" />
					<input type="button" value="Change Default Role" data-action="change_default" class="overlay mo_rbac_button mo_rbac_button-primary rbac_action_btn" />
					<input type="button" value="Rename Role" data-action="rename_role" class="overlay mo_rbac_button mo_rbac_button-primary rbac_action_btn" />
				</div>
			</div>
			
			
		</div>
		</div>
		<div hidden class="rbac_modal_background"></div>

		<div hidden id="add_role" class="rbac_modal">
			<div class="rbac_modal_dialog">
	            <div class="rbac_modal_header">
	                <span class="mo_rbac_close">Close</span>
	                <span class="mo_rbac_baner_text">ADD NEW ROLE</span>
	            </div>
	            <form name="f" method="post" action="">
	                <input type="hidden" name="option" value="rbac_add_role">
	                <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
	                <?php wp_nonce_field( 'rbac_add_roles_caps', '_rbac_add_role_nonce' ); ?>
	                <div class="rbac_modal_body">
	                	<table class="mo_rbac_display_table">
	                		<tr>
	                			<td> <label class="rbac_control_label">Role ID:</label> </td>
	                			<td> <input type="text" class="mo-form-control" id="rbac_role_id" name="rbac_role_id" autocomplete="off"> </td>
	                		</tr>
	                		<tr>
	                			<td> <label class="rbac_control_label">Role name (Display Name):</label> </td>
	                			<td> <input type="text" class="mo-form-control" name="rbac_role_name" autocomplete="off"> </td>
	                		</tr>
	                		<tr>
	                			<td> <label class="rbac_control_label">Copy Capabilities From:</label> </td>
	                			<td>
	                				 <select class="mo-form-control" name='rbac_role' id="rbac_role_edit_dropdown" required> 
							  			<option selected value="none">None</option> 
										<?php wp_dropdown_roles(); ?>
									</select>
	                			</td>
	                		</tr>
	                	</table>
	                </div>
	                <div class="rbac_modal_footer">
	                	<button type="submit" class="mo_rbac_button mo_rbac_button-primary">Add Role</button>
	                    <button type="button" class="cancel mo_rbac_button mo_rbac_button-primary">Cancel</button>
	                </div>
	            </form>
	   		</div>
	   	</div>

	   	<div hidden id="delete_role" class="rbac_modal">
			<div class="rbac_modal_dialog">
	            <div class="rbac_modal_header">
	                <span class="mo_rbac_close">Close</span>
	                <span class="mo_rbac_baner_text">DELETE ROLE</span>
	            </div>
	            <form name="f" method="post" action="">
	                <input type="hidden" name="option" value="rbac_delete_role">
	                <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
	                <?php wp_nonce_field( 'rbac_delete_roles_caps', '_rbac_delete_role_nonce' ); ?>
	                <div class="rbac_modal_body">
	                	( You can only delete roles that are not in use )
	                	<table class="mo_rbac_display_table">
	                		<tr>
	                			<td> <label class="rbac_control_label">Select Role to delete:</label> </td>
	                			<td>  
	                				<select class="mo-form-control" name='rbac_role' id="rbac_delete_role_dropdown" required <?php  ?>>
		                				<?php foreach ($unused_roles as $key => $value) : ?>
											<option value="<?php echo $key; ?>"><?php echo $value." ( ".$key." )"; ?></option> 
										<?php endforeach; ?>
									</select>
	                			</td>
	                		</tr>
	                	</table>
	                </div>
	                <div class="rbac_modal_footer">
	                	<button type="submit" class="mo_rbac_button mo_rbac_button-primary">Delete Role</button>
	                    <button type="button" class="cancel mo_rbac_button mo_rbac_button-primary">Cancel</button>
	                </div>
	            </form>
	   		</div>
	   	</div>


	   	<div hidden id="default_role" class="rbac_modal">
			<div class="rbac_modal_dialog">
	            <div class="rbac_modal_header">
	                <span class="mo_rbac_close">Close</span>
	                <span class="mo_rbac_baner_text">CHANGE DEFAULT ROLE</span>
	            </div>
	            <form name="f" method="post" action="">
	                <input type="hidden" name="option" value="rbac_change_default_role">
	                <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
	                <?php wp_nonce_field( 'rbac_default_roles_caps', '_rbac_default_role_nonce' ); ?>
	                <div class="rbac_modal_body">
	                	<table class="mo_rbac_display_table">
	                		<tr>
	                			<td> <label class="rbac_control_label">Select Default Role:</label> </td>
	                			<td>  
	                				<select class="mo-form-control" name='rbac_role' id="rbac_default_role_dropdown" required <?php ?>>
										<?php wp_dropdown_roles($default_role); ?>
									</select>
	                			</td>
	                		</tr>
	                	</table>
	                </div>
	                <div class="rbac_modal_footer">
	                	<button type="submit" class="mo_rbac_button mo_rbac_button-primary">change Default</button>
	                    <button type="button" class="cancel mo_rbac_button mo_rbac_button-primary">Cancel</button>
	                </div>
	            </form>
	   		</div>
	   	</div>


	   	<div hidden id="add_cap" class="rbac_modal">
			<div class="rbac_modal_dialog">
	            <div class="rbac_modal_header">
	                <span class="mo_rbac_close">Close</span>
	                <span class="mo_rbac_baner_text">ADD CAPABILITY</span>
	            </div>
	            <form name="f" method="post" action="">
	                <input type="hidden" name="option" value="rbac_add_cap">
	                <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
	                <?php wp_nonce_field( 'rbac_add_caps', '_rbac_add_cap_nonce' ); ?>
	                <div class="rbac_modal_body">
	                	<table class="mo_rbac_display_table">
	                		<tr>
	                			<td> <label class="rbac_control_label">Capability Name:</label> </td>
	                			<td> <input type="text" class="mo-form-control" id="rbac_cap_name" name="rbac_cap_name" autocomplete="off"> </td>
	                		</tr>
	                	</table>
	                </div>
	                <div class="rbac_modal_footer">
	                	<button type="submit" class="mo_rbac_button mo_rbac_button-primary">Add Capability</button>
	                    <button type="button" class="cancel mo_rbac_button mo_rbac_button-primary">Cancel</button>
	                </div>
	            </form>
	   		</div>
	   	</div>


	   	<div hidden id="delete_cap" class="rbac_modal">
			<div class="rbac_modal_dialog">
	            <div class="rbac_modal_header">
	                <span class="mo_rbac_close">Close</span>
	                <span class="mo_rbac_baner_text">DELETE CAPABILITY</span>
	            </div>
	            <form name="f" method="post" action="">
	                <input type="hidden" name="option" value="rbac_delete_cap">
	                <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
	                <?php wp_nonce_field( 'rbac_delete_caps', '_rbac_delete_cap_nonce' ); ?>
	                <div class="rbac_modal_body">
	                	<table class="mo_rbac_display_table">
	                		<tr>
	                			<td> <label class="rbac_control_label">Select capability to delete:</label> </td>
	                			<td>
	                				<?php if(!empty($custom_caps)){?>
	                				<select class="mo-form-control" name='rbac_cap' id="rbac_cap_dropdown" required <?php  ?>>
		                				<?php foreach ($custom_caps as $cap) : ?>
											<option value="<?php echo $cap; ?>"><?php echo $cap; ?></option> 
										<?php endforeach; ?>
									</select>
									<?php }else{?>
										<label class="rbac_control_label">No custom Capability has been set.</label>
									<?php }?>
	                			</td>
	                		</tr>
	                	</table>
	                </div>
	                <div class="rbac_modal_footer">
	                	<button type="submit" class="mo_rbac_button mo_rbac_button-primary">Delete Capability</button>
	                    <button type="button" class="cancel mo_rbac_button mo_rbac_button-primary">Cancel</button>
	                </div>
	            </form>
	   		</div>
	   	</div>


	   	<div hidden id="rename_role" class="rbac_modal">
			<div class="rbac_modal_dialog">
	            <div class="rbac_modal_header">
	                <span class="mo_rbac_close">Close</span>
	                <span class="mo_rbac_baner_text">Rename Role</span>
	            </div>
	            <form name="f" method="post" action="">
	                <input type="hidden" name="option" value="rbac_rename_role">
	                <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
	                <input type="hidden" name="role_id" value="<?php echo $curr_role_id ?>">
	                <?php wp_nonce_field( 'rbac_rename_role_caps', '_rbac_rename_role_nonce' ); ?>
	                <div class="rbac_modal_body">
	                	<table class="mo_rbac_display_table">
	                		<tr>
	                			<td> <label class="rbac_control_label">Role ID:</label> </td>
	                			<td> <label class="rbac_control_label"> <?php echo $curr_role_id; ?></label> </td>
	                		</tr>
	                		<tr>
	                			<td> <label class="rbac_control_label">Role Name:</label> </td>
	                			<td><input type="text" class="mo-form-control" value="<?php echo $curr_role['name']; ?>" name="rbac_role_name" autocomplete="off"> </td>
	                		</tr>
	                	</table>
	                </div>
	                <div class="rbac_modal_footer">
	                	<button type="submit" class="mo_rbac_button mo_rbac_button-primary">Rename Role</button>
	                    <button type="button" class="cancel mo_rbac_button mo_rbac_button-primary">Cancel</button>
	                </div>
	            </form>
	   		</div>
	   	</div>
	<?php
	}