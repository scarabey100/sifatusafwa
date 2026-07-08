<div class="form-group">
    <label>{$smarty.const.LL|escape:'html':'UTF-8'}</label>
    <div class="col-lg-9">
        {foreach from=$stickers item=sticker}
            <div class="checkbox" style="font-size: 20px;">
                <label style="background-color: {$sticker.color};padding: 7px">
                    <input type="checkbox" name="stickers[]" value="{$sticker.id_sticker}" {if in_array($sticker.id_sticker, $selected_sticker_ids)}checked{/if}>
                    {$sticker.name}
                    <span class="stars">
                        {for $i=1 to $sticker.rate}
                            <i class="fa fa-star"></i>
                        {/for}
                    </span>
                </label>
            </div>
        {/foreach}
    </div>
</div>