<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Search;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountriesAndRegions;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SearchBundle\Tests\Functional\Engine\AbstractEntitiesOrmIndexerTest;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Tests that Customer entities can be indexed without type casting errors with the ORM search engine.
 *
 * @group search
 * @dbIsolationPerTest
 */
class FrontendEntitiesOrmIndexerTest extends AbstractEntitiesOrmIndexerTest
{
    #[\Override]
    protected function getSearchableEntityClassesToTest(): array
    {
        return [
            Customer::class,
            CustomerAddress::class,
            CustomerGroup::class,
            CustomerUser::class,
            CustomerUserRole::class,
            Website::class,
        ];
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadOrganization::class,
            LoadUser::class,
            LoadCountriesAndRegions::class,
        ]);

        $manager = $this->getDoctrine()->getManagerForClass(Customer::class);
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        /** @var User $owner */
        $owner = $this->getReference(LoadUser::USER);

        $customerGroup = (new CustomerGroup())
            ->setName('Test Group')
            ->setOwner($owner)
            ->setOrganization($organization);
        $this->persistTestEntity($customerGroup);

        $parentCustomer = (new Customer())
            ->setName('Parent Customer')
            ->setOwner($owner)
            ->setOrganization($organization)
            ->setGroup($customerGroup);
        // Parent customer is just supporting data - not validated by the test, as it would have its own "parent" empty
        $manager->persist($parentCustomer);

        $customer = (new Customer())
            ->setName('Test Customer')
            ->setOwner($owner)
            ->setOrganization($organization)
            ->setGroup($customerGroup)
            ->setParent($parentCustomer);
        $this->persistTestEntity($customer);

        $customerUserRole = (new CustomerUserRole())
            ->setLabel('Test Role')
            ->setOrganization($organization);
        $this->persistTestEntity($customerUserRole);

        $customerUser = (new CustomerUser())
            ->setEmail('test@example.com')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPassword('test_password_hash')
            ->setCustomer($customer)
            ->setOwner($owner)
            ->setOrganization($organization)
            ->addUserRole($customerUserRole);
        $this->persistTestEntity($customerUser);

        $website = (new Website())->setName('Test Website')->setOrganization($organization);
        $manager->persist($website);

        /** @var Country $country */
        $country = $this->getReference('country_usa');
        /** @var Region $region */
        $region = $this->getReference('region_usa_california');

        $customerAddress = (new CustomerAddress())
            ->setLabel('Test Address')
            ->setStreet('123 Main St')
            ->setStreet2('Apt 5B')
            ->setCity('Test City')
            ->setPostalCode('12345')
            ->setCountry($country)
            ->setRegion($region)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setFrontendOwner($customer)
            ->setSystemOrganization($organization);
        $customer->addAddress($customerAddress);
        $this->persistTestEntity($customerAddress);

        $manager->flush();
    }
}
