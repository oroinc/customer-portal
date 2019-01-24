<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor\Create;

use Oro\Bundle\ApiBundle\Processor\Create\CreateContext;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Saves new CustomerUser entity to the database.
 */
class SaveCustomerUser implements ProcessorInterface
{
    /** @var CustomerUserManager */
    private $userManager;

    /**
     * @param CustomerUserManager $userManager
     */
    public function __construct(CustomerUserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CreateContext $context */

        /** @var CustomerUser $user */
        $user = $context->getResult();
        if (!is_object($user)) {
            // entity does not exist
            return;
        }

        // generate random secure password for a user
        if (!$user->getPlainPassword()) {
            $user->setPlainPassword($this->userManager->generatePassword(30));
        }

        $this->userManager->updateUser($user);
    }
}
