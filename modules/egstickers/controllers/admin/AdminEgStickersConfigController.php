<?php



class AdminEgStickersConfigController extends ModuleAdminController
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
    private static $productFlags = [
        ['id' => 'new', 'name' => 'New product'],
        ['id' => 'on-sale', 'name' => 'On sale'],
        ['id' => 'discount', 'name' => 'Reduction',],
        ['id' => 'specific-price-discount', 'name' => '% Discount (Specific price)'],
        ['id' => 'online_only', 'name' => 'Available only online'],
        ['id' => 'out_of_stock', 'name' => 'Out of stock'],
    ];
    public function __construct()
    {
        $this->module = Module::getInstanceByName('egstickers'); // Initialize the module instance
        $this->bootstrap = true;
        $this->table = 'egstickers_flags';
        $this->className = 'EgStickersFlags';
        $this->lang = false;
        $this->identifier = 'id_flag';
        $this->allow_export = true;

        parent::__construct();

        $this->fields_list = [
            'id_flag' => [
                'title' => $this->module->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'native_flag' => [
                'title' => $this->module->l('Native Flag'),
            ],
            'parallel_value' => [
                'title' => $this->module->l('Parallel Value'),
            ],
            'sticker_position' => [
                'title' => $this->module->l('Position'),
                'type' => 'int',
            ],
            'color' => [
                'title' => $this->module->l('Color'),
                'type' => 'color',
            ],
            'active' => [
                'title' => $this->module->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->module->l('Delete selected'),
                'confirm' => $this->module->l('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
        ];
        $this->addRowAction('edit');
        $this->addRowAction('delete');
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Edit Flag Configuration'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->module->l('Native Flag'),
                    'name' => 'native_flag',
                    'default_value' => 1,
                    'options' => array(
                        'query' => self::$productFlags,
                        'id' => 'id',
                        'name' => 'name',
                        ),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Parallel Value'),
                    'name' => 'parallel_value',
                    'required' => true,
                    'lang' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Position of sticker'),
                    'name' => 'sticker_position',
                    'default_value' => 2,
                    'options' => array(
                        'query' => self::$sticker_position,
                        'id' => 'id',
                        'name' => 'name',
                        ),
                ],
                [
                    'type' => 'color',
                    'label' => $this->module->l('Color'),
                    'name' => 'color',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Active'),
                    'name' => 'active',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Enabled'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('Disabled'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save'),
            ],
        ];

        return parent::renderForm();
    }
}