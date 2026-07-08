<div class="editions">
    <div class="container">
        {if $combinations && $combinations|@count > 0}
         {hook h='displayEditionBloc'}
        {/if}
        <div class="editions__content">
            <div class="editions__tabs">
                <div class="editions__tabs--nav">
                    {foreach $combinations as $key=>$combination name=combinations} 
                    <div data-tab="tab-{$smarty.foreach.combinations.index}" class="editions__tabs--link {if $smarty.foreach.combinations.first}active{/if}">
                        <div class="editions__tabs--link--img">
                        
                            {if $combination.id_image > 0}
                            <img width="110" height="132" src="{$link->getImageLink($product->link_rewrite, $combination.id_image, 'small_default')}" alt="{$product.meta_description}" loading="lazy" />
                            {else}
                                {foreach $product.images as $image}
                                    {if $image.cover == "1"}
                                        <img width="110" height="132" src="{$link->getImageLink($product->link_rewrite, $image.id_image, 'small_default')}" alt="{$product.meta_description}" loading="lazy" />
                                    {/if}
                                {/foreach}
                            {/if}
                        </div>
                        {assign var="first_value" value=$combination.attributes_values|@reset}
                        <div class="editions__tabs--link--name">{$first_value}</div>
                    </div>
                    {/foreach}
                </div>
                <div class="editions__tabs--content">
                    {foreach $combinations as $key=>$combination name=combinations}
                    <div id="tab-{$smarty.foreach.combinations.index}" class="editions__tabs--tab {if $smarty.foreach.combinations.first}active{/if}">
                        <div class="editions__tabs--tab--intro">
                            <div class="editions__tabs--tab--img"> 
                                {if $combination.id_image > 0}
                                <img width="407" height="492" src="{$link->getImageLink($product->link_rewrite, $combination.id_image, 'medium_default')}" alt="{$product.meta_description}" loading="lazy" />
                                {else}
                                    {foreach $product.images as $image}
                                        {if $image.cover == "1"}
                                            <img width="407" height="492" src="{$link->getImageLink($product->link_rewrite, $image.id_image, 'medium_default')}" alt="{$product.meta_description}" loading="lazy" />
                                        {/if}
                                    {/foreach}
                                {/if}
                            </div>
                            <div class="editions__tabs--tab--description">
                                <h3>{$combination.attributes_values[4]}</h3>
                                <p>{Product::getCombinationDescription($key)}</p>
                                <p>{Product::getProductNameAr($product.id)}</p>
                            </div>
                        </div>
                        {if  FFC::getCombinationsFeaturesNew($product.id_product,$key,$product->features)}
                            <div class="editions__tabs--tab--details">
                                <div class="product__details">
                                    <ul>  
                                    {foreach FFC::getCombinationsFeaturesNew($product.id_product,$key,$product->features) item=feature}
                                        {if $feature.value}
                                        <li><strong>{$feature.name}</strong>{$feature.value|escape:'htmlall'|nl2br nofilter}</li>
                                        {/if}
                                    {/foreach} 
                                    </ul>
                                </div>
                            </div>
                        {/if}
                        <div class="editions__tabs--tab--action">
                            <a href="#" data-id="{$combination.list|replace:"'":""|intval}" class="btn btn-orange change-edition">{l s='Choose this edition' d='Shop.Theme.Global'}</a>
                        </div>
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>