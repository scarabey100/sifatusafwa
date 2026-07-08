<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CpPosts
{
    // Stores the last query results
    public $post;
    public $posts;
    public $args;

    /**
     * Returns posts that matches the query params
     *
     * @param array $args Array of CP_Query attributes
     *
     * @return bool Success of the query
     */
    public static function find($args = [])
    {
        // Crate new instance
        $instance = new self();
        $instance->args = $args;
        if ($instance->posts = cp_get_posts($args)) {
            $instance->post = $instance->posts[0];
        }

        return $instance;
    }

    public static function getPostTypes()
    {
        // Get post types
        $postTypes = cp_get_post_types();

        // Remove some defalt post types
        if (isset($postTypes['revision'])) {
            unset($postTypes['revision']);
        }
        if (isset($postTypes['nav_menu_item'])) {
            unset($postTypes['nav_menu_item']);
        }

        // Convert names to plural
        foreach ($postTypes as $key => $item) {
            if (!empty($item)) {
                $postTypes[$key] = [];
                $postTypes[$key]['slug'] = $item;
                $postTypes[$key]['obj'] = cp_get_post_type_object($item);
                $postTypes[$key]['name'] = $postTypes[$key]['obj']->labels->name;
            }
        }

        return $postTypes;
    }

    public function getParsedObject()
    {
        if (!$this->posts) {
            return [];
        }
        $context = Context::getContext();
        $small = cp_get_image_type_name('small');
        $ret = [];
        foreach ($this->posts as $key => $val) {
            $ret[$key] = [];
            $ret[$key]['id'] = $val['id_product'];
            $ret[$key]['url'] = $context->link->getProductLink($val['id_product'], $val['link_rewrite']);
            $ret[$key]['date-published'] = $val['date_add'];
            $ret[$key]['date-modified'] = $val['date_upd'];
            if ($image = Image::getCover($val['id_product'])) {
                $ret[$key]['thumbnail'] = $context->link->getImageLink($val['link_rewrite'], $image['id_image'], $small);
                $ret[$key]['image-url'] = $context->link->getImageLink($val['link_rewrite'], $image['id_image'], $this->args['img_size']);

                if (empty($ret[$key]['thumbnail'])) {
                    $ret[$key]['thumbnail'] = $ret[$key]['image-url'];
                }
                $ret[$key]['image'] = '<img src="' . $ret[$key]['image-url'] . '" alt="">';
            }
            $ret[$key]['price'] = Tools::displayPrice(Product::getPriceStatic($val['id_product']));
            $ret[$key]['old-price'] = Tools::displayPrice(Product::getPriceStatic($val['id_product'], true, null, 6, null, false, false));
            if ($ret[$key]['price'] === $ret[$key]['old-price']) {
                $ret[$key]['old-price'] = '';
            }
            $ret[$key]['name'] = $val['name'];
            $ret[$key]['title'] = $ret[$key]['name'] . ' ' . $ret[$key]['price'];
            $ret[$key]['description'] = strip_tags($val['description']);
            $ret[$key]['description-short'] = strip_tags($val['description_short']);
            $ret[$key]['author'] = $val['manufacturer'];
            $ret[$key]['manufacturer'] = $val['manufacturer'];
            $catlinks = [];
            $cats = Product::getProductCategoriesFull($val['id_product'], $context->language->id);
            foreach ($cats as &$cat) {
                $catlinks[] = '<a href="' . $context->link->getCategoryLink(
                    $cat['id_category'],
                    $cat['link_rewrite']
                ) . '">' . $cat['name'] . '</a>';
            }
            $ret[$key]['breadcrumbs'] = '<div>' . implode(' / ', $catlinks) . '</div>';
            $ret[$key]['category'] = array_pop($catlinks);

            // $taglinks = [];
            // $tags = Tag::getProductTags($val['id_product']);
            // foreach ($tags[$context->language->id] as $tag) {
            //     $taglinks[] = '['. $tag .']';
            // }
            // $ret[$key]['tags'] = implode(' ', $taglinks);
        }

        return $ret;
    }

    public function getWithFormat($str, $textlength = 0)
    {
        if (!is_array($this->post)) {
            return $str;
        }
        $context = Context::getContext();

        // Post ID
        if (strpos($str, '[id]') !== false) {
            $str = str_replace('[id]', $this->post['id_product'], $str);
        }
        // Post URL
        if (strpos($str, '[url]') !== false) {
            $url = $context->link->getProductLink($this->post['id_product'], $this->post['link_rewrite']);
            $str = str_replace('[url]', $url, $str);
        }
        // Date published
        if (strpos($str, '[date-published]') !== false) {
            $str = str_replace(
                '[date-published]',
                date(cp_get_option('date_format'), strtotime($this->post['date_add'])),
                $str
            );
        }
        // Date modified
        if (strpos($str, '[date-modified]') !== false) {
            $str = str_replace(
                '[date-modified]',
                date(cp_get_option('date_format'), strtotime($this->post['date_upd'])),
                $str
            );
        }
        // Featured image
        if (strpos($str, '[image]') !== false) {
            $cover = Image::getCover($this->post['id_product']);
            $image = $context->link->getImageLink(
                $this->post['link_rewrite'],
                $cover['id_image'],
                $this->args['img_size']
            );
            if (!empty($image)) {
                $str = str_replace('[image]', '<img src="' . $image . '" alt="' . $this->post['name'] . '">', $str);
            }
        }
        // Featured image URL
        if (strpos($str, '[image-url]') !== false) {
            $cover = Image::getCover($this->post['id_product']);
            $image = $context->link->getImageLink(
                $this->post['link_rewrite'],
                $cover['id_image'],
                $this->args['img_size']
            );
            if (!empty($image)) {
                $str = str_replace('[image-url]', $image, $str);
            }
        }
        // Name
        if (strpos($str, '[name]') !== false) {
            $str = str_replace('[name]', $this->getTitle($textlength), $str);
        }
        // Price & old price
        $priceTag = strpos($str, '[price]') !== false;
        $oldPriceTag = strpos($str, '[old-price]') !== false;

        if ($priceTag || $oldPriceTag) {
            $price = Tools::displayPrice(Product::getPriceStatic($this->post['id_product']));

            if ($priceTag) {
                $str = str_replace('[price]', $price, $str);
            }
            if ($oldPriceTag) {
                $oldPrice = Tools::displayPrice(Product::getPriceStatic($this->post['id_product'], true, null, 6, null, false, false));

                if ($price === $oldPrice) {
                    $oldPrice = '';
                }
                $str = str_replace('[old-price]', $oldPrice, $str);
            }
        }
        // Description
        if (strpos($str, '[description]') !== false) {
            $str = str_replace('[description]', $this->getDescription($textlength), $str);
        }
        // Description short
        if (strpos($str, '[description-short]') !== false) {
            $str = str_replace('[description-short]', $this->getDescriptionShort($textlength), $str);
        }
        // Manufacturer
        if (strpos($str, '[manufacturer]') !== false) {
            $str = str_replace('[manufacturer]', $this->post['manufacturer'], $str);
        }
        // Category
        if (strpos($str, '[category]') !== false) {
            $str = str_replace('[category]', $this->getCategory(), $str);
        }
        // Category list
        if (strpos($str, '[breadcrumbs]') !== false) {
            $str = str_replace('[breadcrumbs]', $this->getCategoryList(), $str);
        }
        // Tags list
        // if (strpos($str, '[tags]') !== false) {
        //     $str = str_replace('[tags]', $this->getTagList(), $str);
        // }

        return $str;
    }

    /**
     * Returns the lastly selected post's title
     *
     * @return string The title of the post
     */
    public function getTitle($length = 0)
    {
        if (!is_array($this->post)) {
            return false;
        }

        $title = $this->post['name'];
        if (!empty($length)) {
            $title = Tools::substr($title, 0, $length);
        }

        return $title;
    }

    public function getCategory($post = null)
    {
        if (!empty($post)) {
            $post = $this->post;
        }

        $context = Context::getContext();
        $cats = Product::getProductCategoriesFull($this->post['id_product'], $context->language->id);
        if ($cats && count($cats)) {
            $cat = array_pop($cats);

            return '<a href="' . $context->link->getCategoryLink(
                $cat['id_category'],
                $cat['link_rewrite']
            ) . '">' . $cat['name'] . '</a>';
        } else {
            return '';
        }
    }

    public function getCategoryList($post = null)
    {
        if (!empty($post)) {
            $post = $this->post;
        }

        $context = Context::getContext();
        $cats = Product::getProductCategoriesFull($this->post['id_product'], $context->language->id);
        if ($cats && count($cats)) {
            $list = [];
            foreach ($cats as &$cat) {
                $list[] = '<a href="' . $context->link->getCategoryLink(
                    $cat['id_category'],
                    $cat['link_rewrite']
                ) . '">' . $cat['name'] . '</a>';
            }

            return '<div>' . implode(' / ', $list) . '</div>';
        } else {
            return '';
        }
    }

    /*
    public function getTagList($post = null)
    {
        if (!empty($post)) {
            $post = $this->post;
        }

        if (has_tag(false, $this->post->ID)) {
            $tags = cp_get_post_tags($this->post->ID);
            $list = [];
            foreach ($tags as $val) {
                $list[] = '<a href="/tag/' . $val->slug . '/">' . $val->name . '</a>';
            }
            return '<div>' . implode(', ', $list) . '</div>';
        } else {
            return '';
        }
    }
    */

    /**
     * Returns a subset of the post's content,
     * or the first paragraph if isn't specified
     *
     * @param int $length The subset's length
     *
     * @return string The content
     */
    public function getDescription($length = false)
    {
        if (!is_array($this->post)) {
            return false;
        }

        $content = $this->post['description'];
        if (!empty($length)) {
            return Tools::substr(strip_tags($content), 0, $length);
        }

        return strip_tags($content);
    }

    public function getDescriptionShort($length = false)
    {
        if (!is_array($this->post)) {
            return false;
        }

        $content = cp__($this->post['description_short']);
        if (!empty($length)) {
            return Tools::substr(strip_tags($content), 0, $length);
        }

        return strip_tags($content);
    }
}
