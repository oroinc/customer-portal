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

    /** @var bool */
    private $originalGuestAccessEnabled;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enableVisitor();
        $this->loadVisitor();
    }

    protected function tearDown(): void
    {
        $configManager = $this->getConfigManager();
        $configManager->set(self::GUEST_ACCESS_ENABLED_CONFIG_NAME, $this->originalGuestAccessEnabled);
        $configManager->flush();
        parent::tearDown();
    }

    /**
     * @param bool $guestAccessEnabled
     */
    private function setGuestAccess($guestAccessEnabled)
    {
        $configManager = $this->getConfigManager();
        $this->originalGuestAccessEnabled = $configManager->get(self::GUEST_ACCESS_ENABLED_CONFIG_NAME);
        $configManager->set(self::GUEST_ACCESS_ENABLED_CONFIG_NAME, $guestAccessEnabled);
        $configManager->flush();
    }

    /**
     * @dataProvider apiResourcesProvider
     *
     * @param string $entityResource
     */
    public function testTryToGeResourcesWithDisallowedAccessForGuest($entityResource)
    {
        $this->setGuestAccess(false);
        $response = $this->cget(
            ['entity' => $entityResource],
            [],
            [],
            false
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider apiResourcesProvider
     *
     * @param string $entityResource
     */
    public function testGetResourcesWithAllowedAccessForGuest($entityResource)
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

    /**
     * @return array
     */
    public function apiResourcesProvider()
    {
        return [
            ['countries'],
            ['regions']
        ];
    }
}
