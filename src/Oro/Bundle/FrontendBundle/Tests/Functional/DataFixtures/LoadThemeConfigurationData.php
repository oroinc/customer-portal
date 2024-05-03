<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Oro\Bundle\CMSBundle\Tests\Functional\DataFixtures\LoadContentBlockData;
use Oro\Bundle\LayoutBundle\Layout\Extension\ThemeConfiguration;
use Oro\Bundle\ThemeBundle\Tests\Functional\DataFixtures\LoadThemeConfigurationData as BaseLoadThemeConfigurationData;

class LoadThemeConfigurationData extends BaseLoadThemeConfigurationData implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            LoadContentBlockData::class,
        ];
    }

    protected function processConfiguration(array $configuration): array
    {
        $key = ThemeConfiguration::buildOptionKey('header', 'promotional_content');
        $configuration[$key] = $this->getReference('content_block_1')->getId();

        return $configuration;
    }
}
