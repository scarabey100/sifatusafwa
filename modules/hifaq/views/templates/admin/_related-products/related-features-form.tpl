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
 <form accept="/" method="post" class="hifaq-related-features-form">
    <div class="panel">
        <h3><i class="icon-cogs"></i> {l s='Add related Feature' mod='hifaq'}</h3>
        <div class="form-wrapper" style="max-width:95%;">
            <div class="form-group">
                <div class="col-lg-6">
                    <label>
                        {l s='Feature' mod='hifaq'}
                        <div class="hi-module-whats-this">
                            <a href="#" data-doc="productFeatures">{l s='What\'s this?' mod='hifaq'}</a>
                        </div>
                    </label>
                    <select class="faq-related-feature" name="faq-related-feature[]">
                        <option value="0">{l s='Choose a feature' mod='hifaq'}</option>
                        {foreach $features as $feature}
                            <option value="{$feature.id_feature|intval}">{$feature.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-6">
                    <label>&nbsp;</label>
                    <select class="faq-related-feature-value" name="faq-related-feature-value[]" disabled>
                        <option value="0">{l s='Choose a value' mod='hifaq'}</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-12">
                    <button type="button" class="btn btn-primary" id="hifaq-add-new-feature" data-id-faq="{$id_faq|intval}">
                        <i class="icon-plus-circle"></i> 
                        &nbsp;{l s='Add a feature' mod='hifaq'}
                    </button>
                </div>
            </div>    
        </div>
    </div>
</form>