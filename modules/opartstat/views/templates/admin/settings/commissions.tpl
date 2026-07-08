<form id="configuration_form" class="defaultForm form-horizontal" action="{$settingsCommissionsLink|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data"
    novalidate="">
    <input type="hidden" name="submitOpartUpdateUseCommissions" value="1">
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s="Commissions and fees settings" mod='opartstat'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-4">
                    {l s='Activ commissions and fees' mod='opartstat'}
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="OPARTSTAT_USE_COMMISSIONS" id="OPARTSTAT_USE_COMMISSIONS_on" value="1"
                            {if $useCommissions == 1}checked="checked" {/if}>
                        <label for="OPARTSTAT_USE_COMMISSIONS_on">{l s="yes" mod='opartstat'}</label>
                        <input type="radio" name="OPARTSTAT_USE_COMMISSIONS" id="OPARTSTAT_USE_COMMISSIONS_off"
                            value="0" {if $useCommissions == 0}checked="checked" {/if}>
                        <label for="OPARTSTAT_USE_COMMISSIONS_off">{l s="no" mod='opartstat'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                    <p class="help-block"></p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="configuration_form_submit_btn" name="submitOpartUpdateUseCommissions"
                class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s="Save" mod='opartstat'}
            </button>
        </div>
    </div>
</form>