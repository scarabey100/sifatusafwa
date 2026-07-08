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

class CpRevisions
{
    public static $active = false;
    public static $enabled = true;
    public static $limit = 100;
    public static $interval = 10;

    /**
     * Private constructor to prevent instantiate static class
     *
     * @since 1.6.0
     *
     * @return void
     */
    private function __construct()
    {
    }

    public static function init()
    {
        if (cp_get_option('cp-revisions-enabled', true)) {
            self::$active = true;
        }

        $option = cp_get_option('cp-revisions-enabled', true);
        self::$enabled = !empty($option);
        self::$limit = cp_get_option('cp-revisions-limit', 100);
        self::$interval = cp_get_option('cp-revisions-interval', 10);
    }

    /**
     * Counts the number of revisions saved for the specified popup
     *
     * @since 1.6.0
     *
     * @param int $popupId The popup database ID
     *
     * @return int The number of revisions available for the popup
     */
    public static function count($popupId)
    {
        if (!$popupId || !is_numeric($popupId)) {
            return false;
        }

        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `popup_id` = ' . (int) $popupId
        );
    }

    /**
     * Finds and returns revisions for a specified popup
     *
     * @since 1.6.0
     *
     * @param int $popupId The popup database ID
     *
     * @return array Array of found popup revisions, or false on error
     */
    public static function snapshots($popupId)
    {
        if (!$popupId || !is_numeric($popupId)) {
            return false;
        }

        return array_map(function ($row) {
            return (object) $row;
        }, Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `popup_id` = ' . (int) $popupId . ' ORDER BY `id` LIMIT 500'
        ) ?: []);
    }

    /**
     * Retrieve a specific revision by its database ID
     *
     * @since 1.6.0
     *
     * @param int $revisionId The revision database ID
     *
     * @return object The chosen revision data, or false on error
     */
    public static function get($revisionId)
    {
        if (!$revisionId || !is_numeric($revisionId)) {
            return false;
        }

        $row = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `id` = ' . (int) $revisionId
        );

        return $row ? (object) $row : false;
    }

    /**
     * Retrieve the last revision for a particular popup
     *
     * @since 1.6.0
     *
     * @param int $popupId The popup database ID
     *
     * @return object The last revision, or false on error
     */
    public static function last($popupId)
    {
        if (!$popupId || !is_numeric($popupId)) {
            return false;
        }

        $row = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `popup_id` = ' . (int) $popupId . ' ORDER BY `id` DESC'
        );

        return $row ? (object) $row : false;
    }

    /**
     * Adds a new revision for a specified popup
     *
     * @since 1.6.0
     *
     * @param int $popupId The popup database ID
     * @param string $popupData The serialized data of the popup
     *
     * @return array Array of found popup revisions, or false on error
     */
    public static function add($popupId, $popupData)
    {
        if (!$popupId || !is_numeric($popupId) || !$popupData) {
            return false;
        }

        $db = Db::getInstance();
        $result = $db->insert('creativepopup_revisions', [
            'popup_id' => (int) $popupId,
            'author' => (int) cp_get_current_user_id(),
            'data' => pSQL($popupData, true),
            'date_c' => (int) time(),
        ]);

        return $result ? $db->insert_id() : false;
    }

    /**
     * Removes a revision
     *
     * @since 1.6.0
     *
     * @param int $revisionId The revision database ID
     *
     * @return mixed Returns the number of rows affected, or false on error
     */
    public static function remove($revisionId)
    {
        if (!$revisionId || !is_numeric($revisionId)) {
            return false;
        }

        return Db::getInstance()->delete('creativepopup_revisions', '`id` = ' . (int) $revisionId, 1);
    }

    /**
     * Removes the last revision of the specified popup
     *
     * @since 1.6.0
     *
     * @param int $popupId The revision database ID
     *
     * @return mixed Returns the number of rows affected, or false on error
     */
    public static function shift($popupId)
    {
        if (!$popupId || !is_numeric($popupId)) {
            return false;
        }

        return (bool) Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `popup_id` = ' . (int) $popupId . ' ORDER BY `id` LIMIT 1'
        );
    }

    /**
     * Removes all revisions for a chosen popup
     *
     * @since 1.6.0
     *
     * @param int $popupId The popup database ID
     *
     * @return mixed Returns the number of rows affected, or false on error
     */
    public static function clear($popupId)
    {
        if (!$popupId || !is_numeric($popupId)) {
            return false;
        }

        return Db::getInstance()->delete('creativepopup_revisions', '`popup_id` = ' . (int) $popupId);
    }

    /**
     * Truncates the entire database table.
     *
     * @since 1.6.0
     *
     * @return bool
     */
    public static function truncate()
    {
        return (bool) Db::getInstance()->execute('TRUNCATE ' . _DB_PREFIX_ . 'creativepopup_revisions');
    }

    /**
     * Reverts the specified popup to a chosen revision
     *
     * @since 1.6.0
     *
     * @param int $popupId The popup database ID
     * @param int $revisionId The revision database ID
     *
     * @return bool True on success, false on error
     */
    public static function revert($popupId, $revisionId)
    {
        if (!$popupId || !is_numeric($popupId) || !$revisionId || !is_numeric($revisionId)) {
            return false;
        }

        $popup = CpInstances::find($popupId);
        $revision = self::get($revisionId);
        $data = $revision->data;

        if ($revision && $data) {
            self::add($popupId, $data);
            CpInstances::update($popupId, $popup['name'], json_decode($data, true));
        }

        return true;
    }
}
