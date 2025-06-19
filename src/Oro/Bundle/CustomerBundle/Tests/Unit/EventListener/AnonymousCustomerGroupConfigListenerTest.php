<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\EventListener\AnonymousCustomerGroupConfigListener;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AnonymousCustomerGroupConfigListenerTest extends TestCase
{
    private const CONFIG_KEY = 'alias.config_key';
    private const SETTINGS_KEY = 'alias___config_key';

    private ManagerRegistry&MockObject $doctrine;
    private AnonymousCustomerGroupConfigListener $listener;
    private EventDispatcherInterface&MockObject $eventDispatcher;

    #[\Override]
    protected function setUp(): void
    {
        $this->eventDispatcher = self::createMock(EventDispatcherInterface::class);
        $this->doctrine = self::createMock(ManagerRegistry::class);

        $this->listener = new AnonymousCustomerGroupConfigListener(
            $this->eventDispatcher,
            $this->doctrine,
            self::CONFIG_KEY
        );
    }

    private function getCustomerGroup(int $id): CustomerGroup
    {
        $customerGroup = new CustomerGroup();
        ReflectionUtil::setPropertyValue($customerGroup, 'id', $id);

        return $customerGroup;
    }

    public function testOnFormPreSetData(): void
    {
        $id = 1;
        $customerGroup = $this->getCustomerGroup($id);

        $entityManager = self::createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('find')
            ->with(CustomerGroup::class, $id)
            ->willReturn($customerGroup);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(CustomerGroup::class)
            ->willReturn($entityManager);

        $event = new ConfigSettingsUpdateEvent(
            self::createMock(ConfigManager::class),
            [self::SETTINGS_KEY => ['value' => $id]]
        );
        $this->listener->onFormPreSetData($event);

        self::assertEquals([self::SETTINGS_KEY => ['value' => $customerGroup]], $event->getSettings());
    }

    public function testOnFormPreSetDataWithInvalidCustomerGroup(): void
    {
        $id = 1;
        $entityManager = self::createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('find')
            ->with(CustomerGroup::class, $id)
            ->willReturn(null);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(CustomerGroup::class)
            ->willReturn($entityManager);

        $event = new ConfigSettingsUpdateEvent(
            self::createMock(ConfigManager::class),
            [self::SETTINGS_KEY => ['value' => $id]]
        );
        $this->listener->onFormPreSetData($event);

        self::assertEquals([self::SETTINGS_KEY => ['value' => null]], $event->getSettings());
    }

    public function testOnSettingsSaveBefore(): void
    {
        $event = new ConfigSettingsUpdateEvent(
            self::createMock(ConfigManager::class),
            [self::CONFIG_KEY => ['value' => $this->getCustomerGroup(1)]]
        );
        $this->listener->onSettingsSaveBefore($event);

        self::assertEquals([self::CONFIG_KEY => ['value' => 1]], $event->getSettings());
    }
}
