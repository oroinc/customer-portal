<?php

namespace Oro\Bundle\CustomerBundle\Security\Guesser;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Guesser\OrganizationGuesserInterface;
use Oro\Bundle\SecurityBundle\Exception\BadUserOrganizationException;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

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

    #[\Override]
    public function guess(AbstractUser $user): ?Organization
    {
        if ($user instanceof User || !$this->frontendHelper->isFrontendRequest()) {
            return $this->innerGuesser->guess($user);
        }
        $organization = null;
        if ($user instanceof CustomerUser) {
            $website = $this->websiteManager->getCurrentWebsite();
            $organization = $website?->getOrganization();
        }
        if (null === $organization) {
            throw new BadUserOrganizationException(
                'The user does not have active organization assigned to it.'
            );
        }
        if (!$user->isBelongToOrganization($organization, true)) {
            throw new BadUserOrganizationException(sprintf(
                'The user does not have access to organization "%s".',
                $organization->getName()
            ));
        }

        return $organization;
    }
}
