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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
class CustomTabType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('custom_html',
            CustomHtmlType::class,
            [
                'data' => $options['data'],
                'form_theme' => '@Modules/pm_advancedpack/views/templates/admin/custom_html_block.html.twig',
            ]
        );
    }
}
