<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\CustomerBundle\EventListener\SystemConfigListener;
use Oro\Bundle\UserBundle\Entity\User;

class SystemConfigListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var SystemConfigListener */
    private $listener;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $this->listener = new SystemConfigListener($this->doctrine);
    }

    private function getEvent(array $settings): ConfigSettingsUpdateEvent
    {
        return new ConfigSettingsUpdateEvent($this->configManager, $settings);
    }

    /**
     * @dataProvider invalidSettingsDataProvider
     */
    public function testOnFormPreSetDataInvalidSettings(array $settings)
    {
        $event = $this->getEvent($settings);

        $this->doctrine->expects($this->never())
            ->method($this->anything());

        $this->listener->onFormPreSetData($event);
    }

    /**
     * @dataProvider invalidSettingsDataProvider
     */
    public function testOnSettingsSaveBeforeInvalidSettings(array $settings)
    {
        $event = $this->getEvent($settings);

        $this->doctrine->expects($this->never())
            ->method($this->anything());

        $this->listener->onSettingsSaveBefore($event);
    }

    public function invalidSettingsDataProvider(): array
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

        $user = $this->createMock(User::class);

        $event = $this->getEvent([$key => ['value' => $id]]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('find')
            ->with(User::class, $id)
            ->willReturn($user);

        $this->doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($em);

        $this->listener->onFormPreSetData($event);

        $this->assertEquals([$key => ['value' => $user]], $event->getSettings());
    }

    public function testOnFormPreSetDataWithInvalidId()
    {
        $id = null;
        $key = 'oro_customer___default_customer_owner';

        $event = $this->getEvent([$key => ['value' => $id]]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())
            ->method('find');

        $this->doctrine->expects($this->never())
            ->method('getManagerForClass');

        $this->listener->onFormPreSetData($event);

        $this->assertEquals([$key => ['value' => null]], $event->getSettings());
    }

    public function testOnSettingsSaveBefore()
    {
        $id = 1;
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $event = $this->getEvent(['value' => $user]);

        $this->listener->onSettingsSaveBefore($event);

        $this->assertEquals(['value' => $id], $event->getSettings());
    }
}
