<?php foreach($errors as $error): ?>
<div class="error"><p><strong>uPlexa Gateway Error</strong>: <?php echo $error; ?></p></div>
<?php endforeach; ?>

<h1>uPlexa Gateway Settings</h1>

<?php if($confirm_type === 'uplexa-wallet-rpc'): ?>
<div style="border:1px solid #ddd;padding:5px 10px;">
    <?php
         echo 'Wallet height: ' . $balance['height'] . '</br>';
         echo 'Your balance is: ' . $balance['balance'] . '</br>';
         echo 'Unlocked balance: ' . $balance['unlocked_balance'] . '</br>';
         ?>
</div>
<?php endif; ?>

<table class="form-table">
    <?php echo $settings_html ?>
</table>

<h4><a href="https://github.com/uplexa/uplexa-wordpress">Learn more about using the uPlexa payment gateway</a></h4>

<script>
function uplexaUpdateFields() {
    var confirmType = jQuery("#woocommerce_uplexa_gateway_confirm_type").val();
    if(confirmType == "uplexa-wallet-rpc") {
        jQuery("#woocommerce_uplexa_gateway_uplexa_address").closest("tr").hide();
        jQuery("#woocommerce_uplexa_gateway_viewkey").closest("tr").hide();
        jQuery("#woocommerce_uplexa_gateway_daemon_host").closest("tr").show();
        jQuery("#woocommerce_uplexa_gateway_daemon_port").closest("tr").show();
    } else {
        jQuery("#woocommerce_uplexa_gateway_uplexa_address").closest("tr").show();
        jQuery("#woocommerce_uplexa_gateway_viewkey").closest("tr").show();
        jQuery("#woocommerce_uplexa_gateway_daemon_host").closest("tr").hide();
        jQuery("#woocommerce_uplexa_gateway_daemon_port").closest("tr").hide();
    }
    var useuPlexaPrices = jQuery("#woocommerce_uplexa_gateway_use_uplexa_price").is(":checked");
    if(useuPlexaPrices) {
        jQuery("#woocommerce_uplexa_gateway_use_uplexa_price_decimals").closest("tr").show();
    } else {
        jQuery("#woocommerce_uplexa_gateway_use_uplexa_price_decimals").closest("tr").hide();
    }
}
uplexaUpdateFields();
jQuery("#woocommerce_uplexa_gateway_confirm_type").change(uplexaUpdateFields);
jQuery("#woocommerce_uplexa_gateway_use_uplexa_price").change(uplexaUpdateFields);
</script>

<style>
#woocommerce_uplexa_gateway_uplexa_address,
#woocommerce_uplexa_gateway_viewkey {
    width: 100%;
}
</style>