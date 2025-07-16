<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Helper;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\WebsiteBundle\Helper\WebsiteAwareEntityHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteAwareEntityHelperTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private WebsiteAwareEntityHelper $helper;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->helper = new WebsiteAwareEntityHelper($this->configManager);
    }

    private function getEntityConfig(string $entityClass, array $values): Config
    {
        return new Config(new EntityConfigId('website', $entityClass), $values);
    }

    public function testIsWebsiteAwareForNotConfiguredEntityClass(): void
    {
        $entityClass = \stdClass::class;

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(false);

        self::assertFalse($this->helper->isWebsiteAware($entityClass));
    }

    public function testIsWebsiteAwareForNotConfiguredEntity(): void
    {
        $entity = new \stdClass();
        $entityClass = get_class($entity);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(false);

        self::assertFalse($this->helper->isWebsiteAware($entity));
    }

    public function testIsWebsiteAwareForNotWebsiteAwareEntityClass(): void
    {
        $entityClass = \stdClass::class;
        $entityConfig = $this->getEntityConfig($entityClass, []);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('website', $entityClass)
            ->willReturn($entityConfig);

        self::assertFalse($this->helper->isWebsiteAware($entityClass));
    }

    public function testIsWebsiteAwareForNotWebsiteAwareEntity(): void
    {
        $entity = new \stdClass();
        $entityClass = get_class($entity);
        $entityConfig = $this->getEntityConfig($entityClass, []);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('website', $entityClass)
            ->willReturn($entityConfig);

        self::assertFalse($this->helper->isWebsiteAware($entity));
    }

    public function testIsWebsiteAwareForDisabledWebsiteAwareEntityClass(): void
    {
        $entityClass = \stdClass::class;
        $entityConfig = $this->getEntityConfig($entityClass, ['is_website_aware' => false]);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('website', $entityClass)
            ->willReturn($entityConfig);

        self::assertFalse($this->helper->isWebsiteAware($entityClass));
    }

    public function testIsWebsiteAwareForDisabledWebsiteAwareEntity(): void
    {
        $entity = new \stdClass();
        $entityClass = get_class($entity);
        $entityConfig = $this->getEntityConfig($entityClass, ['is_website_aware' => false]);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('website', $entityClass)
            ->willReturn($entityConfig);

        self::assertFalse($this->helper->isWebsiteAware($entity));
    }

    public function testIsWebsiteAwareForWebsiteAwareEntityClass(): void
    {
        $entityClass = \stdClass::class;
        $entityConfig = $this->getEntityConfig($entityClass, ['is_website_aware' => true]);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('website', $entityClass)
            ->willReturn($entityConfig);

        self::assertTrue($this->helper->isWebsiteAware($entityClass));
    }

    public function testIsWebsiteAwareForWebsiteAwareEntity(): void
    {
        $entity = new \stdClass();
        $entityClass = get_class($entity);
        $entityConfig = $this->getEntityConfig($entityClass, ['is_website_aware' => true]);

        $this->configManager->expects(self::once())
            ->method('hasConfig')
            ->with($entityClass)
            ->willReturn(true);
        $this->configManager->expects(self::once())
            ->method('getEntityConfig')
            ->with('website', $entityClass)
            ->willReturn($entityConfig);

        self::assertTrue($this->helper->isWebsiteAware($entity));
    }
}
