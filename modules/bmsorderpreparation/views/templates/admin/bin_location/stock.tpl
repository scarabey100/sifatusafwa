<div id="custom-alert"></div>
<table border="0" width="100%">
    <tr>
        <td>
            <input
                    type="text"
                    id="stock_{$id_product|escape:'htmlall':'UTF-8'}_{$id_attribute|escape:'htmlall':'UTF-8'}"
                    data-product-id="{$id_product|escape:'htmlall':'UTF-8'}"
                    data-attribute-id="{$id_attribute|escape:'htmlall':'UTF-8'}"
                    data-field="stock"
                    onchange="saveStock(this);"
                    value="{$stock|escape:'htmlall':'UTF-8'}"
                    size="5"
                    >
        </td>
    </tr>
</table>
