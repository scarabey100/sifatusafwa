<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/autoload.php');

use MncKlevu\Klevu\Client as KlevuClient;
use MncKlevu\Klevu\Record as KlevuRecord;
use MncKlevu\PrestaShop\Adapter\Configuration as MncKlevuConfiguration;
use MncKlevu\PrestaShop\Adapter\Form\GeneralSettingsForm as MncKlevuGeneralSettingsForm;
use MncKlevu\PrestaShop\Adapter\Form\HomepageSettingsForm as MncKlevuHomepageSettingsForm;
use MncKlevu\PrestaShop\Adapter\Form\ProductPageSettingsForm as MncKlevuProductPageSettingsForm;
use MncKlevu\PrestaShop\Adapter\Form\ProductRecordAttributeSettingsForm as MncKlevuProductRecordAttributeSettingsForm;
use MncKlevu\PrestaShop\Adapter\Form\SearchSettingsForm as MncKlevuSearchSettingsForm;
use MncKlevu\PrestaShop\Adapter\Form\StoreSettingsForm as MncKlevuStoreSettingsForm;
use MncKlevu\PrestaShop\Adapter\Form\StoreSynchronizationForm as MncKlevuStoreSynchronizationForm;
use MncKlevu\PrestaShop\Adapter\Form\SynchronizationSettingsForm as MncKlevuSynchronizationSettingsForm;
use MncKlevu\PrestaShop\Adapter\Grid\ProductRecordAttributeGrid as MncKlevuProductRecordAttributeGrid;
use MncKlevu\PrestaShop\Adapter\Grid\StoreGrid as MncKlevuStoreGrid;
use MncKlevu\PrestaShop\Adapter\ObjectModel\OrderData as MncKlevuOrderData;
use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecord as MncKlevuProductRecord;
use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecordAttribute as MncKlevuProductRecordAttribute;
use MncKlevu\PrestaShop\Adapter\Presenter\Product\ProductPresenter as MncKlevuProductPresenter;
use MncKlevu\PrestaShop\Adapter\Product\ProductCategoriesRetriever as MncKlevuProductCategoriesRetriever;
use MncKlevu\PrestaShop\Adapter\TabManager as MncKlevuTabManager;
use MncKlevu\Synchronizer\Product\ProductSynchronizer as MncKlevuProductSynchronizer;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;

class MncKlevu extends Module implements WidgetInterface
{
    /**
     * @var string
     */
    const ID_SEARCH_BOX = 'klevu_search_box';

    /**
     * @var string
     */
    const FRONT_CONTROLLER_NAME_SEARCH_RESULTS = 'SearchResults';

    /**
     * @var string
     */
    const FRONT_CONTROLLER_NAME_SYNCHRONIZATION = 'Synchronization';

    /**
     * @var string
     */
    const HOOK_NAME_ACTION_GET_KLEVU_RECORD_OTHER = 'actionGetKlevuRecordOther';

    /**
     * @var string
     */
    const HOOK_NAME_ACTION_GET_KLEVU_RECORD_OTHER_ATTRIBUTE_TO_INDEX = 'actionGetKlevuRecordOtherAttributeToIndex';

    /**
     * @var string
     */
    const ANALYTICS_URL = 'https://stats.ksearchnet.com/analytics/productTracking';

    /**
     * @var bool
     */
    public $bootstrap;

    /**
     * @var string
     */
    public $confirmUninstall;

    /**
     * @var array
     */
    protected static $categoryPageProductCategories = [];

    /**
     * @var array
     */
    protected static $productNameMultilingual = [];

    /**
     * @var array
     */
    protected static $productShortDescriptionMultilingual = [];

    /**
     * @var array
     */
    protected static $productCategoriesMultilingual = [];

    /**
     * @var Smarty_Internal_Template[]
     */
    protected static $productStickersTemplate = [];

    /**
     * @var MncKlevuConfiguration
     */
    protected $configuration;

    /**
     * @var MncKlevuStoreGrid
     */
    protected $storeGrid;

    /**
     * @var MncKlevuStoreSettingsForm
     */
    protected $storeSettingsForm;

    /**
     * @var MncKlevuStoreSynchronizationForm
     */
    protected $storeSynchronizationForm;

    /**
     * @var MncKlevuSynchronizationSettingsForm
     */
    protected $synchronizationSettingsForm;

    /**
     * @var MncKlevuProductRecordAttributeGrid
     */
    protected $productRecordAttributeGrid;

    /**
     * @var MncKlevuProductRecordAttributeSettingsForm
     */
    protected $productRecordAttributeSettingsForm;

    /**
     * @var MncKlevuProductSynchronizer
     */
    protected $productSynchronizer;

    /**
     * @var MncKlevuSearchSettingsForm
     */
    protected $searchSettingsForm;

    /**
     * @var MncKlevuGeneralSettingsForm
     */
    protected $generalSettingsForm;

    /**
     * @var MncKlevuHomepageSettingsForm
     */
    protected $homepageSettingsForm;

    /**
     * @var MncKlevuProductPageSettingsForm
     */
    protected $productPageSettingsForm;

    /**
     * @var ProductPresentationSettings
     */
    protected $productSettings;

    /**
     * @var bool
     */
    protected $productStickersEnabled = false;

    public function __construct()
    {
        $this->name = 'mncklevu';
        $this->tab = 'front_office_features';
        $this->version = '1.6.0';
        $this->author = 'mnemonic88uk';
        $this->controllers = [self::FRONT_CONTROLLER_NAME_SYNCHRONIZATION];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Klevu');
        $this->description = $this->l('Klevu integration.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
        $this->configuration = new MncKlevuConfiguration($this);
        $this->storeGrid = new MncKlevuStoreGrid($this);
        $this->storeSettingsForm = new MncKlevuStoreSettingsForm($this, $this->storeGrid);
        $this->storeSynchronizationForm = new MncKlevuStoreSynchronizationForm($this, $this->storeGrid);
        $this->synchronizationSettingsForm = new MncKlevuSynchronizationSettingsForm($this);
        $this->productRecordAttributeGrid = new MncKlevuProductRecordAttributeGrid($this);
        $this->productRecordAttributeSettingsForm = new MncKlevuProductRecordAttributeSettingsForm($this,
            $this->productRecordAttributeGrid);
        $this->productSynchronizer = new MncKlevuProductSynchronizer($this);
        $this->searchSettingsForm = new MncKlevuSearchSettingsForm($this);
        $this->generalSettingsForm = new MncKlevuGeneralSettingsForm($this);
        $this->homepageSettingsForm = new MncKlevuHomepageSettingsForm($this);
        $this->productPageSettingsForm = new MncKlevuProductPageSettingsForm($this);

        $this->productSettings = (new ProductPresenterFactory(
            $this->context,
            new TaxConfiguration()
        ))->getPresentationSettings();

        if (Module::isEnabled($moduleName = 'ststickers')) {
            $className = 'StStickersClass';
            if (file_exists($classPath = dirname(__FILE__) . "/../$moduleName/classes/$className.php")) {
                require_once ($classPath);
                $this->productStickersEnabled = method_exists($className, 'getAll');
            }
        }
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install() &&
            (new MncKlevuTabManager($this))->addTab() &&
            (new MncKlevuOrderData())->createTable() &&
            (new MncKlevuProductRecord())->createTable() &&
            (new MncKlevuProductRecordAttribute())->createTable() &&
            $this->registerHook([
                'actionAdminControllerSetMedia',
                'actionObjectProductAddAfter',
                'actionObjectProductUpdateAfter',
                'actionObjectProductDeleteAfter',
                'moduleRoutes',
                'actionFrontControllerSetMedia',
                'displayHeader',
                'displayTop',
                'displayHome',
                'displayFooterProduct',
                MncKlevu::HOOK_NAME_ACTION_GET_KLEVU_RECORD_OTHER,
                MncKlevu::HOOK_NAME_ACTION_GET_KLEVU_RECORD_OTHER_ATTRIBUTE_TO_INDEX,
                'actionGetKlevuRecordAdditionalData',
                'displayOrderConfirmation'
            ]);
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $this->configuration
            ->delete(MncKlevuConfiguration::KEY_ALLOWED_IN_FRONTEND)
            ->delete(MncKlevuConfiguration::KEY_REST_AUTH_KEY)
            ->delete(MncKlevuConfiguration::KEY_JS_API_KEY)
            ->delete(MncKlevuConfiguration::KEY_APIV2_CLOUD_SEARCH_URL)
            ->delete(MncKlevuConfiguration::KEY_CONNECTED)
            ->delete(MncKlevuConfiguration::KEY_SYNCHRONIZED)
            ->delete(MncKlevuConfiguration::KEY_PRODUCT_COUNT_PER_REQUEST)
            ->delete(MncKlevuConfiguration::KEY_USE_ITEM_GROUP_ID)
            ->delete(MncKlevuConfiguration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT)
            ->delete(MncKlevuConfiguration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL)
            ->delete(MncKlevuConfiguration::KEY_HOMEPAGE_CONTENT)
            ->delete(MncKlevuConfiguration::KEY_PRODUCT_PAGE_CONTENT);

        return
            (new MncKlevuProductRecordAttribute())->dropTable() &&
            (new MncKlevuProductRecord())->dropTable() &&
            (new MncKlevuOrderData())->dropTable() &&
            (new MncKlevuTabManager($this))->deleteTab() &&
            parent::uninstall();
    }

    /**
     * @return MncKlevuConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return bool
     */
    public function getAllowedInFrontendStatus()
    {
        return (bool)$this->configuration->get(MncKlevuConfiguration::KEY_ALLOWED_IN_FRONTEND);
    }

    /**
     * @param int $languageId
     * @param int $shopId
     *
     * @return bool
     */
    public function getConnectionStatus($languageId = null, $shopId = null)
    {
        if ($languageId === null) {
            $languageId = $this->context->language->id;
        }

        if ($shopId !== null) {
            $shopId = (int)$shopId;
        }

        return
            $this->configuration->get(
                MncKlevuConfiguration::KEY_REST_AUTH_KEY,
                (int)$languageId,
                $shopId
            ) &&
            $this->configuration->get(
                MncKlevuConfiguration::KEY_CONNECTED,
                (int)$languageId,
                $shopId
            );
    }

    /**
     * @param int $languageId
     * @param int $shopId
     *
     * @return bool
     */
    public function getSynchronizationStatus($languageId = null, $shopId = null)
    {
        if ($languageId === null) {
            $languageId = $this->context->language->id;
        }

        if ($shopId !== null) {
            $shopId = (int)$shopId;
        }

        return
            $this->getConnectionStatus($languageId, $shopId) &&
            $this->configuration->get(
                MncKlevuConfiguration::KEY_SYNCHRONIZED,
                (int)$languageId,
                $shopId
            );
    }

    /**
     * @param int $languageId
     * @param int $shopId
     *
     * @return KlevuClient
     */
    public function getClient($languageId, $shopId = null)
    {
        if ($shopId !== null) {
            $shopId = (int)$shopId;
        }

        return new KlevuClient($this->configuration->get(
            MncKlevuConfiguration::KEY_REST_AUTH_KEY,
            (int)$languageId,
            $shopId
        ));
    }

    /**
     * @return MncKlevuProductSynchronizer
     */
    public function getProductSynchronizer()
    {
        return $this->productSynchronizer;
    }

    /**
     * @return string
     */
    protected function displayConnectionWarnings()
    {
        return implode('', array_map(
            function($language) {
                $languageId = (int)$language['id_lang'];

                if ($this->configuration->get(MncKlevuConfiguration::KEY_REST_AUTH_KEY, $languageId) &&
                    !$this->configuration->get(MncKlevuConfiguration::KEY_CONNECTED, $languageId)) {
                    return $this->displayWarning(str_replace(
                        '%language%',
                        $language['name'],
                        $this->l('Failed to check connection. Please make sure the REST AUTH key is correct (language: %language%).')
                    ));
                }
            },
            Language::getLanguages(false)
        ));
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return Tools::hash($this->name);
    }

    /**
     * @return string
     */
    protected function getSynchronizationControllerUrl()
    {
        return $this->context->link->getBaseLink(null, true) . 'module/' . $this->name . '/' .
            self::FRONT_CONTROLLER_NAME_SYNCHRONIZATION . '?token=' . $this->getToken();
    }

    /**
     * @return string
     */
    protected function displayCronJobInformation()
    {
        return $this->displayInformation(str_replace(
            [
                '[1]',
                '[2]',
                '%url%',
                '[/2]',
            ],
            [
                '<br>',
                '<strong>',
                $this->getSynchronizationControllerUrl(),
                '</strong>',
            ],
            $this->l('You can set a cron job that will synchronize products using the following URL:[1][2]%url%[/2]')
        ));
    }

    /**
     * @return string
     */
    public function displaySynchronizeLink($token, $id, $name = null)
    {
        return $this->storeGrid->displaySynchronizeLink($token, $id, $name);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        // $this->productSynchronizer->updateProduct(414, 1);

        if (Tools::isSubmit($this->synchronizationSettingsForm->getSubmitAction())) {
            $this->synchronizationSettingsForm->submit();
        } elseif (Tools::isSubmit($this->productRecordAttributeGrid
            ->getChangeStatusAction(MncKlevuProductRecordAttributeGrid::STATUS_FILTERABLE))) {
            $this->productRecordAttributeGrid->changeStatus(MncKlevuProductRecordAttributeGrid::STATUS_FILTERABLE);
        } elseif (Tools::isSubmit($this->productRecordAttributeGrid->getDeleteAction())) {
            $this->productRecordAttributeGrid->deleteItem();
        } elseif (Tools::isSubmit($this->productRecordAttributeSettingsForm->getSubmitAction())) {
            $this->productRecordAttributeSettingsForm->submit();
        } elseif (Tools::isSubmit($this->storeSettingsForm->getSubmitAction())) {
            $this->storeSettingsForm->submit();
        } elseif (Tools::isSubmit($this->searchSettingsForm->getSubmitAction())) {
            $this->searchSettingsForm->submit();
        } elseif (Tools::isSubmit($this->generalSettingsForm->getSubmitAction())) {
            $this->generalSettingsForm->submit();
        } elseif (Tools::isSubmit($this->homepageSettingsForm->getSubmitAction())) {
            $this->homepageSettingsForm->submit();
        } elseif (Tools::isSubmit($this->productPageSettingsForm->getSubmitAction())) {
            $this->productPageSettingsForm->submit();
        }

        $html = '';

        if (
            Tools::isSubmit($this->productRecordAttributeGrid->getAddAction()) ||
            Tools::isSubmit($this->productRecordAttributeGrid->getEditAction())
        ) {
            $html .= $this->productRecordAttributeSettingsForm->displayErrors();
            $html .= $this->productRecordAttributeSettingsForm->displayForm();
        } elseif (Tools::isSubmit($this->storeGrid->getEditAction())) {
            $html .= $this->storeSettingsForm->displayErrors();
            $html .= $this->storeSettingsForm->displayForm();
        } elseif (
            Tools::isSubmit($this->storeGrid->getIdentifier()) &&
            Tools::isSubmit($this->storeGrid->getSynchronizeAction())
        ) {
            $html .= $this->storeSynchronizationForm->displayForm();
        } else {
            $html .= $this->synchronizationSettingsForm->displayErrors();
            $html .= $this->productRecordAttributeGrid->displayErrors();
            $html .= $this->searchSettingsForm->displayErrors();
            $html .= $this->homepageSettingsForm->displayErrors();
            $html .= $this->productPageSettingsForm->displayErrors();
            $html .= $this->displayConnectionWarnings();
            $html .= $this->displayCronJobInformation();
            $html .= $this->generalSettingsForm->displayForm();
            $html .= $this->synchronizationSettingsForm->displayForm();
            $html .= $this->productRecordAttributeGrid->displayGrid();
            $html .= $this->storeGrid->displayGrid();
            $html .= $this->searchSettingsForm->displayForm();
            $html .= $this->homepageSettingsForm->displayForm();
            $html .= $this->productPageSettingsForm->displayForm();
        }

        return $html;
    }

    /**
     * @param mixed $params
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        if ((Tools::getValue('controller') === 'AdminModules') && (Tools::getValue('configure') === $this->name)) {
            Media::addJsDef([
                $this->name => [
                    'сonnection_warning_message' => $this->l('You need to connect the store in order to synchronize products.')
                ]
            ]);

            $this->context->controller->addJS($this->_path . 'views/js/admin/admin.js');
            $this->context->controller->addCSS($this->_path . 'views/css/admin/admin.css');
        }
    }

    /**
     * @param mixed $params
     */
    public function hookActionObjectProductAddAfter($params)
    {
        /** @var Product $product */
        $product = $params['object'];

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            if ($this->getConnectionStatus($languageId) &&
                $product->active &&
                in_array($product->visibility, ['both', 'catalog'])) {
                $this->productSynchronizer->updateProduct($product->id, $languageId);
            }
        }
    }

    /**
     * @param mixed $params
     */
    public function hookActionObjectProductUpdateAfter($params)
    {
        /** @var Product $product */
        $product = $params['object'];

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            if (!$this->getConnectionStatus($languageId)) {
                continue;
            }

            if ($product->active && in_array($product->visibility, ['both', 'catalog'])) {
                $this->productSynchronizer->updateProduct($product->id, $languageId);
            } else {
                $this->productSynchronizer->deleteProduct($product->id, $languageId);
            }
        }
    }

    /**
     * @param mixed $params
     */
    public function hookActionObjectProductDeleteAfter($params)
    {
        /** @var Product $product */
        $product = $params['object'];

        // foreach (Shop::getShops(false, null, true) as $shopId) {
            foreach (Language::getLanguages(false, false, true) as $languageId) {
                // if (!$this->getConnectionStatus($languageId, $shopId)) {
                if ($this->getConnectionStatus($languageId)) {
                    // $this->productSynchronizer->deleteProduct($product->id, $languageId, $shopId);
                    $this->productSynchronizer->deleteProduct($product->id, $languageId);
                }
            }
        // }
    }

    /**
     * @param mixed $params
     *
     * @return array
     */
    public function hookModuleRoutes($params)
    {
        if (!$this->getAllowedInFrontendStatus() || !$this->getConnectionStatus()) {
            return [];
        }

        return [
            'module-' . $this->name . '-' . self::FRONT_CONTROLLER_NAME_SEARCH_RESULTS => [
                'controller' =>  self::FRONT_CONTROLLER_NAME_SEARCH_RESULTS,
                'rule' => $this->configuration->get(
                    MncKlevuConfiguration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL,
                    $this->context->language->id
                ),
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name
                ]
            ]
        ];
    }

    /**
     * @return AdminController|FrontController
     */
    protected function getController()
    {
        return $this->context->controller;
    }

    /**
     * @return string
     */
    protected function getSearchResultsPageFriendlyUrl()
    {
        return $this->context->link->getModuleLink($this->name, self::FRONT_CONTROLLER_NAME_SEARCH_RESULTS, [], true);
    }

    /**
     * @param Currency $currency
     *
     * @return string
     */
    protected function getCurrencyThousandsSeparator(Currency $currency)
    {
        return ',';
    }

    /**
     * @param Currency $currency
     *
     * @return string
     */
    protected function getCurrencyDecimalSeparator(Currency $currency)
    {
        return '.';
    }

    /**
     * @param Currency $currency
     *
     * @return bool
     */
    protected function getCurrencySymbolOnLeft(Currency $currency)
    {
        return true;
    }

    /**
     * @param Currency $currency
     *
     * @return bool
     */
    protected function getCurrencySpaceBetweenAmountAndSymbol(Currency $currency)
    {
        return false;
    }

    /**
     * @param Currency $currency
     *
     * @return int
     */
    protected function getCurrencyDecimalDigits(Currency $currency)
    {
        return 2;
    }

    /**
     * @return array
     */
    protected function getCurrencies()
    {
        $result = [];
        /** @var Currency[] $currencies */
        $currencies = Currency::getCurrencies(true);
        if (is_array($currencies)) {
            foreach ($currencies as $currency) {
                $code = strtoupper($currency->iso_code);

                $result[$code] = [
                    'code' => $code,
                    'symbol' => $currency->sign,
                    'thousandsSeparator' => $this->getCurrencyThousandsSeparator($currency),
                    'decimalSeparator' => $this->getCurrencyDecimalSeparator($currency),
                    'symbolOnLeft' => $this->getCurrencySymbolOnLeft($currency),
                    'spaceBetweenAmountAndSymbol' => $this->getCurrencySpaceBetweenAmountAndSymbol($currency),
                    'decimalDigits' => $this->getCurrencyDecimalDigits($currency),
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getSortByOptions()
    {
        return [
            'RELEVANCE' => $this->l('Relevance'),
            'PRICE_ASC' => $this->l('Price: Low to high'),
            'PRICE_DESC' => $this->l('Price: High to low'),
            'NAME_ASC' => $this->l('Name: A to Z'),
            'NAME_DESC' => $this->l('Name: Z to A'),
            'NEW_ARRIVAL_DESC' => $this->l('New products first'),
        ];
    }

    /**
     * @return array
     */
    protected function getTranslations()
    {
        return [
            'tabResults' => [
                'productList' => str_replace(['[1]', '[/1]'], ['<b>', '</b>'], $this->l('[1]%s[/1] Products')),
                'contentList' => str_replace(['[1]', '[/1]'], ['<b>', '</b>'], $this->l('[1]%s[/1] Other results')),
            ],
            'filter' => [
                'price' => $this->l('Price'),
            ],
        ];
    }

    /**
     * @param int $productId
     *
     * @return false|array
     */
    protected function getKlevuProductPageMeta($productId)
    {
        $product = new Product((int)$productId, true, (int)$this->context->language->id);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        $product = (new MncKlevuProductPresenter())->presentVariant($product, $this->context->language,
            $product->getDefaultIdProductAttribute());

        $result = [
            'pageType' => 'pdp',
            'itemName' => $product->name,
            'itemUrl' => $product->url,
            'itemSalePrice' => $product->price_amount,
            'itemCurrency' => $this->context->currency->iso_code
        ];

        $itemId = MncKlevuProductRecord::getRecordId($product->id, $product->id_product_attribute,
            $this->context->language->id);
        if ($itemId) {
            $result['itemId'] = $itemId;
        }

        return $result;
    }

    /**
     * @return false|array
     */
    protected function getKlevuOrderData()
    {
        $order = new Order((int)Order::getIdByCartId(Tools::getValue('id_cart')));
        if (!Validate::isLoadedObject($order) || !is_array($products = $order->getCartProducts())) {
            return false;
        }

        $jsApiKey = $this->configuration->get(MncKlevuConfiguration::KEY_JS_API_KEY, $this->context->language->id);
        $currency = Currency::getCurrencyInstance((int)$order->id_currency);
        $useItemGroupId = $this->configuration->get(MncKlevuConfiguration::KEY_USE_ITEM_GROUP_ID);
        $result = [];

        foreach ($products as $product) {
            if (MncKlevuOrderData::getIdByOrderDetailId($product['id_order_detail'])) {
                continue;
            }

            $data = [
                'klevu_apiKey' => $jsApiKey,
                'klevu_type' => 'checkout',
                'klevu_unit' => (int)$product['product_quantity'],
                'klevu_salePrice' => (float)$product['product_price_wt'],
                'klevu_currency' => $currency->iso_code
            ];

            if (!$product['id_product_attribute']) {
                $klevuProductId = $this->productSynchronizer->transformProductId($product['id_product']);
                $data['klevu_productId'] = $klevuProductId;
                $data['klevu_productGroupId'] = $klevuProductId;
                $data['klevu_productVariantId'] = $klevuProductId;
            } else {
                $klevuProductId = $this->productSynchronizer->transformVariantId($product['id_product_attribute']);
                $data['klevu_productId'] = $klevuProductId;
                $data['klevu_productGroupId'] = $useItemGroupId ?
                    $this->productSynchronizer->transformProductId($product['id_product']) : $klevuProductId;
                $data['klevu_productVariantId'] = $klevuProductId;
            }

            $result[] = http_build_query($data);

            (new MncKlevuOrderData())
                ->setOrderDetailId($product['id_order_detail'])
                ->setKlevuProductId($data['klevu_productId'])
                ->setKlevuProductGroupId($data['klevu_productGroupId'])
                ->setKlevuProductVariantId($data['klevu_productVariantId'])
                ->setKlevuUnit($data['klevu_unit'])
                ->setKlevuSalePrice($data['klevu_salePrice'])
                ->setKlevuCurrency($data['klevu_currency'])
                ->setKlevuShopperIp(0)
                ->save();
        }

        return $result;
    }

    protected function addJsDef()
    {
        $data = [
            'searchResultsPageFriendlyUrl' => $this->getSearchResultsPageFriendlyUrl(),
            'apiv2CloudSearchUrl' => $this->configuration->get(
                MncKlevuConfiguration::KEY_APIV2_CLOUD_SEARCH_URL,
                $this->context->language->id
            ),
            'searchBoxMinimalCharacterCount' => (int)$this->configuration
                ->get(MncKlevuConfiguration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT),
            'searchBoxId' => self::ID_SEARCH_BOX,
            
            // --- SECURITY CHANGE 1: API KEY REMOVED FROM PUBLIC JS DEFS ---
            /*
             'jsApiKey' => $this->configuration->get(
                 MncKlevuConfiguration::KEY_JS_API_KEY,
                 $this->context->language->id
             ),
            */
            // --- SECURITY CHANGE 2: ADD NEW PROXY URL FOR FRONTEND CALLS ---
            'klevuProxyUrl' => $this->context->link->getModuleLink(
                $this->name, 
                'proxy', // Link to the MncklevuProxyModuleFrontController
                [],
                true // Force SSL
            ),
            
            'priceFieldSuffix' => $this->context->currency->iso_code,
            'currencySymbol' => $this->context->currency->sign,
            'currencies' => $this->getCurrencies(),
            'sortByOptions' => $this->getSortByOptions(),
            'translations' => $this->getTranslations(),
        ];

        switch (Dispatcher::getInstance()->getController()) {
            case 'product':
                $data['pageMeta'] = $this->getKlevuProductPageMeta(Tools::getValue('id_product'));
                break;

            case 'orderconfirmation':
                $data['orderData'] = $this->getKlevuOrderData();
                break;
        }

        Media::addJsDef([$this->name => $data]);
        
        Media::addJsDef(['ajaxUrlFront' => $this->context->link->getModuleLink('mncklevu', 'Synchronization')]);
        
        Media::addJsDef(['token' => $this->getToken()]);
       
    }

    /**
     * @param mixed $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        if (!$this->getAllowedInFrontendStatus() || !$this->getConnectionStatus()) {
            return;
        }

        $this->getController()->registerJavascript(
            'modules-' . $this->name . '-klevu',
            'https://js.klevu.com/core/v2/klevu.js',
            ['position' => 'head', 'server' => 'remote']
        );

        $this->getController()->registerJavascript(
            'modules-' . $this->name . '-klevu-recs',
            'https://js.klevu.com/recs/v2/klevu-recs.js',
            ['position' => 'head', 'server' => 'remote']
        );

        $this->getController()->registerJavascript(
            'modules-' . $this->name . '-quick-search',
            'https://js.klevu.com/theme/default/v2/quick-search.js',
            ['position' => 'head', 'server' => 'remote']
        );

        $this->getController()->registerJavascript(
            'modules-' . $this->name . '-front',
            'modules/' . $this->name . '/views/js/front/front.js',
            ['position' => 'bottom', 'priority' => 150]
        );

        $this->getController()->registerStylesheet(
            'modules-' . $this->name . '-front',
            'modules/' . $this->name . '/views/css/front/front.css',
            array('media' => 'all', 'priority' => 150)
        );

        $this->addJsDef();
    }

    /**
     * @param mixed $params
     */
    public function hookDisplayHeader($params)
    {
        return '';
    }

    /**
     * @param mixed $params
     *
     * @return string
     */
    public function hookDisplayHome($params)
    {
        return str_replace('title="http', 'data-link="http',
            $this->configuration->get(MncKlevuConfiguration::KEY_HOMEPAGE_CONTENT, $this->context->language->id));
    }

    /**
     * @param mixed $params
     *
     * @return string
     */
    public function hookDisplayFooterProduct($params)
    {
        return $this->configuration->get(MncKlevuConfiguration::KEY_PRODUCT_PAGE_CONTENT,
            $this->context->language->id);
    }

    /**
     * @param string $hookName
     * @param array $configuration
     *
     * @return array
     */
    public function getWidgetVariables($hookName, array $configuration)
    {
        return ['search_box_id' => self::ID_SEARCH_BOX];
    }

    /**
     * @param string $hookName
     * @param array $configuration
     *
     * @return string
     */
    public function renderWidget($hookName, array $configuration)
    {
        if (!$this->getAllowedInFrontendStatus() || !$this->getConnectionStatus()) {
            return '';
        }

        ($template = $this->context->smarty->createTemplate(
            'module:' . $this->name . '/views/templates/hook/search_box.tpl'
        ))->assign($this->getWidgetVariables($hookName, $configuration));

        return $template->fetch();
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return array
     */
    protected function getCategoryPageProductCategories(ProductLazyArray $product, $languageId)
    {
        if (!isset(static::$categoryPageProductCategories[$product->id])) {
            $retriever = new MncKlevuProductCategoriesRetriever();
            $categories = [];

            foreach ($retriever->getMostSpecificCategories($product->id, $languageId) as $hierarchy) {
                array_shift($hierarchy);

                foreach ($hierarchy as $name) {
                    $categories[$name] = true;
                }
            }

            static::$categoryPageProductCategories[$product->id] = array_keys($categories);
        }

        return static::$categoryPageProductCategories[$product->id];
    }

    /**
     * @param ProductLazyArray $product
     *
     * @return bool
     */
    protected function getProductInStockStatus(ProductLazyArray $product)
    {
        if ($this->productSettings->stock_management_enabled &&
            !$product->allow_oosp &&
            ($product->quantity <= 0)) {
            return false;
        }

        return (bool)$product->available_for_order;
    }

    /**
     * @param mixed $params
     *
     * @return array
     */
    public function hookActionGetKlevuRecordOther($params)
    {
        $result = [];

        if ($params['record_type'] === KlevuRecord::TYPE_PRODUCT) {
            /** @var ProductLazyArray $product */
            $product = $params['source_object'];
            $languageId = (int)$params['language_id'];
            $locale = $this->getProductSynchronizer()->getLocale($languageId);

            if ($categories = $this->getCategoryPageProductCategories($product, $languageId)) {
                $result[] = [
                    'id' => 'categoryPage_category',
                    'name' => $this->l('Category', false, $locale),
                    'values' => $categories,
                ];
            }

            if ($this->getProductInStockStatus($product)) {
                $result[] = [
                    'id' => 'inStock_facet',
                    'name' => $this->l('Availability', false, $locale),
                    'values' => [$this->l('In stock', false, $locale)],
                ];
            }
        }

        return $result;
    }

    /**
     * @param ProductLazyArray $product
     * @param int $productLanguageId
     *
     * @return array
     */
    protected function getProductNameMultilingual(ProductLazyArray $product, $productLanguageId)
    {
        if (!isset(static::$productNameMultilingual[$product->id])) {
            $productLanguageId = (int)$productLanguageId;
            static::$productNameMultilingual[$product->id] = [];

            foreach (Language::getLanguages(false, false, true) as $languageId) {
                if ((int)$languageId != $productLanguageId) {
                    static::$productNameMultilingual[$product->id][] =
                        Product::getProductName($product->id, null, $languageId);
                }
            }
        }

        return static::$productNameMultilingual[$product->id];
    }

    /**
     * @param int $productId
     * @param int $languageId
     *
     * @return string
     */
    protected function getProductShortDescription($productId, $languageId)
    {
        $query = (new DbQuery())
            ->select('pl.description_short')
            ->from('product_lang', 'pl')
            ->where('pl.id_product = ' . (int)$productId)
            ->where('pl.id_lang = ' . (int)$languageId)
            ->where('pl.id_shop = ' . (int)$this->context->shop->id)
            ->build();

        return (string)Db::getInstance()->getValue($query);
    }

    /**
     * @param ProductLazyArray $product
     * @param int $productLanguageId
     *
     * @return array
     */
    protected function getProductShortDescriptionMultilingual(ProductLazyArray $product, $productLanguageId)
    {
        if (!isset(static::$productShortDescriptionMultilingual[$product->id])) {
            $productLanguageId = (int)$productLanguageId;
            static::$productShortDescriptionMultilingual[$product->id] = [];

            foreach (Language::getLanguages(false, false, true) as $languageId) {
                if (((int)$languageId != $productLanguageId) &&
                    ($description = strip_tags($this->getProductShortDescription($product->id, $languageId)))) {
                    static::$productShortDescriptionMultilingual[$product->id][] = $description;
                }
            }
        }

        return static::$productShortDescriptionMultilingual[$product->id];
    }

    /**
     * @param ProductLazyArray $product
     * @param int $productLanguageId
     *
     * @return array
     */
    protected function getProductCategoriesMultilingual(ProductLazyArray $product, $productLanguageId)
    {
        if (!isset(static::$productCategoriesMultilingual[$product->id])) {
            $productLanguageId = (int)$productLanguageId;
            $retriever = new MncKlevuProductCategoriesRetriever();
            $categories = [];

            foreach (Language::getLanguages(false, false, true) as $languageId) {
                if ((int)$languageId == $productLanguageId) {
                    // continue;
                }

                foreach ($retriever->getMostSpecificCategories($product->id, $languageId) as $hierarchy) {
                    foreach ($hierarchy as $name) {
                        $categories[$name] = true;
                    }
                }
            }

            static::$productCategoriesMultilingual[$product->id] = array_keys($categories);
        }

        return static::$productCategoriesMultilingual[$product->id];
    }

    /**
     * @param mixed $params
     *
     * @return array
     */
    public function hookActionGetKlevuRecordOtherAttributeToIndex($params)
    {
        $result = [];

        if ($params['record_type'] === KlevuRecord::TYPE_PRODUCT) {
            /** @var ProductLazyArray $product */
            $product = $params['source_object'];
            $languageId = (int)$params['language_id'];
            $locale = $this->getProductSynchronizer()->getLocale($languageId);

            if (isset($product->name_ar) && ($nameInArabic = trim($product->name_ar))) {
                $result[] = [
                    'id' => 'nameInArabic',
                    'name' => $this->l('Name in Arabic', false, $locale),
                    'values' => [$nameInArabic],
                ];
            }
            
            if ($name = $this->getProductNameMultilingual($product, $languageId)) {
                $result[] = [
                    'id' => 'name_multilingual',
                    'name' => $this->l('Name (multilingual)', false, $locale),
                    'values' => $name,
                ];
            }

            if ($shortDescription = $this->getProductShortDescriptionMultilingual($product, $languageId)) {
                $result[] = [
                    'id' => 'shortDescription_multilingual',
                    'name' => $this->l('Short Description (multilingual)', false, $locale),
                    'values' => $shortDescription,
                ];
            }

            if ($categories = $this->getProductCategoriesMultilingual($product, $languageId)) {
                $result[] = [
                    'id' => 'categories_multilingual',
                    'name' => $this->l('Categories (multilingual)', false, $locale),
                    'values' => $categories,
                ];
            }

            if ($isbn = trim($product->isbn)) {
                $result[] = [
                    'id' => 'isbn',
                    'name' => $this->l('ISBN', false, $locale),
                    'values' => [$isbn],
                ];
            }
        }

        return $result;
    }

    /**
     * @return array|null
     */
    protected function getProductsMarkedAsOld()
    {
        if (Module::isEnabled('mncststickersextension')) {
            $query = (new DbQuery())
                ->select('p.id_product')
                ->from('mnc_st_stickers_extension_product', 'p')
                ->where('p.condition = 2');
            
            if (is_array($result = Db::getInstance()->executeS($query)) && (count($result) > 0)) {
                return array_map(
                    function($item) {
                        return $item['id_product'];
                    },
                    $result
                );
            }
        }

        return null;
    }

    /**
     * @param int $languageId
     *
     * @return Smarty_Internal_Template|false
     */
    protected function getProductStickersTemplate($languageId)
    {
        if (!isset(self::$productStickersTemplate[$languageId])) {
            if (!$this->productStickersEnabled) {
                self::$productStickersTemplate[$languageId] = null;
            } else {
                (self::$productStickersTemplate[$languageId] = $this->context->smarty->createTemplate(
                    _PS_ALL_THEMES_DIR_ . _THEME_NAME_ . '/templates/catalog/_partials/miniatures/sticker.tpl'
                ))->assign([
                    'ststickers' => StStickersClass::getAll($languageId, 1, [1, 2, 3, 4, 5, 6, 7]),
                    'marked_as_old' => $this->getProductsMarkedAsOld(),
                    'sticker_position' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 12]
                ]);
            }
        }

        return self::$productStickersTemplate[$languageId];
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    protected function getProductStickersIds($productId)
    {
        if (Validate::isLoadedObject($product = new Product((int)$productId))) {
            $categories = $product->getCategories();
            $manufacturerId = (int)$product->id_manufacturer;

            $query = (new DbQuery())
                ->select('m.id_st_sticker')
                ->from('st_sticker_map', 'm')
                ->where('m.active = 1 AND (
                    m.location = 0 OR (m.location = 1 AND (m.id_products LIKE "%,' . (int)$product->id . ',%"))' .
                    ($categories ? ' OR (m.location = 2 AND (m.id_category = 0 OR m.id_category IN(' .
                        implode(',', $categories) . ')))' : '') .
                    ($manufacturerId ? ' OR (m.location = 3 AND (m.id_manufacturer = 0 OR m.id_manufacturer = ' .
                        $manufacturerId . '))' : '') . '
                )');

            if (is_array($data = Db::getInstance()->executeS($query)) && (count($data) > 0)) {
                $result = [];

                foreach ($data as $item) {
                    $result[$item['id_st_sticker']] = $item['id_st_sticker'];
                }

                return $result;
            }
        }

        return [];
    }

    /**
     * @param int $productAttributeId
     * @param int $languageId
     *
     * @return array|null
     */
    protected function getProductAttributeSticker($productAttributeId, $languageId)
    {
        if (Module::isEnabled($moduleName = 'mncststickersextension') && $productAttributeId) {
            /** @var MncStStickersExtension $module */
            $module = Module::getInstanceByName($moduleName);
            $data = $module->getCombinationDataByProductAttributeId($productAttributeId);
            if (Validate::isLoadedObject($data) &&
                $data->id_st_sticker &&
                is_array($result = StStickersClass::getAll($languageId, 1, 0, (string)$data->id_st_sticker))) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return array
     */
    protected function getProductStickers(ProductLazyArray $product, $languageId)
    {
        $result = [];

        if (($ids = $this->getProductStickersIds($product->id)) &&
            is_array($stickers = StStickersClass::getAll($languageId, 1, 0, implode(',', $ids)))) {
            $result = $stickers;
        }

        if ($sticker = $this->getProductAttributeSticker($product->id_product_attribute, $languageId)) {
            $result = array_merge($result, $sticker);
        }

        return $result;
    }

    /**
     * @param ProductLazyArray $product
     * @param int $languageId
     *
     * @return string
     */
    protected function getProductStickersString(ProductLazyArray $product, $languageId)
    {
        if ($template = $this->getProductStickersTemplate($languageId)) {
            $template->assign([
                'product' => $product,
                'stickers' => $this->getProductStickers($product, $languageId),
                'sticker_quantity' => $product->quantity,
                'sticker_allow_oosp' => $product->allow_oosp,
                'sticker_quantity_all_versions' => $product->quantity_all_versions,
                'sticker_stock_text' => $product->availability_message
            ]);

            if ($html = trim(preg_replace('/[ \t\r\n\f]+/', ' ', $template->fetch()))) {
                return htmlspecialchars($html, ENT_NOQUOTES, 'utf-8');
            }
        }

        return '';
    }

    /**
     * @param mixed $params
     *
     * @return array
     */
    public function hookActionGetKlevuRecordAdditionalData($params)
    {
        if ($params['record_type'] === KlevuRecord::TYPE_PRODUCT) {
            /** @var ProductLazyArray $product */
            $product = $params['source_object'];

            return [
                'id_product' => $product->id,
                'id_product_attribute' => $product->id_product_attribute,
                'minimal_quantity' => $product->minimal_quantity,
                'name_ar' => isset($product->name_ar) ? $product->name_ar : '',
                'stickers' => $this->getProductStickersString($product, (int)$params['language_id'])
            ];
        }

        return [];
    }

    /**
     * @param string $xml
     *
     * @return array|false
     */
    protected function convertXmlToArray($xml)
    {
        if ($object = @simplexml_load_string($xml)) {
            return (array)@json_decode(json_encode((array)$object), true);
        }

        return false;
    }

    /**
     * @param mixed $params
     */
    public function hookDisplayOrderConfirmation($params)
    {
        // if (!isset($params['order'])) {
        //     return;
        // }

        // /** @var Order $order */
        // $order = $params['order'];

        // if (!is_array($products = $order->getCartProducts())) {
        //     return;
        // }

        // curl_setopt_array($handle = curl_init(), [
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET'
        // ]);

        // $jsApiKey = $this->configuration->get(MncKlevuConfiguration::KEY_JS_API_KEY, $this->context->language->id);
        // $currency = Currency::getCurrencyInstance((int)$order->id_currency);
        // $useItemGroupId = $this->configuration->get(MncKlevuConfiguration::KEY_USE_ITEM_GROUP_ID);

        // foreach ($products as $product) {
        //     if (MncKlevuOrderData::getIdByOrderDetailId($product['id_order_detail'])) {
        //         continue;
        //     }

        //     $data = [
        //         'klevu_apiKey' => $jsApiKey,
        //         'klevu_type' => 'checkout',
        //         'klevu_unit' => (int)$product['product_quantity'],
        //         'klevu_salePrice' => (float)$product['product_price_wt'],
        //         'klevu_currency' => $currency->iso_code,
        //         'klevu_shopperIP' => Tools::getRemoteAddr()
        //     ];

        //     if (!$product['id_product_attribute']) {
        //         $klevuProductId = $this->productSynchronizer->transformProductId($product['id_product']);
        //         $data['klevu_productId'] = $klevuProductId;
        //         $data['klevu_productGroupId'] = $klevuProductId;
        //         $data['klevu_productVariantId'] = $klevuProductId;
        //     } else {
        //         $klevuProductId = $this->productSynchronizer->transformVariantId($product['id_product_attribute']);
        //         $data['klevu_productId'] = $klevuProductId;
        //         $data['klevu_productGroupId'] = $useItemGroupId ?
        //             $this->productSynchronizer->transformProductId($product['id_product']) : $klevuProductId;
        //         $data['klevu_productVariantId'] = $klevuProductId;
        //     }

        //     curl_setopt($handle, CURLOPT_URL, self::ANALYTICS_URL . '?' . http_build_query($data));

        //     $response = $this->convertXmlToArray(curl_exec($handle));
        //     if (isset($response['response']) && ($response['response'] === 'SUCCESS')) {
        //         (new MncKlevuOrderData())
        //             ->setOrderDetailId($product['id_order_detail'])
        //             ->setKlevuProductId($data['klevu_productId'])
        //             ->setKlevuProductGroupId($data['klevu_productGroupId'])
        //             ->setKlevuProductVariantId($data['klevu_productVariantId'])
        //             ->setKlevuUnit($data['klevu_unit'])
        //             ->setKlevuSalePrice($data['klevu_salePrice'])
        //             ->setKlevuCurrency($data['klevu_currency'])
        //             ->setKlevuShopperIp((int)ip2long($data['klevu_shopperIP']))
        //             ->save();
        //     }
        // }

        // curl_close($handle);
    }
	public function ThemingProducts(array $products = [], $slider = 0, $passed_title = '')
	{
	    if (!$products || !is_array($products))
		return array();

	    $assembler = new ProductAssembler($this->context);
	    $presenterFactory = new ProductPresenterFactory($this->context);
	    $presentationSettings = $presenterFactory->getPresentationSettings();
	    $presenter = new PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
		new PrestaShop\PrestaShop\Adapter\Image\ImageRetriever(
		    $this->context->link
		),
		$this->context->link,
		new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter(),
		new PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever(),
		$this->context->getTranslator()
	    );

	    $products_for_template = array();

	    foreach ($products as $item) {
		  
		$id_product_attribute = $item['id_product_attribute'];

		if (strpos($id_product_attribute, 'p') === 0) {
		    // If 'p' at the beginning, it's for id_product, so set id_product_attribute to 0
		    $id_product_attribute = 0;
		} elseif (strpos($id_product_attribute, 'v') === 0) {
		    // If 'v' at the beginning, remove 'v' and use the rest as id_product_attribute
		    $id_product_attribute = (int)substr($id_product_attribute, 1);  // Remove 'v' and convert the rest to an integer
		} else {
		    // If there's no 'p' or 'v', we return 0 for id_product_attribute
		    $id_product_attribute = 0;
		}

		// Now we fetch the corresponding data based on id_product_attribute
		if ($id_product_attribute == 0) {
		    // Get RAW product data for id_product
		    $rawProduct = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'product WHERE id_product = '.(int)$item['id_product']);
		} else {
		    // Get RAW product data for id_product_attribute
		    $rawProduct = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'product_attribute WHERE id_product_attribute = '.(int)$id_product_attribute);
		}
		if (!$rawProduct) {
		    // Product does not exist, skip
		    continue;
		}

		$assembledProduct = $assembler->assembleProduct($rawProduct);

		$products_for_template[] = $presenter->present(
		    $presentationSettings,
		    $assembledProduct,
		    $this->context->language
		);
	    }

	    $this->context->smarty->assign(array(
		'products' => $products_for_template,
        'passed_title' => $passed_title,
	    ));
 
        if ($slider == 0 ) {
            return $this->fetch('module:mncklevu/views/templates/front/products.tpl');
        } else {
            return $this->fetch('module:mncklevu/views/templates/front/products_slider.tpl');
        }

	}

}