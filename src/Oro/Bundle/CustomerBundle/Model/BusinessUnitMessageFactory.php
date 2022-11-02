<?php

namespace Oro\Bundle\CustomerBundle\Model;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheByBusinessUnitTopic as Topic;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Factory for creating MQ messages with business unit entity.
 */
class BusinessUnitMessageFactory
{
    private DoctrineHelper $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    public function createMessage(int $jobId, string $entityClass, string|int $entityId): array
    {
        return [
            Topic::JOB_ID => $jobId,
            Topic::BUSINESS_UNIT_ENTITY_ID => $entityId,
            Topic::BUSINESS_UNIT_ENTITY_CLASS => $entityClass,
        ];
    }

    /**
     * @param array $messageBody
     * @return object|null
     */
    public function getBusinessUnitFromMessage(array $messageBody): ?object
    {
        return $this->doctrineHelper
            ->getEntityRepository($messageBody[Topic::BUSINESS_UNIT_ENTITY_CLASS])
            ->find($messageBody[Topic::BUSINESS_UNIT_ENTITY_ID]);
    }

    public function getJobIdFromMessage($data): int
    {
        return $data[Topic::JOB_ID];
    }
}
