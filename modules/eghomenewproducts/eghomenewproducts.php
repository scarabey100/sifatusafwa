<?php
/**
 * 2024 (c) Egio digital
 *
 * MODULE EgBrands
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */ 

if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class EgHomeNewProducts extends Module implements WidgetInterface
{
    private $templateFile;
    public function __construct()
    {  
        $this->name = 'eghomenewproducts';
        $this->tab = 'front_office_features'; // Change according to your needs
        $this->version = '1.0.0';
        $this->author = 'Egio digital';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('HP new products');
        $this->description = $this->l('Module for home page new products.');
        $this->templateFile = 'module:eghomenewproducts/views/templates/hook/eghomenewproducts.tpl';
    }
    public function install()
    {
        $this->installTabs();
        return parent::install() && $this->registerHook('displayBackofficeHeader');
    }
    public function uninstall()
    {
        $this->uninstallTabs(); 
        return parent::uninstall();
    }
     
      /**
     * Create Tab 
     */
  
     public function installTabs()
     {
         $installTabCompleted = true;
 
         foreach ($this->getTabs() as $tab) {
             try {
                 $installTabCompleted = $installTabCompleted && $this->installTab(
                     $tab['className'],
                     $tab['parent'],
                     $tab['name'],
                     $tab['module'],
                     $tab['active'],
                     $tab['icon']
                 );
             } catch (Exception $e) {  
                 return false;
             }
         }
 
         return $installTabCompleted;
     }
     public function uninstallTabs()
     {
         $uninstallTabCompleted = true;
 
         foreach ($this->getTabs() as $tab) {
             try {
                 $uninstallTabCompleted = $installTabCompleted && $this->uninstallTab(
                     $tab['className']
                 );
             } catch (Exception $e) {  
                 return false;
             }
         }
 
         return $uninstallTabCompleted;
     }
     public function installTab($className, $parent, $name, $module, $active, $icon)
     {
         if (Tab::getIdFromClassName($className)) {
             return true;
         }
 
         $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);
 
         $moduleTab = new Tab();
         $moduleTab->class_name = $className;
         $moduleTab->id_parent = $idParent;
         $moduleTab->module = $module;
         $moduleTab->active = $active;
         if (property_exists($moduleTab, 'icon')) {
             $moduleTab->icon = $icon;
         }
 
         $languages = Language::getLanguages(true);
         foreach ($languages as $language) {
             $moduleTab->name[$language['id_lang']] = $name;
         }
 
         return $moduleTab->add();
     }
     /**
      * Remove Tabs module in Dashboard
      * @param $class_name string name Tab
      * @return bool
      * @throws
      * @throws
      */
     public function uninstallTab($class_name)
     {
         if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
             $tab = new Tab($tab_id);
             return $tab->delete();
         } else {
             return false;
         } 
     }
     public function getTabs()
     {
         return [
             [
                 'className' => 'AdminEgHomeNewProducts',
                 'parent' => 'CONFIGURE',
                 'name' => $this->l('HP new products'),
                 'module' => $this->name,
                 'active' => true,
                 'icon' => 'extension',
             ],
         ];
         
     }
    private function getProductIds($hookName, array $configuration)
    {
        // Check if the hook is related to the cart
        if ('displayHome' === $hookName) {
            // Initialize product IDs array - always use default selected products
            $targetCount = (int)Configuration::get('EGHOMENEWPRODUCTS_NB_PRODUCTS');
            $defaultProductIds = explode(',', Configuration::get('EGHOMENEWPRODUCTS_PRODUCT_IDS'));
    
            // Always use all default selected products
            $defaultProductIds = array_filter(array_map('intval', $defaultProductIds)); // Clean and filter empty values
            $productIds_new = $defaultProductIds;
            
           
            // Collect category IDs from cart products to retrieve related products
            $categoryIds = explode(',', Configuration::get('EGHOMENEWPRODUCTS_CATEGORY')); // Fetch category IDs from configuration
            
            // Retrieve additional products from the same categories as cart products
            $relatedProductIds = [];
            
            // Calculate how many more products we need
            $targetCount = $targetCount - count($productIds_new);
            
           
            

            // Retrieve additional products
            $relatedProductIds = [];
            
            // Check if author is selected - if yes, get products by author instead of category
            $selectedAuthor = (int)Configuration::get('EGHOMENEWPRODUCTS_AUTHOR');
             
            if ($selectedAuthor > 0 && $targetCount > 0) {
                // Get products by author
              
                $relatedProductIds = $this->getProductsByAuthor($selectedAuthor, $productIds_new, $targetCount);
            } elseif ($targetCount > 0) {
                
                // If no author selected, use category
                $categoryIds = explode(',', Configuration::get('EGHOMENEWPRODUCTS_CATEGORY'));
                $categoryIds = array_filter(array_map('intval', $categoryIds)); // Clean and filter empty values

                if (!empty($categoryIds)) {
                    $relatedProductIds = $this->getProductsByCategory($categoryIds, $productIds_new, $targetCount);
                  
                }
            }
           
            // Combine default products with related products, ensuring uniqueness
            $productIds_new = array_unique(array_merge($productIds_new, $relatedProductIds));
            
            $productIds_new = array_map(function ($id) {
                return (int)$id;
            }, $productIds_new);
            
            return $productIds_new;
        }
    
        
    }
    private function getProductsByCategory(array $id_categories, array $productIds_new, int $requiredCount)
    {
        if (empty($id_categories)) {
            return [];
        }

        // Sanitize and prepare category IDs for the SQL query
        $categoryIds = implode(',', array_map('intval', $id_categories));
        $excludedProductIds = !empty($productIds_new) ? implode(',', array_map('intval', $productIds_new)) : '';

        // Check if random selection is enabled
        $random = (bool) Configuration::get('EGHOMENEWPRODUCTS_RANDOM'); // Retrieve random configuration

        // Build the SQL query
        $shopId = (int)Context::getContext()->shop->id;

        $sql = "
            SELECT sa.id_product, sa.id_product_attribute, sa.quantity
            FROM " . _DB_PREFIX_ . "product_attribute pa
            INNER JOIN " . _DB_PREFIX_ . "stock_available sa
                ON sa.id_product_attribute = pa.id_product_attribute 
                AND sa.id_product = pa.id_product
                AND sa.id_shop = $shopId
            INNER JOIN " . _DB_PREFIX_ . "product p 
                ON pa.id_product = p.id_product
            INNER JOIN " . _DB_PREFIX_ . "product_shop pss 
                ON p.id_product = pss.id_product AND pss.id_shop = $shopId
            INNER JOIN " . _DB_PREFIX_ . "category_product cp 
                ON p.id_product = cp.id_product
            WHERE pa.default_on = 1
              AND p.active = 1
              AND pss.active = 1
              AND cp.id_category IN ($categoryIds)";
        
        // Only add NOT IN clause if there are excluded product IDs
        if (!empty($excludedProductIds)) {
            $sql .= " AND p.id_product NOT IN ($excludedProductIds)";
        }
        
        $sql .= " AND sa.quantity > 0";
        
        if ($random) {
            $sql .= " ORDER BY RAND(), sa.quantity DESC";
        } else {
            $sql .= " ORDER BY sa.quantity DESC";
        }
        
        $sql .= " LIMIT " . (int)$requiredCount;

        // Execute the query and fetch results
        $results = Db::getInstance()->executeS($sql);
       
        // Extract only the product IDs as integers
        return array_map('intval', array_column($results, 'id_product'));
    }
    
    private function getProductsByAuthor(int $authorId, array $productIds_new, int $requiredCount)
    {
        if (empty($authorId) || $authorId <= 0) {
            return [];
        }

        // Sanitize and prepare excluded product IDs for the SQL query
        $excludedProductIds = !empty($productIds_new) ? implode(',', array_map('intval', $productIds_new)) : '';

        // Check if random selection is enabled
        $random = (bool) Configuration::get('EGHOMENEWPRODUCTS_RANDOM');

        // Build the SQL query
        $shopId = (int)Context::getContext()->shop->id;

        // Query handles both products with and without attributes using UNION
        // First part: Products WITH attributes (using default attribute)
        $sql = "
            SELECT p.id_product, 
                   pa.id_product_attribute,
                   sa.quantity
            FROM " . _DB_PREFIX_ . "product p
            INNER JOIN " . _DB_PREFIX_ . "product_shop pss 
                ON p.id_product = pss.id_product AND pss.id_shop = $shopId
            INNER JOIN " . _DB_PREFIX_ . "product_attribute pa 
                ON p.id_product = pa.id_product AND pa.default_on = 1
            INNER JOIN " . _DB_PREFIX_ . "stock_available sa
                ON sa.id_product = p.id_product 
                AND sa.id_product_attribute = pa.id_product_attribute
                AND sa.id_shop = $shopId
            WHERE p.active = 1
              AND pss.active = 1
              AND p.id_manufacturer = " . (int)$authorId;
        
        // Only add NOT IN clause if there are excluded product IDs
        if (!empty($excludedProductIds)) {
            $sql .= " AND p.id_product NOT IN ($excludedProductIds)";
        }
        
        $sql .= " AND sa.quantity > 0
            
            UNION
            
            
            SELECT p.id_product, 
                   0 as id_product_attribute,
                   sa.quantity
            FROM " . _DB_PREFIX_ . "product p
            INNER JOIN " . _DB_PREFIX_ . "product_shop pss 
                ON p.id_product = pss.id_product AND pss.id_shop = $shopId
            INNER JOIN " . _DB_PREFIX_ . "stock_available sa
                ON sa.id_product = p.id_product 
                AND sa.id_product_attribute = 0
                AND sa.id_shop = $shopId
            LEFT JOIN " . _DB_PREFIX_ . "product_attribute pa 
                ON p.id_product = pa.id_product
            WHERE p.active = 1
              AND pss.active = 1
              AND p.id_manufacturer = " . (int)$authorId
              . " AND pa.id_product IS NULL"; // Only products without attributes
        
        // Only add NOT IN clause if there are excluded product IDs
        if (!empty($excludedProductIds)) {
            $sql .= " AND p.id_product NOT IN ($excludedProductIds)";
        }
        
        $sql .= " AND sa.quantity > 0";
        
        if ($random) {
            $sql .= " ORDER BY RAND(), quantity DESC";
        } else {
            $sql .= " ORDER BY quantity DESC";
        }
        
        $sql .= " LIMIT " . (int)$requiredCount;
        // Execute the query and fetch results
        
        $results = Db::getInstance()->executeS($sql);

        // Extract only the product IDs as integers
        return array_map('intval', array_column($results, 'id_product'));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $productIds_new = $this->getProductIds($hookName, $configuration);
       
        if (!empty($productIds_new)) {
            $products = $this->ToCartProducts($productIds_new);
            
            if (!empty($products)) {
                return [
                    'products' => $products,
                ];
            }
        }

        return false;
    }

    public function renderWidget($hookName, array $configuration)
    {
        $productIds_new = $this->getProductIds($hookName, $configuration);

        if (empty($productIds_new)) {
            return;
        }

        if (!$this->isCached($this->templateFile)) {
            $variables = $this->getWidgetVariables($hookName, $configuration); 
            if (empty($variables)) {
                return false;
            }
            $this->smarty->assign($variables);
            $default_lang = (int) Context::getContext()->language->id;
            $title = Configuration::get('EGHOMENEWPRODUCTS_TITLE_'.$default_lang);
            $linkUrl = Configuration::get('EGHOMENEWPRODUCTS_LINK_URL');
            $selectedCategory = Configuration::get('EGHOMENEWPRODUCTS_CATEGORY');
            $categoryLink = !empty($selectedCategory) ? Context::getContext()->link->getCategoryLink($selectedCategory, null, $default_lang) : '';

            $this->smarty->assign([
                'title' => $title,
                'link_url' => $linkUrl,
                'categoryLink' => $categoryLink
            ]);
        }

        return $this->fetch($this->templateFile);
    }
    protected function ToCartProducts(array $productIds_new = [])
    {
        if (!empty($productIds_new)) {
            $showPrice = (bool) Configuration::get('CROSSSELLING_DISPLAY_PRICE');

            $assembler = new ProductAssembler($this->context);

            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
                $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
            } else {
                $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
            }

            $productsForTemplate = [];

            $presentationSettings->showPrices = $showPrice;
           
            if (is_array($productIds_new)) {
                foreach ($productIds_new as $productId) {
                    if ($productId > 0) {
                        $productsForTemplate[] = $presenter->present(
                            $presentationSettings,
                            $assembler->assembleProduct(['id_product' => $productId]),
                            $this->context->language
                        );
                    }
                }
            }

            return $productsForTemplate;
        }

        return false;
    }
    public function hookDisplayBackofficeHeader($params) 
    {   
        $this->context->controller->addJqueryPlugin('alerts');
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->addJqueryPlugin('alerts');
        $this->context->controller->addJqueryUI('ui.sortable');
         
        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
         
        // Pass selected products to back.js
        $selectedProducts = $this->getSelectedProducts();
        Media::addJsDef([
            'selectedProducts' => $selectedProducts
        ]); 
    }
    private function getSelectedProducts()
    {
        // Retrieve selected product IDs from configuration or any other logic
        $productIds_new = Configuration::get('EGHOMENEWPRODUCTS_PRODUCT_IDS');
        return explode(',', $productIds_new); // Return as an array
    }

    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('submit_eghomenewproducts')) {
            // Handle form submission
            $title = [];
            foreach (Language::getLanguages(false) as $lang) {
                $title[$lang['id_lang']] = Tools::getValue('title_' . $lang['id_lang']);
            }
            foreach ($title as $id_lang => $value) { 
                Configuration::updateValue('EGHOMENEWPRODUCTS_TITLE_'.$id_lang, $value, true); // Save multilingual title
            }
            $productIds_new = Tools::getValue('productIds_new');
            Configuration::updateValue('EGHOMENEWPRODUCTS_PRODUCT_IDS', $productIds_new); // Store as a comma-separated string
            Configuration::updateValue('EGHOMENEWPRODUCTS_NB_PRODUCTS', Tools::getValue('nb_produit'));
            Configuration::updateValue('EGHOMENEWPRODUCTS_ACTIVE', Tools::getValue('active'));
            Configuration::updateValue('EGHOMENEWPRODUCTS_RANDOM', Tools::getValue('random')); // Update random configuration
            
            // Fill EGHOMENEWPRODUCTS_CATEGORY from productIds_choose
            $selectedCategory = Tools::getValue('id_category');
            Configuration::updateValue('EGHOMENEWPRODUCTS_CATEGORY', $selectedCategory);
            
            // Save link URL
            $linkUrl = Tools::getValue('link_url');
            Configuration::updateValue('EGHOMENEWPRODUCTS_LINK_URL', $linkUrl);
            
            // Save author/manufacturer
            $selectedAuthor = Tools::getValue('id_manufacturer');
            Configuration::updateValue('EGHOMENEWPRODUCTS_AUTHOR', $selectedAuthor);
        }

        $html .= $this->renderForm();
        return $html;
    }
    public function getHtmlProduct()
    {
       return $html_product = '<div id="selected_products" class="row">
            <div class="col-md-12">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <div class="row">
                                <div class="col-md-12 button_info">
                                    <button type="button" class="btn btn-outline-primary sensitive add" id="add_product_new" data-id="" data-title="">
                                        <i class="material-icons">add_circle</i>
                                    </button>
                                </div>
                            </div>
                            <div class="row card">
                                <div class="col-lg-12 col-md-12 card-block">
                                    <table class="table item" id="eg-list-item">
                                        <thead>
                                            <tr class="nodrag nodrop">
                                                <th class=""></th>
                                                <th class="">
                                                    <span class="title_box active">ID</span>
                                                </th>
                                                <th class="">
                                                    <span class="title_box active">Produit</span>
                                                </th>
                                                <th class="">
                                                    <span class="title_box"></span>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody class="selected_products_row_position ui-sortable" id="selected_products_body">
                                            <!-- Dynamic content will be injected here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    public function renderForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        // Fetch existing configuration
        $productIds_new = Configuration::get('EGHOMENEWPRODUCTS_PRODUCT_IDS'); // Single product ID
        $nb_produit = Configuration::get('EGHOMENEWPRODUCTS_NB_PRODUCTS') ?: 8; 
        $selectedCategory = Configuration::get('EGHOMENEWPRODUCTS_CATEGORY'); // Fetch selected category
        $linkUrl = Configuration::get('EGHOMENEWPRODUCTS_LINK_URL'); // Fetch link URL
        $selectedAuthor = Configuration::get('EGHOMENEWPRODUCTS_AUTHOR'); // Fetch selected author
        $active = Configuration::get('EGHOMENEWPRODUCTS_ACTIVE');
        $random = Configuration::get('EGHOMENEWPRODUCTS_RANDOM'); // Retrieve random configuration
       // dump($nb_produit); die;
        // Load products for the select dropdown with reference and name using CONCAT
        // Order by ID DESC (most recent first)
        $sql = 'SELECT p.id_product, 
                       CONCAT(p.reference, " ", pl.name) AS product_name 
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product 
                LEFT JOIN ' . _DB_PREFIX_ . 'product_shop pss ON pss.id_product = pl.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . ' 
                AND p.active = 1 AND pss.active = 1
        
            ORDER BY p.id_product DESC';
    
        $products = Db::getInstance()->executeS($sql);
    
        // Prepare formatted products
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id_product' => $product['id_product'],
                'name' => $product['product_name'] // Use product_name from SQL
            ];
        }
    
        $fields_form = [
            'form' => [
                'tinymce' => true,
                'legend' => [
                    'title' => $this->l('Configuration'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Titre'),
                        'name' => 'title',
                        'lang' => true, // Enable multilingual input
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Lien URL'),
                        'name' => 'link_url',
                        'desc' => $this->l('Optionnel: Entrez un lien URL à la place d\'une catégorie'),
                        'lang' => false,
                    ],
                    [
                        'type' => 'categories',
                        'label' => $this->l('Catégorie'),
                        'name' => 'id_category',
                        'tree' => [
                            'id' => 'categories-tree',
                            'use_search' => true,
                            'use_checkbox' => false,
                            'selected_categories' => [$selectedCategory], // Pre-select category
                        ],
                        'required' => false,
                        'desc' => $this->l('Optionnel: Utilisé si aucun lien URL n\'est fourni')
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'label' => $this->l('Auteur'),
                        'name' => 'id_manufacturer',
                        'options' => [
                            'query' => $this->getManufacturers(),
                            'id' => 'id_manufacturer',
                            'name' => 'name'
                        ],
                        'desc' => $this->l('Sélectionner un auteur/manufacturer')
                    ],
                    [
                        'type' => 'select',
                        'class' => 'chosen',
                        'label' => $this->l('Sélectionner un produit'),
                        'name' => 'productIds_choose', // Change name
                        'id' => 'productIds_choose',
                        'multiple' => false, // Single select
                        'tab' => 'blocProduct', 
                        'options' => [
                            'query' => $formattedProducts, // Use formatted products
                            'id' => 'id_product',
                            'name' => 'name'
                        ],
                    ],
                    [
                        'type' => 'hidden', // Hidden field for storing product ID
                        'label' => $this->l('ID produit sélectionné'),
                        'name' => 'productIds_new', // Keep the original name for submission
                        'value' => $productIds_new // Set value from configuration
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->l('Enregistrer'),
                        'name' => 'products',
                        'required' => false, 
                        'html_content' => $this->getHtmlProduct()
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->l('Nombre de produits'),
                        'name' => 'nb_produit',
                        'required' => false, 
                        'html_content' => '<input type="number" name="nb_produit" id="nb_produit" value="'.$nb_produit.'">'
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Random', array(), 'Modules.EgHomeNewProducts.Admin'),
                        'name' => 'random',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Activé', array(), 'Modules.EgHomeNewProducts.Admin')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Désactivé', array(), 'Modules.EgHomeNewProducts.Admin')
                            ]
                        ]
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Affichage', array(), 'Modules.EgHomeNewProducts.Admin'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Activé', array(), 'Modules.EgHomeNewProducts.Admin')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Désactivé', array(), 'Modules.EgHomeNewProducts.Admin')
                            ]
                        ]
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Enregistrer'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];
         
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->name_controller = 'eghomenewproducts';
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit_eghomenewproducts';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = [
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0),
            ];
        }
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        // Set multilingual values for the title field
        foreach (Language::getLanguages(false) as $lang) {
            $title = Configuration::get('EGHOMENEWPRODUCTS_TITLE_'.$lang['id_lang']);
            $helper->fields_value['title'][$lang['id_lang']] = $title ?? '';
        }
        
        $helper->fields_value['productIds_new'] = $productIds_new; // Set chosen product ID
        $helper->fields_value['random'] = $random; // Set random value for the form
        $helper->fields_value['active'] = $active;
        $helper->fields_value['link_url'] = $linkUrl; // Set link URL value
        $helper->fields_value['id_manufacturer'] = $selectedAuthor; // Set author value

    
        return $helper->generateForm([$fields_form]);
    }
    

    public function hookDisplayShoppingCart($params)
    {
        // This is where you can add the logic to display cross-sell products in the cart.
    }
    
    /**
     * Get manufacturers list for select dropdown
     * @return array
     */
    private function getManufacturers()
    {
        $manufacturers = Manufacturer::getManufacturers(false, (int)Context::getContext()->language->id);
        $manufacturersList = [
            [
                'id_manufacturer' => 0,
                'name' => $this->l('-- Aucun --')
            ]
        ];
        
        foreach ($manufacturers as $manufacturer) {
            $manufacturersList[] = [
                'id_manufacturer' => $manufacturer['id_manufacturer'],
                'name' => $manufacturer['name']
            ];
        }
        
        return $manufacturersList;
    }
    
    /**
     * Filter products by author/manufacturer
     * @param array $productIds
     * @param int $authorId
     * @return array
     */
    private function filterProductsByAuthor(array $productIds, int $authorId)
    {
        if (empty($productIds) || $authorId <= 0) {
            return $productIds;
        }
        
        $productIdsStr = implode(',', array_map('intval', $productIds));
        $shopId = (int)Context::getContext()->shop->id;
        
        $sql = "SELECT p.id_product
                FROM " . _DB_PREFIX_ . "product p
                INNER JOIN " . _DB_PREFIX_ . "product_shop pss 
                    ON p.id_product = pss.id_product AND pss.id_shop = $shopId
                WHERE p.id_product IN ($productIdsStr)
                  AND p.id_manufacturer = " . (int)$authorId . "
                  AND p.active = 1
                  AND pss.active = 1";
        
        $results = Db::getInstance()->executeS($sql);
        
        return array_map('intval', array_column($results, 'id_product'));
    }
}
