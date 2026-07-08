<form action="#" class="productDealFormPopup form-horizontal product-page justify-content-md-center product-form">
    <h2>Create new book deal</h2>
    <div class="form-group text-widget language">
        <label>
            Language select
        </label>
        <ul id="language_selector">
            {foreach $languages as $l}
                <li class="{if $l@first}active{/if} " data-id_lang="{$l.id_lang}"><img src="https://www.sifatusafwa.com/img/l/{$l.id_lang}.jpg" /></li>
            {/foreach}
        </ul>
    </div>
    <script>
        {foreach $languages as $l}
        <li class="{if $l@first}active{/if} " data-id_lang="{$l.id_lang}"><img src="https://www.sifatusafwa.com/img/l/{$l.id_lang}.jpg" /></li>
        {/foreach}
    </script>
    <div class="form-group text-widget productName">
        <label for="product_detail_product_name">
            Product Name
        </label>
        {foreach $languages as $l}
            <input
                    type="text"
                    name="product_detail_product_name[{$l.id_lang}]"
                    class="form-control {if $l@first}active{/if} ""
                    value="{$product->name[{$l.id_lang}]}"
                    required="required"
            />
        {/foreach}

    </div>
    {if $combinations}
        <div class="form-group select-widget">
            <label class="" for="product_detail_combinations">
                Book variant
            </label>
            <select id="product_detail_combinations"
                    name="product_detail_combinations"
                    class="custom-select form-control"
                    required="required"
            >
                <option value="" disabled="disabled" selected="selected">Select a book variant</option>
                {foreach $combinations as $combination}
                    <option
                            data-price="{$combination.price|number_format:2:".":""}"
                            data-reference="{$combination.reference}"
                            data-name="{$combination.attribute_name}"
                            value="{$combination.id_product_attribute}"
                            {foreach $combinationsNames as $key => $names}
                                {foreach $names as $k => $name}
                                    {if $k == $combination.id_product_attribute}
                                        data-name-id_lang-{$key}="{$name}"
                                    {/if}
                                {/foreach}
                            {/foreach}
                    >
                        {$combination.attribute_name} - {$combination.price|number_format:2:".":""} €
                    </option>
                {/foreach}
            </select>
        </div>
    {/if}
    <div class="form-group text-widget productUrl">
        <label for="product_detail_url">
            Friendly URL
        </label>
        {foreach $languages as $l}
            <input
                type="text"
                name="product_detail_url[{$l.id_lang}]"
                class="form-control {if $l@first}active{/if} ""
                value="deals-{$product->link_rewrite[{$l.id_lang}]}"
                required="required"
            />
        {/foreach}
    </div>

    <div class="form-group text-widget">
        <label for="product_detail_reference">
            Product Reference
        </label>

        <input
                type="text"
                id="product_detail_reference"
                name="product_detail_reference"
                required="required"
                class="form-control"
                value="UZ-{$product->reference}"
        />
    </div>

    <div class="form-group text-widget">
        <label for="product_detail_stickers">
            Stickers
        </label>
        <select id="product_detail_stickers"
                name="product_detail_stickers"
                class="custom-select form-control"
                required="required"
        >
            <option value="" selected="selected">Select a sticker</option>
            {foreach $stickers as $sticker}
                {if $sticker.rate > 0}
                    <option
                            data-discount="{if $sticker.rate == 4}20{/if}{if $sticker.rate == 3}35{/if}{if $sticker.rate == 2}50{/if}{if $sticker.rate == 1}70{/if}"
                            value="{$sticker.id_sticker}"
                    >
                        {$sticker.name} {for $i=1 to $sticker.rate}★{/for}
                    </option>
                {/if}
            {/foreach}
        </select>
    </div>

    <div class="form-group text-widget">
        <label for="product_detail_quantity">
            Quantity
        </label>
        <input
                type="number"
                id="product_detail_quantity"
                name="product_detail_quantity"
                required="required"
                class="form-control"
                value="1"
        />
    </div>

    <div class="form-group text-widget">
        <label for="product_detail_discount">
            Discount
        </label>
        <div class="input-group">
            <input
                    type="text"
                    id="product_detail_discount"
                    name="product_detail_discount"
                    required="required"
                    class="form-control"
                    value=""
                    placeholder="Type discount in %"
            />
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
    </div>

    <input type="hidden" name="product_detail_original_id_product" value="{$product->id}" />
    <input type="hidden" name="product_detail_original_id_product_attribute" value="{$defaultIdProductAttribute}" />

    {foreach $languages as $l}
        <input
                type="hidden"
                name="product_detail_original_name[{$l.id_lang}]"
                value="{$product->name[{$l.id_lang}]}"
        />
    {/foreach}

    {foreach $languages as $l}
        <input
                type="hidden"
                name="product_detail_original_link_rewrite[{$l.id_lang}]"
                value="{$product->link_rewrite[{$l.id_lang}]}"
        />
    {/foreach}

    <div class="form-group">
        <button
                type="submit"
                id="productDealSubmit"
                name="productDealSubmit"
                class="btn-primary btn btn">
            Create new book deal
        </button>
    </div>
    <div class="form-group clogElement" style="display: none">
        <div class="clog"></div>
    </div>
</form>

