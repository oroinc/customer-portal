<?php

namespace Oro\Bundle\FrontendImportExportBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\FrontendImportExportBundle\Async\Topic\PreExportTopic;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PreExportTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new PreExportTopic(
            $this->createMock(TokenStorageInterface::class)
        );
    }

    public function validBodyDataProvider(): array
    {
        $fullOptionsSet = [
            'jobName' => 'foo',
            'processorAlias' => 'baz',
            'outputFormat' => 'output_format',
            'organizationId' => 1,
            'exportType' => ProcessorRegistry::TYPE_EXPORT_TEMPLATE,
            'options' => [
                'foo' => 'bar',
            ],
            'outputFilePrefix' => 'prefix',
            'refererUrl' => '/some/url',
        ];

        return [
            'only required options' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'baz',
                ],
                'expectedBody' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'baz',
                    'outputFormat' => 'csv',
                    'exportType' => ProcessorRegistry::TYPE_EXPORT,
                    'options' => [],
                    'outputFilePrefix' => null,
                    'refererUrl' => null,
                ],
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
                'exceptionMessage' => '/The required options "jobName", "processorAlias" are missing./',
            ],
            'wrong jobName type' => [
                'body' => [
                    'jobName' => null,
                    'processorAlias' => 'baz',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "jobName" with value null is expected to be of type "string"/',
            ],
            'wrong processorAlias type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => null,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "processorAlias" with value null is expected '
                    . 'to be of type "string"/',
            ],
            'wrong outputFormat type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'bar',
                    'outputFormat' => null,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "outputFormat" with value null is expected to be of type "string"/',
            ],
            'wrong organizationId type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'bar',
                    'organizationId' => new \stdClass(),
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "organizationId" with value stdClass is expected '
                    . 'to be of type "int" or "null"/',
            ],
            'wrong exportType type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'bar',
                    'exportType' => null,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "exportType" with value null is expected to be of type "string"/',
            ],
            'wrong options type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'bar',
                    'options' => null,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "options" with value null is expected to be of type "array"/',
            ],
            'wrong outputFilePrefix type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'bar',
                    'outputFilePrefix' => [],
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "outputFilePrefix" with value array is expected '
                    . 'to be of type "string" or "null"/',
            ],
            'wrong refererUrl type' => [
                'body' => [
                    'jobName' => 'foo',
                    'processorAlias' => 'baz',
                    'refererUrl' => [],
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "refererUrl" with value array is expected '
                    . 'to be of type "string" or "null"/',
            ],
        ];
    }

    public function testGetName(): void
    {
        self::assertEquals('oro_frontend_importexport.pre_export', PreExportTopic::getName());
    }
}
