<?php

class AdminProductdealController extends ModuleAdminController
{
    public function ajaxProcessPopup()
    {
        $idProduct = (int)Tools::getValue('idProduct');
        $product = new Product($idProduct, true);

        if (Validate::isLoadedObject($product)) {

        }

        $languages = Language::getLanguages(false);
        $combinationsNames = [];
        foreach ($languages as $key => $l) {
            $attributes = $product->getAttributeCombinations($l['id_lang']);
            foreach ($attributes as $attribute) {
                $combinationsNames[$l['id_lang']][$attribute['id_product_attribute']] = $attribute['attribute_name'];
            }
        }

        $this->context->smarty->assign(
            [
                'idProduct' => $idProduct,
                'product' => $product,
                'defaultIdProductAttribute' => $product->getDefaultIdProductAttribute(),
                'combinations' => $product->getAttributeCombinations($this->context->language->id),
                'combinationsNames' => $combinationsNames,
                'stickers' => $this->getStickers(),
                'languages' => Language::getLanguages(false),
                'originalProductUrl' => $this->context->link->getProductLink($product, null, null, null, $this->context->language->id),
            ]
        );

        exit($this->module->display(_PS_MODULE_DIR_ . $this->module->name . DIRECTORY_SEPARATOR . $this->module->name . '.php', 'popup.tpl'));
    }

    public function ajaxProcessCreate()
    {
        $originalIdProduct = (int)Tools::getValue('originalIdProduct');
        $originalProductIdProductAttribute = (int)Tools::getValue('originalProductIdProductAttribute');
        parse_str(Tools::getValue('productName'), $productName);
        parse_str(Tools::getValue('productUrl'), $productUrl);

        $productReference = Tools::getValue('productReference');
        $productIdSticker = Tools::getValue('productSticker');
        $productQuantity = Tools::getValue('productQuantity');
        $productDiscount = Tools::getValue('productDiscount');


        $originalProduct = new Product($originalIdProduct, null);
        $languages = Language::getLanguages(false);
        $product = new Product();
        foreach ($languages as $l) {
            $product->name[$l['id_lang']] = $productName['product_detail_product_name'][$l['id_lang']];
            $product->link_rewrite[$l['id_lang']] = $productUrl['product_detail_url'][$l['id_lang']];
            $product->description_short[$l['id_lang']] = $this->getDescriptionShort($l['id_lang'], $productIdSticker, $originalIdProduct, $originalProductIdProductAttribute);

            $product->meta_description[$l['id_lang']] = $originalProduct->meta_description[$l['id_lang']];
            $product->meta_title[$l['id_lang']] = $productName['product_detail_product_name'][$l['id_lang']];
        }

        if ($originalProductIdProductAttribute > 0) {
            $combination = new Combination($originalProductIdProductAttribute);
            $product->price = $originalProduct->getPrice(false, $originalProductIdProductAttribute);
            $product->ean13 = $combination->ean13;
            $product->isbn = $combination->isbn;
            $product->weight = $combination->weight;
        } else {
            $product->price = $originalProduct->price;
            $product->ean13 = $originalProduct->ean13;
            $product->isbn = $originalProduct->isbn;
            $product->weight = $originalProduct->weight;
        }

        $product->reference = $productReference;
        $product->id_tax_rules_group = $originalProduct->id_tax_rules_group;
        $product->name_ar = $originalProduct->name_ar;


        $product->id_supplier = $originalProduct->id_supplier;
        $product->id_manufacturer = $originalProduct->id_manufacturer;

        $product->id_category_default = 125; //Deals category
        $product->active = false;

        if ($product->add()) {

            $product->addToCategories([57, 125]);
            StockAvailable::setQuantity($product->id, '0', $productQuantity);

            $this->addDiscount($product->id, $productDiscount);

            $features = $originalProduct->getFeatures();

            if ($originalProductIdProductAttribute > 0) {
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'featuresforcombinations`
                        WHERE `id_product` = ' . $originalIdProduct . '
                        AND `id_product_attribute` = ' . $originalProductIdProductAttribute;

                $results = Db::getInstance()->executeS($sql);

                foreach ($features as $row) {
                    $result[$row['id_feature']] = $row;
                }
                foreach ($results as $row) {
                    $result[$row['id_feature']] = $row;
                }
                $features = array_values($result);

            }

            foreach ($features as $feature) {
                Product::addFeatureProductImport($product->id, $feature['id_feature'], $feature['id_feature_value']);
            }

            $imageUrls = $this->getImagesUrl($originalProduct, $originalProductIdProductAttribute);

            foreach ($imageUrls as $key => $image_url) {

                $url = $image_url;
                $image = new Image();
                $image->id_product = $product->id;
                if ($key == 0) {
                    $image->cover = true;
                }

                if ($image->add()) {
                    if (!ImageManager::copyImg($product->id, $image->id, $url, 'products', true)) {
                        $image->delete();
                    }
                }
            }

            $product->date_add = '2024-11-13 15:37:20';
            $product->update(); // or ->add() if new

            Db::getInstance()->insert('product_sticker', [
                'id_product' => (int)$product->id,
                'id_sticker' => (int)$productIdSticker,
            ]);
        }
    }

    public function addDiscount($idProduct, $discount)
    {
        $specific_price = new SpecificPrice();
        $specific_price->id_shop = 0;
        $specific_price->id_country = 0;
        $specific_price->id_group = 0;
        $specific_price->id_customer = 0;
        $specific_price->id_product = $idProduct;
        $specific_price->id_product_attribute = 0;
        $specific_price->id_currency = 2;
        $specific_price->from_quantity = 0;
        $specific_price->price = -1;
        $specific_price->reduction = $discount / 100;
        $specific_price->reduction_type = 'percentage';
        $specific_price->from = '0000-00-00 00:00:00';
        $specific_price->to = '0000-00-00 00:00:00';
        return $specific_price->add();
    }

    public function getImagesUrl($originalProduct, $originalProductIdProductAttribute)
    {
        if ($originalProductIdProductAttribute > 0) {
            $idImages = $originalProduct->getCombinationImages($this->context->language->id)[$originalProductIdProductAttribute];
        } else {
            $idImages = $originalProduct->getImages($this->context->language->id);
        }
        $urls = [];
        foreach ($idImages as $idImage) {
            $digits = str_split($idImage['id_image']);
            $url = 'https://www.sifatusafwa.com/img/p/' . implode('/', $digits) . '/' . $idImage['id_image'] . '.jpg';
            $urls[] = $url;
        }

        return $urls;
    }

    public function getDescriptionShort($idLang, $idSticker, $originalIdProduct, $originalProductIdProductAttribute): string
    {
        $sql = 'SELECT `rate` FROM `' . _DB_PREFIX_ . 'egstickers`
        WHERE `id_sticker` = ' . (int )$idSticker;
        $rate = Db::getInstance()->getValue($sql);

        $row = [];
        $row['id_product'] = $originalIdProduct;
        $row['id_product_attribute'] = $originalProductIdProductAttribute;
        $productUrl = Product::getProductProperties($this->context->language->id, $row)['link'];

        if ($rate == 4) { //very good
            if ($idLang == 1 || $idLang == 4) { //English, Arabic
                return "<p><strong>Very good condition:</strong> Book in perfect condition with the exception of a slight defect such as: stained page edges (fingerprints or slight moisture stains), superficial defect on the cover (less than 1cm), sticker or trace of sticker, a few creased pages.
                        <br><br>
                        A book in very good condition has only one of these damages.
                        <br><br>
                        This book is new but in a damaged condition. You can find the listing of the product <a target='_blank' style='text-decoration: underline;' href='" . $productUrl . "'>here</a>.
                        <br><br>
                        <i>The defective books sold as part of the campaign \"Warehouse Deals\" can neither be refunded nor exchanged.</i>
                        </p>";
            } else if ($idLang == 3) { //French
                return "<p><strong>Très bon état :</strong> Livre en parfait état à l'exception d’un léger défaut tel que : tranche des pages tachée (traces de doigt ou légères taches d’humidité), défaut superficiel sur la couverture (- de 1cm), autocollant ou trace d’autocollant, quelques pages froissées.
                        <br><br>
                        Un livre en très bon état ne présente qu’un seul de ces dommages.
                        <br><br>
                        <i>Ce livre est neuf, mais il est endommagé. Vous pouvez trouver la fiche produit du livre neuf <a target='_blank' style='text-decoration: underline;' href='" . $productUrl . "'>ici</a>.</i>
                        <br><br>
                        <i>Les livres défectueux vendus dans le cadre de l’opération “Les Occaz de l’Entrepôt” ne peuvent être ni remboursé ni échangé.</i>
                        </p>";
            }
        }

        if ($rate == 3) { //good
            if ($idLang == 1 || $idLang == 4) { //English, Arabic
                return "<p><strong>Good condition:</strong> Book in good condition with one or more defects : stained page edges (fingerprints or light moisture stains), superficial defect on the cover (less than 1cm), sticker or trace of sticker, a few creased pages, scratches or tears of less than 2cm on a corner or on the back cover, crease or fold mark due to transport, trace of wear and tear under the edge of the book (mark due to storage) 
                        <br><br>
                        A book in good condition has only one or two of these damages.
                        <br><br>
                        This book is new but in a damaged condition. You can find the listing of the product <a target='_blank' style='text-decoration: underline;' href='" . $productUrl . "'>here</a>.
                        <br><br>
                        <i>The defective books sold as part of the campaign \"Warehouse Deals\" can neither be refunded nor exchanged.</i>
                        </p>";
            } else if ($idLang == 3) { //French
                return "<p><strong>Bon état :</strong> Livre en bon état présentant un ou plusieurs défauts : tranche des pages tachée (traces de doigt ou légères taches d’humidité), défaut superficiel sur la couverture (- de 1cm), autocollant ou trace d’autocollant, quelques pages froissées, rayures ou déchirures de moins de 2cm sur un angle ou sur le derrière de la couverture, marque de froissement ou de pli due au transport, trace d’usure sous la tranche du livre (marque due à l’entreposage). 
                        <br><br>
                        Un livre en bon état ne présente qu’un ou deux de ces dommages.
                        <br><br>
                        Ce livre est neuf, mais il est endommagé. Vous pouvez trouver la fiche produit du livre neuf <a target='_blank' style='text-decoration: underline;' href='" . $productUrl . "'>ici</a>.
                        <br><br>
                        <i>Les livres défectueux vendus dans le cadre de l’opération “Les Occaz de l’Entrepôt” ne peuvent être ni remboursé ni échangé.</i>
                        </p>";
            }
        }

        if ($rate == 2) { //Acceptable
            if ($idLang == 1 || $idLang == 4) { //English, Arabic
                return "<p><strong>Acceptable:</strong> Visibly damaged book, but overall appearance is good and perfectly readable. Possible defects include: large scratches or tears on the cover, moisture marks on the pages, defects on the front cover, cover may be bent or misaligned on one side.
                        <br><br>
                        A book in acceptable condition has only one or two of these damages, or several lights damages.
                        <br><br>
                        This book is new but in a damaged condition. You can find the listing of the product <a target='_blank' style='text-decoration: underline;' href='" . $productUrl . "'>here</a>.
                        <br><br>
                        <i>The defective books sold as part of the campaign \"Warehouse Deals\" can neither be refunded nor exchanged.</i>
                        </p>";
            } else if ($idLang == 3) { //French
                return "<p><strong>Acceptable :</strong> Livre abîmé de façon visible, mais dont l’aspect général reste bon et parfaitement lisible. Parmi les défauts possibles : larges rayures ou déchirures sur la couverture, traces d’humidité sur les pages, défaut sur la face de la couverture, la couverture peut être pliée ou désaxée sur un de ses côtés.
                        <br><br>
                        Un livre en état acceptable ne présente qu’un ou deux de ces dommages, ou plusieurs défauts légers.
                        <br><br>
                        Ce livre est neuf, mais il est endommagé. Vous pouvez trouver la fiche produit du livre neuf <a target='_blank' style='text-decoration: underline;' href='" . $productUrl . "'>ici</a>.
                        <br><br> 
                        <i>Les livres défectueux vendus dans le cadre de l’opération “Les Occaz de l’Entrepôt” ne peuvent être ni remboursé ni échangé.</i>
                        </p>";
            }
        }

        if ($rate == 1) {
            if ($idLang == 1 || $idLang == 4) { //English, Arabic
                return "<p><strong>Poor condition:</strong> Significantly damaged book, the damaged appearance is clearly visible, the book remains readable and complete. Possible defects include: torn or twisted cover, significant moisture, deteriorated exterior appearance. 
                        <br><br>
                        A book in poor condition may have one or more of these damages.
                        <br><br>
                        This book is new but in a damaged condition. You can find the listing of the product <a target='_blank' href='" . $productUrl . "'>here</a>.
                        <br><br>
                        <i>The defective books sold as part of the campaign \"Warehouse Deals\" can neither be refunded nor exchanged.</i>
                        </p>";
            } else if ($idLang == 3) { //French
                return "<p><strong>Mauvais état :</strong> Livre endommagé de façon importante, l’aspect abîmé est clairement visible, le livre reste lisible et complet. Parmi les défauts possibles : couverture déchirée ou tordue, trace d'humidité importante, aspect extérieur détérioré.
                        <br><br>
                        Un livre en mauvais état peut présenter un ou plusieurs de ces dommages.
                        <br><br>
                        Ce livre est neuf, mais il est endommagé. Vous pouvez trouver la fiche produit du livre neuf <a target='_blank' href='" . $productUrl . "'>ici</a>.
                        <br><br>
                        <i>Les livres défectueux vendus dans le cadre de l’opération “Les Occaz de l’Entrepôt” ne peuvent être ni remboursé ni échangé.</i>
                        </p>";
            }
        }
        return '';
    }

    public function getStickers()
    {
        $sql = 'SELECT se.`id_sticker`, sel.`name`, se.`rate` FROM `' . _DB_PREFIX_ . 'egstickers` se
        LEFT JOIN `' . _DB_PREFIX_ . 'egstickers_lang` sel ON (sel.`id_sticker` = se.`id_sticker`)
        WHERE `id_lang` = ' . $this->context->language->id;

        return Db::getInstance()->executeS($sql);
    }
}


//Array (
//[0] => Array ( [id_feature] => 1 [id_product] => 4211 [id_feature_value] => 41 [custom] => 0 )
//[1] => Array ( [id_feature] => 8 [id_product] => 4211 [id_feature_value] => 258472 [custom] => )
//[2] => Array ( [id_feature] => 9 [id_product] => 4211 [id_feature_value] => 258473 [custom] => )
//[3] => Array ( [id_feature] => 16 [id_product] => 4211 [id_feature_value] => 34 [custom] => 0 )
//[4] => Array ( [id_feature] => 17 [id_product] => 4211 [id_feature_value] => 39 [custom] => 0 )
//[5] => Array ( [id_feature] => 19 [id_product] => 4211 [id_feature_value] => 231911 [custom] => 0 ) )
//
//Array (
//    [0] => Array ( [id_featuresforcombinations] => 6725 [id_product] => 4211 [id_product_attribute] => 3794 [id_feature] => 17 [id_feature_value] => 38 )
//    [1] => Array ( [id_featuresforcombinations] => 6724 [id_product] => 4211 [id_product_attribute] => 3794 [id_feature] => 15 [id_feature_value] => 69087 )
//    [2] => Array ( [id_featuresforcombinations] => 6726 [id_product] => 4211 [id_product_attribute] => 3794 [id_feature] => 19 [id_feature_value] => 231910 )
//    [3] => Array ( [id_featuresforcombinations] => 6721 [id_product] => 4211 [id_product_attribute] => 3794 [id_feature] => 11 [id_feature_value] => 262076 )
//    [4] => Array ( [id_featuresforcombinations] => 6722 [id_product] => 4211 [id_product_attribute] => 3794 [id_feature] => 10 [id_feature_value] => 262077 )
//    [5] => Array ( [id_featuresforcombinations] => 6723 [id_product] => 4211 [id_product_attribute] => 3794 [id_feature] => 14 [id_feature_value] => 262078 ) )