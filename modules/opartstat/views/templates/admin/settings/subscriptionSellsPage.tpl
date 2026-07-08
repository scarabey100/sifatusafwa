<div class="panel" id="fieldset_0">
    {$sellsPageHtml nofilter}{* can't escape contain HTML *}
    <div style="text-align:center; margin-top:10px">
        <a id="goToSubscriptionSettingsBtn" class="btn btn-primary pointer" href="{$subscriptionSettingsLink|escape:'htmlall':'UTF-8'}">
        <i class="material-icons mi-arrow_forward"><span>arrow_forward</span></i>        
            {l s='Continue' mod='opartstat'}
        </a>
    </div>
{*     <div>
        <strong>Op'art Stat Premium is still in beta, please contact us if you want to participate !</strong>
    </div> *}
    <div class="privacyPolicyLinkContainer">
        <a href="https://www.opart-stat.com/controllers/front/privacyPolicy.php" target="_blank">{l s='Read our privacy policy here' mod='opartstat'}</a>
    </div>
</div>

