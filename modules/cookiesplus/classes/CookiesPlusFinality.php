<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CookiesPlusFinality extends ObjectModel
{
    public $id_cookiesplus_finality;
    public $id_shop;
    public $active = 1;
    public $technical = 0;
    public $modules;
    public $name;
    public $description;
    public $js_script;
    public $body_code;
    public $js_not_script;
    public $position;
    public $date_add;
    public $date_upd;

    const NECESSARY_COOKIE = 1;
    const PREFERENCE_COOKIE = 2;
    const STATISTIC_COOKIE = 3;
    const MARKETING_COOKIE = 4;
    const UNCLASSIFIED_COOKIE = 5;
    const PERFORMANCE_COOKIE = 6;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'cookiesplus_finality',
        'primary' => 'id_cookiesplus_finality',
        'multilang' => true,
        'fields' => [
            'id_cookiesplus_finality' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'technical' => ['type' => self::TYPE_BOOL, 'required' => true],
            'modules' => ['type' => self::TYPE_STRING],
            'name' => ['type' => self::TYPE_STRING, 'required' => true, 'lang' => true],
            'description' => ['type' => self::TYPE_STRING, 'required' => true, 'lang' => true],
            'js_script' => ['type' => self::TYPE_HTML],
            'body_code' => ['type' => self::TYPE_HTML],
            'js_not_script' => ['type' => self::TYPE_HTML],
            'position' => ['type' => self::TYPE_INT],
            'date_add' => ['type' => self::TYPE_DATE, 'copy_post' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'copy_post' => false],
        ],
    ];

    public function add($autodate = true, $null_values = false)
    {
        $this->id_shop = ($this->id_shop) ?: Context::getContext()->shop->id;

        return parent::add($autodate, $null_values);
    }

    public static function getCookiesPlusFinalities($id_lang = null, $only_active = false, $include_technical_cookies = true)
    {
        $cacheKey = 'CookiesPlus::getCookiesPlusFinalities_' . $id_lang . '_' . $only_active . '_' . $include_technical_cookies . '_' . Context::getContext()->shop->id;

        if (!Cache::isStored($cacheKey)) {
            $query = 'SELECT *
                FROM ' . _DB_PREFIX_ . 'cookiesplus_finality cf '
                . 'LEFT JOIN ' . _DB_PREFIX_ . 'cookiesplus_finality_lang cfl on cf.`id_cookiesplus_finality` = cfl.`id_cookiesplus_finality`
                WHERE
                    cfl.`id_lang` = ' . ($id_lang ? (int) $id_lang : (int) Context::getContext()->language->id) .
                    ($only_active ? ' AND cf.`active` = 1' : '') .
                    ($include_technical_cookies ? '' : ' AND cf.`technical` = 0') .
                    ' AND cf.`id_shop` = ' . Context::getContext()->shop->id . '
                ORDER BY `position`;'
            ;

            $result = Db::getInstance()->executeS($query);
            Cache::store($cacheKey, $result);

            return is_array($result) ? $result : [];
        }

        return Cache::retrieve($cacheKey);
    }

    public static function getDefaultValues($cookiesPlusFinality)
    {
        $cookiesPlusFinalityDefaultValues = [
            CookiesPlusFinality::NECESSARY_COOKIE => [
                'name' => [
                    'en' => 'Necessary cookies',
                    'es' => 'Cookies necesarias',
                    'ag' => 'Cookies necesarias',
                    'cb' => 'Cookies necesarias',
                    'mx' => 'Cookies necesarias',
                    'fr' => 'Cookies nécessaires',
                    'qc' => 'Cookies nécessaires',
                    'pl' => 'Niezbędne',
                    'ro' => 'Necesare',
                    'pt' => 'Cookies necessários',
                    'br' => 'Cookies necessários',
                    'sk' => 'Potrebné',
                    'nl' => 'Noodzakelijk',
                    'de' => 'Notwendig',
                    'gr' => 'Αναγκαία',
                    'it' => 'Cookie necessari',
                    'sv' => 'Nödvändig',
                    'da' => 'Nødvendig',
                    'no' => 'Nødvendig',
                    'cs' => 'Nutné',
                    'hu' => 'Elengedhetetlen',
                    'si' => 'Zahtevano',
                ],
                'description' => [
                    'en' => 'Necessary cookies help make a website usable by enabling basic functions like page navigation and access to secure areas of the website. The website cannot function properly without these cookies.',
                    'es' => 'Las cookies necesarias ayudan a hacer una página web utilizable activando funciones básicas como la navegación en la página y el acceso a áreas seguras de la página web. La página web no puede funcionar adecuadamente sin estas cookies.',
                    'ag' => 'Las cookies necesarias ayudan a hacer una página web utilizable activando funciones básicas como la navegación en la página y el acceso a áreas seguras de la página web. La página web no puede funcionar adecuadamente sin estas cookies.',
                    'cb' => 'Las cookies necesarias ayudan a hacer una página web utilizable activando funciones básicas como la navegación en la página y el acceso a áreas seguras de la página web. La página web no puede funcionar adecuadamente sin estas cookies.',
                    'mx' => 'Las cookies necesarias ayudan a hacer una página web utilizable activando funciones básicas como la navegación en la página y el acceso a áreas seguras de la página web. La página web no puede funcionar adecuadamente sin estas cookies.',
                    'fr' => 'Les cookies nécessaires contribuent à rendre un site web utilisable en activant des fonctions de base comme la navigation de page et l\'accès aux zones sécurisées du site web. Le site web ne peut pas fonctionner correctement sans ces cookies.',
                    'qc' => 'Les cookies nécessaires contribuent à rendre un site web utilisable en activant des fonctions de base comme la navigation de page et l\'accès aux zones sécurisées du site web. Le site web ne peut pas fonctionner correctement sans ces cookies.',
                    'pl' => 'Niezbędne pliki cookie przyczyniają się do użyteczności strony poprzez umożliwianie podstawowych funkcji takich jak nawigacja na stronie i dostęp do bezpiecznych obszarów strony internetowej. Strona internetowa nie może funkcjonować poprawnie bez tych ciasteczek.',
                    'ro' => 'Cookie-urile necesare ajută la a face un site utilizabil prin activarea funcţiilor de bază, precum navigarea în pagină şi accesul la zonele securizate de pe site. Site-ul nu poate funcţiona corespunzător fără aceste cookie-uri.',
                    'pt' => 'Os cookies necessários ajudam a tornar um website útil, permitindo funções básicas, como a navegação e o acesso à página para proteger áreas do website. O website pode não funcionar corretamente sem estes cookies.',
                    'br' => 'Os cookies necessários ajudam a tornar um website útil, permitindo funções básicas, como a navegação e o acesso à página para proteger áreas do website. O website pode não funcionar corretamente sem estes cookies.',
                    'sk' => 'Potrebné súbory cookie pomáhajú vytvárať použiteľné webové stránky tak, že umožňujú základné funkcie, ako je navigácia stránky a prístup k chráneným oblastiam webových stránok. Webové stránky nemôžu riadne fungovať bez týchto súborov cookies.',
                    'nl' => 'Noodzakelijke cookies helpen een website bruikbaarder te maken, door basisfuncties als paginanavigatie en toegang tot beveiligde gedeelten van de website mogelijk te maken. Zonder deze cookies kan de website niet naar behoren werken.',
                    'de' => 'Notwendige Cookies helfen dabei, eine Webseite nutzbar zu machen, indem sie Grundfunktionen wie Seitennavigation und Zugriff auf sichere Bereiche der Webseite ermöglichen. Die Webseite kann ohne diese Cookies nicht richtig funktionieren.',
                    'gr' => 'Τα απαραίτητα cookies βοηθούν στο να γίνει χρηστική μία ιστοσελίδα, επιτρέποντας βασικές λειτουργίες όπως την πλοήγηση και την πρόσβαση σε ασφαλείς περιοχές της ιστοσελίδας. Η ιστοσελίδα δεν μπορεί να λειτουργήσει σωστά χωρίς αυτά τα cookies.',
                    'it' => 'I cookie necessari contribuiscono a rendere fruibile il sito web abilitandone funzionalità di base quali la navigazione sulle pagine e l\'accesso alle aree protette del sito. Il sito web non è in grado di funzionare correttamente senza questi cookie.',
                    'sv' => 'Nödvändiga cookies låter dig använda webbplatsen genom att aktivera grundläggande funktioner, såsom sidnavigering och åtkomst till säkra områden på webbplatsen. Webbplatsen fungerar inte korrekt utan dessa cookies.',
                    'da' => 'Nødvendige cookies hjælper med at gøre en hjemmeside brugbar ved at aktivere grundlæggende funktioner såsom side-navigation og adgang til sikre områder af hjemmesiden. Hjemmesiden kan ikke fungere ordentligt uden disse cookies.',
                    'no' => 'Nødvendige cookies bidra til å gjøre en nettside brukbart ved at grunnleggende funksjoner som side navigasjon og tilgang til sikre områder av nettstedet. Nettstedet kan ikke fungere optimalt uten disse informasjonskapslene.',
                    'cs' => 'Nutné cookies pomáhají, aby byla webová stránka použitelná tak, že umožní základní funkce jako navigace stránky a přístup k zabezpečeným sekcím webové stránky. Webová stránka nemůže správně fungovat bez těchto cookies.',
                    'hu' => 'Az elengedhetetlen sütik segítenek használhatóvá tenni a weboldalunkat azáltal, hogy engedélyeznek olyan alapvető funkciókat, mint az oldalon való navigáció és a weboldal biztonságos területeihez való hozzáférés. A weboldal ezen sütik nélkül nem tud megfelelően működni.',
                    'si' => 'Zahtevani piškotki naredijo spletno stran uporabno, saj omogočajo osnovne funkcije, kot so navigacija po strani in dostop do varnih območij spletne strani. Spletna stran brez teh piškotkov ne deluje pravilno.',
                ],
                'technical' => 1,
                'active' => 1,
                'modules' => [],
                'cookies' => CookiesPlusCookie::getDefaultValues(CookiesPlusFinality::NECESSARY_COOKIE),
                'position' => 0,
            ],
            CookiesPlusFinality::PREFERENCE_COOKIE => [
                'name' => [
                    'en' => 'Preference cookies',
                    'es' => 'Cookies de preferencias',
                    'ag' => 'Cookies de preferencias',
                    'cb' => 'Cookies de preferencias',
                    'mx' => 'Cookies de preferencias',
                    'fr' => 'Cookies de préférences',
                    'qc' => 'Cookies de préférences',
                    'pl' => 'Preferencje',
                    'ro' => 'Preferinţe',
                    'pt' => 'Cookies de preferência',
                    'br' => 'Cookies de preferência',
                    'sk' => 'Preferencie',
                    'nl' => 'Voorkeuren',
                    'de' => 'Präferenzen',
                    'gr' => 'Προτιμήσεις',
                    'it' => 'Cookie di preferenza',
                    'sv' => 'Inställningar',
                    'da' => 'Præferencer',
                    'no' => 'Egenskaper',
                    'cs' => 'Preferenční',
                    'hu' => 'Beállítások',
                    'si' => 'Nastavitve',
                ],
                'description' => [
                    'en' => 'Preference cookies enable a website to remember information that changes the way the website behaves or looks, like your preferred language or the region that you are in.',
                    'es' => 'Las cookies de preferencias permiten a la página web recordar información que cambia la forma en que la página se comporta o el aspecto que tiene, como su idioma preferido o la región en la que usted se encuentra.',
                    'ag' => 'Las cookies de preferencias permiten a la página web recordar información que cambia la forma en que la página se comporta o el aspecto que tiene, como su idioma preferido o la región en la que usted se encuentra.',
                    'cb' => 'Las cookies de preferencias permiten a la página web recordar información que cambia la forma en que la página se comporta o el aspecto que tiene, como su idioma preferido o la región en la que usted se encuentra.',
                    'mx' => 'Las cookies de preferencias permiten a la página web recordar información que cambia la forma en que la página se comporta o el aspecto que tiene, como su idioma preferido o la región en la que usted se encuentra.',
                    'fr' => 'Les cookies de préférences permettent à un site web de retenir des informations qui modifient la manière dont le site se comporte ou s’affiche, comme votre langue préférée ou la région dans laquelle vous vous situez.',
                    'qc' => 'Les cookies de préférences permettent à un site web de retenir des informations qui modifient la manière dont le site se comporte ou s’affiche, comme votre langue préférée ou la région dans laquelle vous vous situez.',
                    'pl' => 'Pliki cookie dotyczące preferencji umożliwiają stronie zapamiętanie informacji, które zmieniają wygląd lub funkcjonowanie strony, np. preferowany język lub region, w którym znajduje się użytkownik.',
                    'ro' => 'Cookie-urile de preferinţă permit unui site să îşi amintească informaţii care se modifică după modul în care se comportă sau arată site-ul, precum limba dvs. preferată sau regiunea în care vă aflaţi.',
                    'pt' => 'Os cookies de preferência permitem que um website memorize as informações que mudam o comportamento ou o aspeto do website, como o seu idioma preferido ou a região em que se você encontra.',
                    'br' => 'Os cookies de preferência permitem que um website memorize as informações que mudam o comportamento ou o aspeto do website, como o seu idioma preferido ou a região em que se você encontra.',
                    'sk' => 'Preferenčné súbory cookies umožňujú internetovej stránke zapamätať si informácie, ktoré zmenia spôsob, akým sa webová stránka chová alebo vyzerá, ako napr. váš preferovaný jazyk alebo región, v ktorom sa práve nachádzate.',
                    'nl' => 'Voorkeurscookies zorgen ervoor dat een website informatie kan onthouden die van invloed is op het gedrag en de vormgeving van de website, zoals de taal van uw voorkeur of de regio waar u woont.',
                    'de' => 'Präferenz-Cookies ermöglichen einer Webseite sich an Informationen zu erinnern, die die Art beeinflussen, wie sich eine Webseite verhält oder aussieht, wie z. B. Ihre bevorzugte Sprache oder die Region in der Sie sich befinden.',
                    'gr' => 'Τα cookies προτίμησης επιτρέπουν σε μια ιστοσελίδα να θυμάται πληροφορίες που αλλάζουν τον τρόπο που συμπεριφέρεται η ιστοσελίδα ή την εμφάνισή της, όπως την προτιμώμενη γλώσσα ή την περιοχή στην οποία βρίσκεστε',
                    'it' => 'I cookie di preferenza consentono al sito web di memorizzare informazioni che ne influenzano il comportamento o l\'aspetto, quali la lingua preferita o la località nella quale ti trovi.',
                    'sv' => 'Cookies för inställningar låter en webbplats komma ihåg information som ändrar hur webbplatsen fungerar eller visas. Detta kan t.ex. vara föredraget språk eller regionen du befinner dig i.',
                    'da' => 'Præference cookies gør det muligt for en hjemmeside at huske oplysninger, der ændrer den måde hjemmesiden ser ud eller opfører sig på. F.eks. dit foretrukne sprog, eller den region, du befinder dig i.',
                    'no' => 'Preferanse-cookies gjør et nettsted for å huske informasjon og endrer måten nettsiden oppfører seg eller ser ut, ting som ditt foretrukne språk eller den regionen du befinner deg i.',
                    'cs' => 'Preferenční cookies umožňují, aby si webová stránka zapamatovala informace, které mění, jak se webová stránka chová nebo jak vypadá. Je to například preferovaný jazyk nebo region, kde se nacházíte.',
                    'hu' => 'A preferenciális sütik használatával olyan információkat tudunk megjegyezni, amelyek megváltoztatják a weboldal magatartását, illetve kinézetét, erre példa lehet az Ön által előnyben részesített nyelv vagy a régió, amelyben tartózkodik.',
                    'si' => 'Piškotki za namestitve pomagajo spletni strani, da si ta zapomni informacije, ki spremenijo, na kakšen način se spletna stran obnaša ali izgleda, kot vaš priljubljeni jezik ali regijo, v kateri ste.',
                ],
                'technical' => 0,
                'active' => 0,
                'modules' => [],
                'cookies' => CookiesPlusCookie::getDefaultValues(CookiesPlusFinality::PREFERENCE_COOKIE),
                'position' => 1,
            ],
            CookiesPlusFinality::STATISTIC_COOKIE => [
                'name' => [
                    'en' => 'Statistic cookies',
                    'es' => 'Cookies estadísticas',
                    'ag' => 'Cookies estadísticas',
                    'cb' => 'Cookies estadísticas',
                    'mx' => 'Cookies estadísticas',
                    'fr' => 'Cookies statistiques',
                    'qc' => 'Cookies statistiques',
                    'pl' => 'Statystyka',
                    'ro' => 'Statistici',
                    'pt' => 'Cookies de estatística',
                    'br' => 'Cookies de estatística',
                    'sk' => 'Štatistiky',
                    'nl' => 'Statistieken',
                    'de' => 'Statistiken',
                    'gr' => 'Στατιστικά',
                    'it' => 'Cookie statistici',
                    'sv' => 'Statistik',
                    'da' => 'Statistik',
                    'no' => 'Statistikk',
                    'cs' => 'Statistické',
                    'hu' => 'Statisztikai',
                    'si' => 'Statistika',
                ],
                'description' => [
                    'en' => 'Statistic cookies help website owners to understand how visitors interact with websites by collecting and reporting information anonymously.',
                    'es' => 'Las cookies estadísticas ayudan a los propietarios de páginas web a comprender cómo interactúan los visitantes con las páginas web reuniendo y proporcionando información de forma anónima.',
                    'ag' => 'Las cookies estadísticas ayudan a los propietarios de páginas web a comprender cómo interactúan los visitantes con las páginas web reuniendo y proporcionando información de forma anónima.',
                    'cb' => 'Las cookies estadísticas ayudan a los propietarios de páginas web a comprender cómo interactúan los visitantes con las páginas web reuniendo y proporcionando información de forma anónima.',
                    'mx' => 'Las cookies estadísticas ayudan a los propietarios de páginas web a comprender cómo interactúan los visitantes con las páginas web reuniendo y proporcionando información de forma anónima.',
                    'fr' => 'Les cookies statistiques aident les propriétaires du site web, par la collecte et la communication d\'informations de manière anonyme, à comprendre comment les visiteurs interagissent avec les sites web.',
                    'qc' => 'Les cookies statistiques aident les propriétaires du site web, par la collecte et la communication d\'informations de manière anonyme, à comprendre comment les visiteurs interagissent avec les sites web.',
                    'pl' => 'Statystyczne pliki cookie pomagają właścicielem stron internetowych zrozumieć, w jaki sposób różni użytkownicy zachowują się na stronie, gromadząc i zgłaszając anonimowe informacje.',
                    'ro' => 'Cookie-urile de statistică îi ajută pe proprietarii unui site să înţeleagă modul în care vizitatorii interacţionează cu site-urile prin colectarea şi raportarea informaţiilor în mod anonim.',
                    'pt' => 'Os cookies de estatística ajudam os proprietários de websites a entenderem como os visitantes interagem com os websites, recolhendo e divulgando informações de forma anónima.',
                    'br' => 'Os cookies de estatística ajudam os proprietários de websites a entenderem como os visitantes interagem com os websites, recolhendo e divulgando informações de forma anónima.',
                    'sk' => 'Štatistické súbory cookies pomáhajú majiteľom webových stránok, aby pochopili, ako komunikovať s návštevníkmi webových stránok prostredníctvom zberu a hlásenia informácií anonymne.',
                    'nl' => 'Statistische cookies helpen eigenaren van websites begrijpen hoe bezoekers hun website gebruiken, door anoniem gegevens te verzamelen en te rapporteren.',
                    'de' => 'Statistik-Cookies helfen Webseiten-Besitzern zu verstehen, wie Besucher mit Webseiten interagieren, indem Informationen anonym gesammelt und gemeldet werden.',
                    'gr' => 'Τα Cookies στατιστικών βοηθούν τους ιδιοκτήτες ιστοχώρου να κατανοήσουν πώς αλληλεπιδρούν οι επισκέπτες με τις σελίδες συλλέγοντας και αναφέροντας πληροφορίες ανώνυμα.',
                    'it' => 'I cookie statistici aiutano i proprietari del sito web a capire come i visitatori interagiscono con i siti raccogliendo e trasmettendo informazioni in forma anonima.',
                    'sv' => 'Cookies för statistik hjälper en webbplatsägare att förstå hur besökare interagerar med webbplatser genom att samla och rapportera in information anonymt.',
                    'da' => 'Statistiske cookies giver hjemmesideejere indsigt i brugernes interaktion med hjemmesiden, ved at indsamle og rapportere oplysninger anonymt.',
                    'no' => 'Statistikk-cookies hjelper eiere til å forstå hvordan besøkende kommuniserer med nettsteder ved å samle inn og rapportere informasjon anonymt.',
                    'cs' => 'Statistické cookies pomáhají majitelům webových stránek, aby porozuměli, jak návštěvníci používají webové stránky. Anonymně sbírají a sdělují informace.',
                    'hu' => 'Az adatok névtelen formában való gyűjtésén és jelentésén keresztül a statisztikai sütik segítenek a weboldal tulajdonosának abban, hogy megértse, hogyan lépnek interakcióba a látogatók a weboldallal.',
                    'si' => 'Piškotki za statistiko pomagajo lastnikom spletnih strani razumeti, kako obiskovalci uporabljajo spletno stran tako, da anonimno zbirajo in javljajo informacije.',
                ],
                'technical' => 0,
                'active' => 0,
                'modules' => [
                    /* 'gapi',
                    'ganalytics',
                    'ps_googleanalytics', */
                ],
                'cookies' => CookiesPlusCookie::getDefaultValues(CookiesPlusFinality::STATISTIC_COOKIE),
                'position' => 2,
            ],
            CookiesPlusFinality::MARKETING_COOKIE => [
                'name' => [
                    'en' => 'Marketing cookies',
                    'es' => 'Cookies de marketing',
                    'ag' => 'Cookies de marketing',
                    'cb' => 'Cookies de marketing',
                    'mx' => 'Cookies de marketing',
                    'fr' => 'Cookies marketing',
                    'qc' => 'Cookies marketing',
                    'pl' => 'Marketing',
                    'ro' => 'Marketing',
                    'pt' => 'Cookies de marketing',
                    'br' => 'Cookies de marketing',
                    'sk' => 'Marketing',
                    'nl' => 'Marketing',
                    'de' => 'Marketing',
                    'gr' => 'Εμπορικής προώθησης',
                    'it' => 'Cookie di marketing',
                    'sv' => 'Marknadsföring',
                    'da' => 'Marketing',
                    'no' => 'Markedsføring',
                    'cs' => 'Marketingové',
                    'hu' => 'Marketing',
                    'si' => 'Trženje',
                ],
                'description' => [
                    'en' => 'Marketing cookies are used to track visitors across websites. The intention is to display ads that are relevant and engaging for the individual user and thereby more valuable for publishers and third party advertisers.',
                    'es' => 'Las cookies de marketing se utilizan para rastrear a los visitantes en las páginas web. La intención es mostrar anuncios relevantes y atractivos para el usuario individual, y por lo tanto, más valiosos para los editores y terceros anunciantes.',
                    'ag' => 'Las cookies de marketing se utilizan para rastrear a los visitantes en las páginas web. La intención es mostrar anuncios relevantes y atractivos para el usuario individual, y por lo tanto, más valiosos para los editores y terceros anunciantes.',
                    'cb' => 'Las cookies de marketing se utilizan para rastrear a los visitantes en las páginas web. La intención es mostrar anuncios relevantes y atractivos para el usuario individual, y por lo tanto, más valiosos para los editores y terceros anunciantes.',
                    'mx' => 'Las cookies de marketing se utilizan para rastrear a los visitantes en las páginas web. La intención es mostrar anuncios relevantes y atractivos para el usuario individual, y por lo tanto, más valiosos para los editores y terceros anunciantes.',
                    'fr' => 'Les cookies marketing sont utilisés pour effectuer le suivi des visiteurs au travers des sites web. Le but est d\'afficher des publicités qui sont pertinentes et intéressantes pour l\'utilisateur individuel et donc plus précieuses pour les éditeurs et annonceurs tiers.',
                    'qc' => 'Les cookies marketing sont utilisés pour effectuer le suivi des visiteurs au travers des sites web. Le but est d\'afficher des publicités qui sont pertinentes et intéressantes pour l\'utilisateur individuel et donc plus précieuses pour les éditeurs et annonceurs tiers.',
                    'pl' => 'Marketingowe pliki cookie stosowane są w celu śledzenia użytkowników na stronach internetowych. Celem jest wyświetlanie reklam, które są istotne i interesujące dla poszczególnych użytkowników i tym samym bardziej cenne dla wydawców i reklamodawców strony trzeciej.',
                    'ro' => 'Cookie-urile de marketing sunt utilizate pentru a-i urmări pe utilizatori de la un site la altul. Intenţia este de a afişa anunţuri relevante şi antrenante pentru utilizatorii individuali, aşadar ele sunt mai valoroase pentru agenţiile de puiblicitate şi părţile terţe care se ocupă de publicitate.',
                    'pt' => 'Os cookies de marketing são utilizados para seguir os visitantes pelos websites. A intenção é exibir anúncios que sejam relevantes e apelativos para o utilizador individual e, logo, mais valiosos para os editores e anunciantes independentes.',
                    'br' => 'Os cookies de marketing são utilizados para seguir os visitantes pelos websites. A intenção é exibir anúncios que sejam relevantes e apelativos para o utilizador individual e, logo, mais valiosos para os editores e anunciantes independentes.',
                    'sk' => 'Marketingové súbory cookies sa používajú na sledovanie návštevníkov na webových stránkach. Zámerom je zobrazovať reklamy, ktoré sú relevantné a pútavé pre jednotlivých užívateľov, a tým cennejšie pre vydavateľov a inzerentov tretích strán.',
                    'nl' => 'Marketingcookies worden gebruikt om bezoekers te volgen wanneer ze verschillende websites bezoeken. Hun doel is advertenties weergeven die zijn toegesneden op en relevant zijn voor de individuele gebruiker. Deze advertenties worden zo waardevoller voor uitgevers en externe adverteerders.',
                    'de' => 'Marketing-Cookies werden verwendet, um Besuchern auf Webseiten zu folgen. Die Absicht ist, Anzeigen zu zeigen, die relevant und ansprechend für den einzelnen Benutzer sind und daher wertvoller für Publisher und werbetreibende Drittparteien sind.',
                    'gr' => 'Τα cookies Εμπορικής Προώθησης χρησιμοποιούνται για την παρακολούθηση των επισκεπτών στους ιστότοπους. Η πρόθεση είναι να εμφανίσουμε διαφημίσεις που είναι σχετικές και ελκυστικές για τους χρήστες και ως εκ τούτου πιο πολύτιμες για τρίτους εκδότες και διαφημιστές.',
                    'it' => 'I cookie di marketing vengono utilizzati per tracciare i visitatori sui siti web. La finalità è quella di presentare annunci pubblicitari che siano rilevanti e coinvolgenti per il singolo utente e quindi di maggior valore per editori e inserzionisti di terze parti.',
                    'sv' => 'Cookies för marknadsföring används för att spåra besökare på webbplatser. Avsikten är att visa annonser som är relevanta och engagerande för enskilda användare, och därmed mer värdefull för utgivare och tredjepartsannonsörer.',
                    'da' => 'Marketing cookies bruges til at spore brugere på tværs af websites. Hensigten er at vise annoncer, der er relevante og engagerende for den enkelte bruger, og dermed mere værdifulde for udgivere og tredjeparts-annoncører.',
                    'no' => 'Markedsførings-cookies brukes til å spore besøkende på nettsteder. Hensikten er å vise annonser som er relevante og engasjerende for den enkelte bruker og dermed mer verdifull for utgivere og tredjeparts annonsører.',
                    'cs' => 'Marketingové cookies jsou používány pro sledování návštěvníků na webových stránkách. Záměrem je zobrazit reklamu, která je relevantní a zajímavá pro jednotlivého uživatele a tímto hodnotnější pro vydavatele a inzerenty třetích stran.',
                    'hu' => 'A marketingsütiket a látogatók weboldal-tevékenységének nyomon követésére használjuk. A cél az, hogy releváns hirdetéseket tegyünk közzé az egyéni felhasználók számára, valamint aktivitásra buzdítsuk őket, ez pedig még értékesebbé teszi weboldalunkat a tartalmakat közzétevő és a harmadik fél hirdetők számára.',
                    'si' => 'Piškotki za trženje se uporabljajo za sledenje uporabnikom prek spletnih strani. Namen je prikazovanje oglasov, ki so primerni in zanimivi za posameznega uporabnika in zato več vredni za založnike in oglaševalce tujih strani.',
                ],
                'technical' => 0,
                'active' => 0,
                'modules' => [
                    'pspixel',
                    'facebookconversionpixel',
                    'criteoonetag',
                    // 'cartsguru',
                    'sendinblue',
                ],
                'cookies' => CookiesPlusCookie::getDefaultValues(CookiesPlusFinality::MARKETING_COOKIE),
                'position' => 3,
            ],
            /*CookiesPlusFinality::UNCLASSIFIED_COOKIE => array(
                'name' => array(
                    'en' => 'Unclassified cookies',
                    'es' => 'Cookies no clasificadas',
                    'ag' => 'Cookies no clasificadas',
                    'cb' => 'Cookies no clasificadas',
                    'mx' => 'Cookies no clasificadas',
                    'fr' => 'Cookies non classés',
                    'qc' => 'Cookies non classés',
                    'pl' => 'Nieklasyfikowane',
                    'ro' => 'Neclasificate',
                    'pt' => 'Cookies não classificados',
                    'br' => 'Cookies não classificados',
                    'sk' => 'Nezaradené',
                    'nl' => 'Niet geclassificeerd',
                    'de' => 'Nicht klassifiziert',
                    'gr' => 'Αταξινόμητα',
                    'it' => 'Cookie non classificati',
                    'sv' => 'Oklassificerade',
                    'da' => 'Uklassificeret',
                    'no' => 'Uklassifisert',
                    'cs' => 'Neklasifikované',
                    'hu' => 'Besorolással nem rendelkező'

                ),
                'description' => array(
                    'en' => 'Unclassified cookies are cookies that we are in the process of classifying, together with the providers of individual cookies.',
                    'es' => 'Las cookies no clasificadas son cookies para las que todavía estamos en proceso de clasificar, junto con los proveedores de cookies individuales.',
                    'ag' => 'Las cookies no clasificadas son cookies para las que todavía estamos en proceso de clasificar, junto con los proveedores de cookies individuales.',
                    'cb' => 'Las cookies no clasificadas son cookies para las que todavía estamos en proceso de clasificar, junto con los proveedores de cookies individuales.',
                    'mx' => 'Las cookies no clasificadas son cookies para las que todavía estamos en proceso de clasificar, junto con los proveedores de cookies individuales.',
                    'fr' => 'Les cookies non classés sont les cookies qui sont en cours de classification, ainsi que les fournisseurs de cookies individuels.',
                    'qc' => 'Les cookies non classés sont les cookies qui sont en cours de classification, ainsi que les fournisseurs de cookies individuels.',
                    'pl' => 'Nieklasyfikowane pliki cookie, to pliki, które są w procesie klasyfikowania, wraz z dostawcami poszczególnych ciasteczek.',
                    'ro' => 'Cookie-urile neclasificate sunt cookie-uri în curs de clasificare, împreună cu furnizorii de cookie-uri individuale.',
                    'pt' => 'Os cookies não classificados são cookies que estão em processo de classificação, juntamente com os fornecedores de cookies individuais.',
                    'br' => 'Os cookies não classificados são cookies que estão em processo de classificação, juntamente com os fornecedores de cookies individuais.',
                    'sk' => 'Nezaradené súbory cookies sú cookies, ktoré práve zaraďujeme, spoločne s poskytovateľmi jednotlivých súborov cookies.',
                    'nl' => 'Niet-geclassificeerde cookies zijn cookies die we nog aan het classificeren zijn, samen met de aanbieders van afzonderlijke cookies.',
                    'de' => 'Nicht klassifizierte Cookies sind Cookies, die wir gerade versuchen zu klassifizieren, zusammen mit Anbietern von individuellen Cookies.',
                    'gr' => 'Τα αταξινόμητα cookies είναι τα cookies που είναι σε στάδιο ταξινόμησης, από κοινού με τους παρόχους μεμονωμένων cookies.',
                    'it' => 'I cookie non classificati sono i cookie che sono in fase di classificazione, insieme ai fornitori di cookie individuali.',
                    'sv' => 'Oklassificerade cookies är cookies som håller på att klassificeras tillsammans med utfärdarna av enskilda cookies.',
                    'da' => 'Uklassificerede cookies er cookies, som vi er i færd med at klassificere sammen med udbyderne af de enkelte cookies.',
                    'no' => 'Uklassifiserte cookies er informasjonskapsler som vi er i ferd med å klassifisere, sammen med leverandørene av enkelte cookies.',
                    'cs' => 'Neklasifikované cookies jsou cookies, které máme v procesu klasifikování společně s poskytovateli jednotlivých cookies.',
                    'hu' => 'A besorolással nem rendelkező sütik olyan sütik, amelyek még besorolás alatt állnak, az egyéni sütik szolgáltatóival együtt.'
                ),
                'technical' => 0,
                'active' => 0,
                'modules' => array(),
                'cookies' =>  CookiesPlusCookie::getDefaultValues(CookiesPlusFinality::UNCLASSIFIED_COOKIE)
            ),*/
            /*CookiesPlusFinality::PERFORMANCE_COOKIE => array(
                'name' => array(
                    'en' => 'Performance cookies',
                    'es' => 'Cookies de rendimiento',
                    'ag' => 'Cookies de rendimiento',
                    'cb' => 'Cookies de rendimiento',
                    'mx' => 'Cookies de rendimiento',
                    'fr' => 'Cookies de performance',
                    'qc' => 'Cookies de performance',
                    'pl' => 'Wydajnościowe pliki cookie',
                    'ro' => 'Cookie-uri de performanță',
                    'pt' => 'Cookies de desempenho',
                    'br' => 'Cookies de desempenho',
                    'sk' => 'Výkonové cookies',
                    'nl' => 'Prestatiecookies',
                    'de' => 'Leistungscookies',
                    'gr' => 'Cookies απόδοσης',
                    'it' => 'Cookie di prestazione',
                    'sv' => 'Izvedbeni piškotki',
                    'da' => 'Performance-cookies',
                    'no' => 'Ytelse informasjonskapsler',
                    'cs' => 'Výkonnostní cookies',
                    'hu' => 'Teljesítmény-sütik'
                ),
                'description' => array(
                    'en' => 'Cookies used specifically for gathering data on how visitors use a website, which pages of a website are visited most often, or if they get error messages on web pages. These cookies monitor only the performance of the site as the user interacts with it. These cookies don’t collect identifiable information on visitors, which means all the data collected is anonymous and only used to improve the functionality of a website.',
                    'es' => 'Cookies que se utilizan específicamente para recopilar datos sobre cómo los visitantes utilizan un sitio web, qué páginas de un sitio web se visitan con más frecuencia o si reciben mensajes de error en las páginas web. Estas cookies controlan solo el rendimiento del sitio cuando el usuario interactúa con él. Estas cookies no recopilan información identificable sobre los visitantes, lo que significa que todos los datos recopilados son anónimos y solo se utilizan para mejorar la funcionalidad de un sitio web.',
                    'ag' => 'Cookies que se utilizan específicamente para recopilar datos sobre cómo los visitantes utilizan un sitio web, qué páginas de un sitio web se visitan con más frecuencia o si reciben mensajes de error en las páginas web. Estas cookies controlan solo el rendimiento del sitio cuando el usuario interactúa con él. Estas cookies no recopilan información identificable sobre los visitantes, lo que significa que todos los datos recopilados son anónimos y solo se utilizan para mejorar la funcionalidad de un sitio web.',
                    'cb' => 'Cookies que se utilizan específicamente para recopilar datos sobre cómo los visitantes utilizan un sitio web, qué páginas de un sitio web se visitan con más frecuencia o si reciben mensajes de error en las páginas web. Estas cookies controlan solo el rendimiento del sitio cuando el usuario interactúa con él. Estas cookies no recopilan información identificable sobre los visitantes, lo que significa que todos los datos recopilados son anónimos y solo se utilizan para mejorar la funcionalidad de un sitio web.',
                    'mx' => 'Cookies que se utilizan específicamente para recopilar datos sobre cómo los visitantes utilizan un sitio web, qué páginas de un sitio web se visitan con más frecuencia o si reciben mensajes de error en las páginas web. Estas cookies controlan solo el rendimiento del sitio cuando el usuario interactúa con él. Estas cookies no recopilan información identificable sobre los visitantes, lo que significa que todos los datos recopilados son anónimos y solo se utilizan para mejorar la funcionalidad de un sitio web.',
                    'fr' => 'Cookies utilisés spécifiquement pour collecter des données sur la façon dont les visiteurs utilisent un site Web, quelles pages d\'un site Web sont visitées le plus souvent ou s\'ils reçoivent des messages d\'erreur sur les pages Web. Ces cookies surveillent uniquement les performances du site lorsque l\'utilisateur interagit avec lui. Ces cookies ne collectent pas d\'informations identifiables sur les visiteurs, ce qui signifie que toutes les données collectées sont anonymes et utilisées uniquement pour améliorer la fonctionnalité d\'un site Web.',
                    'qc' => 'Cookies utilisés spécifiquement pour collecter des données sur la façon dont les visiteurs utilisent un site Web, quelles pages d\'un site Web sont visitées le plus souvent ou s\'ils reçoivent des messages d\'erreur sur les pages Web. Ces cookies surveillent uniquement les performances du site lorsque l\'utilisateur interagit avec lui. Ces cookies ne collectent pas d\'informations identifiables sur les visiteurs, ce qui signifie que toutes les données collectées sont anonymes et utilisées uniquement pour améliorer la fonctionnalité d\'un site Web.',
                    'pl' => 'Pliki cookie używane specjalnie do gromadzenia danych o tym, w jaki sposób odwiedzający korzystają ze strony internetowej, które strony witryny są odwiedzane najczęściej lub czy otrzymują komunikaty o błędach na stronach internetowych. Te pliki cookie monitorują tylko działanie witryny w trakcie interakcji użytkownika z nią. Te pliki cookie nie zbierają informacji umożliwiających identyfikację odwiedzających, co oznacza, że wszystkie gromadzone dane są anonimowe i służą wyłącznie do poprawy funkcjonalności strony internetowej.',
                    'ro' => 'Cookie-urile utilizate în mod special pentru colectarea datelor despre modul în care vizitatorii folosesc un site web, ce pagini ale unui site web sunt vizitate cel mai des sau dacă primesc mesaje de eroare pe paginile web. Aceste cookie-uri monitorizează doar performanța site-ului pe măsură ce utilizatorul interacționează cu acesta. Aceste cookie-uri nu colectează informații de identificare ale vizitatorilor, ceea ce înseamnă că toate datele colectate sunt anonime și sunt utilizate numai pentru a îmbunătăți funcționalitatea unui site web.',
                    'pt' => 'Cookies usados especificamente para coletar dados sobre como os visitantes usam um site, quais páginas de um site são visitadas com mais frequência ou se eles recebem mensagens de erro em páginas da web. Esses cookies monitoram apenas o desempenho do site à medida que o usuário interage com ele. Esses cookies não coletam informações identificáveis sobre os visitantes, o que significa que todos os dados coletados são anônimos e usados apenas para melhorar a funcionalidade de um site.',
                    'br' => 'Cookies usados especificamente para coletar dados sobre como os visitantes usam um site, quais páginas de um site são visitadas com mais frequência ou se eles recebem mensagens de erro em páginas da web. Esses cookies monitoram apenas o desempenho do site à medida que o usuário interage com ele. Esses cookies não coletam informações identificáveis sobre os visitantes, o que significa que todos os dados coletados são anônimos e usados apenas para melhorar a funcionalidade de um site.',
                    'sk' => 'Súbory cookie používané špeciálne na zhromažďovanie údajov o tom, ako návštevníci používajú webovú stránku, ktoré stránky webovej stránky sú navštevované najčastejšie alebo či sa im na webových stránkach zobrazujú chybové správy. Tieto súbory cookie monitorujú iba výkonnosť stránok pri interakcii používateľa s nimi. Tieto súbory cookie nezhromažďujú identifikovateľné informácie o návštevníkoch, čo znamená, že všetky zhromaždené údaje sú anonymné a slúžia iba na zlepšenie funkčnosti webových stránok.',
                    'nl' => 'Cookies die specifiek worden gebruikt voor het verzamelen van gegevens over hoe bezoekers een website gebruiken, welke pagina\'s van een website het vaakst worden bezocht of of ze foutmeldingen krijgen op webpagina\'s. Deze cookies controleren alleen de prestaties van de site terwijl de gebruiker ermee communiceert. Deze cookies verzamelen geen identificeerbare informatie over bezoekers, wat betekent dat alle verzamelde gegevens anoniem zijn en alleen worden gebruikt om de functionaliteit van een website te verbeteren.',
                    'de' => 'Cookies, die speziell zum Sammeln von Daten darüber verwendet werden, wie Besucher eine Website nutzen, welche Seiten einer Website am häufigsten besucht werden oder ob sie auf Webseiten Fehlermeldungen erhalten. Diese Cookies überwachen nur die Leistung der Website, während der Benutzer mit ihr interagiert. Diese Cookies sammeln keine identifizierbaren Informationen über Besucher. Dies bedeutet, dass alle gesammelten Daten anonym sind und nur zur Verbesserung der Funktionalität einer Website verwendet werden.',
                    'gr' => 'Cookies που χρησιμοποιούνται ειδικά για τη συλλογή δεδομένων σχετικά με τον τρόπο με τον οποίο οι επισκέπτες χρησιμοποιούν έναν ιστότοπο, ποιες σελίδες ενός ιστότοπου επισκέπτονται συχνότερα ή αν λαμβάνουν μηνύματα σφάλματος σε ιστοσελίδες. Αυτά τα cookie παρακολουθούν μόνο την απόδοση του ιστότοπου καθώς ο χρήστης αλληλεπιδρά με αυτόν. Αυτά τα cookie δεν συλλέγουν αναγνωρίσιμες πληροφορίες για τους επισκέπτες, πράγμα που σημαίνει ότι όλα τα δεδομένα που συλλέγονται είναι ανώνυμα και χρησιμοποιούνται μόνο για τη βελτίωση της λειτουργικότητας ενός ιστότοπου.',
                    'it' => 'Cookie utilizzati specificamente per raccogliere dati su come i visitatori utilizzano un sito web, quali pagine di un sito web vengono visitate più spesso o se ricevono messaggi di errore sulle pagine web. Questi cookie monitorano solo le prestazioni del sito mentre l\'utente interagisce con esso. Questi cookie non raccolgono informazioni identificabili sui visitatori, il che significa che tutti i dati raccolti sono anonimi e utilizzati solo per migliorare la funzionalità di un sito web.',
                    'sv' => 'Piškotki, ki se uporabljajo posebej za zbiranje podatkov o tem, kako obiskovalci uporabljajo spletno mesto, katere strani spletnega mesta so najpogosteje obiskane ali če na spletnih straneh dobijo sporočila o napakah. Ti piškotki spremljajo samo delovanje strani, saj uporabnik z njo sodeluje. Ti piškotki ne zbirajo prepoznavnih podatkov o obiskovalcih, kar pomeni, da so vsi zbrani podatki anonimni in se uporabljajo samo za izboljšanje funkcionalnosti spletnega mesta.',
                    'da' => 'Cookies, der specifikt bruges til at indsamle data om, hvordan besøgende bruger et websted, hvilke sider på et websted der besøges oftest, eller hvis de får fejlmeddelelser på websider. Disse cookies overvåger kun webstedets ydeevne, da brugeren interagerer med det. Disse cookies indsamler ikke identificerbare oplysninger om besøgende, hvilket betyder, at alle de indsamlede data er anonyme og kun bruges til at forbedre funktionaliteten på et websted.',
                    'no' => 'Informasjonskapsler som brukes spesielt for å samle inn data om hvordan besøkende bruker et nettsted, hvilke sider på et nettsted som besøkes oftest, eller hvis de får feilmeldinger på websider. Disse informasjonskapslene overvåker bare ytelsen til nettstedet når brukeren kommuniserer med det. Disse informasjonskapslene samler ikke inn identifiserbar informasjon om besøkende, noe som betyr at all innsamlet data er anonym og kun brukes til å forbedre funksjonaliteten til et nettsted.',
                    'cs' => 'Soubory cookie používané konkrétně pro shromažďování údajů o tom, jak návštěvníci používají web, které stránky webu jsou navštěvovány nejčastěji, nebo zda se jim na webových stránkách zobrazují chybové zprávy. Tyto soubory cookie sledují pouze výkonnost webu při interakci uživatele s ním. Tyto soubory cookie neshromažďují identifikovatelné informace o návštěvnících, což znamená, že všechna shromážděná data jsou anonymní a slouží pouze ke zlepšení funkčnosti webových stránek.',
                    'hu' => 'A cookie-k kifejezetten arra szolgálnak, hogy adatokat gyűjtsenek arról, hogy a látogatók hogyan használnak egy webhelyet, a webhely mely oldalait látogatják meg leggyakrabban, vagy ha hibaüzeneteket kapnak a weboldalakon. Ezek a sütik csak a webhely teljesítményét figyelik, miközben a felhasználó interakcióba lép vele. Ezek a cookie-k nem gyűjtenek azonosítható információkat a látogatókról, ami azt jelenti, hogy az összes összegyűjtött adat névtelen és csak egy weboldal funkcionalitásának javítására szolgál.'
                ),
                'technical' => 0,
                'active' => 0,
                'modules' => array(),
                'cookies' =>  CookiesPlusCookie::getDefaultValues(CookiesPlusFinality::PERFORMANCE_COOKIE)
            ),*/
        ];

        if (isset($cookiesPlusFinalityDefaultValues[$cookiesPlusFinality])) {
            return $cookiesPlusFinalityDefaultValues[$cookiesPlusFinality];
        }

        return [];
    }

    public static function getFinalityNameCallback($value)
    {
        $cookiesPlusFinality = new CookiesPlusFinality($value);

        return $cookiesPlusFinality->name[(int) Context::getContext()->language->id];
    }

    public static function getFinalityDescriptionCallback($value)
    {
        return Tools::strlen($value) > 200 ? Tools::substr($value, 0, 200) . '...' : $value;
    }

    public static function getModulesBlockedCallback($value)
    {
        if ($value) {
            $modulesBlocked = [];
            $modules = json_decode($value, true) ?: [];
            foreach ($modules as $module) {
                $module = Module::getInstanceById($module);
                if ($module) {
                    $modulesBlocked[] = $module->name;
                }
            }

            return implode('<br />', $modulesBlocked);
        }

        return '';
    }

    public function updatePosition($way, $position)
    {
        $res = Db::getInstance()->executeS(
            'SELECT `id_cookiesplus_finality`, `position`
            FROM `' . _DB_PREFIX_ . 'cookiesplus_finality`
            ORDER BY `position` ASC;'
        );

        if (!$res) {
            return false;
        }

        foreach ($res as $obj) {
            if ((int) $obj['id_cookiesplus_finality'] == (int) $this->id) {
                $moved = $obj;
            }
        }

        if (!isset($moved)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'cookiesplus_finality`
                SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
                WHERE `position`
                ' . ($way
                    ? '> ' . (int) $moved['position'] . ' AND `position` <= ' . (int) $position
                    : '< ' . (int) $moved['position'] . ' AND `position` >= ' . (int) $position))
            && Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'cookiesplus_finality`
                SET `position` = ' . (int) $position . '
                WHERE `id_cookiesplus_finality` = ' . (int) $moved['id_cookiesplus_finality']);
    }

    public static function getHigherPosition()
    {
        $sql = 'SELECT MAX(`position`)
                FROM `' . _DB_PREFIX_ . 'cookiesplus_finality`';

        $position = Db::getInstance()->getValue($sql);

        return (is_numeric($position)) ? $position : -1;
    }
}
