{*
 * Cart Reminder
 * 
 *    @category advertising_marketing
 *    @author    Timactive - Romain DE VERA
 *    @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 *    @version 1.0.0
 *    @license   Commercial license
 *
 *************************************
 **         CART REMINDER            *
 **          V 1.0.0                 *
 *************************************
 *  _____ _            ___       _   _           
 * |_   _(_)          / _ \     | | (_)          
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____ 
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *                                              
 * +
 * + Languages: EN, FR
 * + PS version: 1.5,1.6
 *}
<div class="ta-module-admin-nav">
  <nav>
    <ul>
      <li class="{if $ta_gc_tab_select=='giftcardorder'}tab-current {/if}purple" data-tab-select='giftcardorder'><a class="flaticon flaticon-cart" href="{$link->getAdminLink('AdminGiftCardOrder')|escape:'quotes':'UTF-8'}"><span>{l s='Gift Card Order' mod='giftcard'}</span></a></li>
      <li class="{if $ta_gc_tab_select=='giftcardproduct'}tab-current {/if}purple" data-tab-select='giftcardproduct'><a class="flaticon flaticon-price" href="{$link->getAdminLink('AdminGiftCard')|escape:'quotes':'UTF-8'}"><span>{l s='Gift Card Product' mod='giftcard'}</span></a></li>
      <li class="{if $ta_gc_tab_select=='giftcardtemplate'}tab-current {/if}purple" data-tab-select='giftcardtemplate'><a class="flaticon flaticon-picture" href="{$link->getAdminLink('AdminGiftCardTemplate')|escape:'quotes':'UTF-8'}"><span>{l s='Gift Card Template' mod='giftcard'}</span></a></li>
      <li class="{if $ta_gc_tab_select=='settings'}tab-current {/if}apple" data-tab-select='settings'><a class="flaticon flaticon-tools" href="{$link->getAdminLink('AdminModules')|escape:'quotes':'UTF-8'}&configure=giftcard&tab_select=settings"><span>{l s='Settings' mod='giftcard'}</span></a></li>
    </ul>
  </nav>
</div>