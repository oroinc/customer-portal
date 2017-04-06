<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

class CustomerUserFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'Jerry Coleman':
                $entity->setCustomer((new Customer())->setName('Oro Inc.'))
                    ->setFirstName('Jerry')
                    ->setLastName('Coleman')
                    ->setEmail('example@email.com')
                    ->setConfirmed(false)
                    ->setEnabled(true);

                return;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Jerry Coleman');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new CustomerUser();
    }
}
