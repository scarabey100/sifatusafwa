<div class="demTopBar {if $demTimeLeft > 5 }demTopBarBigDontStress{else if $demTimeLeft > 0 }demTopBarUrgency{else}demTopBarBigUrgency{/if}" >
{if $demTimeLeft > 5 }
    <strong>😎 {l s='BE COOL :' mod='opartstat'}</strong>
    {l s='You are using a demo of the OparStat module and you still have %d days to test it.' mod='opartstat' sprintf=$demTimeLeft}
    {l s='If you already know you will use it, you can purchase it by clicking on this button :' mod='opartstat'} 
{else if $demTimeLeft > 0 }
    <strong>🧐 {l s='IMPORTANT :' mod='opartstat'}</strong>
    {l s='Your trial period of OpartStat will finish in %d days.' mod='opartstat' sprintf=$demTimeLeft}
{else}
    <strong>😍 {l s='TIME TO MAKE A CHOICE :' mod='opartstat'}</strong>
    {l s='This the LAST DAY of your OpartStat trial period. If you like it, purchase it :' mod='opartstat'}    
{/if}
    <a href="https://www.store-opart.fr/p/50-module-de-statistiques-pour-prestashop.html?opaffi={$opartAfId|escape:'html':'UTF-8'}&utm_source=opartstatdemo&utm_medium=demomodule&utm_campaign=opartstat14daystrial" class="btn btn-primary" target="blank">{l s='Purchase OpartStat.' mod='opartstat'}</a>
</div>