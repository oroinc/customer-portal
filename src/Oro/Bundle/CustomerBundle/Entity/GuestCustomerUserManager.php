<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;
use Oro\Bundle\CustomerBundle\EventListener\SystemConfigListener;
use Oro\Bundle\UserBundle\Provider\DefaultUserProvider;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

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

    /**
     * @param WebsiteManager $websiteManager
     * @param CustomerUserManager $customerUserManager
     * @param CustomerUserRelationsProvider $customerUserRelationsProvider
     * @param DefaultUserProvider $defaultUserProvider
     * @param PropertyAccessor $propertyAccessor
     */
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
        $customerUser->setIsGuest(true);
        $customerUser->setEnabled(false);
        $customerUser->setConfirmed(false);

        $owner = $this->defaultUserProvider->getDefaultUser(
            OroCustomerExtension::ALIAS,
            SystemConfigListener::SETTING
        );
        $customerUser->setOwner($owner);
        $website = $this->websiteManager->getCurrentWebsite();
        $customerUser->setWebsite($website);
        if ($website && $website->getOrganization()) {
            $customerUser->setOrganization($website->getOrganization());
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

        return $customerUser;
    }

    /**
     * @param string|null $userName
     * @param AbstractAddress|null $address
     *
     * @return CustomerUser
     */
    public function createFromAddress($userName = null, AbstractAddress $address = null)
    {
        if ($userName === null) {
            $userName = $this->customerUserManager->generatePassword(10);
        }

        return $this->generateGuestCustomerUser([
            'username' => $userName,
            'name_prefix' => $address->getNamePrefix(),
            'first_name' => $address->getFirstName(),
            'middle_name' => $address->getMiddleName(),
            'last_name' => $address->getLastName(),
            'name_suffix' => $address->getNameSuffix()
        ]);
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
