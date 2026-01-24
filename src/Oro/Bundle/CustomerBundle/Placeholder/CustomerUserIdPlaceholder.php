<?php

namespace Oro\Bundle\CustomerBundle\Placeholder;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\WebsiteSearchBundle\Placeholder\AbstractPlaceholder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides the CUSTOMER_USER_ID placeholder for website search queries.
 *
 * This placeholder resolves to the ID of the currently authenticated customer user.
 * It returns null if the user is not authenticated or is not a customer user.
 */
class CustomerUserIdPlaceholder extends AbstractPlaceholder
{
    const NAME = 'CUSTOMER_USER_ID';

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
            return $user->getId();
        }

        return null;
    }
}
