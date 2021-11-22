<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\CustomerBundle\EventListener\SystemConfigListener;
use Oro\Bundle\UserBundle\Entity\User;

class SystemConfigListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $userClass;

    /**
     * @var SystemConfigListener
     */
    protected $listener;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->userClass = User::class;

        $this->listener = new SystemConfigListener($this->registry, $this->userClass);
    }

    /**
     * @dataProvider invalidSettingsDataProvider
     * @param mixed $settings
     */
    public function testOnFormPreSetDataInvalidSettings($settings)
    {
        $event = $this->getEvent($settings);

        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->listener->onFormPreSetData($event);
    }

    /**
     * @dataProvider invalidSettingsDataProvider
     * @param mixed $settings
     */
    public function testOnSettingsSaveBeforeInvalidSettings($settings)
    {
        $event = $this->getEvent($settings);

        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->listener->onSettingsSaveBefore($event);
    }

    /**
     * @return array
     */
    public function invalidSettingsDataProvider()
    {
        return [
            [[null]],
            [[]],
            [['a' => 'b']],
            [[new \DateTime()]],
        ];
    }

    public function testOnFormPreSetData()
    {
        $id = 1;
        $key = 'oro_customer___default_customer_owner';

        $user = $this->createMock($this->userClass);

        $event = $this->getEvent([$key => ['value' => $id]]);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('find')
            ->with($this->userClass, $id)
            ->willReturn($user);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->userClass)
            ->willReturn($manager);

        $this->listener->onFormPreSetData($event);

        $this->assertEquals([$key => ['value' => $user]], $event->getSettings());
    }

    public function testOnFormPreSetDataWithInvalidId()
    {
        $id = null;
        $key = 'oro_customer___default_customer_owner';

        $event = $this->getEvent([$key => ['value' => $id]]);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->never())
            ->method('find');

        $this->registry->expects($this->never())
            ->method('getManagerForClass');

        $this->listener->onFormPreSetData($event);

        $this->assertEquals([$key => ['value' => null]], $event->getSettings());
    }

    public function testOnSettingsSaveBefore()
    {
        $id = 1;
        $user = $this->createMock($this->userClass);
        $user->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $event = $this->getEvent(['value' => $user]);

        $this->listener->onSettingsSaveBefore($event);

        $this->assertEquals(['value' => $id], $event->getSettings());
    }

    /**
     * @param array $settings
     * @return ConfigSettingsUpdateEvent
     */
    protected function getEvent(array $settings)
    {
        return new ConfigSettingsUpdateEvent($this->configManager, $settings);
    }
}
