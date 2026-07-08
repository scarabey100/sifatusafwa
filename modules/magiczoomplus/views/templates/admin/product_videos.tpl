{**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*}

<!-- MagicZoomPlus -->
{if $legacy_template}
<style>
#magiczoomplus-template {
    display: none;
}
.magiczoomplus-settings {
    width: 100%;
}
.magiczoomplus-settings td {
    text-align: center;
}
.magiczoomplus-settings .error {
    text-align: left;
}
.magiczoomplus-settings fieldset {
    border: none;
}
.magiczoomplus-settings textarea {
    margin: 0;
    width: 99% !important;
}
.magiczoomplus-settings p {
    text-align: left;
}
</style>
<div id="magiczoomplus-template">
    <table class="magiczoomplus-settings" cellpadding="5">
        {if isset($magiczoomplus_invalid_urls)}
        <tr>
            <td>
                <div class="error">
                    <img src="../img/admin/error2.png" />
                    {l s='"Product Videos" value contains incorrect urls:' mod='magiczoomplus'}
                    <ol>
                        {foreach from=$magiczoomplus_invalid_urls item=url}
                            <li>{$url|escape:'html':'UTF-8'}</li>
                        {/foreach}
                    </ol>
                </div>
            </td>
        </tr>
        {/if}
        <tr>
            <td>
                <fieldset>
                    <legend>Product Videos</legend>
                    <textarea name="magiczoomplus_video" rows="10" cols="45">{if isset($magiczoomplus_textarea)}{$magiczoomplus_textarea|escape:'html':'UTF-8'}{/if}</textarea>
                    <p>{l s='Provide links to video separated by a space or new line' mod='magiczoomplus'}</p>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" value="{l s='Save' mod='magiczoomplus'}" name="submitAddproduct" class="button" />&nbsp;
                <input type="submit" value="{l s='Save and stay' mod='magiczoomplus'}" name="submitAddproductAndStay" class="button" />
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
//<![CDATA[
    var mtTabNumber = $('.tab-page').length + 1;
    $('.tab-page:last').after(
        '<div class="tab-page" id="step'+mtTabNumber+'"><h4 class="tab">'+mtTabNumber+'. MagicZoomPlus</h4>'+
        $('#magiczoomplus-template').html()+
        '</div>'
    );
//]]>
</script>
{else}
<style>
.magiczoomplus-settings .error {
    overflow: hidden;
}
.magiczoomplus-settings fieldset {
    border: none;
}
.magiczoomplus-settings textarea {
    margin: 0;
    width: 99% !important;
    resize: vertical;
}
</style>
<div class="magiczoomplus-settings panel product-tab">
    <input type="hidden" name="submitted_tabs[]" value="magiczoomplus" />
    {if $p16x_template}
    <h3>{l s='Product Videos' mod='magiczoomplus'}</h3>
    {else}
    <h4>{l s='Product Videos' mod='magiczoomplus'}</h4>
    {/if}
    <div class="separation"></div>
    {if isset($magiczoomplus_invalid_urls)}
    {if $p16x_template}
    <div class="bootstrap">
        <div class="module_error alert alert-danger" >
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {l s='"Product Videos" value contains incorrect urls:' mod='magiczoomplus'}
            <ul>
                {foreach from=$magiczoomplus_invalid_urls item=url}
                    <li>{$url|escape:'html':'UTF-8'}</li>
                {/foreach}
            </ul>
        </div>
    </div>
    {else}
    <div class="error">
        <span style="float:right">
            <a id="hideError" href="#"><img alt="X" src="../img/admin/close.png" /></a>
        </span>
        {l s='"Product Videos" value contains incorrect urls:' mod='magiczoomplus'}
        <br/>
        <ol>
            {foreach from=$magiczoomplus_invalid_urls item=url}
                <li>{$url|escape:'html':'UTF-8'}</li>
            {/foreach}
        </ol>
    </div>
    {/if}
    {/if}
    <fieldset>
        <textarea name="magiczoomplus_video" rows="10">{if isset($magiczoomplus_textarea)}{$magiczoomplus_textarea|escape:'html':'UTF-8'}{/if}</textarea>
        <p class="{if $p16x_template}help-block{else}preference_description{/if}">{l s='Provide links to video separated by a space or new line' mod='magiczoomplus'}</p>
    </fieldset>
    {if $p16x_template}
    <div class="panel-footer">
        <button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save' mod='magiczoomplus'}</button>
        <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save and stay' mod='magiczoomplus'}</button>
    </div>
    {/if}
</div>
<div class="clear">&nbsp;</div>
{/if}
<!-- /MagicZoomPlus -->
