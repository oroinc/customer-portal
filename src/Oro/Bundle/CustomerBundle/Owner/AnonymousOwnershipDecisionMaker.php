<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\AbstractEntityOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;

/**
 * Makes ownership decisions for anonymous customer user entities.
 *
 * This decision maker handles ACL ownership checks for entities accessed by anonymous
 * customer users (guest visitors). It verifies that domain objects are associated with
 * the current visitor by checking the CustomerVisitorOwnerAwareInterface implementation
 * and comparing visitor instances.
 */
class AnonymousOwnershipDecisionMaker extends AbstractEntityOwnershipDecisionMaker
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(
        OwnerTreeProviderInterface $treeProvider,
        ObjectIdAccessor $objectIdAccessor,
        EntityOwnerAccessor $entityOwnerAccessor,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        TokenAccessorInterface $tokenAccessor
    ) {
        parent::__construct($treeProvider, $objectIdAccessor, $entityOwnerAccessor, $ownershipMetadataProvider);
        $this->tokenAccessor = $tokenAccessor;
    }

    #[\Override]
    public function supports()
    {
        return $this->tokenAccessor->getToken() instanceof AnonymousCustomerUserToken;
    }

    #[\Override]
    public function isAssociatedWithOrganization($user, $domainObject, $organization = null)
    {
        return $this->isAssociatedWithUser($user, $domainObject, $organization);
    }

    #[\Override]
    public function isAssociatedWithBusinessUnit($user, $domainObject, $deep = false, $organization = null)
    {
        return $this->isAssociatedWithUser($user, $domainObject, $organization);
    }

    #[\Override]
    public function isAssociatedWithUser($user, $domainObject, $organization = null)
    {
        /** @var AnonymousCustomerUserToken $token */
        $token = $this->tokenAccessor->getToken();

        return $domainObject instanceof CustomerVisitorOwnerAwareInterface
            && $token->getVisitor() === $domainObject->getVisitor();
    }
}
