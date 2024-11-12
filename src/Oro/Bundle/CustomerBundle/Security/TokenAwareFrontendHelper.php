<?php

namespace Oro\Bundle\CustomerBundle\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Decorates {@see FrontendHelper} to prepend an additional check of the current security token (if it exists)
 * to check whether the current request is a storefront or back-office request.
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

    #[\Override]
    public function isFrontendRequest(): bool
    {
        if ($this->emulateFrontendRequest !== null) {
            return $this->emulateFrontendRequest;
        }

        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            return
                $token instanceof AnonymousCustomerUserToken
                || $token->getUser() instanceof CustomerUserInterface;
        }

        return parent::isFrontendRequest();
    }
}
