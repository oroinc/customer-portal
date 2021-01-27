<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures\Entity\User;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixture\Entity\Entity;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RecordOwnerDataListenerTest extends \PHPUnit\Framework\TestCase
{
    /**  @var RecordOwnerDataListener */
    protected $listener;

    /** @var CustomerUserProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerUserProvider;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $configProvider;

    protected function setUp(): void
    {
        $this->customerUserProvider = $this->createMock(CustomerUserProvider::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);

        $this->listener = new RecordOwnerDataListener(
            $this->customerUserProvider,
            $this->configProvider,
            PropertyAccess::createPropertyAccessor()
        );
    }

    /**
     * @param $user
     * @param $securityConfig
     * @param $expect
     *
     * @dataProvider preSetData
     */
    public function testPrePersistUser($user, $securityConfig, $expect)
    {
        $entity = new Entity();
        $this->customerUserProvider->expects($this->once())
            ->method('getLoggedUser')
            ->with(false)
            ->will($this->returnValue($user));

        $args = new LifecycleEventArgs($entity, $this->createMock(ObjectManager::class));
        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->will($this->returnValue(true));
        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($securityConfig));

        $this->listener->prePersist($args);
        if (isset($expect['owner'])) {
            $this->assertEquals($expect['owner'], $entity->getOwner());
        } else {
            $this->assertNull($entity->getOwner());
        }
    }

    /**
     * @return array
     */
    public function preSetData()
    {
        /** @var EntityConfigId $entityConfigId */
        $entityConfigId = $this->createMock(EntityConfigId::class);

        $user = new User();
        $user->setId(1);

        $customer = $this->createMock(Customer::class);
        $user->setCustomer($customer);

        $userConfig = new Config($entityConfigId);
        $userConfig->setValues(
            [
                'frontend_owner_type' => 'FRONTEND_USER',
                'frontend_owner_field_name' => 'owner',
                'frontend_owner_column_name' => 'owner_id'
            ]
        );
        $buConfig = new Config($entityConfigId);
        $buConfig->setValues(
            [
                'frontend_owner_type' => 'FRONTEND_CUSTOMER',
                'frontend_owner_field_name' => 'owner',
                'frontend_owner_column_name' => 'owner_id'
            ]
        );
        $organizationConfig = new Config($entityConfigId);
        $organizationConfig->setValues(
            [
                'frontend_owner_type' => 'FRONTEND_ORGANIZATION',
                'frontend_owner_field_name' => 'owner',
                'frontend_owner_column_name' => 'owner_id'
            ]
        );

        return [
            'OwnershipType User' => [
                $user,
                $userConfig,
                ['owner' => $user]
            ],
            'OwnershipType Customer' => [
                $user,
                $buConfig,
                ['owner' => $customer]
            ],
            'OwnershipType Organization' => [
                $user,
                $organizationConfig,
                []
            ],
        ];
    }
}
