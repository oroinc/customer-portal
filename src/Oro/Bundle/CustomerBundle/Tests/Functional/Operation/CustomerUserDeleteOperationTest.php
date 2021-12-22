<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Operation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;

class CustomerUserDeleteOperationTest extends ActionTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    public function testDelete()
    {
        /** @var CustomerUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadCustomerUserData::EMAIL]);

        $this->assertNotNull($user);
        $id = $user->getId();

        $this->assertDeleteOperation(
            $id,
            CustomerUser::class,
            'oro_customer_customer_user_index'
        );

        $this->getEntityManager()->clear();
        $user = $this->getUserRepository()->find($id);

        $this->assertNull($user);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function getUserRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository(CustomerUser::class);
    }
}
