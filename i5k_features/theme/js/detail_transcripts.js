(function(jQuery) {
  Drupal.behaviors.custom_i5k_transcript = {
    attach: function (context, settings) {	  
	  var $table = jQuery('table.tripal-chado_feature-contents-table');
	  var $wrap = jQuery('<div/>').attr('id', 'popup-window');
	  $table.append(
       jQuery('td.tripal-contents-table-td-data').append($wrap)
	  ); 
      var parentDivs = jQuery('#multiAccordion div'),
    childDivs = jQuery('#multiAccordion h3').siblings('div');	
    jQuery("#multiAccordion h2").first().removeClass().addClass('accordionOpen');
    jQuery("#multiAccordion div").first().show();
	jQuery("#multiAccordion h3").first().removeClass().addClass('accordionOpen');
    jQuery("#multiAccordion div div").first().show();
		
    jQuery('#multiAccordion > h2').click(function () {
      //parentDivs.slideUp();
	  //alert("h2 "+jQuery(this).first().next().first().first().html());
      if (jQuery(this).next().is(':hidden')) {	   
	    //jQuery("#multiAccordion h2").removeClass().addClass('accordionClose');
	    jQuery(this).removeClass().addClass('accordionOpen');	
        jQuery(this).next().slideDown();		
      } else {	
	    jQuery(this).removeClass().addClass('accordionClose');				
        jQuery(this).next().slideUp();	  
      }
    });  
    jQuery('#multiAccordion h3').click(function () {
      //childDivs.slideUp();
      if (jQuery(this).next().is(':hidden')) {
	    //jQuery("#multiAccordion h3").removeClass().addClass('accordionClose');
	    jQuery(this).removeClass().addClass('accordionOpen');		
        jQuery(this).next().slideDown();
      } else {
	    jQuery(this).removeClass().addClass('accordionClose');
        jQuery(this).next().slideUp();
      }
    });		
  }
};
})(jQuery);