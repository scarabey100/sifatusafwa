<form action="{$formLink|escape:'html':'UTF-8'}" enctype="multipart/form-data" class="defaultForm form-horizontal" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="material-icons mi-assessment"><span>link</span></i>
            {l s='Trackable links creator' mod='opartstat'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-4">
                    {l s='Url' mod='opartstat'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="url" id="trackableUrl"
                        value="{if isset($trackableLinkUrl)}{$trackableLinkUrl|escape:'html':'UTF-8'}{/if}" class="" />
                    <p class="help-block">
                        {l s='Add here your page link.' mod='opartstat'}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-4">
                    {l s='Saved preset [optional]' mod='opartstat'}
                </label>
                <div class="col-lg-8">
                    <select name="savedPresets" id="savedPresets">
                        <option>{l s='Choose your saved preset' mod='opartstat'}</option>
                        {foreach from=$savedPresets item=preset}                                
                            <option value="{$preset.utmSource|escape:'html':'UTF-8'}|{$preset.utmMedium|escape:'html':'UTF-8'}|{$preset.utmCampaign|escape:'html':'UTF-8'}">source = {$preset.utmSource|escape:'html':'UTF-8'} | medium = {$preset.utmMedium|escape:'html':'UTF-8'} | campaign = {$preset.utmCampaign|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <p class="help-block">
                        {l s='If you already saved your preset. Select it here.' mod='opartstat'}
                    </p>
                </div>
                <label class="control-label col-lg-4">
                    {l s='Source' mod='opartstat'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="source" id="trackableSource"
                        value="{if isset($trackableLinkSource)}{$trackableLinkSource|escape:'html':'UTF-8'}{/if}" class="" />
                    <p class="help-block">
                        {l s='Add here the source where the link will be published. Example : google.com' mod='opartstat'}
                    </p>
                </div>
                <label class="control-label col-lg-4">
                    {l s='Medium' mod='opartstat'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="medium" id="trackableMedium"
                        value="{if isset($trackableLinkMedium)}{$trackableLinkMedium|escape:'html':'UTF-8'}{/if}" class="" />
                    <p class="help-block">
                        {l s='Add here the medium used to promote this link. Example : adwords' mod='opartstat'}
                    </p>
                </div>
                <label class="control-label col-lg-4">
                    {l s='Campaign' mod='opartstat'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="campaign" id="trackableCampaign"
                        value="{if isset($trackableLinkCampaign)}{$trackableLinkCampaign|escape:'html':'UTF-8'}{/if}" class="" />
                    <p class="help-block">
                        {l s='Add here the campaign corresponding to this link. Example : christmas-event' mod='opartstat'}
                    </p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button value="1" id="createAndCopyBtn" name="submitOpartStatConfig" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Create and copy link' mod='opartstat'}
            </button>
            <button value="1" name="submitSavePreset" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save this preset' mod='opartstat'}
            </button>
            <div id="trackableLinkContainer"></div>
        </div>
    </div>
</form>
<script type="text/javascript">
    $('#createAndCopyBtn').click(function(e) {
        e.preventDefault()
        createAndCopyTrackableLink()
    })
    $('#savedPresets').on("change",function() {
        populateTrackableFields($(this))
    });
</script>