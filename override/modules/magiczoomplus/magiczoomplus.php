<?php
/**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class MagicZoomPlusOverride extends MagicZoomPlus
{
    public function parseTemplateStandard($output, $smarty)
    {
        if ($this->isSmarty3) {
            //Smarty v3 template engine
            $currentTemplate = Tools::substr(basename($smarty->template_resource), 0, -4);
            if ($currentTemplate == 'breadcrumb') {
                $currentTemplate = 'product';
            } elseif ($currentTemplate == 'pagination') {
                $currentTemplate = 'category';
            }
        } else {
            //Smarty v2 template engine
            $currentTemplate = $smarty->currentTemplate;
        }

        if ($this->isPrestaShop17x && ($currentTemplate == 'index' || $currentTemplate == 'page') ||
            $this->isPrestaShop15x && $currentTemplate == 'layout') {
            if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
                //NOTE: because we do not know whether the effect is applied to the blocks in the cache
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
            }

            //NOTE: full contents in prestashop 1.5.x
            if ($GLOBALS['magictoolbox']['magiczoomplus']['headers'] == false) {
                $output = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS START -->.*?<\!-- MAGICZOOMPLUS HEADERS END -->/is', '', $output);
            } else {
                $output = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS (START|END) -->/is', '', $output);
                //NOTE: add class for identifying PrestaShop version
                if (preg_match('#(<body\b[^>]*?\sclass\s*+=\s*+"[^"]*+)("[^>]*+>)#is', $output)) {
                    $output = preg_replace('#(<body\b[^>]*?\sclass\s*+=\s*+"[^"]*+)("[^>]*+>)#is', '$1 '.$this->psVersionClass.'$2', $output);
                } else {
                    $output = preg_replace('#(<body\s[^>]*+)>#is', '$1 class="'.$this->psVersionClass.'">', $output);
                }
            }

            return $output;
        }

        switch ($currentTemplate) {
            case 'search':
            case 'manufacturer':
                //$currentTemplate = 'manufacturer';
                break;
            case 'best-sales':
                $currentTemplate = 'bestsellerspage';
                break;
            case 'new-products':
                $currentTemplate = 'newproductpage';
                break;
            case 'prices-drop':
                $currentTemplate = 'specialspage';
                break;
            case 'blockbestsellers-home':
                $currentTemplate = 'blockbestsellers_home';
                break;
            case 'blockspecials-home':
                $currentTemplate = 'blockspecials_home';
                break;
            case 'product-list'://for 'Layered navigation block'
                if (strpos($_SERVER['REQUEST_URI'], 'blocklayered-ajax.php') !== false) {
                    $currentTemplate = 'category';
                }
                break;
            case 'javascript':
                if ($GLOBALS['magictoolbox']['magiczoomplus']['scripts']) {
                    $output .= $GLOBALS['magictoolbox']['magiczoomplus']['scripts'];
                }
                break;
            //NOTE: just in case (issue 88975)
            case 'ProductController':
                $currentTemplate = 'product';
                break;
            case 'products':
                if ($this->isPrestaShop17x && $this->isAjaxRequest) {
                    $page = $smarty->{$this->getTemplateVars}('page');
                    if (is_array($page) && isset($page['page_name'])) {
                        $currentTemplate = $page['page_name'];
                    }
                }
                break;
            case 'ps_featuredproducts':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'homefeatured';
                }
                break;
            case 'ps_bestsellers':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'blockbestsellers_home';
                }
                break;
            case 'ps_newproducts':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'blocknewproducts_home';
                }
                break;
            case 'ps_specials':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'blockspecials_home';
                }
                break;
        }

        $tool = $this->loadTool();
        if (!$tool->params->profileExists($currentTemplate) || $tool->params->checkValue('enable-effect', 'No', $currentTemplate)) {
            return $output;
        }
        $tool->params->setProfile($currentTemplate);

        //global $link;
        $link = $this->context->link;
        $cookie = &$GLOBALS['magictoolbox']['magiczoomplus']['cookie'];
        if (method_exists($link, 'getImageLink')) {
            $_link = &$link;
        } else {
            /* for Prestashop ver 1.1 */
            $_link = &$this;
        }

        $output = self::prepareOutput($output);

        switch ($currentTemplate) {
            case 'homefeatured':
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;

                $categoryID = $this->isPrestaShop15x ? Context::getContext()->shop->getCategory() : 1;
                $category = new Category($categoryID);
                $nb = (int)Configuration::get('HOME_FEATURED_NBR');//Number of product displayed
                $products = $category->getProducts((int)$cookie->id_lang, 1, ($nb ? $nb : 10));

                if (!is_array($products)) {
                    break;
                }
                foreach ($products as $product) {
                    $lrw = $product['link_rewrite'];
                    if (!$tool->params->checkValue('link-to-product-page', 'No')) {
                        $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                    } else {
                        $lnk = false;
                    }
                    $thumb = $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image'));
                    $image = $tool->getMainTemplate(array(
                        'id' => 'homefeatured'.$product['id_image'],
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                        'thumb' => $thumb,
                        'title' => $product['name'],
                        'group' => 'homefeatured',
                    ));
                    if (!$this->isPrestaShop17x) {
                        //NOTE: need a.product_image > img for blockcart module
                        $image = '<div class="MagicToolboxContainer">'.
                                    '<div style="width:0px;height:1px;overflow:hidden;visibility:hidden;">'.
                                        '<a class="product_image" href="#">'.
                                            '<img src="'.$thumb.'" />'.
                                        '</a>'.
                                    '</div>'.
                                    $image.
                                '</div>';
                    }
                    //$image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                    $image_pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], 'home'.$this->imageTypeSuffix), '/');
                    $image_pattern = str_replace('\-home'.$this->imageTypeSuffix, '\-[^"]*?', $image_pattern);
                    $image_pattern = '<img\b[^>]*?\bsrc\s*=\s*"[^"]*?'.$image_pattern.'"[^>]*>';
                    $pattern = $image_pattern.'[^<]*(<span[^>]*?class="new"[^>]*>[^<]*<\/span>)?';
                    $pattern = '<a[^>]*?href="[^"]*?"[^>]*>[^<]*'.$pattern.'[^<]*<\/a>|'.$image_pattern;
                    $output = preg_replace('/'.$pattern.'/is', $image, $output);
                }
                break;
            case 'category':
            case 'manufacturer':
            case 'newproductpage':
            case 'bestsellerspage':
            case 'specialspage':
            case 'search':
                //global $p, $n, $orderBy, $orderWay;
                //$category = new Category((int)Tools::getValue('id_category'), (int)$cookie->id_lang);
                //$products = $category->getProducts((int)$cookie->id_lang, (int)$p, (int)$n, $orderBy, $orderWay);
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                $products = $smarty->{$this->getTemplateVars}('products');

                if (!$products && $this->isPrestaShop17x) {
                    $listing = $smarty->{$this->getTemplateVars}('listing');
                    $products = $listing['products'];
                }

                if (!is_array($products)) {
                    break;
                }
                if ($this->isPrestaShop17x) {
                    //NOTE: to prevent replacing sidebar contents
                    $splitter =	'(<section\b[^>]*?\bid\s*+=\s*+"products"[^>]*+>)'.
                                '('.
                                '(?:'.
                                    '[^<]++'.
                                    '|'.
                                    '<(?!/?section\b|!--)'.
                                    '|'.
                                    '<!--.*?-->'.
                                    '|'.
                                    '<section\b[^>]*+>'.
                                        '(?2)'.
                                    '</section\s*+>'.
                                ')*+'.
                                ')'.
                                '(</section\s*+>)';
                    $parts = preg_split('#'.$splitter.'#i', $output, -1, PREG_SPLIT_DELIM_CAPTURE);
                    if (isset($parts[2])) {
                        $output = $parts[2];
                    }
                }
                foreach ($products as $product) {
                    $lrw = $product['link_rewrite'];
                    if (!$tool->params->checkValue('link-to-product-page', 'No')) {
                        $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                    } else {
                        $lnk = false;
                    }
                    $thumb = $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image'));
                    $html = $tool->getMainTemplate(array(
                        'id' => 'category'.$product['id_image'],
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                        'thumb' => $thumb,
                        'title' => $product['name'],
                        'group' => 'category',
                    ));
                    if (!$this->isPrestaShop16x) {
                        $html = '<div class="MagicToolboxContainer">'.
                                //NOTE: need a.product_img_link > img for blockcart module
                                '<div style="width:0px;height:1px;overflow:hidden;visibility:hidden;"><a class="product_img_link" href="#"><img src="'.$thumb.'" /></a></div>'.
                                $html.
                                '</div>';
                    }

                    $image_pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], 'home'.$this->imageTypeSuffix), '/');
                    $image_pattern = str_replace('\-home'.$this->imageTypeSuffix, '\-[^"]*?', $image_pattern);
                    $image_pattern = '<img[^>]*?src\s*=\s*"[^"]*?'.$image_pattern.'"[^>]*>';
                    if ($this->isPrestaShop17x) {
                        $pattern = $image_pattern;
                    } else {
                        $pattern = $image_pattern.'[^<]*(<span[^>]*?class="new"[^>]*>[^<]*<\/span>)?';
                    }
                    $pattern = '<a[^>]*?href="[^"]*?"[^>]*>[^<]*'.$pattern.'[^<]*<\/a>|'.$image_pattern;
                    //$matches = array();
                    //preg_match_all('/'.$pattern.'/is', $output, $matches, PREG_SET_ORDER);

                    if (!$this->isPrestaShop16x) {
                        //NOTE: for span.new banners
                        if (preg_match('/'.$pattern.'/is', $output, $matches)) {
                            if (isset($matches[1])) {
                                $html = preg_replace('/<\/div>$/is', $matches[1].'</div>', $html);
                            }
                        }
                    }
                    $output = preg_replace('/'.$pattern.'/is', $html, $output);
                }

                if ($this->isAjaxRequest) {
                    $output .= '
                        <script type="text/javascript">
                            //<![CDATA[
                            $(\'#products .MagicZoom\').each(function(i, el) {
                                MagicZoom.refresh(el);
                            });
                            //]]>
                        </script>';
                }

                if ($this->isPrestaShop17x && isset($parts[2])) {
                    $parts[2] = $output;
                    $output = implode('', $parts);
                }

                break;
            case 'product':
                //debug_log('MagicZoomPlus parseTemplateStandard product');

                $product_id = isset($GLOBALS['magictoolbox']['magicthumb']['product']['id'])?$GLOBALS['magictoolbox']['magicthumb']['product']['id']:(int)Tools::getValue('id_product');

                //$product = new Product((int)$smarty->$tpl_vars['product']->id, true, (int)$cookie->id_lang);
                //get some data from $GLOBALS for compatible with Prestashop modules which reset the $product smarty variable

                //$product = new Product((int)$smarty->$tpl_vars['product']->id, true, (int)$cookie->id_lang);
                //get some data from $GLOBALS for compatible with Prestashop modules which reset the $product smarty variable
                $product = new Product((int)$product_id, true, (int)$cookie->id_lang);

                $lrw = $product->link_rewrite;
                $pid = (int)$product->id;
                $meta_description  = $product->meta_description;
                 
                $productImages = $product->getImages((int)$cookie->id_lang);
                //NOTE: not all product images
                //$productImages = $smarty->{$this->getTemplateVars}('product')['images'];
                if (!is_array($productImages)) {
                    $productImages = array();
                }

                $productVideos = $this->loadProductVideoData($pid);

                if (empty($productImages) && empty($productVideos)) {
                    break;
                }

                $sProductData = $smarty->{$this->getTemplateVars}('product');
                if ($this->isPrestaShop17x) {
                    //NOTE: $cover variable contains the data of the current cover image
                    //      which depends on the selected combination
                    //      $cover['cover'] flag indicates that this is the product cover image
                    $cover = isset($sProductData['cover']) ? $sProductData['cover'] : array();
                } else {
                    $cover = $smarty->{$this->getTemplateVars}('cover');
                }

                if (!isset($cover['id_image'])) {
                    break;
                }


                $coverImageIds = is_numeric($cover['id_image']) ? $pid.'-'.$cover['id_image'] : $cover['id_image']; // Product::getCover($product_id)['id_image'];

                //NOTE: to use magic360 module with magiczoomplus
                $used360 = false;
                if (isset($GLOBALS['magictoolbox']['magic360'])) {
                    $images = Db::getInstance()->ExecuteS('SELECT id_image FROM `'._DB_PREFIX_.'magic360_images` WHERE id_product='.$pid.' LIMIT 1');
                    if (count($images) && !$GLOBALS['magictoolbox']['magic360']['class']->params->checkValue('enable-effect', 'No', 'product')) {
                        $used360 = true;
                        $GLOBALS['magictoolbox']['standardTool'] = 'magiczoomplus';
                        $GLOBALS['magictoolbox']['selectorImageType'] = $tool->params->getValue('selector-image');
                    }
                }

                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;

                $defaultContainerId = 'zoom';
                $containersData = array(
                    'zoom' => '',
                    '360' => '',
                    //'video' => '',
                );
                $html = '';
                $m360AsPrimaryImage = $tool->params->checkValue('360-as-primary-image', 'Yes');

                $containersData['zoom'] = $tool->getMainTemplate(array(
                    'id' => 'MainImage',
                    'img' => $_link->getImageLink($lrw, $coverImageIds, $tool->params->getValue('large-image')),
                    'thumb' => $_link->getImageLink($lrw, $coverImageIds, $tool->params->getValue('thumb-image')),
                    'title' => $product->name,
                    'alt' => $meta_description,
                ));

                $selectors = array();
                $selectorIDs = array();
                $originalLayout = $tool->params->checkValue('template', 'original');
                $coverId = '';
                foreach ($productImages as $i) {

                    //NOTE: to prevent dublicates
                    if (isset($selectorIDs[$i['id_image']])) {
                        continue;
                    }

                    $aHtml = $tool->getSelectorTemplate(array(
                        'id' => 'MainImage',
                        'img' => $_link->getImageLink($lrw, $pid.'-'.$i['id_image'], $tool->params->getValue('large-image')),
                        'medium' => $_link->getImageLink($lrw, $pid.'-'.$i['id_image'], $tool->params->getValue('thumb-image')),
                        'thumb' => $_link->getImageLink($lrw, $pid.'-'.$i['id_image'], $tool->params->getValue('selector-image')),
                        'title' => $i['legend'],
                        'alt' => $meta_description
                    ));

                    $selectorIDs[$i['id_image']] = $i['id_image'];

                    $aHtml = str_replace('<a ', '<a data-magic-slide-id="zoom" ', $aHtml);
                    $selectorClass = 'magictoolbox-selector';

                    if ($this->isPrestaShop17x) {
                        if ($originalLayout) {
                            $selectorClass .= ' thumb';
                        }
                    }

                    if (!$m360AsPrimaryImage) {
                        if ($i['id_image'] == $cover['id_image']) {
                            $selectorClass .= ' active-selector';
                            if ($originalLayout) {
                                if ($this->isPrestaShop17x) {
                                    $selectorClass .= ' selected';
                                } else {
                                    $selectorClass .= ' shown';
                                }
                            }
                        }
                    }

                    if ($used360) {
                        $selectorClass .= ' zoom-with-360';
                    }
                    //NOTE: onclick for prevent click on selector before it is initialized
                    $aHtml = str_replace('<a ', '<a class="'.$selectorClass.'" data-mt-selector-id="'.$i['id_image'].'" onclick="return false;" ', $aHtml);

                    if ($originalLayout && !$this->isPrestaShop17x) {
                        $aHtml = str_replace('<img ', '<img id="thumb_'.$i['id_image'].'" ', $aHtml);
                        $pattern = preg_quote($_link->getImageLink($lrw, $pid.'-'.$i['id_image'], 'medium'.$this->imageTypeSuffix), '#');
                        $pattern = '<img\b[^>]*?\bsrc="[^"]*?'.$pattern.'"[^>]*+>';
                        $pattern = '(?:<img\b[^>]*?\bid="thumb_'.$i['id_image'].'"[^>]*+>|'.$pattern.')';
                        $pattern = '<a\b[^>]*+>[^<]*+'.$pattern.'[^<]*+</a>|'.$pattern;
                        //NOTE: append selector in their preserved place
                        $output = preg_replace('#'.$pattern.'#is', $aHtml, $output, 1);
                    } else {
                        $selectors[$i['id_image']] = $aHtml;
                    }

                    if ($i['cover']) {
                        $coverId = $i['id_image'];
                    }
                }

                if ($this->isPrestaShop17x) {
                    $attributeId = $smarty->{$this->getTemplateVars}('product');
                    $attributeId = isset($attributeId['id_product_attribute']) ? $attributeId['id_product_attribute'] : null;
                    //$combinations = $smarty->{$this->getTemplateVars}('combinations');
                    $combinationImages = $smarty->{$this->getTemplateVars}('combinationImages');
                    $combinationData = array(
                        'selectors' => $selectors,
                        'attributes' => array(),
                        'toolId' => 'MagicZoomPlus',
                        'toolClass' => 'MagicZoom',
                        'm360Selector' => '',
                        'videoSelectors' => array(),
                        'coverId' => $coverId,
                    );
                    if (!empty($combinationImages)) {
                        $selectors = array();
                        if (is_array($combinationImages)) {
                            foreach ($combinationImages as $attrId => $combImages) {
                                $combinationData['attributes'][$attrId] = array();
                                foreach ($combImages as $combImage) {
                                    $combinationData['attributes'][$attrId][] = $combImage['id_image'];
                                    if ($attributeId == $attrId) {
                                        if (empty($mainImageOveridden)) {
                                            $containersData['zoom'] = $tool->getMainTemplate(array(
                                                'id' => 'MainImage',
                                                'img' => $_link->getImageLink($lrw, $combImage['id_image'], $tool->params->getValue('large-image')),
                                                'thumb' => $_link->getImageLink($lrw, $combImage['id_image'], $tool->params->getValue('thumb-image')),
                                                'title' => $product->name,
                                                'alt' => $meta_description,
                                            ));
                                            $mainImageOveridden = true;
                                            $selectors = array();
                                        }
                                        $selectors[$combImage['id_image']] = $combinationData['selectors'][$combImage['id_image']];
                                    }
                                }
                            }
                        }
                    }
                }

                //NOTE: product videos
                $videoSelectors = array();
                $combinationScript = '';
                $videoIndex = 1;
                //NOTE: need this sizes for video selectors
                $this->setImageSizes();
                $sMaxHeight = $tool->params->getValue('selector-max-height', 'product');
                $sMaxHeight = is_numeric($sMaxHeight) ? $sMaxHeight.'px' : 'auto';
                $sMaxWidth = $tool->params->getValue('selector-max-width', 'product');
                $sMaxWidth = is_numeric($sMaxWidth) ? $sMaxWidth.'px' : 'auto';
                //NOTE: style for video thumbnails
                //      in order to display them with the same size as the product thumbnails
                //NOTICE: cannot be used with the original template because the picture size may become larger than the size of the <li>
                $html .= '<style>
div.MagicToolboxSelectorsContainer .selector-max-height {
    max-height: '.$sMaxHeight.' !important;
    max-width: '.$sMaxWidth.' !important;
}
</style>';
                $videoSelectorClass = 'video-selector magictoolbox-selector';
                if ($this->isPrestaShop17x && $originalLayout) {
                    $videoSelectorClass .= ' thumb';
                }

                foreach ($productVideos as $videoUrl => $videoData) {
                    if (empty($videoData)) {
                        continue;
                    }
                    if ($videoData['youtube']) {
                        $dataVideoType = 'youtube';
                        $containersData['video-'.$videoIndex] = '<iframe src="https://www.youtube.com/embed/'.$videoData['code'].'?enablejsapi=1"';
                    } else {
                        $dataVideoType = 'vimeo';
                        $containersData['video-'.$videoIndex] = '<iframe src="https://player.vimeo.com/video/'.$videoData['code'].'?byline=0&portrait=0"';
                    }
                    $containersData['video-'.$videoIndex] .=
                        ' frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen data-video-type="'.$dataVideoType.'"></iframe>';

                    $vsId = 9999999000+$videoIndex;

                    $videoSelector =
                        '<a data-mt-selector-id="'.$vsId.'" data-magic-slide-id="video-'.$videoIndex.'" data-video-type="'.$dataVideoType.'" class="'.$videoSelectorClass.'" href="#" onclick="return false">'.
                        '<span><b></b></span>'.
                        '<img class="selector-max-height" src="'.$videoData['thumb'].'" alt="video"/>'.
                        '</a>';

                    if (!$originalLayout || $this->isPrestaShop17x) {
                        $selectors[] = $videoSelector;
                    }

                    if (!$this->isPrestaShop17x) {
                        $videoSelectors[$vsId] = '<li id="thumbnail_'.$vsId.'">'.
                            str_replace('<img ', '<img id="thumb_'.$vsId.'" ', $videoSelector).
                            '</li>';
                        $combinationScript .= 'combinationImages[combId][combinationImages[combId].length] = '.$vsId.';';
                    }

                    if ($this->isPrestaShop17x) {
                        $combinationData['videoSelectors'][] = $videoSelector;
                    }

                    $videoIndex++;
                }

                if (!$this->isPrestaShop17x) {
                    if (!empty($combinationScript)) {
                        $combinationScript = '
<script type="text/javascript">
    //NOTE: to display video thumbnails
    var videoThumbIDs = ['.implode(',', array_keys($videoSelectors)).'];
    if (typeof(combinationImages) != "undefined") {
        for (var combId in combinationImages) {
            '.$combinationScript.'
        }
    }
</script>';
                    }
                    if ($originalLayout) {
                        $thumbsListPattern =   '(<ul\b[^>]*?\bid\s*+=\s*+"thumbs_list_frame"[^>]*+>)'.
                                                '('.
                                                '(?:'.
                                                    '[^<]++'.
                                                    '|'.
                                                    '<(?!/?ul\b|!--)'.
                                                    '|'.
                                                    '<!--.*?-->'.
                                                    '|'.
                                                    '<ul\b[^>]*+>'.
                                                        '(?2)'.
                                                    '</ul\s*+>'.
                                                ')*+'.
                                                ')'.
                                                '</ul\s*+>';
                        // $matches = array();
                        // preg_match_all('#'.$thumbsListPattern.'#is', $output, $matches, PREG_SET_ORDER);
                        // debug_log($matches);
                        $output = preg_replace(
                            '#'.$thumbsListPattern.'#is',
                            '$1$2'.implode('', $videoSelectors).'</ul>'.$combinationScript,
                            $output
                        );
                    } else {
                        $html .= $combinationScript;
                    }
                }

                //NOTE: to use magic360 module with magiczoomplus
                if ($used360) {
                    $containersData['360'] = '<!-- MAGIC360 -->';
                    $defaultContainerId = $m360AsPrimaryImage ? '360' : 'zoom';
                    if ($originalLayout && !$this->isPrestaShop17x) {
                        $output = preg_replace(
                            '/(<ul\b[^>]*?id="thumbs_list_frame"[^>]*>)/is',
                            '$1<li id="thumbnail_9999999999"><!-- MAGIC360SELECTOR --></li>',
                            $output
                        );
                    } else {
                        array_unshift($selectors, '<!-- MAGIC360SELECTOR -->');
                        if ($this->isPrestaShop17x) {
                            $combinationData['m360Selector'] = '<!-- MAGIC360SELECTOR_ESCAPED -->';
                        }
                    }
                }

                $templateParamValue = '';
                if (!$this->isPrestaShop17x) {
                    if ($originalLayout) {
                        $templateParamValue = $tool->params->getValue('template');
                        $tool->params->setValue('template', 'bottom');
                        //NOTE: make views_block visible (it is hidden when product has only one image) when magic360 icon is added
                        if ($GLOBALS['magictoolbox']['standardTool'] && count($productImages) == 1) {
                            $output = preg_replace('/(<div\s[^>]*?id="views_block"[^>]*?class="[^"]*?)hidden([^"]*"[^>]*>)/is', '$1$2', $output);
                            //NOTE: pattern breaks down a bit without this p.clear
                            $output = preg_replace('/(<ul\b[^>]*?id="usefull_link_block"[^>]*>)/is', '<p class="clear"></p>$1', $output);
                        }
                    } else {
                        //NOTE: hide selectors from contents

                        //NOTE: 'image-additional' added to support custom theme #53897
                        //NOTE: div#views_block is parent for div#thumbs_list
                        $thumbsPattern =	'(<div\b[^>]*?(?:\bid\s*+=\s*+"(?:views_block|thumbs_list)"|\bclass\s*+=\s*+"[^"]*?\bimage-additional\b[^"]*+")[^>]*+>)'.
                                            '('.
                                            '(?:'.
                                                '[^<]++'.
                                                '|'.
                                                '<(?!/?div\b|!--)'.
                                                '|'.
                                                '<!--.*?-->'.
                                                '|'.
                                                '<div\b[^>]*+>'.
                                                    '(?2)'.
                                                '</div\s*+>'.
                                            ')*+'.
                                            ')'.
                                            '</div\s*+>';

                        $matches = array();
                        if (preg_match("#{$thumbsPattern}#is", $output, $matches)) {
                            if (strpos($matches[1], 'class')) {
                                $replace = preg_replace('#\bclass\s*+=\s*+"#i', '$0hidden-important ', $matches[1]);
                            } else {
                                $replace = preg_replace('#<div\b#i', '$0 class="hidden-important"', $matches[1]);
                            }
                            $output = str_replace($matches[1], $replace, $output);
                        }

                        //NOTE: remove "View full size" link in old PrestaShop
                        $output = preg_replace('/<li[^>]*+>[^<]*+<span[^>]*?id="view_full_size"[^>]*+>[^<]*<\/span>[^<]*+<\/li>/is', '', $output);

                        //NOTE: hide span#wrapResetImages
                        $matches = array();
                        if (preg_match('#(?:<span\b[^>]*?\bid\s*+=\s*+"wrapResetImages"[^>]*+>|<a\b[^>]*?\bid\s*+=\s*+"resetImages"[^>]*+>)#is', $output, $matches)) {
                            if (strpos($matches[0], 'class')) {
                                $replace = preg_replace('#\bclass\s*+=\s*+"#i', '$0hidden-important ', $matches[0]);
                            } else {
                                $replace = preg_replace('#<span\b#i', '$0 class="hidden-important"', $matches[0]);
                            }

                            $output = str_replace($matches[0], $replace, $output);
                        }

                    }
                }

                //NOTE: we need this sizes for template renderer
                $this->setImageSizes();

                foreach ($containersData as $containerId => $containerHTML) {
                    $activeClass = $defaultContainerId == $containerId ? ' mt-active' : '';
                    $html .= "<div class=\"magic-slide{$activeClass}\" data-magic-slide=\"{$containerId}\">{$containerHTML}</div>";
                }

                require_once(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'magiczoomplus'.DIRECTORY_SEPARATOR.'magictoolbox.templatehelper.class.php');
              
                MagicToolboxTemplateHelperClass::setPath(dirname(__FILE__).DIRECTORY_SEPARATOR.'templates');
                MagicToolboxTemplateHelperClass::setOptions($tool->params);
                $scrollTool = null;
                $scrollOptions = '';
                if (isset($GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'])) {
                    $scrollTool = &$GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'];
                }
                $scrollOptions = $scrollTool ? $scrollTool->params->serialize(false, '', 'product-magicscroll-options') : '';
                $selectors = $this->injectVideoAtMiddlePosition($selectors); // new function
                $html = MagicToolboxTemplateHelperClass::render(array(
                    'main' => $html,
                    'thumbs' => $selectors,
                    'magicscrollOptions' => $scrollOptions,
                    'pid' => $pid,
                ));
                if ($templateParamValue) {
                    //NOTE: in some cases, the wrong template is processed first
                    //      so we need to restore the old option value for the next time
                    $tool->params->setValue('template', $templateParamValue);
                }

                if (!$this->isPrestaShop17x && !$originalLayout) {
                    //NOTE: disable MagicScroll on page load (to start manually)
                    if ($tool->params->checkValue('magicscroll', 'Yes')) {
                        $matches = array();
                        if (preg_match('#<div\b[^>]*?\bclass\s*+=\s*+"[^"]*?\bMagicScroll\b[^"]*+"[^>]*+>#is', $html, $matches)) {
                            $replace = preg_replace('#(class="[^"]*?\bMagicScroll\b)([^"]*+")#i', '$1 hidden-important$2', $matches[0]);
                            if (preg_match('#\sdata-options(\s|=)#is', $replace)) {
                                $replace = preg_replace('#(\sdata-options\s*+=\s*+"[^"]*+)"#is', '$1autostart:false;"', $replace);
                            } else {
                                $replace = preg_replace('#>$#', ' data-options="autostart:false;">', $replace);
                            }
                            $html = str_replace($matches[0], $replace, $html);
                        }
                    }

                    //NOTE: for combinations and magicscroll=yes
                    $html .= '
<div id="MagicToolboxHiddenSelectors" class="hidden-important"></div>
<script type="text/javascript">
    //<![CDATA[
    magictoolboxImagesOrder = ['.implode(',', $selectorIDs).'];
    mtProductCoverImageId = '.$coverId.';
    //]]>
</script>
';
                }

                //NOTE: append main container
                if ($this->isPrestaShop17x) {
                    $html .= '
<script type="text/javascript">
    //<![CDATA[
    var mtCombinationData = '.json_encode($combinationData).';
    var mtScrollEnabled = '.($tool->params->checkValue('magicscroll', 'Yes', 'product') ? 'true' : 'false').';
    var mtScrollOptions = \''.$scrollOptions.'\';
    var mtScrollItems = \''.$tool->params->getValue('items', 'product').'\';
    var mtLayout = \''.$tool->params->getValue('template', 'product').'\';
    //]]>
</script>
';
                    $mainImagePattern = '<div\b[^>]*?\bclass\s*+=\s*+"[^"]*?\bimages-container\b[^"]*+"[^>]*+>'.
                                        '('.
                                        '(?:'.
                                            '[^<]++'.
                                            '|'.
                                            '<(?!/?div\b|!--)'.
                                            '|'.
                                            '<!--.*?-->'.
                                            '|'.
                                            '<div\b[^>]*+>'.
                                                '(?1)'.
                                            '</div\s*+>'.
                                        ')*+'.
                                        ')'.
                                        '</div\s*+>';
                    $matches = array();
                    //preg_match_all('#'.$mainImagePattern.'#is', $output, $matches, PREG_SET_ORDER);
                    //debug_log($matches);

                    if (!preg_match('#'.$mainImagePattern.'#is', $output, $matches)) {
                        break;
                    }

                    //NOTE: for proper show/hide arrows in original template
                    $replace = str_replace('js-qv-product-images', 'js-qv-product-images-disabled', $matches[0]);

                    //NOTE: div.hidden-important can be replaced with ajax contents
                    $output = str_replace(
                        $matches[0],
                        '<div class="hidden-important">'.$replace.'</div>'.$html,
                        $output
                    );

                    if (!$originalLayout) {
                        //NOTE: cut arrows
                        $arrowsPattern = '<div\b[^>]*?\bclass\s*+=\s*+"[^"]*?\bscroll-box-arrows\b[^"]*+"[^>]*+>'.
                                            '('.
                                            '(?:'.
                                                '[^<]++'.
                                                '|'.
                                                '<(?!/?div\b|!--)'.
                                                '|'.
                                                '<!--.*?-->'.
                                                '|'.
                                                '<div\b[^>]*+>'.
                                                    '(?1)'.
                                                '</div\s*+>'.
                                            ')*+'.
                                            ')'.
                                            '</div\s*+>';
                        $output = preg_replace('#'.$arrowsPattern.'#', '', $output);
                    }

                    $output = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS (START|END) -->/is', '', $output);
                } else {
                    //NOTE: 'image' class added to support custom theme #53897
                    $mainImagePattern = '(<div\b[^>]*?(?:\bid\s*+=\s*+"image-block"|\bclass\s*+=\s*+"[^"]*?\bimage\b[^"]*+")[^>]*+>)'.
                                        '('.
                                        '(?:'.
                                            '[^<]++'.
                                            '|'.
                                            '<(?!/?div\b|!--)'.
                                            '|'.
                                            '<!--.*?-->'.
                                            '|'.
                                            '<div\b[^>]*+>'.
                                                '(?2)'.
                                            '</div\s*+>'.
                                        ')*+'.
                                        ')'.
                                        '</div\s*+>';
                    $matches = array();
                    //preg_match_all('#'.$mainImagePattern.'#is', $output, $matches, PREG_SET_ORDER);

                    if (!preg_match('%'.$mainImagePattern.'%is', $output, $matches)) {
                        break;
                    }

                    $iconsPattern = '<span\b[^>]*?\bclass\s*+=\s*+"[^"]*?\b(?:new-box|sale-box|discount)\b[^"]*+"[^>]*+>'.
                                    '('.
                                    '(?:'.
                                        '[^<]++'.
                                        '|'.
                                        '<(?!/?span\b|!--)'.
                                        '|'.
                                        '<!--.*?-->'.
                                        '|'.
                                        '<span\b[^>]*+>'.
                                            '(?1)'.
                                        '</span\s*+>'.
                                    ')*+'.
                                    ')'.
                                    '</span\s*+>';
                    $iconMatches = array();
                    if (preg_match_all('%'.$iconsPattern.'%is', $matches[2], $iconMatches, PREG_SET_ORDER)) {
                        foreach ($iconMatches as $key => $iconMatch) {
                            $matches[2] = str_replace($iconMatch[0], '', $matches[2]);
                            $iconMatches[$key] = $iconMatch[0];
                        }
                    }
                    $icons = implode('', $iconMatches);

                    $output = str_replace($matches[0], "{$matches[1]}{$icons}<div class=\"hidden-important\">{$matches[2]}</div>{$html}</div>", $output);
                }

                $GLOBALS['magictoolbox']['isProductBlockProcessed'] = true;
                break;
            case 'blockspecials':
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                $product = $smarty->{$this->getTemplateVars}('special');
                if (!is_array($product)) {
                    break;
                }
                $lrw = $product['link_rewrite'];
                if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $product['id_product']))) {
                    $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                } else {
                    $lnk = false;
                }

                $image = $tool->getMainTemplate(array(
                    'id' => 'blockspecials'.$product['id_image'],
                    'link' => $lnk,
                    'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                    'thumb' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image')),
                    'title' => $product['name'],
                    'group' => 'blockspecials',
                ));
                $image = '<div class="MagicToolboxContainer">'.$image.'</div>';

                $type = ($this->isPrestaShop16x ? 'small': 'medium').$this->imageTypeSuffix;
                $pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], $type), '/');
                $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                $pattern = '<img[^>]*?src="[^"]*?'.$pattern.'"[^>]*>';
                $pattern = '(<a[^>]*?href="[^"]*?"[^>]*>[^<]*)?'.$pattern.'([^<]*<\/a>)?';
                $output = preg_replace('/'.$pattern.'/is', $image, $output);

                break;
            case 'blockspecials_home':
                if ($this->isPrestaShop17x) {
                    $products = $smarty->{$this->getTemplateVars}('products');
                } else {
                    $products = $smarty->{$this->getTemplateVars}('specials');
                }
                if (!is_array($products)) {
                    break;
                }
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                foreach ($products as $product) {
                    $lrw = $product['link_rewrite'];
                    if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $product['id_product']))) {
                        $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                    } else {
                        $lnk = false;
                    }

                    $image = $tool->getMainTemplate(array(
                        'id' => 'blockspecials'.$product['id_image'],
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                        'thumb' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image')),
                        'title' => $product['name'],
                        'group' => 'blockspecials_home',
                    ));
                    if (!$this->isPrestaShop17x) {
                        $image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                    }

                    $type = 'home'.$this->imageTypeSuffix;
                    $pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], $type), '#');
                    $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                    $pattern = '<img[^>]*?src\s*+=\s*+"[^"]*?'.$pattern.'"[^>]*+>';
                    $pattern = '(<a\b[^>]*?href="[^"]*+"[^>]*+>[^<]*+)?'.$pattern.'([^<]*+</a>)?';
                    $output = preg_replace('#'.$pattern.'#is', $image, $output);
                }
                break;
            case 'blockviewed':
                $productIDs = $GLOBALS['magictoolbox']['magiczoomplus']['productsViewedIds'];
                if ($this->isPrestaShop155x) {
                    $productIDs = array_reverse($productIDs);
                }

                $productIDs = array_slice($productIDs, 0, Configuration::get('PRODUCTS_VIEWED_NBR'));
                foreach ($productIDs as $id_product) {
                    $productViewedObj = new Product((int)$id_product, false, (int)$cookie->id_lang);
                    if (!Validate::isLoadedObject($productViewedObj) || !$productViewedObj->active) {
                        continue;
                    }

                    $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                    $images = $productViewedObj->getImages((int)$cookie->id_lang);
                    foreach ($images as $image) {
                        if ($image['cover']) {
                            $productViewedObj->cover = $productViewedObj->id.'-'.$image['id_image'];
                            $productViewedObj->legend = $image['legend'];
                            break;
                        }
                    }
                    if (!isset($productViewedObj->cover)) {
                        $productViewedObj->cover = Language::getIsoById($cookie->id_lang).'-default';
                        $productViewedObj->legend = '';
                    }
                    $lrw = $productViewedObj->link_rewrite;
                    if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $id_product))) {
                        $lnk = $link->getProductLink($id_product, $lrw, $productViewedObj->category);
                    } else {
                        $lnk = false;
                    }

                    $image = $tool->getMainTemplate(array(
                        'id' => 'blockviewed'.$id_product,
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $productViewedObj->cover, $tool->params->getValue('large-image')),
                        'thumb' => $_link->getImageLink($lrw, $productViewedObj->cover, $tool->params->getValue('thumb-image')),
                        'title' => $productViewedObj->name,
                        'group' => 'blockviewed',
                    ));
                    $image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                    $type = ($this->isPrestaShop16x ? 'small': 'medium').$this->imageTypeSuffix;
                    $pattern = preg_quote($_link->getImageLink($lrw, $productViewedObj->cover, $type), '/');
                    $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                    $pattern = '<img[^>]*?src="[^"]*?'.$pattern.'"[^>]*>';
                    $pattern = '(<a[^>]*?href="[^"]*?"[^>]*>[^<]*)?'.$pattern.'([^<]*<\/a>)?';
                    $output = preg_replace('/'.$pattern.'/is', $image, $output);
                }
                break;
            case 'blockbestsellers':
            case 'blockbestsellers_home':
            case 'blocknewproducts':
            case 'blocknewproducts_home':
                if (in_array($currentTemplate, array('blockbestsellers', 'blockbestsellers_home'))) {
                    //$products = $smarty->{$this->getTemplateVars}('best_sellers');
                    //to get with description etc.
                    //$products = ProductSale::getBestSales((int)$cookie->id_lang, 0, version_compare(_PS_VERSION_, '1.5.1.0', '>=') ? 5 : 4);
                    //NOTE: blockbestsellers module uses a 'getBestSalesLight' function (the result may be different from 'getBestSales')
                    //      description we get a little further (with 'getProductDescription' function)
                    $pCount = $this->isPrestaShop16x ? 8 : (version_compare(_PS_VERSION_, '1.5.1.0', '>=') ? 5 : 4);
                    $products = ProductSale::getBestSalesLight((int)$cookie->id_lang, 0, $pCount);
                } else {
                    if ($this->isPrestaShop17x) {
                        $products = $smarty->{$this->getTemplateVars}('products');
                    } else {
                        $products = $smarty->{$this->getTemplateVars}('new_products');
                    }
                }

                if (!is_array($products)) {
                    break;
                }
                $pCount = count($products);
                if ($pCount) {
                    $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                    for ($i = 0; /*$i < 2 &&*/ $i < $pCount; $i++) {
                        $lrw = $products[$i]['link_rewrite'];
                        if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $products[$i]['id_product']))) {
                            $lnk = $link->getProductLink($products[$i]['id_product'], $lrw, isset($products[$i]['category']) ? $products[$i]['category'] : null);
                        } else {
                            $lnk = false;
                        }

                        $image = $tool->getMainTemplate(array(
                            'id' => $currentTemplate.$products[$i]['id_image'],
                            'link' => $lnk,
                            'img' => $_link->getImageLink($lrw, $products[$i]['id_image'], $tool->params->getValue('large-image')),
                            'thumb' => $_link->getImageLink($lrw, $products[$i]['id_image'], $tool->params->getValue('thumb-image')),
                            'title' => $products[$i]['name'],
                            'group' => $currentTemplate,
                        ));
                        if (!$this->isPrestaShop17x) {
                            $image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                        }
                        if (in_array($currentTemplate, array('blockbestsellers_home', 'blocknewproducts_home'))) {
                            $type = 'home'.$this->imageTypeSuffix;
                        } elseif ($this->isPrestaShop15x && $currentTemplate == 'blockbestsellers' || $this->isPrestaShop16x) {
                            $type = 'small'.$this->imageTypeSuffix;
                        } else {
                            $type = 'medium'.$this->imageTypeSuffix;
                        }

                        $pattern = preg_quote($_link->getImageLink($lrw, $products[$i]['id_image'], $type), '#');
                        $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                        $pattern = '<img\b[^>]*?src\s*+=\s*+"[^"]*?'.$pattern.'"[^>]*+>';
                        $pattern = '(?:<a\b[^>]*+>[^<]*+)?'.
                                        '(?:<span class="number">.*?</span>[^<]*+)?'.
                                        $pattern.
                                    '(?:[^<]*+</a>)?';
                        $output = preg_replace('#'.$pattern.'#is', $image, $output);
                    }
                }
                break;
        }

        return self::prepareOutput($output);

    }
    /**
     * Move the video element (YouTube/Vimeo) into 3 positions: start, middle, and end.
     *
     * @param array $selectors Original list of selector HTML strings.
     * @return array Modified array with video inserted in 3 positions.
    */
    public function injectVideoAtMiddlePosition($selectors) {
        $videoHtml = '';
        $videoKey = null;

        // Step 1: Find the video and remember its key
        foreach ($selectors as $key => $value) {
            if (strpos($value, 'data-video-type="youtube"') !== false || strpos($value, 'data-video-type="vimeo"') !== false) {
                $videoHtml = $value;
                $videoKey = $key;
                unset($selectors[$key]);
                break; // only one video expected
            }
        }

        // Step 2: If no video found, return the original
        if (empty($videoHtml) || $videoKey === null) {
            return $selectors;
        }

        // Step 3: Rebuild array and insert video in the middle
        $newSelectors = [];
        $count = count($selectors);
        $middle = (int) floor($count / 2);
        $i = 0;

        foreach ($selectors as $key => $value) {
            if ($i === $middle) {
                // Insert video using its original key
                $newSelectors[$videoKey] = $videoHtml;
            }
            $newSelectors[$key] = $value;
            $i++;
        }

        return $newSelectors;
    }



}