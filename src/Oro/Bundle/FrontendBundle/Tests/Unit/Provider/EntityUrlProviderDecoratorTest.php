<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProviderInterface;
use Oro\Bundle\FrontendBundle\Provider\EntityUrlProviderDecorator;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class EntityUrlProviderDecoratorTest extends \PHPUnit\Framework\TestCase
{
    private EntityUrlProviderInterface|MockObject $backendProvider;
    private EntityUrlProviderInterface|MockObject $storefrontProvider;
    private FrontendHelper|MockObject $frontendHelper;
    private EntityUrlProviderDecorator $decorator;

    #[\Override]
    protected function setUp(): void
    {
        $this->backendProvider = $this->createMock(EntityUrlProviderInterface::class);
        $this->storefrontProvider = $this->createMock(EntityUrlProviderInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->decorator = new EntityUrlProviderDecorator(
            $this->backendProvider,
            $this->storefrontProvider,
            $this->frontendHelper
        );
    }

    /** @dataProvider requestTypeProvider */
    public function testGetRoute(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $routeType = EntityUrlProviderInterface::ROUTE_VIEW;
        $throwException = false;
        $expectedRoute = $isFrontendRequest ? 'oro_frontend_stdclass_view' : 'oro_stdclass_view';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getRoute')
                ->with($entity, $routeType, $throwException)
                ->willReturn($expectedRoute);

            $this->backendProvider->expects(self::never())->method('getRoute');
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getRoute')
                ->with($entity, $routeType, $throwException)
                ->willReturn($expectedRoute);

            $this->storefrontProvider->expects(self::never())->method('getRoute');
        }

        $result = $this->decorator->getRoute($entity, $routeType, $throwException);

        $this->assertEquals($expectedRoute, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetRouteWithDefaultParameters(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $expectedRoute = $isFrontendRequest ? 'oro_frontend_stdclass_index' : 'oro_stdclass_index';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getRoute')
                ->with($entity, EntityUrlProviderInterface::ROUTE_INDEX, false)
                ->willReturn($expectedRoute);
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getRoute')
                ->with($entity, EntityUrlProviderInterface::ROUTE_INDEX, false)
                ->willReturn($expectedRoute);
        }

        $result = $this->decorator->getRoute($entity);

        $this->assertEquals($expectedRoute, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetRouteWithThrowException(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $routeType = EntityUrlProviderInterface::ROUTE_UPDATE;

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $exception = new \LogicException('Route not found');

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getRoute')
                ->with($entity, $routeType, true)
                ->willThrowException($exception);
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getRoute')
                ->with($entity, $routeType, true)
                ->willThrowException($exception);
        }

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Route not found');

        $this->decorator->getRoute($entity, $routeType, true);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetIndexUrl(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $extraParams = ['filter' => 'active'];
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass' : '/admin/stdclass';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getIndexUrl')
                ->with($entity, $extraParams)
                ->willReturn($expectedUrl);

            $this->backendProvider->expects(self::never())->method('getIndexUrl');
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getIndexUrl')
                ->with($entity, $extraParams)
                ->willReturn($expectedUrl);

            $this->storefrontProvider->expects(self::never())->method('getIndexUrl');
        }

        $result = $this->decorator->getIndexUrl($entity, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetIndexUrlWithoutExtraParams(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass' : '/admin/stdclass';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getIndexUrl')
                ->with($entity, [])
                ->willReturn($expectedUrl);
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getIndexUrl')
                ->with($entity, [])
                ->willReturn($expectedUrl);
        }

        $result = $this->decorator->getIndexUrl($entity);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetViewUrl(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $entityId = 123;
        $extraParams = ['tab' => 'details'];
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass/123' : '/admin/stdclass/123';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getViewUrl')
                ->with($entity, $entityId, $extraParams)
                ->willReturn($expectedUrl);

            $this->backendProvider->expects(self::never())->method('getViewUrl');
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getViewUrl')
                ->with($entity, $entityId, $extraParams)
                ->willReturn($expectedUrl);

            $this->storefrontProvider->expects(self::never())->method('getViewUrl');
        }

        $result = $this->decorator->getViewUrl($entity, $entityId, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetViewUrlWithoutExtraParams(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $entityId = 456;
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass/456' : '/admin/stdclass/456';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getViewUrl')
                ->with($entity, $entityId, [])
                ->willReturn($expectedUrl);
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getViewUrl')
                ->with($entity, $entityId, [])
                ->willReturn($expectedUrl);
        }

        $result = $this->decorator->getViewUrl($entity, $entityId);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetUpdateUrl(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $entityId = 789;
        $extraParams = ['redirect' => 'list'];
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass/789/update' : '/admin/stdclass/789/update';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getUpdateUrl')
                ->with($entity, $entityId, $extraParams)
                ->willReturn($expectedUrl);

            $this->backendProvider->expects(self::never())->method('getUpdateUrl');
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getUpdateUrl')
                ->with($entity, $entityId, $extraParams)
                ->willReturn($expectedUrl);

            $this->storefrontProvider->expects(self::never())->method('getUpdateUrl');
        }

        $result = $this->decorator->getUpdateUrl($entity, $entityId, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetUpdateUrlWithoutExtraParams(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $entityId = 321;
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass/321/update' : '/admin/stdclass/321/update';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getUpdateUrl')
                ->with($entity, $entityId, [])
                ->willReturn($expectedUrl);
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getUpdateUrl')
                ->with($entity, $entityId, [])
                ->willReturn($expectedUrl);
        }

        $result = $this->decorator->getUpdateUrl($entity, $entityId);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetCreateUrl(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $extraParams = ['template' => 'default'];
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass/create' : '/admin/stdclass/create';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getCreateUrl')
                ->with($entity, $extraParams)
                ->willReturn($expectedUrl);

            $this->backendProvider->expects(self::never())
                ->method('getCreateUrl');
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getCreateUrl')
                ->with($entity, $extraParams)
                ->willReturn($expectedUrl);

            $this->storefrontProvider->expects(self::never())
                ->method('getCreateUrl');
        }

        $result = $this->decorator->getCreateUrl($entity, $extraParams);

        $this->assertEquals($expectedUrl, $result);
    }

    /** @dataProvider requestTypeProvider */
    public function testGetCreateUrlWithoutExtraParams(bool $isFrontendRequest): void
    {
        $entity = \stdClass::class;
        $expectedUrl = $isFrontendRequest ? '/frontend/stdclass/create' : '/admin/stdclass/create';

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        if ($isFrontendRequest) {
            $this->storefrontProvider->expects(self::once())
                ->method('getCreateUrl')
                ->with($entity, [])
                ->willReturn($expectedUrl);
        } else {
            $this->backendProvider->expects(self::once())
                ->method('getCreateUrl')
                ->with($entity, [])
                ->willReturn($expectedUrl);
        }

        $result = $this->decorator->getCreateUrl($entity);

        $this->assertEquals($expectedUrl, $result);
    }

    /**
     * Basic data provider to repeat tests for both frontend and backend requests.
     */
    public function requestTypeProvider(): array
    {
        return [
            'backend request' => [
                'isFrontendRequest' => false,
            ],
            'frontend request' => [
                'isFrontendRequest' => true,
            ],
        ];
    }
}
