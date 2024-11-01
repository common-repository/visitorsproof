var vp_parts = window.location.search.substr(1).split("&");
var $_VPJS_GET = {};
var vp_site_url = '';
for (var i = 0; i < vp_parts.length; i++) {
    var temp = vp_parts[i].split("=");
    $_VPJS_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]) || "";
    if($_VPJS_GET[decodeURIComponent(temp[0])] == "undefined") $_VPJS_GET[decodeURIComponent(temp[0])] = "";
}
jQuery(document).ready(function () {
	vp_site_url = jQuery('#vp_site_url').val();
	function visitors_proof_submit_loading(){
		jQuery(document).on('click', '.vp-submit-loading', function (){
			var ths = jQuery(this);
			ths.html('<i class="dashicons dashicons-update-alt" style="margin-top: 4px;"></i> ' + VPADMJS_Params.strings['Saving']);
			setTimeout(function (){
				ths.prop('disabled', true);
			}, 50);
		});
	}
	
	jQuery(document).on('change', '#vp-global-on-off input', function (){
		if(confirm(VPADMJS_Params.strings['Confirmation'])){
			jQuery('#vp-global-on-off span').html(VPADMJS_Params.strings['Loading Text']);
			jQuery('#vp-global-on-off label').remove();
			jQuery.post(vp_site_url + '/wp-admin/admin-ajax.php?action=visitors_proof_ajax_call&callback=status', { status: (this.checked ? 1 : 0) }, function (d){
				location.href = '';
			}, 'JSON');
		}else{
			this.checked = this.checked ? false : true ;
		}
	});
	
	if($_VPJS_GET['page'] == 'visitors-proof-settings'){
		visitors_proof_submit_loading();
		var vp_ele_notification_preview = jQuery(".vp-notification-preview");
		var vp_last_shown_theme = '';

		function visitors_proof_random_number(max){
			if(max == undefined) max = 12;
			return Math.floor((Math.random() * max) + 1);
		}
		
		function visitors_proof_remove_classes(element, class_name){
			jQuery(element).removeClass(function (index, css) {
			     return (css.match (new RegExp("\\b" + class_name + "\\S+", "g")) || []).join(' ');
			});
		}
		
		function visitors_proof_view_preview(loading, action){
			if(loading == null) loading = false
			jQuery('.vp-notification-preview > div').addClass('vp-hide');
			vp_last_shown_theme = jQuery('#vp-theme').val();
			jQuery('.vp_' + vp_last_shown_theme + '_container').removeClass('vp-hide');
			
			visitors_proof_remove_classes('.vp-notification-preview', 'vp-pos-');
			visitors_proof_remove_classes('.vp-notification-preview', 'animate__');
			vp_ele_notification_preview.addClass('vp-pos-' + jQuery('#vp-npos').val() + ' animate__animated vp-hide');
			if(loading){
				setTimeout(function (){
					vp_ele_notification_preview.addClass('animate__' + jQuery('#vp-nenteff').val()).removeClass('vp-hide');
				}, 10);
			}
		}
	
		jQuery(document).on('change', '#vp-theme', function (){
			visitors_proof_view_preview(true, 'theme');
		});
	
		jQuery(document).on('change', '#vp-npos', function (){
			visitors_proof_view_preview(true, 'position');
		});
		
		jQuery(document).on('change', '#vp-nenteff', function (){
			visitors_proof_view_preview(false, 'entrance');
			setTimeout(function (){
				vp_ele_notification_preview.addClass('animate__' + jQuery('#vp-nenteff').val()).removeClass('vp-hide');
			}, 10);
		});
		
		var vp_show_after_hide = false;
		jQuery(document).on('change', '#vp-nexiteff', function (){
			visitors_proof_view_preview(false, 'exit');
			vp_ele_notification_preview.addClass('animate__' + jQuery('#vp-nexiteff').val()).removeClass('vp-hide');
			clearTimeout(vp_show_after_hide);
			vp_show_after_hide = setTimeout(function (){
				visitors_proof_view_preview();
				vp_ele_notification_preview.addClass('animate__' + jQuery('#vp-nenteff').val()).removeClass('vp-hide');
			}, 2000);
		});
	
		jQuery(document).on('click', '.vp-preview-play', function (){
			jQuery('#' + jQuery(this).data('type')).trigger('change');
		});
	
		jQuery(document).on('change', '#vp_preview_bg', function (){
			jQuery('#vp-notification-preview-container').toggleClass('vp-white-bg');
			jQuery('#vp-notification-preview-container iframe').toggleClass('vp-hide');
		});
		
		jQuery('#vp-notification-preview-container iframe').on('load', function(){
			jQuery('#vp-notification-preview-container .vp-load-title').addClass('vp-hide');
	    });
		
		function visitors_proof_refresh_iframe(){
			//jQuery('#vp-notification-preview-container iframe').prop('src', 'javascript:void()');
			jQuery('#vp-notification-preview-container iframe').prop('src', (jQuery('#vp-notification-preview-container iframe').prop('src')));
			if(jQuery('#vp_preview_bg').is(':checked') == false) jQuery('#vp-notification-preview-container .vp-load-title').removeClass('vp-hide');
		}
	
		jQuery(document).on('click', '#vp-enter-fullscreen', function (){
			visitors_proof_refresh_iframe();
			jQuery('#vp-notification-preview-container, body').addClass('vp-fullscreen');
		});
	
		jQuery(document).on('click', '#vp-exit-fullscreen', function (){
			visitors_proof_refresh_iframe();
			jQuery('#vp-notification-preview-container, body').removeClass('vp-fullscreen');
		});
	
		jQuery(document).on('keyup', function (e){
			if(e.keyCode == 27 && jQuery('#vp-exit-fullscreen').length) {
				visitors_proof_refresh_iframe();
				jQuery('#vp-exit-fullscreen').trigger('click');
			}
		});
		
		visitors_proof_view_preview(true);
	}else if($_VPJS_GET['page'] == 'visitors-proof-notifications'){
		visitors_proof_submit_loading();
		jQuery(document).on('click', '#vp-notification-category-list li', function (){
			jQuery('.vp-en-preview-row').addClass('vp-hide');
			jQuery('.' + jQuery(this).data('type')).removeClass('vp-hide');
			jQuery('#vp-notification-category-list li').removeClass('vp-selected');
			jQuery(this).addClass('vp-selected');
			jQuery('.vp-en-preview-row .vp-e-content').addClass('vp-hide').removeClass('animate__backOutLeft');
		});
		
		jQuery(document).on('change', '.vp-swtiches input', function (){
			if(this.checked){
				jQuery(this).parents('.vp-en-preview-row').addClass('vp-enabled').children('.vp-edit-notification').removeClass('vp-hide animate__zoomOut').addClass('animate__zoomIn');
			}else{
				jQuery(this).parents('.vp-en-preview-row').removeClass('vp-enabled').children('.vp-edit-notification').addClass('animate__zoomOut').removeClass('animate__zoomIn');
			}
		});
		
		jQuery(document).on('click', '.vp-edit-notification:not(.vp-custom)', function (){
			jQuery(this).parents('.vp-en-preview-row').children('.vp-e-content').removeClass('vp-hide animate__backOutLeft').addClass('animate__backInLeft');
		});
		
		jQuery(document).on('click', '.vp-close-e-content', function (){
			jQuery(this).parents('.vp-en-preview-row').children('.vp-e-content').addClass('animate__backOutLeft').removeClass('animate__backInLeft');
		});

		jQuery("#option_free_delivery_amount").change(function (){
			jQuery("#option_free_delivery_min").prop("max", this.value).val('');
    	});

    	var options = {
			handle: '.vp-drag-handle',
		    swapThreshold: 1,
		    animation: 150,
		    ghostClass: 'blue-background-class',
		    chosenClass: 'green-chosen'
		};

    	var sorting_timeout = false;
		events = [
		  /*'onChoose',
		  'onAdd',
		  'onUpdate',
		  'onSort',
		  'onRemove',
		  'onUnchoose',
		  'onStart',*/
		  'onEnd',
		  'onChange'
		].forEach(function (name) {
			options[name] = function (evt) {
				jQuery('.vp-en-preview-row .vp-e-content').addClass('vp-hide').removeClass('animate__backOutLeft');
				/*$('#vp-notification-list .list-group-item').removeClass('last-sorted');
				$(evt.item).addClass('last-sorted');
				if(name == 'onEnd'){
					clearTimeout(sorting_timeout);
					sorting_timeout = setTimeout(function (){
						$('.last-sorted').removeClass('last-sorted');
					}, 3000);
				}*/
			};
		});
    			
		var sort_obj = new Sortable(document.getElementById('vp-notification-list'), options);
	}else if($_VPJS_GET['page'] == 'visitors-proof-custom-notifications'){
		visitors_proof_submit_loading();
		
		var vp_icons = [];
		jQuery.post(vp_site_url + '/wp-admin/admin-ajax.php?action=visitors_proof_ajax_call&callback=icons', { visitors_proof_cn_id: jQuery('#visitors_proof_cn_id').val() }, function (d){
			vp_icons = d.icons_kp;
			function template(data) {
				return data.content;
			}
			jQuery(".select-2-html").select2({
				data: d.icons,
				templateResult: template,
				escapeMarkup: function(m) {
					return m;
				}
			});
			setTimeout(function (){
				jQuery('#vp-cni').val(d.cn_icon).trigger('change');
			}, 100);
		}, 'JSON');
		
		function visitors_proof_count_characters(){
			var cur_msg = jQuery('#vp-cn-message').trumbowyg('html');
    		var c_message = cur_msg.replace(/&nbsp;/gi, " ");
    		c_message = jQuery("<div>" + c_message + "</div>").text();
    		jQuery("#vp-vn-chars-count b").text(vp_total_cn_chars - c_message.length);
		}
		
		var vp_total_cn_chars = 120;
		jQuery(document).on('change', '#vp-cni', function (){
			jQuery('#vp-icon-preview').html(vp_icons[this.value].content);
		});
		
		jQuery('#vp-cn-message').trumbowyg({
		    btns: [
		        ['strong', 'em', 'underline']
		    ]
		}).on("tbwchange", function (){
			visitors_proof_count_characters();
    	});
    	
    	if(jQuery('#visitors_proof_cn_id').val()){
			visitors_proof_count_characters();
		}
		
		jQuery(document).on("keypress paste", '.trumbowyg-editor', function (e) {
	        if (jQuery(this).text().length >= vp_total_cn_chars) {
	            e.preventDefault();
	            return false;
	        }
	    });
	}else if($_VPJS_GET['page'] == 'visitors-proof-cf7' && jQuery("#vp-cni").length > 0){
		visitors_proof_submit_loading();
		
		var vp_icons = vp_cf7_forms = [];
		jQuery.post(vp_site_url + '/wp-admin/admin-ajax.php?action=visitors_proof_ajax_call&callback=cf7-forms', { visitors_proof_cn_id: jQuery('#visitors_proof_cn_id').val() }, function (d){
			vp_icons = d.icons_kp;
			vp_cf7_forms = d.forms;
			function template(data) {
				return data.content;
			}
			jQuery("#vp-cni").select2({
				data: d.icons,
				templateResult: template,
				escapeMarkup: function(m) {
					return m;
				}
			});
			jQuery("#vp-cf7f").empty().append('<option value="">' + VPADMJS_Params.strings['Choose Form'] + '</option>');
			for(var i in vp_cf7_forms){
				jQuery("#vp-cf7f").append('<option value="' + vp_cf7_forms[i].ID + '">' + vp_cf7_forms[i].post_title + '</option>');
			}
			jQuery("#vp-cf7f").select2({
				placeholder: VPADMJS_Params.strings['Choose Form']
			});
			setTimeout(function (){
				jQuery('#vp-cni').val(d.cn_icon).trigger('change');
				jQuery('#vp-cf7f').val(d.cf7_form).trigger('change');
			}, 100);
		}, 'JSON');
		
		jQuery(document).on('mousedown', '#vp_cf7_fields input', function (){
			this.select();
		});
		
		jQuery(document).on('change', '#vp-cf7f', function (){
			var vp_current_cf7_ids = vp_cf7_forms[this.value].ids;
			var vp_cf7_code = '<div class="vp-text-info vp-mb-1"><i class="dashicons-before dashicons-warning"></i> Use these fields to make your CF7 custom notification</div>';
			vp_cf7_code += '<input type="text" title="Predefined Attribute" readonly value="{vp-user-city}" class="vp-cf7-fields" draggable="true" />';
			vp_cf7_code += '<input type="text" title="Predefined Attribute" readonly value="{vp-user-country}" class="vp-cf7-fields" draggable="true" />';
			for(var i in vp_current_cf7_ids){
				vp_cf7_code += '<input type="text" title="CF7 Field" readonly value="{' + vp_current_cf7_ids[i] + '}" class="vp-cf7-fields" draggable="true" />';
			}
			jQuery('#vp_cf7_fields').html(vp_cf7_code);
		});
		
		function visitors_proof_count_characters(){
			var cur_msg = jQuery('#vp-cn-message').trumbowyg('html');
    		var c_message = cur_msg.replace(/&nbsp;/gi, " ");
    		c_message = jQuery("<div>" + c_message + "</div>").text();
    		jQuery("#vp-vn-chars-count b").text(vp_total_cn_chars - c_message.length);
    		
    		
		}
		
		var vp_total_cn_chars = 120;
		jQuery(document).on('change', '#vp-cni', function (){
			jQuery('#vp-icon-preview').html(vp_icons[this.value].content);
		});
		
		jQuery('#vp-cn-message').trumbowyg({
		    btns: [
		        ['strong', 'em', 'underline']
		    ]
		}).on("tbwchange", function (){
			visitors_proof_count_characters();
    	});
    	
    	if(jQuery('#visitors_proof_cn_id').val()){
			visitors_proof_count_characters();
		}
		
		jQuery(document).on("keypress paste", '.trumbowyg-editor', function (e) {
	        if (jQuery(this).text().length >= vp_total_cn_chars) {
	            e.preventDefault();
	            return false;
	        }
	    });
	}else if($_VPJS_GET['page'] == 'visitors-proof-reports'){
		visitors_proof_submit_loading();
	}
});