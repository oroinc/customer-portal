<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData;

class CustomerUserRoleDeleteOperationTest extends ActionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserRoleData'
            ]
        );
    }

    public function testDelete()
    {
        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUserRole $userRole */
        $userRole = $this->getUserRoleRepository()
            ->findOneBy(['label' => LoadCustomerUserRoleData::ROLE_EMPTY]);

        $this->assertNotNull($userRole);

        $id = $userRole->getId();

        $this->assertDeleteOperation(
            $id,
            CustomerUserRole::class,
            'oro_customer_customer_user_role_index'
        );

        $this->getObjectManager()->clear();
        $userRole = $this->getUserRoleRepository()->find($id);

        $this->assertNull($userRole);
    }

    /**
     * @return \Doctrine\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getUserRoleRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUserRole');
    }
}
