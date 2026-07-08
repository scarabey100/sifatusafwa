<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgContactBloc
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
 

class EgContactBloc extends Module {

    protected $_html = '';
    protected $templateFile;
    protected $domain;
    protected $img_path;
    public function __construct()
    {
        $this->name = 'egcontactbloc';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Egio digital';
        $this->need_instance = 0; 
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->context = Context::getContext();
 
        parent::__construct();

        $this->domain = 'Modules.EgContactBloc.EgContactBloc';
        $this->displayName = $this->trans('Home Contact Block ', array(), $this->domain);
        $this->description = $this->trans('Display Contact Block', array(), $this->domain);

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', array(), $this->domain);
        $this->img_path = $this->_path.'views/img/';
        $this->templateFile = 'module:egcontactbloc/views/templates/hook/egcontactbloc.tpl';
    }

    /**
     * @see  CREATE TAB module in Dashboard
     */
    public function createTabs()
    {
        $idParent = (int) Tab::getIdFromClassName('AdminEgDigital');
        if (empty($idParent)) {
            $parent_tab = new Tab();
            $parent_tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $parent_tab->name[$lang['id_lang']] = $this->trans('Modules EGIO', array(), $this->domain);
            }
            $parent_tab->class_name = 'AdminEgDigital';
            $parent_tab->id_parent = 0;
            $parent_tab->module = $this->name;
            $parent_tab->icon = 'library_books';
            $parent_tab->add();
        }
 
        // Menage Module
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Contact Block', array(), $this->domain);
        }
        $tab->class_name = 'AdminEgContactBloc';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminEgDigital');
        $tab->module = $this->name;
        $tab->add();

         

        return true;
    }

    /**
     * Remove Tabs module in Dashboard
     * @param $class_name string name Tab
     * @return bool
     * @throws
     * @throws
     */
    public function removeTabs($class_name)
    {
        if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        
        include(dirname(__FILE__).'/sql/install.php');
        return parent::install()
            && $this->createTabs() 
            && $this->registerHook('displayHome')
            && $this->registerHook('displayFooterBefore');
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        $this->removeTabs('AdminEgContactBloc'); 
        return parent::uninstall();
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }
 
    public function clearCache()
    {
        $this->_clearCache($this->templateFile);
    }

    public function hookDisplayHome($params)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId($this->name))) {
            $id_lang = (int)$this->context->language->id;
            $title =  Configuration::get('EG_CONTACT_BLOC_TITLE_'.$id_lang);
            $sub_title =  Configuration::get('EG_CONTACT_BLOC_TITLE_SUB_'.$id_lang);
            $desc = Configuration::get('EG_CONTACT_BLOC_DESC_'.$id_lang); 
            $btn_txt = Configuration::get('EG_CONTACT_BLOC_BTN_TXT_'.$id_lang);
            $btn_url = Configuration::get('EG_CONTACT_BLOC_BTN_URL_'.$id_lang);
            $img = '/modules/egcontactbloc/views/img/'. Configuration::get('EG_CONTACT_BLOC_IMG');
            $img_mobile = '/modules/egcontactbloc/views/img/'. Configuration::get('EG_CONTACT_BLOC_IMG_MOBILE');
            $status = Configuration::get('EG_CONTACT_BLOC_STATUS');
           
            $this->context->smarty->assign(array(
                'title' => $title,
                'sub_title' => $sub_title,
                'desc' => $desc,
                'btn_txt' => $btn_txt,
                'btn_url' => $btn_url,
                'img' =>$img, 
                'img_mobile' => $img_mobile,
                'status' => $status,
            ));
        }
        return $this->fetch($this->templateFile, $this->getCacheId($this->name));
    }
    public function hookDisplayFooterBefore($params)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId($this->name))) {
            $id_lang = (int)$this->context->language->id;
            $title =  Configuration::get('EG_CONTACT_BLOC_TITLE_'.$id_lang);
            $sub_title =  Configuration::get('EG_CONTACT_BLOC_TITLE_SUB_'.$id_lang);
            $desc = Configuration::get('EG_CONTACT_BLOC_DESC_'.$id_lang); 
            $btn_txt = Configuration::get('EG_CONTACT_BLOC_BTN_TXT_'.$id_lang);
            $btn_url = Configuration::get('EG_CONTACT_BLOC_BTN_URL_'.$id_lang);
            $img = '/modules/egcontactbloc/views/img/'. Configuration::get('EG_CONTACT_BLOC_IMG');
            $img_mobile = '/modules/egcontactbloc/views/img/'. Configuration::get('EG_CONTACT_BLOC_IMG_MOBILE');
            $status = Configuration::get('EG_CONTACT_BLOC_STATUS');
           
            $this->context->smarty->assign(array(
                'title' => $title,
                'sub_title' => $sub_title,
                'desc' => $desc,
                'btn_txt' => $btn_txt,
                'btn_url' => $btn_url,
                'img' =>$img, 
                'img_mobile' => $img_mobile,
                'status' => $status,
            ));
        }
        return $this->fetch($this->templateFile, $this->getCacheId($this->name));
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitModule')) {
            // Collect and save multilingual fields
            $fields = [
                'EG_CONTACT_BLOC_TITLE',
                'EG_CONTACT_BLOC_TITLE_SUB',
                'EG_CONTACT_BLOC_DESC',
                'EG_CONTACT_BLOC_BTN_TXT',
                'EG_CONTACT_BLOC_BTN_URL',
            ];
            foreach ($fields as $field) { 
                foreach (Language::getLanguages(false) as $lang) {
                    $value = Tools::getValue($field.'_' . $lang['id_lang'],true);
                    Configuration::updateValue($field.'_' . $lang['id_lang'], $value, true); // Save multilingual title
                }
            }
            // Save images and status (not multilingual)
            if (Tools::getValue('EG_CONTACT_BLOC_IMG') != "") {
                $this->postImage('EG_CONTACT_BLOC_IMG');
            }
            if (Tools::getValue('EG_CONTACT_BLOC_IMG_MOBILE') != "") {
                $this->postImage('EG_CONTACT_BLOC_IMG_MOBILE');
            }
            Configuration::updateValue('EG_CONTACT_BLOC_STATUS', Tools::getValue('EG_CONTACT_BLOC_STATUS'));
        }
        $this->_html .= $this->renderForm();
        return $this->_html;
    }
 
    protected function renderForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this; 
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $helper->default_form_language = $default_lang; 
        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $fields = array(
            'EG_CONTACT_BLOC_IMG' => Configuration::get('EG_CONTACT_BLOC_IMG'),
            'EG_CONTACT_BLOC_STATUS' => Configuration::get('EG_CONTACT_BLOC_STATUS'),
            'EG_CONTACT_BLOC_IMG_MOBILE' => Configuration::get('EG_CONTACT_BLOC_IMG_MOBILE'),
        );
        $languages = Language::getLanguages(false);
        // Use the same pattern as eghomehadithproducts.php: get all values at once and assign by language
        $multilang_fields = [
            'EG_CONTACT_BLOC_TITLE',
            'EG_CONTACT_BLOC_TITLE_SUB',
            'EG_CONTACT_BLOC_DESC',
            'EG_CONTACT_BLOC_BTN_TXT',
            'EG_CONTACT_BLOC_BTN_URL',
        ];
        foreach ($multilang_fields as $field) {
            foreach ($languages as $lang) {
                $id_lang = $lang['id_lang'];
                $fields[$field][$id_lang] = Configuration::get($field . '_' . $id_lang);
            }
        }
        return $fields;
    }

    /**
     * @return array
     */
    protected function getConfigForm()
    {
        $src = $this->img_path . Configuration::get('EG_CONTACT_BLOC_IMG'); 
        $image = '<br/><img class="bloc_img" width="200" alt="" src="' .$src.'" /><br/>';
        $src_mobile = $this->img_path . Configuration::get('EG_CONTACT_BLOC_IMG_MOBILE');
        $image_mobile = '<br/><img class="bloc_img" width="200" alt="" src="' .$src_mobile.'" /><br/>';
        return array(
            'form' => array(
                'tinymce' => true,
                'legend' => array(
                    'title' => $this->trans('Configure Contact Bloc', array(), $this->domain),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Title', array(), $this->domain),
                        'name' => 'EG_CONTACT_BLOC_TITLE',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Sub Title', array(), $this->domain),
                        'name' => 'EG_CONTACT_BLOC_TITLE_SUB',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Description', array(), $this->domain),
                        'name' => 'EG_CONTACT_BLOC_DESC',
                        'rows' => 5,
                        'cols' => 40,
                        'autoload_rte' => true,
                        'lang' => true,
                    ), 
                    array(
                        'type' => 'file',
                        'label' => $this->l('Image'),
                        'name' => 'EG_CONTACT_BLOC_IMG',
                        'desc' => $this->l('Upload Image.'),
                        'image' => $image ? $image: false,
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Mobile Image'),
                        'name' => 'EG_CONTACT_BLOC_IMG_MOBILE',
                        'desc' => $this->l('Upload Mobile Image.'),
                        'image' => $image_mobile ? $image_mobile : false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Button Text', array(), $this->domain),
                        'name' => 'EG_CONTACT_BLOC_BTN_TXT',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Button Link', array(), $this->domain),
                        'name' => 'EG_CONTACT_BLOC_BTN_URL',
                        'lang' => true,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Displayed', array(), $this->domain),
                        'name' => 'EG_CONTACT_BLOC_STATUS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', array(), $this->domain)
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', array(), $this->domain)
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), $this->domain),
                ),
            ),
        );
    } 
  
    public function postImage($field_name = 'EG_CONTACT_BLOC_IMG')
    {
        $result = $this->stUploadImage($field_name);
        if (!empty($result['image'])) {
            Configuration::updateValue($field_name, $result['image']);
        }
        return !empty($result['error']) ? implode(', ', $result['error']) : '';
    }

    protected function stUploadImage($item)
    {
        $result = array(
            'error' => array(),
            'image' => '',
        );
        $types = array('gif', 'jpg', 'jpeg', 'jpe', 'png', 'svg', 'webp', 'avif');
        if (isset($_FILES[$item])) {
            if (isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name'])) {
                $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);
                $imageSize = @getimagesize($_FILES[$item]['tmp_name']);
                if (!empty($imageSize) && ImageManager::isCorrectImageFileExt($_FILES[$item]['name'], $types)) {
                    $imageName = explode('.', $_FILES[$item]['name']);
                    $imageExt = $imageName[1];
                    $tempName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                    $coverImageName = $name . '-' . rand(0, 1000) . '.' . $imageExt;
                    $destinationFile = _PS_MODULE_DIR_ . $this->name . '/views/img/' . $coverImageName;
                    if ($upload_error = ImageManager::validateUpload($_FILES[$item])) {
                        $result['error'][] = $upload_error;
                    } elseif (!$tempName || !move_uploaded_file($_FILES[$item]['tmp_name'], $tempName)) {
                        $result['error'][] = $this->l('An error occurred during move image.');
                    } else {
                        if ($imageExt != "webp") {
                            if (!ImageManager::resize($tempName, $destinationFile, null, null, $imageExt)) {
                                $result['error'][] = $this->l('An error occurred during the image upload.');
                            }
                        } else {
                            $pathinfo = pathinfo($_FILES[$item]['name']);
                            if (!copy($tempName, $destinationFile)) {
                                $result['error'][] = $this->trans('File copy failed', array(), 'Modules.Egpopup');
                            }
                        }
                    }
                    if (isset($tempName)) {
                        @unlink($tempName);
                    }
                    if (!count($result['error'])) {
                        $result['image'] = $coverImageName;
                        $result['width'] = $imageSize[0];
                        $result['height'] = $imageSize[1];
                    }
                    return $result;
                }
            } else {
                $result['error'][] = $this->l('File tmp_name is empty.');
                error_log('File tmp_name is empty for ' . $item);
            }
        } else {
            $result['error'][] = $this->l('No file uploaded.');
            error_log('No file uploaded for ' . $item);
        }
        return $result;
    }
}
