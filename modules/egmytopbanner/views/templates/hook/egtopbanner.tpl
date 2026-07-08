{if $elements}
    <div class="header__top--banner">
        <div class="header__top--banner--inner">
            {foreach from=$elements item=element}
                <div class="header__top--banner--item" style="background-color : {$element.color}">
                    {$element.content_b nofilter}
                </div>
            {/foreach}
        </div>
    </div>
{/if}
