<?php

namespace Oro\Bundle\CustomerBundle\Event;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FilterCustomerUserResponseEvent event which is fired after registration
 */
class FilterCustomerUserResponseEvent extends Event
{
    /**
     * @var CustomerUser
     */
    protected $customerUser;

    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param CustomerUser $customerUser
     * @param Request $request
     * @param Response $response
     */
    public function __construct(CustomerUser $customerUser, Request $request, Response $response)
    {
        $this->customerUser = $customerUser;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
