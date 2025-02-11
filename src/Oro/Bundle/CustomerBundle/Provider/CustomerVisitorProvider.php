<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\CustomerBundle\Security\VisitorIdentifierUtil;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Customer visitor security provider.
 */
class CustomerVisitorProvider implements UserProviderInterface
{
    public function __construct(
        private CustomerVisitorManager $visitorManager,
        private ManagerRegistry $doctrine
    ) {
    }

    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof CustomerVisitor) {
            throw new UnsupportedUserException(\sprintf(
                'Expected an instance of %s, but got "%s".',
                CustomerVisitor::class,
                \get_class($user)
            ));
        }
        try {
            $manager = $this->doctrine->getManagerForClass(CustomerVisitor::class);
            // try to reload existing entity to revert it's state to initial
            if (null !== $manager) {
                $manager->refresh($user);

                return $user;
            }
        } catch (ORMInvalidArgumentException $e) {
            // if entity is not managed and can not be reloaded - load it by ID from the database
        }
        $user = $this->visitorManager->find($user->getSessionId());
        if (null === $user) {
            throw new UserNotFoundException('CustomerVisitor can not be loaded.');
        }

        return $user;
    }

    #[\Override]
    public function supportsClass(string $class): bool
    {
        return $class === CustomerVisitor::class;
    }

    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!VisitorIdentifierUtil::isVisitorIdentifier($identifier)) {
            throw new UserNotFoundException('Username can not be used like a visitor identifier.');
        }
        $customerVisitor = $this->visitorManager->find(VisitorIdentifierUtil::decodeIdentifier($identifier));
        if (null === $customerVisitor) {
            throw new UserNotFoundException('CustomerVisitor can not be loaded.');
        }

        return $customerVisitor;
    }
}
