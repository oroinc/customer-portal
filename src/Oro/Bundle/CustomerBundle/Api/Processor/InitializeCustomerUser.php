<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Sets website, organization, owner and roles to new customer user.
 */
class InitializeCustomerUser implements ProcessorInterface
{
    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var ConfigManager */
    private $configManager;

    /** @var WebsiteManager */
    private $websiteManager;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ConfigManager  $configManager
     * @param WebsiteManager $websiteManager
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ConfigManager $configManager,
        WebsiteManager $websiteManager
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->configManager = $configManager;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeFormDataContext $context */

        /** @var CustomerUser $customerUser */
        $customerUser = $context->getData();
        $this->setWebsite($customerUser);
        $this->setOrganization($customerUser);
        $this->setOwner($customerUser);
        $this->setRoles($customerUser);
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function setWebsite(CustomerUser $customerUser): void
    {
        if (null === $customerUser->getWebsite()) {
            $customerUser->setWebsite($this->websiteManager->getCurrentWebsite());
        }
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function setOrganization(CustomerUser $customerUser): void
    {
        if (null === $customerUser->getOrganization()) {
            $website = $customerUser->getWebsite();
            if (null !== $website) {
                $customerUser->setOrganization($website->getOrganization());
            }
        }
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function setOwner(CustomerUser $customerUser): void
    {
        if (null === $customerUser->getOwner()) {
            $ownerId = $this->configManager->get('oro_customer.default_customer_owner');
            if ($ownerId) {
                $customerUser->setOwner(
                    $this->doctrineHelper->getEntityManagerForClass(User::class)->find(User::class, $ownerId)
                );
            }
        }
    }

    /**
     * @param CustomerUser $customerUser
     */
    private function setRoles(CustomerUser $customerUser): void
    {
        if (count($customerUser->getUserRoles()) === 0) {
            $website = $customerUser->getWebsite();
            if (null !== $website) {
                $role = $website->getDefaultRole();
                if (null !== $role) {
                    $customerUser->addUserRole($role);
                }
            }
        }
    }
}
