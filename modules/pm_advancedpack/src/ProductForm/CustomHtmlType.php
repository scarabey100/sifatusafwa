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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\BaseType;
class CustomHtmlType extends BaseType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['custom_html_from_smarty'] = $options['data']['content'];
    }
}
