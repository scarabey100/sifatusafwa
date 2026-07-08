<div class="opartStatCardsContainer">
    {foreach from=$partnerModules item=pm}
        <div class="opartStatCard">
            <div class="cardHeader">
                <h3>
                    <img src="{$pm['logoUrl']|escape:'html':'UTF-8'}" alt="" width="50px" height="50px" />
                    {$pm['title']|escape:'html':'UTF-8'}
                    {if $pm['alreadyLinked']}<span>{l s='[Linked]' mod='opartstat'}</span>{/if}
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-0 js-customer-email">{$pm['description']|escape:'html':'UTF-8'}</p>
            </div>
            <div class="cardFooter">
                <a class="moduleLink" href="{$pm['link']|escape:'html':'UTF-8'}" target="blank" rel="noreferrer">
                    {l s='Discover this module' mod='opartstat'}
                </a>
                <a href="{if $pm['isAvailable']==true}{$settingsModulesLink|escape:'html':'UTF-8'}&linkModule={$pm['name']|escape:'html':'UTF-8'}{/if}" type="button"
                    class="btn btn-primary {if $pm['isAvailable']==false}disabled{/if}">
                    <i class="material-icons mi-assessment"><span>link</span></i>
                    <span class="label">{l s='Connect to OpartStat' mod='opartstat'}</span>
                </a>
                {if $pm['isAvailable']==false}
                    <span class="help-box" data-html="true" data-placement="left"
                        title="{l s='You can\'t link this module because : it is not installed or it is currently disabled or it is already linked' mod='opartstat'}"></span>
                {/if}
            </div>
        </div>
    {/foreach}
</div>

<script type="text/javascript">
    $('.help-box').tooltip({ placement: $(this).data('placement') })
</script>