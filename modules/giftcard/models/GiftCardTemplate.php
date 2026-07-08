<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class GiftCardTemplate extends ObjectModel
{
    public $id_gift_card_template;

    public $issvg = 0;

    public $isdefault = 0;

    public $name;

    public $id_lang_display;

    public $active = 0;

    public $date_add;

    public $date_upd;

    public $var_price_default = 0;

    public $var_code_default = '';

    public $var_from_default = '';

    public $var_lastname_default = '';

    public $var_message_default = '';

    public $var_expirate_default = '';

    public $pdf_image_only = 0;

    /* field fixed text */
    public $var_text1;

    public $var_text2;

    public $var_text3;

    public $var_text4;

    public $var_text5;

    public $var_text6;

    public $var_text7;

    public $var_text8;

    public $var_text9;

    public $var_text10;

    /* field fixed color */
    public $var_color1;

    public $var_color2;

    public $var_color3;

    public $var_color4;

    public $var_color5;

    public $var_color6;

    public $var_color7;

    public $var_color8;

    public $var_color9;

    public $var_color10;

    /* field fixed imgpath */
    public $var_imgpath1;

    public $var_imgpath2;

    public $virtualuse;

    public $physicaluse;

    /**
     * @var int access rights of created folders (octal)
     */
    protected static $access_rights = 0775;

    /**
     * * @var array Tags
     */
    public $tags;

    public $img_dir;

    public $source_index;

    protected static $images_types_cache;

    protected $table = 'giftcardtemplate';

    protected $identifier = 'id_gift_card_template';

    public static $definition = [
        'table' => 'giftcardtemplate',
        'primary' => 'id_gift_card_template',
        'multilang' => true,
        'fields' => [
            'issvg' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'isdefault' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'name' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => true,
                'size' => 128,
            ],
            'var_text1' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text2' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text3' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text4' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text5' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text6' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text7' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text8' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text9' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_text10' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => false,
                'size' => 255,
            ],
            'var_color1' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color2' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color3' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color4' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color5' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color6' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color7' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color8' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color9' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_color10' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isColor',
            ],
            'var_imgpath1' => [
                'type' => self::TYPE_STRING,
            ],
            'var_imgpath2' => [
                'type' => self::TYPE_STRING,
            ],
            'var_code_default' => [
                'type' => self::TYPE_STRING,
            ],
            'var_price_default' => [
                'type' => self::TYPE_STRING,
            ],
            'var_lastname_default' => [
                'type' => self::TYPE_STRING,
            ],
            'var_from_default' => [
                'type' => self::TYPE_STRING,
            ],
            'var_message_default' => [
                'type' => self::TYPE_STRING,
            ],
            'var_expirate_default' => [
                'type' => self::TYPE_STRING,
            ],
            'physicaluse' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'virtualuse' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'pdf_image_only' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'id_lang_display' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat',
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
        $this->source_index = _PS_PROD_IMG_DIR_ . 'index.php';
        $this->img_dir = _PS_ROOT_DIR_ . '/modules/giftcard/img/templates/';
    }

    public function add($autodate = true, $nullValues = false)
    {
        if (!$this->isdefault && !self::getDefault()) {
            $this->isdefault = true;
        }

        return parent::add();
    }

    public static function getTemplates($id_lang = null, $active = false, $id_shop = null)
    {
        if ($id_lang === null) {
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        }
        $sql = '
		SELECT t.*,tl.*
		FROM `' . _DB_PREFIX_ . 'giftcardtemplate` t
		LEFT JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_lang` tl
		    ON (t.`id_gift_card_template` = tl.`id_gift_card_template`) and tl.id_lang = ' . $id_lang . '
		' . ($id_shop && Shop::isFeatureActive() ? 'JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_shop` ts ON 
				(ts.`id_gift_card_template` = t.`id_gift_card_template` AND ts.id_shop =' . $id_shop . ')' : '') . '
		WHERE 1=1
		' . ($active ? ' AND active=1 ' : '') . ' AND (t.id_lang_display=0 OR t.id_lang_display=' . (int) $id_lang . ')';
        $result = Db::getInstance()->ExecuteS($sql);

        return $result;
    }

    public static function getTemplatesGroupByTag($id_lang = null, $active = false, $id_shop = null)
    {
        if ($id_lang === null) {
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        }
        $sql = 'SELECT tt.id_gift_card_tag,t.*,tl.`name` 
			   FROM `' . _DB_PREFIX_ . 'giftcardtemplate` t
			   INNER JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_lang` tl
			       ON tl.id_gift_card_template = t.id_gift_card_template
			   AND tl.id_lang = ' . $id_lang . '
			   INNER JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_tag` tt
			       ON tt.id_gift_card_template = t.id_gift_card_template
			   ' . ($id_shop && Shop::isFeatureActive() ? 'JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_shop` ts 
			   		ON (ts.`id_gift_card_template` = t.`id_gift_card_template` AND ts.id_shop =' . $id_shop . ')' : '') . '
			   WHERE 1=1' . ($active ? ' AND active=1 ' : '');
        $result = Db::getInstance()->ExecuteS($sql);
        $templategroupbytags = [];
        foreach ($result as $row) {
            $templategroupbytags[$row['id_gift_card_tag']][] = $row;
        }

        return $templategroupbytags;
    }

    public function getTags($id_lang)
    {
        if (is_null($this->tags)) {
            $this->tags = GiftCardTag::getTemplateTags($this->id);
        }
        if (!($this->tags && key_exists($id_lang, $this->tags))) {
            return '';
        }
        $result = '';
        foreach ($this->tags[$id_lang] as $tag_name) {
            $result .= $tag_name . ', ';
        }

        return rtrim($result, ', ');
    }

    public function delete()
    {
        if (!parent::delete()) {
            return false;
        }
        if (!$this->deleteTags()) {
            return false;
        }
        if (is_dir($this->img_dir . $this->id) && isset($this->id) && (int) $this->id > 0) {
            $languages = Language::getLanguages(false);
            if (file_exists($this->img_dir . $this->id . '/' . $this->id . '.jpg')) {
                @unlink($this->img_dir . $this->id . '/' . $this->id . '.jpg');
            }
            if (file_exists($this->img_dir . $this->id . '/' . $this->id . '.svg')) {
                @unlink($this->img_dir . $this->id . '/' . $this->id . '.svg');
            }
            $files_to_delete = [];
            foreach ($languages as $language) {
                $files_to_delete[] = $this->img_dir . $this->id . '/' . $this->id . '-' . $language['id_lang'] . '.jpg';
            }
            $image_types = self::getImagesTypes();
            foreach ($image_types as $image_type) {
                $files_to_delete[] = $this->img_dir . $this->id . '/' . $this->id . '-' . $image_type['name'] . '.jpg';
            }
            foreach ($image_types as $image_type) {
                foreach ($languages as $language) {
                    $files_to_delete[] = $this->img_dir . $this->id . '/' . $this->id . '-' .
                        $image_type['name'] . '-' . $language['id_lang'] . '.jpg';
                }
            }
            // delete index.php
            $files_to_delete[] = $this->img_dir . $this->id . '/index.php';
            // Delete tmp images
            $this->unlinkTmpImg($languages);
            foreach ($files_to_delete as $file) {
                if (file_exists($file) && !@unlink($file)) {
                    return false;
                }
            }
            @rmdir($this->img_dir . $this->id);
        }

        return true;
    }

    public static function getImagesTypes()
    {
        if (!isset(self::$images_types_cache)) {
            self::$images_types_cache = [];
            $image_type_front = [];
            $image_type_front['name'] = 'front';
            $image_type_front['width'] = (int) Configuration::get('GIFTCARD_FRONT_IMG_WIDTH');
            $image_type_front['height'] = (int) Configuration::get('GIFTCARD_FRONT_IMG_HEIGHT');
            self::$images_types_cache[] = $image_type_front;
            $image_type_thickbox = [];
            $image_type_thickbox['name'] = 'thickbox';
            $image_type_thickbox['width'] = (int) Configuration::get('GIFTCARD_FRONT_LIMG_WIDTH');
            $image_type_thickbox['height'] = (int) Configuration::get('GIFTCARD_FRONT_LIMG_HEIGHT');
            self::$images_types_cache[] = $image_type_thickbox;
        }

        return self::$images_types_cache;
    }

    public function unlinkTmpImg($languages = null)
    {
        if (is_dir($this->img_dir . $this->id) && isset($this->id) && (int) $this->id > 0) {
            $files_to_delete = [];
            $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'giftcardtemplate_' . $this->id . '.jpg';
            $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'giftcardtemplatemini_' . $this->id . '.jpg';
            if (!isset($languages)) {
                $languages = Language::getLanguages(false);
            }
            foreach ($languages as $language) {
                $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'giftcardtemplate_' . $this->id . '-' . $language['id_lang'] . '.jpg';
                $files_to_delete[] = _PS_TMP_IMG_DIR_ . 'giftcardtemplatemini_' . $this->id . '-' . $language['id_lang'] . '.jpg';
            }
            foreach ($files_to_delete as $file) {
                if (file_exists($file) && !@unlink($file)) {
                    return false;
                }
            }
        }
    }

    public static function duplicate($id_gift_card_template_old)
    {
        if (Validate::isLoadedObject($gift_card_template = new GiftCardTemplate($id_gift_card_template_old))) {
            unset($gift_card_template->id);
            unset($gift_card_template->id_gift_card_template);
            $gift_card_template->active = 0;
            $gift_card_template->isdefault = false;
            if (Tools::getValue('noimage')) {
                $gift_card_template->issvg = false;
            }
            if ($gift_card_template->add()) {
                if (!GiftCardTemplate::duplicateTags($id_gift_card_template_old, $gift_card_template->id)) {
                    return false;
                }
                if (!Tools::getValue('noimage')) {
                    if (!$gift_card_template->createImgFolder()) {
                        return false;
                    }
                    $languages = Language::getLanguages(false);
                    $template_path_old = $gift_card_template->img_dir . $id_gift_card_template_old . '/';
                    $template_path_new = $gift_card_template->img_dir . $gift_card_template->id . '/';
                    if (file_exists($template_path_old . $id_gift_card_template_old . '.svg')) {
                        copy(
                            $template_path_old . $id_gift_card_template_old . '.svg',
                            $template_path_new . $gift_card_template->id . '.svg'
                        );
                    }
                    if (file_exists($template_path_old . $id_gift_card_template_old . '.jpg')) {
                        copy(
                            $template_path_old . $id_gift_card_template_old . '.jpg',
                            $template_path_new . $gift_card_template->id . '.jpg'
                        );
                    }
                    foreach ($languages as $language) {
                        if (file_exists($template_path_old . $id_gift_card_template_old .
                            '-' . $language['id_lang'] . '.jpg')) {
                            copy(
                                $template_path_old . $id_gift_card_template_old . '-' . $language['id_lang'] . '.jpg',
                                $template_path_new . $gift_card_template->id . '-' . $language['id_lang'] . '.jpg'
                            );
                        }
                    }
                    $images_types = self::getImagesTypes();
                    foreach ($images_types as $k => $image_type) {
                        if (file_exists($template_path_old . $id_gift_card_template_old . '-' . $image_type['name'] . '.jpg')) {
                            copy(
                                $template_path_old . $id_gift_card_template_old . '-' . $image_type['name'] . '.jpg',
                                $template_path_new . $gift_card_template->id . '-' . $image_type['name'] . '.jpg'
                            );
                        }
                        foreach ($languages as $language) {
                            if (file_exists($template_path_old . $id_gift_card_template_old . '-' .
                                $image_type['name'] . '-' . $language['id_lang'] . '.jpg')) {
                                copy(
                                    $template_path_old . $id_gift_card_template_old . '-' . $image_type['name'] . '-' .
                                    $language['id_lang'] . '.jpg',
                                    $template_path_new . $gift_card_template->id . '-' . $image_type['name'] . '-' .
                                    $language['id_lang'] . '.jpg'
                                );
                            }
                        }
                    }
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return $gift_card_template;
    }

    public static function duplicateTags($id_gift_card_template_old, $id_gift_card_template_new)
    {
        $tags = Db::getInstance()->executeS('SELECT `id_gift_card_tag` FROM `' . _DB_PREFIX_ .
            'giftcardtemplate_tag` WHERE `id_gift_card_template` = ' . (int) $id_gift_card_template_old);
        if (!Db::getInstance()->NumRows()) {
            return true;
        }
        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'id_gift_card_template' => (int) $id_gift_card_template_new,
                'id_gift_card_tag' => (int) $tag['id_gift_card_tag'],
            ];
        }

        return Db::getInstance()->insert('giftcardtemplate_tag', $data);
    }

    public function deleteTags()
    {
        return Db::getInstance()->delete('giftcardtemplate_tag', 'id_gift_card_template = ' .
                (int) $this->id)
            && Db::getInstance()->delete('giftcardtag', 'id_gift_card_tag NOT IN (SELECT id_gift_card_tag FROM ' .
                _DB_PREFIX_ . 'giftcardtemplate_tag)');
    }

    public function initCustomVar()
    {
        $this->var_price_default = 0;
        $this->var_code_default = '';
        $this->var_from_default = '';
        $this->var_lastname_default = '';
        $this->var_message_default = '';
        $this->var_expirate_default = '';
        for ($i = 1; $i <= 10; ++$i) {
            $varfield = 'var_text' . $i;
            $this->$varfield = null;
            $varfield = 'var_color' . $i;
            $this->$varfield = null;
        }
    }

    public function updateCustomVar($available_vars = [])
    {
        $languages = Language::getLanguages(false);
        for ($i = 1; $i <= 10; ++$i) {
            if (isset($available_vars['var_text' . $i])) {
                $varfield = 'var_text' . $i;
                $text_l = [];
                foreach ($languages as $language) {
                    $id_lang = (int) $language['id_lang'];
                    $text_l[$id_lang] = $available_vars['var_text' . $i];
                }
                $this->$varfield = $text_l;
            }
            if (isset($available_vars['var_color' . $i])) {
                $varfield = 'var_color' . $i;
                $this->$varfield = $available_vars['var_color' . $i];
            }
        }
        if (isset($available_vars['giftcard_price']) && (int) $available_vars['giftcard_price'] > 0) {
            $this->var_price_default = (int) $available_vars['giftcard_price'];
        }
        if (isset($available_vars['giftcard_code'])) {
            $this->var_code_default = (string) $available_vars['giftcard_code'];
        }
        if (isset($available_vars['giftcard_from'])) {
            $this->var_from_default = (string) $available_vars['giftcard_from'];
        }
        if (isset($available_vars['giftcard_lastname'])) {
            $this->var_lastname_default = (string) $available_vars['giftcard_lastname'];
        }
        if (isset($available_vars['giftcard_message'])) {
            $this->var_message_default = (string) $available_vars['giftcard_message'];
        }
        if (isset($available_vars['giftcard_expirate'])) {
            $this->var_expirate_default = (string) $available_vars['giftcard_expirate'];
        }
    }

    public static function getDefault()
    {
        $result = Db::getInstance()->getRow('
				SELECT t.id_gift_card_template
				FROM `' . _DB_PREFIX_ . 'giftcardtemplate` t
				WHERE t.isdefault=1');
        if (!$result) {
            return false;
        }
        $id_gift_card_template = (int) $result['id_gift_card_template'];
        $gift_card_template = new GiftCardTemplate($id_gift_card_template);

        return $gift_card_template;
    }

    public function generateProductImage($id_product = null)
    {
        $id_shop = (int) Configuration::get('PS_SHOP_DEFAULT');
        $products_todo = [];
        $template_path = $this->img_dir . $this->id . '/';
        $default_language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $images_types = ImageType::getImagesTypes('products');
        if ($id_product == null) {
            $gift_card_ids = GiftCardProduct::getGiftCardsId();
            foreach ($gift_card_ids as $key => $row) {
                $products_todo[] = $key;
            }
        } else {
            $products_todo[] = (int) $id_product;
        }
        foreach ($products_todo as $key => $id_product) {
            $gift_card_product = new GiftCardProduct((int) $id_product);
            $image = Image::getCover($gift_card_product->id);
            $id_image = 0;
            if (!$image) {
                $image = new Image();
                $image->id_product = (int) $gift_card_product->id;
                $image->position = 1;
                $image->cover = true;
                $image->add();
                $id_image = $image->id;
            } else {
                $image = new Image($image['id_image']);
            }
            if (!$new_path = $image->getPathForCreation()) {
                return false;
            } elseif ($this->issvg) {
                $svgparams = [];
                $svgparams['price'] = round($gift_card_product->amount);
                $svg = GiftCardTools::buildTemplateSvgV2($this, $svgparams, $default_language->id);
                if (!GiftCardTools::resizeImageWithTemplate($svg, $new_path . '.jpg', 0, 1900, 620, 'jpg')) {
                    return false;
                }
                foreach ($images_types as $k => $image_type) {
                    if (!GiftCardTools::resizeImageWithTemplate(
                        $svg,
                        $new_path . '-' . Tools::stripslashes($image_type['name']) . '.jpg',
                        0,
                        $image_type['width'],
                        $image_type['height'],
                        'jpg'
                    )) {
                        return false;
                    }
                }
            } else {
                if (!ImageManager::resize($template_path . $this->id . '.jpg', $new_path . '.jpg')) {
                    return false;
                }
                foreach ($images_types as $k => $image_type) {
                    if (!ImageManager::resize(
                        $template_path . $this->id . '.jpg',
                        $new_path . '-' . Tools::stripslashes($image_type['name']) . '.' . $image->image_format,
                        $image_type['width'],
                        $image_type['height'],
                        'jpg'
                    )) {
                        return false;
                    }
                }
            }
            @unlink(_PS_TMP_IMG_DIR_ . 'product_' . $gift_card_product->id . '.jpg');
            @unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . $gift_card_product->id . '.jpg');
            @unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . $gift_card_product->id . '_' . $id_shop . '.jpg');
            @unlink(_PS_TMP_IMG_DIR_ . 'giftcardproduct_mini_' . $gift_card_product->id . '.jpg');
            @unlink(_PS_TMP_IMG_DIR_ . 'giftcardproduct_mini_' . $gift_card_product->id . '_' . $id_shop . '.jpg');
            @unlink(_PS_TMP_IMG_DIR_ . 'giftcardtemplatemini_' . $gift_card_product->id . '_' . $id_shop . '.jpg');
            Hook::exec('actionWatermark', [
                'id_image' => $image->id,
                'id_product' => $gift_card_product->id,
            ]);
        }
    }

    public function changeToDefault()
    {
        $result = Db::getInstance()->update('giftcardtemplate', [
            'isdefault' => 0,
        ]);
        if ($result) {
            $result &= Db::getInstance()->update('giftcardtemplate', [
                'isdefault' => 1,
            ], '`id_gift_card_template` = ' . (int) $this->id);
        }
        $result &= $this->generateProductImage();

        return $result;
    }

    public function createImgFolder()
    {
        if (!$this->id) {
            return false;
        }
        $template_dir = $this->img_dir . $this->id . '/';
        if (!file_exists($template_dir)) {
            // Apparently sometimes mkdir cannot set the rights, and sometimes chmod can't. Trying both.
            $success = @mkdir($template_dir, self::$access_rights, true);
            $chmod = @chmod($template_dir, self::$access_rights);
            // Create an index.php file in the new folder
            if (($success || $chmod)
                && !file_exists($template_dir . 'index.php')
                && file_exists($this->source_index)) {
                return @copy($this->source_index, $template_dir . 'index.php');
            }
        }

        return true;
    }
}
