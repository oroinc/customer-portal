<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Import;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUsersForImport;
use Oro\Bundle\ImportExportBundle\Tests\Functional\Import\AbstractImportTest;

class CustomerUserImportTest extends AbstractImportTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadCustomerUsersForImport::class]);
    }

    public function testButtonsAreVisible()
    {
        $this->client->request('GET', $this->getUrl('oro_customer_customer_user_index'));
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('Import File', $response);
        $this->assertContains('Validate Data File', $response);
        $this->assertContains('Download Data Template', $response);
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
    protected function getFileName()
    {
        return dirname(__FILE__).'/data/import_template.csv';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityArray()
    {
        return [
            ['firstname' => 'Jerry', 'lastname' => 'Coleman'],
            ['firstname' => 'Lorem', 'lastname' => 'Ipsum']
        ];
    }
}
