<?php

namespace Lighthouse\ReportsBundle\Reports\GrossSales\GrossSalesByProducts;

use Lighthouse\CoreBundle\Document\DocumentCollection;
use Lighthouse\CoreBundle\Document\Product\Store\StoreProduct;

class GrossSalesByProductsCollection extends DocumentCollection
{
    /**
     * @param StoreProduct $storeProduct
     * @return bool
     */
    public function containsStoreProduct(StoreProduct $storeProduct)
    {
        return $this->containsKey($storeProduct->id);
    }

    /**
     * @param StoreProduct $storeProduct
     * @param array $endDayHours
     * @return GrossSalesByProduct
     */
    public function createByStoreProduct(StoreProduct $storeProduct, array $endDayHours)
    {
        $report = new GrossSalesByProduct($storeProduct, $endDayHours);
        $this->set($storeProduct->id, $report);
        return $report;
    }

    /**
     * @param StoreProduct $storeProduct
     * @param array $endDayHours
     * @return GrossSalesByProduct
     */
    public function getByStoreProduct(StoreProduct $storeProduct, array $endDayHours)
    {
        if ($this->containsStoreProduct($storeProduct)) {
            return $this->get($storeProduct->id);
        } else {
            return $this->createByStoreProduct($storeProduct, $endDayHours);
        }
    }

    /**
     * @return $this
     */
    public function sortByName()
    {
        return $this->usort(
            function (GrossSalesByProduct $storeProductA, GrossSalesByProduct $storeProductB) {
                return $storeProductA->storeProduct->product->name > $storeProductB->storeProduct->product->name;
            }
        );
    }
}
