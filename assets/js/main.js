+(function($) {

	"use strict";

	$('.wpbean_fopo_popup_hystmodal').appendTo(document.body);
	
	const fopoModal = new HystModal({
		linkAttributeName: 'data-hystmodal',
	});

	function wpbean_fopo_trigger_magnificPopup(id){
		// console.log(id)
		var wrapper      = $(document).find('.wpbean_fopo_popup_wrapper_' + id),
		    closeonbg    = wrapper.data('closeonbg'),
		    escapekey    = wrapper.data('escapekey');

			fopoModal.config.closeOnOverlay = closeonbg;
			fopoModal.config.closeOnEsc     = escapekey;
			fopoModal.config.backscroll     = true;

			fopoModal.open( '#wpbean_fopo_popup_body_' + id );
	}

	// Button click Trigger.
	$(document).on('click', '.wpbean_fopo_init_popup', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
		//console.log(id)
		wpbean_fopo_trigger_magnificPopup(id);
    });



	// Image Trigger on Click.
	$(document).on('click', '.wpbean_fopo_popup_image_init_click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
		wpbean_fopo_trigger_magnificPopup(id);
    });

	$(document).find('.wpbean_fopo_popup_image_init_hover').on( 'mouseenter', function(e){
		e.preventDefault();
        var id = $(this).data('id');
		wpbean_fopo_trigger_magnificPopup(id);
	});

	if( typeof wpbean_fopo_Auto_Triggers != 'undefined' ){
		$.each(wpbean_fopo_Auto_Triggers, function( index, trigger ) {
			// console.log( trigger );
			if( trigger.poopup_trigger_type == 'custom_css_class' ){
				$(document).on(trigger.css_trigger_type, trigger.css_classes, function(e) {
					e.preventDefault();
					wpbean_fopo_trigger_magnificPopup(trigger.id);
				});
			}

			if( trigger.poopup_trigger_type == 'automatic' ){

				if (typeof(localStorage) != 'undefined' ) {
					var trigger_seeen = localStorage.getItem( 'wpbean_fopo_automatic_trigger_seeen' + trigger.id);
				}else{
					var trigger_seeen = 'no';
				}

				if( trigger.automatic_trigger_save == 'off' ){
					var trigger_seeen = 'no';
				}

				if ( $(document).find('.wpbean_fopo_popup_wrapper_' + trigger.id).length > 0){

					// On page Load
					if( trigger.automatic_trigger_type == 'page_load' && trigger_seeen != 'yes' ){
						$( window ).on( 'load', function() {
							wpbean_fopo_trigger_magnificPopup(trigger.id);
							if (typeof(localStorage) != 'undefined' &&  trigger.automatic_trigger_save == 'on' ) {
								localStorage.setItem( 'wpbean_fopo_automatic_trigger_seeen' + trigger.id, 'yes');
							}
						});
					}

					// On page Load and a delay

					if( trigger.automatic_trigger_type == 'page_load_delay' && trigger_seeen != 'yes' ){
						setTimeout(function() { 
							wpbean_fopo_trigger_magnificPopup(trigger.id);
							if (typeof(localStorage) != 'undefined' &&  trigger.automatic_trigger_save == 'on' ) {
								localStorage.setItem( 'wpbean_fopo_automatic_trigger_seeen' + trigger.id, 'yes');
							}
						}, trigger.automatic_trigger_delay);
					}

					// On page scroll to a position
					if( trigger.automatic_trigger_type == 'scroll_position' ){
						var scroll_init = true;
						$(document).scroll(function() {    

							var scroll = $(this).scrollTop();

							if (scroll >= 500 && scroll_init && trigger_seeen != 'yes') {
								wpbean_fopo_trigger_magnificPopup(trigger.id);
								scroll_init = false;
								if (typeof(localStorage) != 'undefined' &&  trigger.automatic_trigger_save == 'on' ) {
									localStorage.setItem( 'wpbean_fopo_automatic_trigger_seeen' + trigger.id, 'yes');
								}
							}
						});
					}


					// On page scroll to a position
					if( trigger.automatic_trigger_type == 'page_leaving' && trigger_seeen != 'yes' ){
						$(document).one('mouseleave', function(){
							wpbean_fopo_trigger_magnificPopup(trigger.id);
							if (typeof(localStorage) != 'undefined' &&  trigger.automatic_trigger_save == 'on' ) {
								localStorage.setItem( 'wpbean_fopo_automatic_trigger_seeen' + trigger.id, 'yes');
							}
						});   
					}
				}
			}	
		});
	}

})(jQuery);