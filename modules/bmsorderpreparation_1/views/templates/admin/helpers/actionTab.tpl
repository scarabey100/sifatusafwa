{**
 * 2007-2022 Boostmyshop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 Boostmyshop
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
 <div id="carrier_wizard" class="row">

			<ul class="steps anchor" style="padding: 0 5px 0 7px">
				{foreach from=$links key=k item=link name=link}
				<li style="float:left;width:{100/$smarty.foreach.link.total}%" class="{if $key_actif == $k}selected{else}done{/if}">
					<a href="{$link.href}" class="{if $key_actif == $k}selected{else}done{/if}" isdone="1" rel="{$smarty.foreach.link.index+1}">
						<span class="stepNumber">{$smarty.foreach.link.index+1}</span>
						<span class="stepDesc">
							{$link.desc}<br>
													</span>
						<span class="chevron"></span>
					</a>
				</li>
				{/foreach}
			</ul>

</div>