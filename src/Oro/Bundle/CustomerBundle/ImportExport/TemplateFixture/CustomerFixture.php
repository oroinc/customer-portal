<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Loads simple customer fixture.
 */
class CustomerFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * @var EnumOptionsProvider
     */
    private $enumOptionsProvider;

    public function __construct(EnumOptionsProvider $enumOptionsProvider)
    {
        $this->enumOptionsProvider = $enumOptionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Customer::class;
    }

    /**
     * {@inheritdoc}
     * @param Customer $entity
     */
    public function fillEntityData($key, $entity)
    {
        $entity->setName('Company A - East Division');
        $entity->setGroup((new CustomerGroup())->setName('All Customers'));
        $entity->setParent((new Customer())->setName('Company A'));
        $entity->setOwner($this->createOwner());

        $internalRating = new EnumOption(
            Customer::INTERNAL_RATING_CODE,
            '1 of 5',
            '1_of_5'
        );

        $entity->setInternalRating($internalRating);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Company A - East Division');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        $customer = new Customer();

        $reflectionClass = new \ReflectionClass(Customer::class);
        $method = $reflectionClass->getProperty('id');
        $method->setAccessible(true);
        $method->setValue($customer, 1);

        return $customer;
    }

    /**
     * @return User
     */
    private function createOwner()
    {
        $user = new User();
        $reflectionUser = new \ReflectionClass($user);

        $userId = $reflectionUser->getProperty('id');
        $userId->setAccessible(true);
        $userId->setValue($user, 1);
        $userId->setAccessible(false);

        return $user;
    }
}
