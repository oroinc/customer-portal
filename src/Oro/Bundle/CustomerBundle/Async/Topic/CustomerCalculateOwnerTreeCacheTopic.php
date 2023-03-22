<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to warm up cache for all customer users who logged in during cache TTL time period.
 */
class CustomerCalculateOwnerTreeCacheTopic extends AbstractTopic implements JobAwareTopicInterface
{
    public const CACHE_TTL = 'cache_ttl';

    public static function getName(): string
    {
        return 'oro.customer.calculate_owner_tree_cache';
    }

    public static function getDescription(): string
    {
        return 'Warm up cache for all customer users who logged in during cache TTL time period.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->define(self::CACHE_TTL)
            ->required()
            ->allowedTypes('int')
            ->allowedValues(static fn (int $ttl) => $ttl > 0)
            ->info('Number of seconds that should pass since the last login of a customer user');
    }

    public function createJobName($messageBody): string
    {
        return self::getName();
    }
}
