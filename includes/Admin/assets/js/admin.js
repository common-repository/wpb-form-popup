/**
 * jQuery Conditions 1.0.1
 *
 * Copyright 2016 Bejamin Rojas
 * @license Released under the MIT license.
 * http://jquery.org/license
 */
!function(e){"use strict";e.fn.conditions=function(i){return this.each(function(t,o){new n(o,i,e.fn.conditions.defaults).init()})},e.fn.conditions.defaults={condition:null,actions:{},effect:"fade"};var n=function(n,i,t){var o=this;o.element=e(n),o.defaults=t,o.conditions=i,o._init=!1,e.isArray(o.conditions)||(o.conditions=[o.conditions]),e.each(o.conditions,function(n,i){o.conditions[n]=e.extend({},o.defaults,i)})};n.prototype.init=function(){var n=this;n._init=!0,e(n.element).on("change",function(){n.matchConditions()}),e(n.element).on("keyup",function(){n.matchConditions()}),n.matchConditions(!0)},n.prototype.matchConditions=function(n){var i=this;n||(i._init=!1),e.each(i.conditions,function(n,t){var o=!1,a=!0;e.isArray(t.conditions)||(t.conditions=[t.conditions]),e.each(t.conditions,function(n,i){switch(i=e.extend({element:null,type:"val",operator:"==",condition:null,multiple:"single"},i),i.element=e(i.element),i.type){case"value":case"val":switch(i.operator){case"===":case"==":case"=":if(e.isArray(i.element.val())){var t=!1,c=!0;e.each(i.element.val(),function(e,n){n===i.condition?t=!0:c=!1}),o="single"==i.multiple?t:c}else o=i.element.val()===i.condition;break;case"!==":case"!=":if(e.isArray(i.element.val())){var t=!1,c=!0;e.each(i.element.val(),function(e,n){n!==i.condition?t=!0:c=!1}),o="single"==i.multiple?t:c}else o=i.element.val()!==i.condition;break;case"array":if(e.isArray(i.element.val())){var t=!1,c=i.element.val().length===i.condition.length;e.each(i.element.val(),function(n,o){-1!==e.inArray(o,i.condition)?t=!0:c=!1}),o="single"==i.multiple?t:c}else o=-1!==e.inArray(i.element.val(),i.condition);break;case"!array":if(e.isArray(i.element.val())){var t=!1,c=!0,s=[];e.each(i.element.val(),function(n,o){-1===e.inArray(o,i.condition)?t=!0:s.push(o)}),s.length==i.condition.length&&(c=!1),o="single"==i.multiple?t:c}else o=-1===e.inArray(i.element.val(),i.condition)}break;case"checked":switch(i.operator){case"is":o=i.element.is(":checked");break;case"!is":o=!i.element.is(":checked")}}!o&&a&&(a=!1)}),a?e.isEmptyObject(t.actions.if)||(e.isArray(t.actions.if)||(t.actions.if=[t.actions.if]),e.each(t.actions.if,function(e,n){i.showAndHide(n,t.effect)})):e.isEmptyObject(t.actions.else)||(e.isArray(t.actions.else)||(t.actions.else=[t.actions.else]),e.each(t.actions.else,function(e,n){i.showAndHide(n,t.effect)}))})},n.prototype.showAndHide=function(n,i){var t=this;switch(n.action){case"show":t._show(e(n.element),i);break;case"hide":t._hide(e(n.element),i)}},n.prototype._show=function(e,n){if(this._init)e.show();else switch(n){case"appear":e.show();break;case"slide":e.slideDown();break;case"fade":e.fadeIn(300)}},n.prototype._hide=function(e,n){if(this._init)e.hide();else switch(n){case"appear":e.hide();break;case"slide":e.slideUp();break;case"fade":e.fadeOut(300)}}}(jQuery);

(function($) {
    
    "use strict";

    const { __, _x, _n, _nx } = wp.i18n;

    /*  
    * Form Condition
    */
    function wpbean_fopo_form_condition(){
        $(document).find('.wpbean-fopo-shortcodes-list-items > .wpbean-fopo-shortcodes-list-item-wrapper > .wpbean-fopo-shortcodes-list-item').each( function(){
            var form = $(this);

            form.find( '.wpbean-fopo-form-group' ).each(function() {
                var form_group = $(this);

                if( form_group.data('condition-field') && form_group.data('condition-value') ){

                    id = form.find('#' + form_group.data('condition-field'));
        
                    $( id ).conditions( {
                        conditions: {
                            element:  id,
                            type:     'value',
                            operator:  '=',
                            condition: form_group.data('condition-value')
                        },
                        actions: {
                            if: {
                                element: form_group,
                                action:  'show'
                            },
                            else: {
                                element: form_group,
                                action:  'hide'
                            }
                        },
                        effect: 'slide'
                    } );
                }
            });
        });
    }

    // Switches option sections

    function wpbean_fopo_switches_meta_sections(){

        $('.wpbean-fopo-shortcodes-list-item-wrapper').each(function(){

            var shortcode = $(this),
                id        = shortcode.find('.wpbean-fopo-shortcodes-list-item ').data('id'),
                group     = shortcode.find('.wpbean-fopo-group'),
                activetab = '';

            group.hide();
            if (typeof(localStorage) != 'undefined' ) {
                activetab = localStorage.getItem("wpbean_fopo_activetab_" + id);
            }
            if (activetab != '' && $(activetab).length ) {
                shortcode.find(activetab).fadeIn();
            } else {
                shortcode.find('.wpbean-fopo-group:first').fadeIn();
            }
            
            if (activetab != '' && $(activetab + '-tab').length ) {
                shortcode.find(activetab + '-tab').addClass('wpbean-fopo-nav-tab-active');
            }
            else {
                shortcode.find('.wpbean-fopo-nav-tab-wrapper a:first').addClass('wpbean-fopo-nav-tab-active');
            }
            
            shortcode.find('.wpbean-fopo-nav-tab-wrapper a').click(function(evt) {
                evt.preventDefault();

                var clicked_group = $(this).attr('href'),
                    form_id       = $(this).closest('.wpbean-fopo-shortcodes-list-item').data('id');

                shortcode.find('.wpbean-fopo-nav-tab-wrapper a').removeClass('wpbean-fopo-nav-tab-active');
                $(this).addClass('wpbean-fopo-nav-tab-active').blur();
                
                if (typeof(localStorage) != 'undefined' ) {
                    localStorage.setItem( "wpbean_fopo_activetab_" + form_id, $(this).attr('href'));
                }
                shortcode.find('.wpbean-fopo-group').hide();
                shortcode.find(clicked_group).fadeIn();
            });
        });
    }


    /*  
    * Save ShortCode Meta
    */

    function wpbean_fopo_save_shortcodes_meta() {
        $(document).on( 'click', '.wpbean-fopo-save-meta-button', function(e) {
            e.preventDefault();

            var btn            = $(this),
                forms          = $(document).find('.wpbean-fopo-shortcodes-list-items > .wpbean-fopo-shortcodes-list-item-wrapper > .wpbean-fopo-shortcodes-list-item'),
                forms_data     = [];

                forms.each(function(i) {

                    if( typeof tinyMCE != 'undefined'){
                        tinyMCE.triggerSave();
                    }

                    var form           = $(this),
                        post_id        = form.data('id'),
                        form_serialize = form.serializeArray(),
                        indexed_array  = {};

                    $.map(form_serialize, function(n, i){

                        indexed_array['post_id'] = post_id;

                        if(indexed_array[n['name']] !== undefined){
                            indexed_array[n['name']].push(n['value']);
                        } else if(n['name'] !== undefined && n['name'].indexOf('[]') > -1){
                            indexed_array[n['name']] = new Array(n['value']);
                        } else {
                            indexed_array[n['name']] = n['value'];
                        }
                    });

                    var output_array = (!$.isEmptyObject(indexed_array) ? JSON.stringify(indexed_array, null, 2) : '');

                    forms_data[i] = output_array;
                });

                //console.log(forms_data);

                wp.ajax.send( {
                    data: {
                        action                                 : 'wpbean_fopo_save_shortcodes_meta',
                        _wpbean_fopo_save_shortcodes_meta_nonce: wpbean_fopo_Vars._wpbean_fopo_nonce,
                        _wpbean_fopo_forms_data                : forms_data,
                    },
                    beforeSend : function ( xhr ) {
                        btn.addClass('wpbean-fopo-btn-loading');
                        btn.attr('disabled', true);
                    },
                    success: function( res ) {
                        btn.removeClass('wpbean-fopo-btn-loading');
                        btn.removeAttr('disabled');

                        new jBox('Notice', {
                            content  : res,
                            color    : 'green',
                            autoClose: 2000,
                            addClass : 'wpbean-fopo-popup-notice',
                            position : {
                                x: 'right',
                                y: 'bottom',
                            },
                            offset: {
                                x: -30,
                                y: -30,
                            }
                        });
                    },
                    error: function(error) {
                        alert( error );
                    }
                });
                
        });
    }


    /* Checkbox meta uncheck save issue fix */
    function wpbean_fopo_checkbox_uncheck_iusse_fix(){
        $('.wpbean-fopo-fieldset input[type=checkbox]').each(function(){
            var self     = $(this),
                name     = self.attr('name'),
                fieldset = self.closest('.wpbean-fopo-fieldset').find('fieldset');

            self.click(function() {
                if (self.is(":checked") == true) {
                    fieldset.find('input[type=hidden]').remove();
                } else {
                    fieldset.prepend('<input type="hidden" name="'+name+'" value="off" />');
                }
            });
        });
    }

    /* Add New Popup */
    function wpbean_fopo_add_new_popup() {
        $('.wpbean-fopo-add-new-popup').on( 'click', function(e){
            var btn   = $(this),
                alert = $('.wpbean-fopo-shortcodes-list-items > .wpbean-fopo-alert-error');

            e.preventDefault();

            wp.ajax.send( {
                ajax_option: 'fire_wpbean_fopo_add_shortcode',
                data       : {
                    action                          : 'wpbean_fopo_add_new_shortcode',
                    _wpbean_fopo_add_shortcode_nonce: wpbean_fopo_Vars._wpbean_fopo_nonce,
                },
                beforeSend : function ( xhr ) {
                    btn.addClass('wpbean-fopo-btn-loading');
                },
                success: function( res ) {
                    btn.removeClass('wpbean-fopo-btn-loading');

                    new jBox('Notice', {
                        content  : __( 'New Popup Added.', 'wpb-form-popup' ),
                        color    : 'green',
                        autoClose: 1200,
                        addClass : 'wpbean-fopo-popup-notice',
                        position : {
                            x: 'right',
                            y: 'bottom',
                        },
                        offset: {
                            x: -30,
                            y: -30,
                        }
                    });

                    if (alert.length > 0){
                        alert.fadeOut( function(){
                            alert.remove();
                        });
                    }

                    $('.wpbean-fopo-shortcodes-list-items').prepend(res.content);

                    setTimeout(
                        function() {
                            $('.wpb-fopo-c-ripple').remove();
                        },
                        400
                    );
                },
                error: function(error) {
                    //alert( error );
                    console.log(error);
                }
            });
        });
    }

    /* Delete a Popup */
    function wpbean_fopo_delete_popup() {
        $('body').on( 'click', '.wpbean-fopo-shortcode-delete', function(e) {
            var btn             = $(this),
                shortcode       = btn.closest('.wpbean-fopo-shortcodes-list-item-wrapper'),
                id              = shortcode.data('id'),
                shortcode_items = btn.closest('.wpbean-fopo-shortcodes-list-items'),
                shortcode_item  = shortcode_items.find('.wpbean-fopo-shortcodes-list-item-wrapper');

            e.preventDefault();

            var wpbean_fopo_delete_popup = new jBox('Confirm', {
                id            : 'wpbean-fopo-shortcode-delete-' + id,
                width         : 480,
                height        : 'auto',
                addClass      : 'wpbean-fopo-popup-modal',
                closeButton   : 'box',
                animation     : 'slide',
                content       : '<h3>'+ __( 'Are you sure to delete this popup shortcode #', 'wpb-form-popup' ) + id + '?' +'</h3>',
                confirmButton : __( 'Yes', 'wpb-form-popup' ),
                cancelButton  : __( 'No', 'wpb-form-popup' ),
                closeOnConfirm: true,
                confirm : function(){
                    wpbean_fopo_delete_popup.destroy();
                    wp.ajax.send( {
                        ajax_option : 'fire_wpbean_fopo_delete_shortcode',
                        data: {
                            action                             : 'wpbean_fopo_delete_shortcode',
                            _wpbean_fopo_delete_shortcode_nonce: wpbean_fopo_Vars._wpbean_fopo_nonce,
                            _wpbean_fopo_shortcode_id          : id,
                        },
                        beforeSend : function ( xhr ) {
                            btn.addClass('wpbean-fopo-btn-loading');
                        },
                        success: function( res ) {
                            btn.removeClass('wpbean-fopo-btn-loading');

                            new jBox('Notice', {
                                content  : res,
                                color    : 'green',
                                autoClose: 1200,
                                addClass : 'wpbean-fopo-popup-notice',
                                position : {
                                    x: 'right',
                                    y: 'bottom',
                                },
                                offset: {
                                    x: -30,
                                    y: -30,
                                }
                            });
        
                            shortcode.fadeOut( function(){
                                shortcode.remove();
                            });

                            if (shortcode_item.length === 1){
                                shortcode_items.append('<div class="wpbean-fopo-alert wpbean-fopo-alert-error">'+ __( 'No Popup ShortCode Found. Please Add Some.', 'wpb-form-popup' ) +'</div>');
                            }
                        },
                        error: function(error) {
                            alert( error );
                        }
                    });
                }
            }).open();
        });
    }


    /* Duplicate a Popup */
    function wpbean_fopo_duplicate_popup() {
        $('body').on( 'click', '.wpbean-fopo-shortcode-duplicate', function(e) {
            var btn       = $(this),
                shortcode = btn.closest('.wpbean-fopo-shortcodes-list-item-wrapper'),
                id        = shortcode.data('id');

            e.preventDefault();

            var wpbean_fopo_duplicate_popup = new jBox('Confirm', {
                id           : 'wpbean-fopo-shortcode-duplicate-' + id,
                width        : 480,
                height       : 'auto',
                addClass     : 'wpbean-fopo-popup-modal',
                closeButton  : 'box',
                animation    : 'slide',
                content      : '<h3>'+__( 'Are you sure to duplicate this shortcode #', 'wpb-form-popup' ) + id + '?'+'</h3>',
                confirmButton: __( 'Yes', 'wpb-form-popup' ),
                cancelButton : __( 'No', 'wpb-form-popup' ),
                confirm : function(){
                    wpbean_fopo_duplicate_popup.destroy();

                    wp.ajax.send( {
                        ajax_option : 'fire_wpbean_fopo_duplicate_shortcode',
                        data: {
                            action                                : 'wpbean_fopo_duplicate_shortcode',
                            _wpbean_fopo_duplicate_shortcode_nonce: wpbean_fopo_Vars._wpbean_fopo_nonce,
                            _wpbean_fopo_shortcode_id             : id,
                        },
                        beforeSend : function ( xhr ) {
                            btn.addClass('wpbean-fopo-btn-loading');
                        },
                        success: function( res ) {
                            btn.removeClass('wpbean-fopo-btn-loading');

                            new jBox('Notice', {
                                content  : __( 'Duplicated Successfully', 'wpb-form-popup' ),
                                color    : 'green',
                                autoClose: 1200,
                                addClass : 'wpbean-fopo-popup-notice',
                                position : {
                                    x: 'right',
                                    y: 'bottom',
                                },
                                offset: {
                                    x: -30,
                                    y: -30,
                                }
                            });
        
                            $('.wpbean-fopo-shortcodes-list-items').prepend(res);

                            setTimeout(
                                function() {
                                    $('.wpb-fopo-c-ripple').remove();
                                },
                                400
                            );
                        },
                        error: function(error) {
                            alert( error );
                        }
                    });
                }
            }).open();
        });
    }

    function wpbean_fopo_tinymce_reinit(){
        $(document).find('.wpbean-fopo-shortcodes-list-item').each(function() {
            var t       = $(this),
                editors = t.find('.wpb_wp_editor_field');

                editors.each(function() {
                    var editor_id = $(this).find('textarea.wp-editor-area').attr('id');
                    tinymce.execCommand( 'mceRemoveEditor', false, editor_id );
                    tinymce.execCommand( 'mceAddEditor', false, editor_id );
                })
        })
    }

    /* Show ShortCode*/
    function wpbean_fopo_show_shortcode() {
        $('body').on( 'click', '.wpbean-fopo-shortcode-shortcode-popup', function(e) {
            var btn       = $(this),
                shortcode = btn.closest('.wpbean-fopo-shortcodes-list-item-wrapper'),
                id        = shortcode.data('id');

            e.preventDefault();

            wp.ajax.send( {
                data: {
                    action                           : 'wpbean_fopo_fire_show_shortcode',
                    _wpbean_fopo_show_shortcode_nonce: wpbean_fopo_Vars._wpbean_fopo_nonce,
                    _wpbean_fopo_shortcode_id        : id,
                },
                beforeSend : function ( xhr ) {
                    btn.addClass('wpbean-fopo-btn-loading');
                },
                success: function( res ) {
                    btn.removeClass('wpbean-fopo-btn-loading');
                    
                    new jBox('Modal', {
                        width         : 780,
                        height        : 'auto',
                        preventDefault: true,
                        addClass      : 'wpbean-fopo-popup-modal wpbean-fopo-shortcode-popup wpbean-fopo-no-padding',
                        closeButton   : 'box',
                        animation     : 'slide',
                        title: res.title,
                        content       : '<div class="wpbean-fopo-pro-discount-message-text">' + res.content + '</div>',
                    }).open();

                    wpbean_fopo_show_shortcode_tabs();
                    wpbean_fopo_copy_shortcode();
                },
                error: function(error) {
                    alert( error );
                }
            });
        });
    }

    /**
     * Show ShortCode Popup tab
     */

    function wpbean_fopo_show_shortcode_tabs() {
        $('.wpbean-fopo-shortcode-tabs').each(function(){

            var t             = $(this),
                nav_item      = t.find('.wpbean-fopo-shortcode-tabs-nav ul li'),
                nav_first     = t.find('.wpbean-fopo-shortcode-tabs-nav ul li:first-child'),
                content       = t.find('.wpbean-fopo-shortcode-tab-content'),
                content_first = t.find('.wpbean-fopo-shortcode-tab-content:first');

            // Show the first tab and hide the rest
            nav_first.addClass('wpbean-fopo-shortcode-nav-active');
            content.hide();
            content_first.show();

            // Click function
            nav_item.click(function ( e ) {
                e.preventDefault();
                nav_item.removeClass('wpbean-fopo-shortcode-nav-active');
                $(this).addClass('wpbean-fopo-shortcode-nav-active');
                content.hide();

                var activeTab = $(this).find('a').attr('href');
                $(activeTab).fadeIn();
                return false;
            });
        });
    }

    /**
     * Copy ShortCode to clipboard
     */

    function wpbean_fopo_copy_shortcode(){
        $( 'body' ).on( 'click', '.wpbean-fopo-copy-shortcode', function( e ) {
            var range = document.createRange();
            var sel   = window.getSelection();
            range.setStartBefore(this.firstChild);
            range.setEndAfter(this.lastChild);
            sel.removeAllRanges();
            sel.addRange(range);
    
            try {  
                var successful = document.execCommand( 'copy' );  
            } catch( err ) {  
                console.error( __( 'Unable to copy', 'wpb-form-popup' ) ); 
            } 		
        });
    }


    
    /**
     * Media Upload Field
     */

    function wpbean_fopo_media_upload_field(){

        var meta_image_frame, btn, media_attachment;

        $('.wpbean_fopo_image_browse').click(function (e) {
            
            e.preventDefault();
            
            btn     = $(this);

            var wrapper = btn.siblings('.wpb-image-preview-wrapper'),
                input   = btn.siblings('.wpbean_fopo_image_id');

            if (meta_image_frame) {
                meta_image_frame.open();
                return;
            }

            meta_image_frame = wp.media({
                title : btn.data('uploader_title'),
                button: {
                    text: btn.data('uploader_button_text'),
                },
                library : {
                    type: [ 'image' ]
                },
                multiple: false,
            });

            meta_image_frame.on('select', function () {
                media_attachment = meta_image_frame.state().get('selection').first().toJSON();
                wrapper.find('.wpb-image-src').attr('src', '');

                if (media_attachment.id) {
                    wrapper.fadeIn();
                    wrapper.find('.wpb-image-src').attr('src', media_attachment.sizes.thumbnail.url);
                    input.val(media_attachment.id).change();
                }
            });

            meta_image_frame.open();
        });

        $(document).on('click', '.wpb-image-remove', function (e) {
            e.preventDefault();

            var self     = $(this),
                fieldset = self.closest('.wpbean-fopo-fieldset'),
                wrapper  = fieldset.find('.wpb-image-preview-wrapper'),
                input    = fieldset.find('.wpbean_fopo_image_id');

                input.val('').change();
                wrapper.fadeOut();
        });
    }

    /**
     * Ajax Select2
     */

    function wpbean_fopo_ajax_select2_init(){

        if (!!$.prototype.select2)
        $('.wpbean-fopo-ajax-select2').select2({
            ajax: {
                url     : ajaxurl,
                dataType: 'json',
                delay   : 250,
                cache   : true,
                data: function (params) {
                    return {
                        _wpbean_fopo_select2_nonce           : wpbean_fopo_Vars._wpbean_fopo_nonce,
                        wpbean_fopo_ajax_select2_search_query: params.term, // search query
                        action                               : 'wpbean_fopo_ajax_select2_get_items', // AJAX action for admin-ajax.php
                        data_type                            : $(this).data('type'),
                        post_type                            : $(this).data('post_type'),
                        taxonomy                             : $(this).data('taxonomy') ? $(this).data('taxonomy'): 'null',
                    }
                },
                processResults: function( data ) {
                    var options = [];
                    if ( data ) {
                        // data is the array of arrays, and each of them contains ID and the Label of the option
                        $.each( data.data, function( index, text ) { // do not forget that "index" is just auto incremented value
                            options.push( { id: text[0], text: text[1]  } );
                        });
                    }
                    return {
                        results: options
                    };
                },
            },
            minimumInputLength: 3, // the minimum of symbols to input before perform a search
        });
    }
    
    /**
     * Title Bind
     */

    function wpbean_fopo_shortcode_title_bind(){
        $('#wpbean_fopo_shortcode_title').blur(function(){
            var objVal = $(this).val();
            if(objVal != ''){
                $('.wpbean-fopo-shortcode-header-left h3').text(objVal);
                $('.wpbean-fopo-section-header-shortcode-edit-page .shortcode-title').text( ' - ' + $('.wpbean-fopo-section-header-shortcode-edit-page .shortcode-title').val() + objVal);
            }
        });
    }


    /**
     * image select radio
     */

    function wpbean_fopo_image_select_radio(){
        $('.wpb-radio-image img').on( 'click', function(){
            $(this).next('input[type="radio"]').prop('checked', true);
        });

        $('.wpb-radio-image').on( 'click', function(){
            var self       = $(this),
                wrapper    = self.closest('.wpb-image-select'),
                image_item = wrapper.find('.wpb-radio-image ');

            image_item.removeClass('wpb-radio-image-active');
            self.addClass('wpb-radio-image-active');
        });
    }


    $(document).ajaxComplete(function( event,xhr,options ){
        wpbean_fopo_form_condition();
    });

    $(document).ready(function(){
        setTimeout(
            function() {
                $('.wpb-fopo-c-ripple').remove();
            },
            400
        );
        $('.wpbean-fopo-shortcode-list-page-content').fadeIn();
        $('.wpbean-fopo-select-buttons').togglebutton();
        $('.wp-color-picker-field').wpColorPicker();

        wpbean_fopo_form_condition();
        wpbean_fopo_checkbox_uncheck_iusse_fix();
        wpbean_fopo_save_shortcodes_meta();
        wpbean_fopo_switches_meta_sections();
        wpbean_fopo_show_shortcode();
        wpbean_fopo_media_upload_field();
        wpbean_fopo_ajax_select2_init();
        wpbean_fopo_shortcode_title_bind();
        wpbean_fopo_image_select_radio();
        wpbean_fopo_add_new_popup();
        wpbean_fopo_delete_popup();
        wpbean_fopo_duplicate_popup();
    });

})(jQuery);