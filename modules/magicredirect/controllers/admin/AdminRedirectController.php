<?php
/**
 * NOTICE OF LICENSE.
 *
 * @file Redirections controller for Magic Redirect package?
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2023 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 */

require_once _PS_MODULE_DIR_.'magicredirect/magicredirect.php';
require_once _PS_MODULE_DIR_.'magicredirect/models/Redirect.php';

class AdminRedirectController extends ModuleAdminController
{
    protected $_defaultOrderBy = 'id_redirect';
    protected $_defaultOrderWay = 'DESC';
    protected $actions_available = array('view', 'edit', 'delete');

    public function __construct()
    {
        $this->table = 'redirect';
        $this->className = 'Redirect';

        $this->context = Context::getContext();
        $this->lang = false;
        $this->bootstrap = true;
        $this->allow_export = true;
        $this->requiredDatabase = true;

        $this->mod = new MagicRedirect();

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = array(
            'id_redirect' => array(
                'title' => '#',
                'align' => 'center',
                'type' => 'int',
            ),
            'old' => array(
                'title' => $this->mod->l('Old URL'),
                'align' => 'center',
            ),
            'new' => array(
                'title' => $this->mod->l('New URL'),
                'align' => 'center',
            ),
            'type' => array(
                'title' => $this->mod->l('Add Date'),
                'align' => 'center',
                'type' => 'datetime',
            ),
            'hit' => array(
                'title' => $this->mod->l('Hit counter'),
                'align' => 'center',
                'type' => 'int',
            ),
            'date_upd' => array(
                'title' => $this->mod->l('Upd Date'),
                'align' => 'center',
                'type' => 'datetime',
                'width' => 5,
            ),
            'regex' => array(
                'title' => $this->mod->l('Reg. Exp.'),
                'type' => 'bool',
                'active' => 'status',
                'align' => 'center',
                'width' => 5,
            ),
            'active' => array(
                'title' => $this->mod->l('Active'),
                'type' => 'bool',
                'active' => 'status',
                'align' => 'center',
                'width' => 5,
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->mod->l('Delete selected'), 'confirm' => $this->mod->l('Delete selected entries ?'),
            ),
        );

        // Reset counter
        Configuration::updateValue('MGRT_COUNTER', '0');

        $this->context = Context::getContext();

        if (!isset($this->errors)) {
            $this->errors = array();
        }

        parent::__construct();

    }

    public function renderForm()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        $obj = $this->loadObject(true);

        if (isset($obj->id)) {
            $this->display = 'edit';
        } else {
            $this->display = 'add';
        }

        $redirect_types = array(
            array(
                'id' => '301',
                'name' => $this->mod->l('Definitive redirect (301)'),
            ),
            array(
                'id' => '302',
                'name' => $this->mod->l('Temporary redirect (302)'),
            ),
            array(
                'id' => '303',
                'name' => $this->mod->l("'See other' redirect (303)"),
            ),

        );

        $array_submit = array(
            array(
                'type' => 'text',
                'label' => $this->mod->l('Old URL :'),
                'name' => 'old',
                'hint' => $this->mod->l('Enter old url to redirect (ex : "/old-url")'),
            ),
            array(
                'type' => 'text',
                'label' => $this->mod->l('New URL :'),
                'hint' => $this->mod->l('Enter new url here (ex : "/new-url.html")'),
                'name' => 'new',
            ),
            array(
                'type' => 'select',
                'label' => $this->mod->l('Type :'),
                'name' => 'type',
                'options' => array(
                    'query' => $redirect_types,
                    'id' => 'id',
                    'name' => 'name',
                ),
            ),
            array(
                'type' => 'radio',
                'label' => $this->mod->l('Regular expression :'),
                'hint' => $this->mod->l('This tool uses PHP preg_replace("/{regex}/i",{result}) function.').' '.$this->mod->l('Don\'t forget to escape your characters in "Old Url" when using this function.'),
                'name' => 'regex',
                'required' => true,
                'is_bool' => true,
                'default_value' => 0,
                'values' => array(
                    array(
                        'id' => 'regex_off',
                        'value' => 0,
                        'label' => $this->mod->l('No'),
                    ),
                    array(
                        'id' => 'regex_on',
                        'value' => 1,
                        'label' => $this->mod->l('Yes'),
                    ),
                ),
            ),

            array(
                'type' => 'radio',
                'label' => $this->mod->l('Status :'),
                'name' => 'active',
                'required' => true,
                'is_bool' => true,
                'default_value' => 1,
                'values' => array(
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->mod->l('Disabled'),
                    ),
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->mod->l('Enabled'),
                    ),
                ),
            ),
        );

        $this->fields_form[0]['form'] = array(
            'tinymce' => false,
            'legend' => array(
                'title' => $this->mod->l('Create and edit redirections'),
            ),
            'input' => $array_submit,
            'submit' => array(
                'title' => $this->mod->l('Save'),
                'class' => 'btn btn-default',
            ),
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form[1]['form'] = array(
                'legend' => array(
                    'title' => $this->mod->l('Shop association:'),
                ),
                'input' => array(
                    array(
                        'type' => 'shop',
                        'label' => $this->mod->l('Shop association:'),
                        'name' => 'checkBoxShopAsso',
                    ),

                ),
            );
        }

        $this->multiple_fieldsets = true;

        return parent::renderForm();
    }

    public function init()
    {
        parent::init();

        if (Shop::isFeatureActive() && version_compare(_PS_VERSION_, '1.5.4.1', '>')) {
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        }

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.$this->table.'_shop` sa ON (a.`id_'.$this->table.'` = sa.`id_'.$this->table.'` AND sa.id_shop = '.(int) $this->context->shop->id.') ';
        }

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND sa.`id_shop` = '.(int) Context::getContext()->shop->id;
        }

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_group = ' GROUP BY a.id_redirect ';
        }
    }

    public function processUpdate()
    {
        $object = parent::processUpdate();
        $this->updateAssoShop($object->id);

        return $object;
    }

    public function processAdd()
    {
        $object = parent::processAdd();
        $this->updateAssoShop($object->id);

        return $object;
    }

    public function postProcess()
    {
        $id_shops = array();

        if (Tools::isSubmit('mass_csv_form_submit')) {
            if (Shop::getContext() == Shop::CONTEXT_ALL) {
                $shops = Shop::getShops();

                if (!empty($shops)) {
                    foreach ($shops as $s) {
                        $id_shops[] = $s['id_shop'];
                    }
                }
            } elseif (Shop::getContext() == Shop::CONTEXT_GROUP) {
                $shopid = Shop::getContextShopGroupID(true);
                $shops = ShopGroup::getShopsFromGroup($shopid);

                if (!empty($shops)) {
                    foreach ($shops as $s) {
                        $id_shops[] = $s['id_shop'];
                    }
                }
            }

            if (empty($id_shops)) {
                $id_shops[] = Shop::getContextShopID();
            }

            $id_shops = array_unique($id_shops);

            if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
                //$mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');

                //if (!in_array($_FILES['csv_file']['type'], $mimes)) {
                //   $this->errors[] = Tools::displayError($this->mod->l('Error : Problem with file upload. Please retry or check your file.'));

                // return false;
                //}

                $csv_content = Tools::file_get_contents($_FILES['csv_file']['tmp_name']);

                if (empty($csv_content)) {
                    $this->errors[] = Tools::displayError($this->mod->l('Error : Your file seems to be empty.').' (Log : Empty file)');
                    parent::postProcess();

                    return false;
                }

                $sep = Configuration::get('MGRT_SEPARATOR') ? Configuration::get('MGRT_SEPARATOR') : ',';

                $array = $this->csvToArray($csv_content, $sep);

                if (empty($array)) {
                    if ($sep == ",") {
                        $array = $this->csvToArray($csv_content, ';');
                    } elseif ($sep == ";") {
                        $array = $this->csvToArray($csv_content, ',');
                    }

                    if (empty($array)) {
                        $this->errors[] = Tools::displayError($this->mod->l('Error : Problem with file upload. Please retry or check your file.').' (Log : Unable to detect content)');
                        parent::postProcess();

                        return false;
                    }
                }

                foreach ($array as $value) {
                    if (!empty($value[0])) {
                        $new = !empty($value[1]) ? trim($value[1]) : '/';
                        $type = !empty($value[2]) ? trim($value[2]) : '301';
                        $regex = !empty($value[3]) ? (int) $value[3] : 0;

                        $redirect = new Redirect();
                        $redirect->old = trim($value[0]);
                        $redirect->new = $new;
                        $redirect->type = $type;
                        $redirect->regex = (bool) $regex;
                        $redirect->active = true;
                        $redirect->add();
                    }
                }
                
                Tools::redirectAdmin(self::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminRedirect').'&conf=4');
            }
        }

        parent::postProcess();
    }

    public function renderList()
    {

        // Autoincrement bugfix, only here to catch problem from the start
        $db = Db::getInstance();
        $test = $db->executeS('SELECT * FROM '._DB_PREFIX_.'redirect WHERE id_redirect IN (0, NULL, false) ORDER BY id_redirect DESC');

        // If last entry is 0, this mean its still a problem
        if (!empty($test)) {

            $db->execute('
                ALTER TABLE `'._DB_PREFIX_.'redirect`
                    MODIFY `id_redirect` int(11) NOT NULL AUTO_INCREMENT;
                COMMIT;
            ');

            // we do not modify presnet informations because it could harm websites 
            // and we cant really meet precise informations over id_shop relations
            // however a loop with incremental id could do the job here in most cases

        }

        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_.'magicredirect/views/templates/admin/mass_csv_form.tpl');

        $tpl->assign(array(
            'separator' => Configuration::get('MGRT_SEPARATOR')
        ));

        $html = $tpl->fetch();

        return $html.parent::renderList();
    }

    public function csvToArray($csv, $delimiter)
    {
        if (empty($delimiter)) {
            $delimiter = ',';
        }

        $data = str_getcsv($csv, "\n"); //parse the rows
        $rows = array();
        foreach ($data as $value) {
            $rows[] = str_getcsv($value, $delimiter); //parse the items in rows
        }

        return $rows;
    }
}
