<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\UserBundle\Provider\DefaultUserProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Provides a set of methods to simplify manage of the guest CustomerUser entities.
 */
class GuestCustomerUserManager
{
    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var CustomerUserManager
     */
    protected $customerUserManager;

    /**
     * @var CustomerUserRelationsProvider
     */
    protected $customerUserRelationsProvider;

    /**
     * @var DefaultUserProvider
     */
    protected $defaultUserProvider;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    public function __construct(
        WebsiteManager $websiteManager,
        CustomerUserManager $customerUserManager,
        CustomerUserRelationsProvider $customerUserRelationsProvider,
        DefaultUserProvider $defaultUserProvider,
        PropertyAccessor $propertyAccessor
    ) {
        $this->websiteManager = $websiteManager;
        $this->customerUserManager = $customerUserManager;
        $this->customerUserRelationsProvider = $customerUserRelationsProvider;
        $this->defaultUserProvider = $defaultUserProvider;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param array $properties
     *
     * @return CustomerUser
     */
    public function generateGuestCustomerUser(array $properties = [])
    {
        $customerUser = new CustomerUser();
        $this->initializeGuestCustomerUser($customerUser, $properties);

        return $customerUser;
    }

    public function initializeGuestCustomerUser(CustomerUser $customerUser, array $properties = [])
    {
        $customerUser->setIsGuest(true);
        $customerUser->setEnabled(false);
        $customerUser->setConfirmed(false);

        $owner = $this->defaultUserProvider->getDefaultUser('oro_customer.default_customer_owner');
        $customerUser->setOwner($owner);
        $website = $this->websiteManager->getCurrentWebsite();
        $customerUser->setWebsite($website);
        if ($website && $website->getOrganization()) {
            $customerUser->setOrganization($website->getOrganization());
            $customerUser->addUserRole($website->getDefaultRole());
        }

        foreach ($properties as $propertyPath => $value) {
            if ($this->propertyAccessor->isWritable($customerUser, $propertyPath)) {
                $this->propertyAccessor->setValue($customerUser, $propertyPath, $value);
            }
        }

        $generatedPassword = $this->customerUserManager->generatePassword(10);
        $customerUser->setPlainPassword($generatedPassword);
        $this->customerUserManager->updatePassword($customerUser);

        $customerUser->createCustomer();

        $anonymousGroup = $this->customerUserRelationsProvider->getCustomerGroup();
        $customerUser->getCustomer()->setGroup($anonymousGroup);
    }

    /**
     * @param string|null $userName
     * @param AbstractAddress|null $address
     *
     * @return CustomerUser
     */
    public function createFromAddress($userName = null, AbstractAddress $address = null)
    {
        $properties = [
            'username' => $userName ?? $this->customerUserManager->generatePassword(10)
        ];
        if (null !== $address) {
            $properties['name_prefix'] = $address->getNamePrefix();
            $properties['first_name'] = $address->getFirstName();
            $properties['middle_name'] = $address->getMiddleName();
            $properties['last_name'] = $address->getLastName();
            $properties['name_suffix'] = $address->getNameSuffix();
        }

        return $this->generateGuestCustomerUser($properties);
    }

    /**
     * @param CustomerUser $customerUser
     * @param string $userName
     * @param AbstractAddress $address
     *
     * @return CustomerUser
     */
    public function updateFromAddress(CustomerUser $customerUser, $userName, AbstractAddress $address)
    {
        $customerUser->setUsername($userName);
        $customerUser->setNamePrefix($address->getNamePrefix());
        $customerUser->setFirstName($address->getFirstName());
        $customerUser->setMiddleName($address->getMiddleName());
        $customerUser->setLastName($address->getLastName());
        $customerUser->setNameSuffix($address->getNameSuffix());
        $customerUser->fillCustomer();

        return $customerUser;
    }
}
