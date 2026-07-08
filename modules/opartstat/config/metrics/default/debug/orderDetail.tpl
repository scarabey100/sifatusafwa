<table border="1" class="orderTable">
    <thead>
        <tr>
            <th>Id order</th>
            <th>reference</th>
            <th>date</th>
            <th>current_state</th>
            <th>total_paid_tax_excl</th>
            <th>total_shipping_tax_excl</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{$order[0]['id_order']|escape:'htmlall':'UTF-8'}</td>
            <td>{$order[0]['reference']|escape:'htmlall':'UTF-8'}</td>
            <td>{$order[0]['orderDate']|escape:'htmlall':'UTF-8'}</td>
            <td>{$order[0]['current_state']|escape:'htmlall':'UTF-8'}</td>
            <td>{$order[0]['total_paid_tax_excl']|escape:'htmlall':'UTF-8'}</td>
            <td>{$order[0]['total_shipping_tax_excl']|escape:'htmlall':'UTF-8'}</td>
        </tr>
        <tr>
            <td colspan="6">
                <table border="1" class="orderDetailTable">
                    <thead>
                        <tr>
                            <th>Order Detail ID</th>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Total Price (Tax Excl.)</th>
                            <th>product_quantity</th>
                            <th>product_quantity_refunded</th>
                            <th>Product purchase Price</th>
                            <th>total_product_purchase_cost</th>
                            <th>Total refunded tax excl</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$order key=k item=detail}
                            <tr>
                                <td>{$detail.id_order_detail|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.product_id|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.product_name|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.total_price_tax_excl|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.product_quantity|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.product_quantity_refunded|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.product_purchase_price|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.total_product_purchase_cost|escape:'htmlall':'UTF-8'}</td>
                                <td>{$detail.total_refunded_tax_excl|escape:'htmlall':'UTF-8'}</td>
                            </tr>
                        {/foreach}
                        <tr class="orderDetailsTotals">
                            <td colspan="3">Totals</td>
                            <td>Total Price (Tax Excl.)</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>total_product_purchase_cost</td>
                            <td>Total refunded tax excl</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td>{$detailtotals.total_price_tax_excl|escape:'htmlall':'UTF-8'}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>{$detailtotals.total_product_purchase_cost|escape:'htmlall':'UTF-8'}</td>
                            <td>{$detailtotals.total_refunded_tax_excl|escape:'htmlall':'UTF-8'}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
<table>