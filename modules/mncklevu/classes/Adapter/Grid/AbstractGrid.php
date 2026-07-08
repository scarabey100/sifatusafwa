<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Grid;

use Context;
use HelperList;
use Module;
use Tools;

abstract class AbstractGrid implements GridInterface
{
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    /**
     * @return string
     */
    abstract protected function getTitle();

    /**
     * @param array $extraParams
     *
     * @return string
     */
    protected function generateIndex(array $extraParams = [])
    {
        $params = [
            'configure' => $this->module->name
        ];

        if ($extraParams) {
            $params = array_merge($params, $extraParams);
        }

        return $this->context->link->getAdminLink('AdminModules', false, [], $params);
    }

    /**
     * @return string
     */
    protected function getToken()
    {
        return Tools::getAdminTokenLite('AdminModules');
    }

    /**
     * @return array
     */
    protected function getActions()
    {
        return ['edit'];
    }

    /**
     * @return bool
     */
    protected function getSimpleHeader()
    {
        return false;
    }

    /**
     * @return array
     */
    abstract protected function getData();

    /**
     * @return string
     */
    public function getAddAction()
    {
        return 'add' . $this->getTable();
    }

    /**
     * @return array
     */
    abstract protected function getFields();

    /**
     * @return string
     */
    public function displayGrid()
    {
        $helper = new HelperList();
        $helper->title = $this->getTitle();
        $helper->table = $this->getTable();
        $helper->identifier = $this->getIdentifier();
        $helper->currentIndex = $this->generateIndex();
        $helper->token = $this->getToken();
        $helper->actions = $this->getActions();
        $helper->module = $this->module;
        $helper->shopLinkType = '';
        $helper->simple_header = $this->getSimpleHeader();
        $helper->listTotal = count($data = $this->getData());

        $helper->toolbar_btn['new'] = [
            'href' => $this->generateIndex([
                $this->getAddAction() => 1,
                'token' => $this->getToken(),
            ]),
            'desc' => $this->module->l('Add', 'AbstractGrid')
        ];

        return $helper->generateList($data, $this->getFields());
    }

    /**
     * @return string
     */
    public function getEditAction()
    {
        return 'update' . $this->getTable();
    }

    /**
     * @return string
     */
    public function getDeleteAction()
    {
        return 'delete' . $this->getTable();
    }
}
