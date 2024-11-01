<?php
$vp_notifications = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE 1 ORDER BY status DESC, ordering ASC");
$vp_notification_count = count($vp_notifications);
$since_strings = $vpchr->visitors_proof_since_types_notify_strings();
$lang_code = get_locale();
?>
<form method="post" id="visitors_proof_settings_form" action="" enctype="multipart/form-data">
	<input type="hidden" name="vp_post_page" value="<?php echo VISITORS_PROOF_PAGE_NOTIFICATIONS; ?>" />
	<div id="col-left" class="vp-w-25">
		<div class="col-wrap">
			<ul class="vp-m-0" id="vp-notification-category-list">
				<?php foreach ($vpchr->visitors_proof_notification_categories() as $nck => $ncd) {
					$cc = $vp_notification_count;
					if ($nck != 'all'){
						$sql = "SELECT COUNT(*) FROM `{$wpdb->prefix}" . VISITORS_PROOF_TABLE_NOTIFICATIONS . "` WHERE categories LIKE '%\"$nck\"%'";
						$cc = $wpdb->get_var($sql);
					}
					?>
					<li data-type="<?php echo $nck; ?>" class="animate__animated <?php if ($nck == 'all') echo 'vp-selected'; ?>"><?php echo esc_attr($ncd); ?> <span><?php echo esc_attr("($cc)"); ?></span></li>
				<?php } ?>
			</ul>
		</div>
		<p class="submit">
    		<button type="submit" id="submit" class="button button-primary vp-submit-loading"><?php _e( 'Save Changes', 'visitorsproof' ); ?></button>
    	</p>
	</div>
	<div id="col-right" class="vp-w-75">
		<?php if (!$vp_wc_installed){ ?>
			<div class="vp-pl-3 vp-pr-3">
				<h2 class="vp-hide"></h2>
				<div class="notice notice-warning"> 
					<p>
						<?php _e( 'Please install and activate', 'visitorsproof' ); ?> 
						<strong><a target="_blank" href="<?php echo admin_url('plugin-install.php?s=woocommerce&tab=search&type=term'); ?>"><?php _e( 'WooCommerce', 'visitorsproof' ); ?>.</a></strong> 
						<?php _e( 'if you want to show <b>disabled notifications</b> related to <b>sales, product, etc.,</b>', 'visitorsproof' ); ?>
					</p>
				</div>
			</div>
		<?php } ?>
		<div class="vp-pl-3 vp-pr-3" id="vp-notification-list">
			<?php 
			$site_name = esc_html(get_bloginfo('name'));
			$theme = visitors_proof_settings('random_theme') ? rand(1, 12) : visitors_proof_settings('theme');
			foreach ($vp_notifications as $vpd) {
				$categories = implode(' ', json_decode($vpd->categories));
				$icon = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}" . VISITORS_PROOF_TABLE_ICONS . " WHERE id = '$vpd->icon'");
				$image = $vpd->image ? '<img src="' . $vpchr->visitors_proof_assets($vpd->image) . '" />' : $icon->content;
				$content = $vpd->content;
				$safe_content = '';
				if (!$vpd->is_custom){
					$defaults = json_decode($vpd->defaults);
					if (in_array($vpd->type, array('Facebook Like', 'Twitter Follow', 'Linkedin Follow'))){
						$options = json_decode($vpd->options);
						$content = str_replace("[company]", $options->fields->url->value, $content);
						$content = str_replace("[locale]", ($vpd->type == 'Twitter Follow' ? substr($lang_code, 0, 2) : $lang_code), $content);
					}
					if (in_array($vpd->type, array('No of Visits', 'Location Visits', 'No of Orders'))){
					    $defaults->company = $site_name;
					    $defaults->hours = __( $since_strings[$defaults->hours], 'visitorsproof' );
					}
					
					preg_match_all("/\[[^]]*\]/", $content, $matches);
					$keys = $matches[0];
					
					$values = array();
					$params = (array)$defaults;
					foreach ($keys as $ki => $k) {
						$rc = '%' . ($ki+1) . '$s';
						$params[$k] = $params[trim($k, '[]')];
						if(in_array($k, array('[visits]', '[visitors]', '[orders]', '[variants]', '[times]', '[items]')) || ($k == '[hours]' && $vpd->type == 'Recent Product Views')) {
							$suffix = explode(' ', $params[$k], 2);
							$rc .= ' ' . $suffix[1];
							$params[$k] = $suffix[0];
						}
						$content = str_replace($k, $rc, $content);
						$values []= $params[$k];
					}
					$safe_content = $content;
					$content = vsprintf(__( $content, 'visitorsproof' ), $values);
				}
				$options = json_decode($vpd->options);
				$fields = isset($options->fields) ? $options->fields : array();
				?>

				<div class="vp-en-preview-row <?php if (!$vp_wc_installed && $vpd->woocommerce) echo 'vp-woocommerce'; ?> <?php if ($vpd->status) echo 'vp-enabled'; ?> all <?php echo $categories; ?>" >
					<input type="hidden" name="vp_ordering[]" value="<?php echo $vpd->id; ?>" />
					<?php if ($vpd->is_custom){ ?>
						<a href="<?php echo admin_url('admin.php?page=' . ($vpd->is_custom == 1 ? VISITORS_PROOF_PAGE_CUSTOM_NOTIFICATIONS : VISITORS_PROOF_PAGE_CF7 ) . '&action=edit&notification_id=' . $vpd->id . '&redirect=' . VISITORS_PROOF_PAGE_NOTIFICATIONS); ?>" class="vp-edit-notification vp-custom animate__animated <?php if ($vpd->status) echo 'animate__zoomIn'; else echo 'vp-hide'; ?>" title="<?php _e( 'Edit Notification', 'visitorsproof' ); ?>">
							<i class="dashicons-before dashicons-edit vp-center"></i> 
						</a>
					<?php }else{ ?>
						<span class="vp-edit-notification animate__animated <?php if ($vpd->status) echo 'animate__zoomIn'; else echo 'vp-hide'; ?>" title="<?php _e( 'Edit Notification', 'visitorsproof' ); ?>">
							<i class="dashicons-before dashicons-edit vp-center"></i> 
						</span>
					<?php } ?>
					<span class="vp-notify-hint ">
						<i class="dashicons-before dashicons-info"></i> 
						<?php if ($vpd->is_custom == 1) {
						    echo '<span class="vp-text-danger">' . $vpd->type . '</span>';
						}else if ($vpd->is_custom == 2) {
						    $content = str_replace(array('{', '}'), array('<b>{', '}</b>'), $content);
						    $cf7 = get_post($vpd->type);
						    echo '<span class="vp-text-danger">CF7 : ' . $cf7->post_title . '</span>';
						}else {
						    echo '<span class="vp-text-success">' . $vpd->type . '</span>';
						} ?>
					</span>
					<table>
						<tbody>
							<tr>
								<td>
									<img src="<?php echo $vpchr->visitors_proof_assets('drags.png'); ?>" class="vp-drag-handle" />
								</td>
								<td>
									<div class="vp-en-preview social-media">
										<div class="vp-notification-preview animate__animated animate__<?php echo visitors_proof_settings('entrance_animation'); ?>">
											<?php if (in_array($theme, array(1, 2))){ ?>
												<div class="vp_<?php echo $theme; ?>_container">
													<div class="vp_left">
														<?php echo $image; ?>
													</div>
													<div class="vp_right">
														<?php echo $content; ?>
														<?php echo visitors_proof_bottom_design(); ?>
													</div>
												</div>
											<?php } ?>
										</div>
									</div>
								</td>
								<td>											
									<div class="vp-on-off-switch vp-swtiches vp-ripple">
										<input type="checkbox" id="o_<?php echo $vpd->id; ?>" value="1" name="vp_n_enabled[<?php echo $vpd->id; ?>]" <?php if ($vpd->status) echo 'checked'; ?> />
										<label for="o_<?php echo $vpd->id; ?>"></label>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="vp-e-content vp-box vp-hide animate__animated">
						<div class="vp-close-e-content"><i class="dashicons-before dashicons-dismiss vp-center"></i></div>
						<div class="vp-center">
							<div class="vp-content"><?php _e( $vpd->description, 'visitorsproof' ); ?></div>
							<?php foreach ($fields as $ok => $ov) {
								$cid = "option_" . str_replace(' ' , '_', strtolower($vpd->type)) . "_$ok";
								?>
								<label class="g-heading" for="<?php echo $cid; ?>"><?php _e( $ov->name, 'visitorsproof' ); ?></label> : 
								<?php if ($ov->type == 'textbox'){ ?>
									<input type="<?php echo $ov->itype; ?>" class="inputs" id="<?php echo $cid; ?>" name="vp_options[<?php echo $vpd->id; ?>][<?php echo $ok; ?>]" 
										<?php if ($ov->itype == 'number') echo ' min="' . $ov->min . '" max="' . $ov->max . '"'; ?> value="<?php echo $ov->value; ?>" required />
								<?php }else{ ?>
									<select class="inputs" id="<?php echo $cid; ?>" name="vp_options[<?php echo $vpd->id; ?>][<?php echo $ok; ?>]">
										<?php foreach ($vpchr->visitors_proof_since_types() as $std) {
										    $c_since = 'Last ' . esc_attr(ucfirst($std));
										    ?>
											<option <?php if ($ov->value == $std) echo 'selected'; ?> value="<?php echo $std; ?>"><?php _e( $c_since , 'visitorsproof' ); ?></option>
										<?php } ?>
									</select>
								<?php } ?>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</form>