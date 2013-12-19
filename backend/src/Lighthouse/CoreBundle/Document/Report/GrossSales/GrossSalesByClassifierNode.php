<?php

namespace Lighthouse\CoreBundle\Document\Report\GrossSales;

use Lighthouse\CoreBundle\Document\Classifier\AbstractNode;

abstract class GrossSalesByClassifierNode extends TodayGrossSales
{
    /**
     * @return AbstractNode
     */
    abstract public function getNode();
}
