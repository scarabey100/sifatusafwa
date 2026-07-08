<div class="opartStatHeaderSettings">
    <a class="btn {if $currentPage=='global'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsGlobalLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-settings"><span>settings</span></i>
        <span class="label">{l s='Global Settings' mod='opartstat'}</span>
    </a>
    <a class="btn {if $currentPage=='robots'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsRobotsLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-android"><span>android</span></i>
        <span class="label">{l s='Robots' mod='opartstat'}</span>
    </a>
    <a class="btn {if $currentPage=='ips'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsIpsLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-https"><span>https</span></i>
        <span class="label">{l s='IP Blocking' mod='opartstat'}</span>
    </a>
    <a class="btn {if $currentPage=='modules'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsModulesLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-extension"><span>extension</span></i>
        <span class="label">{l s='Partner modules' mod='opartstat'}</span>
    </a>
    <a class="btn {if $currentPage=='linksCreator'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsTrackableLinksCreatorLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-link"><span>link</span></i>
        <span class="label">{l s='Trackable links creator' mod='opartstat'}</span>
    </a>
    <a class="btn {if $currentPage=='commissions'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsCommissionsLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-attach_money"><span>attach_money</span></i>
        <span class="label">{l s='Fees & commissions' mod='opartstat'}</span>
    </a>
    <a class="btn {if $currentPage=='advanced'}btn-primary{else}btn-secondary{/if} pointer" href="{$settingsAdvancedLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-attach_money"><span>settings_applications</span></i>
        <span class="label">{l s='Advanced settings' mod='opartstat'}</span>
    </a>
   <!-- <a class="btn {if $currentPage=='subscription'}btn-primary{else}btn-secondary{/if} pointer opartStatPremiumSettingBtn" href="{$settingsSubscriptionLink|escape:'html':'UTF-8'}">
        <i class="material-icons mi-attach_money"><span>star</span></i>
        <span class="label">{l s='Op\'art Stat Premium' mod='opartstat'}</span>
    </a>-->
</div>

