{**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 *}
<div class="row extra-faq-block">
     <input type="hidden" name="id_product" value="{$id_product|intval}">
    <input type="hidden" name="action_type" value="{$action_type}">
    <input type="hidden" name="row_id" value="{$id_row}">
    <div class="col-md-12 form-group">
        <label class="form-control-label col-lg-2">{l s='Active?' mod='hifaq'}</label>
        <div class="col-lg-6">
            <div class="radio">
                <label class="">
                    <input type="radio" id="active_on" name="active" value="1" {if $product_faq->active == '1' || !$product_faq->active} checked="checked"{/if}>
                    {l s='Yes' mod='hifaq'}
                </label>
                <label class="">
                    <input type="radio" id="active_off" name="active" value="0" {if $product_faq->active == '0'} checked="checked"{/if}>
                    {l s='No' mod='hifaq'}
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-12 form-group">
        <label class="form-control-label col-lg-2">{l s='Title' mod='hifaq'}</label>
        {foreach from=$languages item=language}
            <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultlanguage} style="display:none;"{/if}>
                <div class="col-lg-7">
                    <input type="text" id="title_{$language.id_lang|intval}" name="title_{$language.id_lang|intval}" required="required" class="faq-title form-control" value="{$product_faq->title[{$language.id_lang|intval}]}">
                </div>
                {if $languages|count > 1}
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                            {$language.iso_code}
                            <i class="icon-caret-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language}
                                <li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
    <div class="col-md-12 form-group">
        <label class="form-control-label col-lg-2">{l s='Question' mod='hifaq'}</label>
        {foreach from=$languages item=language}
            <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultlanguage} style="display:none;"{/if}>
                <div class="col-lg-7">
                    <input type="text" id="question_{$language.id_lang|intval}" name="question_{$language.id_lang|intval}" required="required" class="faq-question form-control" value="{$product_faq->question[{$language.id_lang|intval}]}">
                </div>
                {if $languages|count > 1}
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                            {$language.iso_code}
                            <i class="icon-caret-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language}
                                <li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
    <div class="col-md-12 form-group">
        <label class="form-control-label col-lg-2">{l s='Answer' mod='hifaq'}</label>
        {foreach from=$languages item=language}
            <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultlanguage} style="display:none;"{/if}>
                <div class="col-lg-7">
                    <textarea id="answer_{$language.id_lang}" name="answer_{$language.id_lang}" class="faq-answer autoload_rte form-control">{$product_faq->answer[{$language.id_lang|intval}]}</textarea>
                </div>
                {if $languages|count > 1}
                    <div class="col-lg-2" {if $psv >= 1.7} style="padding-left: 135px;" {/if}>
                        <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                            {$language.iso_code}
                            <i class="icon-caret-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language}
                                <li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
    <div class="col-md-12 form-group">
        <a href="#" class="btn btn-default pull-right submit_list_save">
            {if $psv >= '1.7'}
                <i class="material-icons">save</i>
            {else}
                <i class="process-icon-save"></i>
            {/if}
            {l s='SAVE' mod='hifaq'}
        </a>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        tinySetup({
            editor_selector :"autoload_rte"
        });
    });
</script>
