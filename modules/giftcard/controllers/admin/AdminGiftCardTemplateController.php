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
class AdminGiftCardTemplateController extends ModuleAdminController
{
    /* public $asso_type = 'shop'; */
    public function __construct()
    {
        if (!$this->module) {
            $this->module = Module::getInstanceByName('giftcard');
        }
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->translator = Context::getContext()->getTranslator();
        }
        $this->bootstrap = true;
        $this->table = 'giftcardtemplate';
        $this->className = 'GiftCardTemplate';
        $this->fields_list = [
            'id_gift_card_template' => [
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 30,
            ],
            'image' => [
                'title' => $this->l('Image'),
                'align' => 'left',
                'callback' => 'printImage',
                'width' => 170,
            ],
            'issvg' => [
                'title' => $this->l('Vector'),
                'align' => 'center',
                'width' => 50,
                'callback' => 'printVector',
            ],
            'tags' => [
                'title' => $this->l('Tag'),
                'align' => 'left',
                'callback' => 'printTags',
                'filter_key' => 'tal!name',
            ],
            'template_name' => [
                'title' => $this->l('Name'),
                'align' => 'left',
                'filter_key' => 'tl!name',
            ],
            'id_lang_display' => [
                'title' => $this->l('Language'),
                'align' => 'center',
                'callback' => 'printLangDisplay',
                'width' => 70,
            ],
            'isdefault' => [
                'title' => $this->l('Default'),
                'align' => 'center',
                'width' => 70,
                'callback' => 'printDefaultIcon',
            ],
            'physicaluse' => [
                'title' => $this->l('Physical'),
                'align' => 'center',
                'width' => 70,
                'callback' => 'printPhysicalDisplay',
            ],
            'virtualuse' => [
                'title' => $this->l('eCard'),
                'align' => 'center',
                'width' => 70,
                'callback' => 'printVirtual',
            ],
            'active' => [
                'title' => $this->l('Status'),
                'width' => 70,
                'active' => 'status',
                'search' => false,
                'align' => 'center',
                'type' => 'bool',
                'orderby' => false,
            ],
        ];
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->fields_list['shopname'] = [
                'title' => $this->l('Default shop'),
                'filter_key' => 'shop!name',
            ];
        }
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ],
            'enableSelection' => [
                'text' => $this->l('Enable selection'),
            ],
            'disableSelection' => [
                'text' => $this->l('Disable selection'),
            ],
        ];
        $this->identifier = 'id_gift_card_template';
        $this->context = Context::getContext();
        $this->imagick = false;
        $this->_group = 'GROUP BY a.id_gift_card_template';
        if (extension_loaded('imagick')) {
            $this->imagick = true;
        }
        parent::__construct();
    }

    public function postProcess()
    {
        if (!$this->redirect_after) {
            parent::postProcess();
        }
        if ($this->display == 'edit' || $this->display == 'add') {
            $this->addJqueryUI([
                'ui.core',
                'ui.widget',
            ]);
            $this->addjQueryPlugin([
                'ajaxfileupload',
                'tagify',
                'colorpicker',
            ]);
            if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
                $this->addCSS([
                    _MODULE_DIR_ . 'giftcard/views/css/admingiftcardtemplatebootstrap.css',
                ]);
            } else {
                $this->addCSS([
                    _MODULE_DIR_ . 'giftcard/views/css/admingiftcardtemplateps15.css',
                ]);
            }
            if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
                $this->addJS([
                    _MODULE_DIR_ . 'giftcard/views/js/admingiftcardtemplatebootstrap.js',
                ]);
            } else {
                $this->addJS([
                    _MODULE_DIR_ . 'giftcard/views/js/admingiftcardtemplateps15.js',
                ]);
            }
        }
    }

    /**
     * @throws PrestaShopException
     */
    public function initProcess()
    {
        parent::initProcess();
        $access = Profile::getProfileAccess(
            $this->context->employee->id_profile,
            (int) Tab::getIdFromClassName('AdminGiftCardTemplate')
        );
        if (Tools::getIsset('duplicate' . $this->table)) {
            if ($access['edit'] === '1') {
                $this->action = 'duplicate';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }
        }
        if ($access['view'] === '1' && ($action = Tools::getValue('submitAction'))) {
            $this->action = $action;
        }
        if (Tools::isSubmit('changeDefaultVal') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_default_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
        if (Tools::isSubmit('changePhysicalUseVal') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_physical_use_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
        if (Tools::isSubmit('changeVirtualUseVal') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_virtual_use_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function processChangeDefaultVal()
    {
        $gift_card_template = new GiftCardTemplate($this->id_object);
        if (!Validate::isLoadedObject($gift_card_template)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value_default = $gift_card_template->isdefault ? 0 : 1;
        if ((int) $value_default == 0) {
            $this->errors[] = Tools::displayError('Default is required');
        } else {
            if (!$gift_card_template->changeToDefault()) {
                $this->errors[] = Tools::displayError('An error occurred while updating information.');
            }
        }
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function processChangePhysicalUseVal()
    {
        $gift_card_template = new GiftCardTemplate($this->id_object);
        if (!Validate::isLoadedObject($gift_card_template)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value = $gift_card_template->physicaluse ? 0 : 1;
        $gift_card_template->physicaluse = $value;
        $gift_card_template->update();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function processChangeVirtualUseVal()
    {
        $gift_card_template = new GiftCardTemplate($this->id_object);
        if (!Validate::isLoadedObject($gift_card_template)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value = $gift_card_template->virtualuse ? 0 : 1;
        $gift_card_template->virtualuse = $value;
        $gift_card_template->update();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function setMedia($isNewtheme = false)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            parent::setMedia();
        } else {
            parent::setMedia($isNewtheme);
        }
        $this->addCSS([
            _MODULE_DIR_ . 'giftcard/views/css/admin-ta-common.css',
            _MODULE_DIR_ . 'giftcard/views/css/icons/flaticon.css',
        ]);
        if (version_compare(_PS_VERSION_, '1.6.0', '<') === true) {
            $this->addCSS(_MODULE_DIR_ . 'giftcard/views/css/admin-ta-commonps15.css');
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');
        $this->_select = 'tl.`name` as template_name,tl.`name` as image,tl.`name` as tags, a.id_gift_card_template as id_giftcardtemplate';
        $this->_join = 'INNER JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_lang` tl
            ON a.id_gift_card_template = tl.id_gift_card_template AND tl.id_lang=' . (int) $this->context->language->id;
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
            $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_shop` gcts
                ON (a.`id_gift_card_template` = gcts.`id_gift_card_template`)';
            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.`id_shop` = gcts.`id_shop`) ';
            }
            $this->_where .= ' AND gcts.' . $this->identifier . ' IN (
						SELECT sa.' . $this->identifier . '
						FROM `' . _DB_PREFIX_ . $this->table . '_shop` sa
						WHERE sa.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')
					)';
        }
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'giftcardtemplate_tag` tat
            ON (tat.`id_gift_card_template` = a.`id_gift_card_template`)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'giftcardtag` tal
            ON (tat.`id_gift_card_tag` = tal.`id_gift_card_tag`
            AND tal.`id_lang` = ' . $this->context->language->id . ')';
        $this->_group = ' GROUP BY a.`id_gift_card_template`';
        $lists = parent::renderList();
        parent::initToolbar();
        $this->context->smarty->assign([
            'ta_gc_tab_select' => 'giftcardtemplate',
            'link' => $this->context->link,
        ]);
        $html = '';
        $html = $this->context->smarty->fetch(parent::getTemplatePath() . '/menu-top.tpl');
        $html .= $lists;
        $html .= $this->context->smarty->fetch(parent::getTemplatePath() . 'footer-module.tpl');

        return $html;
    }

    public function processDelete()
    {
        $gift_card_template = new GiftCardTemplate($this->id_object);
        if ($gift_card_template->isdefault) {
            $this->errors[] = Tools::displayError('It is a default template, you cannot delete this.');

            return false;
        }

        return parent::processDelete();
    }

    public function processAdd()
    {
        $languages = Language::getLanguages(false);
        $this->checkTemplate($languages);
        if (!empty($this->errors)) {
            $this->display = 'add';

            return false;
        }
        $this->object = new $this->className();
        $this->copyFromPost($this->object, $this->table);
        if ($this->object->add()) {
            $this->updateAssoShop($this->object->id);
            PrestaShopLogger::addLog(
                sprintf(
                    $this->l('%s edition', 'AdminTab', false, false),
                    $this->className
                ),
                1,
                null,
                $this->className,
                (int) $this->object->id,
                true,
                (int) $this->context->employee->id
            );
            if (empty($this->errors)) {
                if (!$this->updateTags($languages, $this->object)) {
                    $this->errors[] = Tools::displayError('An error occurred while adding tags.');
                } else {
                    $this->postImage('auto');
                    if (file_exists($this->object->img_dir . $this->object->id . '/' . $this->object->id . '.svg')) {
                        $this->object->issvg = true;
                    } else {
                        $this->object->issvg = false;
                    }
                    $this->object->update();
                    /* All good */
                    if (empty($this->errors)) {
                        // Save and preview
                        if (Tools::isSubmit('submitAddGiftCardTemplateAndPreview')) {
                            // $this->redirect_after = $this->getPreviewUrl($this->object);
                        }
                        // Save and stay on same form
                        if ($this->display == 'edit') {
                            $this->redirect_after = self::$currentIndex . '&id_gift_card_template=' .
                                (int) $this->object->id . '&updategiftcardtemplate&conf=3&key_tab=' .
                                Tools::safeOutput(Tools::getValue('key_tab')) . '&token=' . $this->token;
                        } else {
                            // Default behavior (save and back)
                            $this->redirect_after = self::$currentIndex . '&conf=3&token=' . $this->token;
                        }
                    }
                }
                if ($this->object->isdefault) {
                    $this->object->generateProductImage();
                }
            } else {
                $this->object->delete();
            }
            // if errors : stay on edit page
            $this->display = 'edit';
        } else {
            $this->errors[] = Tools::displayError('An error occurred while creating an object.') .
                ' <b>' . $this->table . '</b>';
        }

        return $this->object;
    }

    public function processUpdate()
    {
        $languages = Language::getLanguages(false);
        $this->checkTemplate($languages);
        if (!empty($this->errors)) {
            $this->display = 'edit';

            return false;
        }
        $id = (int) Tools::getValue('id_gift_card_template');
        /* Update an existing product */
        if (isset($id) && !empty($id)) {
            $object = new $this->className((int) $id);
            $this->object = $object;
            if (Validate::isLoadedObject($object)) {
                $this->copyFromPost($object, $this->table);
                if ($object->update()) {
                    $this->updateAssoShop($this->object->id);
                    PrestaShopLogger::addLog(
                        sprintf(
                            $this->l('%s edition', 'AdminTab', false, false),
                            $this->className
                        ),
                        1,
                        null,
                        $this->className,
                        (int) $this->object->id,
                        true,
                        (int) $this->context->employee->id
                    );
                    if (empty($this->errors)) {
                        if (!$this->updateTags($languages, $this->object)) {
                            $this->errors[] = Tools::displayError('An error occurred while adding tags.');
                        } else {
                            $this->postImage('auto');
                            if (file_exists($object->img_dir . $object->id . '/' . $object->id . '.svg')) {
                                $object->issvg = true;
                            } else {
                                $object->issvg = false;
                            }
                            $object->update();
                            /* All good */
                            if (empty($this->errors)) {
                                // Save and preview
                                if (Tools::isSubmit('submitAddGiftCardTemplateAndPreview')) {
                                    // $this->redirect_after = $this->getPreviewUrl($this->object);
                                }
                                // Save and stay on same form
                                if ($this->display == 'edit') {
                                    $this->redirect_after = self::$currentIndex .
                                        '&id_gift_card_template=' . (int) $this->object->id .
                                        '&updategiftcardtemplate&conf=3&currentFormTab=' .
                                        Tools::getValue('currentFormTab', 'informations') .
                                        '&key_tab=' . Tools::safeOutput(Tools::getValue('key_tab')) . '&token=' . $this->token;
                                } else {
                                    // Default behavior (save and back)
                                    $this->redirect_after = self::$currentIndex . '&conf=3&token=' . $this->token;
                                }
                            }
                        }
                        // Save and preview
                        if ($this->object->isdefault) {
                            $this->object->generateProductImage();
                        }
                    } else {
                        $this->display = 'edit';
                    }
                }
            } else {
                $this->errors[] = Tools::displayError('An error occurred while updating an object.') .
                    ' <b>' . $this->table . '</b> (' . Tools::displayError('The object cannot be loaded. ') . ')';
            }

            return $object;
        }
    }

    public function checkTemplate($languages)
    {
        foreach ($languages as $language) {
            if ($value = Tools::getValue('tags_' . $language['id_lang'])) {
                if (!Validate::isTagsList($value)) {
                    $this->errors[] = sprintf(
                        Tools::displayError('The tags list (%s) is invalid.'),
                        $language['name']
                    );
                }
            }
        }
    }

    /**
     * @throws PrestaShopException
     */
    public function updateTags($languages, $giftcardtemplate)
    {
        $tag_success = true;
        /* Reset all tags for THIS product */
        if (!GiftCardTag::deleteTagsForTemplate((int) $giftcardtemplate->id)) {
            $this->errors[] = Tools::displayError('An error occurred while attempting to delete previous tags.');
        }
        /* Assign tags to this product */
        foreach ($languages as $language) {
            if ($value = Tools::getValue('tags_' . $language['id_lang'])) {
                $tag_success &= GiftCardTag::addTags($language['id_lang'], (int) $giftcardtemplate->id, $value);
            }
        }
        if (!$tag_success) {
            $this->errors[] = Tools::displayError('An error occurred while adding tags.');
        }

        return $tag_success;
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }
        $this->toolbar_btn['save-and-stay'] = [
            'href' => '#',
            'desc' => $this->l('Save and Stay'),
        ];
        $current_object = $this->loadObject(true);
        $languages = Language::getLanguages();
        $availableVars = [];
        $have_custom_text = false;
        $have_custom_color = false;
        $image_template = null;
        if ($current_object->id) {
            $obj->tags = GiftCardTag::getTemplateTags($current_object->id);
            if ($obj->issvg) {
                $availableVars = GiftCardTools::getAvailableVars($this->object);
                foreach (array_keys($availableVars) as $key) {
                    if (strstr($key, 'var_text')) {
                        $have_custom_text = true;
                    }
                    if (strstr($key, 'var_color')) {
                        $have_custom_color = true;
                    }
                }
            }
            $template_path = $this->object->img_dir . $this->object->id . '/';
            if ($obj->issvg) {
                $template_file_path = $template_path . $this->object->id . '-' . $this->context->language->id . '.jpg';
            } else {
                $template_file_path = $template_path . $this->object->id . '.jpg';
            }
            $image = ImageManager::thumbnail(
                $template_file_path,
                'giftcardtemplate_' . (int) $this->object->id . '.jpg',
                350,
                'jpg'
            );
            $this->fields_value = [];
            $image_template = [
                'image' => $image ? $image : false,
                'size' => $image ? filesize($template_file_path) / 1000 : false,
            ];
        }
        $imagick = false;
        if (extension_loaded('imagick')) {
            $imagick = true;
        }
        $this->fields_form['submit'] = [
            'title' => $this->l('Save'),
            'class' => 'button',
        ];
        if (Shop::isFeatureActive() && count(Shop::getShops(true, null, true)) > 1) {
            $helper = new HelperForm();
            $helper->id = Tools::getValue($this->identifier, null);
            $helper->table = $this->table;
            $helper->identifier = $this->identifier;
            $this->context->smarty->assign('asso_shops', $helper->renderAssoShop());
        }
        $this->context->smarty->assign([
            'image_template' => $image_template,
            'haveCustomText' => $have_custom_text,
            'haveCustomColor' => $have_custom_color,
            'show_toolbar' => true,
            'availablevars' => $availableVars,
            'title' => $this->l('Gift Card template'),
            'id_lang_default' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'languages' => $languages,
            'currentIndex' => self::$currentIndex,
            'currentToken' => $this->token,
            'currentObject' => $current_object,
            'currentTab' => $this,
            'toolbar_btn' => $this->toolbar_btn,
            'toolbar_scroll' => $this->toolbar_scroll,
            'giftcard_img_dir' => _MODULE_DIR_ . 'giftcard/img/',
            'ps_version' => _PS_VERSION_,
            'imagick' => $imagick,
        ]);

        return parent::renderForm();
    }

    public function postImage($method = 'auto')
    {
        if (!$this->object->id) {
            return false;
        }
        /* if no upload */
        if (!isset($_FILES['image_template']['tmp_name']) || empty($_FILES['image_template']['tmp_name'])) {
            // default rebuild with customise field
            if ($this->object->issvg
                && file_exists($this->object->img_dir . $this->object->id . '/' . $this->object->id . '.svg')) {
                $this->templateBuildImages();
            }

            return false;
        }
        if (isset($_FILES['error'])) {
            $this->errors[] = sprintf(Tools::displayError('Error while uploading image; please change your server\'s settings. (Error code: %s)'),
                $_FILES['error']);

            return false;
        }
        if (self::isExt(
            $_FILES['image_template']['name'],
            ['svg']
        ) && !$this->imagick) {
            $this->errors[] = Tools::displayError('PHP extension Imagick is required for the svg format');

            return false;
        }
        if (!$this->object->createImgFolder()
            || (self::isExt(
                $_FILES['image_template']['name'],
                ['svg']
            )
                && !move_uploaded_file(
                    $_FILES['image_template']['tmp_name'],
                    $this->object->img_dir . $this->object->id . '/' . $this->object->id . '.svg'
                ))) {
            $this->errors[] = Tools::displayError('An error occurred during the image upload process.');

            return false;
        } else {
            if (self::isExt($_FILES['image_template']['name'], [
                'svg',
            ])) {
                $this->templateBuildImages(true);
            } else {
                $template_path = $this->object->img_dir . $this->object->id . '/';
                if (file_exists($this->object->img_dir . $this->object->id . '/' . $this->object->id . '.svg')) {
                    @unlink($this->object->img_dir . $this->object->id . '/' . $this->object->id . '.svg');
                }
                if ($error = ImageManager::validateUpload($_FILES['image_template'])) {
                    $this->errors[] = $error;
                } else {
                    if (!($tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS'))
                        || !move_uploaded_file($_FILES['image_template']['tmp_name'], $tmp_name)) {
                        $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                    } elseif (!ImageManager::resize($tmp_name, $template_path . $this->object->id . '.jpg')) {
                        $this->errors[] = Tools::displayError('An error occurred while copying the image.');
                    } elseif ($method == 'auto') {
                        $images_types = GiftCardTemplate::getImagesTypes();
                        foreach ($images_types as $k => $image_type) {
                            if (!ImageManager::resize(
                                $tmp_name,
                                $template_path . $this->object->id . '-' . Tools::stripslashes($image_type['name']) . '.jpg',
                                $image_type['width'],
                                $image_type['height'],
                                'jpg'
                            )) {
                                $this->errors[] = Tools::displayError('An error occurred while copying image:') .
                                    ' ' . Tools::stripslashes($image_type['name']);
                            }
                        }
                    }
                    @unlink($tmp_name);
                    $this->object->unlinkTmpImg();
                }
            }
        }

        return true;
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function templateBuildImages($firstbuild = false)
    {
        $template_path = $this->object->img_dir . $this->object->id . '/';
        $template_file_path = $template_path . $this->object->id . '.svg';
        if (!file_exists($template_file_path)) {
            $this->errors[] = Tools::displayError('The file template does not exist, file name :' .
                $template_file_path);
        } else {
            if ($firstbuild) {
                $available_vars = GiftCardTools::getAvailableVars($this->object);
                $this->object->initCustomVar();
                $this->object->updateCustomVar($available_vars);
                // init personnalize var
                $this->object->update();
            }
            $svg = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $svg[(int) $language['id_lang']] = GiftCardTools::buildTemplateSvgV2(
                    $this->object,
                    [],
                    (int) $language['id_lang']
                );
                if (!$svg[(int) $language['id_lang']]) {
                    $this->errors[] = Tools::displayError('Error building svg :' . $template_file_path);

                    return;
                }
            }
            // $images_types = ImageType::getImagesTypes('products');
            $images_types = GiftCardTemplate::getImagesTypes();
            $image_type_admin = [];
            $image_type_admin['name'] = '';
            $image_type_admin['width'] = 1600;
            $image_type_admin['height'] = 1052;
            $images_types[] = $image_type_admin;
            foreach ($images_types as $k => $image_type) {
                foreach ($languages as $language) {
                    if (!GiftCardTools::resizeImageWithTemplate(
                        $svg[(int) $language['id_lang']],
                        $template_path . $this->object->id . (!empty($image_type['name']) ?
                            '-' . Tools::stripslashes($image_type['name']) : '') .
                        '-' . $language['id_lang'] . '.jpg',
                        0,
                        $image_type['width'],
                        $image_type['height'],
                        'jpg'
                    )) {
                        $this->errors[] = Tools::displayError('An error occurred while copying image:') . ' ' .
                            Tools::stripslashes($image_type['name']);
                    }
                }
            }
            $this->object->unlinkTmpImg($languages);
        }
    }

    public static function isExt($filename, $exts)
    {
        $authorized_extensions = $exts;
        $name_explode = explode('.', $filename);
        if (count($name_explode) >= 2) {
            $current_extension = Tools::strtolower($name_explode[count($name_explode) - 1]);
            if (!in_array($current_extension, $authorized_extensions)) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function processGenerateImg()
    {
        $this->object = new $this->className();
        $this->copyFromGet($this->object, $this->table);
        if (isset($this->object->id_gift_card_template) && (int) $this->object->id_gift_card_template > 0) {
            $this->object->id = (int) $this->object->id_gift_card_template;
        } else {
            return false;
        }
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        $svg = GiftCardTools::buildTemplateSvgV2($this->object, [], (int) Tools::getValue('id_lang_preview', 0));
        $im = new Imagick();
        if (!(Tools::isSubmit('pdf_image_only') && (int) Tools::getValue('pdf_image_only'))) {
            $im->setResolution(200, 200);
        }
        $im->readImageBlob($svg);
        $im->setImageFormat('png32');
        header('Content-type: image/png');
        exit($im);
    }

    public function printTags($value, $gift_card_template)
    {
        $tagshtml = '';
        if ((int) $gift_card_template['id_gift_card_template'] > 0) {
            $tags = GiftCardTag::getTemplateTags((int) $gift_card_template['id_gift_card_template']);
            if ($tags && count($tags) > 0) {
                foreach ($tags as $tag_l) {
                    foreach ($tag_l as $tag) {
                        $tagshtml .= '<span class="color_field" style="display: inline-block;' .
                            'padding: 3px 5px;margin: 3px;border-radius: 2px;border: 1px solid #EEE;' .
                            'background-color: #FFEBA1;color: #888;">';
                        $tagshtml .= $tag;
                        $tagshtml .= '</span>';
                    }
                }
            }
        }

        return $tagshtml;
    }

    public function printImage($value, $gift_card_template)
    {
        $id = (int) $gift_card_template['id_gift_card_template'];
        $template_path = _PS_ROOT_DIR_ . '/modules/giftcard/img/templates/' . (int) $id . '/';
        if ((int) $gift_card_template['issvg']) {
            $id_lang = (int) $this->context->language->id;
            $template_file_path = $template_path . (int) $id . '-' . $id_lang . '.jpg';
        } else {
            $template_file_path = $template_path . (int) $id . '.jpg';
        }
        if ((int) $gift_card_template['id_gift_card_template'] > 0 && file_exists($template_file_path)) {
            if ((int) $gift_card_template['issvg']) {
                $image = ImageManager::thumbnail(
                    $template_file_path,
                    'giftcardtemplatemini_' . (int) $id . '-' . $id_lang . '.jpg',
                    100,
                    'jpg'
                );
            } else {
                $image = ImageManager::thumbnail(
                    $template_file_path,
                    'giftcardtemplatemini_' . (int) $id . '.jpg',
                    100,
                    'jpg'
                );
            }

            return $image;
        }

        return '';
    }

    public function processDuplicate()
    {
        $giftcardtemplate = GiftCardTemplate::duplicate((int) Tools::getValue('id_gift_card_template'));
        if (!$giftcardtemplate) {
            $this->errors[] = $this->l('An error occurred while creating an object.');
        }
    }

    public function printVector($value, $gift_card_template)
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
            if ((int) $value > 0) {
                return '<span class="badge" style="background-color:LimeGreen">' . $this->l('Yes') . '</span>';
            }

            return '<span class="badge" style="background-color:DarkGrey;">' . $this->l('No') . '</span>';
        } else {
            if ((int) $value > 0) {
                return '<span class="color_field" style="background-color:LimeGreen;' .
                    'width:15px;height:15px;color:#FFF">' . $this->l('Yes') . '</span>';
            }

            return '<span class="color_field" style="background-color:DarkGrey;width:15px;height:15px;color:#FFF">' .
                $this->l('No') . '</span>';
        }
    }

    public function printLangDisplay($value, $gift_card_template)
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
            if ((int) $value > 0) {
                $language = new Language((int) $value);

                return '<span class="badge" style="background-color:black;">' . $language->iso_code . '</span>';
            }

            return '<span class="badge" style="background-color:black;">' . $this->l('ALL') . '</span>';
        } else {
            if ((int) $value > 0) {
                $language = new Language((int) $value);

                return '<span class="color_field" style="background-color:black;width:15px;height:15px;color:#FFF">' .
                    $language->iso_code . '</span>';
            }

            return '<span class="color_field" style="background-color:black;width:15px;height:15px;color:#FFF">' .
                $this->l('ALL') . '</span>';
        }
    }

    public function printDefaultIcon($value, $gift_card_template)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return '<a class="list-action-enable ' . ($value ? 'action-enabled' : 'action-disabled') .
                '" href="index.php?tab=AdminGiftCardTemplate&id_gift_card_template=' .
                (int) $gift_card_template['id_gift_card_template'] . '&changeDefaultVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCardTemplate') . '">
				' . ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=AdminGiftCardTemplate&id_gift_card_template=' .
                (int) $gift_card_template['id_gift_card_template'] . '&changeDefaultVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCardTemplate') . '">
            ' . ((int) $value ? '<img src="../img/admin/enabled.gif" alt="' .
                    $this->l('Default') . '" title="' . $this->l('Default') . '"/>' :
                    '<img src="../img/admin/disabled.gif" alt="' .
                    $this->l('Not Default') . '" title="' . $this->l('Not Default') . '"/>') . '</a>';
        }
    }

    public function printPhysicalDisplay($value, $gift_card_template)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return '<a class="list-action-enable ' .
                ($value ? 'action-enabled' : 'action-disabled') .
                '" href="index.php?tab=AdminGiftCardTemplate&id_gift_card_template=' .
                (int) $gift_card_template['id_gift_card_template'] . '&changePhysicalUseVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCardTemplate') . '">
				' . ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=AdminGiftCardTemplate&id_gift_card_template=' .
                (int) $gift_card_template['id_gift_card_template'] . '&changePhysicalUseVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCardTemplate') . '">
            ' . ((int) $value ? '<img src="../img/admin/enabled.gif" alt="' .
                    $this->l('Can be used in the mode "send by post"') . '" title="' .
                    $this->l('Can be used in the mode "send by post"') . '"/>' :
                    '<img src="../img/admin/disabled.gif" alt="' .
                    $this->l('Can\'t be used in the mode "send by post"') .
                    '" title="' . $this->l('Can\'t be used in the mode "send by post"') . '"/>') . '</a>';
        }
    }

    public function printVirtual($value, $gift_card_template)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return '<a class="list-action-enable ' .
                ($value ? 'action-enabled' : 'action-disabled') .
                '" href="index.php?tab=AdminGiftCardTemplate&id_gift_card_template=' .
                (int) $gift_card_template['id_gift_card_template'] .
                '&changeVirtualUseVal&token=' . Tools::getAdminTokenLite('AdminGiftCardTemplate') . '">
				' . ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=AdminGiftCardTemplate&id_gift_card_template=' .
                (int) $gift_card_template['id_gift_card_template'] . '&changeVirtualUseVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCardTemplate') . '">
            ' . ((int) $value ? '<img src="../img/admin/enabled.gif" alt="' .
                    $this->l('Can be used in the mode "send by email"') .
                    '" title="' . $this->l('Can be used in the mode "send by email"') . '"/>' :
                    '<img src="../img/admin/disabled.gif" alt="' .
                    $this->l('Can\'t be used in the mode "send by email"') .
                    '" title="' . $this->l('Can\'t be used in the mode "send by email"') . '"/>') . '</a>';
        }
    }

    private function copyFromGet(&$object, $table)
    {
        /* Classical fields */
        foreach ($_GET as $key => $value) {
            if (property_exists($object, $key) && $key != 'id_' . $table) {
                $object->{$key} = $value;
            }
        }
        /* Multilingual fields */
        $rules = call_user_func([
            get_class($object),
            'getValidationRules',
        ], get_class($object));
        if (count($rules['validateLang'])) {
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                foreach (array_keys($rules['validateLang']) as $field) {
                    if (Tools::getIsset($field . '_' . (int) $language['id_lang'])) {
                        $object->{$field}[(int) $language['id_lang']] = Tools::getValue($field . '_' .
                            (int) $language['id_lang']);
                    }
                }
            }
        }
    }

    protected function updateAssoShop($id_object)
    {
        if (!Shop::isFeatureActive()) {
            return;
        }
        $assos_data = $this->getSelectedAssoShop($this->table, $id_object);
        // Get list of shop id we want to exclude from asso deletion
        $exclude_ids = $assos_data;
        foreach (Db::getInstance()->executeS('SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop') as $row) {
            if (!$this->context->employee->hasAuthOnShop($row['id_shop'])) {
                $exclude_ids[] = $row['id_shop'];
            }
        }
        Db::getInstance()->delete(
            $this->table . '_shop',
            '`' . $this->identifier . '` = ' . (int) $id_object . ($exclude_ids ?
                ' AND id_shop NOT IN (' . implode(', ', $exclude_ids) . ')' : '')
        );
        $insert = [];
        foreach ($assos_data as $id_shop) {
            $insert[] = [
                $this->identifier => $id_object,
                'id_shop' => (int) $id_shop,
            ];
        }

        return Db::getInstance()->insert($this->table . '_shop', $insert, false, true, Db::INSERT_IGNORE);
    }

    protected function getSelectedAssoShop($table)
    {
        if (!Shop::isFeatureActive()) {
            return [];
        }
        $shops = Shop::getShops(true, null, true);
        if (count($shops) == 1 && isset($shops[0])) {
            return [
                $shops[0],
                'shop',
            ];
        }
        $assos = [];
        if (Tools::isSubmit('checkBoxShopAsso_' . $table)) {
            foreach (Tools::getValue('checkBoxShopAsso_' . $table) as $id_shop => $value) {
                $assos[] = (int) $id_shop;
            }
        } else {
            // if we do not have the checkBox multishop, we can have an admin with only one shop and being in multishop
            if (Shop::getTotalShops(false) == 1) {
                $assos[] = (int) Shop::getContextShopID();
            }
        }

        return $assos;
    }
}
