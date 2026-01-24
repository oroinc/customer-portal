<?php

namespace Oro\Bundle\CustomerBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Defines the contract for customer user identity in the security system.
 *
 * This interface extends Symfony's UserInterface to represent customer users as security principals,
 * allowing them to be authenticated and authorized within the application.
 */
interface CustomerUserIdentity extends UserInterface
{
}
