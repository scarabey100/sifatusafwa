<div id="shareReportModal" class="opartStatModal">
    <i class="material-icons mi-close" onclick="hideSharedReportModal();"><span>close</span></i>
    <h2>{l s="Choose who you want to share this report with" mod='opartstat'}</h2>
    {include file=$smarty.const._PS_MODULE_DIR_|cat:"opartstat/views/templates/admin/partial/loader.tpl" divId='employeesLoader'}
    <div id="employeesContainer"></div>
    <div class="alert alert-danger hideMe templateMsg">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {l s='Error during rights update' mod='opartstat'}
    </div>
    <div class="alert alert-success hideMe templateMsg">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {l s='Rights successfully updated' mod='opartstat'}
    </div>
</div>