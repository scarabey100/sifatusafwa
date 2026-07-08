<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

namespace PrestaModule\AdvancedPack\ProductForm;
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShopBundle\Form\FormBuilderModifier;
use Symfony\Component\Form\FormBuilderInterface;
class ProductFormModifier
{
    private $formBuilderModifier;
    private $moduleInstance;
    public function __construct()
    {
        if (version_compare(_PS_VERSION_, '8.1.0', '<')) {
            return;
        }
        $this->moduleInstance = \Module::getInstanceByName('pm_advancedpack');
        $this->formBuilderModifier = $this->moduleInstance->getContainer()->get('form.form_builder_modifier');
    }
    public function modify(
        int $productId,
        FormBuilderInterface $productFormBuilder
    ) {
        if (version_compare(_PS_VERSION_, '8.1.0', '<')) {
            return;
        }
        $context = \Context::getContext();
        $product = new \Product((int)$productId, true, $context->language->id, $context->shop->id);
        if (\AdvancedPack::isValidPack($productId) || \Tools::getValue('is_real_new_pack')) {
            $this->formBuilderModifier->addBefore(
                $productFormBuilder,
                'description',
                'pm_advancedpack_custom_html',
                CustomTabType::class,
                [
                    'label' => $this->moduleInstance->l('Pack configuration'),
                    'data' => [
                        'id_product' => $productId,
                        'content' => $this->moduleInstance->getAdminNewPackOutput($product),
                    ],
                ]
            );
        } else {
            if (\Validate::isLoadedObject($product) && !\Pack::isPack($product->id)) {
                $this->formBuilderModifier->addAfter(
                    $productFormBuilder,
                    'options',
                    'pm_advancedpack_custom_html',
                    CustomTabType::class,
                    [
                        'label' => $this->moduleInstance->l('Related packs'),
                        'data' => [
                            'id_product' => $productId,
                            'content' => $this->moduleInstance->getRelatedPacksForm($product),
                        ],
                    ]
                );
                $this->formBuilderModifier->addAfter(
                    $productFormBuilder->get('description'),
                    'related_products',
                    'pm_advancedpack_bundles',
                    CustomHtmlType::class,
                    [
                        'data' => [
                            'content' => $this->moduleInstance->hookDisplayAdminProductsMainStepLeftColumnBottom([
                                'id_product' => $productId,
                            ]),
                        ],
                        'form_theme' => '@Modules/pm_advancedpack/views/templates/admin/custom_html_block.html.twig',
                    ]
                );
            }
        }
    }
}
