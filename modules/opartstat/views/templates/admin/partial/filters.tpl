<div {if $reportType=='live'}style="display:none" {/if} id="filtersContainer" class="panel  panel-default">
	<div class="panel-heading">
		<h2>{l s='Choose your filters' mod='opartstat'}</h2>
	</div>
	<div class="panel-body">
		<div id="initialCalendarAndFiltersContainer">
			<div class="osFirstCalendarContainer">
				<div class="osCalendarTitle choosePeriodTitle">{l s='Choose a period' mod='opartstat'}</div>
				<select id="osPresetDateInitial" class="osSelectPresetDate">
					<option value="custom" {if $reportSettings['initial']['date']==''}selected{/if}>
						{l s='Choose a preset period' mod='opartstat'}
					</option>
					<option value="today" {if $reportSettings['initial']['date']=='today'}selected{/if}>
						{l s='Today' mod='opartstat'}
					</option>
					<option value="yesterday" {if $reportSettings['initial']['date']=='yesterday'}selected{/if}>
						{l s='Yesterday' mod='opartstat'}
					</option>
					<option value="last7" {if $reportSettings['initial']['date']=='last7'}selected{/if}>
						{l s='Last 7 days' mod='opartstat'}
					</option>
					<option value="last30" {if $reportSettings['initial']['date']=='last30'}selected{/if}>
						{l s='Last 30 days' mod='opartstat'}
					</option>
					<option value="last90" {if $reportSettings['initial']['date']=='last90'}selected{/if}>
						{l s='Last 90 days' mod='opartstat'}
					</option>
					<option value="last365" {if $reportSettings['initial']['date']=='last365'}selected{/if}>
						{l s='Last 365 days' mod='opartstat'}
					</option>
                    <option value="lastWeek" {if $reportSettings['initial']['date']=='lastWeek'}selected{/if}>
                        {l s='Last week' mod='opartstat'}
                    </option>
					<option value="lastMonth" {if $reportSettings['initial']['date']=='lastMonth'}selected{/if}>
						{l s='Last month' mod='opartstat'}
					</option>
					<option value="weekToDate" {if $reportSettings['initial']['date']=='weekToDate'}selected{/if}>
						{l s='Week to date' mod='opartstat'}
					</option>
					<option value="monthToDate" {if $reportSettings['initial']['date']=='monthToDate'}selected{/if}>
						{l s='Month to date' mod='opartstat'}
					</option>
					<option value="lastYear" {if $reportSettings['initial']['date']=='lastYear'}selected{/if}>
						{l s='Last year' mod='opartstat'}
					</option>
					<option value="yearToDate" {if $reportSettings['initial']['date']=='yearToDate'}selected{/if}>
						{l s='Year to date' mod='opartstat'}
					</option>
				</select>
				<div class="osInputDateContainer">
					<input type="text" class="osDateinput" id="dateFrom"
						data-error-msg="{l s='The first date is not valid' mod='opartstat'}" data-allow-null="false"
						size="10">
					<input type="text" class="osDateinput" id="dateTo"
						data-error-msg="{l s='The second date is not valid' mod='opartstat'}" data-allow-null="false"
						size="10">
				</div>
				<div class="datepicker" id="datepickerInitial"></div>
				<div class="osCompareOrNotContainer">
					<input type="checkbox" id="compareOrNot" name="compareOrNot" /> <label
						for="compareOrNot">{l s='Compare to another period' mod='opartstat'}</label>
				</div>
			</div>

			<div class="initialFiltersContainer addFilterContainer">
				{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/filtersSelect.tpl"  isCompare=false isExclude=false}
				{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/filtersSelect.tpl"  isCompare=false isExclude=true}	
			</div>
		</div>

		<div id="compareCalenderHidder">
			<div id="compareCalendarAndFiltersContainer">
				<div class="osCompareCalendarContainer" id="osCompareCalendarContainer">
					<div class="osCalendarTitle choosePeriodTitle">{l s='Compare to' mod='opartstat'}</div>
					<select id="osPresetDateCompare" class="osSelectPresetDate">
						<option value="custom" {if $reportSettings['compare']['date']==''}selected{/if}>
							{l s='Choose a preset period' mod='opartstat'}
						</option>
						<option value="samePeriod" {if $reportSettings['compare']['date']=='samePeriod'}selected{/if}>
							{l s='Same period the previous year' mod='opartstat'}
						</option>
						<option value="previousPeriod"
							{if $reportSettings['compare']['date']=='previousPeriod'}selected{/if}>
							{l s='Previous period this year' mod='opartstat'}
						</option>
						<option value="today" {if $reportSettings['compare']['date']=='today'}selected{/if}>
							{l s='Today' mod='opartstat'}
						</option>
						<option value="yesterday" {if $reportSettings['compare']['date']=='yesterday'}selected{/if}>
							{l s='Yesterday' mod='opartstat'}
						</option>
						<option value="last7" {if $reportSettings['compare']['date']=='last7'}selected{/if}>
							{l s='Last 7 days' mod='opartstat'}
						</option>
						<option value="last30" {if $reportSettings['compare']['date']=='last30'}selected{/if}>
							{l s='Last 30 days' mod='opartstat'}
						</option>
						<option value="last90" {if $reportSettings['compare']['date']=='last90'}selected{/if}>
							{l s='Last 90 days' mod='opartstat'}
						</option>
						<option value="last365" {if $reportSettings['compare']['date']=='last365'}selected{/if}>
							{l s='Last 365 days' mod='opartstat'}
						</option>
                        <option value="lastWeek" {if $reportSettings['compare']['date']=='lastWeek'}selected{/if}>
                            {l s='Last week' mod='opartstat'}
                        </option>
                        <option value="lastMonth" {if $reportSettings['compare']['date']=='lastMonth'}selected{/if}>
                            {l s='Last month' mod='opartstat'}
                        </option>
						<option value="weekToDate" {if $reportSettings['compare']['date']=='weekToDate'}selected{/if}>
							{l s='Week to date' mod='opartstat'}
						</option>
						<option value="monthToDate" {if $reportSettings['compare']['date']=='monthToDate'}selected{/if}>
							{l s='Month to date' mod='opartstat'}
						</option>
						<option value="lastYear" {if $reportSettings['compare']['date']=='lastYear'}selected{/if}>
							{l s='Last year' mod='opartstat'}
						</option>
						<option value="yearToDate" {if $reportSettings['compare']['date']=='yearToDate'}selected{/if}>
							{l s='Year to date' mod='opartstat'}
						</option>
					</select>
					<div class="osInputDateContainer">
						<input type="text" class="osDateinput" id="dateFromCompare"
							data-error-msg="{l s='The first comparison date is not valid' mod='opartstat'}"
							data-allow-null="true" size="10">
						<input type="text" class="osDateinput" id="dateToCompare"
							data-error-msg="{l s='The second comparison date is not valid' mod='opartstat'}"
							data-allow-null="true" size="10">
					</div>
					<div class="datepicker" id="datepickerCompare"></div>
				</div>
				<div class="compareFiltersContainer addFilterContainer">
					{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/filtersSelect.tpl"  isCompare=true isExclude=false}
					{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/filtersSelect.tpl"  isCompare=true isExclude=true}						
				</div>
			</div>
		</div>
		<div class="containerCalendarBtn">
			<a class="btn btn-primary pointer spacedBtn submitDateBtn">
				{l s='Apply' mod='opartstat'}
			</a>
			{if $isAuthorizedToEdit}
				<a id="saveThisSettings" class="btn pointer spacedBtn btn-secondary tooltip1 addIsLoadingClass" data-html="true"
					data-placement="bottom" title="{l s='Click here to save those settings' mod='opartstat'}">
					<i class="material-icons mi-save"><span>save</span></i>
					<span>{l s='Save and apply' mod='opartstat'}</span>
				</a>
			{/if}
		</div>
	</div>
</div>

<div id="productsfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Products :' mod='opartstat'}</span>
	<div class="productsSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_products searchBox"
			placeholder="{l s='Type a product\'s name' mod='opartstat'}" />

		<div class="useKeywordInputContainer hideMe">
			<input type="text" name="productsKeyword" class="useKeywordInput"
				placeholder="{l s='Type your keyword' mod='opartstat'}" />
			<span>{l s="Type your keyword using % to replace missing element"}</span>
		</div>
		<input type="checkbox" class="checkboxDisplayKeywordInput" />
		<label class="labelDisplayKeywordInput">{l s="Click here if you prefer to use a keyword as a filter" mod='opartstat'}</label>

		<input type="hidden" class="hidden_selected_products tempoFiltersValues" data-filter-name="products" value="" />
	</div>
	<div class="displaySelectedItems displaySelected_products" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_products">
		<div id="productsItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="products"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></span> *}
		</div>
	</div>
</div>

<div id="attributesfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Attributes :' mod='opartstat'}</span>
	<div class="attributesSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_attributes searchBox"
			placeholder="{l s='Type an attribute\'s value name' mod='opartstat'}" />
		
		<input type="hidden" class="hidden_selected_attributes tempoFiltersValues" data-filter-name="attributes" value="" />
	
		<label class="labelAndOrOr" for="attributesUseAnd">{l s="The attributes must be " mod='opartstat'}</label>
		<select class="useAndSelect tempoFiltersValues" name="attributesUseAnd" data-filter-name="attributes_useAnd">
			<option value="false">{l s="separated (OR)" mod='opartstat'}</option>
			<option value="true">{l s="combined (AND)" mod='opartstat'}</option>
		</select>
	</div>
	<div class="displaySelectedItems displaySelected_attributes" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_attributes">
		<div id="attributesItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="attributes"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="featuresfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Features :' mod='opartstat'}</span>
	<div class="featuresSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_features searchBox"
			placeholder="{l s='Type a feature\'s value name' mod='opartstat'}" />
		
		<input type="hidden" class="hidden_selected_features tempoFiltersValues" data-filter-name="features" value="" />
	
		<label class="labelAndOrOr" for="featuresUseAnd">{l s="The features must be " mod='opartstat'}</label>
		<select class="useAndSelect tempoFiltersValues" name="featuresUseAnd" data-filter-name="features_useAnd">
			<option value="false">{l s="separated (OR)" mod='opartstat'}</option>
			<option value="true">{l s="combined (AND)" mod='opartstat'}</option>
		</select>
	</div>
	<div class="displaySelectedItems displaySelected_features" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_features">
		<div id="featuresItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="features"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="categoriesfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Categories :' mod='opartstat'}</span>
	<div class="categoriesSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_categories searchBox"
			placeholder="{l s='Type a category\'s name' mod='opartstat'}" />

		<input type="hidden" class="hidden_selected_categories tempoFiltersValues" data-filter-name="categories"
			value="" />

		<div class="containerGetAllChildrenCategories">
			<input type="checkbox" name="getAllChildrenCategories" data-filter-name="getAllChildrenCategories"
			class="tempoFiltersValues getAllChildrenCategoriesCheckbox" />	
			<label class="getAllChildrenCategoriesLabel">{l s='Get all children categories' mod='opartstat'}</label>	
		</div>

		<div class="useKeywordInputContainer hideMe">		
			<input type="text" name="categoriesKeyword" class="useKeywordInput"
				placeholder="{l s='Type your keyword' mod='opartstat'}" />
			<span>{l s="Type your keyword using % to replace missing element"}</span>
		</div>

		<input type="checkbox" class="checkboxDisplayKeywordInput" />
		<label class="labelDisplayKeywordInput">{l s="Click here if you prefer to use a keyword as a filter" mod='opartstat'}</label>
	</div>
	<div class="displaySelectedItems displaySelected_categories" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_categories">
		<div id="categoriesItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="categories"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="brandsfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Brands :' mod='opartstat'}</span>
	<div class="brandsSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_brands searchBox"
			placeholder="{l s='Type a brand\'s name' mod='opartstat'}" />

		<div class="useKeywordInputContainer hideMe">
			<input type="text" name="brandsKeyword" class="useKeywordInput"
				placeholder="{l s='Type your keyword' mod='opartstat'}" />
			<span>{l s="Type your keyword using % to replace missing element"}</span>
		</div>
		<input type="checkbox" class="checkboxDisplayKeywordInput" id="brandsUseKeyword" />
		<label class="labelDisplayKeywordInput"
			for="brandsUseKeyword">{l s="Click here if you prefer to use a keyword as a filter" mod='opartstat'}</label>

		<input type="hidden" class="hidden_selected_brands tempoFiltersValues" data-filter-name="brands" value="" />
	</div>
	<div class="displaySelectedItems displaySelected_brands" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_brands">
		<div id="brandsItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="brands"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="customerGroupsfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Customer groups :' mod='opartstat'}</span>
	<div class="customerGroupsSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_customerGroups searchBox"
			placeholder="{l s='Type a customer group\'s name' mod='opartstat'}" />

		<div class="useKeywordInputContainer hideMe">
			<input type="text" name="customerGroupsKeyword" class="useKeywordInput"
				placeholder="{l s='Type your keyword' mod='opartstat'}" />
			<span>{l s="Type your keyword using % to replace missing element"}</span>
		</div>
		<input type="checkbox" class="checkboxDisplayKeywordInput" id="customerGroupsUseKeyword" />
		<label class="labelDisplayKeywordInput"
			for="customerGroupsUseKeyword">{l s="Click here if you prefer to use a keyword as a filter" mod='opartstat'}</label>

		<input type="hidden" class="hidden_selected_customerGroups tempoFiltersValues" data-filter-name="customerGroups"
			value="" />
	</div>
	<div class="displaySelectedItems displaySelected_customerGroups" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_customerGroups">
		<div id="customerGroupsItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="customerGroups"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="countriesfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Countries :' mod='opartstat'}</span>
	<div class="countriesSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_countries searchBox"
			placeholder="{l s='Type a countrie\'s name' mod='opartstat'}" />

		<div class="useKeywordInputContainer hideMe">
			<input type="text" name="countriesKeyword" class="useKeywordInput"
				placeholder="{l s='Type your keyword' mod='opartstat'}" />
			<span>{l s="Type your keyword using % to replace missing element"}</span>
		</div>
		<input type="checkbox" class="checkboxDisplayKeywordInput" id="countriesUseKeyword" />
		<label class="labelDisplayKeywordInput"
			for="countriesUseKeyword">{l s="Click here if you prefer to use a keyword as a filter" mod='opartstat'}</label>

		<input type="hidden" class="hidden_selected_countries tempoFiltersValues" data-filter-name="countries"
			value="" />
	</div>
	<div class="displaySelectedItems displaySelected_countries" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_countries">
		<div id="countriesItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="countries"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="paymentMethodsfilterOptions" class="filterTemplate">
	<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
	{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
	<span class="filterName">{l s='Payment Methods :' mod='opartstat'}</span>
	<div class="paymentMethodsSearchBoxContainer searchBoxContainer">
		<input type="text" name="searchItem" autocomplete="off" class="ac_input searchBox_paymentMethods searchBox"
			placeholder="{l s='Type payment method\'s name' mod='opartstat'}" />

		<div class="useKeywordInputContainer hideMe">
			<input type="text" name="paymentMethodKeyword" class="useKeywordInput"
				placeholder="{l s='Type your keyword' mod='opartstat'}" />
			<span>{l s="Type your keyword using % to replace missing element"}</span>
		</div>
		<input type="checkbox" class="checkboxDisplayKeywordInput" id="paymentMethodsUseKeyword" />
		<label class="labelDisplayKeywordInput"
			for="paymentMethodsUseKeyword">{l s="Click here if you prefer to use a keyword as a filter" mod='opartstat'}</label>

		<input type="hidden" class="hidden_selected_paymentMethods tempoFiltersValues" data-filter-name="paymentMethods"
			value="" />
	</div>
	<div class="displaySelectedItems displaySelected_paymentMethods" class="panel-body"></div>
	<div class="selectedItemHtmlTpl selectedItemHtmlTpl_paymentMethods">
		<div id="paymentMethodsItemId_%itemId%" data-id-to-remove="%itemId%" data-filter-name="paymentMethods"
			class="selectedItemLabel">
			<span>%selectedItem%</span>
			{* <span class="material-icons removeSelectedItem" onclick="removeItemField($(this))">highlight_remove</span> *}
			<i class="material-icons removeSelectedItem" onclick="removeItemField($(this))"><span>highlight_remove</span></i>
		</div>
	</div>
</div>

<div id="devicefilterOptions" class="filterTemplate">
	<div class="checkboxFilterContainer">
		<i class="material-icons removeFilterOption" onclick="removeFilterOption($(this))"><span>highlight_remove</span></i>
		{* <span class="material-icons removeFilterOption" onclick="removeFilterOption($(this))">highlight_remove</span> *}
		<span class="filterName">{l s='Devices :' mod='opartstat'}</span>
		<div class="alignCheckboxFilter">
			<input type="checkbox" value="1" id="deviceFilterComputer" class="checkboxFilter">
			<label for="deviceFilterComputer" id="deviceFilterComputerLabel" class="checkboxFilterLabel">{l s='Computer' mod='opartstat'}</label>

			<input type="checkbox" value="2" id="deviceFilterTablet" class="checkboxFilter">
			<label for="deviceFilterTablet" id="deviceFilterTabletLabel" class="checkboxFilterLabel">{l s='Tablet' mod='opartstat'}</label>

			<input type="checkbox" value="4" id="deviceFilterMobile" class="checkboxFilter">
			<label for="deviceFilterMobile" id="deviceFilterMobileLabel" class="checkboxFilterLabel">{l s='Mobile' mod='opartstat'}</label>
		</div>
		<input type="hidden" class="hidden_selected_device tempoFiltersValues" data-filter-name="device" value="" />
	</div>
</div>