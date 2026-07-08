<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Product;

use Category;
use Product;

class ProductCategoriesRetriever
{
    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * @param int $productId
     *
     * @return array
     */
    protected function getAllProductCategoriesIds($productId)
    {
        $ids = Product::getProductCategories($productId);
        if (is_array($ids)) {
            return array_map(function($id) { return (int)$id; }, $ids);
        }

        return [];
    }

    /**
     * @param int $categoryId
     * @param int $languageId
     *
     * @return array
     */
    protected function getCategoryChildren($categoryId, $languageId)
    {
        return is_array($result = Category::getChildren($categoryId, $languageId)) ? $result : [];
    }

    /**
     * @param int $categoryId
     * @param int $languageId
     * @param array $allCategoriesIds
     *
     * @return bool
     */
    protected function hasCategoryChild($categoryId, $languageId, array &$allCategoriesIds)
    {
        foreach ($this->getCategoryChildren($categoryId, $languageId) as $child) {
            if (in_array($child['id_category'], $allCategoriesIds) ||
                $this->hasCategoryChild($child['id_category'], $languageId, $allCategoriesIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $categoryId
     * @param int $languageId
     *
     * @return array
     */
    public function getHierarchy($categoryId, $languageId)
    {
        $result = [];
        $parents = (new Category((int)$categoryId))->getParentsCategories((int)$languageId);

        for ($i = count($parents) - 1; $i >= 0; $i--) {
            $parent = $parents[$i];
            $parentId = (int)$parent['id_category'];

            if ($parentId > 2) {
                $result[$parentId] = $parent['name'];
            }
        }

        return $result;
    }

    /**
     * @param int $productId
     * @param int $languageId
     *
     * @return array
     */
    public function getMostSpecificCategories($productId, $languageId)
    {
        $cacheKey = $productId . '_' . $languageId;
        if (isset(static::$cache[$cacheKey])) {
            return static::$cache[$cacheKey];
        }

        $result = [];
        $allCategoriesIds = $this->getAllProductCategoriesIds($productId);
        foreach ($allCategoriesIds as $categoryId) {
            if (!$this->hasCategoryChild($categoryId, $languageId, $allCategoriesIds)) {
                $result[$categoryId] = $this->getHierarchy($categoryId, $languageId);
            }
        }

        return (static::$cache[$cacheKey] = $result);
    }
}
