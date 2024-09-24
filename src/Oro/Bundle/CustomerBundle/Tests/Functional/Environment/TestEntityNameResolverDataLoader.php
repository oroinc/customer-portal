<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Environment;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;
    private CustomerUserManager $customerUserManager;

    public function __construct(
        TestEntityNameResolverDataLoaderInterface $innerDataLoader,
        CustomerUserManager $customerUserManager
    ) {
        $this->innerDataLoader = $innerDataLoader;
        $this->customerUserManager = $customerUserManager;
    }

    #[\Override]
    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (Customer::class === $entityClass) {
            $customer = new Customer();
            $customer->setName('Test Customer');
            $repository->setReference('customer', $customer);
            $em->persist($customer);
            $em->flush();

            return ['customer'];
        }

        if (CustomerAddress::class === $entityClass) {
            $customer = new Customer();
            $customer->setName('Test Customer');
            $em->persist($customer);
            $customerAddress = new CustomerAddress();
            $customerAddress->setOrganization($repository->getReference('organization'));
            $customerAddress->setOwner($repository->getReference('user'));
            $customerAddress->setFrontendOwner($customer);
            $customerAddress->setFirstName('Jane');
            $customerAddress->setMiddleName('M');
            $customerAddress->setLastName('Doo');
            $repository->setReference('customerAddress', $customerAddress);
            $em->persist($customerAddress);
            $em->flush();

            return ['customerAddress'];
        }

        if (CustomerGroup::class === $entityClass) {
            $group = new CustomerGroup();
            $group->setOrganization($repository->getReference('organization'));
            $group->setOwner($repository->getReference('user'));
            $group->setName('Test Customer Group');
            $repository->setReference('group', $group);
            $em->persist($group);
            $em->flush();

            return ['group'];
        }

        if (CustomerUser::class === $entityClass) {
            $customerUser = new CustomerUser();
            $customerUser->setOrganization($repository->getReference('organization'));
            $customerUser->setOwner($repository->getReference('user'));
            $customerUser->setEmail('john@example.com');
            $customerUser->setPassword($this->customerUserManager->generatePassword());
            $customerUser->setFirstName('John');
            $customerUser->setMiddleName('M');
            $customerUser->setLastName('Doo');
            $repository->setReference('customerUser', $customerUser);
            $this->customerUserManager->updateUser($customerUser, false);
            $em->flush();

            return ['customerUser'];
        }

        if (CustomerUserAddress::class === $entityClass) {
            $customerUser = new CustomerUser();
            $customerUser->setOrganization($repository->getReference('organization'));
            $customerUser->setOwner($repository->getReference('user'));
            $customerUser->setEmail('john_addr@example.com');
            $customerUser->setPassword($this->customerUserManager->generatePassword());
            $customerUser->setFirstName('John');
            $customerUser->setLastName('Doo');
            $this->customerUserManager->updateUser($customerUser, false);
            $customerUserAddress = new CustomerUserAddress();
            $customerUserAddress->setOrganization($repository->getReference('organization'));
            $customerUserAddress->setOwner($repository->getReference('user'));
            $customerUserAddress->setFrontendOwner($customerUser);
            $customerUserAddress->setFirstName('Jane');
            $customerUserAddress->setMiddleName('M');
            $customerUserAddress->setLastName('Doo');
            $repository->setReference('customerUserAddress', $customerUserAddress);
            $em->persist($customerUserAddress);
            $em->flush();

            return ['customerUserAddress'];
        }

        if (CustomerUserRole::class === $entityClass) {
            $role = new CustomerUserRole();
            $role->setRole('ROLE_TEST');
            $role->setLabel('Test Role');
            $repository->setReference('role', $role);
            $em->persist($role);
            $em->flush();

            return ['role'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[\Override]
    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (Customer::class === $entityClass) {
            return 'Test Customer';
        }
        if (CustomerAddress::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? 'Jane'
                : 'Jane M Doo';
        }
        if (CustomerGroup::class === $entityClass) {
            return 'Test Customer Group';
        }
        if (CustomerUser::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? 'John'
                : 'John M Doo';
        }
        if (CustomerUserAddress::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? 'Jane'
                : 'Jane M Doo';
        }
        if (CustomerUserRole::class === $entityClass) {
            return 'Test Role';
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }
}
