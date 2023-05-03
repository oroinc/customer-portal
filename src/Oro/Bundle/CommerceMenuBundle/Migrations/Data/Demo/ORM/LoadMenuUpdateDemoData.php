<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Oro\Bundle\CommerceMenuBundle\Migrations\Data\AbstractMenuUpdateFixture;
use Oro\Bundle\WebCatalogBundle\Migrations\Data\Demo\ORM\LoadWebCatalogDemoData;

/**
 * Loads menu updates demo data.
 */
class LoadMenuUpdateDemoData extends AbstractMenuUpdateFixture implements DependentFixtureInterface
{
    protected function getDataPath(): string
    {
        return '@OroCommerceMenuBundle/Migrations/Data/Demo/ORM/data/menuUpdates.yml';
    }

    public function getDependencies(): array
    {
        return [
            LoadWebCatalogDemoData::class,
            LoadPromoDigitalAssetDemoData::class,
        ];
    }
}
