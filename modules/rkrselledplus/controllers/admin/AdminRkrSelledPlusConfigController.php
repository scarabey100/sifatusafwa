<?php
/**
 * @author    Rekire <info@rekire.com>
 * @copyright Rekire
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminRkrSelledPlusConfigController extends ModuleAdminController
{
    protected $position_identifier = 'id_slider';

    public function __construct()
    {
        $this->bootstrap = true;

        $this->table = 'rkr_selled_plus';
        $this->className = 'RkrSelledPlusConfig';
        $this->identifier = 'id_slider';
        $this->lang = true;
        $this->_defaultOrderBy = 'position';
        $this->explicitSelect = true;

        parent::__construct();

        $this->page_header_toolbar_title = $this->module->l('Add carrusel', 'adminrkrselledplusconfigcontroller');

        $this->fields_list = [
            'id_slider' => [
                'title' => $this->module->l('Id', 'adminrkrselledplusconfigcontroller'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'a!id_slider',
                'orderby' => false,
                'search' => false,
            ],
            'name' => [
                'title' => $this->trans('Name', [], 'Admin.Global'),
                'orderby' => false,
                'search' => false,
                'filter_key' => 'a!name',
            ],
            'title' => [
                'title' => $this->module->l('Title', 'adminrkrselledplusconfigcontroller'),
                'orderby' => false,
                'search' => false,
            ],
            'type' => [
                'title' => $this->trans('Type', [], 'Admin.Global'),
                'orderby' => false,
                'search' => false,
                'callback' => 'getType',
            ],
            'hook_short' => [
                'title' => $this->module->l('Hook / ShortCode', 'adminrkrselledplusconfigcontroller'),
                'orderby' => false,
                'search' => false,
                'filter_key' => 'a!id_slider',
                'callback' => 'getHookShortcode',
            ],
            'period' => [
                'title' => $this->trans('Period', [], 'Admin.Global'),
                'orderby' => false,
                'search' => false,
                'callback' => 'getPeriod',
            ],
            'category_option' => [
                'title' => $this->trans('Category', [], 'Admin.Global'),
                'orderby' => false,
                'search' => false,
                'callback' => 'getCategoryOption',
            ],
            'manufacturer_option' => [
                'title' => $this->trans('Brand', [], 'Admin.Global'),
                'orderby' => false,
                'search' => false,
                'callback' => 'getManufacturerOption',
            ],
            'image_type' => [
                'title' => $this->trans('Image type', [], 'Admin.Design.Feature'),
                'orderby' => false,
                'search' => false,
            ],
            'position' => [
                'title' => $this->trans('Position', [], 'Admin.Global'),
                'filter_key' => 'a!position',
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'position' => 'position',
                'orderby' => true,
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Active', [], 'Admin.Global'),
                'active' => 'status',
                'type' => 'bool',
                'filter_key' => 'a!active',
                'orderby' => false,
                'search' => false,
                'width' => 'auto',
            ],
        ];

        $url = $this->context->link->getBaseLink() . 'modules/' . $this->module->name . '/';
        $dirEs = $url . 'docs/readme-es.pdf';
        $dirEn = $url . 'docs/readme-en.pdf';

        $this->fields_options = [
            'selledplusconfig' => [
                'title' => $this->module->l('Documentation', 'adminrkrselledplusconfigcontroller'),
                'image' => '../img/admin/cog.gif',
                'fields' => [
                    'RKR_SELLED_PLUS_ONLY_ORDERS' => [
                        'type' => 'bool',
                        'title' => $this->module->l('Use only products from the order table', 'adminrkrselledplusconfigcontroller'),
                        'key' => 'table_sales',
                    ],
                ],
                'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions'), 'btn btn-default pull-right'],
                'bottom' => '<div><a href="' . $dirEs . '">Documentación</a><br><a href="' . $dirEn . '">Documentation</a></div>',
            ],
        ];

        $this->bulk_actions = [];
    }

    public function initProcess()
    {
        if (($id_slider = Tools::getValue('id_slider')) && Tools::getValue('conf') == 3) {
            $isShortCode = RkrSelledPlusConfig::isShortCodeById($id_slider);
            if ($isShortCode) {
                $this->informations[] = $this->module->l('Short code to use on CMS pages: ') . " {rkrselledplus:$id_slider}";
            }
        }

        return parent::initProcess();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->_select = 'a.`hook_name`';

        return parent::renderList();
    }

    public function renderForm()
    {
        $this->multiple_fieldsets = true;

        $oneToTen = [
            ['id' => 1], ['id' => 2], ['id' => 3], ['id' => 4], ['id' => 5],
            ['id' => 6], ['id' => 7], ['id' => 8], ['id' => 9], ['id' => 10],
        ];

        $titleCategoryVisibility = $this->module->l('No select = all', 'adminrkrselledplusconfigcontroller');
        $filterCat = Validate::isLoadedObject($this->object) ? json_decode($this->object->filter_id_categories) : [];

        $this->fields_form = [
            [
                'form' => [
                    'legend' => [
                        'title' => $this->trans('Config', [], 'Admin.Global'),
                        'icon' => 'icon-cogs',
                    ],
                    'tabs' => [
                        'general' => $this->l('General configuration'),
                        'visibility' => $this->l('Set visibility'),
                        'slider' => $this->l('Carrusel options'),
                        'show' => $this->l('Details to show'),
                        'styles' => $this->l('Font size'),
                    ],
                    'input' => [
                        ['type' => 'text',
                            'name' => 'name',
                            'label' => $this->trans('Name', [], 'Admin.Global'),
                            'maxchar' => 64,
                            'maxlength' => 64,
                            'required' => true,
                            'class' => 'col-lg-9',
                            'tab' => 'general',
                        ],
                        ['type' => 'text',
                            'name' => 'title',
                            'label' => $this->module->l('Title', 'adminrkrselledplusconfigcontroller'),
                            'maxchar' => 128,
                            'maxlength' => 128,
                            'required' => true,
                            'lang' => true,
                            'tab' => 'general',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'type',
                            'label' => $this->module->l('Type', 'adminrkrselledplusconfigcontroller'),
                            'desc' => [
                                $this->module->l('where it shows', 'adminrkrselledplusconfigcontroller'),
                                $this->module->l('In hook or in cms using shortcode', 'adminrkrselledplusconfigcontroller'),
                            ],
                            'required' => true,
                            'options' => [
                                'query' => $this->getAvailableTypes(),
                                'id' => 'id',
                                'name' => 'label',
                            ],
                            'tab' => 'general',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'hook_name',
                            'label' => $this->module->l('Hook', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('hook where to show the best-selling products', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $this->getAvailableHooks(),
                                'id' => 'id',
                                'name' => 'name',
                            ],
                            'tab' => 'general',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'period',
                            'label' => $this->l('Period', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $this->getAvailablePeriods(),
                                'id' => 'id',
                                'name' => 'label',
                            ],
                            'tab' => 'general',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'category_option',
                            'label' => $this->l('Category', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $this->getAvailableCategoryOptions(),
                                'id' => 'id',
                                'name' => 'label',
                            ],
                            'tab' => 'general',
                        ],
                        ['type' => 'categories',
                            'name' => 'id_categories',
                            'label' => $this->l('Categories to display', 'adminrkrselledplusconfigcontroller'),
                            'title' => $this->l('Selected categories to display', 'adminrkrselledplusconfigcontroller'),
                            'tree' => [
                                'id' => 'categories-tree',
                                'selected_categories' => Validate::isLoadedObject($this->object) ? json_decode($this->object->id_categories) : [],
                                'use_checkbox' => true,
                            ],
                            'tab' => 'general',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'manufacturer_option',
                            'label' => $this->trans('Brand', [], 'Admin.Global'),
                            'required' => true,
                            'options' => [
                                'query' => $this->getAvailableManufacturerOptions(),
                                'id' => 'id',
                                'name' => 'label',
                            ],
                            'tab' => 'general',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'id_manufacturer',
                            'label' => $this->l('Choose manufacturer', 'adminrkrselledplusconfigcontroller'),
                            'options' => [
                                'query' => $this->getAvailableManufacturer(),
                                'id' => 'id_manufacturer',
                                'name' => 'name',
                            ],
                            'tab' => 'general',
                        ],
                        ['type' => 'switch',
                            'label' => $this->trans('Active', [], 'Admin.Global'),
                            'name' => 'active',
                            'required' => true,
                            'values' => [
                                ['id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], ['id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'general',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Active visibility filter', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'filter_page_visibility',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Home page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_index',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                    'class' => 'rkr-visibility',
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Product page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_product',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                    'class' => 'rkr-visibility',
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Category page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_category',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'html',
                            'name' => 'filter_id_categories',
                            'label' => $this->l('Show on this category page', 'adminrkrselledplusconfigcontroller'),
                            'html_content' => $this->getCategoryHtml(
                                'categories-tree-filter', 'filter_id_categories', $titleCategoryVisibility, $filterCat
                            ),
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Addresses Page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_addresses',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Manufacturer page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_manufacturer',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'filter_id_manufacturer',
                            'label' => $this->l('Choose manufacturer', 'adminrkrselledplusconfigcontroller'),
                            'options' => [
                                'query' => $this->getAvailableManufacturerFilter(),
                                'id' => 'id_manufacturer',
                                'name' => 'name',
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Supplier page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_supplier',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Cms page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_cms',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Cart page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_cart',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('My account page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_my_account',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        ['type' => 'switch',
                            'label' => $this->l('Sitemap page', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'page_sitemap',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'visibility',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'image_type',
                            'label' => $this->module->l('Images size', 'adminrkrselledplusconfigcontroller'),
                            'desc' => 'Size:',
                            'required' => true,
                            'options' => [
                                'query' => ImageType::getImagesTypes('products'),
                                'id' => 'name',
                                'name' => 'name',
                            ],
                            'tab' => 'slider',
                        ],
                        ['type' => 'text',
                            'name' => 'max_products_show',
                            'label' => $this->module->l('Max products to show on the carrusel', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('The page load could be slow if there are a lot products',
                                'adminrkrselledplusconfigcontroller'),
                            'maxchar' => 6,
                            'maxlength' => 6,
                            'class' => 'col-md-4 col-lg-3',
                            'tab' => 'slider',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'columns_xl',
                            'label' => $this->module->l('Max columns to show on displays >= 1200', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $oneToTen,
                                'id' => 'id',
                                'name' => 'id',
                            ],
                            'tab' => 'slider',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'columns_lg',
                            'label' => $this->module->l('Max columns to show on displays >= 992', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $oneToTen,
                                'id' => 'id',
                                'name' => 'id',
                            ],
                            'tab' => 'slider',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'columns_md',
                            'label' => $this->module->l('Max columns to show on displays >= 768', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $oneToTen,
                                'id' => 'id',
                                'name' => 'id',
                            ],
                            'tab' => 'slider',
                        ],
                        [
                            'type' => 'select',
                            'name' => 'columns_sm',
                            'label' => $this->module->l('Max columns to show on displays >= 576', 'adminrkrselledplusconfigcontroller'),
                            'required' => true,
                            'options' => [
                                'query' => $oneToTen,
                                'id' => 'id',
                                'name' => 'id',
                            ],
                            'tab' => 'slider',
                        ],
                        ['type' => 'switch',
                            'label' => $this->trans('Autoplay', [], 'Admin.Global'),
                            'name' => 'autoplay',
                            'value' => 1,
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'slider',
                        ],
                        ['type' => 'switch',
                            'label' => $this->trans('Loop', [], 'Admin.Global'),
                            'name' => 'loop',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'slider',
                        ],
                        ['type' => 'switch',
                            'label' => $this->trans('Lazy load', [], 'Admin.Global'),
                            'name' => 'lazy',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'slider',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('show product name', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_product_name',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('show product category', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_product_category',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('show "add to cart"', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_add_to_cart',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('Show price', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_price',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('Process hook displayProductPriceBlock', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_displayProductPriceBlock',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('Show product variants', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_product_variants',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('Show stickers', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_product_flags',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('Show quick view', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'show_product_quick_view',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'show',
                        ],
                        ['type' => 'switch',
                            'label' => $this->module->l('Default sizes', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('Use theme default sizes', 'adminrkrselledplusconfigcontroller'),
                            'name' => 'default_style',
                            'values' => [
                                [
                                    'value' => 1,
                                    'label' => $this->trans('Enabled', [], 'Admin.Global'),
                                ], [
                                    'value' => 0,
                                    'label' => $this->trans('Disabled', [], 'Admin.Global'),
                                ],
                            ],
                            'tab' => 'styles',
                        ],
                        ['type' => 'text',
                            'name' => 'rem_size_title',
                            'label' => $this->module->l('Title font size', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('Size in rem', 'adminrkrselledplusconfigcontroller'),
                            'maxchar' => 6,
                            'maxlength' => 6,
                            'class' => 'col-md-4 col-lg-3 rem-size',
                            'disabled' => true,
                            'tab' => 'styles',
                        ],
                        ['type' => 'text',
                            'name' => 'rem_size_product_name',
                            'label' => $this->module->l('Product name font size', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('Size in rem', 'adminrkrselledplusconfigcontroller'),
                            'maxchar' => 6,
                            'maxlength' => 6,
                            'class' => 'col-md-4 col-lg-3 rem-size',
                            'disabled' => true,
                            'tab' => 'styles',
                        ],
                        ['type' => 'text',
                            'name' => 'rem_size_product_category',
                            'label' => $this->module->l('Product category font size', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('Size in rem', 'adminrkrselledplusconfigcontroller'),
                            'maxchar' => 6,
                            'maxlength' => 6,
                            'class' => 'col-md-4 col-lg-3 rem-size',
                            'disabled' => true,
                            'tab' => 'styles',
                        ],
                        ['type' => 'text',
                            'name' => 'rem_size_product_price',
                            'label' => $this->module->l('Product price size', 'adminrkrselledplusconfigcontroller'),
                            'desc' => $this->module->l('Size in rem', 'adminrkrselledplusconfigcontroller'),
                            'maxchar' => 6,
                            'maxlength' => 6,
                            'class' => 'col-md-4 col-lg-3 rem-size',
                            'disabled' => true,
                            'tab' => 'styles',
                        ],
                    ],
                    'submit' => [
                        'title' => $this->trans('Save', [], 'Admin.Actions'),
                    ],
                ],
            ],
        ];

        if (!Validate::isLoadedObject($this->object)) {
            $this->fields_value['image_type'] = ImageType::getFormattedName('home');
        }

        return parent::renderForm();
    }

    public function getCategoryHtml($id, $inputName, $title = '', $selectedCategories = [])
    {
        $root = Category::getRootCategory();

        $tree = new HelperTreeCategories($id, $title);
        $tree->setUseCheckBox(true)
            ->setRootCategory($root->id)
            ->setSelectedCategories($selectedCategories)
            ->setInputName($inputName);

        return $tree->render();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia();

        $moduleDir = _PS_MODULE_DIR_ . $this->module->name;
        if ($this->display == 'add' || $this->display == 'edit' || $this->display == 'list') {
            Media::addJsDef([
                'products_images' => ImageType::getImagesTypes('products'),
                'hook_description' => $this->module->l('hook where to show the best-selling products', 'adminrkrselledplusconfigcontroller'),
                'hook_description_custom' => $this->module->l('Add this line in the desired template: ', 'adminrkrselledplusconfigcontroller'),
                'hook_cart_disabled' => $this->module->l(' (add to cart is disabled)', 'adminrkrselledplusconfigcontroller'),
            ]);
            $this->addJS($moduleDir . '/views/js/rkr_selledplus_config.js');
        }
    }

    public function getAvailableTypes()
    {
        return [
            1 => [
                'id' => 1,
                'label' => $this->module->l('Hook', 'adminrkrselledplusconfigcontroller'),
            ],
            2 => [
                'id' => 2,
                'label' => $this->module->l('Short code', 'adminrkrselledplusconfigcontroller'),
            ],
        ];
    }

    public function getHookShortcode($valor, $arrDatos)
    {
        if ($arrDatos['type'] == 1) {
            return $arrDatos['hook_name'];
        } else {
            return "{rkrselledplus:$valor}";
        }
    }

    public function getType($valor, $arrDatos)
    {
        return $this->getAvailableTypes()[$valor]['label'];
    }

    protected function getAvailableHooks()
    {
        return [
            [
                'id' => 'displayHome',
                'name' => 'Home page',
            ], [
                'id' => 'displayTop',
                'name' => 'displayTop',
            ], [
                'id' => 'displayLeftColumn',
                'name' => 'displayLeftColumn',
            ], [
                'id' => 'displayRightColumn',
                'name' => 'displayRightColumn',
            ], [
                'id' => 'displayLeftColumnProduct',
                'name' => 'displayLeftColumnProduct',
            ], [
                'id' => 'displayRightColumnProduct',
                'name' => 'displayRightColumnProduct',
            ], [
                'id' => 'displayProductAdditionalInfo',
                'name' => 'displayProductAdditionalInfo',
            ], [
                'id' => 'displayFooter',
                'name' => 'displayFooter',
            ], [
                'id' => 'displayFooterAfter',
                'name' => 'displayFooterAfter',
            ], [
                'id' => 'displayFooterBefore',
                'name' => 'displayFooterBefore',
            ], [
                'id' => 'displayFooterProduct',
                'name' => 'displayFooterProduct',
            ], [
                'id' => 'displayShoppingCart',
                'name' => 'displayShoppingCart',
            ], [
                'id' => 'displayShoppingCartFooter',
                'name' => 'displayShoppingCartFooter',
            ], [
                'id' => 'displayOrderConfirmation',
                'name' => 'displayOrderConfirmation',
            ], [
                'id' => 'displayOrderConfirmation1',
                'name' => 'displayOrderConfirmation1',
            ], [
                'id' => 'displayOrderConfirmation2',
                'name' => 'displayOrderConfirmation2',
            ], [
                'id' => 'displayCrossSellingShoppingCart',
                'name' => 'displayCrossSellingShoppingCart',
            ], [
                'id' => 'displaySearch',
                'name' => 'displaySearch',
            ], [
                'id' => 'displayNavFullWidth',
                'name' => 'displayNavFullWidth',
            ], [
                'id' => 'displayNav',
                'name' => 'displayNav',
            ], [
                'id' => 'displayNav1',
                'name' => 'displayNav1',
            ], [
                'id' => 'displayNav2',
                'name' => 'displayNav2',
            ], [
                'id' => 'displayReassurance',
                'name' => 'displayReassurance',
            ], [
                'id' => 'displayRkrSelledPlus1',
                'name' => 'Custom hook 1',
            ], [
                'id' => 'displayRkrSelledPlus2',
                'name' => 'Custom hook 2',
            ], [
                'id' => 'displayRkrSelledPlus3',
                'name' => 'Custom hook 3',
            ],
        ];
    }

    protected function getAvailablePeriods()
    {
        return [
            'all' => [
                'id' => 'all',
                'label' => $this->module->l('All', 'adminrkrselledplusconfigcontroller'),
            ],
            '1 day' => [
                'id' => '1 day',
                'label' => $this->module->l('1 day', 'adminrkrselledplusconfigcontroller'),
            ],
            '1 week' => [
                'id' => '1 week',
                'label' => $this->module->l('1 week', 'adminrkrselledplusconfigcontroller'),
            ],
            '1 month' => [
                'id' => '1 month',
                'label' => $this->module->l('1 month', 'adminrkrselledplusconfigcontroller'),
            ],
            '3 months' => [
                'id' => '3 months',
                'label' => $this->module->l('3 months', 'adminrkrselledplusconfigcontroller'),
            ],
            '6 months' => [
                'id' => '6 months',
                'label' => $this->module->l('6 months', 'adminrkrselledplusconfigcontroller'),
            ],
            '1 year' => [
                'id' => '1 year',
                'label' => $this->module->l('1 year', 'adminrkrselledplusconfigcontroller'),
            ],
        ];
    }

    public function getPeriod($valor, $arrDatos)
    {
        return $this->getAvailablePeriods()[$valor]['label'];
    }

    public function getAvailableCategoryOptions()
    {
        return [
            1 => [
                'id' => 1,
                'label' => $this->module->l('All', 'adminrkrselledplusconfigcontroller'),
            ],
            2 => [
                'id' => 2,
                'label' => $this->module->l('Choose Category', 'adminrkrselledplusconfigcontroller'),
            ],
            3 => [
                'id' => 3,
                'label' => $this->module->l('Current', 'adminrkrselledplusconfigcontroller'),
            ],
        ];
    }

    public function getCategoryOption($valor, $arrDatos)
    {
        return $this->getAvailableCategoryOptions()[$valor]['label'];
    }

    public function getAvailableManufacturer()
    {
        return Manufacturer::getManufacturers();
    }

    public function getAvailableManufacturerOptions()
    {
        return [
            1 => [
                'id' => 1,
                'label' => $this->module->l('All', 'adminrkrselledplusconfigcontroller'),
            ],
            2 => [
                'id' => 2,
                'label' => $this->module->l('Choose manufacturer', 'adminrkrselledplusconfigcontroller'),
            ],
            3 => [
                'id' => 3,
                'label' => $this->module->l('Current', 'adminrkrselledplusconfigcontroller'),
            ],
        ];
    }

    public function getManufacturerOption($valor, $arrDatos)
    {
        return $this->getAvailableManufacturerOptions()[$valor]['label'];
    }

    public function getAvailableManufacturerFilter()
    {
        return array_merge(
            [['id_manufacturer' => 0, 'name' => $this->module->l('All', 'adminrkrselledplusconfigcontroller')]],
            Manufacturer::getManufacturers()
        );
    }

    protected function copyFromPost(&$object, $table)
    {
        $oldHookName = $object->hook_name;
        parent::copyFromPost($object, $table);
        $object->id_categories = json_encode(Tools::getValue('id_categories', []));
        $object->filter_id_categories = json_encode(Tools::getValue('filter_id_categories', []));

        if (!Tools::isSubmit('hook_name') && $oldHookName) {
            $object->hook_name = '';
        }
    }

    public function processAdd()
    {
        $retParent = parent::processAdd();

        if ($retParent !== false) {
            $hook = Tools::getValue('hook_name');
            if (!Hook::isModuleRegisteredOnHook($this->module, $hook, $this->context->shop->id)) {
                $this->module->registerHook($hook);
            }
        }

        return $retParent;
    }

    public function processUpdate()
    {
        $newType = Tools::getValue('type');
        $oldType = $this->object->type;

        $newHook = Tools::getValue('hook_name');
        $oldHook = $this->object->hook_name;

        $retParent = parent::processUpdate();
        if ($retParent !== false) {
            if ($oldHook != $newHook) {
                if ($this->module->isRegisteredInHook($oldHook) && !RkrSelledPlusConfig::isExist($oldHook)) {
                    $this->module->unregisterHook($oldHook);
                }
            }
            if ($newType == 1) {
                if (!$this->module->isRegisteredInHook($newHook)) {
                    $this->module->registerHook($newHook);
                }
            }
        }

        return $retParent;
    }

    public function ajaxProcessUpdatePositions()
    {
        $way = (int) Tools::getValue('way');
        $id_reg = (int) Tools::getValue('id');
        $positions = Tools::getValue('slider');

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int) $pos[2] === $id_reg) {
                if ($campo = new RkrSelledPlusConfig((int) $pos[2])) {
                    if (isset($position) && $campo->updatePosition($way, $position)) {
                        echo 'ok position ' . (int) $position . ' for reg ' . (int) $pos[1] . '\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update reg ' . (int) $id_reg . ' to position ' . (int) $position . ' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This reg (' . (int) $id_reg . ') can t be loaded"}';
                }
                break;
            }
        }
    }
}
