<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\AbstractEntityOwnershipDecisionMaker;
use Oro\Bundle\SecurityBundle\Owner\EntityOwnerAccessor;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;

class EntityOwnershipDecisionMaker extends AbstractEntityOwnershipDecisionMaker
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var ManagerRegistry */
    protected $doctrine;

    /**
     * @param OwnerTreeProviderInterface         $treeProvider
     * @param ObjectIdAccessor                   $objectIdAccessor
     * @param EntityOwnerAccessor                $entityOwnerAccessor
     * @param OwnershipMetadataProviderInterface $ownershipMetadataProvider
     * @param TokenAccessorInterface             $tokenAccessor
     * @param ManagerRegistry                    $doctrine
     */
    public function __construct(
        OwnerTreeProviderInterface $treeProvider,
        ObjectIdAccessor $objectIdAccessor,
        EntityOwnerAccessor $entityOwnerAccessor,
        OwnershipMetadataProviderInterface $ownershipMetadataProvider,
        TokenAccessorInterface $tokenAccessor,
        ManagerRegistry $doctrine
    ) {
        parent::__construct($treeProvider, $objectIdAccessor, $entityOwnerAccessor, $ownershipMetadataProvider);
        $this->tokenAccessor = $tokenAccessor;
        $this->doctrine = $doctrine;
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
    // TODO: please remove this workaround after BB-10196
    public function isAssociatedWithBusinessUnit($user, $domainObject, $deep = false, $organization = null)
    {
        $isAssociated = parent::isAssociatedWithBusinessUnit($user, $domainObject, $deep, $organization);

        if (!$isAssociated && $deep) {
            $metadata = $this->getObjectMetadata($domainObject);
            if ($metadata->isUserOwned() && method_exists($domainObject, 'getCustomer')) {
                $customerId = $this->getObjectId($user->getCustomer());
                $ownerId = $this->getObjectIdIgnoreNull($domainObject->getCustomer());
                $isAssociated = $customerId === $ownerId;
                if (!$isAssociated) {
                    /** @var CustomerRepository $customerRepository */
                    $customerRepository = $this->doctrine->getRepository(Customer::class);
                    $childrenIds = $customerRepository->getChildrenIds($customerId);
                    $isAssociated = in_array($ownerId, $childrenIds, true);
                }
            }
        }

        return $isAssociated;
    }
}
