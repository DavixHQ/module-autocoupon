# Davix - Module AutoCoupon

    ``davix/module-autocoupon``

 - [Main Functionality](#main-functionality)
 - [Requirements](#requirements)
 - [Installation](#installation)
 - [Configuration](#configuration)
 - [Specifications](#specifications)
 - [FAQ](#faq)


## Main Functionality
This module lets you apply a coupon code to a customer's cart via a link, e.g. for email or
affiliate campaigns:

```
http://www.yoursite.com/applydiscount/?code=DISCOUNT-CODE&redirect_url=http://www.yoursite.com/
```

`redirect_url` is only ever honoured when it points back at this store's own host - an
external URL is ignored and the request falls back to the homepage instead.

For logged-in customers, the last coupon code they successfully applied is remembered and
automatically reapplied the next time their cart has no coupon on it (e.g. after logging back
in, or adding an item to a fresh cart). Applying a different coupon replaces what's remembered,
and clearing it - either via this module's own link with an empty `code` param, or the cart
page's own "Cancel Coupon" button - stops it being remembered too. This is per logged-in
customer only - guests aren't tracked.

## Requirements
- Magento Open Source / Adobe Commerce 2.4.x
- PHP 8.1, 8.2, or 8.3

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Davix`
 - Enable the module by running `php bin/magento module:enable Davix_AutoCoupon`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require davix/module-autocoupon`
 - enable the module by running `php bin/magento module:enable Davix_AutoCoupon`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

## Configuration

 - Status (ioauto_coupon/general/status)

 - Message (ioauto_coupon/general/message)

 - Error Message (ioauto_coupon/general/error_message)

The `status` and `message` settings are also exposed on the `StoreConfig` GraphQL type, as
`ioauto_coupon_general_status` and `ioauto_coupon_general_message`.

## Specifications

 - Observer
	- checkout_cart_product_add_after > Davix\AutoCoupon\Observer\Frontend\ApplyCoupon
	- customer_login > Davix\AutoCoupon\Observer\Frontend\ApplyCoupon

 - Plugin
	- Magento\Checkout\Controller\Cart\CouponPost > Davix\AutoCoupon\Plugin\ClearStickyCouponOnCancelPlugin

 - Controller
	- frontend > applydiscount/index/index


## FAQ

If you have any issues while using any of our modules please feel free to get in touch and we will be happy to help resolve them for you. 

http://www.davix.uk

