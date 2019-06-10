<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class GuestAccessApiRequestTest extends FrontendRestJsonApiTestCase
{
    protected function tearDown()
    {
        $this->setGuestAccess(true);
    }

    /**
     * @dataProvider apiResourcesProvider
     *
     * @param string $entityResource
     */
    public function testTryToGeResourcesWithDisallowedAccessForGuest($entityResource)
    {
        $this->setCurrentWebsite();
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
        $this->setCurrentWebsite();
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

    /**
     * @param bool $guestAccessEnabled
     */
    private function setGuestAccess($guestAccessEnabled)
    {
        $configManager = static::getContainer()->get('oro_config.manager');
        $configManager->set('oro_frontend.guest_access_enabled', $guestAccessEnabled);
    }
}
