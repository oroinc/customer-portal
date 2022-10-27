<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\ScopeBundle\Manager\ScopeCacheKeyBuilderInterface;
use Oro\Bundle\ScopeBundle\Model\ScopeCriteria;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Adds customer user and website/organization IDs to the cache key if they exist in the current security context.
 */
class CustomerUserScopeCacheKeyBuilder implements ScopeCacheKeyBuilderInterface
{
    /** @var ScopeCacheKeyBuilderInterface */
    private $innerBuilder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WebsiteManager */
    private $websiteManager;

    /** @var FrontendHelper */
    private $frontendHelper;

    public function __construct(
        ScopeCacheKeyBuilderInterface $innerBuilder,
        TokenStorageInterface $tokenStorage,
        WebsiteManager $websiteManager,
        FrontendHelper $frontendHelper
    ) {
        $this->innerBuilder = $innerBuilder;
        $this->tokenStorage = $tokenStorage;
        $this->websiteManager = $websiteManager;
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(ScopeCriteria $criteria): ?string
    {
        if (!$this->frontendHelper->isFrontendRequest()) {
            return $this->innerBuilder->getCacheKey($criteria);
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $customerUserId = $this->getCustomerUserId($token);
        if (null === $customerUserId) {
            return null;
        }

        $cacheKey = 'data;customerUser=' . $customerUserId;
        $website = $this->websiteManager->getCurrentWebsite();
        if (null !== $website) {
            $cacheKey .= ';website=' . $website->getId();
        } elseif ($token instanceof OrganizationAwareTokenInterface) {
            $cacheKey .= ';organization=' . $token->getOrganization()->getId();
        }

        return $cacheKey;
    }

    private function getCustomerUserId(TokenInterface $token): ?string
    {
        if ($token instanceof AnonymousCustomerUserToken) {
            return 'anonymous';
        }

        $user = $token->getUser();

        return $user instanceof CustomerUser
            ? (string)$user->getId()
            : null;
    }
}
