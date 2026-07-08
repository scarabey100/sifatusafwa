{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
{if isset($error_raw)}
	<div class="alert alert-warning">{$error_raw nofilter}</div>
{/if}
{if $useRemote == true}
	{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/demTopBar.tpl" demTimeLeft=$demTimeLeft opartAfId=$opartAfId}
{/if}
{if $isAuthorizedToEdit}
	<a class="btn btn-primary displaySideBarBtn openMenuBtn tooltip1 opartStatTopBtn" data-html="true"
		data-placement="bottom" title="{l s='Manage metrics position in this report' mod='opartstat'}" id="open_osSideBar">
		<i class="material-icons mi-ordering"><span>format_line_spacing</span></i>
		<span class="label">{l s='Manage positions' mod='opartstat'}</span>
	</a>
	{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/sidebar.tpl" allActiveMetrics=$allActiveMetrics}
{/if}

<div class="opHeader">
	<a id="open_filtersContainer" class="btn btn-primary pointer openMenuBtn opartStatTopBtn"
		{if $reportType=='live'}style="display:none" {/if}>
		<i class="material-icons mi-filter_list"><span>filter_list</span></i>
		<span class="label">{l s='Filter' mod='opartstat'}</span>
	</a>

	{* line break are used by jquery tooltip here *}
	<span {if $reportType=='live'}style="display:none" {/if} class="help-box" data-html="true" data-placement="bottom"
		title="{if $excludeShipping==1}{l s='Shipping costs are not taken into account in the revenues calculation' mod='opartstat'}{else}{l s='Shipping costs are taken into account in the revenues calculation' mod='opartstat'}{/if} {l s='(You can change this settings in the module configuration)' mod='opartstat'}

		{if $excludeFreeOrder==1}{l s='Free order are excluded from calculation' mod='opartstat'}{else}{l s='Free order are included in the calculation' mod='opartstat'}{/if} {l s='(You can change this settings in the module configuration)' mod='opartstat'}

		{if $lastStatDate!=false}{l s='To avoid overloading your database and slowing down your site, traffic statistics are limited. The oldest recorded visit was %1$s' sprintf=$lastStatDate|date_format:$smartyDateFormat mod='opartstat'}{/if}
		">
	</span>

	<a id="open_reportsMenuContainer" class="btn btn-primary pointer opartStatTopBtn openMenuBtn">
		<i class="material-icons mi-assessment"><span>assessment</span></i>
		<span class="label">{l s='Choose a report' mod='opartstat'}</span>
	</a>
	{if $isAuthorizedToEdit}
		<a id="open_addMetricsMenuContainer" class="btn btn-primary pointer opartStatTopBtn openMenuBtn">
			<i class="material-icons mi-exposure"><span>exposure</span></i>
			<span class="label">{l s='Choose metrics of this report' mod='opartstat'}</span>
		</a>
	{/if}
	<a id="open_helpContainer" class="btn btn-primary pointer opartStatTopBtn openMenuBtn">
		<i class="material-icons mi-help_outline"><span>help_outline</span></i>
		<span class="label">{l s='Help' mod='opartstat'}</span>
	</a>
</div>

<input type="hidden" value="{$reportName|escape:'htmlall':'UTF-8'}" id="currentReportName" />

{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/filters.tpl"  reportType=$reportType reportSettings=$reportSettings isAuthorizedToEdit=$isAuthorizedToEdit}
{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/reportsMenu.tpl"}
{if $isAuthorizedToEdit}
	{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/addMetricsMenu.tpl"}
{/if}
{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/help.tpl"}
{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/filtersSummary.tpl"}
{if $isAuthorizedToEdit}
	{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/shareReportModal.tpl"}
{/if}

{include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/exportPdfModal.tpl"}

<div class="hideMe" id="reportBtnContainer">
	<a href="{$adminControllerLink|escape:'html':'UTF-8'}&duplicateReport={$reportName|escape:'html':'UTF-8'}"
		class="addIsLoadingClass"><i class="material-icons reportNameBtn copyReport"
			id="duplicateReportBtn"><span>content_copy</span></i></a>
	{if $isAuthorizedToEdit}
		<i class="material-icons reportNameBtn deleteReport" id="deleteReportBtn"><span>delete</span></i>
	{/if}
	<div class="confirmDeleteReportContainer hideMe">
		{if $reportName == "live" || count($reportList)<=2}
			<span>{l s='You have to keep at least one report and the "live" report'}</span>
			<button class="cancelDeleteReportBtn btn">OK</button>
		{else}
			<span>{l s='Are you sure you want to delete this report ?' mod='opartstat'}</span>
			<a href="{$adminControllerLink|escape:'html':'UTF-8'}&deleteReport={$reportName|escape:'html':'UTF-8'}"
				class="btn btn-primary btnWithoutI addIsLoadingClass">
				<span>YES</span>
			</a>
			<button class="cancelDeleteReportBtn btn">NO</button>
		{/if}
	</div>	
	{if $displayShareReportBtn == true}
		<i class="material-icons reportNameBtn shareReport" id="shareReportBtn"><span>share</span></i>
	{/if}
	<i class="material-icons reportNameBtn getPdf" id="getPdfBtn"><span>picture_as_pdf</span></i>

	<a class="btn btn-primary pull-right opartStatTopBtn discoverAllOpartModulesBtn" href="{$discoverOpartModuleLink|escape:'htmlall':'UTF-8'}" target="blank">
		<i class="material-icons mi-power">power</i>
		<span class="label">{l s='Discover all our modules' mod='opartstat'}</span>
	</a>
</div>


<div class="osAllStatsPanel">
	{foreach from=$reportContent key=position item=metric}
		{$metric['content'] nofilter} {* can't escape, contain HTML *}
	{/foreach}
</div>

{if $premiumIsActive != 1}
	<div class="opartStatModal" id="needOpartStatPremiumModal">
		<i class="material-icons mi-close closeOpartStatModal" ><span>close</span></i>
		<h2>{l s='This metric requires Op\'art Stat Premium account' mod='opartstat'}</h2>
		<a href="{$adminOpartStatSubscriptionSellsPageUrl|escape:'htmlall':'UTF-8'}" class="btn btn-primary">{l s='Click here to know more' mod='opartstat'}</a>
	</div>
{/if}
<div id="modalBackground"></div>

<script src="{$opartStatDir|escape:'html':'UTF-8'}views/js/apexcharts.js/dist/apexcharts.min.js"></script>
<script>
  (function() {
    // la locale de l'employé BO (ex "fr", "en", "de", ...)
    var requestedLocale = '{$lang_iso|escape:'javascript':'UTF-8'}';

    // on prépare un flag global pour dire "Apex a chargé la locale"
    window.apexLocaleReady = false;

    // URL du contrôleur du module opartstat/locale
    var localeUrl = '{$link->getModuleLink('opartstat', 'locale')|escape:'javascript':'UTF-8'}'
      + '?lang=' + encodeURIComponent(requestedLocale);

    // s'assurer que Apex.chart existe
    if (typeof Apex === 'object') {
      Apex.chart = Apex.chart || {};
      Apex.chart.locales = Apex.chart.locales || [];
    }

    fetch(localeUrl)
      .then(function(res){
        return res.json();
      })
      .then(function(localeData){
        if (typeof Apex !== 'object' || !localeData || localeData.error) {
          throw new Error('Invalid localeData');
        }

        // normalement fr.json a déjà "name": "fr"
        // mais on sécurise si un jour tu ajoutes "en.json" qui n'a pas de name
        if (!localeData.name) {
          localeData.name = requestedLocale;
        }

        // on injecte la locale dans Apex
        Apex.chart.locales.push(localeData);

        // on définit la locale par défaut
        Apex.chart.defaultLocale = localeData.name;

        // on marque la locale comme prête
        window.apexLocaleReady = true;
      })
      .catch(function(e){
        console.error('Cannot load ApexCharts locale', e);

        if (typeof Apex === 'object') {
          // fallback quand même pour pas tout casser
          Apex.chart.defaultLocale = requestedLocale || 'en';
        }

        window.apexLocaleReady = true;
      });
  })();
</script>

<script src="{$opartStatDir|escape:'html':'UTF-8'}views/js/eip.js"></script>

<script src="{$opartStatDir|escape:'html':'UTF-8'}views/js/jsPdf/jspdf.umd.min.js"></script>
<script src="{$opartStatDir|escape:'html':'UTF-8'}views/js/jsPdf/html2canvas.min.js"></script>

<script type="text/javascript">
	{* 1.6 compatibility *}
	{if $ps_version == "1.6"}
		var currencyFormat = "{$currencyFormat|escape:'html':'UTF-8'}"
		var currencySign = "{$currencySign|escape:'html':'UTF-8'}"
		var currencyBlank = "{$currencyBlank|escape:'html':'UTF-8'}"
		var priceDisplayPrecision = 2
	{/if}
	{* *}
	{*var ajaxUrl = '{$ajaxUrl|escape:'javascript':'UTF-8'}' *}
	var osErrorMsg = "<span class='osErrorMsg'>{l s='An error occured' mod='opartstat'}</span>"

	var prestaDateFormat = '{$smartyDateFormat|escape:'html':'UTF-8'}'
	var dateFormat = '{$jsDateFormat|escape:'html':'UTF-8'}'
	var isItemReport = {if $reportType=='product' || $reportType=='category'}true{else}false{/if}


	
	$(document).ready(function() {
		initialStatsToLoad = [
			{foreach from=$reportContent key=position item=metric}			
				{
					metricName: '{$metric['name']|escape:'html':'UTF-8'}',
					dir: '{$metric['dir']|escape:'html':'UTF-8'}',
					callBack: {$metric['callBackJsfunction']|escape:'html':'UTF-8'},
					ajaxCallBack: '{$metric['ajaxCallBack']|escape:'html':'UTF-8'}'
				},
			{/foreach}
		]

	displaySavedFilters()

	$(".openMenuBtn").click(function() {
		displayTopMenuContainer(this)
	})

	$(".datepicker").datepicker({
		showOn: 'focus',
		dateFormat: dateFormat,
		beforeShowDay: function(date) {
			var inputIdSufix = ($(this).attr('id') == "datepickerCompare") ? "Compare" : ""

			var date1 = $.datepicker.parseDate(dateFormat, $("#dateFrom" + inputIdSufix).val())
			var date2 = $.datepicker.parseDate(dateFormat, $("#dateTo" + inputIdSufix).val())
			return [true, date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <=
				date2)) ? "dp-highlight" : ""];
		},
		onSelect: function(dateText, inst) {
			var inputIdSufix = ($(this).attr('id') == "datepickerCompare") ? "Compare" : ""
			var date1 = $.datepicker.parseDate(dateFormat, $("#dateFrom" + inputIdSufix).val())
			var date2 = $.datepicker.parseDate(dateFormat, $("#dateTo" + inputIdSufix).val())
			var selectedDate = $.datepicker.parseDate(dateFormat, dateText)

			var selectPeriodElementId = (inputIdSufix == "Compare") ? "osPresetDateCompare" :
				"osPresetDateInitial"
			$('#' + selectPeriodElementId).val('custom')

			if (!date1 || date2) {
				osChangeInputDateValue('dateFrom' + inputIdSufix, dateText)
				osChangeInputDateValue('dateTo' + inputIdSufix, '')
				$(this).datepicker();
			} else if (selectedDate < date1) {
				osChangeInputDateValue('dateTo' + inputIdSufix, $('#dateFrom').val())
				osChangeInputDateValue('dateFrom' + inputIdSufix, dateText)
				$(this).datepicker();
			} else {
				osChangeInputDateValue('dateTo' + inputIdSufix, dateText)
				$(this).datepicker();
			}
		}
	});

	{if $isAuthorizedToEdit}
		$('#saveThisSettings').click(function(e) {
			e.preventDefault()
			saveSettings('{$reportName|escape:'html':'UTF-8'}', initialStatsToLoad)
		})
	{/if}

	{if $reportType=="product" || $reportType=="category"}
		{if isset($selectedItem)}
			populateItemField({$selectedItem.id|escape:'html':'UTF-8'},"{$selectedItem.name|escape:'html':'UTF-8'}","{$selectedItem.reference|escape:'html':'UTF-8'}")
		{/if}	
	{/if}

	{if $reportType=="live"}
		{* {assign var="defaultPreselectedPeriodFirst" value=""} *}
		osSubmitDate(-1, -1, null, null, initialStatsToLoad);
		setTimeout(refreshPage, {$liveTime|escape:'html':'UTF-8'} * 1000 * 60);
	{/if}

	{if $reportSettings['initial']['date']!='' && $reportType!="live"}
		{if $reportSettings['initial']['date'] == 'custom'}
			var presetDatesFirst = [];
			presetDatesFirst[0] = '{$reportSettings['initial']['from']|escape:'html':'UTF-8'}';
			presetDatesFirst[1] = '{$reportSettings['initial']['to']|escape:'html':'UTF-8'}';
		{else}		
			var presetDatesFirst = getPresetPeriod('{$reportSettings['initial']['date']|escape:'html':'UTF-8'}',true)
		{/if}
		setCalendarDate("datepickerInitial", presetDatesFirst[0], presetDatesFirst[1])
		{* {if $reportSettings['compare']['date']!='custom' || ($reportSettings['compare']['date']!='custom' && array_key_exists('from',$reportSettings['compare']) && array_key_exists('to',$reportSettings['compare']))} *}
		{if $reportSettings['compare']['date']!='custom' || (array_key_exists('from',$reportSettings['compare']) && array_key_exists('to',$reportSettings['compare']))}	
			$('#compareOrNot').prop('checked', true)
			toggleDisplayCompareCalendar()
			{if $reportSettings['compare']['date'] == 'custom' && array_key_exists('from',$reportSettings['compare']) && array_key_exists('to',$reportSettings['compare'])}
				var presetDatesCompare = [];
				presetDatesCompare[0] = '{$reportSettings['compare']['from']|escape:'html':'UTF-8'}';
				presetDatesCompare[1] = '{$reportSettings['compare']['to']|escape:'html':'UTF-8'}';
			{else}
				var presetDatesCompare = getPresetPeriod('{$reportSettings['compare']['date']|escape:'html':'UTF-8'}',true)
			{/if}
			setCalendarDate("datepickerCompare", presetDatesCompare[0], presetDatesCompare[1])
		{/if}
		{if $reportType!="product" && $reportType!="category"}
			osSubmitCalendar(initialStatsToLoad);
		{else if isset($selectedItem)}
			populateItemField({$selectedItem.id|escape:'html':'UTF-8'},"{$selectedItem.name|escape:'html':'UTF-8'}","{$selectedItem.reference|escape:'html':'UTF-8'}")
			osSubmitCalendar(initialStatsToLoad);
		{/if}
	{/if}

	var translateJsObject = {}
	{foreach from=$translationJsArray key=key item=string}
		translateJsObject.{$key|escape:'html':'UTF-8'} = "{$string|escape:'html':'UTF-8'}"
	{/foreach}
	//for test purpose only
	/* 	osChangeInputDateValue('dateFrom', '01/01/2022')
		osChangeInputDateValue('dateTo', '01/12/2022')

		osChangeInputDateValue('dateFromCompare', '01/01/2021')
		osChangeInputDateValue('dateToCompare', '01/12/2021') */
	//end test purposeInly

		$('.submitDateBtn').click(function() {
			osSubmitCalendar(initialStatsToLoad)
		})

		$('.osShowMoreLink').click(function(e) {
			e.preventDefault();
			$(this).parent().find(".osShowLessLink").show();
			showResultList($(this).data('metricName'),100);
		})

		$('.osShowAllLink').click(function(e) {
			e.preventDefault();
			$(this).parent().find(".osShowLessLink").show();
			showResultList($(this).data('metricName'),-1);
		})

		$('.osShowLessLink').click(function(e) {
			e.preventDefault();
			collapseListContent($(this).data('metricName'));
		})

		$('.osDateinput').change(function() {
			if (dateIsValid($(this).val(), dateFormat)) {
				$(this).removeClass('osDateInputError')
				osChangeInputDateValue($(this).attr('id'), $(this).val(), $(this).data('allowNull'))
			}
		})

		$('.osDateinput').on("keyup", function() {
			if ($(this).attr('id') == "dateFromCompare" || $(this).attr('id') == "dateToCompare")
				selectPeriodElementId = 'osPresetDateCompare';
			else
				selectPeriodElementId = 'osPresetDateInitial';
			$('#' + selectPeriodElementId).val('custom')

			if (
				selectPeriodElementId == 'osPresetDateInitial' &&
				dateIsValid($(this).val(), dateFormat) &&
				($("#osPresetDateCompare").val() == "samePeriod" || $("#osPresetDateCompare").val() ==
					"previousPeriod")
			) {
				$("#osPresetDateCompare").trigger('change');
			}
		});


		$("#sortableMetricContainer").sortable({
				placeholder: "sortableMetric"
		});
		$("#sortableMetricContainer").disableSelection();

		{if $isAuthorizedToEdit}
			$('#sideBarSaveBtn').click(function() {
				var position = 0;
				var metrics = $('#sortableMetricContainer .sortableMetric').map(function(i) {
					return [this.id];
				}).get();
				saveMetricConfig(metrics,'{$reportName|escape:'html':'UTF-8'}',true);
			})
		{/if}

		{if $isAuthorizedToEdit}
			$('#addMetricSaveBtn').click(function() {
				var metrics = $('#addMetricsMenuContainer .metricsToAdd').map(function(i) {
					if ($('#' + this.id + ' > .osAddMetricCheckbox').is(':checked'))
						return [this.id];
				}).get();
				saveMetricConfig(metrics,'{$reportName|escape:'html':'UTF-8'}');
			})
		{/if}
		$('.savePeriodBtn').click(function(e) {
			e.preventDefault()
			/* $(this).addClass('isLoading'); */
			metricName = $(this).data('metricName')
			metric = {
				'name': metricName,
				'selectedPeriod': $('#' + metricName + 'SelectPeriod').val()
			}
			saveMetricSelectedPeriod(metric,'{$reportName|escape:'html':'UTF-8'}');
		})

		$('#compareOrNot').change(function() {
			toggleDisplayCompareCalendar()
		})

		$('.osSelectPresetDate').change(function() {
			if ($(this).val() == 'custom')
				return false

			presetDates = getPresetPeriod($(this).val(), true)

			var calendarId = ($(this).attr('id') == "osPresetDateInitial") ? "datepickerInitial" : "datepickerCompare";

			setCalendarDate(calendarId, presetDates[0], presetDates[1])

			console.log(calendarId);
			console.log($("#osPresetDateCompare").val());
			if (calendarId == 'datepickerInitial' && ($("#osPresetDateCompare").val() == "samePeriod" || $(
					"#osPresetDateCompare").val() == "previousPeriod")) {
				$("#osPresetDateCompare").trigger('change');
			}
		})

		$('.help-box').tooltip({ placement: $(this).data('placement') })
		$('.tooltip1').tooltip({ placement: $(this).data('placement') })

		$(".metricsToAdd, .metricsToAdd input[type='checkbox']").on("click", function() {
			if ($(this).hasClass('needOpartStatPremium')) {
				displayOpartStatModal('needOpartStatPremiumModal')
				return false;
			}
			var checkbox = $(this).closest(".metricsToAdd").find("input[type='checkbox']");
			if (checkbox.prop("disabled"))
				return false;

			checkbox.prop("checked", !checkbox.prop("checked"));
			if (checkbox.is(':checked')) {
				$(this).closest(".metricsToAdd").addClass("metricsToAddSelected");
			} else {
				$(this).closest(".metricsToAdd").removeClass("metricsToAddSelected");
			}
		});

		$(".reloadBtn").click(function() {
			var position = $(this).data('metricPosition');
			var metric = initialStatsToLoad[position];
			reloadMetric(metric)
		})

		$(".csvExportButton").click(function() {
			var position = $(this).data('metricPosition');
			var metric = initialStatsToLoad[position];
			getCsvDatas(metric)
		})

	})

	locale = '{$currentLocaleIsoCode|escape:'html':'UTF-8'}'
	$.getJSON('{$opartStatDir|escape:'html':'UTF-8'}views/js/apexcharts.js/dist/locales/' + locale + '.json', function(data) {
	Apex.chart = {
	locales: [data],
	defaultLocale: locale
	}
	})

	$(document).ready(function() {
		$('.addIsLoadingClass').click(function() {
			$(this).addClass('isLoading');
		})
		$('.closeOpartStatModal').click(function() {
			modalId = $(this).parent().attr('id');
			hideOpartStatModal(modalId);
		})
	})

	$(document).ready(function() {
		$("#searchMetricField").on("keyup", function() {
			var value = $(this).val().toLowerCase();
			$("#allMetricContainer .metricName").filter(function() {
				parentNode = $(this).parent()
				parentNode.toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});

			$("#allMetricContainer").children('h2').each(function() {
				var categoryTitle = $(this);
				var metricsContainer = categoryTitle.nextUntil('h2');
				displayCategoryTitle = false;
				metricsContainer.each(function() {
					if($(this).is(':visible')) {
						displayCategoryTitle = true;
						return false;
					}
				})
				if(displayCategoryTitle)
					categoryTitle.show();
				else
					categoryTitle.hide();
			});
		});

		$('.osSelectPeriod').on('change', function() {
			metricName = $(this).data('metricName')
			stringJson = $('#' + metricName + 'ContainerData').val()
			var jsonData = JSON.parse(stringJson);
			console.log($(this))
			console.log(metricName)
			console.log(jsonData)
			displayTrend(jsonData, metricName, true)
		});

		$('.osDisplayPieChartBtn').on('click', function() {
			toggleListPieChart($(this))
		});

		$('.osDisplayListBtn').on('click', function() {
			toggleListPieChart($(this))
		});

		$('.addFilterSelect').on('change', function() {
			displayFilterOption($(this))
		})

		$('.useSameFilterBtn').on('click', function() {
			copyInitialFilters()
		})
	});

	{if $isAuthorizedToEdit}
		$('.page-title').editable({
			onChange: function(values) {
				editReportName(values, '{$reportName|escape:'html':'UTF-8'}');
			}
		});
	{else}
		$('.page-title').addClass('nonEditable');
	{/if}

	$('#reportBtnContainer').insertAfter($('.page-title'));
	$('#reportBtnContainer').removeClass('hideMe');
	$('#deleteReportBtn').on('click', function() {
		$('.confirmDeleteReportContainer').fadeIn('fast');
	})
	$('.cancelDeleteReportBtn').on('click', function(e) {
		e.preventDefault();
		$('.confirmDeleteReportContainer').fadeOut('fast');
	})
	$('.checkboxFilter').on('change', function() {
		populateHiddenInputForFilterCheckbox($(this));
	})
	{if $isAuthorizedToEdit}
		$('#shareReportBtn').on('click', function() {
			displayShareReportModal('{$reportName|escape:'html':'UTF-8'}');
		})
	{/if}

	$('#getPdfBtn').on('click', function(e) {
		e.preventDefault();
		generatePDF();
	})
</script>