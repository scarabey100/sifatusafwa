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
<script type="text/javascript">
    {literal}
        var psv = {/literal}{$psv|floatval}{literal};
        var id_lang = {/literal}{$id_lang|intval}{literal};
        var faq_secure_key = '{/literal}{$faq_secure_key|escape:'html':'UTF-8'}{literal}';
        var faq_admin_controller_dir = '{/literal}{$faq_admin_controller_dir nofilter}{literal}';
        var address_token = '{/literal}{getAdminToken tab='AdminAddresses'}{literal}';
        var ajaxErrorMessage = "{/literal}{l s='Something went wrong, please refresh the page and try again.' mod='hifaq'}{literal}";
        var select2Placeholder = "{/literal}{l s='-- Choose --' mod='hifaq'}{literal}";
    {/literal}
</script>