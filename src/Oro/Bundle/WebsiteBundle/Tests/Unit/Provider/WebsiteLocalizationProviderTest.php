<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\DependencyInjection\Configuration;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteLocalizationProvider;
use Oro\Component\Testing\ReflectionUtil;

class WebsiteLocalizationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var LocalizationManager|\PHPUnit\Framework\MockObject\MockObject */
    private $localizationManager;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var WebsiteRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $websiteRepository;

    /** @var WebsiteLocalizationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->localizationManager = $this->createMock(LocalizationManager::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->websiteRepository = $this->createMock(WebsiteRepository::class);

        $this->provider = new WebsiteLocalizationProvider(
            $this->configManager,
            $this->localizationManager,
            $this->doctrineHelper
        );
    }

    private function getWebsite(int $id): Website
    {
        $website = new Website();
        ReflectionUtil::setId($website, $id);

        return $website;
    }

    private function getLocalization(int $id): Localization
    {
        $localization = new Localization();
        ReflectionUtil::setId($localization, $id);

        return $localization;
    }

    public function testGetLocalizations()
    {
        $websiteId = 42;
        $ids = [100, 200];

        $localizations = [
            $this->getLocalization(100),
            $this->getLocalization(200),
        ];

        $this->configManager->expects($this->once())
            ->method('get')
            ->with(sprintf('oro_locale.%s', Configuration::ENABLED_LOCALIZATIONS))
            ->willReturn($ids);

        $this->localizationManager->expects($this->once())
            ->method('getLocalizations')
            ->with($ids)
            ->willReturn($localizations);

        $this->assertEquals($localizations, $this->provider->getLocalizations($this->getWebsite($websiteId)));
    }

    public function testGetLocalizationsByWebsiteId()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Website::class)
            ->willReturn($this->websiteRepository);

        $this->websiteRepository->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn(new Website());

        $this->websiteRepository->expects($this->never())
            ->method('getDefaultWebsite');

        $this->provider->getLocalizationsByWebsiteId(42);
    }

    public function testGetLocalizationsByWebsiteIdEmptyId()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Website::class)
            ->willReturn($this->websiteRepository);

        $this->websiteRepository->expects($this->never())
            ->method('find');

        $this->websiteRepository->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn(new Website());

        $this->provider->getLocalizationsByWebsiteId();
    }

    public function testGetLocalizationsByWebsiteIdNonExistentId()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Website::class)
            ->willReturn($this->websiteRepository);

        $this->websiteRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn(null);

        $this->websiteRepository->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn(new Website());

        $this->provider->getLocalizationsByWebsiteId(123);
    }

    public function testGetLocalizationsByWebsiteIdNonIntegerId()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Website::class)
            ->willReturn($this->websiteRepository);

        $this->websiteRepository->expects($this->never())
            ->method('find');

        $this->websiteRepository->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn(new Website());

        $this->provider->getLocalizationsByWebsiteId('string');
    }
}
