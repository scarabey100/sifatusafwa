<?php
/**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*/

if (!defined('MAGICTOOLBOX_TEMPLATE_HELPER_CLASS_LOADED')) {

    define('MAGICTOOLBOX_TEMPLATE_HELPER_CLASS_LOADED', true);

    class MagicToolboxTemplateHelperClass
    {

        public static $extension = 'php';
        public static $path;
        public static $options;

        public static function setExtension($extension)
        {
            self::$extension = $extension;
        }

        public static function setPath($path)
        {
            self::$path = $path;
        }

        public static function setOptions($options)
        {
            self::$options = $options;
        }

        public static function prepareMagicScrollClass()
        {
            $magicscroll = self::$options->checkValue('magicscroll', 'Yes') ? ' MagicScroll' : '';
            if (!empty($magicscroll)) {
                $additionalClasses = self::$options->getValue('scroll-extra-styles');
                if (!empty($additionalClasses)) {
                    $magicscroll = $magicscroll.' '.$additionalClasses;
                }
            }
            return $magicscroll;
        }

        public static function render($name, $options = null)
        {
            $main = '';
            $thumbs = array();
            $pid = '';
            $magicscrollOptions = '';
            if (func_num_args() == 1) {
                $options = $name;
                $name = self::$options->getValue('template');
            }
            extract($options);

            $items = self::$options->getValue('items');
            $items = is_numeric($items) ? (int)$items : 0;
            if (count($thumbs) > $items) {
                $magicscroll = self::prepareMagicScrollClass();
            } else {
                $magicscroll = '';
            }

            //NOTE: spike for prestashop validator
            $GLOBALS['magictoolbox_temp_options'] = self::$options;
            $GLOBALS['magictoolbox_temp_magicscroll_options'] = $magicscrollOptions;
            $GLOBALS['magictoolbox_temp_magicscroll'] = $magicscroll;
            $GLOBALS['magictoolbox_temp_main'] = $main;
            $GLOBALS['magictoolbox_temp_thumbs'] = $thumbs;
            $GLOBALS['magictoolbox_temp_pid'] = $pid;
            ob_start();
            require(self::$path.DIRECTORY_SEPARATOR.preg_replace('/[^a-zA-Z0-9_]/is', '-', $name).'.tpl.'.self::$extension);
            unset($GLOBALS['magictoolbox_temp_options']);
            unset($GLOBALS['magictoolbox_temp_magicscroll_options']);
            unset($GLOBALS['magictoolbox_temp_magicscroll']);
            unset($GLOBALS['magictoolbox_temp_main']);
            unset($GLOBALS['magictoolbox_temp_thumbs']);
            unset($GLOBALS['magictoolbox_temp_pid']);
            //return str_replace("\n", ' ', str_replace("\r", ' ', ob_get_clean()));
            return ob_get_clean();
        }
    }

}
