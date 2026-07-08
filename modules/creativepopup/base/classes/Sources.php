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

class CpSources
{
    // handle => path
    public static $skins = [];
    public static $transitions = [];

    private function __construct()
    {
    }

    /**
     * Adds the skins from the directory provided, so
     * users can select them in the popup settings.
     *
     * @since 1.6.0
     *
     * @param string $path Path to directory that holds your skins.
     *                     It's assumed to be a direct skin folder if it contains a skin.css file.
     *
     * @return void
     */
    public static function addSkins($path)
    {
        $skinsPath = $skins = [];
        $path = rtrim($path, '/\\');

        // It's a direct skin folder
        if (file_exists($path . '/skin.css')) {
            $skinsPath = [$path];
        } else { // Get all children if it's a parent directory
            $skinsPath = glob($path . '/*', GLOB_ONLYDIR);
        }

        // Iterate over the skins
        foreach ($skinsPath as $path) {
            // Exclude non-valid skins
            if (!file_exists($path . '/skin.css')) {
                continue;
            }

            // Gather skin data
            $handle = Tools::strtolower(basename($path));
            $skins[$handle] = [
                'name' => $handle,
                'handle' => $handle,
                'dir' => $path,
                'file' => $path . DIRECTORY_SEPARATOR . 'skin.css',
            ];

            // Get skin info (if any)
            if (file_exists($path . '/info.json')) {
                $skins[$handle]['info'] = json_decode(call_user_func('file_get_contents', $path . '/info.json'), true);
                $skins[$handle]['name'] = $skins[$handle]['info']['name'];
            }
        }

        self::$skins = array_merge(self::$skins, $skins);
        ksort(self::$skins);
    }

    /**
     * Removes a previously added skin by its folder name as being $handle.
     *
     * @since 1.6.0
     *
     * @param string $skin The name of the skin/folder
     *
     * @return void
     */
    public static function removeSkin($handle)
    {
        unset(self::$skins[Tools::strtolower($handle)]);
    }

    /**
     * Returns skin information by its folder name as being $handle.
     *
     * @since 1.6.0
     *
     * @param string $skin The name of the skin/folder
     *
     * @return array Skin details
     */
    public static function getSkin($handle)
    {
        return self::$skins[Tools::strtolower($handle)];
    }

    /**
     * Returns all skins.
     *
     * @since 1.6.0
     *
     * @return array Array of all skins
     */
    public static function getSkins()
    {
        return self::$skins;
    }

    /**
     * Returns the directory path of a skin by its folder name as being $handle
     *
     * @since 1.6.0
     *
     * @param string $skin The name of the skin/folder
     *
     * @return string Path for the skin's directory
     */
    public static function pathForSkin($handle)
    {
        return self::$skins[Tools::strtolower($handle)]['dir'] . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the directory path of a skin by its folder name as being $handle
     *
     * @since 1.6.0
     *
     * @param string $skin The name of the skin/folder
     *
     * @return string URL for the skin's directory
     */
    public static function urlForSkin($handle)
    {
        $path = self::$skins[Tools::strtolower($handle)]['dir'];
        $url = cp_content_url() . str_replace(realpath(CP_CONTENT_DIR), '', realpath($path)) . '/';

        return str_replace('\\', '/', $url);
    }
}
