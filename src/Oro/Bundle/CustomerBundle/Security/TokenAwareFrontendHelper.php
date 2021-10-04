<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The helper class that use the current security token if it exists
 * to check whether the current request is a storefront or management console request.
 * @see \Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\ConfigureFrontendHelperPass
 */
class TokenAwareFrontendHelper extends FrontendHelper
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param string $backendPrefix
     * @param RequestStack $requestStack
     * @param ApplicationState $applicationState
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        $backendPrefix,
        RequestStack $requestStack,
        ApplicationState $applicationState,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($backendPrefix, $requestStack, $applicationState);
        $this->tokenStorage = $tokenStorage;
    }

    public function isFrontendRequest(): bool
    {
        $token = $this->tokenStorage->getToken();
        if (null !== $token && $token->isAuthenticated()) {
            return
                $token instanceof AnonymousCustomerUserToken
                || $token->getUser() instanceof CustomerUserInterface;
        }

        return parent::isFrontendRequest();
    }
}
