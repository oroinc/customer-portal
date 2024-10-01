<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Security\Listener;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Security\Listener\CustomerUserDoctrineAclCacheListener;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\SecurityBundle\Cache\DoctrineAclCacheProvider;
use Oro\Bundle\SecurityBundle\EventListener\DoctrineAclCacheListener;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadBusinessUnit;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\Group;

/**
 * @dbIsolationPerTest
 */
class CustomerUserDoctrineAclCacheListenerTest extends WebTestCase
{
    /** @var DoctrineAclCacheProvider|\PHPUnit\Framework\MockObject\MockObject  */
    private $queryCacheProvider;

    /** @var DoctrineAclCacheListener */
    private $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([
            LoadCustomerUserData::class,
            LoadOrganization::class,
            LoadBusinessUnit::class
        ]);

        $this->queryCacheProvider = $this->createMock(DoctrineAclCacheProvider::class);

        $container = self::getContainer();
        $this->listener = new CustomerUserDoctrineAclCacheListener(
            $this->queryCacheProvider,
            $container->get('oro_customer.owner.tree_provider')
        );

        $container->get('doctrine')->getManager()->getEventManager()->addEventListener('onFlush', $this->listener);
    }

    private function getRole(string $roleName = 'ROLE_FRONTEND_ADMINISTRATOR'): CustomerUserRole
    {
        return self::getContainer()->get('doctrine')->getManager()->getRepository(CustomerUserRole::class)
            ->findOneBy([
                'role' => $roleName
            ]);
    }

    private function expectUpdatedCustomers(array $expectedCustomerIds): void
    {
        $this->queryCacheProvider->expects(self::once())
            ->method('clearForEntities')
            ->willReturnCallback(function (string $className, array $customerIds) use ($expectedCustomerIds) {
                self::assertEquals(Customer::class, $className);
                self::assertCount(count($expectedCustomerIds), $customerIds);
                self::assertEquals([], array_diff($expectedCustomerIds, $customerIds));
            });
    }

    private function persistEntity(object $entity): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $em->persist($entity);
        $em->flush();
    }

    private function deleteEntity(object $entity): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();

        $em->remove($entity);
        $em->flush();
    }

    public function testOnNewNonSupportedEntity(): void
    {
        $entity = new Group();
        $entity->setOwner($this->getReference('business_unit'))
            ->setOrganization($this->getReference('organization'))
            ->setName('test group');

        $this->queryCacheProvider->expects(self::never())
            ->method('clearForEntities');

        $this->persistEntity($entity);
    }

    public function testOnNewCustomerUser(): void
    {
        $organization = $this->getReference('organization');
        $owner = $this->getReference('user');
        $customer = $this->getReference('customer.level_1.2.1.1');

        $customerUser = new CustomerUser();
        $customerUser
            ->setIsGuest(false)
            ->setCustomer($customer)
            ->setOwner($owner)
            ->setEmail('test@test.com')
            ->setEnabled(true)
            ->setOrganization($organization)
            ->addUserRole($this->getRole())
            ->setPlainPassword('iuh789(*&YUI')
            ->setConfirmed(true);

        $this->expectUpdatedCustomers([
            $customer->getId(),
            $this->getReference('customer.level_1.2.1')->getId(),
            $this->getReference('customer.level_1.2')->getId(),
            $this->getReference('customer.level_1')->getId(),
        ]);

        self::getContainer()->get('oro_customer_user.manager')->updateUser($customerUser);
    }

    public function testOnNewCustomerWithoutCustomerUsers(): void
    {
        $organization = $this->getReference('organization');
        $owner = $this->getReference('user');
        $parentCustomer = $this->getReference('customer.level_1.2.1');

        $customer = new Customer();
        $customer->setOwner($owner)
            ->setOrganization($organization)
            ->setParent($parentCustomer)
            ->setName('testCustomer');

        $this->queryCacheProvider->expects(self::never())
            ->method('clearForEntities');

        $this->persistEntity($customer);
    }

    public function testOnUpdateCustomerUserChangeCustomer(): void
    {
        $customerUser = $this->getReference(LoadCustomerUserData::LEVEL_1_1_EMAIL);
        $newCustomer = $this->getReference('customer.level_1_1');

        $customerUser->setCustomer($newCustomer);

        $this->expectUpdatedCustomers([
            $newCustomer->getId(),
            $this->getReference('customer.level_1.1')->getId(),
            $this->getReference('customer.level_1')->getId(),
        ]);

        self::getContainer()->get('oro_customer_user.manager')->updateUser($customerUser);
    }

    public function testOnUpdateCustomerChangeParent(): void
    {
        $customer = $this->getReference('customer.level_1.1');
        $newParent = $this->getReference('customer.level_1.3.1');

        $customer->setParent($newParent);

        $this->expectUpdatedCustomers([
            $customer->getId(),
            $newParent->getId(),
            $this->getReference('customer.level_1')->getId(),
            $this->getReference('customer.level_1.3')->getId(),
        ]);

        $this->persistEntity($customer);
    }

    public function testOnDeleteCustomer(): void
    {
        $customer = $this->getReference('customer.level_1.1');

        $this->expectUpdatedCustomers([
            $customer->getId(),
            $this->getReference('customer.level_1')->getId(),
            $this->getReference('customer.level_1.1.1')->getId(),
            $this->getReference('customer.level_1.1.2')->getId()
        ]);

        $em = self::getContainer()->get('doctrine')->getManager();

        $em->remove($customer);
        $em->flush();
    }

    public function testEditCustomerUserAddRole(): void
    {
        $customerUser = $this->getReference(LoadCustomerUserData::LEVEL_1_1_EMAIL);
        $customerUser->addUserRole($this->getRole('ROLE_FRONTEND_BUYER'));

        $this->expectUpdatedCustomers([
            $this->getReference('customer.level_1.1')->getId()
        ]);

        self::getContainer()->get('oro_customer_user.manager')->updateUser($customerUser);
    }
}
