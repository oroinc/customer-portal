<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Controller\Api\Rest\CustomerUserAddressController as BaseCustomerUserAddressController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * REST API controller for customer user address entity.
 */
class CustomerUserAddressController extends BaseCustomerUserAddressController
{
    /**
     * {@inheritdoc}
     */
    protected function getCustomerUserAddresses(CustomerUser $customerUser)
    {
        if ($customerUser !== $this->getUser()) {
            return parent::getCustomerUserAddresses($customerUser);
        }

        return $this->get('oro_customer.provider.frontend.address')->getCurrentCustomerUserAddresses();
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAccess($entity)
    {
        if ($entity !== $this->getUser() && !$this->isGranted('VIEW', $entity)) {
            throw $this->createAccessDeniedException();
        }
    }
}
