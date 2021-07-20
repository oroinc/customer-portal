<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadInternalRating;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCustomerData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $owner = $this->getReference('user');
        $parent = $manager->getRepository(Customer::class)->findOneByName('CustomerUser CustomerUser');

        $customer = new Customer();
        $customer->setName('customer.1')
            ->setOwner($owner)
            ->setOrganization($owner->getOrganization())
            ->setParent($parent)
            ->addSalesRepresentative($owner)
            ->setGroup($this->getReference(LoadGroups::GROUP1))
            ->setInternalRating($this->getReference('internal_rating.1 of 5'));

        $manager->persist($customer);
        $manager->flush();

        $this->addReference('customer.1', $customer);
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadUser::class,
            LoadOrganization::class,
            LoadGroups::class,
            LoadInternalRating::class
        ];
    }
}
