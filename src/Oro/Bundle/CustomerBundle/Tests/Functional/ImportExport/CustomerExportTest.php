<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\ImportExportBundle\Tests\Functional\Export\AbstractExportTest;

/**
 * @dbIsolationPerTest
 */
class CustomerExportTest extends AbstractExportTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadCustomers::class]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProcessorAlias()
    {
        return 'oro_customer_customer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityName()
    {
        return Customer::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContains()
    {
        return [
            'Id',
            'Name',
            'Parent',
            'Group Name',
            LoadCustomers::CUSTOMER_LEVEL_1_1,
            LoadCustomers::DEFAULT_ACCOUNT_NAME,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getNotContains()
    {
        return ['Addresses'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedNumberOfLines()
    {
        return 16;
    }
}
