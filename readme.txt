=== iPay for WooCommerce ===
Contributors: ipayglobal
Donate link: https://ipay.lk
Tags: ipay, woocommerce, payments, paymentgateways, cardpayments
Requires at least: 4.9
Tested up to: 6.5
Stable tag: 1.2.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate your iPay merchant account with your e-commerce store to easily accept payments via iPay. 

== Description ==

iPay is a Srilanka based global payment solution which currently operates in Srilanka and Cambodia, which enables customers to do their day to day financial transactions
using connected bank accounts or cards.

= How it Works =

After a successful integration the plugin will provide the merchant website with the feature
to accept payments from iPay users or from users using a LankaQR compliant apps. 

1. If the customers opts to make the payment using iPay, then the user will be required to enter his/ her iPay Mobile
Number and Email for validation purposes. Upon successful validation the payment request for the
online order will be sent to the users iPay app for transaction authentication. 

2. If the user opts to pay via LankaQR, a LankaQR QR code will be generated upon Cart Checkout which will allow the user to
make the payment by scanning the QR with any LankaQR compliant app in the market. 

== Installation ==

= Minimum Requirements =

* Wordpress 4.9
* WooCommerce 7.0 or higher recommended.

= Manual Installation = 

1. Go to the Wordpress 'Dashboard' -> 'Plugins' -> 'Upload Plugin'.
2. Upload the plugin .zip file, and click 'Install Now'.
3. Go to 'Plugins' in Dashboard's side menu -> Look in to 'iPay for WooCommerce' and click on 'Activate'.
4. To setup iPay admin settings, go to 'WooCommerce' in side menu -> Go to 'Settings' -> Click on 'Payments' tab.

* Also it is possible to extract the zip file into the wp-content/plugins directory in your wordpress installation and continue from the 3rd step above.

== Frequently Asked Questions ==

= Are there any iPay related configurations that should be done? =

Yes, The merchant should register and login as an iPay merchant from the official iPay site (https://www.ipay.lk/ipayMerchantApp/login), and should enable the 'Development Settings'.
After enabling, the 'Developer Portal' can be accessed, where in the 'Payment Integration' section you can generate a web token for web payments.
This generated merchant web token has to be used in the plugin.
Required 'Secret' and the 'Callback API Url' can be found in the plugin's settings page.
For more information and sandbox integrations official documentation can be found at (https://ipay.lk/integrate-with-us)

== Changelog ==

= 1.0.0 =
* Initial release.

= 1.1.0 =
* IPG integration added.

= 1.2.0 =
* Woocommerce HPOS compatibility assured.
* HPOS compatibility declation added.
* Allowed changes to the redirect url.
* Minor fixes.

== Upgrade Notice ==

= 1.0.0 =
This is the initial release of the plugin.

= 1.1.0 =
Please upgrade to the latest version of iPay to enable IPG payments, since the old web payment method will be depricated soon.

= 1.2.0 =
Please upgrade to the latest version.

== Screenshots ==

1. iPay Admin Settings.
2. Payment Integration in iPay Merchant Web Portal.