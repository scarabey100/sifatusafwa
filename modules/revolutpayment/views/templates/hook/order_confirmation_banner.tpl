{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    Revolut
* @copyright Since 2020 Revolut
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*}



<div id="OrderConfirmationBanner" 

data-banner-public-token={$merchant_public_key|escape:'htmlall':'UTF-8'}
data-banner-currency={$currency|escape:'htmlall':'UTF-8'}
data-banner-locale={$locale|escape:'htmlall':'UTF-8'}
data-banner-type="{$banner_type|escape:'htmlall':'UTF-8'}" 
data-banner-transaction-id="{$transaction_id|escape:'htmlall':'UTF-8'}" 
data-banner-order-token={$revolut_order_public_id|escape:'htmlall':'UTF-8'} 
data-banner-customer-email="{$customer_email|escape:'htmlall':'UTF-8'}" 
data-banner-customer-phone="{$customer_phone|escape:'htmlall':'UTF-8'}">

</div>
