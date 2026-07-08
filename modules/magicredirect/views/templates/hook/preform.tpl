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
 	<div class="bootstrap panel">
		
		<div style="text-align:center;"><img src="{$logo_img|escape:'htmlall':'UTF-8'}" />
			<h2>{$displayName|escape:'htmlall':'UTF-8'}</h2>
			<br>
			<h3>{$description|escape:'htmlall':'UTF-8'}</h3>
		</div>
		
		{if Shop::isFeatureActive()}

			<p class='alert alert-info'>{l s='Please note that redirections are added in function of which shop you selected. When in Group mode/All shops mode, all redirects are added separately for each shop.' mod='magicredirect'}</p>

		{/if}

		<p>{l s='Redirects are multilang ready. So you don\'t have to include "/en" or any language indicator in URL\'s.' mod='magicredirect'}</p>

		<p>{l s='You can manage them here :' mod='magicredirect'} <a class="btn-default btn" href="{$redir_admin_link|escape:'htmlall':'UTF-8'}"> > {l s='Redirect Tool' mod='magicredirect'} < </a></p>

	</div>