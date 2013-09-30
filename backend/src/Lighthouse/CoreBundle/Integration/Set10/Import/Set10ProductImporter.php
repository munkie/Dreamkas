<?php

namespace Lighthouse\CoreBundle\Integration\Set10\Import;

use Lighthouse\CoreBundle\Document\Classifier\Category\Category;
use Lighthouse\CoreBundle\Document\Classifier\Group\Group;
use Lighthouse\CoreBundle\Document\Classifier\SubCategory\SubCategory;
use Lighthouse\CoreBundle\Document\Product\Product;
use XMLReader;
use DOMDocument;

class Set10ProductImporter
{
    /**
     * @var XMLReader
     */
    protected $xmlReader;

    /**
     * @var Group[]
     */
    protected $groups;

    /**
     * @var Category[]
     */
    protected $categories;

    /**
     * @var SubCategory[]
     */
    protected $subCategories;

    /**
     * @param $xmlFilePath
     */
    public function __construct($xmlFilePath)
    {
        $this->createXmlReader($xmlFilePath);
    }

    /**
     * @param string $xmlFilePath
     */
    protected function createXmlReader($xmlFilePath)
    {
        $this->xmlReader = new XMLReader();
        $this->xmlReader->open($xmlFilePath, 'UTF-8');
    }

    /**
     * @return GoodElement
     */
    public function readNextNode()
    {
        while ($this->xmlReader->read()) {
            if (XMLReader::ELEMENT === $this->xmlReader->nodeType && 'good' == $this->xmlReader->name) {
                $domNode = $this->xmlReader->expand();
                $doc = new DOMDocument('1.0', 'UTF-8');
                return simplexml_import_dom($doc->importNode($domNode, true), GoodElement::getClassName());
            }
        }
        return false;
    }

    /**
     * @return bool|Product
     */
    public function createNextProduct()
    {
        $xml = $this->readNextNode();
        if ($xml) {
            return $this->createProduct($xml);
        } else {
            return false;
        }
    }

    /**
     * @param GoodElement $good
     * @return Product
     */
    public function createProduct(GoodElement $good)
    {
        $product = new Product();
        $product->name = $good->getGoodName();
        $product->sku  = $good->getSku();
        $product->vat  = $good->getVat();
        $product->barcode = $good->getBarcode();
        $product->vendor = $good->getVendor();
        $product->units = $good->getUnits();

        $product->subCategory = $this->getSubCategory($good);

        return $product;
    }

    /**
     * @param GoodElement $good
     * @return SubCategory
     */
    public function getSubCategory(GoodElement $good)
    {
        $groups = $this->normalizeGroups($good);

        // create subCategory if not exists
        if (!isset($this->subCategories[$groups[2]['id']])) {
            $subCategory = new SubCategory();
            $subCategory->name = $groups[2]['name'];

            // create category if not exists
            if (!isset($this->categories[$groups[1]['id']])) {
                $category = new Category();
                $category->name = $groups[1]['name'];

                // create group if not exists
                if (!isset($this->groups[$groups[0]['id']])) {
                    $group = new Group();
                    $group->name = $groups[0]['name'];

                    $this->groups[$groups[0]['id']] = $group;
                }

                $category->group = $this->groups[$groups[0]['id']];

                $this->categories[$groups[1]['id']] = $category;
            }

            $subCategory->category = $this->categories[$groups[1]['id']];

            $this->subCategories[$groups[2]['id']] = $subCategory;
        }

        return $this->subCategories[$groups[2]['id']];
    }

    /**
     * @param GoodElement $good
     * @return array
     */
    protected function normalizeGroups(GoodElement $good)
    {
        $groups = $good->getGroups();
        $groupsCount = count($groups);
        if ($groupsCount == 0) {
            return $groups;
        } elseif ($groupsCount > 3) {
            $groups = array_slice($groups, 0, 3);
        } elseif ($groupsCount < 3) {
            $lastGroup = end($groups);
            for ($i = $groupsCount; $i < 3; $i++) {
                $groups[] = $lastGroup;
            }
        }
        return $groups;
    }
}
