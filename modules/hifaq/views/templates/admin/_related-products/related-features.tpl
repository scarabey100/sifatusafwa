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
 <div class="panel hi-faq-related-features">
    <h3><i class="icon-list"></i> {l s='Related Features' mod='hifaq'}</h3>
    {if $features}
        <div class="form-group">
            <ul class="hi-faq-features-list col-sm-12">
                {foreach from=$features item=feature}
                    <li class="hi-faq-features-item">
                        <div class="hi-faq-feature-name-value">
                            <b>{$feature.featureName|escape:'html':'UTF-8'}:</b> <span>{$feature.featureValue}</span>
                        </div>
                        <div class="hi-faq-feature-actions">
                            <button class="btn hi-faq-delete-related-feature" data-id-faq-feature="{$feature.id_hifaqrelatedproductfeature}" data-id-faq="{$id_faq|intval}">
                                <i class="icon-trash"></i>
                            </button>
                        </div>
                    </li>
                {/foreach}
            </ul>
        </div>
    {else}
        <div class="list-empty">
            <div class="list-empty-msg">
                <i class="icon-warning-sign list-empty-icon"></i>
                {l s='No records found' mod='hifaq'}
            </div>
        </div>
    {/if}
</div>