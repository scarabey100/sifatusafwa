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
	<h4>{l s='Advanced usage (for developpers)' mod='magicredirect'}</h4>
	<p>{l s='You can use regex, but take care to escape any character used.' mod='magicredirect'}</p>
	<p>{l s='Regex are not multilang, to permit you to redirect any lang to another.' mod='magicredirect'}</p>
	<p>{l s='For example you can use (to redirect any language) :' mod='magicredirect'}</p>
	<p> - {l s='as the old url.' mod='magicredirect'} : \/en\/(?.*)</p>
	<p> - {l s='as the new url.' mod='magicredirect'} : /fr/$1</p>
</div>

 <div><small>{l s='Module by' mod='magicredirect'} Agence Malttt</small></div>