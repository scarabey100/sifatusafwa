# DMU Campagnes de Promotion (par [Dream me up](http://www.dream-me-up.fr))

```
   .--.
   |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
   |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
   '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
        w w w . d r e a m - m e - u p . f r       '

  @author    Dream me up <prestashop@dream-me-up.fr>
  @copyright 2007 - 2024 Dream me up
  @license   All Rights Reserved

```
## changelog 3.1.0

* Suppression des lignes de promotion lorsque le prix spécifique a été supprimé directement
* Suppression de la compatibilité PrestaShop 1.6 et inférieur

## changelog 3.0.2

* Correction de la compatibilité pour PrestaShop v8

## changelog 3.0.1

* Correction d'un problème sur les traductions

## changelog 3.0.0

* Ajout de la compatibilité pour PrestaShop v8

## changelog 2.5.3

* Corrections du problème de duplication lorsqu'un prix spécifique a été supprimé

## changelog 2.5.2

* Corrections des traductions anglaises qui étaient remplacées par des traductions françaises

## changelog 2.5.1

* Correction d'un bug important qui pouvait provoquer la création d'une ligne de prix spécifique sur un id_product = 0

## changelog 2.5.0

* Ajout de la fonction pour n'appliquer la promotion que sur les produits en stock

## changelog 2.4.1

* Correction du calcul de stock

## changelog 2.4.0

* Correction de traductions manquantes sur 1.5
* Correction de la sélection multiple sous 1.5
* Amélioration de la modification de promos en masse, notamment pour les gros catalogues

## changelog 2.3.1

* Correction d'un problème de compatibilité avec les dernières version de PS 1.7 (fonction setMedia)

## changelog 2.3.0

* Correction de la gestion du prix d'achat en récupérant le prix d'achat au niveau du fournisseur quand celui-ci existe

## changelog 2.2.1

* Correction d'un bug sur le calcul des prix quand la règle de TVA n'existait pas ou pas de TVA

## changelog 2.2.0

* Amélioration du multi-boutique

## changelog 2.1.9

* Remplacement de la fonction PrestaShop Product::getProductName() qui fonctionne mal

## changelog 2.1.8

* Ajout du tri par Prix

## changelog 2.1.7

* Réparation du bug de duplication

## changelog 2.1.6

* Modification pour compatibilité PrestaShop 1.7.x.x

## changelog 2.1.5

* Ajout des nouvelles colonnes en BDD : id_currency, id_country et id_group

## changelog 2.1.4

* Ajout de Restrictions selon : Groupe, Pays, Devise

## changelog 2.1.3

* Amélioration du code
* Suppressions de fonctions « deprecated »
* Corrections de Bugs

## changelog 2.1.2

* Ajout du filtre : Stock minimum
* Ajout de la référence produit

## changelog 2.1.1

* Ajout de la conversion " virgule => point "
* Suppression de l'Ancien Controller Admin lors d'une mise à jour
* Suppression de la différence de gestion des prix HT/TTC si version >= 1.6.0.11

## changelog 2.1.0 bis

* Ajout de la différence de gestion des prix HT/TTC si version < 1.6.0.11

## changelog 2.1.0

* Nouvelle version compatible 1.6.x.x
* Réparation de plusieurs bugs