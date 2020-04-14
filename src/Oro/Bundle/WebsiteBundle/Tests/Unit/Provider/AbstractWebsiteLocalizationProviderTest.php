<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Component\Testing\Unit\EntityTrait;

abstract class AbstractWebsiteLocalizationProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $configManager;

    /** @var LocalizationManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $localizationManager;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $doctrineHelper;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->localizationManager = $this->createMock(LocalizationManager::class);

        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
    }

    /**
     * @param int $id
     * @return Website
     */
    protected function getWebsite($id)
    {
        return $this->getEntity(Website::class, ['id' => $id]);
    }

    /**
     * @param int $id
     * @return Localization
     */
    protected function getLocalization($id)
    {
        return $this->getEntity(Localization::class, ['id' => $id]);
    }
}
