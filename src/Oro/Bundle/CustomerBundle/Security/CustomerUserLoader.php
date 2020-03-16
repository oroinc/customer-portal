<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerUserRepository;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Security\UserLoaderInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * Loads CustomerUser entity from the database for the authentication system.
 */
class CustomerUserLoader implements UserLoaderInterface
{
    /** @var ManagerRegistry */
    private $doctrine;

    /** @var ConfigManager */
    private $configManager;

    /** @var TokenAccessor */
    private $tokenAccessor;

    /** @var WebsiteManager */
    private $websiteManager;

    /**
     * @param ManagerRegistry $doctrine
     * @param ConfigManager   $configManager
     * @param TokenAccessor   $tokenAccessor
     */
    public function __construct(
        ManagerRegistry $doctrine,
        ConfigManager $configManager,
        TokenAccessor $tokenAccessor
    ) {
        $this->doctrine = $doctrine;
        $this->configManager = $configManager;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param WebsiteManager $websiteManager
     */
    public function setWebsiteManager(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserClass(): string
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUser(string $login): ?UserInterface
    {
        return $this->loadUserByEmail($login);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername(string $username): ?UserInterface
    {
        // username and email for customer users are equal
        return $this->loadUserByEmail($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByEmail(string $email): ?UserInterface
    {
        $useLowercase = (bool)$this->configManager->get('oro_customer.case_insensitive_email_addresses_enabled');

        $organization = $this->tokenAccessor->getOrganization();
        if (!$organization) {
            $website = $this->websiteManager->getCurrentWebsite();
            $organization = $website ? $website->getOrganization() : null;
        }

        if (null !== $organization) {
            return $this->getRepository()->findUserByEmailAndOrganization($email, $organization, $useLowercase);
        }

        return $this->getRepository()->findUserByEmail($email, $useLowercase);
    }

    /**
     * @return CustomerUserRepository
     */
    private function getRepository(): CustomerUserRepository
    {
        return $this->doctrine
            ->getManagerForClass($this->getUserClass())
            ->getRepository($this->getUserClass());
    }
}
