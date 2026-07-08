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
<div class="hi-faq-feedback-block {if isset($feedbackPosition) && $feedbackPosition == 2}hi-faq-feedback-block-static{/if}" data-id-faq="{$idFaq|intval}">
    <div class="hi-faq-feedback-wrapper">
        <div class="hi-faq-feedback-block-title-block">
            <div class="hi-faq-feedback-block-title">
                {l s='Does this answer your question?' mod='hifaq'}
            </div>
            <div class="hi-faq-feedback-dismiss">
                <i class="hi-faq-feedback-dismiss-icon"></i>
            </div>
        </div>
        <div class="hi-faq-feedback-block-actions">
            <div class="hi-faq-feedback-button hi-faq-feedback-good">
                <i class="hi-faq-feedback-happy-icon"></i>
                {l s='Yes' mod='hifaq'} {if isset($faq.goodFeedbacksCount)}({$faq.goodFeedbacksCount|intval}){/if}
            </div>
            <div class="hi-faq-feedback-button hi-faq-feedback-sad">
                <i class="hi-faq-feedback-sad-icon"></i>
                {l s='No' mod='hifaq'} {if isset($faq.badFeedbacksCount)}({$faq.badFeedbacksCount|intval}){/if}
            </div>
        </div>
    </div>
    <div class="hi-faq-feedback-form-wrapper hi-module-hide">
        <div class="hi-faq-feedback-block-title-block">
            <div class="hi-faq-feedback-block-title">
                {l s='Sorry about that' mod='hifaq'}
            </div>
            <div class="hi-faq-feedback-dismiss">
                <i class="hi-faq-feedback-dismiss-icon"></i>
            </div>
        </div>
        <div class="hi-faq-how-improvel">{l s='How can we improve it?' mod='hifaq'}</div>

        <form>
            <textarea class="hi-faq-comment-area" name="hi-faq-comment-area"></textarea>
            <div class="hi-faq-feedback-button hi-faq-feedback-comment">
                <i class="hi-faq-feedback-happy-icon"></i>
                {l s='Submit' mod='hifaq'}
            </div>
        </form>
    </div>

    <div class="hi-faq-feedback-success-wrapper hi-module-hide">
        <div class="hi-faq-feedback-success-icon-block">
            <i class="hi-faq-feedback-success-icon"></i>
        </div>
        <div class="hi-faq-feedback-block-title-block">
            <div class="hi-faq-feedback-block-title">
                {l s='Thanks' mod='hifaq'}
            </div>

            <div class="hi-faq-feedback-success-message">
                {l s='Your feedback helps improve this answer for everyone.' mod='hifaq'}
            </div>
        </div>

        <div class="hi-faq-feedback-dismiss">
            <i class="hi-faq-feedback-dismiss-icon"></i>
        </div>
    </div>
</div>