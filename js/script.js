var visitors_proof_parts	= window.location.search.substr(1).split("&");
var visitors_proof_loaded = false;
if(document.getElementById("visitors_proof_loaded")){
    visitors_proof_loaded = true;
} else {
    visitors_proof_loaded = false;
    var visitors_proof_loaded_ele = document.createElement('span');
    visitors_proof_loaded_ele.setAttribute("id", "visitors_proof_loaded");
    document.body.appendChild(visitors_proof_loaded_ele);
    
    visitors_proof_on_load();
}

function visitors_proof_random_number(max){
	if(max == undefined) max = 12;
	return Math.floor((Math.random() * max) + 1);
}

function visitors_proof_on_load(){
	jQuery(document).ready(function($) {
		if(jQuery('body').hasClass('visitors-proof-preview') == false && window.self === window.top && VPJS_Params.enabled == 1){
			visitors_proof_init();
		}
	});
}

function visitors_proof_init(){
	
	var visitors_proof_jquery_version 	= jQuery.fn.jquery.replace('.', '');
    visitors_proof_jquery_version 		= parseInt(visitors_proof_jquery_version.substring(0, 2) || 0);
	var visitors_proof_bind_support 	= visitors_proof_jquery_version < 17;
	var visitors_proof_ordering			= 0;
	var visitors_proof_remove_timeout	= null;
	var visitors_proof_notify_timeout	= null;
    var visitors_proof_o 				= {};
	var visitors_proof_api				= VPJS_Params.site_url + '/wp-admin/admin-ajax.php?action=visitors_proof_ajax_call&callback=';
	var visitors_proof_status			= 0;
	
	var visitors_proof_n_top = '<div class="vp-notification-preview vp-pos-{position} animate__animated animate__{animation}"><span id="vp-close-notification" class="animate__animated animate__fadeInUpCustom" title="Close Notification">+</span>';
	var visitors_proof_n_bottom = '</div>';
	var visitors_proof_n_logo = '<a href="https://visitorsproof.com" target="_blank"><b>Visitorsproof</b> <img src="' + VPJS_Params.plugin_url + '/assets/logo-16.png" class="animate__animated animate__infinite animate__heartBeat"></a>';
	var visitors_proof_n_body = {};
	visitors_proof_n_body[1] = '<div class="vp_1_container"> <div class="vp_left"> {image_svg} </div> <div class="vp_right"> {content} <div class="vp_bottom"> {vp_n_logo} </div></div> </div>';
	visitors_proof_n_body[2] = '<div class="vp_2_container"> <div class="vp_left"> {image_svg} </div> <div class="vp_right"> {content} <div class="vp_bottom"> {vp_n_logo} </div></div> </div>';
	
	function visitors_proof_serve_notification(theme, image_svg, content){
		if(VPJS_Params.settings.random_theme == 1) theme = visitors_proof_random_number(12);
		var visitors_proof_cn_code = visitors_proof_n_top + visitors_proof_n_body[theme] + visitors_proof_n_bottom;
		visitors_proof_cn_code = visitors_proof_cn_code.replace('{position}', VPJS_Params.settings.position);
		visitors_proof_cn_code = visitors_proof_cn_code.replace('{animation}', VPJS_Params.settings.entrance_animation);
		visitors_proof_cn_code = visitors_proof_cn_code.replace('{image_svg}', image_svg);
		visitors_proof_cn_code = visitors_proof_cn_code.replace('{content}', content);
		visitors_proof_cn_code = visitors_proof_cn_code.replace('{vp_n_logo}', visitors_proof_n_logo);
		jQuery(visitors_proof_cn_code).appendTo('body');
		
		if(visitors_proof_remove_timeout) clearTimeout(visitors_proof_remove_timeout);
		visitors_proof_remove_timeout = setTimeout(function(){
			visitors_proof_remove_notification();
		}, 1000 * VPJS_Params.settings.display_seconds);
	}
	
	function visitors_proof_remove_notification(){
		visitors_proof_status = 0;
		clearTimeout(visitors_proof_remove_timeout, visitors_proof_notify_timeout);
		jQuery('.vp-notification-preview').removeClass('animate__' + VPJS_Params.settings.entrance_animation);
		jQuery('.vp-notification-preview').addClass('animate__' + VPJS_Params.settings.exit_animation);
		visitors_proof_notify_timeout = setTimeout(function (){ visitors_proof_notify() }, 1000 * VPJS_Params.settings.interval_seconds);
		setTimeout(function(){
			jQuery(".vp-notification-preview").remove();
		}, 2000);
	}
	
	function visitors_proof_prepare_notification(d){
		VPJS_Params.settings = d.settings;
		visitors_proof_status = 1;
		visitors_proof_o.slug = d.slug;
    	visitors_proof_o.notify_id = d.notify_id;
		visitors_proof_ordering = visitors_proof_o.ordering = d.ordering;
    	visitors_proof_serve_notification(VPJS_Params.settings.theme, d.image_svg, d.content);
	}
	
	function visitors_proof_notify(){
		jQuery.ajax({
		    type: 'POST',
		    url: visitors_proof_api + "notify",
		    data: visitors_proof_o,
		    dataType: 'JSON',
		    success: function (d){
		    	if(d.done){
		    		visitors_proof_status = -1;
		    	}else{
		    		visitors_proof_prepare_notification(d);
		    	}
		    }
		});
	}
	
	function visitors_proof_close_notification(){
		if(jQuery('.vp-notification-preview').hasClass('closed')) return false;
		jQuery('.vp-notification-preview').addClass('closed');
		jQuery.ajax({
		    type: 'POST',
		    url: visitors_proof_api + "closed",
		    data: visitors_proof_o,
		    dataType: 'JSON',
		    success: function (d){
		    	visitors_proof_remove_notification();
		    }
		});
	}
	
	function visitors_proof_clicked(){
		if(jQuery('.vp-notification-preview').hasClass('clicked')) return false;
		jQuery('.vp-notification-preview').addClass('clicked');
		jQuery.ajax({
		    type: 'POST',
		    url: visitors_proof_api + "clicked",
		    data: visitors_proof_o,
		    dataType: 'JSON',
		    success: function (d){
		    	if(visitors_proof_o.slug == ''){
		    		visitors_proof_remove_notification();
		    	}else{
		    		window.location.href = visitors_proof_o.slug;
		    	}
		    }
		});
	}

	if(visitors_proof_loaded == false){
	    if(visitors_proof_bind_support){
	    	function hasParentWithMatchingSelector (target, selector) {
	    		return [...document.querySelectorAll(selector)].some(el =>
	    			el !== target && el.contains(target)
	    		)
	    	}
	    	
            jQuery(document).bind('click', '#vp-close-notification', function (e){
            	if(jQuery(e.target).prop('id') == 'vp-close-notification') visitors_proof_close_notification();
			});
            jQuery(document).bind('click', '.vp-notification-preview', function (e){
            	if(hasParentWithMatchingSelector(e.target, '.vp-notification-preview') && jQuery(e.target).prop('id') != 'vp-close-notification') visitors_proof_clicked();
        	});
	    }else{
            jQuery(document).on('click', '#vp-close-notification', function (e){
            	visitors_proof_close_notification();
			});
            jQuery(document).on('click', '.vp-notification-preview', function (e){
        		if(jQuery(e.target).prop('id') != 'vp-close-notification') visitors_proof_clicked();
        	});
    	}
	}else{
	    console.log('Twice');
	}
	
	var VPBrowserDetect = {
        init: function() {
    		this.browser = this.searchString(this.dataBrowser) || "Unknown";
    		this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "Unknown";
    		this.OS = this.searchString(this.dataOS) || "Unknown";
    	},
    	searchString: function(data) {
    		for (var i = 0; i < data.length; i++) {
    			var dataString = data[i].string;
    			var dataProp = data[i].prop;
    			this.versionSearchString = data[i].versionSearch || data[i].identity;
    			if (dataString) {
    				if (dataString.indexOf(data[i].subString) != -1) return data[i].identity;
    			} else if (dataProp) return data[i].identity;
    		}
    	},
    	searchVersion: function(dataString) {
    		var index = dataString.indexOf(this.versionSearchString);
    		if (index == -1) return;
    		return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
    	},
    	dataBrowser: [{
    		string: navigator.userAgent,
    		subString: "Chrome",
    		identity: "Chrome"
    	}, {
    		string: navigator.userAgent,
    		subString: "OmniWeb",
    		versionSearch: "OmniWeb/",
    		identity: "OmniWeb"
    	}, {
    		string: navigator.vendor,
    		subString: "Apple",
    		identity: "Safari",
    		versionSearch: "Version"
    	}, {
    		prop: window.opera,
    		identity: "Opera",
    		versionSearch: "Version"
    	}, {
    		string: navigator.vendor,
    		subString: "iCab",
    		identity: "iCab"
    	}, {
    		string: navigator.vendor,
    		subString: "KDE",
    		identity: "Konqueror"
    	}, {
    		string: navigator.userAgent,
    		subString: "Firefox",
    		identity: "Firefox"
    	}, {
    		string: navigator.vendor,
    		subString: "Camino",
    		identity: "Camino"
    	}, { // for newer Netscapes (6+)
    		string: navigator.userAgent,
    		subString: "Netscape",
    		identity: "Netscape"
    	}, {
    		string: navigator.userAgent,
    		subString: "MSIE",
    		identity: "Explorer",
    		versionSearch: "MSIE"
    	}, {
    		string: navigator.userAgent,
    		subString: "Gecko",
    		identity: "Mozilla",
    		versionSearch: "rv"
    	}, { // for older Netscapes (4-)
    		string: navigator.userAgent,
    		subString: "Mozilla",
    		identity: "Netscape",
    		versionSearch: "Mozilla"
    	}],
    	dataOS: [{
    		string: navigator.platform,
    		subString: "Win",
    		identity: "Windows"
    	}, {
    		string: navigator.platform,
    		subString: "Mac",
    		identity: "Mac"
    	}, {
    		string: navigator.userAgent,
    		subString: "iPhone",
    		identity: "iPhone/iPod"
    	}, {
    		string: navigator.userAgent,
    		subString: "Android",
    		identity: "Android"
    	}, {
    		string: navigator.userAgent,
    		subString: "iPad",
    		identity: "Tablet"
    	}, {
    		string: navigator.userAgent,
    		subString: "Tablet",
    		identity: "Tablet"
    	}, {
    		string: navigator.platform,
    		subString: "Linux",
    		identity: "Linux"
    	}]

 	};
 	VPBrowserDetect.init();
    
    /* mobile */
    var isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function() {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function() {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function() {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function() {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };
    
	visitors_proof_o.browser 		= VPBrowserDetect.browser;
	visitors_proof_o.os 			= VPBrowserDetect.OS;
	visitors_proof_o.page_url 		= window.location.href;
	visitors_proof_o.domain_url 	= window.location.host;
	visitors_proof_o.page_id 		= VPJS_Params.page_id;
	visitors_proof_o.page_type 		= VPJS_Params.page_type;
	visitors_proof_o.post_type 		= VPJS_Params.post_type;
	visitors_proof_o.product 		= VPJS_Params.product;
	visitors_proof_o.cf7_id			= VPJS_Params.cf7_id;
	visitors_proof_o.user	 		= VPJS_Params.user;
	visitors_proof_o.referrer 		= document.referrer;
	visitors_proof_o.page_title 	= document.title;
	visitors_proof_o.ordering		= visitors_proof_ordering;
	visitors_proof_o.visit_id		= 0;
	visitors_proof_o.notify_id		= 0;
	visitors_proof_o.slug			= '';
	
	jQuery.ajax({
	    type: 'POST',
	    url: visitors_proof_api + "track",
	    data: visitors_proof_o,
	    dataType: 'JSON',
	    success: function (d){
	    	visitors_proof_o.visit_id = d.visit_id;
	    	if(d.done){
	    		visitors_proof_status = -1;
	    	}else{
	    		visitors_proof_prepare_notification(d);
	    	}
	    }
	});
}