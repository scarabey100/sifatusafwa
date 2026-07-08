<form id="cleanCache" class="defaultForm form-horizontal" action="{$settingsAdvancedLink|escape:'html':'UTF-8'}" method="post"
    enctype="multipart/form-data" novalidate="">
    <div class="panel" id="fieldset_0">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {l s="Delete cache files" mod='opartstat'}
        </div>
        <div class="form-wrapper">  
        {l s="there are currently %d files in the cache folder" sprintf=[$cacheFilesCount|escape:'html':'UTF-8'] mod='opartstat'}
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="deleteCacheFile" name="submitCleanCache" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>
                {l s="Clear Op'art Stat cache" mod='opartstat'}
            </button>
        </div>
    </div>
</form>