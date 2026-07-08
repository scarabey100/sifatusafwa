{**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 *}
<div class="form-horizontal col-lg-10">
    {foreach $errors as $error}
        <div class="alert alert-danger">
            {$error|escape:'htmlall':'UTF-8'}
        </div>
    {/foreach}
    {foreach $success as $succes}
        <div class="alert alert-success">
            {$succes|escape:'htmlall':'UTF-8'}
        </div>
    {/foreach}
    {$content nofilter}
</div>
<div class="clearfix"></div>
