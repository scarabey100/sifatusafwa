<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminCreativePopupMediaController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        define('CP_MEDIA_PATH', __PS_BASE_URI__ . "modules/{$this->module->name}/base/mediamanager/");
        include _PS_MODULE_DIR_ . $this->module->name . '/base/mediamanager/php/functions.php';

        header_register_callback(function () {
            header_remove('Content-Security-Policy');
            header_remove('X-Content-Type-Options');
            header_remove('X-Frame-Options');
            header_remove('X-Xss-Protection');
        });
    }

    public function postProcess()
    {
        parent::postProcess();
        if (isset($this->context->cookie->cp_error)) {
            $this->errors[] = $this->context->cookie->cp_error;
            unset($this->context->cookie->cp_error);
        }
        $action = Tools::getValue('action', '');
        if ($action) {
            $this->{'action' . $action}();
        }
    }

    public function actionListImages()
    {
        $dir = preg_replace('/\.+/', '.', Tools::getValue('d'));
        $imagepath = _PS_IMG_DIR_ . $dir;

        $del = Tools::getValue('del');
        if ($del) {
            $delimagepath = $imagepath . urldecode($del);

            if (file_exists($delimagepath)) {
                @call_user_func('unlink', $delimagepath);
            }
        }

        $files = [];
        if (is_dir($imagepath)) {
            $files = scandir($imagepath);
        }

        // $array_count = 0;
        $files_array = [];

        if ($imagepath != _PS_IMG_DIR_) {
            $files_array[] = '..';
        }

        foreach ($files as $file_name) {
            $file_path = $imagepath . $file_name;
            if (is_dir($file_path) && $file_name[0] !== '.') {
                $files_array[] = $file_name;
            }
        }
        $pattern = '/\.(jpe?g|png|gif|bmp|webp)$/i';
        // $mediatype = Tools::getValue('type', 'image');
        foreach ($files as $file_name) {
            $file_path = $imagepath . $file_name;
            if (is_file($file_path) && $file_name[0] !== '.' && preg_match($pattern, $file_name)) {
                $files_array[] = $file_name;
            }
        }

        $nofiles = count($files_array);
        $resultspp = 4096;
        $nopages = ceil($nofiles / $resultspp);

        $pageno = Tools::getValue('p');

        $error = [
            'thumbhtml' => iconv(
                'UTF-8',
                'UTF-8//IGNORE',
                cp_display_gallery_page($files_array, $pageno, $dir, $resultspp, false)
            ),
            'paginationhtml' => cp_display_gallery_pagination('', count($files_array), $pageno, $resultspp, false),
            'noofpages' => $nopages,
        ];

        exit(json_encode([$error]));
    }

    public function actionUploadFile()
    {
        $uploadfolder = _PS_IMG_DIR_;
        $reluploadfolder = _PS_IMG_;

        if (Tools::getIsset('uploadfolder')) {
            $dir = preg_replace('/\.+/', '.', Tools::getValue('uploadfolder'));
            $uploadfolder .= $dir;
            $reluploadfolder .= $dir;
        }
        if (file_exists($uploadfolder)) {
            if (isset($_FILES['userfile']['name'])) {
                $mediatype = Tools::getValue('mediatype');

                $tname = $_FILES['userfile']['name'];

                $name = strtr(
                    $tname,
                    'ŔÁÂĂÄĹÇČÉĘËĚÍÎĎŇÓÔŐÖŮÚŰÜÝ' .
                    'ŕáâăäĺçčéęëěíîďđňóôőöůúűüý˙',
                    'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy'
                );
                $name = preg_replace('/\s+/', '-', $name);

                $fileext = Tools::strtolower(pathinfo($name, PATHINFO_EXTENSION));

                $filename = Tools::strtolower(pathinfo($name, PATHINFO_FILENAME));

                $destination = $uploadfolder . $name;
                $reldestination = $reluploadfolder . $name;

                if (file_exists($destination)) {
                    $uniqid = uniqid();
                    $filename = $filename . '_' . $uniqid;
                    $name = $filename . '.' . $fileext;
                    $destination = $uploadfolder . $name;
                    $reldestination = $reluploadfolder . $name;
                }

                if (move_uploaded_file($_FILES['userfile']['tmp_name'], $destination)) {
                    $uploadinfo = new stdClass();

                    $uploadinfo->name = $name;
                    $uploadinfo->destination = $reldestination;
                    $uploadinfo->mediatype = $mediatype;
                    exit(json_encode([$uploadinfo]));
                } else {
                    $error = ['error' => 'There was a problem uploading the file, please try again'];
                    exit(json_encode([$error]));
                }
            } else {
                $error = ['error' => 'No file sent'];
                exit(json_encode([$error]));
            }
        } else {
            $error = ['error' => 'Upload folder not correctly configured! ' . $uploadfolder];
            exit(json_encode([$error]));
        }
    }

    public function display()
    {
        ob_start();
        require_once _PS_MODULE_DIR_ . $this->module->name . '/base/mediamanager/mediamanager.phtml';
        $this->content = ob_get_clean();

        exit($this->content);
    }
}
