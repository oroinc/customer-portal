<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Operation;

use Oro\Bundle\ActionBundle\Tests\Functional\ActionTestCase;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;

class CustomerUserDeleteOperationTest extends ActionTestCase
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
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData'
            ]
        );
    }

    public function testDelete()
    {
        /** @var \Oro\Bundle\CustomerBundle\Entity\CustomerUser $user */
        $user = $this->getUserRepository()->findOneBy(['email' => LoadCustomerUserData::EMAIL]);

        $this->assertNotNull($user);
        $id = $user->getId();

        $this->assertDeleteOperation(
            $id,
            CustomerUser::class,
            'oro_customer_customer_user_index'
        );

        $this->getObjectManager()->clear();
        $user = $this->getUserRepository()->find($id);

        $this->assertNull($user);
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
    protected function getUserRepository()
    {
        return $this->getObjectManager()->getRepository('OroCustomerBundle:CustomerUser');
    }
}
