<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheTopic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CustomerCalculateOwnerTreeTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new CustomerCalculateOwnerTreeCacheTopic();
    }

    public function validBodyDataProvider(): array
    {
        return [
            'required only' => [
                'body' => [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => 42],
                'expectedBody' => [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => 42],
            ],
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' =>
                    '/The required option "cache_ttl" is missing./',
            ],
            'cache_ttl has invalid type' => [
                'body' => [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => new \stdClass()],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "cache_ttl" with value stdClass is expected '
                    . 'to be of type "int"/',
            ],
            'cache_ttl is invalid' => [
                'body' => [CustomerCalculateOwnerTreeCacheTopic::CACHE_TTL => 0],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "cache_ttl" with value 0 is invalid./',
            ],
        ];
    }

    public function testCreateJobName(): void
    {
        self::assertSame(
            'oro.customer.calculate_owner_tree_cache',
            $this->getTopic()->createJobName([])
        );
    }
}
