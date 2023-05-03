<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\AbstractEntityOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Extends logic of original decision maker for entities with frontend ownership
 * Allows to see entities with LOCAL and DEEP access levels permissions
 */
class EntityOwnershipDecisionMaker extends AbstractEntityOwnershipDecisionMaker
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    public function __construct(
        OwnerTreeProviderInterface $treeProvider,
        ObjectIdAccessor $objectIdAccessor,
        EntityOwnerAccessor $entityOwnerAccessor,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        TokenAccessorInterface $tokenAccessor,
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor
    ) {
        parent::__construct($treeProvider, $objectIdAccessor, $entityOwnerAccessor, $ownershipMetadataProvider);
        $this->tokenAccessor = $tokenAccessor;
        $this->doctrine = $doctrine;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function supports()
    {
        return $this->tokenAccessor->getUser() instanceof CustomerUser;
    }

    /**
     * {@inheritdoc}
     */
    public function isAssociatedWithBusinessUnit($user, $domainObject, $deep = false, $organization = null)
    {
        $isAssociated = parent::isAssociatedWithBusinessUnit($user, $domainObject, $deep, $organization);

        if (!$isAssociated) {
            $metadata = $this->getObjectMetadata($domainObject);

            /** @var CustomerUser $user */
            if ($metadata instanceof FrontendOwnershipMetadata &&
                $metadata->isUserOwned() &&
                $metadata->getCustomerFieldName() &&
                $user->getCustomer()
            ) {
                $customerId = $this->getObjectId($user->getCustomer());

                $customer = $this->propertyAccessor->getValue($domainObject, $metadata->getCustomerFieldName());
                $ownerId = $this->getObjectIdIgnoreNull($customer);

                $isAssociated = $customerId === $ownerId;
                if (!$isAssociated && $deep) {
                    $childrenIds = $this->getCustomerRepository()->getChildrenIds($customerId);
                    $isAssociated = in_array($ownerId, $childrenIds, true);
                }
            }
        }

        return $isAssociated;
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->doctrine->getManagerForClass(Customer::class)->getRepository(Customer::class);
    }
}
