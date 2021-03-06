<?php

namespace Lighthouse\IntegrationBundle\Document\Integration\Set10;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Lighthouse\JobBundle\Document\Job\Job;

/**
 * @MongoDB\Document(
 *      repositoryClass="Lighthouse\CoreBundle\Job\JobRepository",
 *      collection="Jobs"
 * )
 */
class ExportProductsJob extends Job
{
    const TYPE = 'set10_export_products';

    /**
     * @MongoDB\Id
     * @var string
     */
    protected $id;

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}
