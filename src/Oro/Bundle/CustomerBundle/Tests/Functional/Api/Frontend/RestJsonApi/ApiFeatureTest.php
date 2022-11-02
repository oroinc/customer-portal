<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\ApiFeatureTrait;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadAdminCustomerUserData;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class ApiFeatureTest extends FrontendRestJsonApiTestCase
{
    use ApiFeatureTrait;

    private const API_FEATURE_NAME = 'oro_frontend.web_api';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadAdminCustomerUserData::class,
            LoadCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer.yml'
        ]);
    }

    public function testGetListOptionsOnEnabledFeature()
    {
        $response = $this->options(
            $this->getListRouteName(),
            ['entity' => 'customers']
        );

        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testTryToGetListOptionsOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->options(
                $this->getListRouteName(),
                ['entity' => 'customers'],
                [],
                false
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testGetListOnEnabledFeature()
    {
        $response = $this->cget(['entity' => 'customers']);

        $this->assertResponseContains('cget_customer.yml', $response);
    }

    public function testTryToGetListOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->cget(
                ['entity' => 'customers'],
                [],
                [],
                false
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testGetOnEnabledFeature()
    {
        $response = $this->get(
            ['entity' => 'customers', 'id' => '<toString(@customer3->id)>']
        );

        $this->assertResponseContains('get_customer.yml', $response);
    }

    public function testTryToGetOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->get(
                ['entity' => 'customers', 'id' => '<toString(@customer3->id)>'],
                [],
                [],
                false
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToCreateOnEnabledFeature()
    {
        $response = $this->post(
            ['entity' => 'customers'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToCreateOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->post(
                ['entity' => 'customers'],
                [],
                [],
                false
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToUpdateOnEnabledFeature()
    {
        $response = $this->patch(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToUpdateOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->patch(
                ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
                [],
                [],
                false
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDeleteOnEnabledFeature()
    {
        $response = $this->delete(
            ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
            [],
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteOnDisabledFeature()
    {
        $this->disableApiFeature(self::API_FEATURE_NAME);
        try {
            $response = $this->delete(
                ['entity' => 'customers', 'id' => '<toString(@customer1->id)>'],
                [],
                [],
                false
            );
        } finally {
            $this->enableApiFeature(self::API_FEATURE_NAME);
        }

        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
