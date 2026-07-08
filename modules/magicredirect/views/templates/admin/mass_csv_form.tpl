{**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2023 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 *}

 <div class="panel">
	<p><strong>{l s='Regular expression are checked every hour per URL, it\'s a powerfull tool but you shouldn\'t stack too much of them for good performances.' mod='magicredirect'}</strong></p>
	<p>{l s='Be careful with regular expression, use it if you know what you are doing. This is a powerful tool but must be used with care. Do no hesitate to contact a developper for this, it uses PHP preg_replace function.' mod='magicredirect'}</p>
	<p><strong>{l s='Classic rules are prefered to regular expressions.' mod='magicredirect'}</strong></p>
</div>

<div class="panel">
	<h3>{l s='Massive URL redirects by CSV :' mod='magicredirect'}</h3>
	<p>{l s='Format your CSV with UTF-8 &' mod='magicredirect'} "{$separator}" <br/>{l s='(URL example : /example.html, don\'t add http://www.domain.tld/lang/ )' mod='magicredirect'}
	<br/><a href="/modules/magicredirect/assets/redirections.csv">{l s='See the sample file here' mod='magicredirect'}</a>
	</p>
	<form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" enctype="multipart/form-data">
		<label from="csv_file">{l s='Select a file' mod='magicredirect'}</label>
		<input type="file" name="csv_file" id="csv_file"> 
		<hr/>
		<button type="submit" name="mass_csv_form_submit" value="mass_csv_form_submit" class="button btn btn-default">{l s='Upload & Add' mod='magicredirect'}</button>
	</form>
</div>