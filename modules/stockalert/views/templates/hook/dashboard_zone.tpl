{**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2025 idnovate.com
*  @license   See above
*}

<div class="col-lg-12 idnovate-modules-ad">
    <div class="panel clearfix">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Recommended modules' mod='stockalert'}
        </div>

        {*foreach from=$modules key=k item=module*}
            <div class="module-item">
                <div class="module-item-heading">
                    <div class="module-logo-thumb">
                        <img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/company/{$modules['id']|escape:'htmlall':'UTF-8'}.png" alt="{$modules['name']|escape:'htmlall':'UTF-8'}">
                    </div>
                    <h3 title="{$modules['name']|escape:'htmlall':'UTF-8'}" class="text-ellipsis module-name">
                        <span>{$modules['name']|escape:'htmlall':'UTF-8'}</span>
                    </h3>
                </div>
                <div class="module-item-description">
                    {$modules['description']|escape:'htmlall':'UTF-8'}
                </div>
                <div class="module-item-badge">
                    <img src="{$this_path|escape:'htmlall':'UTF-8'}views/img/company/round_logo.png">
                    <span>{l s='Made by idnovate.com' mod='stockalert'}</span>
                </div>
                <div class="module-item-buton">
                    <a href="https://addons.prestashop.com/en/{$modules['id']|escape:'htmlall':'UTF-8'}-.html" title="{$modules['name']|escape:'htmlall':'UTF-8'}" target="_blank" class="btn btn-primary">
                        <i class="icon-thumbs-o-up"></i>
                        {l s='Learn more' mod='stockalert'}
                    </a>
                </div>
            </div>
        {*/foreach*}

    </div>
</div>
<div class="clearfix"></div>

<style type="text/css">
    .bootstrap .idnovate-modules-ad .module-item-heading {
        position: relative;
        text-align: center;
    }
    .bootstrap .idnovate-modules-ad .module-logo-thumb {
        width: 55px;
        height: 55px;
        text-align: center;
        margin: 0 auto;
        border-radius: 0;
    }
    .bootstrap .idnovate-modules-ad .module-logo-thumb img {
        max-width: 55px;
        max-height: 55px;
    }
    #content.bootstrap .idnovate-modules-ad h3.module-name {
        border: none;
        font-size: 1.2em;
        padding: 0 0 0 5px;
        position: relative;
        text-align: center;
        font-weight: bolder;
        margin: 1rem 0 .5rem;
        min-height: 24px;
        font-family: Open Sans,Helvetica,Arial,sans-serif;
        line-height: 1.2;
        color: #363a41;
        text-transform: none;
        border-bottom: none;
        height: inherit;
    }
    .bootstrap .idnovate-modules-ad .module-item-description {
        margin: 15px 0;
        text-align: center;
    }
    .bootstrap .idnovate-modules-ad .module-read-more {
        display: inline-block;
    }
    .bootstrap .idnovate-modules-ad .module-price {
        right: auto;
        bottom: auto;
        position: relative;
        margin-right: 5px;
        float: right;
        font-size: 14px;
    }
    .bootstrap .idnovate-modules-ad hr {
        margin-top: 1.875rem;
        margin-bottom: 1.875rem;
        border: 0;
        border-top: 1px solid #bbcdd2;
    }
    .bootstrap .idnovate-modules-ad .module-item-buton {
        text-align: center;
    }

    .bootstrap .idnovate-modules-ad .module-item-badge {
        font-size: 14px;
        text-align: center;
        margin: 15px 0;
    }

    .bootstrap .idnovate-modules-ad .btn {
        width: 100%;
    }

    .bootstrap .idnovate-modules-ad .module-item-badge img {
        display: inline-block;
        padding-right: 5px;
    }
</style>
