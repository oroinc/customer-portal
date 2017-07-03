<?php

namespace Oro\Bundle\CustomerBundle\Action;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\CustomerBundle\Entity\GuestCustomerUserManager;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Component\Action\Action\AssignActiveUser;
use Oro\Component\ConfigExpression\ContextAccessor;

class GetOrCreateActiveUser extends AssignActiveUser
{
    /**
     * @var GuestCustomerUserManager
     */
    protected $guestCustomerUserManager;

    /**
     * @param ContextAccessor          $contextAccessor
     * @param TokenStorageInterface    $tokenStorage
     * @param GuestCustomerUserManager $guestCustomerUserManager
     */
    public function __construct(
        ContextAccessor $contextAccessor,
        TokenStorageInterface $tokenStorage,
        GuestCustomerUserManager $guestCustomerUserManager
    ) {
        parent::__construct($contextAccessor, $tokenStorage);

        $this->guestCustomerUserManager = $guestCustomerUserManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $customerUser = null;

        $token = $this->tokenStorage->getToken();
        if ($token instanceof AnonymousCustomerUserToken) {
            /**
             * @TODO: Try to get customer user by customer visitor
             */
            $customerUser = $this->guestCustomerUserManager->create();
        } elseif (null !== $token) {
            $customerUser = $token->getUser();
        }

        $this->contextAccessor->setValue($context, $this->options['attribute'], $customerUser);
    }
}
