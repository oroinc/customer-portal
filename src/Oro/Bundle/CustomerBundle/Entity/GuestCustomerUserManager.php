<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
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
     * @TODO: Method must be updated/refactored in scope of task BB-10377
     *
     * @return CustomerUser
     */
    public function create()
    {
        $customerUser = new CustomerUser();
        $customerUser->setUsername($this->customerUserManager->generatePassword(15));
        $generatedPassword = $this->customerUserManager->generatePassword(10);
        $customerUser->setPlainPassword($generatedPassword);
        $this->customerUserManager->updatePassword($customerUser);
        $customerUser->setEnabled(false);
        $customerUser->setConfirmed(false);
        $website = $this->websiteManager->getCurrentWebsite();
        $customerUser->setWebsite($website);
        $customerUser->setOrganization($website->getOrganization());
        $customerUser->createCustomer();
        /** Anonymous group will be used to define payment term */
        $anonymousGroup = $this->customerUserRelationsProvider->getCustomerGroup();
        $customerUser->getCustomer()->setGroup($anonymousGroup);

        $customerUserEntityManager = $this->doctrineHelper->getEntityManagerForClass(CustomerUser::class);
        $customerUserEntityManager->persist($customerUser);
        $customerUserEntityManager->flush($customerUser);

        return $customerUser;
    }
}
