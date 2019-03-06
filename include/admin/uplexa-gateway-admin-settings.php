<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled' => array(
        'title' => __('Enable / Disable', 'uplexa_gateway'),
        'label' => __('Enable this payment gateway', 'uplexa_gateway'),
        'type' => 'checkbox',
        'default' => 'no'
    ),
    'title' => array(
        'title' => __('Title', 'uplexa_gateway'),
        'type' => 'text',
        'desc_tip' => __('Payment title the customer will see during the checkout process.', 'uplexa_gateway'),
        'default' => __('uPlexa Gateway', 'uplexa_gateway')
    ),
    'description' => array(
        'title' => __('Description', 'uplexa_gateway'),
        'type' => 'textarea',
        'desc_tip' => __('Payment description the customer will see during the checkout process.', 'uplexa_gateway'),
        'default' => __('Pay securely using uPlexa. You will be provided payment details after checkout.', 'uplexa_gateway')
    ),
    'discount' => array(
        'title' => __('Discount for using uPlexa', 'uplexa_gateway'),
        'desc_tip' => __('Provide a discount to your customers for making a private payment with uPlexa', 'uplexa_gateway'),
        'description' => __('Enter a percentage discount (i.e. 5 for 5%) or leave this empty if you do not wish to provide a discount', 'uplexa_gateway'),
        'type' => __('number'),
        'default' => '0'
    ),
    'valid_time' => array(
        'title' => __('Order valid time', 'uplexa_gateway'),
        'desc_tip' => __('Amount of time order is valid before expiring', 'uplexa_gateway'),
        'description' => __('Enter the number of seconds that the funds must be received in after order is placed. 3600 seconds = 1 hour', 'uplexa_gateway'),
        'type' => __('number'),
        'default' => '3600'
    ),
    'confirms' => array(
        'title' => __('Number of confirmations', 'uplexa_gateway'),
        'desc_tip' => __('Number of confirms a transaction must have to be valid', 'uplexa_gateway'),
        'description' => __('Enter the number of confirms that transactions must have. Enter 0 to zero-confim. Each confirm will take approximately two minutes', 'uplexa_gateway'),
        'type' => __('number'),
        'default' => '5'
    ),
    'confirm_type' => array(
        'title' => __('Confirmation Type', 'uplexa_gateway'),
        'desc_tip' => __('Select the method for confirming transactions', 'uplexa_gateway'),
        'description' => __('Select the method for confirming transactions', 'uplexa_gateway'),
        'type' => 'select',
        'options' => array(
            'viewkey'        => __('viewkey', 'uplexa_gateway'),
            'uplexa-wallet-rpc' => __('uplexa-wallet-rpc', 'uplexa_gateway')
        ),
        'default' => 'viewkey'
    ),
    'uplexa_address' => array(
        'title' => __('uPlexa Address', 'uplexa_gateway'),
        'label' => __('Useful for people that have not a daemon online'),
        'type' => 'text',
        'desc_tip' => __('uPlexa Wallet Address (uPlexaL)', 'uplexa_gateway')
    ),
    'viewkey' => array(
        'title' => __('Secret Viewkey', 'uplexa_gateway'),
        'label' => __('Secret Viewkey'),
        'type' => 'text',
        'desc_tip' => __('Your secret Viewkey', 'uplexa_gateway')
    ),
    'daemon_host' => array(
        'title' => __('uPlexa wallet RPC Host/IP', 'uplexa_gateway'),
        'type' => 'text',
        'desc_tip' => __('This is the Daemon Host/IP to authorize the payment with', 'uplexa_gateway'),
        'default' => '127.0.0.1',
    ),
    'daemon_port' => array(
        'title' => __('uPlexa wallet RPC port', 'uplexa_gateway'),
        'type' => __('number'),
        'desc_tip' => __('This is the Wallet RPC port to authorize the payment with', 'uplexa_gateway'),
        'default' => '21060',
    ),
    'testnet' => array(
        'title' => __(' Testnet', 'uplexa_gateway'),
        'label' => __(' Check this if you are using testnet ', 'uplexa_gateway'),
        'type' => 'checkbox',
        'description' => __('Advanced usage only', 'uplexa_gateway'),
        'default' => 'no'
    ),
    'onion_service' => array(
        'title' => __(' SSL warnings ', 'uplexa_gateway'),
        'label' => __(' Check to Silence SSL warnings', 'uplexa_gateway'),
        'type' => 'checkbox',
        'description' => __('Check this box if you are running on an Onion Service (Suppress SSL errors)', 'uplexa_gateway'),
        'default' => 'no'
    ),
    'show_qr' => array(
        'title' => __('Show QR Code', 'uplexa_gateway'),
        'label' => __('Show QR Code', 'uplexa_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to show a QR code after checkout with payment details.'),
        'default' => 'no'
    ),
    'use_uplexa_price' => array(
        'title' => __('Show Prices in uPlexa', 'uplexa_gateway'),
        'label' => __('Show Prices in uPlexa', 'uplexa_gateway'),
        'type' => 'checkbox',
        'description' => __('Enable this to convert ALL prices on the frontend to uPlexa (experimental)'),
        'default' => 'no'
    ),
    'use_uplexa_price_decimals' => array(
        'title' => __('Display Decimals', 'uplexa_gateway'),
        'type' => __('number'),
        'description' => __('Number of decimal places to display on frontend. Upon checkout exact price will be displayed.'),
        'default' => 2,
    ),
);
