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

class CpExportUtil
{
    /**
     * The managed ZipArchieve instance.
     */
    private $zip;

    /**
     * A temporary file to manipulate
     * ZIPs on the fly without permanently saving to file system.
     */
    private $file;

    /**
     * Holds used image URLs in popup to be exported
     */
    private $imageList;

    /**
     * Prepares a ZipArchieve instance and the file system
     * to work with the class.
     *
     * @since 1.6.0
     *
     * @return void
     */
    public function __construct()
    {
        // Check for ZipArchieve
        if (class_exists('ZipArchive')) {
            // Temporary directory for file operations
            $upload_dir = cp_upload_dir();
            $tmp_dir = $upload_dir['basedir'];

            // Prepare ZIP to work with
            $this->file = tempnam($tmp_dir, 'zip');
            $this->zip = new ZipArchive();
            $this->zip->open($this->file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        }
    }

    /**
     * Adds popup settings .json file to ZIP
     *
     * @since 1.6.0
     *
     * @param string $data popup settings JSON
     *
     * @return void
     */
    public function addSettings($data, $folder = '')
    {
        $folder = !empty($folder) ? $folder . '/' : '';
        $this->zip->addFromString($folder . 'settings.json', $data);
    }

    /**
     * Adds popup images to ZIP
     *
     * @since 1.6.0
     *
     * @param string $path Image path to add
     *
     * @return void
     */
    public function addImage($files, $folder = '')
    {
        // Check file
        if (empty($files)) {
            return false;
        }

        // Check file type
        if (!is_array($files)) {
            $files = [$files];
        }

        // Check folder
        $folder = is_string($folder) ? $folder . '/uploads/' : 'uploads/';

        // Add contents to ZIP
        foreach ($files as $file) {
            if (!empty($file) && is_string($file)) {
                $this->zip->addFile(
                    $file,
                    $folder . cp_sanitize_file_name(basename($file))
                );
            }
        }
    }

    /**
     * Closes all pending operations and downloads the ZIP file.
     *
     * @since 1.6.0
     *
     * @return void
     */
    public function download()
    {
        // Close ZIP operations
        $this->zip->close();

        $filename = 'CreativePopup_Export_' . date('Y-m-d') . '_at_' . date('H.i.s') . '.zip';

        // Set headers and to user
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-length: ' . filesize($this->file));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($this->file);

        // Remove temporary file
        @call_user_func('unlink', $this->file);
        exit;
    }

    public function getImagesForPopup($data)
    {
        // Array to hold image URLs
        $this->imageList = [];

        // Popup Preview
        if (!empty($data['meta'])) {
            $this->addImageToList($data['meta'], 'previewId', 'preview');
        }

        $this->addImageToList($data['properties'], 'backgroundimageId', 'backgroundimage');

        // Pages
        if (!empty($data['layers']) && is_array($data['layers'])) {
            foreach ($data['layers'] as $page) {
                $this->addImageToList($page['properties'], 'backgroundId', 'background');
                $this->addImageToList($page['properties'], 'thumbnailId', 'thumbnail');

                // Layers
                if (!empty($page['sublayers']) && is_array($page['sublayers'])) {
                    foreach ($page['sublayers'] as $layer) {
                        $this->addImageToList($layer, 'imageId', 'image');
                        $this->addImageToList($layer, 'posterId', 'poster');
                    }
                }
            }
        }

        return $this->imageList;
    }

    public function fontsForPopup($data)
    {
        $ret = [];
        $usedFonts = [];
        $googleFonts = cp_get_option('cp-google-fonts', []);

        if (!empty($data['layers']) && is_array($data['layers'])) {
            foreach ($data['layers'] as $page) {
                if (!empty($page['sublayers']) && is_array($data['layers'])) {
                    foreach ($page['sublayers'] as $layer) {
                        if (!empty($layer['styles'])) {
                            $layer['styles'] = stripslashes($layer['styles']);

                            $styles = !empty($layer['styles'])
                                ? json_decode(stripslashes($layer['styles']), true)
                                : new stdClass()
                            ;

                            if (!empty($styles['font-family'])) {
                                $families = explode(',', $styles['font-family']);
                                foreach ($families as $family) {
                                    $family = trim($family, " \"'\t\n\r\0\x0B");

                                    if (!empty($family)) {
                                        $usedFonts[] = strtolower($family);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($googleFonts as $font) {
            list($family) = explode(':', $font['param']);
            $family = Tools::strtolower(str_replace('+', ' ', $family));

            if (array_search($family, $usedFonts) !== false) {
                $font['admin'] = false;
                $ret[] = $font;
            }
        }

        return $ret;
    }

    public function getFSPaths($urls)
    {
        if (!empty($urls) && is_array($urls)) {
            $paths = [];
            $upload = cp_upload_dir();
            $uploadDir = basename($upload['basedir']);

            foreach ($urls as $url) {
                // Get URL relative to the uploads folder
                $urlPath = parse_url($url, PHP_URL_PATH);
                $urlPath = explode("/$uploadDir/", $urlPath);

                if (empty($urlPath[1])) {
                    continue;
                }

                $urlPath = $urlPath[1];

                // Get file path
                $filePath = $upload['basedir'] . $urlPath;
                $filePath = realpath($filePath);

                // Add to array
                if (file_exists($filePath) && is_file($filePath)) {
                    $paths[] = $filePath;
                }
            }

            return $paths;
        }

        return [];
    }

    protected function addImageToList($data, $idKey = '', $urlKey = '')
    {
        $idKey .= '';
        if (!empty($data[$urlKey])) {
            $src = $data[$urlKey];
        }

        if (!empty($src)) {
            $this->imageList[] = $src;
        }
    }
}
