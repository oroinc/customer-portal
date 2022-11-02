<?php

namespace Oro\Bundle\CustomerBundle\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomerIdPlaceholder extends AbstractPlaceholder
{
    const NAME = 'CUSTOMER_ID';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        $token = $this->tokenStorage->getToken();

        if ($token && $token->getUser() instanceof CustomerUser) {
            /** @var CustomerUser $user */
            $user = $token->getUser();
            $customer = $user->getCustomer();

            return $customer ? $customer->getId() : null;
        }

        return null;
    }
}
