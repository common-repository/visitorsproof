<?php
if(is_visitors_proof_cf7_active()){
$is_editing = isset($_GET['action']) && $_GET['action'] == 'edit';
if($is_editing) {
    $cnotification_id = absint($_GET['notification_id']);
    $cnotification = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE id = '$cnotification_id'");
    $rules = json_decode($cnotification->rules);
}
?>
<div id="col-left" class="vp-w-70">
	<div id="poststuff" class="vp-pt-0" style="min-width: 100%;">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">
						<?php
						$vp_cf7_obj->prepare_items();
						$vp_cf7_obj->display(); 
						?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>
<div id="col-right" class="vp-w-30">
	<div class="vp-m-3">
		
		<form method="POST" id="visitors_proof_settings_form" action="" enctype="multipart/form-data">
			<input type="hidden" name="vp_post_page" value="<?php echo VISITORS_PROOF_PAGE_CF7; ?>" />
			<input type="hidden" name="visitors_proof_cn_id" id="visitors_proof_cn_id" value="<?php echo $is_editing ? $cnotification_id : 0; ?>" />
			<div class="col-wrap">
				<h1 class="wp-heading-inline"><?php _e( 'CF7 Form Details', 'visitorsproof' ); ?></h1>
				<a href="<?php echo ('?page=' . VISITORS_PROOF_PAGE_CF7 . '&action=add'); ?>" class="page-title-action"><?php _e( 'Add New', 'visitorsproof' ); ?></a>
    			<div class="form-wrap">
        			<div class="form-field form-required term-name-wrap">
                        <label for="vp-cf7f"><?php _e( 'CF7 Form', 'visitorsproof' ); ?></label>
                        <select class="form-control select-2-html vp-w-100" name="vp_cf7[type]" id="vp-cf7f" ></select>
                        <p class="error"><?php _e( 'Choose from contact form 7', 'visitorsproof' ); ?></p>
                    </div>
        		</div>
    			<div class="form-wrap">
        			<div class="form-field form-required term-name-wrap">
                        <label for="vp-cni"><?php _e( 'Icon', 'visitorsproof' ) ?></label>
                        <select class="form-control select-2-html vp-w-100" name="vp_cf7[icon]" id="vp-cni" ></select>
                        <p class="error"><?php _e( 'Choose the best icon that suits you', 'visitorsproof' ); ?></p>
                    </div>
        			<div class="form-field form-required term-name-wrap">
                        <label for="vp-cni"><?php _e( 'Icon Preview', 'visitorsproof' ); ?></label>
                        <div id="vp-icon-preview"></div>
                    </div>
        		</div>
    			<div class="form-wrap">
    				<div style="background: #fff; padding: 10px; border: 1px solid #ccc;">
        				<div><b class="vp-text-danger"><?php _e( 'Example Setting', 'visitorsproof' ); ?>:</b> <br/><?php printf( __( '%1$s from %2$s has filled support contact form', 'visitorsproof' ), '<b>{your-name}</b>', '<b>{vp-user-city}</b>' ); ?></div><br/>
        				<div><b class="vp-text-danger"><?php _e( 'Example Output', 'visitorsproof' ); ?>:</b> <br/><?php printf( __( '%1$s from %2$s has filled support contact form', 'visitorsproof' ), '<b>John</b>', '<b>San Fransico</b>' ); ?></div>
    				</div>
    				<div id="vp_cf7_fields" class="vp-mt-2"></div>
        			<div class="form-field form-required term-name-wrap">
                        <label for="vp-cn-message"><?php _e( 'Message', 'visitorsproof' ); ?> <span id="vp-vn-chars-count"><b>120</b> <?php _e( 'characters available', 'visitorsproof' ); ?></span></label>
                        <textarea class="form-control" name="vp_cf7[content]" id="vp-cn-message" maxlength="120" placeholder="<?php printf( __( 'Only %s characters are allowed', 'visitorsproof' ), 120 ); ?>" ><?php if ($is_editing) echo $cnotification->content; ?></textarea>
                        <p class="error"><?php printf( __( 'Only %s characters are allowed', 'visitorsproof' ), 120 ); ?></p>
                    </div>
        		</div>
    			<div class="form-wrap">
        			<div class="form-field form-required term-name-wrap">
                        <label for="vp-nrules"><?php _e( 'Display Rule', 'visitorsproof' ); ?></label>
                        <select id="vp-nrules" name="vp_cf7[rules][type]">
                        	<option <?php if ($is_editing && 'All' == $rules->type) echo 'selected'; ?> value="All">All Pages</option>
                        	<option <?php if ($is_editing && 'Form' == $rules->type) echo 'selected'; ?> value="Form">Form Page</option>
                        </select>
                    </div>
        		</div>
    			<div class="form-wrap">
        			<div class="form-field form-required term-name-wrap">
                        <label><input type="checkbox" name="vp_cf7[status]" <?php if ($is_editing && $cnotification->status) echo 'checked'; ?> /> <?php _e( 'Enabled', 'visitorsproof' ); ?></label>
                    </div>
        		</div>
        		<p class="submit">
        			<button type="submit" id="submit" class="button button-primary vp-submit-loading"><?php _e( 'Save Changes', 'visitorsproof' ); ?></button>
        		</p>
    		</div>
		</form>
	</div>
</div>
<?php }else{ ?>
	<div class="vp-pl-3 vp-pr-3 vp-text-center">
		<h2 class="vp-hide"></h2>
		<div class="notice notice-warning"> 
			<p>
				<?php _e( 'Please install and activate', 'visitorsproof' ) ?>
				<strong><a target="_blank" href="<?php echo admin_url('plugin-install.php?s=Contact+Form+7&tab=search&type=term'); ?>"><?php _e( 'Contact Form 7', 'visitorsproof' ) ?>.</a></strong> 
				<?php _e( 'if you want to show notifications from Contact Form 7', 'visitorsproof' ) ?>.</strong>
			</p>
		</div>
	</div>
<?php } ?>