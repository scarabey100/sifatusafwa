<?php
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'featuresforcombinations/classes/FFC.php';

class AdminImportFeaturesForCombinationsController extends AdminImportController
{
    private $languages;
    /**
     * @var string
     */
    protected $tabClassName;
    /**
     * @var false|Module
     */
    protected $module;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->tabClassName = 'AdminImportFeaturesForCombinations';
        $this->context = Context::getContext();
        $this->submit_action = 'submitConfigImport';

        parent::__construct();

        $this->controller_type = 'moduleadmin';

        $this->module = Module::getInstanceByName('featuresforcombinations');
        if (!$this->module->id) {
            throw new PrestaShopException('Module featuresforcombinations not found');
        }

        $_POST['entity'] = 0;
        $_POST['iso_lang'] = 'fr';
        $_POST['truncate'] = '0';
        $_POST['forceIDs'] = '0';
        $_POST['regenerate'] = '0';
        $_POST['sendemail'] = '0';
        $_POST['match_ref'] = '0';

        $this->entities = [
            'Import features' => 0,
        ];

        $this->required_fields = [
            'id_product',
            'id_product_attribute',
            'id_feature',
        ];

        $this->available_fields = [
            // $this->l('Ignore this column');
            'no' => ['label' => $this->trans('Ignore this column', [], 'Admin.Advparameters.Feature')],
            // $this->l('Product ID')
            'id_product' => ['label' => $this->trans('Product ID', [], 'Modules.Featuresforcombinations.Global')],
            // $this->l('Combination ID')
            'id_product_attribute' => ['label' => $this->trans('Combination ID', [], 'Modules.Featuresforcombinations.Global')],
            // $this->l('Feature ID')
            'id_feature' => ['label' => $this->trans('Feature ID', [], 'Modules.Featuresforcombinations.Global')],
            // $this->l('Feature value ID')
            'id_value' => ['label' => $this->trans('Feature value ID', [], 'Modules.Featuresforcombinations.Global')],
            // $this->l('Custom value')
            'custom_value' => ['label' => $this->trans('Custom value', [], 'Modules.Featuresforcombinations.Global')],
            // $this->l('Delete (0/1)')
            'delete' => ['label' => $this->trans('Delete (0/1)', [], 'Modules.Featuresforcombinations.Global')],
        ];

        self::$default_values = [
            'delete' => 0,
        ];
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['bulk_add'] = [
            'href' => $this->context->link->getAdminLink('AdminFeaturesForCombinations'),
            // $this->l('Import by File')
            'desc' => $this->trans('Back', [], 'Admin.Actions'),
        ];
        parent::initPageHeaderToolbar();
    }

    public function initContent()
    {
        if ($this->display == 'import') {
            if (Tools::getValue('csv')) {
                $this->tpl_folder = 'import/';
                $this->content .= $this->renderView();
            } else {
                // $this->l('To proceed, please upload a file first.');
                $this->errors[] = $this->trans('To proceed, please upload a file first.', [], 'Admin.Advparameters.Notification');
                $this->content .= $this->renderForm();
            }
        } elseif (!$this->ajax) {
            $this->content .= $this->renderForm();
        }

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    public function getTemplatePath()
    {
        return _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/';
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                // $this->l('Import configuration');
                'title' => $this->trans('Import configuration', [], 'Modules.Featuresforcombinations.Global'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'file',
                    // $this->l('File (.csv, .xlsx, .odt)');
                    'label' => $this->trans('File (.csv, .xlsx, .odt)', [], 'Modules.Featuresforcombinations.Global'),
                    'name' => 'file',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    // $this->l('Separator (only for .csv)');
                    'label' => $this->trans('Separator (only for .csv)', [], 'Modules.Featuresforcombinations.Global'),
                    'name' => 'separator',
                    'class' => 'fixed-width-xxl',
                    'required' => true,
                ],
            ],
            'submit' => [
                // $this->l('Visualize');
                'title' => $this->trans('Visualize', [], 'Modules.Featuresforcombinations.Global'),
                'action' => 'test',
            ],
        ];

        return $this->parentRenderForm();
    }

    private function parentRenderForm()
    {
        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        if (Tools::getValue('submitFormAjax')) {
            $this->content .= $this->context->smarty->fetch('form_submit_ajax.tpl');
        }

        if ($this->fields_form && is_array($this->fields_form)) {
            if (!$this->multiple_fieldsets) {
                $this->fields_form = [['form' => $this->fields_form]];
            }

            // For add a fields via an override of $fields_form, use $fields_form_override
            if (is_array($this->fields_form_override) && !empty($this->fields_form_override)) {
                $this->fields_form[0]['form']['input'] = array_merge($this->fields_form[0]['form']['input'], $this->fields_form_override);
            }

            $fields_value = $this->getFieldsValue($this->object);

            Hook::exec('action' . $this->controller_name . 'FormModifier', [
                'object' => &$this->object,
                'fields' => &$this->fields_form,
                'fields_value' => &$fields_value,
                'form_vars' => &$this->tpl_form_vars,
            ]);

            $helper = new HelperForm();
            $this->setHelperDisplay($helper);
            $helper->fields_value = $fields_value;
            $helper->submit_action = $this->submit_action;
            $helper->tpl_vars = $this->getTemplateFormVars();
            $helper->show_cancel_button = (isset($this->show_form_cancel_button)) ? $this->show_form_cancel_button : ($this->display == 'add' || $this->display == 'edit');

            $back = rawurldecode(Tools::getValue('back', ''));
            if (empty($back)) {
                $back = self::$currentIndex . '&token=' . $this->token;
            }
            if (!Validate::isCleanHtml($back)) {
                exit(Tools::displayError('Provided "back" parameter is invalid.'));
            }

            $helper->back_url = $back;
            null !== $this->base_tpl_form ? $helper->base_tpl = $this->base_tpl_form : '';
            if ($this->access('view')) {
                if (Tools::getValue('back')) {
                    $helper->tpl_vars['back'] = Tools::safeOutput(Tools::getValue('back'));
                } else {
                    $helper->tpl_vars['back'] = Tools::safeOutput(self::$currentIndex . '&token=' . $this->token);
                }
            }
            $form = $helper->generateForm($this->fields_form);

            return $form;
        }

        return '';
    }

    public function renderView()
    {
        $this->context->smarty->assign([
        ]);
        $result = parent::renderView();
        $this->addJS(_MODULE_DIR_ . 'featuresforcombinations/views/js/import-ffc.js');
        $this->removeJS(_PS_JS_DIR_ . 'admin/import.js');

        Media::addJsDef([
            'importControllerUrl' => $this->context->link->getAdminLink('AdminImportFeaturesForCombinations'),
        ]);

        return $result;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitConfigImport')) {
            // import file
            $filename = $this->uploadCsv();
            if (!$filename) {
                return false;
            } else {
                Configuration::updateValue('FFC_separator', Tools::getValue('separator'));
                $_POST['csv'] = $filename;
                $this->display = 'import';
            }
        }
        return parent::postProcess();
    }

    public function processUpload()
    {
        return true;
    }

    public function processNoRedirect()
    {
        return true;
    }

    public function uploadCsv()
    {
        $filename_prefix = date('YmdHis') . '-';

        if (isset($_FILES['file']) && !empty($_FILES['file']['error'])) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    // $this->l('The uploaded file exceeds the upload_max_filesize directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess.');
                    $this->errors[] = $this->trans('The uploaded file exceeds the upload_max_filesize directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess.', [], 'Admin.Advparameters.Notification');

                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    // $this->l('The uploaded file exceeds the post_max_size directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess, for example:');
                    $this->errors[] = $this->trans('The uploaded file exceeds the post_max_size directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess, for example:', [], 'Admin.Advparameters.Notification')
                        . '<br/><a href="' . $this->context->link->getAdminLink('AdminMeta') . '" >
					<code>php_value post_max_size 20M</code> ' .
                        $this->trans('(click to open "Generators" page)', [], 'Admin.Advparameters.Notification') . '</a>';

                    break;
                case UPLOAD_ERR_PARTIAL:
                    // $this->l('The uploaded file was only partially uploaded.');
                    $this->errors[] = $this->trans('The uploaded file was only partially uploaded.', [], 'Admin.Advparameters.Notification');

                    break;
                case UPLOAD_ERR_NO_FILE:
                    // $this->l('No file was uploaded.');
                    $this->errors[] = $this->trans('No file was uploaded.', [], 'Admin.Advparameters.Notification');

                    break;
            }
        } elseif (!preg_match('#([^\.]*?)\.(csv|xls[xt]?|o[dt]s)$#is', $_FILES['file']['name'])) {
            // $this->l('The extension of your file should be ".csv".');
            $this->errors[] = $this->trans('The extension of your file should be ".csv".', [], 'Admin.Advparameters.Notification');
        } elseif (!@filemtime($_FILES['file']['tmp_name'])
            || !@move_uploaded_file($_FILES['file']['tmp_name'], AdminImportController::getPath() . $filename_prefix . str_replace("\0", '', $_FILES['file']['name']))) {
            // $this->l('An error occurred while uploading / copying the file.');
            $this->errors[] = $this->trans('An error occurred while uploading / copying the file.', [], 'Admin.Advparameters.Notification');
        } else {
            @chmod(AdminImportController::getPath() . $filename_prefix . $_FILES['file']['name'], 0664);
            return $filename_prefix . str_replace('\0', '', $_FILES['file']['name']);
        }
    }

    public function getFieldValue($obj, $key, $id_lang = null)
    {
        if ($value = parent::getFieldValue($obj, $key, $id_lang)) {
            return $value;
        }

        return Configuration::get('FFC_' . $key);
    }

    public function importByGroups($offset = false, $limit = false, &$results = null, $validateOnly = false, $moreStep = 0)
    {
        // Check if the CSV file exist
        if (Tools::getValue('csv')) {
            $shop_is_feature_active = Shop::isFeatureActive();
            // If i am a superadmin, i can truncate table (ONLY IF OFFSET == 0 or false and NOT FOR VALIDATION MODE!)
            if (!$offset && !$moreStep && !$validateOnly && (($shop_is_feature_active && $this->context->employee->isSuperAdmin()) || !$shop_is_feature_active) && Tools::getValue('truncate')) {
                $this->truncateTables((int) Tools::getValue('entity'));
            }
            $import_type = false;
            $doneCount = 0;
            /** @var array<string> $moreStepLabels */
            $moreStepLabels = [];
            // Sometime, import will use registers to memorize data across all elements to import (for trees, or else).
            // Since import is splitted in multiple ajax calls, we must keep these data across all steps of the full import.
            $crossStepsVariables = [];
            if ($crossStepsVars = Tools::getValue('crossStepsVars')) {
                $crossStepsVars = json_decode($crossStepsVars, true);
                if (count($crossStepsVars) > 0) {
                    $crossStepsVariables = $crossStepsVars;
                }
            }
            Db::getInstance()->disableCache();
            $clearCache = false;
            $doneCount += $this->ffcImport($offset, $limit, $validateOnly);

            if ($results !== null) {
                $results['isFinished'] = ($doneCount < $limit);
                if ($results['isFinished'] && $clearCache && !$validateOnly) {
                    $this->clearSmartyCache();
                }
                $results['doneCount'] = $offset + $doneCount;
                if ($offset === 0) {
                    // compute total count only once, because it takes time
                    $handle = $this->openCsvFile(0);
                    if ($handle) {
                        $count = 0;
                        while (fgetcsv($handle, MAX_LINE_SIZE, $this->separator)) {
                            ++$count;
                        }
                        $results['totalCount'] = $count;
                    }
                    $this->closeCsvFile($handle);
                }
                if (!$results['isFinished'] || (!$validateOnly && ($moreStep < count($moreStepLabels)))) {
                    // Since we'll have to POST this array from ajax for the next call, we should care about it size.
                    $nextPostSize = mb_strlen(json_encode($crossStepsVariables));
                    $results['crossStepsVariables'] = $crossStepsVariables;
                    $results['nextPostSize'] = $nextPostSize + (1024 * 64); // 64KB more for the rest of the POST query.
                    $results['postSizeLimit'] = Tools::getMaxUploadSize();
                }
                if ($results['isFinished'] && !$validateOnly && ($moreStep < count($moreStepLabels))) {
                    $results['oneMoreStep'] = $moreStep + 1;
                    $results['moreStepLabel'] = $moreStepLabels[$moreStep];
                }
            }

            if ($import_type !== false) {
                // $this->l('%s import');
                $log_message = sprintf($this->trans('%s import', [], 'Admin.Advparameters.Notification'), $import_type);
                if ($offset !== false && $limit !== false) {
                    // $this->l('(from %s to %s)');
                    $log_message .= ' ' . sprintf($this->trans('(from %s to %s)', [], 'Admin.Advparameters.Notification'), $offset, $limit);
                }
                if (Tools::getValue('truncate')) {
                    // $this->l('with truncate');
                    $log_message .= ' ' . $this->trans('with truncate', [], 'Admin.Advparameters.Notification');
                }
                PrestaShopLogger::addLog($log_message, 1, null, $import_type, null, true, (int) $this->context->employee->id);
            }

            Db::getInstance()->enableCache();
        } else {
            // $this->l('To proceed, please upload a file first.');
            $this->errors[] = $this->trans('To proceed, please upload a file first.', [], 'Admin.Advparameters.Notification');
        }
    }

    public function ffcImport($offset = false, $limit = false, $validateOnly = false)
    {
        $this->receiveTab();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Language::getIdByIso(Tools::getValue('iso_lang'));
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }
        AdminImportController::setLocale();

        $this->languages = Language::getLanguages(false, $id_lang);

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator)) && (!$limit || $current_line < $limit); ++$current_line) {
            ++$line_count;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $info = AdminImportController::getMaskedRow($line);
            if (count($line) == 1 && $line[0] == null) {
                // $this->l('There is an empty row in the file that won\'t be imported.');
                $this->warnings[] = $this->trans('There is an empty row in the file that won\'t be imported.', [], 'Admin.Advparameters.Notification');

                continue;
            } elseif (!array_key_exists('id_value', $info) && !array_key_exists('custom_value', $info)) {
                // $this->l('Please provide a value ID or a custom value for the line number %d.')
                $this->warnings[] = sprintf($this->trans(
                    'Please provide a value ID or a custom value for the line number %d.',
                    [],
                    'Modules.Featuresforcombinations.Global'
                ), $current_line + 1);

                continue;
            }

            if (!$validateOnly) {
                $this->ffcImportOne(
                    $info
                );
            }
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    protected function ffcImportOne($info)
    {
        try {
            $ffc = new FFC($info['id_product']);
        } catch (Exception $e) {
            // $this->l('Product with ID %s does not exist.')
            $this->errors[] = sprintf($this->trans(
                'Product with ID %s does not exist.',
                [],
                'Modules.Featuresforcombinations.Global'
            ), $info['id_product']);
            return;
        }
        AdminImportController::setDefaultValues($info);

        if ($info['delete'] == '1') {
            $ffc->deleteFFC($info['id_product_attribute'], $info['id_feature'], $info['id_value']);
        } else {
            if (!empty($info['id_value'])) {
                $ffc->addCombinationsFeaturesToDB(
                    $info['id_product_attribute'],
                    $info['id_feature'],
                    $info['id_value']
                );
            } else {
                $idValue = $ffc->addCombinationsFeaturesToDB(
                    $info['id_product_attribute'],
                    $info['id_feature'],
                    0,
                    true
                );
                $customValue = $info['custom_value'];
                foreach ($this->languages as $language) {
                    $ffc->addFeaturesCustomToDB($idValue, (int) $language['id_lang'], $customValue);
                }
            }
        }
    }

    public function setHelperDisplay(Helper $helper)
    {
        if ($helper instanceof HelperView) {
            $helper->module = $this->module;
        }
        parent::setHelperDisplay($helper);
    }

    public function initModal()
    {
        parent::initModal();
        $modal_content = $this->context->smarty->fetch($this->module->getLocalPath() .
            'views/templates/admin/modal_import_progress.tpl');
        $this->modals = array_map(function ($modal) use ($modal_content) {
            return $modal['modal_id'] !== 'importProgress' ? $modal : [
                'modal_id' => 'importProgress',
                'modal_class' => 'modal-md',
                // $this->l('Importing your data...');
                'modal_title' => $this->trans('Importing your data...', [], 'Modules.Featuresforcombinations.Global'),
                'modal_content' => $modal_content,
            ];
        }, $this->modals);
    }

    protected function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
            return parent::trans($id, $parameters, $domain, $locale);
        } else {
            return $this->l($id);
        }
    }
}
