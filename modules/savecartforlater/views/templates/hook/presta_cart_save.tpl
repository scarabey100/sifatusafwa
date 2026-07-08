{**
* 2008-2024 Prestaworld
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one website.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
*
* DISCLAIMER
*
* Do not alter or add/update to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    prestaworld
* @copyright 2008-2024 Prestaworld
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
* International Registered Trademark & Property of prestaworld
*}

<a
	title                       = "{l s='Save for later' mod='savecartforlater'}"
	class                       = "save-to-cart"
	rel                         = "nofollow"
	href                        = "javascript:void(0);"
	data-link-action            = "save-to-cart"
	data-id-product             = "{$product.id_product|escape:'htmlall':'UTF-8'}"
	data-id-product-attribute   = "{$product.id_product_attribute|escape:'htmlall':'UTF-8'}"
	data-id-customization   	  = "{$product.id_customization|escape:'htmlall':'UTF-8'}">
	<i class="material-icons">av_timer</i>
</a>
<div class="presta-loader hidecontent">
	<img src="{$modules_dir|escape:'htmlall':'UTF-8'}savecartforlater/views/img/loading.gif" width="20px;"/>
</div>
