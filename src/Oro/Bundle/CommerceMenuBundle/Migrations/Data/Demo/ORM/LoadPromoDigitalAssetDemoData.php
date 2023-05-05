<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Data\Demo\ORM;

use Oro\Bundle\DigitalAssetBundle\Migrations\Data\AbstractDigitalAssetFixture;

/**
 * Loads digital assets for demo menu updates.
 */
class LoadPromoDigitalAssetDemoData extends AbstractDigitalAssetFixture
{
    protected function getDataPath(): string
    {
        return '@OroCommerceMenuBundle/Migrations/Data/Demo/ORM/data/promoDigitalAssets.yml';
    }
}
