<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Presenter\Product;

use Context;
use Language;
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductPresenter as PsProductPresenter;
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;
use PrestaShop\PrestaShop\Core\Product\ProductPresentationSettings;
use Product;
use ProductPresenterFactory;
use TaxConfiguration;
use Validate;

class ProductPresenter
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProductPresenterFactory
     */
    protected $productPresenterFactory;

    /**
     * @var ProductExtraContentFinder
     */
    protected $extraContentFinder;

    /**
     * @var ObjectPresenter
     */
    protected $objectPresenter;

    public function __construct()
    {
        $this->context = Context::getContext();

        $this->productPresenterFactory = new ProductPresenterFactory(
            $this->context,
            new TaxConfiguration()
        );

        $this->extraContentFinder = new ProductExtraContentFinder();
        $this->objectPresenter = new ObjectPresenter();
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    public function getProductAttributesIds($productId)
    {
        $result = Product::getProductAttributesIds($productId, true);
        if (!is_array($result)) {
            return [];
        }

        return array_map(
            function($item) {
                return (int)$item['id_product_attribute'];
            },
            $result
        );
    }

    /**
     * @param int $combinationId
     * @param Product $object
     * @param Language $language
     *
     * @return null|array
     */
    public function findCombinationById($combinationId, Product $object, Language $language)
    {
        if (is_array($combinations = $object->getAttributesGroups($language->id))) {
            foreach ($combinations as $combination) {
                if ((int)$combination['id_product_attribute'] === $combinationId) {
                    return $combination;
                }
            }
        }

        return null;
    }

    /**
     * @param array $product
     * @param Product $object
     * @param Language $language
     *
     * @return int
     */
    protected function getMinimalQuantity(array $product, Product $object, Language $language)
    {
        $productAttributeId = $product['id_product_attribute'];
        if (!$productAttributeId) {
            return (int)$object->minimal_quantity;
        }

        $combination = $this->findCombinationById($productAttributeId, $object, $language);
        if ($combination['minimal_quantity']) {
            return (int)$combination['minimal_quantity'];
        }

        return 1;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    protected function getExtraContent(Product $product)
    {
        return $this->extraContentFinder
            ->addParams(['product' => $product])
            ->present();
    }

    /**
     * @return PsProductPresenter
     */
    protected function getProductPresenter()
    {
        return $this->productPresenterFactory->getPresenter();
    }

    /**
     * @return ProductPresentationSettings
     */
    protected function getProductPresentationSettings()
    {
        return $this->productPresenterFactory->getPresentationSettings();
    }

    /**
     * @param Product $object
     * @param Language $language
     * @param int $productAttributeId
     *
     * @return ProductLazyArray
     */
    public function presentVariant(Product $object, Language $language, $productAttributeId = null)
    {
        if ($productAttributeId !== null) {
            $productAttributeId = (int)$productAttributeId;
        }

        $product = $this->objectPresenter->present($object);
        $product['id_product'] = (int)$object->id;
        $product['out_of_stock'] = (int)$object->out_of_stock;
        $product['new'] = (int)$object->new;
        $product['id_product_attribute'] = $productAttributeId;
        $product['minimal_quantity'] = $this->getMinimalQuantity($product, $object, $language);
        $product['extraContent'] = $this->getExtraContent($object);

        $product = Product::getProductProperties(
            $language->id,
            $product,
            $this->context
        );

        return $this->getProductPresenter()->present(
            $this->getProductPresentationSettings(),
            $product,
            $language
        );
    }

    /**
     * @param int $productId
     * @param int $languageId
     *
     * @return null|ProductLazyArray[]
     */
    public function present($productId, $languageId)
    {
        $product = new Product((int)$productId, true, (int)$languageId);
        if (!Validate::isLoadedObject($product)) {
            return null;
        }

        $language = new Language((int)$languageId);
        if (!Validate::isLoadedObject($language)) {
            return null;
        }

        $productAttributesIds = $this->getProductAttributesIds($product->id);
        if (!$productAttributesIds) {
            return [$this->presentVariant($product, $language)];
        }

        return array_map(
            function($productAttributeId) use ($product, $language) {
                return $this->presentVariant($product, $language, $productAttributeId);
            },
            $productAttributesIds
        );
    }
}
