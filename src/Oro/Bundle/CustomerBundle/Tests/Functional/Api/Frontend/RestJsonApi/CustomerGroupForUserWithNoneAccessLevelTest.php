<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\RestJsonApi;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures\LoadBuyerCustomerUserData;
use Oro\Bundle\FrontendBundle\Tests\Functional\Api\FrontendRestJsonApiTestCase;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Symfony\Component\HttpFoundation\Response;

class CustomerGroupForUserWithNoneAccessLevelTest extends FrontendRestJsonApiTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadBuyerCustomerUserData::class,
            '@OroCustomerBundle/Tests/Functional/Api/Frontend/DataFixtures/customer_group.yml'
        ]);
        $this->updateRolePermission(
            $this->getReference('buyer')->getRole(),
            CustomerGroup::class,
            AccessLevel::NONE_LEVEL
        );
    }

    public function testTryToGetList()
    {
        $response = $this->cget(
            ['entity' => 'customergroups'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }

    public function testTryGet()
    {
        $response = $this->get(
            ['entity' => 'customergroups', 'id' => '<toString(@customer_group1->id)>'],
            [],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'access denied exception',
                'detail' => 'No access to this type of entities.'
            ],
            $response,
            Response::HTTP_FORBIDDEN
        );
    }
}
