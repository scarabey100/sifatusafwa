{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
<div id="addMetricsMenuContainer" class="panel panel-default">

    <div class="panel-heading">
        <h2>{l s='Choose the metrics to display on this report' mod='opartstat'}</h2>
    </div>
    <div class="reportsMenuHeader">
        <div class="panel panel-default searchMetricContainer">
            <h3>{l s='Search for your metric' mod='opartstat'}</h3>
            <input id="searchMetricField" type="text" value="" />
        </div>
        <div class="panel panel-default youlikethemoduleContainer">
            <h3>{l s='Do you like the module?' mod='opartstat'}</h3>
            <span><a href="https://prestashop.pxf.io/y21BPD" target="blank">{l s='Discover all our other modules on the PrestaShop marketplace by clicking here.' mod='opartstat'}</a></span>
        </div>
        <div class="panel panel-default doYouNeedCustomMetricContainer">
            <h3>{l s='Do you need a custom metric ?' mod='opartstat'}</h3>
            <span>{l s='Contact us, we will be happy to create the custom metrics you need !' mod='opartstat'}</span>
        </div>
    </div>
    <div class="reportsMenuGrid" id="allMetricContainer">
        {foreach from=$allMetrics item=categoryMetrics key=catId }
            {if $catId != 7}<h2>{$categoriesName[$catId]}</h2>
                {foreach from=$categoryMetrics item=metric}
                    <div class="metricsToAdd {if $metric['active']=="yes"}metricsToAddSelected{/if} {if $metric['active']=="disabled"}metricsToAddDisabled{/if} {if isset($metric['addCssClass'])}{$metric['addCssClass']|escape:'htmlall':'UTF-8'}{/if}"
                        id="{$metric['name']|escape:'html':'UTF-8'}">
                        <span class="help-box"  data-html="true" data-placement="bottom" title="{$metric['help']|escape:'htmlall':'UTF-8'}"></span>
                        <span class="metricName">{$metric['title']|escape:'html':'UTF-8'}</span>
                        <input class="osAddMetricCheckbox" type="checkbox" {if $metric['active']=="disabled"}disabled{/if}
                            {if $metric['active']=="yes"}checked{/if} />
                    </div>
                {/foreach}
            {/if}            
        {/foreach}
    </div>
    <div class="sideBarSaveBtnContainer">
        <a id="addMetricSaveBtn" class="btn btn-primary pointer spacedBtn addIsLoadingClass">
            <i class="material-icons mi-save"><span>save</span></i>
            &nbsp;{l s='Save' mod='opartstat'}
        </a>
    </div>
</div>