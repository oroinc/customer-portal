<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import\DataFixtures;

use Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM\LoadCustomerUserDemoData as BaseLoadCustomerUserDemoData;

/**
 * Loads customer users demo data fixture.
 */
class LoadCustomerUserDemoData extends BaseLoadCustomerUserDemoData
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomerDemoData::class];
    }
}
