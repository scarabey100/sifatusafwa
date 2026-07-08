<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

/**
 * @property EgBannerClass $object
 */
class AdminEgBannerController extends ModuleAdminController
{
    protected $position_identifier = 'id_eg_banner';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'eg_banner';
        $this->className = 'EgBannerClass';
        $this->identifier = 'id_eg_banner';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';
        $this->toolbar_btn = null;
        $this->list_no_link = true;
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->fields_list = array(
            'id_eg_banner' => array(
                'title' => $this->l('Id')
            ),
            'image_background' => array(
                'title' => $this->l('Image backbground'),
                'type' => 'text',
                'callback' => 'showBanner',
                'callback_object' => 'EgBannerClass',
                'class' => 'fixed-width-xxl',
                'search' => false,
            ),
            'image_book' => array(
                'title' => $this->l('Image book'),
                'type' => 'text',
                'callback' => 'showBanner',
                'callback_object' => 'EgBannerClass',
                'class' => 'fixed-width-xxl',
                'search' => false,
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'filter_key' => 'b!title',
            ),
            'title_arabic' => array(
                'title' => $this->l('Title Arabic'),
                'filter_key' => 'b!title_arabic',
            ),
            'recto' => array(
                'title' => $this->l('Brightness'),
                'filter_key' => 'a!recto',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ),
            'active' => array(
                'title' => $this->l('Displayed'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'orderby' => false
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ),
            
        );
    }

    /**
     * @param $description
     * @return string Content without html
     */
    public static function getDescriptionClean($description)
    {
        return Tools::getDescriptionClean($description);
    }

    /**
     * AdminController::init() override
     * @see AdminController::init()
     */
    public function init()
    {
        parent::init();

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND b.`id_shop` = '.(int)Context::getContext()->shop->id;
        }
    }

    /**
     * @see AdminController::initPageHeaderToolbar()
     */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_banner'] = array(
                'href' => self::$currentIndex.'&addeg_banner&token='.$this->token,
                'desc' => $this->l('Add new banner'),
                'icon' => 'process-icon-new'
            );
        }
        parent::initPageHeaderToolbar();
    }

    /**
     * @param $item
     * @return array
     */
    protected function stUploadImage($item)
    {
        $result = array(
            'error' => array(),
            'image' => '',
        );
        $types = array('gif', 'jpg', 'jpeg', 'jpe', 'png', 'svg','webp','avif');
        if (isset($_FILES[$item]) && isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name'])) {
            $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);

            $imageSize = @getimagesize($_FILES[$item]['tmp_name']);
            if (!empty($imageSize) &&
                ImageManager::isCorrectImageFileExt($_FILES[$item]['name'], $types)) {
                $imageName = explode('.', $_FILES[$item]['name']);
                $imageExt = $imageName[1];
                $tempName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $coverImageName = $name .'-'.rand(0, 1000).'.'.$imageExt;
                $destinationFile = _PS_MODULE_DIR_ . $this->module->name.'/views/img/'.$coverImageName; 
                if ($upload_error = ImageManager::validateUpload($_FILES[$item])) {
                    $result['error'][] = $upload_error; 
                } elseif (!$tempName || !move_uploaded_file($_FILES[$item]['tmp_name'], $tempName)) {
                    $result['error'][] = $this->l('An error occurred during move image.');
                } else {
                    
                    if ( $imageExt != "webp") {
                        
                        if (!ImageManager::resize($tempName, $destinationFile, null, null, $imageExt)){
                            $result['error'][] = $this->l('An error occurred during the image upload.');
                        }
                    } else {
                        $pathinfo = pathinfo($_FILES[$item]['name']);
                        
                        $cp =  copy(
                            $tempName,
                            $destinationFile
                        );
                        if (!$cp) { 
                            $output .= $this->trans('File copy failed', array(), 'Modules.Egpopup');
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
            return $result;
        }
    }

    /**
     * AdminController::postProcess() override
     * @see AdminController::postProcess()
     */
    public function postProcess()
    {
        // Upload FILES EG Banner
        if ($this->action && $this->action == 'save') {
            foreach (Language::getLanguages(true) as $lang) {
                $image_book = $this->stUploadImage('image_book_'.$lang['id_lang']);
                if (isset($image_book['image']) && !empty($image_book['image'] )) {
                    $_POST['image_book_'.$lang['id_lang']]= $image_book['image'];
                    // Copier image_book dans image_mobile_book pour utiliser la même image pour desktop et mobile
                    $_POST['image_mobile_book_'.$lang['id_lang']]= $image_book['image'];
                } elseif (Tools::getValue('image_book_'.$lang['id_lang'])) {
                    // Si aucune nouvelle image n'est uploadée mais qu'une image existe déjà, copier dans mobile
                    $_POST['image_mobile_book_'.$lang['id_lang']] = Tools::getValue('image_book_'.$lang['id_lang']);
                }
                $image_background = $this->stUploadImage('image_background_'.$lang['id_lang']);
                if (isset($image_background['image']) && !empty($image_background['image'] )) {
                    $_POST['image_background_'.$lang['id_lang']]= $image_background['image'];
                }
                $image_mobile_background = $this->stUploadImage('image_mobile_background_'.$lang['id_lang']);
                if (isset($image_mobile_background['image']) && !empty($image_mobile_background['image'] )) {
                    $_POST['image_mobile_background_'.$lang['id_lang']]= $image_mobile_background['image'];
                }
            }
        }
        // Delete Images EG Banner
        if (Tools::isSubmit('forcedeleteImage') || Tools::getValue('deleteImage')) {
            $champ = Tools::getValue('champ');
            $imgValue = Tools::getValue('image');
            EgBannerClass::updateEgBannerImag($champ, $imgValue);
            if (Tools::isSubmit('forcedeleteImage')) {
                Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminEgBanner'));
            }
        }

        return parent::postProcess();
    }

    /**
     * @see AdminController::initProcess()
     */
    public function initProcess()
    {
        $this->context->smarty->assign(array(
            'uri' => $this->module->getPathUri()
        ));
        parent::initProcess();
    }

    public function getHookList()
    {
        $hooks = array();
        foreach ($this->myHook as $key => $hook)
        {
            $hooks[$key]['key'] = $hook;
            $hooks[$key]['name'] = $hook;
        }
        return $hooks;
    }
    public function getAvailableVideoType()
    {
        return [
            ['id_video_type' => 'video_type_youtube', 'type_name' => $this->l('Youtube')],
            ['id_video_type' => 'video_type_vimeo', 'type_name' => $this->l('Vimeo')],
            ['id_video_type' => 'video_type_other', 'type_name' => $this->l('Source')], 
        ];
    }
    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Page'),
                'icon' => 'icon-folder-close'
            ),
            // custom template
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Titre:'),
                    'name' => 'title',
                    'lang' => true,
                    'desc' => $this->l('Please enter a title for the banner.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Titre arabic:'),
                    'name' => 'title_arabic',
                    'lang' => true,
                    'desc' => $this->l('Please enter a title for the banner.'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'autoload_rte' => true,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}',
                    'desc' => $this->l('Please enter a description for the banner.')
                ),
                array(
                    'type' => 'datetime',
                    'label' => $this->l('Start Date:'),
                    'name' => 'start_date',
                    'desc' => $this->l('Please select the start date and time for the banner.'),
                ),
                array(
                    'type' => 'datetime',
                    'label' => $this->l('End Date:'),
                    'name' => 'end_date',
                    'desc' => $this->l('Please select the end date and time for the banner.'),
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Image background Desktop:'),
                    'name' => 'image_background',
                    'lang' => true,
                    'delete_Lien' => self::$currentIndex.'&'.$this->identifier .'='.$obj->id.'&token='.$this->token.'&champ=image&deleteImage=1',
                    'desc' => $this->l('Téléchargez un logo depuis votre ordinateur.')
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Image background Mobile:'),
                    'name' => 'image_mobile_background',
                    'lang' => true,
                    'delete_Lien' => self::$currentIndex.'&'.$this->identifier .'='.$obj->id.'&token='.$this->token.'&champ=image_mobile&deleteImage=1',
                    'desc' => $this->l('Téléchargez une image pour votre bannière supérieure. Les dimensions recommandées sont de 384 x 366px si vous utilisez le thème par défaut.')
                ),
                array(
                    'type' => 'file_lang',
                    'label' => $this->l('Image produit:'),
                    'name' => 'image_book',
                    'lang' => true,
                    'delete_Lien' => self::$currentIndex.'&'.$this->identifier .'='.$obj->id.'&token='.$this->token.'&champ=image&deleteImage=1',
                    'desc' => $this->l('Téléchargez une image produit (utilisée pour desktop et mobile).')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alt:'),
                    'name' => 'alt',
                    'lang' => true,
                    'desc' => $this->l('Veuillez saisir un texte alternatif pour la bannière.')

                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Lien'),
                    'name' => 'link',
                    'lang' => true,
                    'required' => true,
                    'desc' => $this->l('Please enter a link for the banner.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Texte du bouton'),
                    'name' => 'link_text',
                    'lang' => true,
                    'required' => true,
                    'desc' => $this->l('Please enter a custom text for button.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Background image'),
                    'name' => 'use_background',
                    'is_bool' => true,
                    'values' => array(
                        array(
                           'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('use background')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('no background')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Direction'),
                    'name' => 'recto',
                    'is_bool' => true,
                    'values' => array(
                        array(
                           'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Left')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Right')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Affichage'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Activé')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Désactivé')
                        )
                    )
                ),
                
            ),
             'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );


        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }

        return parent::renderForm();
    }

    /**
     * Update Positions Banner
     */
    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $idEgBanner = (int)(Tools::getValue('id'));
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value){
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $idEgBanner){
                if ($banner = new EgBannerClass((int)$pos[2])){
                    if (isset($position) && $banner->updatePosition($way, $position)){
                        echo 'ok position '.(int)$position.' for tab '.(int)$pos[1].'\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update tab '.(int)$idEgBanner.' to position '.(int)$position.' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This tab ('.(int)$idEgBanner.') can t be loaded"}';
                }

                break;
            }
        }
    }
}
