<?php
/**
 * 2007-2020 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @copyright 2007-2020 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/*
 * @since   1.5.0
 */
 
include_once  _PS_OVERRIDE_DIR_.'/modules/ps_imageslider/Ps_HomeSlideOverride.php';

class Ps_ImageSliderOverride extends Ps_ImageSlider 
{
    public function enable($force_all = false)
    {
        $this->installTabs();
        return parent::enable($force_all) && $this->configExtra()&& $this->registerHook('displayBackofficeHeader');
    }
    public function disable($force_all = false)
    {
       $this->uninstallTabs();

        $result = true;

        $result &= parent::disable($force_all);

        return (bool) $result;
    }
    /**
     * Create Tab 
     */ 
     public function installTabs()
     {
         $installTabCompleted = true;
 
         foreach ($this->getTabs() as $tab) {
             try {
                 $installTabCompleted = $installTabCompleted && $this->installCustomTab(
                     $tab['className'],
                     $tab['parent'],
                     $tab['name'],
                     $tab['module'],
                     $tab['active'],
                     $tab['icon']
                 );
             } catch (Exception $e) {  
                 return false;
             }
         }
 
         return $installTabCompleted;
     }
     public function uninstallTabs()
     {
         $uninstallTabCompleted = true;
 
         foreach ($this->getTabs() as $tab) {
             try {
                 $uninstallTabCompleted = $installTabCompleted && $this->uninstallCustomTab(
                     $tab['className']
                 );
             } catch (Exception $e) {  
                 return false;
             }
         }
 
         return $uninstallTabCompleted;
     }
     public function installCustomTab($className, $parent, $name, $module, $active, $icon)
     {
         if (Tab::getIdFromClassName($className)) {
             return true;
         }
 
         $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);
 
         $moduleTab = new Tab();
         $moduleTab->class_name = $className;
         $moduleTab->id_parent = $idParent;
         $moduleTab->module = $module;
         $moduleTab->active = $active;
         if (property_exists($moduleTab, 'icon')) {
             $moduleTab->icon = $icon;
         }
 
         $languages = Language::getLanguages(true);
         foreach ($languages as $language) {
             $moduleTab->name[$language['id_lang']] = $name;
         }
 
         return $moduleTab->add();
     }
     /**
      * Remove Tabs module in Dashboard
      * @param $class_name string name Tab
      * @return bool
      * @throws
      * @throws
      */
     public function uninstallCustomTab($class_name)
     {
         if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
             $tab = new Tab($tab_id);
             return $tab->delete();
         } else {
             return false;
         } 
     }
     public function getTabs()
     {
         return [
             [
                 'className' => 'AdminConfigureSlides',
                 'parent' => 'CONFIGURE',
                 'name' => $this->l('HP Slider'),
                 'module' => $this->name,
                 'active' => true,
                 'icon' => 'extension',
             ],
         ];
         
     }
    public function hookDisplayBackofficeHeader($params)
    { 
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == 'ps_imageslider') {
            $this->context->controller->addJs($this->_path.'views/js/back.js');
        }
    }
    private function configExtra(){
        $sql = array();
        $columnsLang = array('image_mobile', 'btn_1_title', 'btn_1_url', 'btn_2_title', 'btn_2_url');
        $columnsSlider = array('vimeo_video','type_video','content_position','date_start','date_end');
    
        foreach ($columnsLang as $column) {
            if (Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'homeslider_slides_lang` LIKE \'' . $column . '\'') == false) {
                $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'homeslider_slides_lang` ADD `' . $column . '` varchar(255)  NULL';
            }
        }
    
        foreach ($columnsSlider as $column) {
            if (Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'homeslider_slides` LIKE \'' . $column . '\'') == false) {
                if ($column == 'type_video') {
                    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'homeslider_slides` ADD `' . $column . '` varchar(255) DEFAULT "video_type_image" NULL';
                } elseif ($column == 'content_position') {
                    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'homeslider_slides` ADD `' . $column . '` varchar(255) DEFAULT "center" NULL';
                } elseif ($column == 'date_start' || $column == 'date_end') {
                    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'homeslider_slides` ADD `' . $column . '` datetime NULL';
                }
                 else {
                    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'homeslider_slides` ADD `' . $column . '` varchar(255) NULL';
                }
            }
        }
    
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }
        return true;
    }
    
    public function getAddFieldsValues()
    {
        $fields = [];

        if (Tools::isSubmit('id_slide') && $this->slideExists((int) Tools::getValue('id_slide'))) {
            $slide = new Ps_HomeSlideOverride((int) Tools::getValue('id_slide'));
            $fields['id_slide'] = (int) Tools::getValue('id_slide', $slide->id);
        } else {
            $slide = new Ps_HomeSlideOverride();
        }

        $fields['active_slide'] = Tools::getValue('active_slide', $slide->active);
        $fields['has_picture'] = true;

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields['image'][$lang['id_lang']] = Tools::getValue('image_' . (int) $lang['id_lang']);
            $fields['title'][$lang['id_lang']] = Tools::getValue(
                'title_' . (int) $lang['id_lang'],
                isset($slide->title[$lang['id_lang']]) ? $slide->title[$lang['id_lang']] : ''
            );
            $fields['url'][$lang['id_lang']] = Tools::getValue(
                'url_' . (int) $lang['id_lang'],
                isset($slide->url[$lang['id_lang']]) ? $slide->url[$lang['id_lang']] : ''
            );
            $fields['legend'][$lang['id_lang']] = Tools::getValue(
                'legend_' . (int) $lang['id_lang'],
                isset($slide->legend[$lang['id_lang']]) ? $slide->legend[$lang['id_lang']] : ''
            );
            $fields['description'][$lang['id_lang']] = Tools::getValue(
                'description_' . (int) $lang['id_lang'],
                isset($slide->description[$lang['id_lang']]) ? $slide->description[$lang['id_lang']] : ''
            );
            $fields['btn_1_title'][$lang['id_lang']] = Tools::getValue(
                'btn_1_title_' . (int) $lang['id_lang'],
                isset($slide->btn_1_title[$lang['id_lang']]) ? $slide->btn_1_title[$lang['id_lang']] : ''
            );
            $fields['btn_1_url'][$lang['id_lang']] = Tools::getValue(
                'btn_1_url_' . (int) $lang['id_lang'],
                isset($slide->btn_1_url[$lang['id_lang']]) ? $slide->btn_1_url[$lang['id_lang']] : ''
            );
            $fields['btn_2_title'][$lang['id_lang']] = Tools::getValue(
                'btn_2_title_' . (int) $lang['id_lang'],
                isset($slide->btn_2_title[$lang['id_lang']]) ? $slide->btn_2_title[$lang['id_lang']] : ''
            );
            $fields['btn_2_url'][$lang['id_lang']] = Tools::getValue(
                'btn_2_url_' . (int) $lang['id_lang'],
                isset($slide->btn_2_url[$lang['id_lang']]) ? $slide->btn_2_url[$lang['id_lang']] : ''
            );
        }
        $fields['vimeo_video'] =Tools::getValue(
            'vimeo_video',
            isset($slide->vimeo_video) ? $slide->vimeo_video : ''
        );
        $fields['type_video'] =Tools::getValue(
            'type_video',
            isset($slide->type_video) ? $slide->type_video : 'video_type_image'
        );
        $fields['content_position'] = Tools::getValue(
            'content_position',
            isset($slide->content_position) ? $slide->content_position : 'center'
        );
        $fields['date_start'] = Tools::getValue(
            'date_start',
            isset($slide->date_start) && $slide->date_start != '0000-00-00 00:00:00' ? $slide->date_start : ''
        );
        $fields['date_end'] = Tools::getValue(
            'date_end',
            isset($slide->date_end) && $slide->date_end != '0000-00-00 00:00:00' ? $slide->date_end : ''
        );
        return $fields;
    }
    protected function _postProcess()
    {
        $errors = [];
        $shop_context = Shop::getContext();

        /* Processes Slider */
        if (Tools::isSubmit('submitSlider')) {
            $shop_groups_list = [];
            $shops = Shop::getContextListShopID();
            $res = true;

            foreach ($shops as $shop_id) {
                $shop_group_id = (int) Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                $res &= Configuration::updateValue('HOMESLIDER_SPEED', (int) Tools::getValue('HOMESLIDER_SPEED'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', (int) Tools::getValue('HOMESLIDER_PAUSE_ON_HOVER'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('HOMESLIDER_WRAP', (int) Tools::getValue('HOMESLIDER_WRAP'), false, $shop_group_id, $shop_id);
            }

            /* Update global shop context if needed*/
            switch ($shop_context) {
                case Shop::CONTEXT_ALL:
                    $res &= Configuration::updateValue('HOMESLIDER_SPEED', (int) Tools::getValue('HOMESLIDER_SPEED'));
                    $res &= Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', (int) Tools::getValue('HOMESLIDER_PAUSE_ON_HOVER'));
                    $res &= Configuration::updateValue('HOMESLIDER_WRAP', (int) Tools::getValue('HOMESLIDER_WRAP'));
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res &= Configuration::updateValue('HOMESLIDER_SPEED', (int) Tools::getValue('HOMESLIDER_SPEED'), false, $shop_group_id);
                            $res &= Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', (int) Tools::getValue('HOMESLIDER_PAUSE_ON_HOVER'), false, $shop_group_id);
                            $res &= Configuration::updateValue('HOMESLIDER_WRAP', (int) Tools::getValue('HOMESLIDER_WRAP'), false, $shop_group_id);
                        }
                    }
                    break;
                case Shop::CONTEXT_GROUP:
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res &= Configuration::updateValue('HOMESLIDER_SPEED', (int) Tools::getValue('HOMESLIDER_SPEED'), false, $shop_group_id);
                            $res &= Configuration::updateValue('HOMESLIDER_PAUSE_ON_HOVER', (int) Tools::getValue('HOMESLIDER_PAUSE_ON_HOVER'), false, $shop_group_id);
                            $res &= Configuration::updateValue('HOMESLIDER_WRAP', (int) Tools::getValue('HOMESLIDER_WRAP'), false, $shop_group_id);
                        }
                    }
                    break;
            }

            $this->clearCache();

            if (!$res) {
                $errors[] = $this->displayError($this->getTranslator()->trans('The configuration could not be updated.', [], 'Modules.Imageslider.Admin'));
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=6&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }
        } elseif (Tools::isSubmit('changeStatus') && Tools::isSubmit('id_slide')) {
            $slide = new Ps_HomeSlideOverride((int) Tools::getValue('id_slide'));
            if ($slide->active == 0) {
                $slide->active = 1;
            } else {
                $slide->active = 0;
            }
            $res = $slide->update();
            $this->clearCache();
            $this->_html .= ($res ? $this->displayConfirmation($this->getTranslator()->trans('Configuration updated', [], 'Admin.Notifications.Success')) : $this->displayError($this->getTranslator()->trans('The configuration could not be updated.', [], 'Modules.Imageslider.Admin')));
        } elseif (Tools::isSubmit('submitSlide')) {
            /* Sets ID if needed */
            if (Tools::getValue('id_slide')) {
                $slide = new Ps_HomeSlideOverride((int) Tools::getValue('id_slide'));
                if (!Validate::isLoadedObject($slide)) {
                    $this->_html .= $this->displayError($this->getTranslator()->trans('Invalid slide ID', [], 'Modules.Imageslider.Admin'));

                    return false;
                }
            } else {
                $slide = new Ps_HomeSlideOverride();
                /* Sets position */
                $slide->position = (int) $this->getNextPosition();
            }
            /* Sets active */
            $slide->active = (int) Tools::getValue('active_slide');

            
            $slide->vimeo_video = Tools::getValue('vimeo_video');

            $slide->type_video = Tools::getValue('type_video');
            $slide->content_position = Tools::getValue('content_position');
            
            // Handle date fields
            $date_start = Tools::getValue('date_start');
            $slide->date_start = (!empty($date_start) && $date_start != '0000-00-00 00:00:00') ? $date_start : null;
            
            $date_end = Tools::getValue('date_end');
            $slide->date_end = (!empty($date_end) && $date_end != '0000-00-00 00:00:00') ? $date_end : null;
            /* Sets each langue fields */
            $languages = Language::getLanguages(false);

            foreach ($languages as $language) {
                $slide->title[$language['id_lang']] = Tools::getValue('title_' . $language['id_lang']);
                $slide->url[$language['id_lang']] = Tools::getValue('url_' . $language['id_lang']);
                $slide->legend[$language['id_lang']] = Tools::getValue('legend_' . $language['id_lang']);
                $slide->description[$language['id_lang']] = Tools::getValue('description_' . $language['id_lang']);
                $slide->btn_1_title[$language['id_lang']] = Tools::getValue('btn_1_title_' . $language['id_lang']);
                $slide->btn_1_url[$language['id_lang']] = Tools::getValue('btn_1_url_' . $language['id_lang']);
                $slide->btn_2_title[$language['id_lang']] = Tools::getValue('btn_2_title_' . $language['id_lang']);
                $slide->btn_2_url[$language['id_lang']] = Tools::getValue('btn_2_url_' . $language['id_lang']);
                /* Uploads image and sets slide */
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_' . $language['id_lang']]['name'], '.'), 1));
                if (isset($_FILES['image_' . $language['id_lang']]) && !empty($_FILES['image_' . $language['id_lang']]) && strlen($_FILES['image_' . $language['id_lang']]['tmp_name']) > 0) {
                    $imagesize = @getimagesize($_FILES['image_' . $language['id_lang']]['tmp_name']);
                } else {
                    $imagesize = null;
                }
                if (isset($_FILES['image_' . $language['id_lang']]) &&
                    isset($_FILES['image_' . $language['id_lang']]['tmp_name']) &&
                    !empty($_FILES['image_' . $language['id_lang']]['tmp_name']) &&
                    !empty($imagesize) &&
                    in_array(
                        Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), [
                            'jpg',
                            'gif',
                            'jpeg',
                            'png',
                        ]
                    ) &&
                    in_array($type, ['jpg', 'gif', 'jpeg', 'png'])
                ) {
                    $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                    $salt = sha1(microtime());
                    if ($error = ImageManager::validateUpload($_FILES['image_' . $language['id_lang']])) {
                        $errors[] = $error;
                    } elseif (!$temp_name || !move_uploaded_file($_FILES['image_' . $language['id_lang']]['tmp_name'], $temp_name)) {
                        return false;
                    } elseif (!ImageManager::resize($temp_name, _PS_MODULE_DIR_ . $this->name . '/images/' . $salt . '_' . $_FILES['image_' . $language['id_lang']]['name'], null, null, $type)) {

                        $errors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process.', [], 'Admin.Notifications.Error'));
                    }
                    if (file_exists($temp_name)) {
                        @unlink($temp_name);
                    }
                    $slide->image[$language['id_lang']] = $salt . '_' . $_FILES['image_' . $language['id_lang']]['name'];
                } elseif (Tools::getValue('image_old_' . $language['id_lang']) != '') {
                    $slide->image[$language['id_lang']] = Tools::getValue('image_old_' . $language['id_lang']);
                }
                /* Uploads image Mobile and sets slide */
                $type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_mobile_' . $language['id_lang']]['name'], '.'), 1));
                if (isset($_FILES['image_mobile_' . $language['id_lang']]) && !empty($_FILES['image_mobile_' . $language['id_lang']]) && strlen($_FILES['image_mobile_' . $language['id_lang']]['tmp_name']) > 0) {
                    $imagesize = @getimagesize($_FILES['image_mobile_' . $language['id_lang']]['tmp_name']);
                } else {
                    $imagesize = null;
                }
                if (isset($_FILES['image_mobile_' . $language['id_lang']]) &&
                    isset($_FILES['image_mobile_' . $language['id_lang']]['tmp_name']) &&
                    !empty($_FILES['image_mobile_' . $language['id_lang']]['tmp_name']) &&
                    !empty($imagesize) &&
                    in_array(
                        Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), [
                            'jpg',
                            'gif',
                            'jpeg',
                            'png',
                        ]
                    ) &&
                    in_array($type, ['jpg', 'gif', 'jpeg', 'png'])
                ) {
                    $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                    $salt = sha1(microtime());
                    if ($error = ImageManager::validateUpload($_FILES['image_mobile_' . $language['id_lang']])) {
                        $errors[] = $error;
                    } elseif (!$temp_name || !move_uploaded_file($_FILES['image_mobile_' . $language['id_lang']]['tmp_name'], $temp_name)) {
                        return false;
                    } elseif (!ImageManager::resize($temp_name,_PS_MODULE_DIR_ . $this->name . '/images/' . $salt . '_' . $_FILES['image_mobile_' . $language['id_lang']]['name'], null, null, $type)) {
                        $errors[] = $this->displayError($this->getTranslator()->trans('An error occurred during the image upload process. xxxx', [], 'Admin.Notifications.Error'));
                       
                    }
                    
                    if (file_exists($temp_name)) { 
                        @unlink($temp_name);
                    }
                    $slide->image_mobile[$language['id_lang']] = $salt . '_' . $_FILES['image_mobile_' . $language['id_lang']]['name'];
                } elseif (Tools::getValue('image_old_' . $language['id_lang']) != '') {
                    $slide->image_mobile[$language['id_lang']] = Tools::getValue('image_old_' . $language['id_lang']);
                }
            }

            /* Processes if no errors  */
            if (!$errors) {
                /* Adds */
                if (!Tools::getValue('id_slide')) {
                    if (!$slide->add()) {
                        $errors[] = $this->displayError($this->getTranslator()->trans('The slide could not be added.', [], 'Modules.Imageslider.Admin'));
                    }
                } elseif (!$slide->update()) {
                    $errors[] = $this->displayError($this->getTranslator()->trans('The slide could not be updated.', [], 'Modules.Imageslider.Admin'));
                }
                $this->clearCache();
            }
        } elseif (Tools::isSubmit('delete_id_slide')) {
            $slide = new Ps_HomeSlideOverride((int) Tools::getValue('delete_id_slide'));
            $res = $slide->delete();
            $this->clearCache();
            if (!$res) {
                $this->_html .= $this->displayError('Could not delete.');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=1&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }
        }

        /* Display errors if needed */
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
        } elseif (Tools::isSubmit('submitSlide') && Tools::getValue('id_slide')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=4&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        } elseif (Tools::isSubmit('submitSlide')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=3&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }
    }
    public function renderAddForm()
    {
        if (Tools::isSubmit('id_slide') && $this->slideExists((int) Tools::getValue('id_slide'))) {
            $slide = new Ps_HomeSlideOverride((int) Tools::getValue('id_slide')); 
            $images = $slide->image[$this->context->language->id] ;
            $images_mobile = $slide->image_mobile[$this->context->language->id];
        }
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->getTranslator()->trans('Slide information', [], 'Modules.Imageslider.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Type video'),
                        'name' => 'type_video',
                        'multiple' => false,  
                        'options' => [
                            'query' => $this->getAvailableVideoType(),
                            'id' => 'id_video_type',
                            'name' => 'type_name',
                        ],
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Code video', [], 'Modules.Imageslider.Admin'),
                        'name' => 'vimeo_video',  
                    ],
                    [
                        'type' => 'html',
                        'name' => 'separator',
                        'html_content' => '<img src="/modules/ps_imageslider/images/'.$images.'" class="img-thumbnail">',
                    ],
                    [
                        'type' => 'file_lang',
                        'label' => $this->getTranslator()->trans('Image', [], 'Admin.Global'),
                        'name' => 'image',
                        'required' => true,
                        'lang' => true,
                        'desc' => $this->getTranslator()->trans('Maximum image size: %s, recommended dimensions are 1920 x 1920 px..', [ini_get('upload_max_filesize')], 'Admin.Global'),
                    ],
                    [
                        'type' => 'html',
                        'name' => 'separator',
                        'html_content' => '<img src="/modules/ps_imageslider/images/'.$images_mobile.'" class="img-thumbnail">',
                    ],
                    [
                        'type' => 'file_lang',
                        'label' => $this->getTranslator()->trans('Image Mobile', [], 'Admin.Global'),
                        'name' => 'image_mobile',
                        'required' => true,
                        'lang' => true,
                        'desc' => $this->getTranslator()->trans('Maximum image size: %s, recommended dimensions are 1920 x 1920 px..', [ini_get('upload_max_filesize')], 'Admin.Global'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Title', [], 'Admin.Global'),
                        'name' => 'title',
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Target URL', [], 'Modules.Imageslider.Admin'),
                        'name' => 'url',
                        'required' => true,
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Caption', [], 'Modules.Imageslider.Admin'),
                        'name' => 'legend',
                        'lang' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->getTranslator()->trans('Description', [], 'Admin.Global'),
                        'name' => 'description',
                        'autoload_rte' => true,
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Boutton 1 titre', [], 'Modules.Imageslider.Admin'),
                        'name' => 'btn_1_title',  
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Boutton 1 lien', [], 'Modules.Imageslider.Admin'),
                        'name' => 'btn_1_url',  
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Boutton 2 titre', [], 'Modules.Imageslider.Admin'),
                        'name' => 'btn_2_title',  
                        'lang' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Boutton 2 lien', [], 'Modules.Imageslider.Admin'),
                        'name' => 'btn_2_url',  
                        'lang' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->getTranslator()->trans('Content Position', [], 'Modules.Imageslider.Admin'),
                        'name' => 'content_position',
                        'options' => [
                            'query' => [
                                ['id' => 'center', 'name' => $this->l('Center')],
                                ['id' => 'left', 'name' => $this->l('Left')],
                                ['id' => 'right', 'name' => $this->l('Right')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'required' => true,
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->getTranslator()->trans('Start Date', [], 'Modules.Imageslider.Admin'),
                        'name' => 'date_start',
                        'desc' => $this->getTranslator()->trans('Leave empty to show slide immediately', [], 'Modules.Imageslider.Admin'),
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->getTranslator()->trans('End Date', [], 'Modules.Imageslider.Admin'),
                        'name' => 'date_end',
                        'desc' => $this->getTranslator()->trans('Leave empty to show slide indefinitely', [], 'Modules.Imageslider.Admin'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->getTranslator()->trans('Enabled', [], 'Admin.Global'),
                        'name' => 'active_slide',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->getTranslator()->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        if (Tools::isSubmit('id_slide') && $this->slideExists((int) Tools::getValue('id_slide'))) {
            $slide = new Ps_HomeSlideOverride((int) Tools::getValue('id_slide'));
            $fields_form['form']['input'][] = ['type' => 'hidden', 'name' => 'id_slide'];

            $has_picture = true;

            foreach (Language::getLanguages(false) as $lang) {
                if (!isset($slide->image[$lang['id_lang']])) {
                    $has_picture &= false;
                }
            }

            if ($has_picture) {
                $fields_form['form']['input'][] = ['type' => 'hidden', 'name' => 'has_picture'];
            }
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSlide';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = [
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => [
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code,
            ],
            'fields_value' => $this->getAddFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'image_baseurl' => $this->_path . 'images/',
        ];

        $helper->override_folder = '/';

        $languages = Language::getLanguages(false);

        if (count($languages) > 1) {
            return $this->getMultiLanguageInfoMsg() . $helper->generateForm([$fields_form]);
        } else {
            return $helper->generateForm([$fields_form]);
        }
    }
    public function getSlides($active = null, $forceShowAll = false)
    {
        $this->context = Context::getContext();
        $id_shop = $this->context->shop->id;
        $id_lang = $this->context->language->id;

        $currentDate = date('Y-m-d H:i:s');
        $dateFilter = '';
        
        // Filter by date range if active is true (frontend display)
        if ($active) {
            $dateFilter = ' AND (hss.`date_start` IS NULL OR hss.`date_start` = "0000-00-00 00:00:00" OR hss.`date_start` <= "' . pSQL($currentDate) . '")
                        AND (hss.`date_end` IS NULL OR hss.`date_end` = "0000-00-00 00:00:00" OR hss.`date_end` >= "' . pSQL($currentDate) . '")';
        }
        
        $slides = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS('
            SELECT hs.`id_homeslider_slides` as id_slide, hss.`position`, hss.`active`, hssl.`title`,
            hssl.`url`, hssl.`legend`, hssl.`description`, hssl.`image`,hss.type_video,hss.`vimeo_video`,hssl.`btn_1_title`,hssl.`btn_1_url`,hssl.`btn_2_title`,hssl.`btn_2_url`,hssl.`image_mobile`, hss.`content_position`, hss.`date_start`, hss.`date_end`
            FROM ' . _DB_PREFIX_ . 'homeslider hs
            LEFT JOIN ' . _DB_PREFIX_ . 'homeslider_slides hss ON (hs.id_homeslider_slides = hss.id_homeslider_slides)
            LEFT JOIN ' . _DB_PREFIX_ . 'homeslider_slides_lang hssl ON (hss.id_homeslider_slides = hssl.id_homeslider_slides)
            WHERE id_shop = ' . (int) $id_shop . '
            AND hssl.id_lang = ' . (int) $id_lang .
            ($active ? ' AND hss.`active` = 1' : ' ') . 
            $dateFilter . '
            ORDER BY hss.position'
        );

        foreach ($slides as &$slide) {
            if (empty($slide['image'])) {
                $slide['image_url'] = '';
            } else {
                $slide['image_url'] = $this->context->link->getMediaLink(_MODULE_DIR_ . 'ps_imageslider/images/' . $slide['image']);
            }
        
            if (empty($slide['image_mobile'])) {
                $slide['image_mobile_url'] = '';
            } else {
                $slide['image_mobile_url'] = $this->context->link->getMediaLink(_MODULE_DIR_ . 'ps_imageslider/images/' . $slide['image_mobile']);
            }
        
            $slide['url'] = $this->updateUrl($slide['url']);
        }

        return $slides;
    }
    protected function updateUrl($link)
    {
        // Empty or anchor link.
        if (empty($link) || 0 === strpos($link, '#')) {
            return $link;
        }

        if (substr($link, 0, 7) !== 'http://' && substr($link, 0, 8) !== 'https://') {
            $link = 'http://' . $link;
        }

        return $link;
    }
    public function getAvailableVideoType()
    {
        return [
            ['id_video_type' => 'video_type_image', 'type_name' => $this->l('Image')],
            ['id_video_type' => 'video_type_youtube', 'type_name' => $this->l('Youtube')],
            ['id_video_type' => 'video_type_vimeo', 'type_name' => $this->l('Vimeo')],
            ['id_video_type' => 'video_type_other', 'type_name' => $this->l('Source')],
        ];
    }
}