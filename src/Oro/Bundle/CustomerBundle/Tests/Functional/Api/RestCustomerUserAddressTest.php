<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserAddresses;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;

/**
 * @group CommunityEdition
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class RestCustomerUserAddressTest extends RestJsonApiTestCase
{
    use UserUtilityTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([LoadCustomerUserAddresses::class]);
    }

    public function testGetCustomerUserAddresses()
    {
        $uri = $this->getUrl('oro_rest_api_cget', ['entity' => $this->getEntityType(CustomerUserAddress::class)]);

        $response = $this->request('GET', $uri, []);
        $this->assertApiResponseStatusCodeEquals($response, Response::HTTP_OK, CustomerUserAddress::class, 'get list');
        $content = json_decode($response->getContent(), true);

        $this->assertCount(5, $content['data']);
    }

}
