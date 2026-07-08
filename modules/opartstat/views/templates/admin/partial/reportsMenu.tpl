{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
<div id="reportsMenuContainer" class="panel panel-default">
    <div class="panel-heading">
        <h2>{l s='Choose a report' mod='opartstat'}</h2>
        <a href="{$adminControllerLink|escape:'html':'UTF-8'}&addNewReport=1" id="addNewReport" class="btn btn-primary pointer opartStatTopBtn addIsLoadingClass">
            <i class="material-icons mi-help_outline"><span>library_add</span></i>
            <span class="label">NEW REPORT</span>
        </a>
    </div>
    <div class="reportsMenuGrid">
    {foreach from=$reportList item=report}
        <a id="linkToReport_{$report['name']|escape:'html':'UTF-8'}" href="{$adminControllerLink|escape:'html':'UTF-8'}&reportName={$report['name']|escape:'html':'UTF-8'}">{$report['displayTitle']|escape:'html':'UTF-8'}</a>
    {/foreach}
    </div>
</div>