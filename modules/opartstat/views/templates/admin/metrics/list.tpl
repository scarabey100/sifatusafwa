{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
<!-- START best list -->
<div class="panel panel-default osStatPanel osListPanel"
    style="grid-column-end:span {$cols|escape:'html':'UTF-8'}; grid-row-end:span {$rows|escape:'html':'UTF-8'};">
    <span class="material-icons osDisplayPieChartBtn" data-metric-name="{$metricName|escape:'html':'UTF-8'}"
        id="{$metricName|escape:'html':'UTF-8'}DisplayPieChartBtn">
        <span>pie_chart</span>
    </span>
    <span class="material-icons osDisplayListBtn osDisplayChartActiv"
        data-metric-name="{$metricName|escape:'html':'UTF-8'}" id="{$metricName|escape:'html':'UTF-8'}DisplayListBtn">
        <span>format_align_left</span>
    </span>

    {* <span class="help-box"  data-html="true" data-placement="left" title="{foreach from=$help item=helpText}{$helpText|escape:'html':'UTF-8'}{/foreach}" ></span> *}
    <span class="help-box" data-html="true" data-placement="left"
        title="{$help nofilter}{* can't escape contain HTML *}"></span>
    <span class="material-icons reloadBtn addIsLoadingClass" data-metric-position="{$position|escape:'htmlall':'UTF-8'}">
        <i class="material-icons">
            <span>refresh</span>
        </i>
    </span>
    <span class="material-icons csvExportButton addIsLoadingClass" data-metric-position="{$position|escape:'htmlall':'UTF-8'}"
        id="{$metricName|escape:'html':'UTF-8'}CsvExportButton">
        <i class="material-icons">
            <span>file_download</span>
        </i>
    </span>

    <div class="panel-body">
        <h2>{$metricitle|escape:'html':'UTF-8'}</h2>
        <div id="{$metricName|escape:'html':'UTF-8'}Container">
            <div class="osListExpander">
                <table class="osListContainer">
                    <tr class="osListHeader">
                        {foreach from=$listCols item=listCol}
                            <th>{$listCol['label']|escape:'html':'UTF-8'}</th>
                        {/foreach}
                        <th class="osCompareCol compareElement"></th>
                        <th class="osCompareCol compareElement"></th>
                    </tr>
                    <tr class="osListItemTemplate" id="{$metricName|escape:'html':'UTF-8'}_item_id_%id%">
                        {foreach from=$listCols item=listCol}
                            <td class="osListCaTd {if isset($listCol['cssClassName'])}{$listCol['cssClassName']|escape:'htmlall':'UTF-8'}{/if}">
                                {if isset($listCol['varNameForLink']) && $listCol['varNameForLink']!=""}
                                    <a href="%{$listCol['varNameForLink']|escape:'htmlall':'UTF-8'}%" target="blank"
                                        rel="noreferrer">%{$listCol['varName']|escape:'html':'UTF-8'}%</a>
                                {else}
                                    %{$listCol['varName']|escape:'html':'UTF-8'}%
                                {/if}
                            </td>
                        {/foreach}
                        <td class="osListCaTd osCompareTotalCa osCompareCol compareElement">%compare_total%</td>
                        <td class="osListCaTd osCompareCol compareElement">
                            %compare_percent_variation%</td>
                    </tr>
                </table>
            </div>
            <div>
                <table id="{$metricName|escape:'html':'UTF-8'}TotalContainer" class="osListTotalContainer">
                    <tr>
                        {foreach from=$listCols item=listCol}
                            <td class="osListCaTd {if isset($listCol['cssClassName'])}{$listCol['cssClassName']|escape:'htmlall':'UTF-8'}{/if}">
                                {if isset($listCol['calcTotal']) && $listCol['calcTotal']==true}
                                    <span class="{$listCol['varName']|escape:'htmlall':'UTF-8'}Total">%{$listCol['varName']|escape:'html':'UTF-8'}_total%</span>
                                {/if}
                            </td>
                        {/foreach}
                        <td class="osListCaTd osCompareTotalCa osCompareCol compareElement">
                            <span class="{$listCol['varName']|escape:'htmlall':'UTF-8'}CompareTotal"></span>
                        </td>
                        <td class="osListCaTd osCompareCol compareElement">
                            <span class="{$listCol['varName']|escape:'htmlall':'UTF-8'}ComparePercentVariationTotal"></span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        {include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/loader.tpl" divId=$metricName|cat:'Loader'}
        {* <a href="#" class="osShowMoreLink" data-opened-text="{l s='Show more' mod='opartstat'}"
            data-closed-text="{l s='Show less' mod='opartstat'}">{l s='Show more' mod='opartstat'}</a> *}
        <a href="#" class="osShowMoreLink" data-metric-name="{$metricName|escape:'htmlall':'UTF-8'}">{l s='Show more' mod='opartstat'}</a>
        <a href="#" class="osShowAllLink" data-metric-name="{$metricName|escape:'htmlall':'UTF-8'}">{l s='Show All' mod='opartstat'}</a>
        <a href="#" class="osShowLessLink" data-metric-name="{$metricName|escape:'htmlall':'UTF-8'}">{l s='Show less' mod='opartstat'}</a>
    </div>
    <input type="hidden" class="storeDataField" id="{$metricName|escape:'html':'UTF-8'}StoredData" />
    <input type="hidden" value="false" id="{$metricName|escape:'html':'UTF-8'}KillDisplay" />
    <input type="hidden" value="{$dir|escape:'html':'UTF-8'}" id="{$metricName|escape:'html':'UTF-8'}Dir" />
    <input type="hidden" value="0" id="{$metricName|escape:'html':'UTF-8'}LastLineDisplayedNumber" />
    <input type="hidden" value="9" id="{$metricName|escape:'html':'UTF-8'}DefaultNumberOfLines" />
</div>
<!-- END best list -->