<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\ApiDoc;

use Oro\Bundle\ApiBundle\Provider\ResourcesProvider;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Request\Version;
use Oro\Bundle\CustomerBundle\Api\ApiDoc\NonAuthenticatedVisitorsDocumentationProvider;

class NonAuthenticatedVisitorsDocumentationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ResourcesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $resourcesProvider;

    /** @var NonAuthenticatedVisitorsDocumentationProvider */
    private $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->resourcesProvider = $this->createMock(ResourcesProvider::class);

        $valueNormalizer = $this->createMock(ValueNormalizer::class);
        $valueNormalizer->expects(self::any())
            ->method('normalizeValue')
            ->with(self::isType('string'), DataType::ENTITY_TYPE)
            ->willReturnCallback(function (mixed $value) {
                return str_replace('Test\\', 'test', $value);
            });

        $this->provider = new NonAuthenticatedVisitorsDocumentationProvider(
            ['Test\Entity3', 'Test\Entity2', 'Test\Entity1'],
            $valueNormalizer,
            $this->resourcesProvider
        );
    }

    public function testGetDocumentationWhenNoApiResourcesForNonAuthenticatedVisitors(): void
    {
        $requestType = new RequestType(['test']);

        $this->resourcesProvider->expects(self::exactly(3))
            ->method('isResourceEnabled')
            ->willReturn(false);

        self::assertNull($this->provider->getDocumentation($requestType));
    }

    public function testGetDocumentationWhenApiResourcesForNonAuthenticatedVisitorsExist(): void
    {
        $requestType = new RequestType(['test']);

        $this->resourcesProvider->expects(self::exactly(3))
            ->method('isResourceEnabled')
            ->willReturnMap([
                ['Test\Entity1', ApiAction::OPTIONS, Version::LATEST, $requestType, true],
                ['Test\Entity2', ApiAction::OPTIONS, Version::LATEST, $requestType, false],
                ['Test\Entity3', ApiAction::OPTIONS, Version::LATEST, $requestType, true]
            ]);

        self::assertEquals(
            'The following API resources can be used by non-authenticated visitors'
            . ' when "Enable Guest Storefront API" configuration option is enabled:'
            . "\n"
            . "\n- testEntity1"
            . "\n- testEntity3",
            $this->provider->getDocumentation($requestType)
        );
    }
}
