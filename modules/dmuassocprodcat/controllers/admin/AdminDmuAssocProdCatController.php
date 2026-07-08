<?php
/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from SARL DREAM ME UP
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL DREAM ME UP is strictly forbidden.
 *
 *   .--.
 *   |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
 *   |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
 *   '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
 *        w w w . d r e a m - m e - u p . f r       '
 *
 *  @author    Dream me up <prestashop@dream-me-up.fr>
 *  @copyright 2007 - 2025 Dream me up
 *  @license   All Rights Reserved
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminDmuAssocProdCatController extends ModuleAdminController
{
    protected $context_id_employee;
    protected $context_id_lang;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;

        $context = Context::getContext();
        $this->context_id_employee = $context->employee->id;
        $this->context_id_lang = $context->language->id;

        $this->token = Tools::getAdminToken(
            get_class($this) . (int) $this->context_id_employee
        );

        parent::__construct();
    }

    public function postProcess()
    {
        if (Tools::getIsset('ajax')) {
            @ob_end_clean();
            $ajax = Tools::getValue('ajax');
            $this->$ajax();
            exit;
        }
    }

    public function renderView()
    {
        $categories = Category::getCategories((int) $this->context_id_lang, false);
        $current = current($categories);
        $current = $current[key($current)];
        $option_select_categories = $this->recurseCategoryNbProds(
            $categories,
            $current,
            $current['infos']['id_category'],
            0
        );
        $option_select_marques = $this->manufacturerNbProds();

        $url = 'index.php?controller=AdminModules&amp;configure=dmuassocprodcat
&amp;tab_module=administration&amp;module_name=dmuassocprodcat&amp;token=';
        $url .= Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign([
            'module_dir' => _MODULE_DIR_,
            'token' => $this->token,
            'is_ps_18' => version_compare(_PS_VERSION_, '8.0.0', '>='),
            'option_select_manufacturer' => $option_select_marques,
            'option_select_categories' => $option_select_categories,
            'txt_infos' => $this->module->l('Informations'),
            'cnf_value' => !Configuration::get('DMUASSOCPRODCAT_INACTIVE_PRODUCTS'),
            'txt_select' => $this->module->l('Current configuration:'),
            'txt_all_products' => $this->module->l('Active and inactive products'),
            'txt_only_active' => $this->module->l('Only active products'),
            'txt_switch' => $this->module->l('define on '),
            'cnf_link' => $url,
            'txt_cnfpage' => $this->module->l('configuration page'),
            'txt_pdts_associes' => $this->module->l('Products to associate'),
            'txt_filtre' => $this->module->l('Filter products'),
            'txt_marque' => $this->module->l('Manufacturer:'),
            'txt_categories' => $this->module->l('Category:'),
            'txt_tous' => $this->module->l('All'),
            'txt_recherche' => $this->module->l('Search:'),
            'txt_resultats' => $this->module->l('Results'),
            'txt_prem_recherche' => $this->module->l('First search'),
            'txt_categorie_defaut' => $this->module->l('Define as default categoy:'),
            'txt_oui' => $this->module->l('Yes'),
            'txt_non' => $this->module->l('No'),
            'txt_assoc_produits' => $this->module->l('Associate selected products to selected category'),
            'txt_depla_produits' => $this->module->l('Move selected products to selected category'),
            'txt_cat_assocs' => $this->module->l('Categories and associations'),
            'txt_cat_explorer' => $this->module->l('Destination'),
            'txt_aucune' => $this->module->l('None'),
            'txt_produits_assocs' => $this->module->l('Associated products'),
            'txt_cadre' => $this->module->l('Product already associated to selected category.'),
            'txt_choix' => $this->module->l('Choose a category to see associations.'),
            'txt_enlever_produits' => $this->module->l('Remove selected products from selected category.'),
        ]);

        return parent::renderView();
    }

    public function renderList()
    {
        return $this->renderView();
    }

    public function renderForm()
    {
        return $this->renderView();
    }

    public function recurseCategoryNbProds($categories, $current, $id_category = 1, $id_selected = 1, $result = [])
    {
        $only_active = !Configuration::get('DMUASSOCPRODCAT_INACTIVE_PRODUCTS');

        // Récupération du nombre de produits de cette catégorie et du nombre de produits par défaut
        $sql = 'SELECT COUNT(DISTINCT cp.id_product) AS nb_products
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                INNER JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = cp.id_product
                WHERE id_category = ' . (int) $id_category .
                ($only_active ? ' AND p.`active` = 1' : '');
        $nb_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        $sql = '
        SELECT COUNT(DISTINCT id_product) AS nb_def 
        FROM ' . _DB_PREFIX_ . 'product 
        WHERE id_category_default = ' . (int) $id_category .
        ($only_active ? ' AND `active` = 1' : '');
        $nb_def_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        $nb_pas_def = ($nb_products - $nb_def_products);

        $chaine_produits = '';
        if ($nb_products > 0) {
            $chaine_produits .= ' (' . $nb_products . ' ' . $this->module->l('products');
            if ($nb_pas_def > 0) {
                $chaine_produits .= ', ' . $nb_pas_def . ' ' . $this->module->l('no by default');
            }
            $chaine_produits .= ')';
        }

        $result[] = [
            'id_category' => $id_category,
            'level_depth' => $current['infos']['level_depth'],
            'name' => stripslashes($current['infos']['name']),
            'nb_products' => $nb_products,
            'nb_pas_def' => $nb_pas_def,
            'selected' => ($id_selected == $id_category ? true : false),
        ];
        if (isset($categories[$id_category])) {
            // foreach ($categories[$id_category] as $key => $row) {
            foreach (array_keys($categories[$id_category]) as $key) {
                $result = $this->recurseCategoryNbProds(
                    $categories,
                    $categories[$id_category][$key],
                    $key,
                    $id_selected,
                    $result
                );
            }
        }

        return $result;
    }

    public function manufacturerNbProds()
    {
        $only_active = !Configuration::get('DMUASSOCPRODCAT_INACTIVE_PRODUCTS');

        $manufacturers = Manufacturer::getManufacturers(
            true,
            (int) $this->context_id_lang,
            $only_active
        );
        $manufacturers = array_unique($manufacturers, SORT_REGULAR);

        return $manufacturers;
    }

    public function ajaxProduits()
    {
        $recherche = Tools::getValue('recherche');
        $r = urldecode($recherche);
        $id_manufacturer = Tools::getValue('id_manufacturer');
        $id_categorie = Tools::getValue('id_categorie');
        $id_categorie_assoc = Tools::getValue('id_categorie_assoc');
        if (!empty($id_manufacturer) || !empty($id_categorie) || !empty($r)) {
            $products = $this->getProductsSearch(
                (int) $this->context_id_lang,
                0,
                0,
                'name',
                'ASC',
                $id_manufacturer,
                $id_categorie,
                $r
            );

            $sql = '
            SELECT id_product 
            FROM ' . _DB_PREFIX_ . 'category_product 
            WHERE id_category = ' . (int) $id_categorie_assoc;
            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

            $ids = [];
            foreach ($res as $prod) {
                $ids[] = $prod['id_product'];
            }

            if (!empty($products)) {
                $i = 0;
                foreach ($products as &$p) {
                    $p['class'] = ((1 == $i % 2) ? 'alt_row' : '');
                    $p['warning'] = (empty($ids) || !in_array($p['id_product'], $ids) ? false : true);

                    // Recupere l'image Cover au format proche de 100px x 100px
                    $img = '';
                    $img_types = ImageType::getImagesTypes('products', true);
                    $founded = false;

                    $img_thumb_height = 150;
                    foreach ($img_types as $type) {
                        if ((int) $type['width'] < 150 && !$founded) {
                            $cover = Product::getCover((int) $p['id_product']);
                            if ($cover) {
                                $objCover = new Image($cover['id_image']);
                                $img_size = $objCover->getSize($type['name']);
                                $img_thumb_height = ($img_size['height'] * 100) / $img_size['width'];
                                $img = _THEME_PROD_DIR_ . $objCover->getExistingImgPath() . '-' . $type['name'] . '.jpg';
                                $founded = true;
                            }
                        }
                    }

                    if ('' != $img) {
                        $p['img_top'] = $img_thumb_height / 2;
                        $p['img_src'] = $img;
                    }
                    ++$i;
                }
            }
        }

        $this->context->smarty->assign([
            'products' => (isset($products) ? $products : false),
            'id_manufacturer' => $id_manufacturer,
            'id_categorie' => $id_categorie,
            'r' => $r,
        ]);

        exit($this->createTemplate('liste_products.tpl')->fetch());
    }

    public function ajaxProduitsAssociation()
    {
        $id_categorie_assoc = Tools::getValue('id_categorie_assoc');
        if (!empty($id_categorie_assoc)) {
            $products = $this->getProductsSearch(
                (int) $this->context_id_lang,
                0,
                0,
                'position',
                'ASC',
                '',
                $id_categorie_assoc
            );

            $objc = new Category($id_categorie_assoc);

            if (!empty($products)) {
                $i = 0;
                foreach ($products as &$p) {
                    $p['class'] = ((1 == $i % 2) ? 'alt_row' : '');

                    // Recupere l'image Cover au format proche de 100px x 100px
                    $img = '';
                    $img_types = ImageType::getImagesTypes('products', true);
                    $founded = false;
                    $img_thumb_height = 150;
                    foreach ($img_types as $type) {
                        if ((int) $type['width'] < 150 && !$founded) {
                            $cover = Product::getCover((int) $p['id_product']);
                            $objCover = new Image($cover['id_image']);
                            $img_size = $objCover->getSize($type['name']);
                            $img_thumb_height = ($img_size['height'] * 100) / $img_size['width'];
                            $img = _THEME_PROD_DIR_ . $objCover->getExistingImgPath() . '-' . $type['name'] . '.jpg';
                            $founded = true;
                        }
                    }

                    if ('' != $img) {
                        $p['img_top'] = $img_thumb_height / 2;
                        $p['img_src'] = $img;
                    }

                    ++$i;
                }
            }
        }

        $this->context->smarty->assign([
            'products' => (isset($products) ? $products : false),
            'id_categorie_assoc' => $id_categorie_assoc,
            'name_cat' => (!empty($id_categorie_assoc) ? $objc->getName() : null),
        ]);

        exit($this->createTemplate('liste_products_cat.tpl')->fetch());
    }

    public function ajaxDoAssociation()
    {
        $id_categorie = Tools::getValue('id_categorie_assoc');
        $prods = Tools::getValue('prods');
        $id_produits = explode(',', $prods);
        $def = Tools::getValue('def');

        if (!empty($id_categorie)) {
            foreach ($id_produits as $id_produit) {
                $produit = new Product((int) $id_produit);
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT `id_category` 
                                                                        AS id FROM `' . _DB_PREFIX_ . 'category_product` 
                                                                        WHERE `id_product` = ' . (int) $id_produit);
                $categories = [];
                foreach ($result as $cat) {
                    $categories[] = $cat['id'];
                }
                $categories[] = $id_categorie;

                $produit->updateCategories($categories);

                if (1 == (int) $def) {
                    $id_shop = Shop::getContextShopID();

                    if (!empty($id_shop)) {
                        if ($id_shop == Configuration::get('PS_SHOP_DEFAULT')) {
                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product` 
                                    SET id_category_default=' . (int) $id_categorie . ' 
                                    WHERE id_product = ' . (int) $id_produit;
                            Db::getInstance()->Execute($sql);
                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                                    SET id_category_default=' . (int) $id_categorie . ' 
                                    WHERE id_product = ' . (int) $id_produit . ' 
                                    AND `id_shop` = ' . (int) $id_shop;
                            Db::getInstance()->Execute($sql);
                        } else {
                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                                    SET id_category_default=' . (int) $id_categorie . ' 
                                    WHERE id_product = ' . (int) $id_produit . ' 
                                    AND `id_shop` = ' . (int) $id_shop;
                            Db::getInstance()->Execute($sql);
                        }
                    } else {
                        $id_shop = Configuration::get('PS_SHOP_DEFAULT');
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product` 
                                SET id_category_default=' . (int) $id_categorie . ' 
                                WHERE id_product = ' . (int) $id_produit;
                        Db::getInstance()->Execute($sql);
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                                SET id_category_default=' . (int) $id_categorie . ' 
                                WHERE id_product = ' . (int) $id_produit . ' 
                                AND `id_shop` = ' . (int) $id_shop;
                        Db::getInstance()->Execute($sql);
                    }
                }
            }
        }
    }

    public function ajaxRemoveAssociation()
    {
        $id_categorie = Tools::getValue('id_categorie_assoc');
        $prods = Tools::getValue('prods');
        $id_produits = explode(',', $prods);

        foreach ($id_produits as $id_produit) {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'category_product` 
                                        WHERE id_category = ' . (int) $id_categorie . ' 
                                        AND `id_product` = ' . (int) $id_produit);
        }
    }

    public function ajaxMakeDefault()
    {
        $id_categorie = Tools::getValue('id_categorie_assoc');
        $prods = Tools::getValue('prods');
        $id_produits = explode(',', $prods);

        foreach ($id_produits as $id_produit) {
            $product = new Product((int) $id_produit, false);
            $product->id_category_default = $id_categorie;
            $product->save();
        }
    }

    public function getProductsSearch(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_manufacturer = false,
        $id_category = false,
        $search = '',
        $only_active = false
    ) {
        $only_active = !Configuration::get('DMUASSOCPRODCAT_INACTIVE_PRODUCTS');

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            exit(Tools::displayError());
        }
        if ('id_product' == $order_by || 'price' == $order_by || 'date_add' == $order_by) {
            $order_by_prefix = 'p';
        } elseif ('name' == $order_by) {
            $order_by_prefix = 'pl';
        } elseif ('position' == $order_by) {
            $order_by_prefix = 'c';
        }

        $where_search = '';
        if (!empty($search) && Tools::strlen($search) >= 2) {
            $where_search = " AND (pl.name LIKE '%" . pSQL($search) . "%'
                                OR pl.description_short LIKE '%" . pSQL($search) . "%'
                                OR pl.description LIKE '%" . pSQL($search) . "%'
                                OR p.reference LIKE '%" . pSQL($search) . "%'
                                OR p.supplier_reference LIKE '%" . pSQL($search) . "%') ";
        }

        $id_shop = Shop::getContextShopID();
        if (empty($id_shop)) {
            $id_shop = Configuration::get('PS_SHOP_DEFAULT');
        }
        $req_prod_shop = '';
        if (!empty($id_shop)) {
            $req_prod_shop = ' AND pl.`id_shop` = ' . (int) $id_shop;
        } else {
            $req_prod_shop = ' AND pl.`id_shop` = 1';
        }
        $sql = '
        SELECT p.*, pl.*, product_shop.id_category_default AS id_category_default
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') . '
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product`' . $req_prod_shop . ')' .
                (!empty($id_category) ? ' 
        LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') . '
        WHERE pl.`id_lang` = ' . (int) $id_lang .
                (!empty($id_category) ? ' AND c.`id_category` = ' . (int) $id_category : '') .
                ($only_active ? ' AND p.`active` = 1' : '') . '
        ' . $where_search . '' .
        (!empty($id_manufacturer) ? ' AND p.`id_manufacturer` = ' . (int) $id_manufacturer : '') . '
        GROUP BY p.id_product
        ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`'
        . pSQL($order_by) . '` ' . pSQL($order_way) . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if ('price' == $order_by) {
            Tools::orderbyPrice($rq, $order_way);
        }

        return $rq;
    }

    public function removeProductsCategory($id_categorie, $products)
    {
        if (empty($products)) {
            return false;
        }

        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_category = ' . (int) $id_categorie . '
                AND id_product IN(' . implode(',', $products) . ')';
        $result = Db::getInstance()->Execute($sql);

        return $result;
    }

    public function ajaxDoDeplacement()
    {
        $id_category_from = Tools::getValue('id_category_from');
        $id_category_to = Tools::getValue('id_category_to');
        $id_produits = explode(',', Tools::getValue('prods'));
        $def = Tools::getValue('def');

        if (!empty($id_category_from) && !empty($id_category_to)) {
            foreach ($id_produits as $id_produit) {
                $nom_table = _DB_PREFIX_ . 'category_product';
                $sql = sprintf('
                    SELECT COALESCE( MAX(position) + 1, 0) 
                    FROM %s 
                    WHERE id_category = %d', $nom_table, $id_category_to);
                $pos = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

                $sql = sprintf(
                    'UPDATE %s 
                    SET id_category = %d, position = %d 
                    WHERE id_product = %d 
                    AND id_category = %d',
                    $nom_table,
                    $id_category_to,
                    $pos,
                    $id_produit,
                    $id_category_from
                );

                if (!Db::getInstance()->execute($sql)) {
                    exit('Erreur : impossible de mettre à jour la table category_product.');
                }

                if (1 == (int) $def || $this->isOnlyInOneCategory($id_produit)) {
                    $id_shop = Shop::getContextShopID();

                    if (!empty($id_shop)) {
                        if ($id_shop == Configuration::get('PS_SHOP_DEFAULT')) {
                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product` 
                                    SET id_category_default=' . (int) $id_category_to . ' 
                                    WHERE id_product = ' . (int) $id_produit;
                            if (!Db::getInstance()->Execute($sql)) {
                                exit('Erreur : impossible de mettre à jour la table product.');
                            }

                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                                    SET id_category_default=' . (int) $id_category_to . ' 
                                    WHERE id_product = ' . (int) $id_produit . ' 
                                    AND `id_shop` = ' . (int) $id_shop;
                            if (!Db::getInstance()->Execute($sql)) {
                                exit('Erreur : impossible de mettre à jour la table product.');
                            }
                        } else {
                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                                    SET id_category_default=' . (int) $id_category_to . ' 
                                    WHERE id_product = ' . (int) $id_produit . ' 
                                    AND `id_shop` = ' . (int) $id_shop;
                            if (!Db::getInstance()->Execute($sql)) {
                                exit('Erreur : impossible de mettre à jour la table product.');
                            }
                        }
                    } else {
                        $id_shop = Configuration::get('PS_SHOP_DEFAULT');
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product` 
                                SET id_category_default=' . (int) $id_category_to . ' 
                                WHERE id_product = ' . (int) $id_produit;
                        if (!Db::getInstance()->Execute($sql)) {
                            exit('Erreur : impossible de mettre à jour la table product.');
                        }
                        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                                SET id_category_default=' . (int) $id_category_to . ' 
                                WHERE id_product = ' . (int) $id_produit . ' 
                                AND `id_shop` = ' . (int) $id_shop;
                        if (!Db::getInstance()->Execute($sql)) {
                            exit('Erreur : impossible de mettre à jour la table product.');
                        }
                    }
                }
            }
        }
    }

    public function isOnlyInOneCategory($id_product)
    {
        $nom_table = _DB_PREFIX_ . 'category_product';
        $sql = sprintf('SELECT id_category 
                        FROM %s 
                        WHERE id_product = %d', $nom_table, $id_product);
        $categories = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return (count($categories) > 1) ? false : true;
    }

    public function saveOrder()
    {
        $table_order = Tools::getValue('tableorder');
        $table_order = explode('-', $table_order);
        $id_category = Tools::getValue('id_category');
        $nom_table = _DB_PREFIX_ . 'category_product';
        foreach ($table_order as $key => $val) {
            $sql = sprintf('UPDATE %s 
                            SET position = %s 
                            WHERE id_product = %d 
                            AND id_category = %d', $nom_table, $key, $val, $id_category);
            Db::getInstance()->execute($sql);
        }
    }
}
