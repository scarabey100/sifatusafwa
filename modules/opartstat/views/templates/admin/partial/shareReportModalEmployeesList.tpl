<ul>
    {foreach from=$employeesList item=employee}
        <select class="employeesSelectRights" data-guest-id="{$employee['id']|escape:'htmlall':'UTF-8'}">
            <option value="0" {if $employee['rights'] ==0}selected{/if}>{l s="Not shared" mod='opartstat'}</option>
            <option value="1" {if $employee['rights'] ==1}selected{/if}>{l s="Read-only" mod='opartstat'}</option>
            <option value="2" {if $employee['rights'] ==2}selected{/if}>{l s="Edit" mod='opartstat'}</option>
        </select>
        <span class="employeeName">{$employee['name']|escape:'htmlall':'UTF-8'}</span>
        <br />
    {/foreach}
</ul>
<script type="text/javascript">
    $('.employeesSelectRights').on('change', function() {
        updateEmployeeRights($(this));
    })
</script>