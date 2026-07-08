<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Form;

use Configuration;
use Context;
use HelperForm;
use MncKlevu\PrestaShop\Adapter\Grid\GridInterface;
use Module;
use Tools;

abstract class AbstractForm
{
    /**
     * @var Module
     */
    protected $module;

    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param Module $module
     * @param GridInterface $grid
     */
    public function __construct(Module $module, GridInterface $grid = null)
    {
        $this->module = $module;
        $this->grid = $grid;
        $this->context = Context::getContext();
    }

    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_' . $this->module->name . '_settings';
    }

    /**
     * @param array $extraParams
     *
     * @return string
     */
    protected function getCurrentIndex(array $extraParams = [])
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
    abstract protected function getFieldsValue();

    /**
     * @return Controller|AdminController
     */
    protected function getController()
    {
        return $this->context->controller;
    }

    /**
     * @return bool
     */
    protected function initializeConfiguration()
    {
        return false;
    }

    /**
     * @return array
     */
    abstract protected function getSettings();

    /**
     * @return string
     */
    public function displayForm()
    {
        $extraParams = [];

        if ($this->grid !== null) {
            if (Tools::isSubmit($action = $this->grid->getAddAction())) {
                $extraParams[$action] = 1;
            } elseif (
                Tools::isSubmit($identifier = $this->grid->getIdentifier()) &&
                Tools::isSubmit($action = $this->grid->getEditAction())
            ) {
                $extraParams[$identifier] = Tools::getValue($identifier);
                $extraParams[$action] = 1;
            }
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = $this->getSubmitAction();
        $helper->currentIndex = $this->getCurrentIndex($extraParams);
        $helper->token = $this->getToken();

        $helper->tpl_vars = [
            'fields_value' => $this->getFieldsValue(),
            'languages' => $this->getController()->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        if ($this->initializeConfiguration()) {
            $extraParams['token'] = $this->getToken();
            Tools::redirectAdmin($this->getCurrentIndex($extraParams));
        }

        return $helper->generateForm(['form' => ['form' => $this->getSettings()]]);
    }

    /**
     * @return bool
     */
    abstract protected function validate();

    /**
     * @return bool
     */
    abstract protected function saveFormData();

    protected function redirectWithConfirmation()
    {
        Tools::redirectAdmin($this->getCurrentIndex([
            'conf' => 4,
            'token' => $this->getToken(),
        ]));
    }

    public function submit()
    {
        if ($this->validate() && $this->saveFormData()) {
            $this->redirectWithConfirmation();
        }
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return (bool)count($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
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

    /**
     * @return int
     */
    protected function getGridItemId()
    {
        return $this->grid !== null ? (int)Tools::getValue($this->grid->getIdentifier()) : 0;
    }
}
