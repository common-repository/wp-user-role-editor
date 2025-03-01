<?php
/* Show Pricing Details for premium services */
function mo_rbac_show_licensing_info(){
	global $mo_manager_utility;
	$registered  = $mo_manager_utility->is_registered();
	$disabled    = !$registered ? 'disabled' : '';
	$hostname    = get_option('mo_rbac_host_name');
	$login_url   = $hostname . '/moas/login';
	$username    = get_option('mo_rbac_admin_email');
	$payment_url = $hostname . '/moas/initializepayment';

	echo'<div class="mo_rbac_table_layout"; style="width: 96.8%">';
			
	echo	'<form style="display:none;" id="mo_rbac_loginform" action="'.$login_url.'" target="_blank" method="post">
				<input type="email" name="username" value="'.$username.'" />
				<input type="text" name="redirectUrl" value="'.$payment_url.'" />
				<input type="text" name="requestOrigin" id="requestOrigin"  />
			</form>
			<script>
				function mo_rbac_upgradeform(planType){
					jQuery("#requestOrigin").val(planType);
					jQuery("#mo_rbac_loginform").submit();
				}
			</script>
			
			<table class="mo_rbac_pricing_table">
			<h2>LICENSING PLANS
				<span style="float:right">
					<input type="button" name="ok_btn" id="ok_btn" class="mo_rbac_button mo_rbac_button-primary button-large" value="OK, Got It" onclick="window.location.href=\'admin.php?page=mo_rbac_settings&tab=roles_and_capabilities\'" />
				</span>
			<h2>
			<hr>
			
			<tr style="vertical-align:top; ">
			    <td>
					<div class="mo_rbac_thumbnail mo_rbac_pricing_free_tab" style="width: 320px">
						<h3 class="mo_rbac_pricing_header">Free</h3>
							<br><p style="margin-top:28px;"></p>
						<hr>
						
						<p class="mo_rbac_pricing_text" style="margin-bottom:38px;">$0</p><br>
						
						<hr>
						
					<ul class="mo_rbac_pricing_text" style="margin-bottom:311px;">
							<li>Add Roles, Change Default Roles and Rename Roles</li>
							<li>Custom Registration Fields</li>
							<li>Manage Pending User Registration</li>
							<li>Send Confirmation Email</li>
							<li>Send Activation Link</li>
							<li>Hide/Display WP Admin Bar</li>
							<li>Add/Delete Capabilities</li>
					</ul>
						<hr>
						<p class="mo_rbac_pricing_text">Basic Support By Email<br><br></p>
					</div>
				</td>
				<td>
					<div class="mo_rbac_thumbnail mo_rbac_pricing_paid_tab" style="width: 320px">
						<h3 class="mo_rbac_pricing_header">Premium</h3>
							<h4 class="mo_rbac_pricing_sub_header">
								<a style="margin-bottom:3.8%;"  class="button mo_rbac_button-primary button-large" onclick="mo_rbac_upgradeform(\'wp_user_management_basic_plan\')" >Click here to upgrade</a> *
							</h4>
						<hr>
						
						<p class="mo_rbac_pricing_text">$79 - One Time Payment<br/>(For 1 Site)</p>
						<br>
						<hr>
						
						
					<ul class="mo_rbac_pricing_text" style="margin-bottom:18px">
							<li>Add Roles, Change Default Roles and Rename Roles</li>
							<li>Custom Registration Fields</li>
							<li>Manage Pending User Registration</li>
							<li>Send Confirmation Email</li>
							<li>Send Activation Link</li>
							<li>Hide/Display WP Admin Bar</li>
							<li>Add/Delete Capabilities</li>
							<li>Bulk Import/Export Users</li>
							<li>Approve/Deny Users via SMS</li>
							<li>Customize Email Templates</li>
							<li>Use miniOrange SMTP Gateway</li>
							<li>Restrict Page/Posts Access</li>
							<li>Restrict Content Access Role-wise</li>
							<li>Periodic Approval of users (Re-certification)</li>
							<li>Spam Protection</li>
							<li>Login Protection</li>
							<li>Registration/Contact Form Protection</li>
							<li>Two Factor Authentication (15+ Auth Methods)</li>
							
							
							
					</ul>
						<hr>
						<p class="mo_rbac_pricing_text" style="margin-bottom: 35px">Standard / Premium Support Plans Available</p>
					</div>
				</td>
			</td>
			
			</tr>
			</table>
			<div id="disclaimer" style="margin-bottom:15px;">
			<p>If you are looking for a user store or directory as well with user management. Then, go for premium plan.</p>
				<!--<h3> 
				 Steps to Upgrade to Premium Plugin -</h3>
				<p>1. You will be redirected to miniOrange Login Console. Enter your password with which you created an account with us. After that you will be redirected to payment page.</p>
				<p>2. Enter you card details and complete the payment. On successful payment completion, you will see the link to download the premium plugin.</p>
				<p>3. Once you download the premium plugin, just unzip it and replace the folder with existing plugin. <br>
				<b>Note: Do not first delete and upload again from wordpress admin panel as your already saved settings will get lost.</b></p>
				<p>4. From this point on, do not update the plugin from the Wordpress store.</p>-->
				
				*If you have any doubts regarding the licensing plans, you can mail us at <a href="mailto:info@xecurify.com"><i>info@xecurify.com</i></a> or submit a query using the <b>support form</b> on right.
				<br>
				<h3>Refund Policy -</h3>
				<p><b>At miniOrange, we want to ensure you are 100% happy with your purchase. If the premium plugin you purchased is not working as advertised and you\'ve attempted to resolve any issues with our support team, which couldn\'t get resolved then we will refund the whole amount within 10 days of the purchase. Please email us at <a href="mailto:info@xecurify.com"><i>info@xecurify.com</i></a> for any queries regarding the return policy.</b></p>
			</div>
		</div>';
}