<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Import\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumOptionRepository;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsite;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

class LoadCustomerUserData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadUser::class,
            LoadWebsite::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var User $user */
        $user = $this->getReference(LoadUser::USER);

        $this->loadCustomerInternalRatings($manager);
        $this->loadCustomerGroups($manager, $user);
        $this->loadCustomers($manager, $user);
        $this->loadCustomerUsers($manager, $user->getOrganization());

        $manager->flush();
    }

    private function loadCustomerInternalRatings(ObjectManager $manager): void
    {
        /** @var EnumOptionRepository $enumRepo */
        $enumRepo = $manager->getRepository(EnumOption::class);
        $priority = 1;
        $data = ['1_of_1' => '1 of 1'];
        foreach ($data as $id => $name) {
            $enumOption = $enumRepo->createEnumOption(Customer::INTERNAL_RATING_CODE, $id, $name, $priority++);
            $enumOption->setLocale(Translator::DEFAULT_LOCALE);
            $this->setReference('customer_internal_rating.' . $id, $enumOption);
            $manager->persist($enumOption);
        }
    }

    private function loadCustomerGroups(ObjectManager $manager, User $owner): void
    {
        $names = ['All Customers', 'Wholesale Customers', 'Partners'];
        foreach ($names as $name) {
            $customerGroup = new CustomerGroup();
            $customerGroup->setName($name);
            $customerGroup->setOrganization($owner->getOrganization());
            $customerGroup->setOwner($owner);
            $this->setReference('customer_group.' . $name, $customerGroup);
            $manager->persist($customerGroup);
        }
    }

    private function loadCustomers(ObjectManager $manager, User $owner): void
    {
        $data = [
            'Company A' => [
                'group' => 'All Customers',
                'subsidiaries' => [
                    'Company A - East Division' => [
                        'group' => 'All Customers'
                    ],
                    'Company A - West Division' => [
                        'group' => 'All Customers'
                    ]
                ]
            ],
            'Wholesaler B' => [
                'group' => 'Wholesale Customers'
            ]
        ];
        foreach ($data as $customerName => $item) {
            $customer = $this->createCustomer(
                $manager,
                $customerName,
                $owner,
                $this->getReference('customer_group.' . $item['group'])
            );
            if (isset($item['subsidiaries'])) {
                foreach ($item['subsidiaries'] as $subsidiaryName => $subsidiaryData) {
                    $this->createCustomer(
                        $manager,
                        $subsidiaryName,
                        $owner,
                        $this->getReference('customer_group.' . $subsidiaryData['group']),
                        $customer
                    );
                }
            }
        }
    }

    private function createCustomer(
        ObjectManager $manager,
        string $name,
        User $owner,
        CustomerGroup $group,
        ?Customer $parent = null
    ): Customer {
        $customer = new Customer();
        $customer->setName($name);
        $customer->setOwner($owner);
        $customer->setOrganization($owner->getOrganization());
        $customer->setGroup($group);
        $customer->setInternalRating($this->getReference('customer_internal_rating.1_of_1'));
        $customer->setParent($parent);
        $this->setReference('customer.' . $name, $customer);
        $manager->persist($customer);

        return $customer;
    }

    private function loadCustomerUsers(ObjectManager $manager, Organization $organization): void
    {
        /** @var CustomerUserManager $userManager */
        $userManager = $this->container->get('oro_customer_user.manager');
        /** @var Website $website */
        $website = $this->getReference(LoadWebsite::WEBSITE);
        /** @var CustomerUserRole $role */
        $role = $manager->getRepository(CustomerUserRole::class)->findOneBy(
            ['label' => 'Administrator', 'organization' => $organization]
        );

        $data = [
            [
                'customer' => 'Company A',
                'email' => 'AmandaRCole@example.org',
                'firstName' => 'Amanda',
                'lastName' => 'Cole'
            ],
            [
                'customer' => 'Company A - East Division',
                'email' => 'LonnieVTownsend@example.org',
                'firstName' => 'Lonnie',
                'lastName' => 'Townsend'
            ],
            [
                'customer' => 'Company A - West Division',
                'email' => 'RuthWMaxwell@example.org',
                'firstName' => 'Ruth',
                'lastName' => 'Maxwell'
            ],
            [
                'customer' => 'Wholesaler B',
                'email' => 'NancyJSallee@example.com',
                'firstName' => 'Nancy',
                'lastName' => 'Sallee'
            ]
        ];
        foreach ($data as $item) {
            /** @var Customer $customer */
            $customer = $this->getReference('customer.' . $item['customer']);

            /** @var CustomerUser $customerUser */
            $customerUser = $userManager->createUser();
            $customerUser->setOwner($customer->getOwner());
            $customerUser->setOrganization($organization);
            $customerUser->setWebsite($website);
            $customerUser->setCustomer($customer);
            $customerUser->setUsername($item['email']);
            $customerUser->setEmail($item['email']);
            $customerUser->setFirstName($item['firstName']);
            $customerUser->setLastName($item['lastName']);
            $customerUser->setPlainPassword($item['email']);
            $customerUser->addUserRole($role);
            $userManager->updateUser($customerUser, false);
            $this->addReference('customer_user.' . $item['email'], $customerUser);
        }
    }
}
