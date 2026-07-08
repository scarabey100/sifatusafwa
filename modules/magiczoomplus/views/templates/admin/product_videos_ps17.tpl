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

<div class="col-md-12">
    <div class="row">
        <div class="col-md-9">
            <fieldset class="form-group">
                <label class="form-control-label">
                    {l s='Product Videos' mod='magiczoomplus'}
                    <span class="help-box" data-toggle="popover" data-content="{l s='Provide links to video separated by a space or new line.' mod='magiczoomplus'}" ></span>
                </label>
                {if isset($magiczoomplus_invalid_urls)}
                <div class="row">
                    <div class="col-md-9">
                        <div class="alert alert-warning" role="alert">
                            <i class="material-icons">help</i>
                            <p class="alert-text">
                                {l s='"Product Videos" value contains incorrect urls:' mod='magiczoomplus'}<br><br>
                                <ul>
                                    {foreach from=$magiczoomplus_invalid_urls item=url}
                                        <li>{$url|escape:'html':'UTF-8'}</li>
                                    {/foreach}
                                </ul>
                            </p>
                        </div>
                    </div>
                </div>
                {/if}
                <div class="translations tabbable" id="form_stepX_product_videos">
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <textarea name="magiczoomplus_video" placeholder="" class="form-control" rows="10" cols="45">{if isset($magiczoomplus_textarea)}{$magiczoomplus_textarea|escape:'html':'UTF-8'}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>
