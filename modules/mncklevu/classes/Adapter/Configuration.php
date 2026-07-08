<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter;

use Configuration as PsConfiguration;
use Context;
use Module;

class Configuration
{
    /**
     * @var string
     */
    const KEY_ALLOWED_IN_FRONTEND = 'allowed_in_frontend';

    /**
     * @var string
     */
    const KEY_REST_AUTH_KEY = 'rest_auth_key';

    /**
     * @var string
     */
    const KEY_JS_API_KEY = 'js_api_key';

    /**
     * @var string
     */
    const KEY_APIV2_CLOUD_SEARCH_URL = 'apiv2_cloud_search_url';

    /**
     * @var string
     */
    const KEY_CONNECTED = 'connected';

    /**
     * @var string
     */
    const KEY_SYNCHRONIZED = 'synchronized';

    /**
     * @var string
     */
    const KEY_PRODUCT_COUNT_PER_REQUEST = 'product_count_per_request';

    /**
     * @var string
     */
    const KEY_USE_ITEM_GROUP_ID = 'use_item_group_id';

    /**
     * @var string
     */
    const KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT = 'search_box_minimal_character_count';

    /**
     * @var string
     */
    const KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL = 'search_results_page_friendly_url';

    /**
     * @var string
     */
    const KEY_HOMEPAGE_CONTENT = 'homepage_content';

    /**
     * @var string
     */
    const KEY_PRODUCT_PAGE_CONTENT = 'product_page_content';

    /**
     * @var string
     */
    const KEY_PRODUCT_PAGE_CATEGORIES = 'product_page_categories';

    /**
     * @var string
     */
    protected $keyPrefix;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->keyPrefix = strtoupper($module->name) . '_';
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function transformKey($key)
    {
        return $this->keyPrefix . strtoupper((string)$key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $html
     * @param int $shopId
     *
     * @return $this
     */
    public function set($key, $value, $html = false, $shopId = null)
    {
        PsConfiguration::updateValue(
            $this->transformKey($key),
            $value,
            (bool)$html,
            null,
            $shopId ? (int)$shopId : Context::getContext()->shop->id
        );

        return $this;
    }

    /**
     * @param string $key
     * @param int $languageId
     * @param int $shopId
     *
     * @return string
     */
    public function get($key, $languageId = null, $shopId = null)
    {
        return PsConfiguration::get(
            $this->transformKey($key),
            $languageId,
            null,
            $shopId ? (int)$shopId : Context::getContext()->shop->id
        );
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function delete($key)
    {
        PsConfiguration::deleteByName($this->transformKey($key));

        return $this;
    }
}
