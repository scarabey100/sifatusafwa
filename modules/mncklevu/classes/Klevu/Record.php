<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\Klevu;

class Record
{
    /**
     * @var string
     */
    const TYPE_PRODUCT = 'KLEVU_PRODUCT';

    /**
     * @var string
     */
    const TYPE_CATEGORY = 'KLEVU_CATEGORY';

    /**
     * @var string
     */
    const TYPE_CMS = 'KLEVU_CMS';

    /**
     * @var string
     */
    const PRICE_TYPE_ORIGINAL = 'price';

    /**
     * @var string
     */
    const PRICE_TYPE_FINAL = 'salePrice';

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $itemGroupId = '';

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $sku = null;

    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var string Currency code, e.g. EUR, USD, GBP
     */
    protected $currency = null;

    /**
     * @var float
     */
    protected $price = null;

    /**
     * @var float
     */
    protected $salePrice = 0.0;

    /**
     * @var string
     */
    protected $otherPrices = '';

    /**
     * @var string
     */
    protected $inStock = '';

    /**
     * @var string
     */
    protected $category = null;

    /**
     * @var string
     */
    protected $listCategory = null;

    /**
     * @var string URL
     */
    protected $image = '';

    /**
     * @var string URL
     */
    protected $imageHover = '';

    /**
     * @var string
     */
    protected $shortDesc = '';

    /**
     * @var string
     */
    protected $desc = '';

    /**
     * @var string
     */
    protected $other = '';

    /**
     * @var string
     */
    protected $otherAttributeToIndex = '';

    /**
     * @var string
     */
    protected $tags = '';

    /**
     * @var string JSON
     */
    protected $additionalDataToReturn = '';

    /**
     * @var string date (ISO 8601)
     */
    protected $createdAt = '';

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = strtoupper((string)$type);

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (string)$id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $itemGroupId
     *
     * @return $this
     */
    public function setItemGroupId($itemGroupId)
    {
        $this->itemGroupId = (string)$itemGroupId;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    /**
     * @param string $sku
     *
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = (string)$sku;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = (string)$url;

        return $this;
    }

    /**
     * @param string $currency Currency code, e.g. EUR, USD, GBP
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = (string)$currency;

        return $this;
    }

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = (float)$price;

        return $this;
    }

    /**
     * @param float $salePrice
     *
     * @return $this
     */
    public function setSalePrice($salePrice)
    {
        $this->salePrice = (float)$salePrice;

        return $this;
    }

    /**
     * @param string $otherPrices
     *
     * @return $this
     */
    public function setOtherPrices($otherPrices)
    {
        $this->otherPrices = (string)$otherPrices;

        return $this;
    }

    /**
     * @param bool $inStock
     *
     * @return $this
     */
    public function setInStock($inStock)
    {
        $this->inStock = $inStock ? 'yes' : 'no';

        return $this;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = (string)$category;

        return $this;
    }

    /**
     * @param string $listCategory
     *
     * @return $this
     */
    public function setListCategory($listCategory)
    {
        $this->listCategory = (string)$listCategory;

        return $this;
    }

    /**
     * @param string $image URL
     *
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = (string)$image;

        return $this;
    }

    /**
     * @param string $imageHover URL
     *
     * @return $this
     */
    public function setImageHover($imageHover)
    {
        $this->imageHover = (string)$imageHover;

        return $this;
    }

    /**
     * @param string $shortDesc
     *
     * @return $this
     */
    public function setShortDesc($shortDesc)
    {
        $this->shortDesc = (string)$shortDesc;

        return $this;
    }

    /**
     * @param string $desc
     *
     * @return $this
     */
    public function setDesc($desc)
    {
        $this->desc = (string)$desc;

        return $this;
    }

    /**
     * @param string $other
     *
     * @return $this
     */
    public function setOther($other)
    {
        $this->other = (string)$other;

        return $this;
    }

    /**
     * @param string $otherAttributeToIndex
     *
     * @return $this
     */
    public function setOtherAttributeToIndex($otherAttributeToIndex)
    {
        $this->otherAttributeToIndex = (string)$otherAttributeToIndex;

        return $this;
    }

    /**
     * @param string $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = (string)$tags;

        return $this;
    }

    /**
     * @param string $additionalDataToReturn JSON
     *
     * @return $this
     */
    public function setAdditionalDataToReturn($additionalDataToReturn)
    {
        $this->additionalDataToReturn = (string)$additionalDataToReturn;

        return $this;
    }

    /**
     * @param string $createdAt date (ISO 8601)
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = (string)$createdAt;

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $cdata
     *
     * @return string
     */
    protected function getPairXml($key, $value, $cdata = false)
    {
        if ($cdata) {
            $value = '<![CDATA[' . $value . ']]>';
        }

        return '<pair><key>' . $key . '</key><value>' . $value . '</value></pair>';
    }

    /**
     * @return string
     */
    public function getXml()
    {
        return implode('', [
            '<record><pairs>',
            $this->getPairXml('id', $this->id),
            $this->itemGroupId ? $this->getPairXml('itemGroupId', $this->itemGroupId) : '',
            $this->name ? $this->getPairXml('name', $this->name, true) : '',
            $this->sku ? $this->getPairXml('sku', $this->sku) : '',
            $this->url ? $this->getPairXml('url', $this->url) : '',
            $this->currency ? $this->getPairXml('currency', $this->currency) : '',
            $this->price ? $this->getPairXml(Record::PRICE_TYPE_ORIGINAL, $this->price) : '',
            $this->salePrice ? $this->getPairXml(Record::PRICE_TYPE_FINAL, $this->salePrice) : '',
            $this->otherPrices ? $this->getPairXml('otherPrices', $this->otherPrices) : '',
            $this->inStock ? $this->getPairXml('inStock', $this->inStock) : '',
            $this->category ? $this->getPairXml('category', $this->category, true) : '',
            $this->type && $this->listCategory ?
                $this->getPairXml('listCategory', $this->type . ';;' . $this->listCategory, true) : '',
            $this->image ? $this->getPairXml('image', $this->image) : '',
            $this->imageHover ? $this->getPairXml('imageHover', $this->imageHover) : '',
            $this->shortDesc ? $this->getPairXml('shortDesc', $this->shortDesc, true) : '',
            $this->desc ? $this->getPairXml('desc', $this->desc, true) : '',
            $this->other ? $this->getPairXml('other', $this->other, true) : '',
            $this->otherAttributeToIndex ?
                $this->getPairXml('otherAttributeToIndex', $this->otherAttributeToIndex, true) : '',
            $this->tags ? $this->getPairXml('tags', $this->tags, true) : '',
            $this->additionalDataToReturn ?
                $this->getPairXml('additionalDataToReturn', $this->additionalDataToReturn, true) : '',
            $this->createdAt ? $this->getPairXml('created_at', $this->createdAt) : '',
            '</pairs></record>'
        ]);
    }
}
