<?php

namespace Lighthouse\ReportsBundle\Document\GrossMarginSales;

use Lighthouse\CoreBundle\Document\AbstractDocument;
use Lighthouse\CoreBundle\Document\Store\Store;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * @property DateTime $dateFrom
 * @property DateTime $dateTo
 * @property Store $store
 */
class GrossMarginSalesFilter extends AbstractDocument
{
    /**
     * @var DateTime
     */
    protected $dateFrom;

    /**
     * @var DateTime
     */
    protected $dateTo;

    /**
     * @var Store
     */
    protected $store;
}
