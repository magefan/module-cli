# Magento 2 CLI Extension by Magefan

[![Total Downloads](https://poser.pugx.org/magefan/module-cli/downloads)](https://packagist.org/packages/magefan/module-cli)
[![Latest Stable Version](https://poser.pugx.org/magefan/module-cli/v/stable)](https://packagist.org/packages/magefan/module-cli)

This Magento 2 module allows you to run CLI commands from admin panel (System > Tools > Command Line) using exec php function. You can restrict access to this interface using Magento 2 Access Control List. We recommend to use it ONLY on dev environments as it is not securely to run exec.

<a href="https://magefan.com/magento2-cli-extension"><img width="300" height="60" src="https://cm.magefan.com/mf_webp/png/media/wysiwyg/DOWNLOAD_NOW.webp"></a>

![alt text](https://magefan.com/media/wysiwyg/magento2-cli.png)

## Requirements
  * Magento Community Edition 2.1.x-2.4.x or Magento Enterprise Edition 2.1.x-2.4.x
  * Exec function needs to be enabled in PHP settings.

## Installation Method 1 - Installing via composer
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run command: composer require magefan/module-cli

## Installation Method 2 - Installing using archive
  * Download [ZIP Archive](https://magefan.com/magento2-cli-extension)
  * Extract files
  * In your Magento 2 root directory create folder app/code/Magefan/Cli
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues, please [contact us](mailto:support@magefan.com)
then if you still need help, open a bug report in GitHub's
[issue tracker](https://github.com/magefan/module-cli/issues).

Please do not use Magento Marketplace Reviews or (especially) the Q&A for support.
There isn't a way for us to reply to reviews and the Q&A moderation is very slow.

## Need More Features?
Please contact us to get a quote
https://magefan.com/contact

## License
The code is licensed under [EULA](https://magefan.com/end-user-license-agreement).

## Other [Magento 2 Extensions](https://magefan.com/magento2-extensions) by Magefan
  * [Magento 2 Blog Extension](https://magefan.com/magento2-blog-extension)
  * [Magento 2 Blog Plus Extension](https://magefan.com/magento2-blog-extension/pricing)
  * [Magento 2 Blog Extra Extension](https://magefan.com/magento2-blog-extension/pricing)
  * [Magento 2 Multi Blog Extension](https://magefan.com/magento-2-multi-blog-extension)
  * [Magento 2 Login As Customer Extension](https://magefan.com/login-as-customer-magento-2-extension)
  * [Magento 2 Convert Guest to Customer Extension](https://magefan.com/magento2-convert-guest-to-customer)
  * [Magento 2 Facebook Open Graph Extension](https://magefan.com/magento-2-open-graph-extension-og-tags)
  * [Magento 2 Auto Currency Switcher Extension](https://magefan.com/magento-2-currency-switcher-auto-currency-by-country)
  * [Magento 2 Auto Language Switcher Extension](https://magefan.com/magento-2-auto-language-switcher)
  * [Magento 2 GeoIP Switcher Extension](https://magefan.com/magento-2-geoip-switcher-extension)
  * [Magento 2 YouTube Widget Extension](https://magefan.com/magento2-youtube-extension)
  * [Magento 2 Product Grid Inline Editor](https://magefan.com/magento-2-product-grid-inline-editor)
  * [Magento 2 Product Widget Advanced Extension](https://magefan.com/magento-2-product-widget)
  * [Magento 2 Conflict Detector Extension](https://magefan.com/magento2-conflict-detector)
  * [Magento 2 Lazy Load Extension](https://magefan.com/magento-2-image-lazy-load-extension)
  * [Magento 2 Rocket JavaScript Extension](https://magefan.com/rocket-javascript-deferred-javascript)
  * [Magento Twitter Cards Extension](https://magefan.com/magento-2-twitter-cards-extension)
  * [Magento 2 Mautic Integration Extension](https://magefan.com/magento-2-mautic-extension)
  * [Magento 2 Alternate Hreflang Extension](https://magefan.com/magento2-alternate-hreflang-extension)
  * [Magento 2 Dynamic Categories](https://magefan.com/magento-2-dynamic-categories)
  * [Magento 2 CMS Display Rules Extension](https://magefan.com/magento-2-cms-display-rules-extension)
  * [Magento 2 Translation Extension](https://magefan.com/magento-2-translation-extension)
  * [Magento 2 WebP Optimized Images Extension](https://magefan.com/magento-2-webp-optimized-images)
  * [Magento 2 Image Optimization Extension](https://magefan.com/magento-2-image-optimization)
  * [Magento 2 Zendesk Chat Extension](https://magefan.com/magento-2-zendesk-chat-extension)
  * [Magento 2 Admin View Extension](https://magefan.com/magento-2-admin-view-extension)
  * [Magento 2 Zero Downtime Deployment](https://magefan.com/blog/magento-2-zero-downtime-deployment)
  * [Magento 2 Better Order Grid Extension](https://magefan.com/magento-2-better-order-grid-extension)
  * [Magento 2 Admin Email Notifications](https://magefan.com/magento-2-admin-email-notifications)
  * [Magento 2 Facebook Pixel Extension](https://magefan.com/magento-2-facebook-pixel-extension)
  * [Better Magento 2 Checkout Extension](https://magefan.com/better-magento-2-checkout-extension)
