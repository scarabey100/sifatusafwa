<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
} 
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_ . 'ps_customtext/classes/CustomText.php';
class Ps_CustomtextOverride extends Ps_Customtext
{
        // Equivalent module on PrestaShop 1.6, sharing the same data
    const MODULE_16 = 'blockcmsinfo';

    /**
     * @var string Template used by widget
     */
    private $templateFile;

    public function __construct()
    {
        $this->name = 'ps_customtext';
        $this->tab = 'front_office_features';
        $this->author = 'PrestaShop';
        $this->version = '4.2.1';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        Shop::addTableAssociation('info', ['type' => 'shop']);

        $this->displayName = $this->trans('Custom text block', [], 'Modules.Customtext.Admin');
        $this->description = $this->trans('Give your visitors extra information, display a customized block of content on your homepage.', [], 'Modules.Customtext.Admin');

        $this->ps_versions_compliancy = ['min' => '1.7.5.0', 'max' => _PS_VERSION_];

        $this->templateFile = 'module:ps_customtext/ps_customtext.tpl';
    }
    public function enable($force_all = false)
    {
        if (!$this->registerHook('displayFooterCustomText')) {
            return false;
        }
        $result = true;

        $result &= parent::enable($force_all);

        return (bool) $result;
    }
        /**
     * @param string|null $hookName
     * @param array $configuration
     *
     * @return array<string, mixed>|false
     */
    public function getCustomWidgetVariables()
    {
        $idShop = (int) $this->context->shop->id;
        $idInfo = CustomText::getCustomTextIdByShop($idShop);
        if ($idInfo === false) {
            return false;
        }

        $customText = new CustomText($idInfo, $this->context->language->id, $idShop);
        $objectPresenter = new ObjectPresenter();
        $data = $objectPresenter->present($customText);
        $data['id_lang'] = $this->context->language->id;
        $data['id_shop'] = $idShop;
        unset($data['id']);

        return ['cms_infos' => $data];
    }
    public function HookDisplayFooterCustomText(){ 
         
        $widgetVariables = $this->getCustomWidgetVariables();

        if ($widgetVariables === false) {
            return false;
        }

        $this->smarty->assign($widgetVariables);
     

        return $this->fetch($this->templateFile);
    }
}
