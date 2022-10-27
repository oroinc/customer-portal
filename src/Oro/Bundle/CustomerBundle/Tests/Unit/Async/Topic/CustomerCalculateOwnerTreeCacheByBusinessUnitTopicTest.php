<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerCalculateOwnerTreeCacheByBusinessUnitTopic as Topic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CustomerCalculateOwnerTreeCacheByBusinessUnitTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): Topic
    {
        return new Topic();
    }

    public function validBodyDataProvider(): array
    {
        return [
            'required only' => [
                'body' => [
                    Topic::JOB_ID => 42,
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => \stdClass::class,
                    Topic::BUSINESS_UNIT_ENTITY_ID => 4242,
                ],
                'expectedBody' => [
                    Topic::JOB_ID => 42,
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => \stdClass::class,
                    Topic::BUSINESS_UNIT_ENTITY_ID => 4242,
                ],
            ],
            'required alternative types' => [
                'body' => [
                    Topic::JOB_ID => 42,
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => \stdClass::class,
                    Topic::BUSINESS_UNIT_ENTITY_ID => '4242',
                ],
                'expectedBody' => [
                    Topic::JOB_ID => 42,
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => \stdClass::class,
                    Topic::BUSINESS_UNIT_ENTITY_ID => '4242',
                ],
            ],
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' => '/The required options "entityClass", "entityId", "jobId" are missing./',
            ],
            'entityClass has invalid type' => [
                'body' => [
                    Topic::JOB_ID => 42,
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => new \stdClass(),
                    Topic::BUSINESS_UNIT_ENTITY_ID => 4242,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "entityClass" with value stdClass is expected '
                    . 'to be of type "string"/',
            ],
            'entityId has invalid type' => [
                'body' => [
                    Topic::JOB_ID => 42,
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => \stdClass::class,
                    Topic::BUSINESS_UNIT_ENTITY_ID => new \stdClass(),
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "entityId" with value stdClass is expected '
                    . 'to be of type "string" or "int"/',
            ],
            'jobId has invalid type' => [
                'body' => [
                    Topic::JOB_ID => new \stdClass(),
                    Topic::BUSINESS_UNIT_ENTITY_CLASS => \stdClass::class,
                    Topic::BUSINESS_UNIT_ENTITY_ID => 4242,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "jobId" with value stdClass is expected '
                    . 'to be of type "int"/',
            ],
        ];
    }
}
