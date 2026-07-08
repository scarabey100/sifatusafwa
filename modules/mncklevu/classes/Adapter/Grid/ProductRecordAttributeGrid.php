<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Grid;

use Db;
use DbQuery;
use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecordAttribute;
use Tools;

class ProductRecordAttributeGrid extends AbstractGrid
{
    /**
     * @var string
     */
    const STATUS_FILTERABLE = 'filterable';

    /**
     * @return string
     */
    protected function getTitle()
    {
        return $this->module->l('Product Record Attributes', 'ProductRecordAttributeGrid');
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return ProductRecordAttribute::$definition['table'];
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return ProductRecordAttribute::$definition['primary'];
    }

    /**
     * @return array
     */
    protected function getActions()
    {
        return ['edit', 'delete'];
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return ProductRecordAttribute::getProductRecordAttributes();
    }

    /**
     * @param int $attributeGroupId
     *
     * @return string
     */
    protected function getAttributeGroupName($attributeGroupId)
    {
        $query = (new DbQuery())
            ->select('agl.public_name')
            ->from('attribute_group_lang', 'agl')
            ->where('agl.id_attribute_group = ' . (int)$attributeGroupId)
            ->where('agl.id_lang = ' . (int)$this->context->language->id);

        return (string)Db::getInstance()->getValue($query->build());
    }

    /**
     * @param int $featureId
     *
     * @return string
     */
    protected function getFeatureName($featureId)
    {
        $query = (new DbQuery())
            ->select('fl.name')
            ->from('feature_lang', 'fl')
            ->where('fl.id_feature = ' . (int)$featureId)
            ->where('fl.id_lang = ' . (int)$this->context->language->id);

        return (string)Db::getInstance()->getValue($query->build());
    }

    /**
     * @return string
     */
    public function displaySourceInfo($value, $row)
    {
        switch ((int)$row['source_type']) {
            case ProductRecordAttribute::SOURCE_TYPE_ATTRIBUTE_GROUP:
                return $this->getAttributeGroupName((int)$value) .
                    ' (' . $this->module->l('Attribute Group', 'ProductRecordAttributeGrid') . ')';

            case ProductRecordAttribute::SOURCE_TYPE_FEATURE:
                return $this->getFeatureName((int)$value) .
                    ' (' . $this->module->l('Feature', 'ProductRecordAttributeGrid') . ')';

            case ProductRecordAttribute::SOURCE_TYPE_MANUFACTURER:
                return $this->module->l('Manufacturer', 'ProductRecordAttributeGrid');
        }

        return $this->module->l('Unknown', 'ProductRecordAttributeGrid');
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        return [
            'source_id' => [
                'title' => $this->module->l('Source', 'ProductRecordAttributeGrid'),
                'orderby' => false,
                'search' => false,
                'callback' => 'displaySourceInfo',
                'callback_object' => $this,
            ],
            ProductRecordAttributeGrid::STATUS_FILTERABLE => [
                'title' => $this->module->l('Filterable', 'ProductRecordAttributeGrid'),
                'orderby' => false,
                'search' => false,
                'active' => ProductRecordAttributeGrid::STATUS_FILTERABLE,
                'align' => 'center',
            ],
        ];
    }

    /**
     * @param string $status
     *
     * @return string
     */
    public function getChangeStatusAction($status)
    {
        return $status . $this->getTable();
    }

    /**
     * @return ProductRecordAttribute
     */
    public function getObject()
    {
        return new ProductRecordAttribute((int)Tools::getValue($this->getIdentifier()));
    }

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param int $confirmation
     */
    protected function redirectWithConfirmation($confirmation)
    {
        Tools::redirectAdmin($this->generateIndex([
            'conf' => $confirmation,
            'token' => $this->getToken(),
        ]));
    }

    /**
     * @param string $status
     */
    public function changeStatus($status)
    {
        $object = $this->getObject();
        if (!isset($object->{$status})) {
            $this->errors[] = $this->module->l('Invalid status.', 'ProductRecordAttributeGrid');
        } else {
            $object->{$status} = (int)(!$object->{$status});

            if (!$object->save()) {
                $this->errors[] = $this->module->l('Failed to change status.', 'ProductRecordAttributeGrid');
            }
        }

        if (!count($this->errors)) {
            $this->redirectWithConfirmation(4);
        }
    }

    public function deleteItem()
    {
        if (!$this->getObject()->delete()) {
            $this->errors[] = $this->module->l('Failed to delete item.', 'ProductRecordAttributeGrid');
        } else {
            $this->redirectWithConfirmation(1);
        }
    }

    /**
     * @return string
     */
    public function displayErrors()
    {
        return implode('', array_map(
            function($error) {
                return $this->module->displayError($error);
            },
            $this->errors
        ));
    }
}
