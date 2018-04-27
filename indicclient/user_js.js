jQuery(document).ready(function() {

	jQuery("#form_client").hide();
	jQuery("#indcados_client").hide();

	jQuery("#indicar_amigo").click(function(){
		if(jQuery("#indcados_client").attr("display") !== 'none'){
			jQuery("#indcados_client").hide();
		}
		jQuery("#form_client").toggle();

	});

	jQuery("#amigo_indicado").click(function(){
		if(jQuery("#form_client").attr("display") !== 'none'){
			jQuery("#form_client").hide();
		}
		jQuery("#indcados_client").toggle();

	});

	jQuery("#amigo_indicado").click(function(){
		
	});


	jQuery("#enviar_indic").click(function(){
		jQuery('#enviar_indic_not').simulateClick('click');
	});

	
	jQuery(".select_turno").change(function() {
       jQuery('#clien_hora').attr("value", jQuery(this).val());
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