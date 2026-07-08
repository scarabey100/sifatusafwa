{**
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 * @package    Magic Redirect
 * @copyright  Copyright (c) 2015-2023 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @author     Matthieu Deroubaix
 * @license    Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 * 
 **}
<section id="box" class="panel widget">
	<header><i class="icon-arrow-right"></i>&nbsp;{l s='404 & 301' mod='magicredirect'} </header>
	<ul class="data_list">
		<li>
			<span class="data_label">
				<a href="{$admin_link|escape:'htmlall':'UTF-8'}">{l s='You have' mod='magicredirect'} : {(int)Configuration::get('MGRT_COUNTER')|escape:'htmlall':'UTF-8'} {l s=' "not found" pages left to treat.' mod='magicredirect'}</a>
			</span>
		</li> 
	</ul>

</section>