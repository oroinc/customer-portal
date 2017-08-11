<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

class GuestCustomerUserManager
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param WebsiteManager $websiteManager
     * @param CustomerUserManager $customerUserManager
     * @param CustomerUserRelationsProvider $customerUserRelationsProvider
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        WebsiteManager $websiteManager,
        CustomerUserManager $customerUserManager,
        CustomerUserRelationsProvider $customerUserRelationsProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->doctrineHelper                = $doctrineHelper;
        $this->websiteManager                = $websiteManager;
        $this->customerUserManager           = $customerUserManager;
        $this->customerUserRelationsProvider = $customerUserRelationsProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string|null $userName
     * @param AbstractAddress|null $address
     *
     * @return CustomerUser
     */
    public function createFromAddress($userName = null, AbstractAddress $address = null)
    {
        $customerUser = new CustomerUser();
        $customerUser->setIsGuest(true);
        $customerUser->setEnabled(false);
        $customerUser->setConfirmed(false);
        if ($userName === null) {
            $userName = $this->customerUserManager->generatePassword(10);
        }
        $customerUser->setUsername($userName);
        if ($address) {
            $customerUser->setNamePrefix($address->getNamePrefix());
            $customerUser->setFirstName($address->getFirstName());
            $customerUser->setMiddleName($address->getMiddleName());
            $customerUser->setLastName($address->getLastName());
            $customerUser->setNameSuffix($address->getNameSuffix());
        }
        $generatedPassword = $this->customerUserManager->generatePassword(10);
        $customerUser->setPlainPassword($generatedPassword);
        $this->customerUserManager->updatePassword($customerUser);
        $website = $this->websiteManager->getCurrentWebsite();
        $customerUser->setWebsite($website);
        $customerUser->setOrganization($website->getOrganization());
        $customerUser->createCustomer();

        $anonymousGroup = $this->customerUserRelationsProvider->getCustomerGroup();
        $customerUser->getCustomer()->setGroup($anonymousGroup);

        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken) {
            $visitor = $token->getVisitor();
            $visitor->setCustomerUser($customerUser);
        }

        return $customerUser;
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
