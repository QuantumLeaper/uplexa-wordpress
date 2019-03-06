# uPlexa Gateway for WooCommerce

## Features

* Payment validation done through either `uplexa-wallet-rpc` or the [explorer.uplexa.com blockchain explorer](https://explorer.uplexa.com/).
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of uPlexa.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* uPlexa wallet to receive payments - [GUI](https://github.com/uplexa/uplexa-gui/releases) - [CLI](https://github.com/uplexa/uplexa-gui/releases) - [Web](https://wallet.uplexa.com)
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/uplexa/uplexa-wordpress) or clone with `git clone https://github.com/uplexa/uplexa-wordpress`
* Unzip or place the `uplexa-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "uPlexa Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

## Option 1: Use your wallet address and viewkey

This is the easiest way to start accepting uPlexa on your website. You'll need:

* Your uPlexa wallet address starting with `4`
* Your wallet's secret viewkey

Then simply select the `viewkey` option in the settings page and paste your address and viewkey. You're all set!

Note on privacy: when you validate transactions with your private viewkey, your viewkey is sent to (but not stored on) explorer.uplexa.com over HTTPS. This could potentially allow an attacker to see your incoming, but not outgoing, transactions if they were to get his hands on your viewkey. Even if this were to happen, your funds would still be safe and it would be impossible for somebody to steal your money. For maximum privacy use your own `uplexa-wallet-rpc` instance.

## Option 2: Using `uplexa-wallet-rpc`

The most secure way to accept uPlexa on your website. You'll need:

* Root access to your webserver
* Latest [uPlexa-currency binaries](https://github.com/uplexa-project/uplexa/releases)

After downloading (or compiling) the uPlexa binaries on your server, install the [systemd unit files](https://github.com/uplexa/uplexa-wordpress/tree/master/assets/systemd-unit-files) or run `uplexad` and `uplexa-wallet-rpc` with `screen` or `tmux`. You can skip running `uplexad` by using a remote node with `uplexa-wallet-rpc` by adding `--daemon-address node.uplexaworld.com:18089` to the `uplexa-wallet-rpc.service` file.

Note on security: using this option, while the most secure, requires you to run the uPlexa wallet RPC program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds.

## Configuration

* `Enable / Disable` - Turn on or off uPlexa gateway. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: uPlexa Gateway)
* `Discount for using uPlexa` - Percentage discount applied to orders for paying with uPlexa. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `Confirmation Type` - Confirm transactions with either your viewkey, or by using `uplexa-wallet-rpc`. (Default: viewkey)
* `uPlexa Address` (if confirmation type is viewkey) - Your public uPlexa address starting with 4. (No default)
* `Secret Viewkey` (if confirmation type is viewkey) - Your *private* viewkey (No default)
* `uPlexa wallet RPC Host/IP` (if confirmation type is `uplexa-wallet-rpc`) - IP address where the wallet rpc is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `uPlexa wallet RPC port` (if confirmation type is `uplexa-wallet-rpc`) - Port the wallet rpc is bound to with the `--rpc-bind-port` argument. (Default 21060)
* `Testnet` - Check this to change the blockchain explorer links to the testnet explorer. (Default: unchecked)
* `SSL warnings` - Check this to silence SSL warnings. (Default: unchecked)
* `Show QR Code` - Show payment QR codes. (Default: unchecked)
* `Show Prices in uPlexa` - Convert all prices on the frontend to uPlexa. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in uPlexa is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded and will be displayed down to the nanouPlexa. (Default: 2)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of uPlexa in the selected currency. If no currency is provided, the store's default currency will be used.

```
[uplexa-price]
[uplexa-price currency="BTC"]
[uplexa-price currency="USD"]
[uplexa-price currency="CAD"]
[uplexa-price currency="EUR"]
[uplexa-price currency="GBP"]
```
Will display:
```
1 UPX = 123.68000 USD
1 UPX = 0.01827000 BTC
1 UPX = 123.68000 USD
1 UPX = 168.43000 CAD
1 UPX = 105.54000 EUR
1 UPX = 94.84000 GBP
```
