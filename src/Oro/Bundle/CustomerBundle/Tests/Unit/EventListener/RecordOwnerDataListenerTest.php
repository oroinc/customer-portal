<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Fixtures\Entity\TestEntity;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RecordOwnerDataListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $customerUserProvider;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var RecordOwnerDataListener */
    private $listener;

    protected function setUp(): void
    {
        $this->customerUserProvider = $this->createMock(CustomerUserProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->listener = new RecordOwnerDataListener(
            $this->customerUserProvider,
            $this->configManager,
            PropertyAccess::createPropertyAccessor()
        );
    }

    private function getEventArgs(TestEntity $entity): BaseLifecycleEventArgs
    {
        return new BaseLifecycleEventArgs($entity, $this->createMock(ObjectManager::class));
    }

    private function getEntityConfig(array $values = []): Config
    {
        return new Config($this->createMock(EntityConfigId::class), $values);
    }

    private function expectsGetLoggedUser(CustomerUser $user): void
    {
        $this->customerUserProvider->expects(self::once())
            ->method('getLoggedUser')
            ->with(self::isFalse())
            ->willReturn($user);
    }

    public function testPrePersistWhenNoLoggedInUser(): void
    {
        $entity = new TestEntity();

        $this->customerUserProvider->expects(self::once())
            ->method('getLoggedUser')
            ->with(self::isFalse())
            ->willReturn(null);

        $this->configManager->expects(self::never())
            ->method(self::anything());

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertNull($entity->getCustomerUser());
        self::assertNull($entity->getCustomer());
    }

    public function testPrePersistForNotConfigurableEntity(): void
    {
        $entity = new TestEntity();
        $user = new CustomerUser();

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(false);
        $this->configManager->expects(self::never())
            ->method('getEntityConfig');

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertNull($entity->getCustomerUser());
        self::assertNull($entity->getCustomer());
    }

    public function testPrePersistForEntityWithoutOwnership(): void
    {
        $entity = new TestEntity();
        $user = new CustomerUser();

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig());

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertNull($entity->getCustomerUser());
        self::assertNull($entity->getCustomer());
    }

    public function testPrePersistForEntityWithCustomerOwnership(): void
    {
        $entity = new TestEntity();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'       => 'FRONTEND_CUSTOMER',
                'frontend_owner_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertNull($entity->getCustomerUser());
        self::assertSame($customer, $entity->getCustomer());
    }

    public function testPrePersistForEntityWithCustomerOwnershipAndWhenEntityAlreadyHasCustomer(): void
    {
        $entity = new TestEntity();
        $anotherCustomer = new Customer();
        $entity->setCustomer($anotherCustomer);
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'       => 'FRONTEND_CUSTOMER',
                'frontend_owner_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertNull($entity->getCustomerUser());
        self::assertSame($anotherCustomer, $entity->getCustomer());
    }

    public function testPrePersistForEntityWithCustomerOwnershipAndWhenLoggedInUserDoesNotHaveCustomer(): void
    {
        $entity = new TestEntity();
        $user = new CustomerUser();

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'       => 'FRONTEND_CUSTOMER',
                'frontend_owner_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertNull($entity->getCustomerUser());
        self::assertNull($entity->getCustomer());
    }

    public function testPrePersistForEntityWithUserOwnership(): void
    {
        $entity = new TestEntity();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'          => 'FRONTEND_USER',
                'frontend_owner_field_name'    => 'customerUser',
                'frontend_customer_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertSame($user, $entity->getCustomerUser());
        self::assertSame($customer, $entity->getCustomer());
    }

    public function testPrePersistForEntityWithUserOwnershipAndWhenEntityAlreadyHasUser(): void
    {
        $anotherUser = new CustomerUser();
        $entity = new TestEntity();
        $entity->setCustomerUser($anotherUser);
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'          => 'FRONTEND_USER',
                'frontend_owner_field_name'    => 'customerUser',
                'frontend_customer_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertSame($anotherUser, $entity->getCustomerUser());
        self::assertSame($customer, $entity->getCustomer());
    }

    public function testPrePersistForEntityWithUserOwnershipAndWhenEntityAlreadyHasCustomer(): void
    {
        $anotherCustomer = new Customer();
        $entity = new TestEntity();
        $entity->setCustomer($anotherCustomer);
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'          => 'FRONTEND_USER',
                'frontend_owner_field_name'    => 'customerUser',
                'frontend_customer_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertSame($user, $entity->getCustomerUser());
        self::assertSame($anotherCustomer, $entity->getCustomer());
    }

    public function testPrePersistForEntityWithUserOwnershipAndWhenCustomerFieldNotConfigured(): void
    {
        $entity = new TestEntity();
        $customer = new Customer();
        $user = new CustomerUser();
        $user->setCustomer($customer);

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'       => 'FRONTEND_USER',
                'frontend_owner_field_name' => 'customerUser'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertSame($user, $entity->getCustomerUser());
        self::assertNull($entity->getCustomer());
    }

    public function testPrePersistForEntityWithUserOwnershipAndWhenLoggedInUserDoesNotHaveCustomer(): void
    {
        $entity = new TestEntity();
        $user = new CustomerUser();

        $this->expectsGetLoggedUser($user);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with(TestEntity::class)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('ownership', TestEntity::class)
            ->willReturn($this->getEntityConfig([
                'frontend_owner_type'          => 'FRONTEND_USER',
                'frontend_owner_field_name'    => 'customerUser',
                'frontend_customer_field_name' => 'customer'
            ]));

        $this->listener->prePersist($this->getEventArgs($entity));

        self::assertSame($user, $entity->getCustomerUser());
        self::assertNull($entity->getCustomer());
    }
}
