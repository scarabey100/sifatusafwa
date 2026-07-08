{**
* 2012 - 2022 HiPresta
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License 3.0 (AFL-3.0).
* It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
*
* @author    HiPresta <support@hipresta.com>
* @copyright HiPresta 2022
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
* @link      https://hipresta.com
*}

<nav id="hmd-modal" class="col-lg-3 hmd-sidebar-right hmd-sidebar-animate text-sm-center">
    <div class="hmd-container">
        <div class="hmd-header">
            <a href="#" class="hmd-dismiss-modal">×</a>
            <h2>{l s='Help' mod='hifaq'}</h2>
        </div>
        <div class="hmd-content">
            <div class="hmd-item" data-doc="faqIcons">
                <h2>{l s='Icons Type' mod='hifaq'}</h2>

                <div class="hmd-item-content">
                    <p>{l s='Choose the icon set that best matches your theme: "Material" or "Font Awesome". Different PrestaShop themes utilize different icon sets, so selecting the appropriate one ensures seamless integration with your theme\'s design.' mod='hifaq'}</p>
                </div>
            </div>

            <div class="hmd-item" data-doc="cleanDb">
                <h2>{l s='Clean Database when module uninstalled' mod='hifaq'}</h2>

                <div class="hmd-item-content">
                    <p>{l s='We recommend to keep this option disabled. If you enable it, after uninstalling the module all data related to this module will be deleted from database.' mod='hifaq'}</p>
                    <p>{l s='This option can be used if for some reason you don\'t want to use the module anymore or you need to reset all settings to defaults.' mod='hifaq'}</p>
                </div>
            </div>

            <div class="hmd-item" data-doc="productSearch">
                <h2>{l s='Search Product' mod='hifaq'}</h2>

                <div class="hmd-item-content">
                    <p>{l s='You can input a few characters from the product name and then choose the product from the list to link a FAQ to it.' mod='hifaq'}</p>
                    <p>{l s='Alternatively, you can enter the product ID directly and then click the "Add" button.' mod='hifaq'}</p>
                    <p><b>{l s='Kindly note: If the search results don\'t include newly added products, you can navigate to Shop Parameters -> Search tab and click on the "Add missing products to the index" button.' mod='hifaq'}</b></p>
                </div>
            </div>
            
            <div class="hmd-item" data-doc="productFeatures">
                <h2>{l s='Add Related Features' mod='hifaq'}</h2>

                <div class="hmd-item-content">
                    <p>{l s='Here, you can link product features to the FAQ, making it visible on all product pages with the selected features' mod='hifaq'}</p>
                </div>
            </div>
        </div>

        <div class="hmd-footer">
            {l s='Feel free to [1]Contact Us[/1] if you need further assistance.' tags=["<a href='{$contactLink}' target='_blank'>"] mod='hifaq'}
        </div>
    </div>
</nav>