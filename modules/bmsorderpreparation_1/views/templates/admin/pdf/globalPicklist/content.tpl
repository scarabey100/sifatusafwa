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
 
<table style="border:1px solid black;padding-top:2px;width: 100%">

<tr>
	<td align="center" style="font-size:200%;" width="15%">
		{$quantities|escape:'htmlall':'UTF-8'}
	</td>
	<td  align="center" width="10%">
		<img src="{$image}" width="50px" height="50px"/>	</td>
	<td align="center" width="50%">
		
		{$reference|escape:'htmlall':'UTF-8'}{$codeBarre|escape:'htmlall':'UTF-8'}
		<br/>
		{$nom|escape:'htmlall':'UTF-8'}
	</td>
	<td align="center" width="25%">
		{$location|escape:'htmlall':'UTF-8'}
	</td>
</tr>
</table>