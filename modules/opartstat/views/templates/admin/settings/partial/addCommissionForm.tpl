<form id="addCommissionForm" class="defaultForm form-horizontal" action="{$settingsCommissionsLink|escape:'html':'UTF-8'}" method="post"
    enctype="multipart/form-data" novalidate="">
    {if isset($isEdit) && $isEdit == true}
        <input type="hidden" value="{$updateCommissionId|escape:'html':'UTF-8'}" name="updateCommissionId" />
    {/if}
    <input type="hidden"
        name="{if isset($isEdit) && $isEdit == true}submitOpartUpdateCommission{else}submitOpartAddCommission{/if}"
        value="1">
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {if isset($isEdit) && $isEdit == true}{l s="Update a commission or fee" mod='opartstat'}{else}{l s="Add a commission or fee" mod='opartstat'}{/if}
        </div>
        <div class="form-wrapper">
            <div class="form-group addFormContainer">
                <div class="commissionCalendarContainer">
                    <div class="datepicker commissionsDatePicker" id="datepickerCompare"></div>
                    <div class="inputDateContainer">
                        <div class="inlineInput">
                            <label for="dateFrom">{l s="Start date" mod='opartstat'}</label>
                            <input type="text" name="dateFrom" class="osDateinput" id="dateFrom"
                                value="{if isset($dateFrom)}{$dateFrom|escape:'html':'UTF-8'}{/if}"
                                data-error-msg="{l s='The first date is not valid' mod='opartstat'}"
                                data-allow-null="false" size="10">
                        </div>
                        <div class="inlineInput">
                            <label for="dateTo">{l s="End date" mod='opartstat'}</label>
                            <input type="text" name="dateTo" class="osDateinput" id="dateTo"
                                value="{if isset($dateTo) && $dateTo != null}{$dateTo|escape:'html':'UTF-8'}{/if}"
                                data-error-msg="{l s='The second date is not valid' mod='opartstat'}"
                                data-allow-null="false" size="10">
                            <p class="help-block">
                                {l s="Leave End date field blank if you don't want to specify an end limit" mod='opartstat'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group addFormContainer">
                <div class="inlineInput">
                    <label class="control-label">
                        {l s="Fixed fees" mod='opartstat'}
                    </label>
                    <input type="text" name="fixedFees" id="fixedFees" value="{if isset($fixedFees)}{$fixedFees|escape:'html':'UTF-8'}{/if}"
                        class="">
                    <p class="help-block">
                        {l s="Add here the fixed fees for each order" mod='opartstat'}
                    </p>
                </div>
                <div class="inlineInput">
                    <label class="control-label">
                        {l s="Variable fees (%)" mod='opartstat'}
                    </label>
                    <input type="text" name="variableFees" id="variableFees"
                        value="{if isset($variableFees)}{$variableFees|escape:'html':'UTF-8'}{/if}" class="">
                    <p class="help-block">
                        {l s="Add here the variable costs (as a percentage of the total amount including VAT of the order)" mod='opartstat'}
                    </p>
                </div>
                <div class="inlineInput">
                    <label class="control-label">
                        {l s="Payment method" mod='opartstat'}
                    </label>

                    <select name="paymentMethod" id="paymentMethod">
                        <option value="0">{l s="Choose a payment method" mod='opartstat'}</option>
                        <option value="keyword" {if isset($paymentMethod) && $paymentMethod == "keyword"}selected{/if}>{l s="Use a keyword" mod='opartstat'}</option>
                        <optgroup label="{l s="Payment method" mod='opartstat'}">
                            {foreach from=$paymentMethods item=paymentItem}
                            <option value="{$paymentItem|escape:'html':'UTF-8'}" {if isset($paymentMethod) && $paymentItem == $paymentMethod}selected{/if}>{$paymentItem|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                    <p class="help-block">
                        {l s="Choose the payment method affected by these costs" mod='opartstat'}
                    </p>
                </div>
                        <div class="inlineInput {if !isset($paymentMethod) || $paymentMethod != "keyword"}hideMe{/if}" id="paymentKeywordInputContainer">
                    <label class="control-label">
                        {l s="Keyword" mod='opartstat'}
                    </label>
                    <input type="text" name="paymentKeyword" id="paymentKeyword"
                        value="{if isset($paymentKeyword)}{$paymentKeyword|escape:'html':'UTF-8'}{/if}" class="">
                    <p class="help-block">
                    {l s="Enter your keyword using % to replace the missing parts." mod='opartstat'}<br />
                    <br />
                    {l s='For instance : if your payment method is "Ebay xxxx" where xxxx is an order number. Type "Ebay%" (without quote) as keyword. The % charactère will be replaced by the order number automatically' mod='opartstat'}
                    </p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="configuration_form_submit_btn"
                name="{if isset($isEdit) && $isEdit == true}submitOpartUpdateCommission{else}submitOpartAddCommission{/if}"
                class="btn btn-default pull-right">
                <i class="process-icon-save"></i>
                {if isset($isEdit) && $isEdit == true}{l s="Update" mod='opartstat'}{else}{l s="Add" mod='opartstat'}{/if}
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
    var dateFormat = '{$jsDateFormat|escape:'html':'UTF-8'}'
    $(".datepicker").datepicker({
        showOn: 'focus',
        dateFormat: dateFormat,
        beforeShowDay: function(date) {
            var date1 = $.datepicker.parseDate(dateFormat, $("#dateFrom").val())
            var date2 = $.datepicker.parseDate(dateFormat, $("#dateTo").val())
            return [true, date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 &&
                date <=
                date2)) ? "dp-highlight" : ""];
        },
        onSelect: function(dateText, inst) {
            var date1 = $.datepicker.parseDate(dateFormat, $("#dateFrom").val())
            var date2 = $.datepicker.parseDate(dateFormat, $("#dateTo").val())
            var selectedDate = $.datepicker.parseDate(dateFormat, dateText)

            if (!date1 || date2) {
                osChangeInputDateValue('dateFrom', dateText)
                osChangeInputDateValue('dateTo', '')
                $(this).datepicker();
            } else if (selectedDate < date1) {
                osChangeInputDateValue('dateTo', $('#dateFrom').val())
                osChangeInputDateValue('dateFrom', dateText)
                $(this).datepicker();
            } else {
                osChangeInputDateValue('dateTo', dateText)
                $(this).datepicker();
            }
        }
    });
    {if isset($isEdit) && $isEdit == true}
        $('html, body').animate({
            scrollTop: $('#addCommissionForm').offset().top
        }, 1000);
    {/if}
    $('#paymentMethod').on('change',function() {
        if($(this).val()=='keyword') {
            $('#paymentKeywordInputContainer').fadeIn('fast').css("display", "inline-block");
        }
        else {
            $('#paymentKeywordInputContainer').fadeOut('fast')
        }
    })
</script>