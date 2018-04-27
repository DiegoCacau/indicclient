jQuery(document).ready(function() {

	jQuery('ul.tabs li').click(function(){
        var tab_id = jQuery(this).attr('data-tab');

        jQuery('ul.tabs li').removeClass('current');
        jQuery('.tab-content').removeClass('current');

        jQuery(this).addClass('current');
        jQuery("#"+tab_id).addClass('current');
    })

	jQuery("#button_pendente_email").click(function(){
		jQuery('#send_button_pendente_email').simulateClick('click');
	});

	jQuery("#button_primeiro_email").click(function(){
		jQuery('#send_button_primeiro_email').simulateClick('click');
	});

	jQuery("#button_visita_email").click(function(){
		jQuery('#send_button_visita_email').simulateClick('click');
	});

	jQuery("#button_assinado_email").click(function(){
		jQuery('#send_button_assinado_email').simulateClick('click');
	});


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