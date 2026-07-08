{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
<div class="panel panel-default osStatPanel osTrendPanel" style="grid-column-end:span {$cols|escape:'html':'UTF-8'}; grid-row-end:span {$rows|escape:'html':'UTF-8'};">
    {* <span class="help-box"  data-html="true" data-placement="left" title="{foreach from=$help item=helpText}{$helpText|escape:'html':'UTF-8'}{/foreach} " ></span> *}
    <span class="help-box"  data-html="true" data-placement="left" title="{$help nofilter}{* can't escape contain HTML *}"></span>
    <span class="material-icons reloadBtn addIsLoadingClass" data-metric-position="{$position}">
        <i class="material-icons">
            <span>refresh</span>
        </i>
    </span>
        
    <div class="panel-body">
        <div class="osPanelHeader">
            <h2>{$metricitle|escape:'html':'UTF-8'}</h2>            
        </div>
        {include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/loader.tpl" divId=$metricName|cat:'Loader'|escape:'html':'UTF-8'}
        <div class="osSelectPeriodContainer" >
            <a href="#" class="saveSettingsBtn savePeriodBtn addIsLoadingClass" id="{$metricName|escape:'html':'UTF-8'}SavePeriodBtn" data-metric-name="{$metricName|escape:'html':'UTF-8'}">
                <i class="material-icons mi-save tooltip1" data-html="true" data-placement="bottom" title="{l s='Click here to save your selected period for this chart' mod='opartstat'}"><span>save</span></i>
            </a>
            <select id="{$metricName|escape:'html':'UTF-8'}SelectPeriod" class="osSelectPeriod" data-metric-name="{$metricName|escape:'html':'UTF-8'}">
                <option value="perDay" {if $selectedPeriod=="perDay"}selected{/if}>{l s='Days' mod='opartstat'}</option>
                <option value="perWeek" {if $selectedPeriod=="perWeek"}selected{/if}>{l s='Weeks' mod='opartstat'}</option>
                <option value="perMonth" {if $selectedPeriod=="perMonth"}selected{/if}>{l s='Months' mod='opartstat'}</option>
                <option value="perYear" {if $selectedPeriod=="perYear"}selected{/if}>{l s='Years' mod='opartstat'}</option>
            </select>
        </div>
        <div id="{$metricName|escape:'html':'UTF-8'}Container" class="osSingleNumberStat"></div>
         
        <span id="{$metricName|escape:'html':'UTF-8'}GlobalValue" class="osGlobalValue"></span> 
        <span id="{$metricName|escape:'html':'UTF-8'}CompareGlobalValue" class="osGlobalValue osCompareGlobalValue compareElement"></span>
        <span id="{$metricName|escape:'html':'UTF-8'}GlobalPercentVariationContainer" class="compareElement"></span>
    </div>
    <input type="hidden" id="{$metricName|escape:'html':'UTF-8'}ContainerData" value=""/>
</div>