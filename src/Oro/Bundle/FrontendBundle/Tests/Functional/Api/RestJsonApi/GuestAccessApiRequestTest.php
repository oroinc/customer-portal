<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class GuestAccessApiRequestTest extends FrontendRestJsonApiTestCase
{
    private const GUEST_ACCESS_ENABLED_CONFIG_NAME = 'oro_frontend.guest_access_enabled';

    private bool $originalGuestAccessEnabled;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
    }

    protected function tearDown(): void
    {
        $configManager = self::getConfigManager();
        $configManager->set(self::GUEST_ACCESS_ENABLED_CONFIG_NAME, $this->originalGuestAccessEnabled);
        $configManager->flush();
        parent::tearDown();
    }

    private function setGuestAccess(bool $guestAccessEnabled): void
    {
        $configManager = self::getConfigManager();
        $this->originalGuestAccessEnabled = $configManager->get(self::GUEST_ACCESS_ENABLED_CONFIG_NAME);
        $configManager->set(self::GUEST_ACCESS_ENABLED_CONFIG_NAME, $guestAccessEnabled);
        $configManager->flush();
    }

    /**
     * @dataProvider apiResourcesProvider
     */
    public function testTryToGeResourcesWithDisallowedAccessForGuest(string $entityResource): void
    {
        $this->setGuestAccess(false);
        $response = $this->cget(
            ['entity' => $entityResource],
            [],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
        self::assertSame('', $response->getContent());
    }

    /**
     * @dataProvider apiResourcesProvider
     */
    public function testGetResourcesWithAllowedAccessForGuest(string $entityResource): void
    {
        $this->setGuestAccess(true);
        $response = $this->cget(
            ['entity' => $entityResource],
            [],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_OK);
    }

    public function apiResourcesProvider(): array
    {
        return [
            ['countries'],
            ['regions']
        ];
    }
}
