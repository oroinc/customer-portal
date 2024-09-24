<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import\DataFixtures;

use Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadCustomerDemoData as BaseLoadCustomerDemoData;
use Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadCustomerGroupDemoData;

/**
 * Loads Customers demo data for default organization
 */
class LoadCustomerDemoData extends BaseLoadCustomerDemoData
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadCustomerInternalRatingDemoData::class,
            LoadCustomerGroupDemoData::class
        ];
    }
}
