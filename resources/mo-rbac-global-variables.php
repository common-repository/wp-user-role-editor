<?php
	class Mo_Rbac_Global_Variables{
		function __construct() {
			global $mo_manager_utility,$role_cap_actions,$sign_up_actions,$db_queries,$notification_actions,$login_actions,$registration,$template,$default_template,$custom_fields_actions;
			$mo_manager_utility = new Mo_Manager_Utility();
			$role_cap_actions = new Mo_Rbac_Role_Cap_Actions();
			$db_queries = new Mo_Db_Queries();
			$login_actions = new Mo_Rbac_Login_Actions();
			$registration = new Mo_Rbac_Registration_Actions();
			$template = new Mo_Rbac_Database_Setup();

		}
	}
?>