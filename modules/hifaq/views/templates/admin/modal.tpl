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
<div id="modal_container">
    <div id="hi-presta-module-modal" class="modal fade hi-faq-modal" role="dialog">
        <div class="modal-dialog modal-lg">
            {if $extra_page}
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title module-modal-title">{l s='FAQ form' mod='hifaq'}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="content"></div>
                    </div>
                </div>
            {else}
                <div class="content"></div>
            {/if}
        </div>
    </div>
</div>