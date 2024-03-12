<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
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
        private CustomerVisitorManager $customerVisitorManager,
        private ManagerRegistry $doctrine
    ) {
    }

    public function refreshUser(UserInterface $customerVisitor): UserInterface
    {
        if (!$customerVisitor instanceof CustomerVisitor) {
            throw new UnsupportedUserException(sprintf(
                'Expected an instance of %s, but got "%s".',
                CustomerVisitor::class,
                get_class($customerVisitor)
            ));
        }
        try {
            $manager = $this->doctrine->getManagerForClass(ClassUtils::getClass($customerVisitor));
            // try to reload existing entity to revert it's state to initial
            if (null !== $manager) {
                $manager->refresh($customerVisitor);

                return $customerVisitor;
            }
        } catch (ORMInvalidArgumentException $exception) {
            // if entity is not managed and can not be reloaded - load it by ID from the database
        }
        $customerVisitor = $this->customerVisitorManager->find(
            $customerVisitor->getId(),
            $customerVisitor->getSessionId()
        );
        if (null === $customerVisitor) {
            throw new UserNotFoundException('CustomerVisitor can not be loaded.');
        }

        return $customerVisitor;
    }

    public function supportsClass(string $class): bool
    {
        return $class === CustomerVisitor::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!VisitorIdentifierUtil::isVisitorIdentifier($identifier)) {
            throw new UserNotFoundException('Username can not be used like a visitor identifier.');
        }
        list($visitorId, $visitorSessionId) = VisitorIdentifierUtil::decodeIdentifier($identifier);
        $customerVisitor = $this->customerVisitorManager->find($visitorId, $visitorSessionId);
        if (null === $customerVisitor) {
            throw new UserNotFoundException('CustomerVisitor can not be loaded.');
        }

        return $customerVisitor;
    }
}
