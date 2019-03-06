/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function uplexa_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#uplexa_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function uplexa_showQR(show=true) {
    jQuery('#uplexa_qr_code_container').toggle(show);
}
function uplexa_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': uplexa_details.order_id
    };
    jQuery.get(uplexa_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            uplexa_details = response;
            uplexa_updateDetails();
        }
    });
}

function uplexa_updateDetails() {

    var details = uplexa_details;

    jQuery('#uplexa_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.uplexa_payment_unpaid').show();
            jQuery('.uplexa_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.uplexa_payment_partial').show();
            jQuery('.uplexa_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.uplexa_payment_paid').show();
            jQuery('.uplexa_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.uplexa_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.uplexa_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.uplexa_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#uplexa_exchange_rate').html('1 UPX = '+details.rate_formatted+' '+details.currency);
    jQuery('#uplexa_total_amount').html(details.amount_total_formatted);
    jQuery('#uplexa_total_paid').html(details.amount_paid_formatted);
    jQuery('#uplexa_total_due').html(details.amount_due_formatted);

    jQuery('#uplexa_integrated_address').html(details.integrated_address);

    if(uplexa_show_qr) {
        var qr = jQuery('#uplexa_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#uplexa_tx_table').show();
        jQuery('#uplexa_tx_none').hide();
        jQuery('#uplexa_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+uplexa_explorer_url+'/tx/'+tx.txid+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' uPlexa</td>'+
                '</tr>';

            jQuery('#uplexa_tx_table tbody').append(row);
        }
    } else {
        jQuery('#uplexa_tx_table').hide();
        jQuery('#uplexa_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = uplexa_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                uplexa_showNotification('Transaction received for '+new_txs[i].amount_formatted+' uPlexa');
            }
        }
    }

    if(details.status != uplexa_order_state.status) {
        switch(details.status) {
            case 'paid':
                uplexa_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                uplexa_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                uplexa_showNotification('Your order has expired', 'error');
                break;
        }
    }

    uplexa_order_state = {
        status: uplexa_details.status,
        txs: uplexa_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof uplexa_details !== 'undefined') {
        uplexa_order_state = {
            status: uplexa_details.status,
            txs: uplexa_details.txs
        };
        setInterval(uplexa_fetchDetails, 30000);
        uplexa_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#uplexa_integrated_address':
                    uplexa_showNotification('Copied destination address!');
                    break;
                case '#uplexa_total_due':
                    uplexa_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});