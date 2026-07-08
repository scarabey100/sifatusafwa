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
{extends file="helpers/form/form.tpl"}

{block name="script"}
    var ps_force_friendly_product = false;
{/block}

{block name="input" prepend}
    <script type="text/javascript">
        var PS_ALLOW_ACCENTED_CHARS_URL;
        {if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
            PS_ALLOW_ACCENTED_CHARS_URL = 1;
        {else}
            PS_ALLOW_ACCENTED_CHARS_URL = 0;
        {/if}
    </script>
{/block}

{block name="label"}
    {if isset($input.label)}
        <label class="control-label col-lg-3{if isset($input.required) && $input.required && $input.type != 'radio'} required{/if}">
            {if isset($input.hint)}
            <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{if is_array($input.hint)}
                        {foreach $input.hint as $hint}
                            {if is_array($hint)}
                                {$hint.text|escape:'html':'UTF-8'}
                            {else}
                                {$hint|escape:'html':'UTF-8'}
                            {/if}
                        {/foreach}
                    {else}
                        {$input.hint|escape:'html':'UTF-8'}
                    {/if}">
            {/if}
            {$input.label|escape:'html':'UTF-8'}
            {if isset($input.hint)}
            </span>
            {/if}

            {if isset($input.doc) && $input.doc}
                <div class="hi-module-whats-this">
                    <a href="#" data-doc="{$input.doc|escape:'html':'UTF-8'}">{if isset($input.docTitle) && $input.docTitle}{$input.docTitle|escape:'html':'UTF-8'}{else}{l s='What\'s this?' mod='hifaq'}{/if}</a>
                </div>
            {/if}
        </label>
    {/if}
{/block}