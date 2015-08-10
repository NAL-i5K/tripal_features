/**
 * @file
 * jQuery code.
 * 
 */

// Setting up popup.
// 0 means disabled; 1 means enabled.
var popupStatus = 0;

/**
 * Loading popup with jQuery.
 */
function popup_message_load_popup() {
  // Loads popup only if it is disabled.
  if (popupStatus == 0) {
    jQuery("#popup-message-background").css( {
      "opacity": "0.7"
    });
    jQuery("#popup-message-background").fadeIn("slow");
    jQuery("#popup-message-window").fadeIn("slow");
    popupStatus = 1;
  }
}

/**
 * Disabling popup with jQuery.
 */
function popup_message_disable_popup() {
  // Disables popup only if it is enabled.
  if (popupStatus == 1) {
    jQuery("#popup-message-background").fadeOut("slow");
    jQuery("#popup-message-window").fadeOut("slow");
    popupStatus = 0;
  }
}

/**
 * Centering popup.
 */
function popup_message_center_popup(width, height) {
  // Request data for centering.
  var windowWidth = document.documentElement.clientWidth;
  var windowHeight = document.documentElement.clientHeight;

  var popupWidth = 0
  if (typeof width == "undefined") {
    popupWidth = jQuery("#popup-message-window").width();
  }
  else {
    popupWidth = width;
  }
  var popupHeight = 0
  if (typeof width == "undefined") {
    popupHeight = jQuery("#popup-message-window").height();
  }
  else {
    popupHeight = height;
  }

  // Centering.
  jQuery("#popup-message-window").css( {
    "position": "absolute",
    "width" : popupWidth + "px",
    "height" : popupHeight + "px",
    "top": windowHeight / 2 - popupHeight / 2,
    "left": windowWidth / 2 - popupWidth / 2
  });

  // Only need force for IE6.
  jQuery("#popup-message-background").css( {
    "height": windowHeight
  });
}

/**
 * Display popup message.
 */
function popup_message_display_popup(fid, type, width, height, unspliced, strand, fmin, fmax) {
  fmin = (typeof fmin === "undefined") ? "":fmin;
  fmax = (typeof fmax === "undefined") ? "":fmax;
  strand = (strand === '-1') ? "negative":strand;
  
  unspliced = (typeof unspliced === "undefined") ? "0" : unspliced;
  var popup_title = "";
  if(fmin && fmax) {  
    popup_title = (unspliced == '1')?(fid+"-"+type+"-"+strand+"-"+unspliced):(fid+"-"+type+"-"+strand+"-"+unspliced+"-"+fmin+"-"+fmax);	  	
  } else {
    popup_title = (unspliced == '1')?(fid+"-"+type+"-"+strand+"-"+unspliced):(fid+"-"+type+"-"+strand);	  
  }
  jQuery.ajax({
    type: 'POST',
    url: '/zclip/'+popup_title,        
    data: '',		
    success: function(data) { 	 
      jQuery('#popup-window').append(data);	
      // Loading popup.
	  
      popup_message_center_popup(width, height);
      popup_message_load_popup();
    jQuery(".l-region--navigation").css({"display":"none"});
	
      // Closing popup.
      // Click the x event!
      jQuery("#popup-message-close").click(function() {
        jQuery('#popup-window').text('');
        popup_message_disable_popup();
	    jQuery(".l-region--navigation a:visited").css({"background":"none"}); 
	    jQuery(".l-region--navigation").css({"display":"block"});
      });
      // Click out event!
      jQuery("#popup-message-background").click(function() {
        jQuery('#popup-window').text('');
        popup_message_disable_popup();
	    jQuery(".l-region--navigation a:visited").css({"background":"none"});
	    jQuery(".l-region--navigation").css({"display":"block"});
      });
      // Press Escape event!
      jQuery(document).keydown(function(e) {	  
      if (e.keyCode == 27 && popupStatus == 1) {
	    jQuery('#popup-window').text('');
        popup_message_disable_popup();	  
	    jQuery(".l-region--navigation a:visited").css({"background":"none"});
        jQuery(".l-region--navigation").css({"display":"block"});
	  }
    });    		  
    },
    error: function(){ alert("ERROR"); },            
    cache:false
  });
}
