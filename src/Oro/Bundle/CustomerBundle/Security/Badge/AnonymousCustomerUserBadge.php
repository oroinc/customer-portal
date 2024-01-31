<?php

namespace Oro\Bundle\CustomerBundle\Security\Badge;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\EventListener\UserProviderListener;

/**
 * Anonymous customer user badge.
 */
class AnonymousCustomerUserBadge extends UserBadge
{
    private $userIdentifier;
    private $userLoader;
    private $user;

    public function __construct(string $userIdentifier, callable $userLoader = null)
    {
        parent::__construct($userIdentifier, $userLoader);

        $this->userIdentifier = $userIdentifier;
        $this->userLoader = $userLoader;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getUser(): UserInterface
    {
        if (null !== $this->user) {
            return $this->user;
        }

        if (null === $this->userLoader) {
            throw new \LogicException(
                sprintf(
                    'No user loader is configured, did you forget to register the "%s" listener?',
                    UserProviderListener::class
                )
            );
        }

        $user = ($this->userLoader)($this->userIdentifier);

        // No user has been found via the $this->userLoader callback
        if (null === $user) {
            $exception = new UserNotFoundException();
            $exception->setUserIdentifier($this->userIdentifier);

            throw $exception;
        }

        if (!$user instanceof CustomerVisitor) {
            throw new AuthenticationServiceException(
                sprintf(
                    'The user provider must return a CustomerVisitor object, "%s" given.',
                    get_debug_type($user)
                )
            );
        }

        return $this->user = $user;
    }

    public function getUserLoader(): ?callable
    {
        return $this->userLoader;
    }

    public function setUserLoader(callable $userLoader): void
    {
        $this->userLoader = $userLoader;
    }

    public function isResolved(): bool
    {
        return true;
    }
}
