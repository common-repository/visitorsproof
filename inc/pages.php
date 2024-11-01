<?php
global $vpchr;
$visitors_proof_enabled = get_option('visitors_proof_enabled');

$errors = array();
if($_POST){
    $errors = new WP_Error();
    $vp_post_page = isset($_POST['vp_post_page']) ? sanitize_text_field($_POST['vp_post_page']) : false;
    if ($vp_post_page == VISITORS_PROOF_PAGE_SETTINGS) {
        $vp_settings = esc_sql($_POST['visitors_proof_settings']);
        $vp_settings['display_seconds'] = absint($vp_settings['display_seconds']);
        $vp_settings['interval_seconds'] = absint($vp_settings['interval_seconds']);
        if(!$vp_settings['display_seconds']) $errors->add('display_seconds', __( 'Please enter notification display seconds', 'visitorsproof' ));
		if(!$vp_settings['interval_seconds']) $errors->add('interval_seconds', __( 'Please enter notification interval seconds', 'visitorsproof' ));
        if(!$vp_settings['theme']) $errors->add('theme', __( 'Please choose notification theme', 'visitorsproof' ));
        if(!$vp_settings['position']) $errors->add('position', __( 'Please choose notification position', 'visitorsproof' ));
        if(!$vp_settings['entrance_animation']) $errors->add('entrance_animation', __( 'Please choose notification entrance animation', 'visitorsproof' ));
        if(!$vp_settings['exit_animation']) $errors->add('exit_animation', __( 'Please choose notification exit animation', 'visitorsproof' ));
	    if (empty($errors->errors)){
	        $vp_settings['theme'] = absint($vp_settings['theme']);
	        $vp_settings['position'] = sanitize_text_field($vp_settings['position']);
	        $vp_settings['entrance_animation'] = sanitize_text_field($vp_settings['entrance_animation']);
	        $vp_settings['exit_animation'] = sanitize_text_field($vp_settings['exit_animation']);
	       
	        if($vp_settings['display_seconds'] < 5) $vp_settings['display_seconds'] = 5;
	        if($vp_settings['interval_seconds'] < 5) $vp_settings['interval_seconds'] = 5;
	        $vp_settings['loop'] = isset($vp_settings['loop']) ? 1 : 0;
	        $vp_settings['random_theme'] = 0;
	        $vp_settings['clear_data'] = isset($vp_settings['clear_data']) ? 1 : 0;
		    update_option('visitors_proof_settings', json_encode($vp_settings));
		    visitors_proof_redirect('?page=' . VISITORS_PROOF_PAGE_SETTINGS . '&success=1');
	    }
    }else if ($vp_post_page == VISITORS_PROOF_PAGE_NOTIFICATIONS) {
        $vp_ordering = array_map('absint', $_POST['vp_ordering']);
        $vp_options = esc_sql($_POST['vp_options']);
        foreach ($vp_ordering as $index => $n_id) {
    		$vp_o = array('ordering' => ($index+1));
    		if (isset($vp_options[$n_id]) && $vp_options[$n_id]){
	    		$fields = $wpdb->get_var("SELECT options FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE id = '$n_id'");
	    		$fields = json_decode($fields);
	    		foreach ($vp_options[$n_id] as $ck => $cv) {
	    		    $ck = sanitize_text_field($ck);
	    		    $cv = sanitize_text_field($cv);
	    			$fields->fields->{$ck}->value = $cv;
	    		}
    			$vp_o['options'] = json_encode($fields);
    		}
    		$vp_o['status'] = isset($_POST['vp_n_enabled'][$n_id]) && $_POST['vp_n_enabled'][$n_id] == 1 ? 1 : 0;
    		$wpdb->update($wpdb->prefix . VISITORS_PROOF_TABLE_NOTIFICATIONS, $vp_o, array('id' => $n_id));
    	}
    	visitors_proof_redirect('?page=' . VISITORS_PROOF_PAGE_NOTIFICATIONS . '&success=1');
    }else if ($vp_post_page == VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS) {
    	$vp_cn = $_POST['vp_cn'];
    	$vp_cn['content'] = wp_kses_post($vp_cn['content']);
    	$vp_cn['content'] = trim($vp_cn['content']);
		$l_content = strtolower($vp_cn['content']);
    	
	    $unknown = array('$', 'rs.', 'purchase', 'sale', 'bought', 'buy');
	    $c_error = false;
	    foreach ($unknown as $u) {
	        if (strpos($l_content, $u) !== false) {
	            $c_error = true;
	            break;
	        }
	    }
	    if(preg_match("/\&.\d+;/", $vp_cn['content']) || $c_error) $errors->add('content', __( 'Currency Symbols, sales related words are not allowed in custom notifications', 'visitorsproof' ));
    	
    	$vp_cn['icon'] = absint($vp_cn['icon']);
    	$vp_cn['rules']['type'] = sanitize_text_field($vp_cn['rules']['type']);
    	if(!$vp_cn['icon']) $errors->add('icon', __( 'Please choose notification icon', 'visitorsproof' ));
    	if(!$vp_cn['content']) $errors->add('content', __( 'Please enter notification content or message', 'visitorsproof' ));
    	if(!$vp_cn['rules']['type']) $errors->add('content', __( 'Please choose a display rule', 'visitorsproof' ));
    	
    	if (empty($errors->errors)){
    	    $vp_cn['content'] = str_replace(array("<p>", "</p>"), "", $vp_cn['content']);
    	    $vp_cn['rules'] = json_encode($vp_cn['rules']);
    	    $visitors_proof_cn_id = absint($_POST['visitors_proof_cn_id']);
    	    if ($visitors_proof_cn_id) {
    	        $vp_cn['status'] = isset($vp_cn['status']) ? 1 : 0;
    	        $vp_cn['updated'] = current_time('Y-m-d H:i:s');
    	        $wpdb->update($wpdb->prefix . VISITORS_PROOF_TABLE_NOTIFICATIONS, $vp_cn, array('id' => $visitors_proof_cn_id, 'is_custom' => 1));
    	    }else{
    	        $vp_cn['status'] = isset($vp_cn['status']) ? 1 : 0;
    	        $vp_cn['is_custom'] = 1;
    	        $vp_cn['type'] = 'Custom Notification';
    	        $vp_cn['created'] = $vp_cn['updated'] = current_time('Y-m-d H:i:s');
    	        $vp_cn['options'] = $vp_cn['defaults'] = '[]';
    	        $vp_cn['categories'] = '["custom"]';
    	        $vp_cn['ordering'] = $wpdb->get_var( "SELECT MAX(ordering) FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS ) + 1;
    	        $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_NOTIFICATIONS, $vp_cn);
    	    }
    	    if (isset($_GET['redirect'])) visitors_proof_redirect('?page=' . sanitize_text_field($_GET['redirect']));
    	    visitors_proof_redirect('?page=' . VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS . '&success=1');
    	}
    }else if (is_visitors_proof_cf7_active() && $vp_post_page == VISITORS_PROOF_PAGE_CF7) {
        $vp_cf7 = $_POST['vp_cf7'];
        $vp_cf7['content'] = wp_kses_post($vp_cf7['content']);
        $vp_cf7['content'] = trim($vp_cf7['content']);
        $l_content = strtolower($vp_cf7['content']);
        
        $unknown = array('$', 'rs.', 'purchase', 'sale', 'bought', 'buy');
        $c_error = false;
        foreach ($unknown as $u) {
            if (strpos($l_content, $u) !== false) {
                $c_error = true;
                break;
            }
        }
        if(preg_match("/\&.\d+;/", $vp_cf7['content']) || $c_error) $errors->add('content', __( 'Currency Symbols, sales related words are not allowed in custom notifications', 'visitorsproof' ));
        $vp_cf7['type'] = absint($vp_cf7['type']);
        $vp_cf7['icon'] = absint($vp_cf7['icon']);
        $vp_cf7['rules']['type'] = sanitize_text_field($vp_cf7['rules']['type']);
        if(!$vp_cf7['type']) $errors->add('icon', __( 'Please choose CF7 form', 'visitorsproof' ));
        if(!$vp_cf7['icon']) $errors->add('icon', __( 'Please choose notification icon', 'visitorsproof' ));
        if(!$vp_cf7['content']) $errors->add('content', __( 'Please enter notification content or message', 'visitorsproof' ));
        if(!$vp_cf7['rules']['type']) $errors->add('content', __( 'Please choose a display rule', 'visitorsproof' ));
        
        $visitors_proof_cn_id = absint($_POST['visitors_proof_cn_id']);
        $e_sql = $visitors_proof_cn_id ? " AND id != '$visitors_proof_cn_id'" : "";
        $check_cf7_form = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE is_custom = 2 AND type = '$vp_cf7[type]' $e_sql");
        if(!empty($check_cf7_form)){
            $cf7 = get_post($vp_cf7['type']);
            $errors->add('icon', sprintf( __( '%s is already used in another notification', 'visitorsproof' ), "<b>$cf7->post_title</b>" ) );
        }
        
        if (empty($errors->errors)){
            $vp_cf7['content'] = str_replace(array("<p>", "</p>"), "", $vp_cf7['content']);
            $vp_cf7['rules'] = json_encode($vp_cf7['rules']);
            if ($visitors_proof_cn_id) {
                $vp_cf7['status'] = isset($vp_cf7['status']) ? 1 : 0;
                $vp_cf7['updated'] = current_time('Y-m-d H:i:s');
                $wpdb->update($wpdb->prefix . VISITORS_PROOF_TABLE_NOTIFICATIONS, $vp_cf7, array('id' => $visitors_proof_cn_id, 'is_custom' => 2));
            }else{
                $vp_cf7['status'] = isset($vp_cf7['status']) ? 1 : 0;
                $vp_cf7['is_custom'] = 2;
                $vp_cf7['type'] = $vp_cf7['type'];
                $vp_cf7['created'] = $vp_cf7['updated'] = current_time('Y-m-d H:i:s');
                $vp_cf7['options'] = $vp_cf7['defaults'] = '[]';
                $vp_cf7['categories'] = '["cf7-form"]';
                $vp_cf7['ordering'] = $wpdb->get_var( "SELECT MAX(ordering) FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS ) + 1;
                $wpdb->insert($wpdb->prefix . VISITORS_PROOF_TABLE_NOTIFICATIONS, $vp_cf7);
            }
            if (isset($_GET['redirect'])) visitors_proof_redirect('?page=' . sanitize_text_field($_GET['redirect']));
            visitors_proof_redirect('?page=' . VISITORS_PROOF_PAGE_CF7 . '&success=1');
        }
    }
}
if (!function_exists('visitors_proof_bottom_design')) {
    function visitors_proof_bottom_design() {
        global $vpchr;
        $code = '<div class="vp_bottom">
			<a href="https://visitorsproof.com" target="_blank"><b>Visitorsproof</b>
			<img src="' . $vpchr->visitors_proof_assets('logo-16.png') . '" class="animate__animated animate__infinite animate__heartBeat"></a>
		</div>';
        return $code;
    }
}

if(!get_option('visitors_proof_site_id')){
    $request    = wp_remote_post(
        'https://wp.visitorsproof.com/api/vp_install',
        array(
            'timeout'   => 15,
            'body'      => visitors_proof_wp_obj(),
        )
	);
    if (!is_wp_error($request)) {
        $request = json_decode(wp_remote_retrieve_body($request));
        if(isset($request->status) && $request->status == 'success') {
            update_option('visitors_proof_site_id', $request->site_id);
            update_option('visitors_proof_site_domain', $request->site_domain);
        }
    }
}

$visitors_proof_current_tab = $vpchr->visitors_proof_current_tab();
$vp_wc_installed = is_visitors_proof_woo_commerce_active();
?>
<div class="wrap vp-page-<?php echo isset($_REQUEST['page']) ? $_REQUEST['page'] : ''; ?>">
	<input type="hidden" id="vp_site_url" value="<?php echo home_url(); ?>" />
	<?php if ($errors && $errors->errors){ ?>
    	<div class="error notice">
    		<?php foreach ($errors->errors as $error){ ?>
    			<p><?php echo $error[0]; ?></p>
    		<?php } ?>
    	</div>
	<?php } ?>
	<?php if (isset($_GET['success'])){
	    if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS) echo '<br/>';
	    ?>
		<h2></h2>
		<div class="notice notice-success is-dismissible"> 
			<p><strong><?php _e( 'All changes are updated', 'visitorsproof' ) ?></strong></p>
			<button type="button" class="notice-dismiss"></button>
		</div>
	<?php } ?>
	
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<a href="<?php echo $vpchr->visitors_proof_tabs_menus(VISITORS_PROOF_PAGE_NOTIFICATIONS); ?>" 
			class="nav-tab <?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_NOTIFICATIONS) echo 'nav-tab-active'; ?>"><?php _e( 'All Notifications', 'visitorsproof' ); ?></a>
		<a href="<?php echo $vpchr->visitors_proof_tabs_menus(VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS); ?>" 
			class="nav-tab <?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS) echo 'nav-tab-active'; ?>"><?php _e( 'Custom Notifications', 'visitorsproof' ); ?></a>
		<a href="<?php echo $vpchr->visitors_proof_tabs_menus(VISITORS_PROOF_PAGE_CF7); ?>" 
			class="nav-tab <?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_CF7) echo 'nav-tab-active'; ?>"><?php _e( 'Contact Form 7', 'visitorsproof' ); ?></a>
		<a href="<?php echo $vpchr->visitors_proof_tabs_menus(VISITORS_PROOF_PAGE_SETTINGS); ?>" 
			class="nav-tab <?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_SETTINGS) echo 'nav-tab-active'; ?>"><?php _e( 'Settings', 'visitorsproof' ); ?></a>
		<a href="<?php echo $vpchr->visitors_proof_tabs_menus(VISITORS_PROOF_PAGE_REPORTS); ?>" 
			class="nav-tab <?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_REPORTS) echo 'nav-tab-active'; ?>"><?php _e( 'Reports', 'visitorsproof' ); ?></a>
		<a href="<?php echo $vpchr->visitors_proof_tabs_menus(VISITORS_PROOF_PAGE_SUPPORT); ?>" 
			class="nav-tab <?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_SUPPORT) echo 'nav-tab-active'; ?>"><?php _e( 'Support', 'visitorsproof' ); ?></a>
		
		<div id="vp-global-on-off">
			<?php if ($visitors_proof_enabled) {?>
			    <span style="color: green;"><?php _e( 'Plugin is enabled', 'visitorsproof' ) ?></span>
			<?php }else{ ?>
				<span style="color: red;"><?php _e( 'Plugin is temporarily disabled', 'visitorsproof' ) ?></span>
			<?php } ?> 
			<label><input <?php if($visitors_proof_enabled) echo 'checked'; ?> type="checkbox" value="1" /> <?php _e( 'Enable', 'visitorsproof' ) ?></label>
		</div>
	</nav>

	<div id="col-container" class="wp-clearfix vp-mt-3">
    	<?php if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_NOTIFICATIONS){ 
    	    require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/page_all_notifications.php';
    	}else if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS){
    	    require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/page_custom_notifications.php';
    	}else if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_CF7){
    	    require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/page_cf7.php';
    	}else if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_SETTINGS){
    	    require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/page_settings.php';
    	}else if ($visitors_proof_current_tab == VISITORS_PROOF_PAGE_SUPPORT){
    	    require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/page_support.php';
    	}else{
    	    require_once VISITORS_PROOF_PLUGIN_DIR . 'inc/page_reports.php';
    	} ?>
	</div>
</div>
