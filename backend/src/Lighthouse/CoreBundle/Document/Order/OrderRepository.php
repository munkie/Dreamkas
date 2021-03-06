<?php

namespace Lighthouse\CoreBundle\Document\Order;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\LockMode;
use Lighthouse\CoreBundle\Document\DocumentRepository;

/**
 * @method Order find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 */
class OrderRepository extends DocumentRepository
{
    /**
     * @param string $storeId
     * @param OrdersFilter $ordersFilter
     * @return Cursor|Order[]
     */
    public function findAllByStoreId($storeId, OrdersFilter $ordersFilter)
    {
        $criteria = array('store' => $storeId);
        $sort = array('createdDate' => self::SORT_DESC, 'id' => self::SORT_DESC);
        if ($ordersFilter->hasIncomplete()) {
            $criteria['invoice'] = null;
        }
        return $this->findBy($criteria, $sort);
    }

    /**
     * @param string $id
     * @return NullOrder
     */
    public function getNullObject($id)
    {
        $nullObject = new NullOrder();
        $nullObject->id = $id;
        return $nullObject;
    }
}
