<?php
function mo_rbac_show_troubleshooting(){
	?>
	
	<div class="mo_rbac_table_layout"; style="width: 96.8%">
		<table class="mo_rbac_help">
					<tbody><tr>
						<td class="mo_rbac_help_cell">
							<div id="mo_rbac_help_add_role" class="mo_wpns_title_panel">
								<div class="mo_rbac_help_title">How do I add a new role?</div>
							</div>
							<div hidden="" id="mo_rbac_help_role_desc" class="mo_rbac_help_desc" style="display: none;">
								<ul>
									<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Click on the <b>Roles and Capabilities</b> tab</li>
									<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;On the right section under <b>Actions</b>, click on <b>Add Role</b>.</li>
									<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Enter the Role ID<b>(needs to be unique)</b> and Role Display Name<b>(needs to be unique)</b>. </li>
									<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Under <b>Copy Capabilities From</b> you can choose a pre-defined role to clone that role's capabilities.</li>
									<li>Step 5:&nbsp;&nbsp;&nbsp;&nbsp;After adding the role, you can find it in the dropdown next to <b>SELECT ROLE</b>.</li>
								</ul>
							</div>
						</td>
					</tr><tr>
						<td class="mo_rbac_help_cell">
							<div id="mo_rbac_help_add_cap" class="mo_wpns_title_panel">
								<div class="mo_rbac_help_title">How do I add a new capability?</div>
							</div>
							<div hidden="" id="mo_rbac_help_cap_desc" class="mo_rbac_help_desc" style="display: none;">
								<ul>
									<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Click on the <b>Roles and Capabilities</b> tab</li>
									<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;On the right section under <b>Actions</b>, click on <b>Add Capability</b>.</li>
									<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Enter the Capability name(needs to be unique) and click on Add Capability in the pop-up.</li>
									<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Under <b>Custom Capabilities</b> you can find the created capability.</li>
									
								</ul>
							</div>
						</td>
					</tr><tr>
						<td class="mo_rbac_help_cell">
							<div id="mo_rbac_help_curl_title" class="mo_wpns_title_panel">
								<div class="mo_rbac_help_title">How to enable PHP cURL extension? (Pre-requisite)</div>
							</div>
							<div hidden="" id="mo_rbac_help_curl_desc" class="mo_rbac_help_desc" style="display: none;">
								<ul>
									<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Open php.ini file located under php installation folder.</li>
									<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Search for <b>extension=php_curl.dll</b>. </li>
									<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Uncomment it by removing the semi-colon(<b>;</b>) in front of it.</li>
									<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Restart the Apache Server.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr><tr>
						<td class="mo_rbac_help_cell">
							<div id="mo_rbac_help_otp_title" class="mo_wpns_title_panel">
								<div class="mo_rbac_help_title">OTP and Forgot Password</div>
							</div>
							<div hidden="" id="mo_rbac_help_otp_desc" class="mo_rbac_help_desc" style="display: none;">
								<h4><a  id="mo_rbac_question1"  >I did not receive OTP. What should I do?</a></h4>
								<div  id="mo_rbac_question1_desc">
									The OTP is sent as an email to your email address with which you have registered with miniOrange. If you can't see the email from miniOrange in your mails, please make sure to check your SPAM folder. <br/><br/>If you don't see an email even in SPAM folder, please verify your account using your mobile number. You will get an OTP on your mobile number which you need to enter on the page. If none of the above works, please contact us using the Support form on the right.
								</div>
								<hr>
								<h4><a  id="mo_rbac_question2"  >After entering OTP, I get Invalid OTP. What should I do?</a></h4>
								<div  id="mo_rbac_question2_desc">
									Use the <b>Resend OTP</b> option to get an additional OTP. Please make sure you did not enter the first OTP you recieved if you selected <b>Resend OTP</b> option to get an additional OTP. Enter the latest OTP since the previous ones expire once you click on Resend OTP. <br/><br/>If OTP sent on your email address are not working, please verify your account using your mobile number. You will get an OTP on your mobile number which you need to enter on the page. If none of the above works, please contact us using the Support form on the right.
								</div>
								<hr>
								<h4><a  id="mo_rbac_question3" >I forgot the password of my miniOrange account. How can I reset it?</a></h4>
								<div  id="mo_rbac_question3_desc">
									There are two cases according to the page you see -<br><br/>
										1. <b>Login with miniOrange</b> screen: You should click on <b>forgot password</b> link. You will get your new password on your email address which you have registered with miniOrange . Now you can login with the new password.<br><br/>
										2. <b>Register with miniOrange</b> screen: Enter your email ID and any random password in <b>password</b> and <b>confirm password</b> input box. This will redirect you to <b>Login with miniOrange</b> screen. Now follow first step.
								</div>
							</div>
						</td>
					</tr>
				</tbody></table>
	</div>
	
	
	<?php

}