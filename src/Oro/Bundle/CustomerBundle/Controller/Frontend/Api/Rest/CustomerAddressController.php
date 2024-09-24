<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use Oro\Bundle\CustomerBundle\Controller\Api\Rest\CommerceCustomerAddressController as BaseCustomerAddressController;
use Oro\Bundle\CustomerBundle\Entity\Customer;

/**
 * REST API controller for customer address entity.
 */
class CustomerAddressController extends BaseCustomerAddressController
{
    #[\Override]
    protected function getCustomerAddresses(Customer $customer)
    {
        if ($customer !== $this->getUser()->getCustomer()) {
            return parent::getCustomerAddresses($customer);
        }

        return $this->container->get('oro_customer.provider.frontend.address')->getCurrentCustomerAddresses();
    }

    #[\Override]
    protected function checkAccess($entity)
    {
        if ($entity !== $this->getUser()->getCustomer() && !$this->isGranted('VIEW', $entity)) {
            throw $this->createAccessDeniedException();
        }
    }
}
