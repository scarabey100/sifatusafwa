{*<div class="form-group">*}
{*    <label>{$smarty.const.LL|escape:'html':'UTF-8'}</label>*}
{*    <div class="col-lg-9">*}
{*        {foreach from=$stickers item=sticker}*}
{*            <div class="sticker" style="font-size: 20px;">*}
{*                <span style="background-color: {$sticker.color}; padding: 7px; display: inline-block;">*}
{*                    {$sticker.name}*}
{*                    {if $sticker.rate > 0}*}
{*                        <span class="stars">*}
{*                            {for $i=1 to $sticker.rate}*}
{*                                <i class="fa fa-star"></i>*}
{*                            {/for}*}
{*                        </span>*}
{*                    {/if}*}
{*                </span>*}
{*            </div>*}
{*        {/foreach}*}
{*    </div>*}
{*</div>*}

{foreach from=$stickers item=sticker}
    <li class="product-flag eg-sticker ss-ribbon {if $sticker.sticker_position == 1}sticker_top what2{else}sticker_bottom{/if} {if $sticker.rate > 0}has__stars{/if}" style="background-color: {$sticker.color|escape:'html':'UTF-8'};">
        <span>{$sticker.name|escape:'html':'UTF-8'}</span>
        {if $sticker.rate > 0}
            <div class="product-flag__ratings">
                {for $i=1 to $sticker.rate}
                    <svg xmlns="http://www.w3.org/2000/svg" width="10.807" height="10.317" viewBox="130.012 12.667 10.807 11.317">
                        <path d="m135.794 12.943.915 2.817a.787.787 0 0 0 .749.544h2.962c.386 0 .546.493.234.72l-2.397 1.74a.787.787 0 0 0-.286.88l.916 2.818c.12.367-.3.671-.612.445l-2.397-1.741a.788.788 0 0 0-.925 0l-2.397 1.74a.398.398 0 0 1-.612-.444l.916-2.817a.787.787 0 0 0-.286-.88l-2.397-1.741a.398.398 0 0 1 .234-.72h2.962c.341 0 .643-.22.748-.544l.916-2.817a.398.398 0 0 1 .757 0" fill="#ffb527" fill-rule="evenodd" data-name="Path 18051"/>
                    </svg>
                {/for}
            </div>
        {/if}
    </li>
{/foreach}
{if !empty($classicDiscount)}
    <li class="product-flag eg-classic-discount ss-ribbon {if $classicDiscount.sticker_position == 1}sticker_top{else}sticker_bottom{/if}" style="background-color: {$classicDiscount.color|escape:'html':'UTF-8'};">
        <span>{$classicDiscount.label|escape:'html':'UTF-8'}</span>
    </li>
{/if}
{if !empty($discount)}
    <li class="product-flag eg-discount-flag ss-signet eg-discount-{$discount.type|escape:'html':'UTF-8'}">
        <span>{$discount.label|escape:'html':'UTF-8'}</span>
    </li>
{/if}