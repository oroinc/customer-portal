<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Api\ApiDoc;

use Oro\Bundle\ApiBundle\Config\Extension\FeatureConfigurationExtension;
use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Api\ApiDoc\NonAuthenticatedVisitorsDocumentationProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;

class NonAuthenticatedVisitorsDocumentationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var FeatureChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $featureChecker;

    /** @var NonAuthenticatedVisitorsDocumentationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $valueNormalizer = $this->createMock(ValueNormalizer::class);
        $valueNormalizer->expects(self::any())
            ->method('normalizeValue')
            ->with(self::isType('string'), DataType::ENTITY_TYPE)
            ->willReturnCallback(function (mixed $value) {
                return str_replace('Test\\', 'test', $value);
            });

        $this->provider = new NonAuthenticatedVisitorsDocumentationProvider(
            ['Test\Entity3', 'Test\Entity2', 'Test\Entity1'],
            $this->configManager,
            $valueNormalizer,
            $this->featureChecker
        );
    }

    public function testGetDocumentationWhenAccessToNonAuthenticatedVisitorsDenied(): void
    {
        $requestType = new RequestType(['test']);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(false);

        $this->featureChecker->expects(self::never())
            ->method('isResourceEnabled');

        self::assertNull($this->provider->getDocumentation($requestType));
    }

    public function testGetDocumentationWhenNoApiResourcesForNonAuthenticatedVisitors(): void
    {
        $requestType = new RequestType(['test']);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(true);

        $this->featureChecker->expects(self::exactly(3))
            ->method('isResourceEnabled')
            ->willReturn(false);

        self::assertNull($this->provider->getDocumentation($requestType));
    }

    public function testGetDocumentationWhenApiResourcesForNonAuthenticatedVisitorsExist(): void
    {
        $requestType = new RequestType(['test']);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.non_authenticated_visitors_api')
            ->willReturn(true);

        $this->featureChecker->expects(self::exactly(3))
            ->method('isResourceEnabled')
            ->willReturnMap([
                ['Test\Entity1', FeatureConfigurationExtension::API_RESOURCE_KEY, null, true],
                ['Test\Entity2', FeatureConfigurationExtension::API_RESOURCE_KEY, null, false],
                ['Test\Entity3', FeatureConfigurationExtension::API_RESOURCE_KEY, null, true]
            ]);

        $expectedDocumentation = <<<MARKDOWN
The following API resources can be used by non-authenticated visitors:

- testEntity1
- testEntity3
MARKDOWN;

        self::assertEquals(
            $expectedDocumentation,
            $this->provider->getDocumentation($requestType)
        );
    }
}
