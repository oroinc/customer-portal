<?php

namespace Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;

use Oro\Bundle\CustomerBundle\Controller\Api\Rest\CustomerUserAddressController as BaseCustomerUserAddressController;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;

/**
 * @NamePrefix("oro_api_customer_frontend_")
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
}
