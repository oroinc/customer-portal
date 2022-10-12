<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to warm up cache for all customer users by the specified business unit.
 */
class CustomerCalculateOwnerTreeCacheByBusinessUnitTopic extends AbstractTopic
{
    public const JOB_ID = 'jobId';
    public const BUSINESS_UNIT_ENTITY_ID = 'entityId';
    public const BUSINESS_UNIT_ENTITY_CLASS = 'entityClass';

    public static function getName(): string
    {
        return 'oro.customer.calculate_business_unit_owner_tree_cache';
    }

    public static function getDescription(): string
    {
        return 'Warm up cache for all customer users by the specified business unit.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->define(self::JOB_ID)
            ->required()
            ->allowedTypes('int');

        $resolver
            ->define(self::BUSINESS_UNIT_ENTITY_CLASS)
            ->required()
            ->allowedTypes('string');

        $resolver
            ->define(self::BUSINESS_UNIT_ENTITY_ID)
            ->required()
            ->allowedTypes('string', 'int');
    }
}
