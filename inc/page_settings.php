<form method="post" id="visitors_proof_settings_form" action="" enctype="multipart/form-data">
	<input type="hidden" name="vp_post_page" value="<?php echo VISITORS_PROOF_PAGE_SETTINGS; ?>" />
	<div id="col-left" class="vp-w-35">
		<div class="col-wrap">
			<h3><?php _e( 'General Settings', 'visitorsproof' ) ?></h3>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-nds"><?php _e( 'Display Seconds', 'visitorsproof' ) ?></label>
                    <input required type="number" step="1" min="5" max="600" id="vp-nds" name="visitors_proof_settings[display_seconds]" value="<?php echo visitors_proof_settings('display_seconds'); ?>" />
                    <p class="error"><?php _e( 'How many seconds does a notification should display', 'visitorsproof' ) ?></p>
                </div>
    		</div>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-ibs"><?php _e( 'Interval Seconds', 'visitorsproof' ) ?></label>
                    <input required type="number" step="1" min="5" max="1000" id="vp-ibs" name="visitors_proof_settings[interval_seconds]" value="<?php echo visitors_proof_settings('interval_seconds'); ?>" />
                    <p class="error"><?php _e( 'Interval between notifications', 'visitorsproof' ) ?></p>
                </div>
    		</div>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-theme"><?php _e( 'Theme', 'visitorsproof' ) ?></label>
                    <select id="vp-theme" name="visitors_proof_settings[theme]">
                    	<?php foreach ($vpchr->visitors_proof_notification_themes() as $nk => $nv) { ?>
                    		<option <?php if (visitors_proof_settings('theme') == $nk) echo 'selected'; ?> value="<?php echo $nk; ?>" data-index="<?php echo $nk; ?>"><?php echo sprintf( __( $nv, 'visitorsproof' ), $nk ); ?></option>
                    	<?php } ?>
                    </select>
                    <div class="dashicons-before dashicons-controls-play vp-preview-play" data-type="vp-theme"></div>
                    <p class="error"><?php printf( __( 'Choose any one theme from %s themes. <b>Upgrade to premium to get more themes and random theme feature</b>', 'visitorsproof' ), count($vpchr->visitors_proof_notification_themes()) ); ?></p>
                </div>
    		</div>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-npos"><?php _e( 'Notification Position', 'visitorsproof' ) ?></label>
                    <select id="vp-npos" name="visitors_proof_settings[position]">
                    	<?php foreach ($vpchr->visitors_proof_notification_positions() as $nk => $nv) { ?>
                    		<option <?php if (visitors_proof_settings('position') == $nk) echo 'selected'; ?> value="<?php echo $nk; ?>"><?php echo esc_attr($nv); ?></option>
                    	<?php } ?>
                    </select>
                    <div class="dashicons-before dashicons-controls-play vp-preview-play" data-type="vp-npos"></div>
                    <p class="error"><?php printf( __( 'Choose any one position from %s positions. <b>Upgrade to premium to get more notification positions</b>', 'visitorsproof' ), count($vpchr->visitors_proof_notification_positions()) ); ?></p>
                </div>
    		</div>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-nenteff"><?php _e( 'Entrance Animation', 'visitorsproof' ) ?></label>
                    <select id="vp-nenteff" name="visitors_proof_settings[entrance_animation]">
                    	<?php foreach ($vpchr->visitors_proof_notification_entrance_animations() as $nk => $nv) { ?>
                    		<option <?php if (visitors_proof_settings('entrance_animation') == $nk) echo 'selected'; ?> value="<?php echo $nk; ?>"><?php echo esc_attr($nv); ?></option>
                    	<?php } ?>
                    </select>
                    <div class="dashicons-before dashicons-controls-play vp-preview-play" data-type="vp-nenteff"></div>
                    <p class="error"><?php _e( 'Choose an animation effects. <b>Upgrade to premium to get more animation effects</b>', 'visitorsproof' ) ?></p>
                 </div>
    		</div>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-nexiteff"><?php _e( 'Exit Animation', 'visitorsproof' ) ?></label>
                    <select id="vp-nexiteff" name="visitors_proof_settings[exit_animation]">
                    	<?php foreach ($vpchr->visitors_proof_notification_exit_animations() as $nk => $nv) { ?>
                    		<option <?php if (visitors_proof_settings('exit_animation') == $nk) echo 'selected'; ?> value="<?php echo $nk; ?>"><?php echo esc_attr($nv); ?></option>
                    	<?php } ?>
                    </select>
                    <div class="dashicons-before dashicons-controls-play vp-preview-play" data-type="vp-nexiteff"></div>
                    <p class="error"><?php _e( 'Choose an animation effects. <b>Upgrade to premium to get more animation effects</b>', 'visitorsproof' ) ?></p>
                 </div>
    		</div>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-nloop"><input type="checkbox" id="vp-nloop" name="visitors_proof_settings[loop]" value="1" <?php if (visitors_proof_settings('loop') == 1) echo 'checked'; ?> /> <?php _e( 'Enable Notification Loop', 'visitorsproof' ) ?></label>
                    <p class="error"><?php _e( 'Start over again from beginning after showing all notifications in their order', 'visitorsproof' ) ?></p>
                 </div>
    		</div>
    		<p class="submit vp-pb-1">
    			<button type="submit" id="submit" class="button button-primary vp-submit-loading"><?php _e( 'Save Settings', 'visitorsproof' ) ?></button>
    		</p>
			<div class="form-wrap">
    			<div class="form-field form-required term-name-wrap">
                    <label for="vp-clear-data-on-uninstall" style="color: red;"><input type="checkbox" id="vp-clear-data-on-uninstall" name="visitors_proof_settings[clear_data]" value="1" <?php if (visitors_proof_settings('clear_data') == 1) echo 'checked'; ?> /> <?php _e( 'Clear Data', 'visitorsproof' ) ?></label>
                    <p class="error" style="color: red;"><?php _e( 'Wipe out all data related to this plugin upon uninstallation or deletion of plugin. It is irreversible process and it cannot be undone', 'visitorsproof' ) ?></p>
                 </div>
    		</div>
		</div>
	</div>
	<div id="col-right" class="vp-w-65">
		<div class="vp-m-3">
			<h3><?php _e( 'Instant Preview', 'visitorsproof' ) ?> <small class="vp-ml-3"><label><input id="vp_preview_bg" type="checkbox" value="q" /> <?php _e( 'Test with White Background', 'visitorsproof' ) ?></label></small></h3>
			<div id="vp-notification-preview-container">
				<div id="vp-exit-fullscreen"><?php _e( 'Exit Fullscreen', 'visitorsproof' ) ?></div>
				<div id="vp-enter-fullscreen"><?php _e( 'Fullscreen', 'visitorsproof' ) ?></div>
				<div class="vp-load-title"><?php _e( 'Loading preview...', 'visitorsproof' ) ?></div>
				<iframe src="<?php echo home_url('wp-admin/admin-ajax.php?action=visitors_proof_ajax_call&callback=site_content'); ?>"></iframe>
				<div class="vp-notification-preview">
					<div class="vp_1_container vp-hide">
						<div class="vp_left">
							<img src="<?php echo $vpchr->visitors_proof_assets('google-pixel.png'); ?>" />
						</div>
						<div class="vp_right">
							<?php printf( __( '%1$s from %2$s purchased %3$s recently', 'visitorsproof' ), '<b>Andrew Williams</b>', '<b>New York, United States</b>', '<b>Awesome Google Pixel 4</b>' ); ?>
							<?php echo visitors_proof_bottom_design(); ?>
						</div>
					</div>
					<div class="vp_2_container vp-hide">
						<div class="vp_left">
							<img src="<?php echo $vpchr->visitors_proof_assets('rubiks-cube.jpg'); ?>" />
						</div>
						<div class="vp_right">
							<?php printf( __( '%1$s is the Most Viewed Product. It has <b>%2$s Unique Views</b> since last month', 'visitorsproof' ), '<b>3 x 3 Rubiks Cube</b>', '638' ); ?>
							<?php echo visitors_proof_bottom_design(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
