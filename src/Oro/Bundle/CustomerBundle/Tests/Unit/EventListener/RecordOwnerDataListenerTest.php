<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\EventListener\RecordOwnerDataListener;
use Oro\Bundle\CustomerBundle\Tests\Unit\Fixtures\Entity\User;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixture\Entity\Entity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RecordOwnerDataListenerTest extends \PHPUnit\Framework\TestCase
{
    /**  @var RecordOwnerDataListener */
    protected $listener;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenStorage;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $configProvider;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);

        $this->listener = new RecordOwnerDataListener($this->tokenStorage, $this->configProvider);
    }

    /**
     * @param $token
     * @param $securityConfig
     * @param $expect
     *
     * @dataProvider preSetData
     */
    public function testPrePersistUser($token, $securityConfig, $expect)
    {
        $entity = new Entity();
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

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
        $entityConfigId = $this->getMockBuilder(EntityConfigId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = new User();
        $user->setId(1);

        $customer = $this->createMock(Customer::class);
        $user->setCustomer($customer);

        $userConfig = new Config($entityConfigId);
        $userConfig->setValues(
            [
                "frontend_owner_type" => "FRONTEND_USER",
                "frontend_owner_field_name" => "owner",
                "frontend_owner_column_name" => "owner_id"
            ]
        );
        $buConfig = new Config($entityConfigId);
        $buConfig->setValues(
            [
                "frontend_owner_type" => "FRONTEND_CUSTOMER",
                "frontend_owner_field_name" => "owner",
                "frontend_owner_column_name" => "owner_id"
            ]
        );
        $organizationConfig = new Config($entityConfigId);
        $organizationConfig->setValues(
            [
                "frontend_owner_type" => "FRONTEND_ORGANIZATION",
                "frontend_owner_field_name" => "owner",
                "frontend_owner_column_name" => "owner_id"
            ]
        );

        return [
            'OwnershipType User with UsernamePasswordToken' => [
                new UsernamePasswordToken($user, 'admin', 'key'),
                $userConfig,
                ['owner' => $user]
            ],
            'OwnershipType Customer with UsernamePasswordToken' => [
                new UsernamePasswordToken($user, 'admin', 'key'),
                $buConfig,
                ['owner' => $customer]
            ],
            'OwnershipType Organization with UsernamePasswordToken' => [
                new UsernamePasswordToken($user, 'admin', 'key'),
                $organizationConfig,
                []
            ],
        ];
    }
}
