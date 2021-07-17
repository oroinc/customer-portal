<?php

namespace Oro\Bundle\CustomerBundle\Security\Guesser;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Guesser\OrganizationGuesserInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The organization guesser decorator that guesses an organization to login into the storefront.
 */
class OrganizationGuesser implements OrganizationGuesserInterface
{
    /** @var OrganizationGuesserInterface */
    private $innerGuesser;

    /** @var FrontendHelper */
    private $frontendHelper;

    /** @var WebsiteManager */
    private $websiteManager;

    public function __construct(
        OrganizationGuesserInterface $innerGuesser,
        FrontendHelper $frontendHelper,
        WebsiteManager $websiteManager
    ) {
        $this->innerGuesser = $innerGuesser;
        $this->frontendHelper = $frontendHelper;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function guess(AbstractUser $user, TokenInterface $token = null): ?Organization
    {
        if ($user instanceof User || !$this->frontendHelper->isFrontendRequest()) {
            return $this->innerGuesser->guess($user, $token);
        }

        if ($user instanceof CustomerUser) {
            if ($token instanceof OrganizationAwareTokenInterface) {
                $organization = $token->getOrganization();
                if (null !== $organization) {
                    return $organization;
                }
            }

            $website = $this->websiteManager->getCurrentWebsite();
            if (null !== $website) {
                return $website->getOrganization();
            }
        }

        return null;
    }
}
