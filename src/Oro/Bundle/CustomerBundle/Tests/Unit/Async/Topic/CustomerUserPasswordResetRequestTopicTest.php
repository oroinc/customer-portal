<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic as Topic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CustomerUserPasswordResetRequestTopicTest extends AbstractTopicTestCase
{
    #[\Override]
    protected function getTopic(): Topic
    {
        return new Topic();
    }

    #[\Override]
    public function validBodyDataProvider(): array
    {
        return [
            'required only' => [
                'body' => [Topic::USER_IDENTIFIER => 'john@example.com'],
                'expectedBody' => [
                    Topic::USER_IDENTIFIER => 'john@example.com',
                    Topic::WEBSITE_ID => null,
                    Topic::LOCALIZATION_ID => null,
                    Topic::REQUEST_PARAMETERS => [],
                ],
            ],
            'with website and localization' => [
                'body' => [
                    Topic::USER_IDENTIFIER => 'john@example.com',
                    Topic::WEBSITE_ID => 42,
                    Topic::LOCALIZATION_ID => 4242,
                ],
                'expectedBody' => [
                    Topic::USER_IDENTIFIER => 'john@example.com',
                    Topic::WEBSITE_ID => 42,
                    Topic::LOCALIZATION_ID => 4242,
                    Topic::REQUEST_PARAMETERS => [],
                ],
            ],
            'with request parameters' => [
                'body' => [
                    Topic::USER_IDENTIFIER => 'john@example.com',
                    Topic::REQUEST_PARAMETERS => ['_checkout_id' => '42'],
                ],
                'expectedBody' => [
                    Topic::USER_IDENTIFIER => 'john@example.com',
                    Topic::WEBSITE_ID => null,
                    Topic::LOCALIZATION_ID => null,
                    Topic::REQUEST_PARAMETERS => ['_checkout_id' => '42'],
                ],
            ],
        ];
    }

    #[\Override]
    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' => '/The required option "userIdentifier" is missing./',
            ],
            'userIdentifier has invalid type' => [
                'body' => [Topic::USER_IDENTIFIER => ['john@example.com']],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "userIdentifier" with value array is expected '
                    . 'to be of type "string"/',
            ],
            'websiteId has invalid type' => [
                'body' => [Topic::USER_IDENTIFIER => 'john@example.com', Topic::WEBSITE_ID => '42'],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "websiteId" with value "42" is expected '
                    . 'to be of type "int" or "null"/',
            ],
            'localizationId has invalid type' => [
                'body' => [Topic::USER_IDENTIFIER => 'john@example.com', Topic::LOCALIZATION_ID => '42'],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "localizationId" with value "42" is expected '
                    . 'to be of type "int" or "null"/',
            ],
            'requestParameters has invalid type' => [
                'body' => [Topic::USER_IDENTIFIER => 'john@example.com', Topic::REQUEST_PARAMETERS => '42'],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "requestParameters" with value "42" is expected '
                    . 'to be of type "array"/',
            ],
        ];
    }
}
