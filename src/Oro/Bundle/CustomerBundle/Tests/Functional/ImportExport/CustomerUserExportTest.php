<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData;
use Oro\Bundle\ImportExportBundle\Tests\Functional\Export\AbstractExportTest;

class CustomerUserExportTest extends AbstractExportTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadCustomerUserData::class]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProcessorAlias()
    {
        return 'oro_customer_customer_user';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityName()
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContains()
    {
        return [
            'First Name',
            'Last Name',
            'Email',
            'Customer Name',
            'Enabled',
            'Confirmed',
            LoadCustomerUserData::FIRST_NAME,
            LoadCustomerUserData::LAST_NAME,
            LoadCustomerUserData::LEVEL_1_1_EMAIL,
            LoadCustomerUserData::LEVEL_1_1_FIRST_NAME,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getNotContains()
    {
        return [
            'Name Prefix',
            'Middle Name',
            'Birthday',
            'Addresses',
            'Owner',
            'Sales Representatives',
            LoadCustomerUserData::LEVEL_1_PASSWORD,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedNumberOfLines()
    {
        return 8;
    }
}
