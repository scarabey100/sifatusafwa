<div class="js-product-details tab-pane fade in active"
     id="product-details"
     data-product="{$product.embedded_attributes|default:[]|json_encode}"
     role="tabpanel"
  > 
  {block name='product_reference'}
    {if isset($product.reference_to_display) && $product.reference_to_display neq ''}
      <div class="product__ref product__ref_js" style="display:none;">{l s='Reference:' d='Shop.Theme.Anass'} {$product.reference_to_display}</div>
    {/if}
  {/block}
    {if !empty($product.grouped_features)}
      <div class="product__details">
        <div class="product__details_js">
        <input type="hidden" class="quantity_available_in_stock" name="quantity_available_in_stock" value="{StockAvailable::getQuantityAvailableByProduct($product.id_product,$product.id_product_attribute)}">
        {foreach $combinations as $key=>$combination name=combinations}
          
          {if $product.reference_to_display == $combination.reference}
              <p>{Product::getCombinationDescription($key)}</p>
          {/if}
         
        {/foreach}
        </div>
      <ul>
      {foreach from=$product.grouped_features item=feature}
          {if $feature.value}
             <li><strong>{$feature.name}</strong>{$feature.value|escape:'htmlall'|nl2br nofilter}</li>
          {/if}
      {/foreach}
      </ul>
        </div>
  {/if}  
</div>
