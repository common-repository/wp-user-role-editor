jQuery(document).ready(function () {	
    $ = jQuery;

    $(".mo_rbac_title_panel").click(function () {
        $(this).next(".mo_rbac_help_desc").slideToggle(400);
    });
    
    jQuery("#mo_rbac_help_add_role").click(function () {
            jQuery("#mo_rbac_help_role_desc").slideToggle(400);
    });

    jQuery("#mo_rbac_help_add_cap").click(function () {
            jQuery("#mo_rbac_help_cap_desc").slideToggle(400);
    });
	
	jQuery("#mo_rbac_help_curl_title").click(function () {
    	jQuery("#mo_rbac_help_curl_desc").slideToggle(400);
    });

    jQuery("#mo_rbac_register_title").click(function () {
        jQuery("#mo_rbac_register_desc").slideToggle(400);
    });
	
	jQuery("#mo_rbac_help_editor").click(function () {
			jQuery("mo_rbac_help_editor_desc").slideToggle(400);
	});
	
	jQuery("#mo_rbac_help_otp_title").click(function () {
    	jQuery("#mo_rbac_help_otp_desc").slideToggle(400);
    });
	
	jQuery("#mo_rbac_question1").click(function () {
    	jQuery("#mo_rbac_question1_desc").slideToggle(400);
    });
	
	jQuery("#mo_rbac_question2").click(function () {
    	jQuery("#mo_rbac_question2_desc").slideToggle(400);
    });
	
	jQuery("#mo_rbac_question3").click(function () {
    	jQuery("#mo_rbac_question3_desc").slideToggle(400);
    });
	
	jQuery("#mo_rbac_question4").click(function () {
    	jQuery("#mo_rbac_question4_desc").slideToggle(400);
    });
    
    $("#rbac_role_edit_dropdown").change(function() {
    	$url = window.location.href;
    	$str = "&role_id";
    	if($url.indexOf($str) != -1){
    		$url = $url.substring(0,$url.indexOf($str));
    	}
    		$url = $url + "&role_id=" +$(this).val();
    	window.location.href = $url;
	});

    $(".overlay").click(function(){
        if($(this).data('action')=="add_role"){
            $(".rbac_modal_background").show();
            $("#add_role").show();
            $("#rbac_role_id").focus();
        }else if($(this).data('action')=="delete_role"){
            $(".rbac_modal_background").show();
            $("#delete_role").show();
        }else if($(this).data('action')=="change_default"){
            $(".rbac_modal_background").show();
            $("#default_role").show();
        }else if($(this).data('action')=="add_cap"){
            $(".rbac_modal_background").show();
            $("#add_cap").show();
            $("#rbac_cap_name").focus();
        }else if($(this).data('action')=="delete_cap"){
            $(".rbac_modal_background").show();
            $("#delete_cap").show();
        }else if($(this).data('action')=="rename_role"){
            $(".rbac_modal_background").show();
            $("#rename_role").show();
        }else if($(this).data('action')=="add_field"){
            $(".rbac_modal_background").show();
            $("#add_field").show();
        }
    });

    $(".cancel").click(function(){
        $(".rbac_modal_background").hide();
        $(".rbac_modal").hide();
    });

    $(".mo_rbac_close").click(function(){
        $(".rbac_modal_background").hide();
        $(".rbac_modal").hide();
    });

    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $(".rbac_modal_background").hide();
            $(".rbac_modal").hide();
        }
    });

});
