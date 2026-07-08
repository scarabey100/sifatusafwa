<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

class AdminEgWishlistController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = Module::getInstanceByName('egwishlist');
        $this->table = 'egwishlist_product';
        $this->className = 'EgWishListProduct';
        $this->lang = false;
        $this->bootstrap = true;
        parent::__construct();

        // Remove DISTINCT, use only aggregate and group fields in _select
        $this->_select = 'MAX(a.date_add) as date_add';
        $this->_group = 'GROUP BY a.id_customer';

        $this->fields_list = array(
            'id_customer' => array(
                'title' => $this->module->l('Customer ID'),
                'filter_key' => 'a.id_customer',
            ),
            'customer_email' => array(
                'title' => $this->module->l('Email'),
                'filter_key' => 'c.email',
            ),
            'date_add' => array(
                'title' => $this->module->l('Date added'),
                'filter_key' => 'a.date_add',
            ),
        );

        $this->addRowAction('edit');
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        if (!empty($this->_list)) {
            foreach ($this->_list as &$row) {
                $customer = new Customer((int)$row['id_customer']);
                $row['customer_email'] = $customer->email;
            }
        }
    }


    /**
     * Render product names as HTML links in the admin list (grouped by customer).
     */
    public function renderProductsNames($value, $row)
    {
        $id_customer = isset($row['id_customer']) ? (int)$row['id_customer'] : 0;
        if (!$id_customer) {
            return '';
        }
        $id_lang = (int)Context::getContext()->language->id;
        $link = Context::getContext()->link;

        $sql = 'SELECT a.id_product, a.id_product_attribute, pl.name
                FROM '._DB_PREFIX_.'egwishlist_product a
                LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = a.id_product AND pl.id_lang = '.$id_lang.')
                WHERE a.id_customer = '.$id_customer.'
                GROUP BY a.id_product, a.id_product_attribute';

        $products = Db::getInstance()->executeS($sql);

        $links = [];
        foreach ($products as $prod) {
            $product = new Product((int)$prod['id_product'], false, $id_lang);
            $url = $link->getProductLink($product, null, null, null, $id_lang, null, (int)$prod['id_product_attribute']);
            $name = $prod['name'];
            $reference = $product->reference;
            $attr_text = '';
            if (!empty($prod['id_product_attribute'])) {
                $combination = new Combination((int)$prod['id_product_attribute']);
                $attrs = $combination->getAttributesName($id_lang);
                if ($attrs) {
                    $attr_text = ' (' . implode(' / ', array_column($attrs, 'name')) . ')';
                }
            }
            $links[] = 'Réference : '.$reference.' <a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($name . $attr_text) . '</a>';
        }

        return implode('<br>', $links);
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }
        // Show a grouped preview of the wishlist products for the customer in the form
        $productsPreview = '';
        $emailPreview = '';
        $id_customer = $obj->id_customer;
        if ($id_customer) {
            $productsPreview = $this->renderProductsNames('', ['id_customer' => $id_customer]);
            $customer = new Customer((int)$id_customer);
            $emailPreview = '<input type="text" name="customer_email" id="customer_email" value="'.$customer->email.'" class="" required="required">';
        }
        $inputs = array(
            array(
                'type' => 'text',
                'label' => $this->module->l('Customer ID'),
                'name' => 'id_customer',
                'required' => true,
                'autoload_rte' => false,
            ),
            array(
                'type' => 'html',
                'label' => $this->module->l('Email'),
                'name' => 'customer_email', 
                'html_content' => $emailPreview,
            ),
            array(
                'type' => 'html',
                'label' => $this->module->l('Wishlist Products'),
                'name' => 'wishlist_products_preview',
                'html_content' => $productsPreview ? $productsPreview : '',
            ),
            array(
                'type' => 'datetime',
                'label' => $this->module->l('Date Added'),
                'name' => 'date_add',
            ),
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Edit Wishlist Product'),
            ),
            'input' => $inputs,
        );

        return parent::renderForm();
    }
}
