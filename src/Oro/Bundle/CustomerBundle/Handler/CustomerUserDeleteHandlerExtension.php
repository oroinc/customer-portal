<?php

namespace Oro\Bundle\CustomerBundle\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;
use Oro\Bundle\OrganizationBundle\Ownership\OwnerDeletionManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * The delete handler extension for CustomerUser entity.
 */
class CustomerUserDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
{
    /** @var TokenAccessorInterface */
    private $tokenAccessor;

    /** @var OwnerDeletionManager */
    private $ownerDeletionManager;

    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        OwnerDeletionManager $ownerDeletionManager
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->ownerDeletionManager = $ownerDeletionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function assertDeleteGranted($entity): void
    {
        /** @var CustomerUser $entity */

        $loggedUser = $this->tokenAccessor->getUser();
        if ($loggedUser instanceof CustomerUser && $loggedUser->getId() === $entity->getId()) {
            throw $this->createAccessDeniedException('self delete');
        }

        if ($this->ownerDeletionManager->hasAssignments($entity)) {
            throw $this->createAccessDeniedException('has associations to other entities');
        }
    }
}
