<h2>SQL</h2>
{$sqlQuery}

<h2>configValue</h2>
<div class="gridConfigValues">
    {assign var="total" value=$configValues|@count}
    {math equation="ceil(x / y)" x=$total y=3 assign="perColumn"}
    {assign var="counter" value=0}
    {foreach from=$configValues key=key item=value name=configValuesLoop}
        {if $smarty.foreach.configValuesLoop.iteration % $perColumn == 1}
        <div>
            <table border="1" class="tableConfigValues">
            {/if}
            <tr>
                <td>{$key|escape:'htmlall':'UTF-8'}</td>
                <td>{$value|escape:'htmlall':'UTF-8'}</td>
            </tr>
            {if $smarty.foreach.configValuesLoop.iteration % $perColumn == 0 || $smarty.foreach.configValuesLoop.last}
            </table>
        </div>
    {/if}
{/foreach}
</div>

<h2>Orders</h2>
{$orders}
{$orderTotal}