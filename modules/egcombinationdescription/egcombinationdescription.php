<?php
if (!defined('_PS_VERSION_')) {
    exit;
} 
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\ConstraintValidator\Constraints\DefaultLanguage;
use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EgCombinationDescription extends Module
{
    protected $templateFile;
    public function __construct()
    {
        $this->name = 'egcombinationdescription';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Egio Digital';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Combination Description');
        $this->description = $this->l('Adds a description field to product combinations.');
        $this->templateFile = 'module:egcombinationdescription/views/templates/hook/front.tpl';
    }

    public function install()
    { 
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayEditionBloc') &&
            $this->registerHook('actionCombinationFormFormBuilderModifier') &&
            $this->registerHook('actionAfterUpdateCombinationFormFormHandler') &&
            $this->registerHook('actionProductFormBuilderModifier') &&
            $this->registerHook('actionAfterUpdateProductFormHandler') &&
            $this->registerHook('actionCartUpdateQuantityBefore');
            
    }

    public function uninstall()
    {
        return parent::uninstall();
    } 
    public function hookActionCartUpdateQuantityBefore(array $params)
    {
        // Get the cart object
            $cart = $params['cart'];

            // Get the product ID
            $id_product = (int)Tools::getValue('id_product');
            if ($id_product > 0) {
                    // Get the new quantity
            $quantity = (int)Tools::getValue('quantity');

            // Get the product attribute ID (if applicable)
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');

            // Get the operator (e.g., '+', '-')
            $operator = Tools::getValue('op');

            $stock = StockAvailable::getQuantityAvailableByProduct($id_product,$id_product_attribute);
    
            // You can access the cart object ($cart), the product ID ($id_product),
            // the new quantity ($quantity), and other relevant data through the $params array. 
            // Example: Check if the quantity is within a specific range
        
            foreach ($cart->getProducts() as $product) { 
                if ($product['id_product'] == $id_product && $product['id_product_attribute'] == $id_product_attribute) {
                    if ($operator === 'up') {
                        $quantity = $product['cart_quantity'] + (int) $params['quantity'];
                    } elseif ($operator === 'down') {
                        $quantity = $product['cart_quantity'] - (int) $params['quantity'];
                    }
                }
            }
            
            if ($quantity > $stock) {
                // Update cart quantity to available stock
                $cart->updateQty($quantity, $id_product, $id_product_attribute);
                die;
                // If the quantity is invalid, you can prevent the update
                // by setting the $operator to false or throwing an exception
                
            }
            // You can also modify other properties of the product or cart here
            // For example, you could adjust the product price based on the new quantity     
            }

    }
        /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js'); 
    }
            /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayEditionBloc()
    {
        $this->context->smarty->assign(array(
            'title' => !empty(Configuration::get('EG_EDITION_BLOC_TITLE_'.Context::getContext()->language->id, true)) 
            ? Configuration::get('EG_EDITION_BLOC_TITLE_'.Context::getContext()->language->id, true) 
            : '',
            'desc' => !empty(Configuration::get('EG_EDITION_BLOC_DESC_'.Context::getContext()->language->id, true)) 
            ? Configuration::get('EG_EDITION_BLOC_DESC_'.Context::getContext()->language->id, true) 
            : '',
        ));
        return $this->fetch($this->templateFile, $this->getCacheId($this->name));
    }
    public function hookActionProductFormBuilderModifier(array $params)
    {
        
        $formBuilder = $params['form_builder'];
        $product = $params['data'];
        
        // Get the current value if the product exists
        $nameAr = isset($product['id']) ? Db::getInstance()->getValue(
            'SELECT name_ar FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . (int)$product['id']
        ) : '';
         
        $formBuilder->add('name_ar', TextType::class, [
            'label' => $this->trans('Arabic Name', [], 'Modules.EgCombinationDescription.Admin'),
            'required' => false,
            'data' => $nameAr,
        ]);
    }

    public function hookActionAfterUpdateProductFormHandler(array $params)
    {
        
        $idProduct = (int)$params['id'];
        $formData = $params['form_data'];

        if (isset($formData['name_ar'])) {
            Db::getInstance()->execute(
                'UPDATE ' . _DB_PREFIX_ . 'product SET name_ar = "' . pSQL($formData['name_ar']) . '" WHERE id_product = ' . $idProduct
            );
        }
    }

    public function hookActionCombinationFormFormBuilderModifier(array $params)
    {
        $formBuilder = $params['form_builder'];
        $combinationId = $params['id'];
        $languages = Language::getLanguages(false);
        $descriptions = [];

        // Fetch descriptions by language
        foreach ($languages as $lang) {
            $descriptions[$lang['id_lang']] = Db::getInstance()->getValue(
                'SELECT description FROM ' . _DB_PREFIX_ . 'product_attribute_lang 
                WHERE id_product_attribute = ' . (int)$combinationId . ' 
                AND id_lang = ' . (int)$lang['id_lang']
            ) ?: ''; // Default to empty if no value exists
        }
    
        // Add translatable description field to the form
        $formBuilder->add('description', TranslatableType::class, [
            'label' => $this->l('Description'),
            'required' => false, 
            'data' => $descriptions, // Set the retrieved translations
        ]);
    }
    
    public function hookActionAfterUpdateCombinationFormFormHandler(array $params)
    {
        $formData = $params['form_data'];
        $combinationId = $params['id'];
    
        // Check if descriptions are set in formData, otherwise use an empty array
        $descriptions = isset($formData['description']) && is_array($formData['description'])
            ? $formData['description']
            : [];  // Default to empty array if not set
    
        // Make sure descriptions is an array (to prevent foreach errors)
        if (!is_array($descriptions)) {
            return;
        }
    
        // Insert or update descriptions for each language
        foreach ($descriptions as $id_lang => $desc) {
            // Check if the combination already has a description for the language
            $existingDesc = Db::getInstance()->getValue(
                'SELECT description FROM ' . _DB_PREFIX_ . 'product_attribute_lang 
                 WHERE id_product_attribute = ' . (int)$combinationId . ' 
                 AND id_lang = ' . (int)$id_lang
            );
    
            if ($existingDesc === false) {
                // If no existing description, insert new description
                Db::getInstance()->execute(
                    'INSERT INTO ' . _DB_PREFIX_ . 'product_attribute_lang 
                     (id_product_attribute, id_lang, description) 
                     VALUES (' . (int)$combinationId . ', ' . (int)$id_lang . ', "' . pSQL($desc) . '")'
                );
            } else {
                // If description exists, update it
                Db::getInstance()->execute(
                    'UPDATE ' . _DB_PREFIX_ . 'product_attribute_lang 
                     SET description = "' . pSQL($desc) . '" 
                     WHERE id_product_attribute = ' . (int)$combinationId . ' 
                     AND id_lang = ' . (int)$id_lang
                );
            }
        }
    } 
    /**
     * @return mixed
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitEditionConf')) {
            $languages = Language::getLanguages(false);
            $values = [];
            foreach ($languages as $lang) {
                $langId = $lang['id_lang']; 
                Configuration::updateValue('EG_EDITION_BLOC_TITLE_' . $langId, Tools::getValue('EG_EDITION_BLOC_TITLE_' . $langId));
                Configuration::updateValue('EG_EDITION_BLOC_DESC_' . $langId, Tools::getValue('EG_EDITION_BLOC_DESC_' . $langId),true);
            }
        }
        $this->_html .= $this->renderForm();
        return $this->_html;
    }
    public function renderForm()
    {

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Title', [], $this->domain),
                        'name' => 'EG_EDITION_BLOC_TITLE',
                        'lang' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Description', [], $this->domain),
                        'name' => 'EG_EDITION_BLOC_DESC',
                        'autoload_rte' => true, 
                        'rows' => 5,
                        'cols' => 40,
                        'lang' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEditionConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }
 

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);
        $fields = [];
        foreach ($languages as $lang) { 
             $fields['EG_EDITION_BLOC_TITLE'][$lang['id_lang']] = Tools::getValue('EG_EDITION_BLOC_TITLE_' . $lang['id_lang'], Configuration::get('EG_EDITION_BLOC_TITLE_' .$lang['id_lang']));  
             $fields['EG_EDITION_BLOC_DESC'][$lang['id_lang']] =  Tools::getValue('EG_EDITION_BLOC_DESC_' . $lang['id_lang'], Configuration::get('EG_EDITION_BLOC_DESC_' .$lang['id_lang']));  
        }
        return $fields;
    } 

    
}
