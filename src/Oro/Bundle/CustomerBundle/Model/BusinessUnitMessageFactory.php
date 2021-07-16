<?php

namespace Oro\Bundle\CustomerBundle\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ProductBundle\Model\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Factory for creating MQ messages with business unit entity.
 */
class BusinessUnitMessageFactory
{
    private const JOB_ID = 'jobId';
    private const BUSINESS_UNIT_ENTITY_ID = 'entityId';
    private const BUSINESS_UNIT_ENTITY_CLASS = 'entityClass';

    /**
     * @var OptionsResolver
     */
    private $resolver;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * BusinessUnitMessageFactory constructor.
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param int $jobId
     * @param string $entityClass
     * @param string|int $entityId
     * @return array
     */
    public function createMessage(int $jobId, string $entityClass, $entityId): array
    {
        return $this->getResolvedData([
            self::JOB_ID => $jobId,
            self::BUSINESS_UNIT_ENTITY_ID => $entityId,
            self::BUSINESS_UNIT_ENTITY_CLASS => $entityClass
        ]);
    }

    /**
     * @param array $data
     * @return object
     * @throws InvalidArgumentException
     */
    public function getBusinessUnitFromMessage($data)
    {
        $data = $this->getResolvedData($data);

        return $this->doctrineHelper->getEntityReference(
            $data[self::BUSINESS_UNIT_ENTITY_CLASS],
            $data[self::BUSINESS_UNIT_ENTITY_ID]
        );
    }

    public function getJobIdFromMessage($data): int
    {
        $data = $this->getResolvedData($data);

        return $data[self::JOB_ID];
    }

    private function getOptionsResolver(): OptionsResolver
    {
        if (null === $this->resolver) {
            $resolver = new OptionsResolver();

            $resolver->setRequired([
                self::BUSINESS_UNIT_ENTITY_CLASS,
                self::BUSINESS_UNIT_ENTITY_ID,
                self::JOB_ID
            ]);

            $resolver->setAllowedTypes(self::BUSINESS_UNIT_ENTITY_CLASS, 'string');
            $resolver->setAllowedTypes(self::BUSINESS_UNIT_ENTITY_ID, ['int', 'string']);
            $resolver->setAllowedTypes(self::JOB_ID, ['int']);

            $this->resolver = $resolver;
        }

        return $this->resolver;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getResolvedData(array $data): array
    {
        try {
            return $this->getOptionsResolver()->resolve($data);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}
