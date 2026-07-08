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
        <li class="product-flag {if $sticker.sticker_position == 1}sticker_top what2{else}sticker_bottom{/if} {if $sticker.rate > 0}has__stars{/if}" style="background-color: {$sticker.color}; color: {$sticker.color}; padding-right: 0px !important;">
            <span>{$sticker.name}</span>
            <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 34" width="22" height="32" style="left: 100%;">
                <g id="Group 5212">
{*                    <path id="Path 18046" fill-rule="evenodd" fill="currentColor" d="m20 16.2c-3.1-2.8-5.8-6-8.1-9.5q-0.4-0.6-0.9-1.1-0.6-0.5-1.2-0.8-0.6-0.3-1.3-0.5-0.7-0.2-1.4-0.2h-1.2v-1.1q0-0.6-0.2-1.1-0.2-0.6-0.7-1-0.4-0.4-0.9-0.7-0.6-0.2-1.2-0.2h-108.3q-1.1 0-2.1 0.4-1.1 0.5-1.9 1.2-0.7 0.8-1.2 1.9-0.4 1-0.4 2.1v30.1h113.9q0.6 0 1.2-0.3 0.5-0.2 0.9-0.6 0.5-0.5 0.7-1 0.2-0.6 0.2-1.2v-1.1h1.2q0.7 0 1.4-0.1 0.7-0.2 1.3-0.5 0.6-0.4 1.2-0.9 0.5-0.4 0.9-1c2.3-3.5 5-6.8 8.1-9.6l1.8-1.6z"/>*}
                    <path id="Path 18046" fill-rule="evenodd" fill="currentColor" d="M20 17.05 L0 0 H-107 Q-111 0 -111 4 V30.1 Q-111 34.1 -107 34.1 H0 Z"></path>
                </g>
            </svg>
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