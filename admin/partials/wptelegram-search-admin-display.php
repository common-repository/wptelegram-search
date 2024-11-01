<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://t.me/ManzoorWaniJK
 * @since      1.0.0
 *
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<script type="text/javascript">
$ = jQuery;
var baseURL = 'https://api.telegram.org/bot';
function sendAjaxRequest( bot_token, endpoint, data, callback, section ){
     $.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        url: baseURL + bot_token + endpoint,
        dataType: "json",
        crossDomain:true,
        data: JSON.stringify( data ),
        complete: function( jqXHR ){
          window[callback]( jqXHR, data.chat_id, section );
        }
    });
}
function setWebhook( button ) {
	$('#wptelegram-search-webhook-info').html('');
	$.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        url: $(button).attr("href"),
        crossDomain:true,
        complete: function( jqXHR ){
        	if ( undefined == jqXHR || '' == jqXHR.responseText ){
        		$('#wptelegram-search-webhook-info').html('<?php  echo __("Error: Could not connect", "wptelegram"); ?>');
        	} else if ( false == JSON.parse( jqXHR.responseText ).ok ) {

        		$('#wptelegram-search-webhook-info').html('<?php  echo __( 'Error', 'wptelegram'); ?>' + ' ' + JSON.parse( jqXHR.responseText ).error_code + ': ' + JSON.parse( jqXHR.responseText ).description);
        	} else{
        		location.reload();
        	}
        }
    });
}

function validateToken(section) {
    var bot_token = $('input[name="wptelegram_'+section+'[bot_token]"]').val();
    var regex = new RegExp(/^\d{9}:[\w-]{35}$/);

    if ( regex.test( bot_token ) || '' == bot_token ) {
        $('#wptelegram-'+section+'-token-err').addClass("hidden");
        return true;
    }
    else{
        $('#wptelegram-'+section+'-test').addClass("hidden");
        $('#wptelegram-'+section+'-token-err').removeClass("hidden");
        $('#wptelegram-'+section+'-chat-list').text('');
        $('#wptelegram-'+section+'-mem-count').addClass("hidden");
        return false;
    }
}
function getMe(section) {
    var bot_token = $('input[name="wptelegram_'+section+'[bot_token]"]').val();
    if( '' != bot_token ) {
        if ( 'function' === typeof $('#checkbot').prop ){
            $('#checkbot').prop('disabled', true);
            $('#checkbot').text('<?php echo __("Please wait...", "wptelegram") ?> ');
        }
        sendAjaxRequest( bot_token, '/getMe', {}, 'fillBotInfo', section );
        if ( 'function' === typeof $('#checkbot').prop ){
            $('#checkbot').prop('disabled', false);
            $('#checkbot').text('<?php echo __("Test Token", "wptelegram") ?>');
        }
    }
    else {
        alert(' <?php  echo __("Bot Token is empty", "wptelegram") ?>') 
    }
}
function fillBotInfo( jqXHR,chat_id, section ) {
    $('#wptelegram-'+section+'-token-err').addClass("hidden");
    $('#wptelegram-'+section+'-test').removeClass("hidden");

    if ( undefined == jqXHR  || '' == jqXHR.responseText ) {
        $('#bot-info').text('');
        $('#bot-info').append('<span style="color:#f10e0e;"><?php  echo __("Error: Could not connect", "wptelegram"); ?></span>');
    }
    else if ( true == JSON.parse( jqXHR.responseText ).ok ){
        var result = JSON.parse( jqXHR.responseText ).result;
        
        $('#bot-info').text( result.first_name + ' ' + ( undefined == result.last_name ? ' ' :  result.last_name ) + '(@' + result.username + ')' );
    }
    else{
        $('#bot-info').text('error: '+ jqXHR.status + ' (' + jqXHR.statusText + ')');
    }
}
function wptelegramSearchSelect(){
  $("select").each(function (){
    if(! $(this).hasClass('no-fancy'))
      $(this).select2();
  });
}
(function ($) {
    'use strict';
    $(document).ready(function() {
        $('#thumb-url-button').click(function(e) {
            e.preventDefault();

            var uploader = wp.media({
                multiple: false,
                library: {
                        type: [ 'image' ]
                },
            })
            .on('select', function() {
                var attachment = uploader.state().get('selection').first().toJSON();
                $('#wptelegram-search-thumb').attr('src', attachment.url);
                $('#wptelegram-search-thumb').removeClass('hidden');
                $('input[name="wptelegram_search_msg[thumb_url]"]').val(attachment.url);

            })
            .open();
        });
        if ( 'function' == typeof $().emojioneArea ) {
            if (window.matchMedia('(max-width: 800px)').matches) {
                var pos = 'top';
            } else {
                var pos = 'left';
            }
            var e = $('#wptelegram_search_result_template').emojioneArea({
                container: "#wptelegram_search_result_template-container",
                hideSource: true,
                pickerPosition: pos,
                tonesStyle: 'radio',
                });
        }
        $('.wptelegram-tag').click(function () {
            if ( 'function' == typeof $().emojioneArea )
                $('.emojionearea-editor')[0].focus();
                var val = this.innerText;
                pasteHtmlAtCaret(val,true);
        });
        wptelegramSearchSelect();
        if (! $('input[name="wptelegram_search_tg[bot_token]"]').length ) {
            return;
        }
        var from_terms = $('select[name="wptelegram_search_wp[from_terms][]"]');
        var terms = $('select[name="wptelegram_search_wp[terms][]"]');
        var from_authors = $('select[name="wptelegram_search_wp[from_authors][]"]');
        var authors = $('select[name="wptelegram_search_wp[authors][]"]');

        $(from_terms).change(function() {
            if ( 'all' == from_terms.find("option:selected").val() ) {
                terms.closest( 'tr' ).hide(300);
            }
            else{
                terms.closest( 'tr' ).show(300);
            }
        });

        $(from_authors).change(function() {
            if ( 'all' == from_authors.find("option:selected").val() ) {
                authors.closest( 'tr' ).hide(300);
            }
            else{
                authors.closest( 'tr' ).show(300);
            }
        });
    });
})(jQuery)
</script>