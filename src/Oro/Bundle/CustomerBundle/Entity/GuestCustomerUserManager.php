<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\CustomerBundle\Provider\CustomerUserRelationsProvider;
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
     * @param DoctrineHelper                $doctrineHelper
     * @param WebsiteManager                $websiteManager
     * @param CustomerUserManager           $customerUserManager
     * @param CustomerUserRelationsProvider $customerUserRelationsProvider
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        WebsiteManager $websiteManager,
        CustomerUserManager $customerUserManager,
        CustomerUserRelationsProvider $customerUserRelationsProvider
    ) {
        $this->doctrineHelper                = $doctrineHelper;
        $this->websiteManager                = $websiteManager;
        $this->customerUserManager           = $customerUserManager;
        $this->customerUserRelationsProvider = $customerUserRelationsProvider;
    }

    /**
     * @param CustomerVisitor $visitor
     *
     * @return CustomerUser
     */
    public function getOrCreate(CustomerVisitor $visitor)
    {
        if ($visitor->getCustomerUser()) {
            $customerUser = $visitor->getCustomerUser();
        } else {
            $customerUser = $this->create();
            $visitor->setCustomerUser($customerUser);

            $customerVisitorEntityManager = $this->doctrineHelper->getEntityManagerForClass(CustomerVisitor::class);
            $customerVisitorEntityManager->persist($visitor);
            $customerVisitorEntityManager->flush($visitor);
        }

        return $customerUser;
    }

    /**
     * @return CustomerUser
     */
    public function create()
    {
        $customerUser = new CustomerUser();
        $customerUser->setIsGuest(true);
        $customerUser->setEnabled(false);
        $customerUser->setConfirmed(false);
        $customerUser->setUsername($this->customerUserManager->generatePassword(15));
        $generatedPassword = $this->customerUserManager->generatePassword(10);
        $customerUser->setPlainPassword($generatedPassword);
        $this->customerUserManager->updatePassword($customerUser);
        $website = $this->websiteManager->getCurrentWebsite();
        $customerUser->setWebsite($website);
        $customerUser->setOrganization($website->getOrganization());
        $customerUser->createCustomer();
        $anonymousGroup = $this->customerUserRelationsProvider->getCustomerGroup();
        $customerUser->getCustomer()->setGroup($anonymousGroup);

        $customerUserEntityManager = $this->doctrineHelper->getEntityManagerForClass(CustomerUser::class);
        $customerUserEntityManager->persist($customerUser);
        $customerUserEntityManager->flush($customerUser);

        return $customerUser;
    }
}
