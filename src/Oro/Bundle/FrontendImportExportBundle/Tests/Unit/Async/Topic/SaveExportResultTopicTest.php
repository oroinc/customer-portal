<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\FrontendImportExportBundle\Async\Topic\SaveExportResultTopic;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SaveExportResultTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new SaveExportResultTopic();
    }

    public function validBodyDataProvider(): array
    {
        $onlyRequiredOptionsSet = [
            'jobId' => 1,
            'entity' => 'entityName',
            'type' => ProcessorRegistry::TYPE_EXPORT,
            'customerUserId' => 1,
        ];
        $fullOptionsSet = array_merge(
            $onlyRequiredOptionsSet,
            [
                'options' => [
                    'foo' => 'bar',
                ],
            ]
        );

        return [
            'only required options' => [
                'body' => $onlyRequiredOptionsSet,
                'expectedBody' => array_merge(
                    $onlyRequiredOptionsSet,
                    [
                        'options' => [],
                    ]
                )
            ],
            'full set of options' => [
                'body' => $fullOptionsSet,
                'expectedBody' => $fullOptionsSet,
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' =>
                    '/The required options "customerUserId", "entity", "jobId", "type" are missing./',
            ],
            'wrong jobId type' => [
                'body' => [
                    'jobId' => null,
                    'entity' => 'entityName',
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'customerUserId' => 1,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "jobId" with value null is expected to be of type "int"/',
            ],
            'wrong type type' => [
                'body' => [
                    'jobId' => 1,
                    'entity' => 'entityName',
                    'type' => null,
                    'customerUserId' => 1,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "type" with value null is expected to be of type "string"/',
            ],
            'wrong customerUserId type' => [
                'body' => [
                    'jobId' => 1,
                    'entity' => 'entityName',
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'customerUserId' => null,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "customerUserId" with value null is expected to be of type "int"/',
            ],
            'wrong entity type' => [
                'body' => [
                    'jobId' => 1,
                    'entity' => null,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'customerUserId' => 1,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "entity" with value null is expected to be of type "string"/',
            ],
            'wrong options type' => [
                'body' => [
                    'jobId' => 1,
                    'entity' => null,
                    'type' => ProcessorRegistry::TYPE_EXPORT,
                    'customerUserId' => 1,
                    'options' => null,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' =>
                    '/The option "options" with value null is expected to be of type "array", '
                    . 'but is of type "null"./',
            ],
        ];
    }
}
