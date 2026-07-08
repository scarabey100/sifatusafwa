{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
{* Global revenues *}
<div class="panel panel-default osStatPanel osSingleNumberPanel" style="grid-column-end:span {$cols|escape:'html':'UTF-8'}; grid-row-end:span {$rows|escape:'html':'UTF-8'};">
  
    {* <span class="help-box"  data-html="true" data-placement="left" title="{foreach from=$help item=helpText}{$helpText|escape:'html':'UTF-8'}{/foreach} " ></span> *}
    <span class="help-box"  data-html="true" data-placement="left" title="{$help nofilter}{* can't escape contain HTML *}" ></span>
    <span class="material-icons reloadBtn addIsLoadingClass" data-metric-position="{$position|escape:'htmlall':'UTF-8'}">
        <i class="material-icons">
            <span>refresh</span>
        </i>
    </span>
        
    <div class="panel-body">
        <h2>{$metricitle|escape:'html':'UTF-8'}</h2>
        {include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/loader.tpl" divId=$metricName|cat:'Loader'}
        <div id="{$metricName|escape:'html':'UTF-8'}Container" class="osSingleNumberStat osSingleNumberInitial">0</div>
        <div id="{$metricName|escape:'html':'UTF-8'}CompareGlobalValue" class="osSingleNumberStat osSingleNumberStatCompare compareElement">0</div>
        <div id="{$metricName|escape:'html':'UTF-8'}GlobalPercentVariationContainer" class="osSingleNumberStat osSingleNumberPercent compareElement">0</div>
    </div>
</div>