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

class CpInstances
{
    /**
     * @var array Array containing the result of the last DB query
     */
    public static $results = [];

    /**
     * @var int Count of found popups in the last DB query
     */
    public static $count;

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

    /**
     * Returns the count of found popups in the last DB query
     *
     * @since 1.6.0
     *
     * @return int Count of found popups in the last DB query
     */
    public static function count()
    {
        return self::$count;
    }

    /**
     * Find popups with the provided filters
     *
     * @since 1.6.0
     *
     * @param mixed $args Find any popup with the provided filters
     *
     * @return mixed Array on success, false otherwise
     */
    public static function find($args = [])
    {
        if (is_numeric($args) && (int) $args == $args) {
            // Find by popup ID
            return self::getById((int) $args);
        } elseif (is_array($args) && isset($args[0]) && is_numeric($args[0])) {
            // Find by list of popup IDs
            return self::getByIds($args);
        } else {
            // Find by query
            $defaults = [
                'term' => '',
                'exclude' => ['removed'],
                'orderby' => 'date_c',
                'order' => 'DESC',
                'limit' => 10,
                'page' => 1,
                'data' => true,
            ];
            $args = array_merge($defaults, $args);

            in_array($args['orderby'], [
                'id',
                'author',
                'name',
                'data',
                'date_c',
                'date_m',
                'flag_hidden',
                'flag_deleted',
                'schedule_start',
                'schedule_end',
            ]) || $args['orderby'] = 'date_c';

            // Build the query
            $db = Db::getInstance();
            $query = (new DbQuery())->from('creativepopup');

            if ($args['exclude']) {
                in_array('hidden', $args['exclude']) && $query->where('`flag_hidden` = 0');
                in_array('removed', $args['exclude']) && $query->where('`flag_deleted` = 0');
            }
            if ($args['term']) {
                $query->where('`name` LIKE "%' . pSQL($args['term']) . '%"');
            }
            $query->orderBy(bqSQL($args['orderby']) . ('DESC' === $args['order'] ? ' DESC' : ''));
            $query->limit($args['limit'], $args['limit'] * $args['page'] - $args['limit']);
            $popups = $db->executeS($query);

            // Set counter
            self::$count = (int) $db->getValue('SELECT FOUND_ROWS()');

            // Parse popup data
            if ($args['data'] && $popups) {
                foreach ($popups as &$popup) {
                    $popup['data'] = json_decode($popup['data'], true);
                }
            }

            // Return popups
            return $popups;
        }
    }

    /**
     * Add popup with the provided name and optional popup data
     *
     * @since 1.6.0
     *
     * @param string $title The title of the popup to create
     * @param array $data The settings of the popup to create
     *
     * @return int The popup database ID inserted
     */
    public static function add($title = 'Unnamed', $data = [])
    {
        // Popup data
        $data = $data ?: [
            'properties' => [
                'createdWith' => CP_PLUGIN_VERSION,
                'popupVersion' => CP_PLUGIN_VERSION,
                'title' => $title,
                'new' => true,
            ],
            'layers' => [[]],
        ];
        // reset popup props
        unset($data['properties']['status']);
        unset($data['properties']['shop']);
        unset($data['properties']['lang']);
        unset($data['properties']['cats']);
        unset($data['properties']['pages']);
        unset($data['properties']['position']);

        // Fix issue with longer varchars
        // than the column length
        if (Tools::strlen($title) > 99) {
            $title = Tools::substr($title, 0, 99 - Tools::strlen($title));
        }

        // Insert popup
        $time = time();
        $db = Db::getInstance();
        $db->insert('creativepopup', [
            'author' => (int) cp_get_current_user_id(),
            'name' => pSQL($title),
            'data' => pSQL(json_encode($data), true),
            'date_c' => (int) $time,
            'date_m' => (int) $time,
            'flag_hidden' => 1,
            'flag_popup' => 1,
        ]);

        // Return insert database ID
        return $db->insert_id();
    }

    /**
     * Updates popups
     *
     * @since 1.6.0
     *
     * @param int $id The database ID of the popup to be updated
     * @param string $title The new title of the popup
     * @param array $data The new settings of the popup
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function update($id = 0, $title = 'Unnamed', $data = [])
    {
        // Check ID
        if (!is_int($id)) {
            return false;
        }

        // Popup data
        $data = $data ?: [
            'properties' => ['title' => $title],
            'layers' => [[]],
        ];

        // Fix issue with longer varchars
        // than the column length
        if (Tools::strlen($title) > 99) {
            $title = Tools::substr($title, 0, 99 - Tools::strlen($title));
        }

        // Status
        $status = 0;
        if (empty($data['properties']['status']) || 'false' === $data['properties']['status']) {
            $status = 1;
        }

        // Schedule
        $schedule = ['schedule_start' => 0, 'schedule_end' => 0];
        foreach ($schedule as $key => $val) {
            $val += 0;
            if (!empty($data['properties'][$key])) {
                if (is_numeric($data['properties'][$key])) {
                    $schedule[$key] = (int) $data['properties'][$key];
                } else {
                    $tz = date_default_timezone_get();
                    date_default_timezone_set(cp_get_option('timezone_string'));
                    $schedule[$key] = (int) strtotime(str_replace('/', '-', $data['properties'][$key]));
                    date_default_timezone_set($tz);
                }
            }
        }

        // Update
        return Db::getInstance()->update('creativepopup', [
            'name' => pSQL($title),
            'data' => pSQL(json_encode($data), true),
            'schedule_start' => (int) $schedule['schedule_start'],
            'schedule_end' => (int) $schedule['schedule_end'],
            'date_m' => (int) time(),
            'flag_hidden' => (int) $status,
            'flag_popup' => 1,
        ], '`id` = ' . (int) $id, 1)
            && cp_delete_transient('cp-popup-data-' . $id);
    }

    /**
     * Marking a popup as removed without deleting it
     * with its database ID.
     *
     * @since 1.6.0
     *
     * @param int $id The database ID if the popup to remove
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function remove($id = null)
    {
        // Check ID
        if (!is_int($id)) {
            return false;
        }

        // Remove
        return Db::getInstance()->update('creativepopup', [
            'flag_deleted' => 1,
            'flag_hidden' => 1,
        ], '`id` = ' . (int) $id, 1)
            && CpPopups::removeIndex($id)
            && cp_delete_transient('cp-popup-data-' . $id);
    }

    /**
     * Delete a popup by its database ID
     *
     * @since 1.6.0
     *
     * @param int $id The database ID if the popup to delete
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function delete($id = null)
    {
        // Check ID
        if (!is_int($id)) {
            return false;
        }

        // Delete
        return Db::getInstance()->delete('creativepopup', '`id` = ' . (int) $id, 1)
            && CpPopups::removeIndex($id)
            && cp_delete_transient('cp-popup-data-' . $id);
    }

    /**
     * Restore a popup marked as removed previously by its database ID.
     *
     * @since 1.6.0
     *
     * @param int $id The database ID if the popup to restore
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function restore($id = null)
    {
        // Check ID
        if (!is_int($id)) {
            return false;
        }

        // Restore
        return Db::getInstance()->update('creativepopup', [
            'flag_deleted' => 0,
            'flag_hidden' => 1,
        ], '`id` = ' . (int) $id, 1);
    }

    private static function getById($id = null)
    {
        // Check ID
        if (!is_int($id)) {
            return false;
        }

        // Get popup
        $result = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'creativepopup WHERE `id` = ' . (int) $id
        );

        // Decode popup data
        if ($result) {
            $result['data'] = json_decode($result['data'], true);

            if (!empty($result['data']['properties'])) {
                $result['data']['properties']['status'] = !$result['flag_hidden'];
            }
        }

        return $result;
    }

    private static function getByIds(array $ids = [])
    {
        // Check IDs
        if (!$ids) {
            return false;
        }

        // Get popups
        $limit = count($ids);
        $result = Db::getInstance()->executeS('
            SELECT * FROM ' . _DB_PREFIX_ . 'creativepopup
            WHERE `id` IN (' . implode(',', array_map('intval', $ids)) . ')
            ORDER BY `id` DESC LIMIT ' . (int) $limit
        );

        // Decode popup data
        if ($result) {
            foreach ($result as &$popup) {
                $popup['data'] = json_decode($popup['data'], true);

                if (!empty($popup['data']['properties'])) {
                    $popup['data']['properties']['status'] = !$popup['flag_hidden'];
                }
            }
        }

        return $result;
    }

    /**
     * Is popup deleted
     *
     * @param int $id
     *
     * @return bool
     */
    public static function isDeleted($id)
    {
        return Db::getInstance()->getValue(
            'SELECT `flag_deleted` FROM ' . _DB_PREFIX_ . 'creativepopup WHERE `id` = ' . (int) $id
        );
    }

    /**
     * Publish / Unpublish popup(s)
     *
     * @param int|array $id
     * @param bool $published
     *
     * @return bool
     */
    public static function changeStatus($ids, $published)
    {
        is_array($ids) || $ids = [$ids];

        return Db::getInstance()->update(
            'creativepopup',
            ['flag_hidden' => (int) !$published],
            '`id` IN (' . implode(',', array_map('intval', $ids)) . ')',
            count($ids)
        ) && ($published
            ? CpPopups::addIndex($ids)
            : CpPopups::removeIndex($ids)
        );
    }
}
