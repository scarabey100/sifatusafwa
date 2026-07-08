<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBannerTwo
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

/**
 * Class EgBannerTwoClass.
 */
class EgBannerTwoClass extends ObjectModel
{
    /** @var int EdBannerID */
    public $id_eg_banner;

  /** @var int Category ID */
    public $id_category;

 	/** @var string title Manufacture */
	public $title;

	/** @var string hook */
	public $hook;

    /** @var  int width image banner */
    public $width;

    /** @var  int height image banner */
    public $height;

    /** @var  int sport position */
    public $position;

    /** @var  string Long description Manufacture*/
    public $description;

    /** @var string image  */
    public $image_book;

    /** @var string image_mobile  */
    public $image_mobile_book;
 
    /** @var string image  */
    public $image_background;

    /** @var string image_mobile  */
    public $image_mobile_background; 

    /** @var string alt image  */
    public $alt;

    /** @var string link image */
    public $link;

    /** @var string link image */
    public $link_text;
    /** @var string link image */
    public $linktwo;

    /** @var string link image */
    public $link_text_two;
    
    /** @var bool Status for display Banner*/
    public $active = true;

    /** @var string link image */
    public $recto;
    
    /** @var string link image */
    public $use_background;

    /** @var string Start date (datetime) */
    public $start_date;

    /** @var string End date (datetime) */
    public $end_date;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'eg_banner_two',
		'primary' => 'id_eg_banner',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'), 
            'position'    => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'active'      => array('type' => self::TYPE_BOOL),
            'recto'        => array('type' => self::TYPE_BOOL), 
            'use_background'        => array('type' => self::TYPE_BOOL), 

            /* Lang fields Banner*/  
            'image_book'        => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'image_mobile_book' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'image_background'        => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'image_mobile_background' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'description'  => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'), 
            'title'        => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'link'         => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isGenericName'),
            'alt'          => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'),
            'link_text'    => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'validate' => 'isCleanHtml'),
            'start_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'end_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            //'linktwo'      => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),
            //'link_text_two'    => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'),
        ),
	);

    /**
     * Validates the start_date and end_date.
     * Ensures start_date is earlier than or equal to end_date.
     *
     * @return bool
     */
    public function validateDates()
    {
        if ($this->start_date && $this->end_date) {
            return strtotime($this->start_date) <= strtotime($this->end_date);
        }
        return true;
    }

    /**
     * Adds current sport as a new Object to the database
     *
     * @param bool $autoDate    Automatically set `date_upd` and `date_add` columns
     * @param bool $nullValues Whether we want to use NULL values instead of empty quotes values
     *
     * @return bool Indicates whether the Banner has been successfully added
     * @throws
     * @throws
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->validateDates()) {
            throw new PrestaShopException('The start date must be earlier than or equal to the end date.');
        }
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    }

    /**
     * Overrides the update method to validate dates before updating.
     */
    public function update($nullValues = false)
    {
        if (!$this->validateDates()) {
            throw new PrestaShopException('The start date must be earlier than or equal to the end date.');
        }
        return parent::update($nullValues);
    }


    /**
     * @return int MAX Position Banner
     */
    public static function getMaxPosition()
    {
        $query = new DbQuery();
        $query->select('MAX(position)');
        $query->from('eg_banner_two', 'eg');

        $response = Db::getInstance()->getRow($query);

        if ($response['MAX(position)'] == null){
            return -1;
        }
        return $response['MAX(position)'];
    }

    /**
     * @param $way int
     * @param $position int Position Banner
     * @return bool
     * @throws
     */
    public function updatePosition($way, $position)
    {
        $query = new DbQuery();
        $query->select('eg.`id_eg_banner`, eg.`position`');
        $query->from('eg_banner_two', 'eg');
        $query->orderBy('eg.`position` ASC');
        $tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$tabs ) {
            return false;
        }

        foreach ($tabs as $tab) {
            if ((int) $tab['id_eg_banner'] == (int) $this->id) {
                $moved_tab = $tab;
            }
        }

        if (!isset($moved_tab) || !isset($position)) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        return (Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'eg_banner_two`
            SET `position`= `position` '.($way ? '- 1' : '+ 1').'
            WHERE `position`
            '.($way
                    ? '> '.(int)$moved_tab['position'].' AND `position` <= '.(int)$position
                    : '< '.(int)$moved_tab['position'].' AND `position` >= '.(int)$position
                ))
            && Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'eg_banner_two`
            SET `position` = '.(int)$position.'
            WHERE `id_eg_banner` = '.(int)$moved_tab['id_eg_banner']));
    }

    /**
     * @param $value string image Banner
     * @return string src
     */
    public static function showBanner($value)
    {
        $src = __PS_BASE_URI__. 'modules/egbannertwo/views/img/'.$value;
        return $value ? '<img src="'.$src.'" width="80" height="40px" class="img img-thumbnail"/>' : '-';
    }

    /**
     * @param $idBannerPos int ID BannerPos
     * @return string hook name
     */
    public static function getNameHook($idBannerPos)
    {
        $query = new DbQuery();
        $query->select('ebp.hook');
        $query->from('eg_banner_pos', 'ebp');
        $query->where('ebp.`id_eg_banner_pos` =  '.(int) $idBannerPos);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param $limit int
     * @return array list banner by hook
     * @throws
     */
    public static function getBannerFromHook($limit = null)
    {
        $idLang = Context::getContext()->language->id;
        $currentDate = date('Y-m-d');

        $query = new DbQuery();
        $query->select('eg.*, egl.*');
        $query->from('eg_banner_two', 'eg');
        $query->leftJoin('eg_banner_two_lang', 'egl', 'eg.`id_eg_banner` = egl.`id_eg_banner`'.Shop::addSqlRestrictionOnLang('egl'));
        $query->where('eg.`active` =  1 AND egl.`id_lang` =  '.(int) $idLang);
        $query->where('(eg.`start_date` IS NULL OR eg.`start_date` <= "'.$currentDate.'")');
        $query->where('(eg.`end_date` IS NULL OR eg.`end_date` >= "'.$currentDate.'")');
        if ($limit) {
            $query->limit((int) $limit);
        }
        $query->orderBy('eg.`position` ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public static function getCategorySelectedById($idEgBanner)
    {
        $query = new DbQuery();
        $query->select('b.`id_category`');
        $query->from('eg_banner_two', 'b');
        $query->where('b.`id_eg_banner` =  '.(int) $idEgBanner);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * @param $IdCategory int Category ID
     * @return string name category
     */
    public static function getNameCategoryById($IdCategory)
    {
        $idLang = (int) Context::getContext()->language->id;
        $query = new DbQuery();
        $query->select('name');
        $query->from('category_lang', 'cl');
        $query->leftJoin('eg_banner_two', 'cb', 'cb.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl'));
        $query->where('cb.`id_category` =  '.(int) $IdCategory.' AND cl.`id_lang` =  '.$idLang);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public static function updateEgBannerImag($champ, $imgValue)
    {
        $res = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'eg_banner_two_lang` SET '.$champ.' = Null  WHERE '.$champ.' = "'.$imgValue.'"');
        if ($res && file_exists(__PS_BASE_URI__. 'modules/egbannertwo/views/img/'.$imgValue)) {
            @unlink(__PS_BASE_URI__. 'modules/egbannertwo/views/img/'.$$imgValue);
        }
    }
}
