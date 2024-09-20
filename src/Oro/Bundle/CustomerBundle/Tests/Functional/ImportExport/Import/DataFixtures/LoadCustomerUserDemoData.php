<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import\DataFixtures;

use Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadCustomerUserDemoData as BaseLoadCustomerUserDemoData;

/**
 * Loads customer users demo data fixture.
 */
class LoadCustomerUserDemoData extends BaseLoadCustomerUserDemoData
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadCustomerDemoData::class];
    }
}
