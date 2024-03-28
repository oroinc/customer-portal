<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Layout\DataProvider;

use Doctrine\Persistence\ManagerRegistry;
use Knp\Menu\ItemInterface;
use Oro\Bundle\CMSBundle\Entity\ContentBlock;
use Oro\Bundle\FrontendBundle\Model\QuickAccessButtonConfig;
use Oro\Bundle\FrontendBundle\Provider\QuickAccessButtonDataProvider;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;

/**
 * Layout data provider for Header System Config options.
 */
class ThemeHeaderConfigProvider
{
    public function __construct(
        private AclHelper $aclHelper,
        private ManagerRegistry $doctrine,
        private QuickAccessButtonDataProvider $quickAccessButtonDataProvider,
        private ThemeConfigurationProvider $themeConfigurationProvider
    ) {
    }

    /**
     * Returns the Alias of configured promotional content block or an empty string
     *
     * @see \Oro\Bundle\CMSBundle\Layout\DataProvider\ContentBlockDataProvider::getContentBlockView
     */
    public function getPromotionalBlockAlias(): string
    {
        $configValue = $this->themeConfigurationProvider->getThemeConfigurationOption(
            ThemeConfiguration::buildOptionKey('header', 'promotional_content')
        );

        if ($configValue) {
            return $this->doctrine
                ->getRepository(ContentBlock::class)
                ->getContentBlockAliasById($configValue, $this->aclHelper) ?? '';
        }

        return '';
    }

    public function getQuickAccessButton(): ?ItemInterface
    {
        $value = $this->getQuickAccessButtonValue();

        return $value ? $this->quickAccessButtonDataProvider->getMenu($value) : null;
    }

    public function getQuickAccessButtonLabel(): ?string
    {
        $value = $this->getQuickAccessButtonValue();

        return $value ? $this->quickAccessButtonDataProvider->getLabel($value) : null;
    }

    private function getQuickAccessButtonValue(): ?QuickAccessButtonConfig
    {
        return $this->themeConfigurationProvider->getThemeConfigurationOption(
            ThemeConfiguration::buildOptionKey('header', 'quick_access_button')
        );
    }
}
