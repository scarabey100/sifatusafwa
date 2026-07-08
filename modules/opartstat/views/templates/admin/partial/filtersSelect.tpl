<div class="osCalendarTitle {if $isExclude}osCalendarTitleMarginTop{/if}">{if $isExclude}{l s='Exclude' mod='opartstat'}{else}{l s='Include' mod='opartstat'}{/if} :
<select class="addFilterSelect" data-filter-type="{if $isCompare}compare{else}initial{/if}" data-exclude-include="{if $isExclude}exclude{else}include{/if}">
        <option value="">
            {l s='Add a filter' mod='opartstat'}
        </option>
        <option value="products">
            {l s='Products' mod='opartstat'}
        </option>
        <option value="attributes">
            {l s='Attributes' mod='opartstat'}
        </option>        
        <option value="features">
            {l s='Features' mod='opartstat'}
        </option>
        <option value="categories">
            {l s='Categories' mod='opartstat'}
        </option>
        <option value="brands">
            {l s='Brands' mod='opartstat'}
        </option>
        <option value="customerGroups">
            {l s='Customer groups' mod='opartstat'}
        </option>
        <option value="countries">
            {l s='Countries' mod='opartstat'}
        </option>
        <option value="paymentMethods">
            {l s='Payment methods' mod='opartstat'}
        </option>        
        <option value="device">
            {l s='Device' mod='opartstat'}
        </option>
    </select>
    {if $isCompare && !$isExclude}
        {* <a href="#" class="useSameFilterBtn"><span class="material-icons">content_copy</span>{l s='Use same filters' mod='opartstat'}</a> *}
        <a href="#" class="useSameFilterBtn"><i class="material-icons content_copy"><span>content_copy</span></i>{l s='Use same filters' mod='opartstat'}</a>
    {/if}
</div>
<div class="{if $isExclude}exclude{else}include{/if}FiltersContainer"></div>