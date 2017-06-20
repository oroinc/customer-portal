<?php

namespace Oro\Bundle\CustomerBundle\Owner;

use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SecurityBundle\Owner\AbstractEntityOwnershipDecisionMaker;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

class EntityOwnershipDecisionMaker extends AbstractEntityOwnershipDecisionMaker
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * {@inheritdoc}
     */
    public function supports()
    {
        return $this->getContainer()->get('oro_security.security_facade')->getLoggedUser() instanceof CustomerUser;
    }

    /**
     * {@inheritdoc}
     */
    // TODO: please remove this workaround after BB-10196
    public function isAssociatedWithLocalLevelEntity($user, $domainObject, $deep = false, $organization = null)
    {
        $isAssociated = parent::isAssociatedWithLocalLevelEntity($user, $domainObject, $deep, $organization);

        if (!$isAssociated && $deep) {
            $metadata = $this->getObjectMetadata($domainObject);
            if ($metadata->isBasicLevelOwned() && method_exists($domainObject, 'getCustomer')) {
                $customerId = $this->getObjectId($user->getCustomer());
                $ownerId = $this->getObjectIdIgnoreNull($domainObject->getCustomer());
                $isAssociated = $customerId === $ownerId;
                if (!$isAssociated) {
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
    private function getCustomerRepository()
    {
        if (!$this->customerRepository) {
            $this->customerRepository = $this->getContainer()->get('oro_customer.repository.customer');
        }

        return $this->customerRepository;
    }
}
