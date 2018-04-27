jQuery(document).ready(function() {

	var classe_show = '';

	jQuery('.tr_indic').click(function(){
		jQuery('.marcado').removeClass('marcado');
		jQuery(this).addClass('marcado');
		if(classe_show !== ''){
			jQuery('.'+classe_show).addClass('indic_none');
		}
	    classe_show  = jQuery(this).attr("id");
		jQuery('.'+classe_show).removeClass('indic_none');
	});

	jQuery('.tr_clicavel').click(function(){
		jQuery('.tr_clicada').removeClass('tr_clicada');
		jQuery(this).addClass('tr_clicada');
		jQuery('#change_status').attr('value', jQuery(this).attr('value'));
	});	

	jQuery("#change_status_2").click(function(){
		if(jQuery('#change_status').attr('value')){
			jQuery('#change_status').simulateClick('click');
		}
	});

	jQuery('ul.tabs li').click(function(){
        var tab_id = jQuery(this).attr('data-tab');

        jQuery('ul.tabs li').removeClass('current');
        jQuery('.tab-content').removeClass('current');

        jQuery(this).addClass('current');
        jQuery("#"+tab_id).addClass('current');
    })
	
	jQuery.fn.simulateClick = function() {
        return this.each(function() {
        	if('createEvent' in document) {
                var doc = this.ownerDocument,
                evt = doc.createEvent('MouseEvents');
                evt.initMouseEvent('click', true, true, doc.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
                this.dispatchEvent(evt);
            } else {
                this.click(); // IE Boss!
            }
        });
    }

});