<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Oro\Bundle\CustomerBundle\DependencyInjection\Configuration;
use Oro\Bundle\CustomerBundle\EventListener\RedirectAfterLoginConfigListener;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RedirectAfterLoginConfigListenerTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private ManagerRegistry&MockObject $doctrine;
    private RedirectAfterLoginConfigListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->listener = new RedirectAfterLoginConfigListener($this->doctrine);
    }

    public function testFormPreSetWithoutValue(): void
    {
        $this->listener->formPreSet(new ConfigSettingsUpdateEvent($this->configManager, []));

        $this->doctrine->expects(self::never())
            ->method('getRepository');
    }

    public function testFormPreSet(): void
    {
        $category = new Category();
        ReflectionUtil::setId($category, 1);
        $contentNode = new ContentNode();
        ReflectionUtil::setId($contentNode, 2);

        $event = new ConfigSettingsUpdateEvent($this->configManager, [
            self::getSettingKey() => ['value' => ['category' => 1, 'contentNode' => 2]]
        ]);

        $objectRepository = $this->createMock(ObjectRepository::class);
        $objectRepository->expects(self::exactly(2))
            ->method('find')
            ->withConsecutive([1], [2])
            ->willReturnOnConsecutiveCalls($category, $contentNode);

        $this->doctrine->expects(self::exactly(2))
            ->method('getRepository')
            ->withConsecutive([Category::class], [ContentNode::class])
            ->willReturn($objectRepository);

        $this->listener->formPreSet($event);

        self::assertEquals(
            ['category' => $category, 'contentNode' => $contentNode],
            $event->getSettings()[self::getSettingKey()]['value']
        );
    }

    public function testBeforeSaveWithoutValue(): void
    {
        $event = new ConfigSettingsUpdateEvent($this->configManager, []);
        $this->listener->beforeSave($event);

        self::assertEquals([], $event->getSettings());
    }

    public function testBeforeSave(): void
    {
        $category = new Category();
        ReflectionUtil::setId($category, 1);
        $contentNode = new ContentNode();
        ReflectionUtil::setId($contentNode, 2);

        $event = new ConfigSettingsUpdateEvent($this->configManager, [
            'value' => ['category' => $category, 'contentNode' => $contentNode]
        ]);

        $this->listener->beforeSave($event);

        self::assertEquals(['category' => 1, 'contentNode' => 2], $event->getSettings()['value']);
    }

    private static function getSettingKey(): string
    {
        return TreeUtils::getConfigKey(
            Configuration::ROOT_NODE,
            Configuration::REDIRECT_AFTER_LOGIN,
            ConfigManager::SECTION_VIEW_SEPARATOR
        );
    }
}
