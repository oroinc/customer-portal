<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProvider;
use Oro\Bundle\EntityConfigBundle\Provider\EntityUrlProviderInterface;
use Oro\Bundle\FrontendBundle\Provider\EntityUrlProviderDecorator;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class EntityUrlProviderDecoratorTest extends WebTestCase
{
    private EntityUrlProviderInterface $applicationAwareProvider;
    private EntityUrlProviderInterface $backendProvider;
    private FrontendHelper $frontendHelper;

    protected function setUp(): void
    {
        $this->initClient();

        // Get the application-aware service (decorated)
        $this->applicationAwareProvider = self::getContainer()
            ->get('oro_frontend.tests.entity_url_provider.application_aware');

        // Get the backend-only service (original, by class name)
        $this->backendProvider = self::getContainer()
            ->get(EntityUrlProvider::class);

        // Get the FrontendHelper to control request emulation
        $this->frontendHelper = self::getContainer()
            ->get(FrontendHelper::class);
    }

    protected function tearDown(): void
    {
        $this->frontendHelper->resetRequestEmulation();
        parent::tearDown();
    }

    public function testApplicationAwareServiceIsDecorated(): void
    {
        // The application-aware service should be the decorator
        self::assertInstanceOf(
            EntityUrlProviderDecorator::class,
            $this->applicationAwareProvider,
            'The application-aware service should be decorated by EntityUrlProviderDecorator'
        );
    }

    public function testBackendServiceIsNotDecorated(): void
    {
        // The class name service should be exactly the original, not decorated (strict check)
        self::assertSame(
            EntityUrlProvider::class,
            $this->backendProvider::class,
            'The backend service (by class name) should be exactly EntityUrlProvider, not a subclass or decorator'
        );
    }

    public function testGetRouteForBackendRequest(): void
    {
        // Emulate a back-office request
        $this->frontendHelper->emulateBackendRequest();

        // Both providers should return the same back-office route for back-office requests
        $backendRoute = $this->backendProvider->getRoute(CustomerUser::class);
        $applicationAwareRoute = $this->applicationAwareProvider->getRoute(CustomerUser::class);

        self::assertSame('oro_customer_customer_user_index', $backendRoute);
        self::assertSame(
            $backendRoute,
            $applicationAwareRoute,
            'For back-office requests, application-aware provider should return the same route as backend provider'
        );
    }

    public function testGetRouteForFrontendRequest(): void
    {
        // Emulate a frontend request
        $this->frontendHelper->emulateFrontendRequest();

        // Backend provider should still return the back-office route (always)
        $backendRoute = $this->backendProvider->getRoute(CustomerUser::class);
        self::assertSame('oro_customer_customer_user_index', $backendRoute);

        // Application-aware provider should return the storefront route for storefront requests
        $applicationAwareRoute = $this->applicationAwareProvider->getRoute(CustomerUser::class);
        self::assertSame('oro_customer_frontend_customer_user_index', $applicationAwareRoute);

        // They should be different
        self::assertNotSame(
            $backendRoute,
            $applicationAwareRoute,
            'For storefront requests, application-aware provider should return storefront route, not back-office route'
        );
    }

    public function testBackendProviderAlwaysReturnsBackendRoutes(): void
    {
        // Emulate a storefront request
        $this->frontendHelper->emulateFrontendRequest();

        // Even on storefront requests, the backend provider should return back-office routes
        $route = $this->backendProvider->getRoute(CustomerUser::class);
        self::assertSame(
            'oro_customer_customer_user_index',
            $route,
            'Backend provider should always return back-office routes, even for storefront requests'
        );

        // Verify it does NOT return the frontend route
        self::assertNotSame('oro_customer_frontend_customer_user_index', $route);
    }
}
