<?php

namespace Oro\Bundle\CustomerBundle\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides the CUSTOMER_ID placeholder for website search queries.
 *
 * This placeholder resolves to the ID of the customer associated with the currently
 * authenticated customer user. It returns null if the user is not authenticated or
 * if the customer user is not associated with a customer.
 */
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

    #[\Override]
    public function getPlaceholder()
    {
        return self::NAME;
    }

    #[\Override]
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
