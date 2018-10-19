<?php

namespace Oro\Bundle\CustomerBundle\Layout\DataProvider;

use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * The data provider for "sign in" form.
 */
class SignInProvider
{
    /** @var array */
    protected $options = [];

    /** @var RequestStack */
    protected $requestStack;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var CsrfTokenManagerInterface */
    protected $csrfTokenManager;

    /** @var SignInTargetPathProviderInterface */
    private $targetPathProvider;

    /**
     * @param RequestStack                      $requestStack
     * @param TokenAccessorInterface            $tokenAccessor
     * @param CsrfTokenManagerInterface         $csrfTokenManager
     * @param SignInTargetPathProviderInterface $targetPathProvider
     */
    public function __construct(
        RequestStack $requestStack,
        TokenAccessorInterface $tokenAccessor,
        CsrfTokenManagerInterface $csrfTokenManager,
        SignInTargetPathProviderInterface $targetPathProvider
    ) {
        $this->requestStack = $requestStack;
        $this->tokenAccessor = $tokenAccessor;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->targetPathProvider = $targetPathProvider;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        if (!array_key_exists('last_username', $this->options)) {
            $request = $this->requestStack->getCurrentRequest();
            $session = $request->getSession();
            
            // last username entered by the user
            $this->options['last_username'] = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);
        }

        return $this->options['last_username'];
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        if (!array_key_exists('error', $this->options)) {
            $request = $this->requestStack->getCurrentRequest();
            $session = $request->getSession();

            // get the error if any (works with forward and redirect -- see below)
            if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
                $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
            } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
                $error = $session->get(Security::AUTHENTICATION_ERROR);
                $session->remove(Security::AUTHENTICATION_ERROR);
            } else {
                $error = '';
            }

            $this->options['error'] = $error;
        }

        return $this->options['error'];
    }

    /**
     * @return string
     */
    public function getCSRFToken()
    {
        if (!array_key_exists('csrf_token', $this->options)) {
            $this->options['csrf_token'] = $this->csrfTokenManager->getToken('authenticate')->getValue();
        }

        return $this->options['csrf_token'];
    }

    /**
     * @return mixed|null
     */
    public function getLoggedUser()
    {
        return $this->tokenAccessor->getUser();
    }

    /**
     * @return string|null
     */
    public function getTargetPath(): ?string
    {
        return $this->targetPathProvider->getTargetPath();
    }
}
