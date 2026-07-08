<?php 

require_once  _PS_MODULE_DIR_ .'/egstickers/classes/Sticker.php';
 
class AdminEgStickersController extends ModuleAdminController
{
    private static $sticker_position = [
        1 => [
            'id' => 1,
            'name' => 'Top',
        ],
        2 => [
            'id' => 2,
            'name' => 'Bottom',
        ],
    ];

    public function __construct()
    {
        $this->module = Module::getInstanceByName('egstickers'); // Initialize the module instance

        $this->bootstrap = true; //Gestion de l'affichage en mode bootstrap 
        $this->table = Sticker::$definition['table']; //Table de l'objet
        $this->identifier = Sticker::$definition['primary']; //Clé primaire de l'objet
        $this->className = Sticker::class; //Classe de l'objet
        $this->lang = true; //Flag pour dire si utilisation de langues ou non
        parent::__construct(); // must be called before using $this->context

        // Now safe to use $this->context
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'egstickers_lang` stl ON (
            stl.`id_sticker` = a.`id_sticker` AND stl.`id_lang` = '.(int)$this->context->language->id.'
        )';
        $this->_select = 'stl.name';

        $this->fields_list = [
            'id_sticker' => [
                'title' => $this->module->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->module->l('Name'),
                'filter_key' => 'stl!name',
                'orderby' => true,
            ],
            'color' => [
                'title' => $this->module->l('Color'),
                'type' => 'color',
            ],
            'rate' => [
                'title' => $this->module->l('Rate'),
                'type' => 'select',
                'list' => [1 => '1', 2 => '2', 3 => '3', 4 => '4'],
                'filter_key' => 'a!rate',
            ],
            'active' => array(
                'title' => $this->module->l('Affichage'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'orderby' => false
            ),
            'position' => array(
                'title' => $this->module->l('Position'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ),
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();
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
                                    <button type="button" class="btn btn-outline-primary sensitive add" id="add_product_sticker" data-id="" data-title="">
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
        if (!($obj = $this->loadObject(true))) {
            return;
        }
        $id_sticker = $obj->id_sticker;

        $sql = 'SELECT ps.id_product 
            FROM ' . _DB_PREFIX_ . 'product_sticker ps
            INNER JOIN ' . _DB_PREFIX_ . 'product p ON ps.id_product = p.id_product
            INNER JOIN ' . _DB_PREFIX_ . 'product_shop pshop ON ps.id_product = pshop.id_product
            WHERE ps.id_sticker = ' . (int)$id_sticker . '
            AND p.active = 1
            AND pshop.active = 1';
        $selectedProducts = Db::getInstance()->executeS($sql);
       
        $productIds = [];
        foreach ($selectedProducts as $product) {
            $productIds[] = $product['id_product'];
        }
        $productIds = implode(',', $productIds);
      
        $sql = 'SELECT p.id_product, 
                    CONCAT(p.reference, " ", pl.name) AS product_name 
                    FROM ' . _DB_PREFIX_ . 'product p
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product 
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_shop pss ON pss.id_product = pl.id_product
                    LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                    WHERE pl.id_lang = ' . (int)Context::getContext()->language->id . ' 
                    AND p.active = 1 AND pss.active = 1
                    AND sa.quantity > 0
                    ORDER BY p.id_product ASC';

        $products = Db::getInstance()->executeS($sql);

        // Prepare formatted products
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id_product' => $product['id_product'],
                'name' => $product['product_name'] // Use product_name from SQL
            ];
        }
        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Sticker'),
                'icon' => 'icon-tag',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Name'),
                    'name' => 'name',
                    'lang' => true,
                    'required' => true,
                ],
                [
                    'type' => 'color',
                    'label' => $this->module->l('Color'),
                    'name' => 'color',
                    'required' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Position'),
                    'name' => 'sticker_position',
                    'default_value' => 3,
                    'options' => array(
                        'query' => self::$sticker_position,
                        'id' => 'id',
                        'name' => 'name',
                        ),
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Rate'),
                    'name' => 'rate',
                    'options' => [
                        'query' => [
                            ['id' => 0, 'name' => '0'],
                            ['id' => 1, 'name' => '1'],
                            ['id' => 2, 'name' => '2'],
                            ['id' => 3, 'name' => '3'],
                            ['id' => 4, 'name' => '4'],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
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
                    'type' => 'html', // HTML type for displaying selected product IDs
                    'label' => '',
                    'name' => 'productIds_serving', // Keep the original name for submission
                    'html_content' => '<input type="hidden" name="productIds_serving" id="productIds_serving" value="' . htmlspecialchars($productIds, ENT_QUOTES, 'UTF-8') . '" />'
                ],
                [
                    'type' => 'html',
                    'label' => $this->l('Enregistrer'),
                    'name' => 'products',
                    'required' => false, 
                    'html_content' => $this->getHtmlProduct()
                ],
                [ 
                    'type' => 'switch',
                    'label' => $this->l('Affichage'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Activé')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Désactivé')
                        )
                    ) 
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddegstickers')) {
           
            $id_sticker = (int)Tools::getValue('id_sticker');
            $productIds = Tools::getValue('productIds_serving');

            if (!empty($productIds)) {

                $productIdsArray = explode(',', $productIds);

                // Delete id_product entries not in $productIdsArray for the given id_sticker
                $deleteSql = 'DELETE FROM ' . _DB_PREFIX_ . 'product_sticker 
                              WHERE id_sticker = ' . (int)$id_sticker . ' 
                              AND id_product NOT IN (' . implode(',', array_map('intval', $productIdsArray)) . ')';
                             // echo $deleteSql ;  die;
                Db::getInstance()->execute($deleteSql);

                foreach ($productIdsArray as $id_product) {
                    $id_product = (int)$id_product;
                   
                    // Check if the product-sticker relationship already exists
                    $sql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product_sticker 
                            WHERE id_sticker = ' . (int)$id_sticker . ' AND id_product = ' . (int)$id_product;
                    $exists = Db::getInstance()->getValue($sql);
                   
                    if (!$exists) {
                        // Insert the new relationship
                        $insertSql = 'INSERT INTO ' . _DB_PREFIX_ . 'product_sticker (id_sticker, id_product) 
                                      VALUES (' . (int)$id_sticker . ', ' . (int)$id_product . ')';
                        Db::getInstance()->execute($insertSql);
                    }
                }
            }
        }

        parent::postProcess();
    }
}