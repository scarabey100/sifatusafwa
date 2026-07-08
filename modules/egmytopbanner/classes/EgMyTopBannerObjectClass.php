<?php

class EgMyTopBannerObjectClass extends ObjectModel {

    public $id;
    public $title;
    public $content_b;
    public $color;
    public $active; 
    public static $definition = array(
        'table' => 'egmytopbanner',
        'primary' => 'id_egmytopbanner',
        'multilang' => true, // Enable multilang
        'fields' => array( 
            'title' => array(
                'type' => self::TYPE_STRING,
                'lang' => true, // Multilang
                'validate' => 'isCleanHtml',
                'required' => false
            ),
            'content_b' => array(
                'type' => self::TYPE_HTML,
                'lang' => true, // Multilang
                'required' => false
            ),
            'color' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_INT, 
                'required' => false
            ),
        )
    ); 
    public static $dirname =   _PS_IMG_DIR_.'egmytopbanner' ;
    public static $module = 'egmytopbanner' ;

     /**
     * @param int $id_lang
     * @param int $id_shop
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getAllBanners($id_lang = null)
    {
        if ($id_lang === null) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        $sql = 'SELECT b.content_b, pr.color FROM `' . _DB_PREFIX_ . 'egmytopbanner_lang` b
                INNER JOIN `' . _DB_PREFIX_ . 'egmytopbanner` pr ON b.id_egmytopbanner = pr.id_egmytopbanner
                WHERE pr.active = 1 AND b.id_lang = '.(int)$id_lang.' ORDER BY pr.id_egmytopbanner';
        $result = Db::getInstance()->executeS($sql);
               
        return $result;
    }
     /**
     * @param $value string image Banner
     * @return string src
     */
    public static function showBanner($value)
    {
        $src = __PS_BASE_URI__. 'modules/egmytopbanner/views/img/'.$value;
        
        return $value ? '<img src="'.$src.'" width="80" height="40px" class="img img-thumbnail"/>' : '-';
    }
      /**
     * @param $email
     * @return mixed
     */
    public static function newElement($titre, $desc, $id_lang = null)
    {   
        // Insert into main table first
        $db = \Db::getInstance();
        $result = $db->insert("egmytopbanner", [
            // Only non-multilang fields
        ]);
        if (!$result) {
            return false;
        }
        $id_egmytopbanner = $db->Insert_ID();
        if ($id_lang === null) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        // Insert into lang table
        $result_lang = $db->insert("egmytopbanner_lang", [
            'id_egmytopbanner' => $id_egmytopbanner,
            'id_lang' => $id_lang,
            'title' => $titre,
            'content_b' => $desc
        ]);
        return $result && $result_lang;
    }
    public static function stUploadImage($item,$module)
    {
        $result = array(
            'error' => array(),
            'image' => '',
        );
        $types = array('gif', 'jpg', 'jpeg', 'jpe', 'png', 'svg');
        if (isset($_FILES[$item]) && isset($_FILES[$item]['tmp_name']) && !empty($_FILES[$item]['tmp_name'])) {
            $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);

            $imageSize = @getimagesize($_FILES[$item]['tmp_name']);
            if (!empty($imageSize) &&
                ImageManager::isCorrectImageFileExt($_FILES[$item]['name'], $types)) {
                $imageName = explode('.', $_FILES[$item]['name']);
                $imageExt = $imageName[1];
                $tempName = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                $coverImageName = 'seller' .'-'.rand(0, 1000).'.'.$imageExt;
                if ($upload_error = ImageManager::validateUpload($_FILES[$item])) {
                    $result['error'][] = $upload_error;
                } elseif (!$tempName || !move_uploaded_file($_FILES[$item]['tmp_name'], $tempName)) {
                    $result['error'][] = $this->l('An error occurred during move image.');
                } else {
                    
                    //$destinationFile = $this->module->img_path.$coverImageName;
                    $destinationFile = _PS_MODULE_DIR_ .$module.'/views/img/'.$coverImageName;
                   
                     
                    if (!ImageManager::resize($tempName, $destinationFile, null, null, $imageExt)){
                        $result['error'][] = $this->name;
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
}
