<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Oro\Bundle\CustomerBundle\Controller\Api\Rest\CommerceCustomerAddressController as BaseCustomerAddressController;
use Oro\Bundle\CustomerBundle\Entity\Customer;

/**
 * @NamePrefix("oro_api_customer_frontend_")
 */
class CustomerAddressController extends BaseCustomerAddressController
{
    /**
     * {@inheritdoc}
     */
    protected function getCustomerAddresses(Customer $customer)
    {
        if ($customer !== $this->getUser()->getCustomer()) {
            return parent::getCustomerAddresses($customer);
        }

        return $this->get('oro_customer.provider.frontend.address')->getCurrentCustomerAddresses();
    }
}
