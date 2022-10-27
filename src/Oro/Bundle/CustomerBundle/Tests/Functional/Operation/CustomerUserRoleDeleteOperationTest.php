<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Operation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
        $this->loadFixtures([LoadCustomerUserRoleData::class]);
    }

    public function testDelete()
    {
        /** @var CustomerUserRole $userRole */
        $userRole = $this->getUserRoleRepository()
            ->findOneBy(['label' => LoadCustomerUserRoleData::ROLE_EMPTY]);

        $this->assertNotNull($userRole);

        $id = $userRole->getId();

        $this->assertDeleteOperation(
            $id,
            CustomerUserRole::class,
            'oro_customer_customer_user_role_index'
        );

        $this->getEntityManager()->clear();
        $userRole = $this->getUserRoleRepository()->find($id);

        $this->assertNull($userRole);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function getUserRoleRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(CustomerUserRole::class);
    }
}
