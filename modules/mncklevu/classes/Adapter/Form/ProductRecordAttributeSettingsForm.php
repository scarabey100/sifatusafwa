<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Form;

use AttributeGroup;
use Feature;
use MncKlevu\PrestaShop\Adapter\Grid\ProductRecordAttributeGrid;
use MncKlevu\PrestaShop\Adapter\ObjectModel\ProductRecordAttribute;
use Module;
use Tools;
use Validate;

class ProductRecordAttributeSettingsForm extends AbstractForm
{
    /**
     * @param Module $module
     * @param ProductRecordAttributeGrid $grid
     */
    public function __construct(Module $module, ProductRecordAttributeGrid $grid)
    {
        parent::__construct($module, $grid);
    }

    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_product_record_attribute_settings';
    }

    /**
     * @return ProductRecordAttribute
     */
    public function getProductRecordAttributeObject()
    {
        return new ProductRecordAttribute($this->getGridItemId());
    }

    /**
     * @param int $type
     * @param int $id
     *
     * @return string
     */
    protected function getSourceOptionValue($type, $id)
    {
        return (int)$type . ':' . (int)$id;
    }

    /**
     * @return array
     */
    protected function getFieldsValue()
    {
        $object = $this->getProductRecordAttributeObject();

        return [
            'source' => Tools::getValue('source', $this->getSourceOptionValue(
                $object->source_type,
                $object->source_id
            )),
            'filterable' => Tools::getValue('filterable', $object->filterable),
        ];
    }

    /**
     * @return array
     */
    protected function getAttributeGroupOptions()
    {
        $attributeGroups = AttributeGroup::getAttributesGroups($this->context->language->id);
        if (!is_array($attributeGroups)) {
            return [];
        }

        return [
            'name' => $this->module->l('Attribute groups', 'ProductRecordAttributeSettingsForm'),
            'query' => array_map(
                function($attributeGroup) {
                    return [
                        'id' => $this->getSourceOptionValue(
                            ProductRecordAttribute::SOURCE_TYPE_ATTRIBUTE_GROUP,
                            $attributeGroup['id_attribute_group']
                        ),
                        'name' => $attributeGroup['public_name'] . ' (' . $attributeGroup['id_attribute_group'] . ')',
                    ];
                },
                $attributeGroups
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getFeatureOptions()
    {
        $features = Feature::getFeatures($this->context->language->id);
        if (!is_array($features)) {
            return [];
        }

        return [
            'name' => $this->module->l('Features', 'ProductRecordAttributeSettingsForm'),
            'query' => array_map(
                function($feature) {
                    return [
                        'id' => $this->getSourceOptionValue(
                            ProductRecordAttribute::SOURCE_TYPE_FEATURE,
                            $feature['id_feature']
                        ),
                        'name' => $feature['name'] . ' (' . $feature['id_feature'] . ')',
                    ];
                },
                $features
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOtherOptions()
    {
        return [
            'name' => $this->module->l('Other', 'ProductRecordAttributeSettingsForm'),
            'query' => [
                [
                    'id' => $this->getSourceOptionValue(ProductRecordAttribute::SOURCE_TYPE_MANUFACTURER, 0),
                    'name' => $this->module->l('Manufacturer', 'ProductRecordAttributeSettingsForm'),
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getSourceOptions()
    {
        return [
            $this->getAttributeGroupOptions(),
            $this->getFeatureOptions(),
            $this->getOtherOptions()
        ];
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return [
            'legend' => [
                'title' => $this->module->l('Product record attribute settings', 'ProductRecordAttributeSettingsForm'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->module->l('Source', 'ProductRecordAttributeSettingsForm'),
                    'name' => 'source',
                    'options' => [
                        'optiongroup' => [
                            'query' => $this->getSourceOptions(),
                            'label' => 'name',
                        ],
                        'options' => [
                            'query' => 'query',
                            'id' => 'id',
                            'name' => 'name',
                        ],
                        'default' => [
                            'value' => $this->getSourceOptionValue(0, 0),
                            'label' => $this->module->l('-- Please choose --', 'ProductRecordAttributeSettingsForm'),
                        ],
                    ],
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Filterable', 'ProductRecordAttributeSettingsForm'),
                    'name' => 'filterable',
                    'is_bool' => true,
                    'values' => [
                        ['value' => 1],
                        ['value' => 0],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'ProductRecordAttributeSettingsForm'),
            ],
            'buttons' => [
                [
                    'href' => $this->getCurrentIndex(['token' => $this->getToken()]),
                    'title' => $this->module->l('Cancel', 'ProductRecordAttributeSettingsForm'),
                    'icon' => 'process-icon-cancel',
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    protected function decodeValueOfSource()
    {
        $data = explode(':', (string)Tools::getValue('source'));
        if (count($data) != 2) {
            return [
                'source_type' => 0,
                'source_id' => 0
            ];
        }

        return [
            'source_type' => (int)$data[0],
            'source_id' => (int)$data[1]
        ];
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        $data = $this->decodeValueOfSource();
        if (!in_array($data['source_type'], [
            ProductRecordAttribute::SOURCE_TYPE_ATTRIBUTE_GROUP,
            ProductRecordAttribute::SOURCE_TYPE_FEATURE,
            ProductRecordAttribute::SOURCE_TYPE_MANUFACTURER
        ]) || (
            ($data['source_type'] == ProductRecordAttribute::SOURCE_TYPE_ATTRIBUTE_GROUP) &&
            !Validate::isLoadedObject(new AttributeGroup($data['source_id']))
        ) || (
            ($data['source_type'] == ProductRecordAttribute::SOURCE_TYPE_FEATURE) &&
            !Validate::isLoadedObject(new Feature($data['source_id']))
        )) {
            $this->errors[] = $this->module->l('Invalid source.', 'ProductRecordAttributeSettingsForm');
        }

        return !$this->hasErrors();
    }

    /**
     * @return bool
     */
    protected function saveFormData()
    {
        $data = $this->decodeValueOfSource();

        $this->getProductRecordAttributeObject()
            ->setShopId($this->context->shop->id)
            ->setSourceType($data['source_type'])
            ->setSourceId($data['source_id'])
            ->setFilterable(Tools::getValue('filterable'))
            ->save();

        return true;
    }
}
