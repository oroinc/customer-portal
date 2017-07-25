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

    /** @var TokenStorageInterface */
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
     * @param string $email
     * @param AbstractAddress $address
     *
     * @return CustomerUser
     */
    public function createFromAddress($email, AbstractAddress $address)
    {
        $customerUser = new CustomerUser();
        $customerUser->setIsGuest(true);
        $customerUser->setEnabled(false);
        $customerUser->setConfirmed(false);
        $customerUser->setEmail($email);
        $customerUser->setNamePrefix($address->getNamePrefix());
        $customerUser->setFirstName($address->getFirstName());
        $customerUser->setMiddleName($address->getMiddleName());
        $customerUser->setLastName($address->getLastName());
        $customerUser->setNameSuffix($address->getNameSuffix());

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
}
